<?php

namespace Aught\SpaceBundle\Entity;

use Aught\SpaceBundle\Entity\EmailSection;

/**
 * Email (not stored in database)
 *
 */
class Email
{
    private $sections;
    private $top_section_id;

    public function __construct($data)
    {
        $mail_resource = mailparse_msg_create();
        $parse_success = mailparse_msg_parse($mail_resource, $data);
        if (!$parse_success) {
            throw new \Exception("Parsing failed", 1);
        }

        EmailSection::setMailData($data);
        EmailSection::setMailResource($mail_resource);

        $section_ids = mailparse_msg_get_structure($mail_resource);

        // Create objects for each section
        $parsed = array();
        foreach($section_ids as $section_id) {
            $this->sections[$section_id] = new EmailSection($section_id);
            if (!$this->top_section_id && $this->sections[$section_id]->hasHeader('from')) {
                $this->top_section_id = $section_id;
            }
        }

        if ($this->top_section_id === null) {
            throw new \Exception("No sender given", 1);
        }
    }

    /**
     * Gets body of email by piecing together sections
     */
    public function getBody()
    {
        return $this->getBodyRecursive();
    }

    /**
     * Go into nested email structure to retrieve body
     */
    private function getBodyRecursive($parent_id = '')
    {
        $section_ids = array_keys($this->sections);
        $children = array();
        foreach ($section_ids as $section_id) {
            if (preg_match("/^{$parent_id}\d*$/", $section_id)) {
                $children[] = $section_id;
            }
        }

        $has_multipart = false;
        $found_html = false;
        $partial_body = '';
        foreach ($children as $section_id) {
            $content_type = $this->sections[$section_id]->getContentType();
            if (preg_match("/^multipart\//", $content_type)) {
                $has_multipart = true;
                $partial_body = $this->getBodyRecursive($section_id . '.');
            } else {
                if ($content_type == 'text/html') {
                    $found_html = true;
                    $partial_body = $this->sections[$section_id]->getBody();
                }
                if (!$found_html || !$has_multipart) {
                    if ($content_type == 'text/plain') {
                        $partial_body .= '<p>' . $this->sections[$section_id]->getBody() . '</p>';
                    } else if (preg_match("/^image\//", $content_type)) {
                        if (!$this->sections[$section_id]->getHeader('content-id')) {
                            $this->sections[$section_id]->setHeader('content-id', $section_id);
                            $partial_body .= '<img src="cid:' . $section_id . '">';
                        }
                    }
                }
            }
        }
        return $partial_body;
    }

    /**
     * Get sender of the email from the 'From' header
     */
    public function getSender()
    {
        $sender = $this->sections[$this->top_section_id]->getHeader('from');
        if (!$sender) return null;

        $sender['relation'] = SpaceLink::RELATION_FROM;
        return $sender;
    }

    /**
     * Get subject of the email
     */
    public function getSubject()
    {
        return $this->sections[$this->top_section_id]->getHeader('subject') ?: '';
    }

    /**
     * Get in-reply-to of the email
     */
    public function getInReplyTo()
    {
        return $this->sections[$this->top_section_id]->getHeader('in-reply-to');
    }

    /**
     * Get the message-id trail of the email (references)
     */
    public function getReferences()
    {
        $references = $this->sections[$this->top_section_id]->getHeader('references');
        return $references ? explode(' ', $references) : array();
    }

    /**
     * Get message-id of the email
     */
    public function getMessageId()
    {
        return $this->sections[$this->top_section_id]->getHeader('message-id');
    }

    /**
     * Get message recipients from the To: and CC: fields
     */
    public function getRecipients()
    {
        $recipients = array();
        $cc = $this->sections[$this->top_section_id]->getHeader('cc');
        $to = $this->sections[$this->top_section_id]->getHeader('to');
        $sender = $this->getSender();

        if ($cc) {
            foreach ($cc as $recipient) {
                // Skip addresses from this domain, as well as the sender
                if (preg_match("/". Space::SITE_DOMAIN ."$/", $recipient['email'])
                    || $sender['email'] == $recipient['email']) continue;

                $recipient['relation'] = SpaceLink::RELATION_CC;
                $recipients[] = $recipient;
            }
        }

        if ($to) {
            foreach ($to as $recipient) {
                // Skip addresses from this domain, as well as the sender
                if (preg_match("/". Space::SITE_DOMAIN ."$/", $recipient['email'])
                    || $sender['email'] == $recipient['email']) continue;

                $recipient['relation'] = SpaceLink::RELATION_TO;
                $recipients[] = $recipient;
            }
        }

        return $recipients;
    }

    public function getImages()
    {
        return $this->getAttachments("/image\//");
    }

    /**
     * Get message recipients from the To: and CC: fields
     *
     * @param  string   $content_type_regex   A regex to narrow down results by content-type
     * @return array    An array of EmailSection objects representing attachments
     */
    public function getAttachments($content_type_regex = null)
    {
        $attachment = array();
        foreach ($this->sections as $section_id => $section) {
            if ($section->hasHeader('content-disposition')
                && (!$content_type_regex || preg_match($content_type_regex, $section->getContentType())))
            {
                $attachment[] = $section;
            }
        }

        return $attachment;
    }
}
