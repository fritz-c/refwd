<?php

namespace Aught\SpaceBundle\Services;

use Aught\SpaceBundle\Entity\Email;
use Aught\SpaceBundle\Entity\EmailSection;

use Aught\SpaceBundle\Entity\User;
use Aught\SpaceBundle\Entity\Space;
use Aught\SpaceBundle\Entity\Image;


/**
 * Email Utils
 *
 * Handles operations related to emails and parsing emails
 */
class EmailUtils
{
    /**
     * Email object
     * @var \Aught\SpaceBundle\Entity\Email
     */
    private $email;

    /**
     * User Repository
     * @var \Aught\SpaceBundle\Entity\UserRepository
     */
    private $user_rep;

    /**
     * Space repository
     * @var \Aught\SpaceBundle\Entity\SpaceRepository
     */
    private $space_rep;

    /**
     * Image utilities
     * @var \Aught\SpaceBundle\Services\ImageUtils
     */
    private $image_utils;

    /**
     * Storage utilities
     * @var \Aught\SpaceBundle\Services\StorageUtils
     */
    private $storage_utils;

    /**
     * Init utility class with image data
     *
     * @param  \Aught\SpaceBundle\Entity\Email  $email  Email object
     * @return \Aught\SpaceBundle\Services\EmailUtils  EmailUtils Object
     */
    public function initWithEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function __construct($user_rep, $space_rep, $image_utils, $storage_utils) {
        $this->user_rep      = $user_rep;
        $this->space_rep     = $space_rep;
        $this->image_utils   = $image_utils;
        $this->storage_utils = $storage_utils;
    }

    /**
     * Register the space object
     */
    public function registerSpace($em)
    {
        // Get SUBJECT
        $subject = $this->email->getSubject();
        $message_id = $this->email->getMessageId();
        $in_reply_to = $this->email->getInReplyTo();
        $references = $this->email->getReferences();

        if (!empty($in_reply_to)) {
            $existing_email = $this->space_rep->findByMessageId($in_reply_to);
            if (!empty($existing_email)) {
                // Message was a reply to previously uploaded message
                exit(0);
            }
            foreach ($references as $reference_id) {
                $existing_email = $this->space_rep->findByMessageId($reference_id);
                if (!empty($existing_email)) {
                    // Message was a reply to previously uploaded message
                    exit(0);
                }
            }
        }

        // Reject spam emails
        if (strpos($subject, '[SPAM]') === 0) {
            $this->sendSpamRejectedEmail();
            throw new \Exception('SPAM! Subject:' . $this->email->getSubject() . ' From:' . $this->email->getSender()['email'], 1);
        }

        // Create space
        $space = new Space();
        $space->setSubject($subject);
        $space->setBody($this->email->getBody());
        $space->setMessageId($message_id);
        $space->setToken($this->space_rep->getUniqueId());
        $em->persist($space);

        return $space;
    }

    /**
     * Register author of email as user
     */
    public function registerAuthor($em, $space)
    {
        $sender = $this->email->getSender();
        $author = $this->user_rep->upsertUserInSpace($sender['name'], $sender['email'], $sender['relation'], $space, $em);

        return $author;
    }

    /**
     * Register recipients of the email as users
     */
    public function registerRecipients($em, $space)
    {
        $recipient_data = $this->email->getRecipients();

        // Create addressees and their corresponding space links
        $recipients = array();
        foreach ($recipient_data as $rd) {
            $recipients[] = $this->user_rep->upsertUserInSpace($rd['name'], $rd['email'], $rd['relation'], $space, $em);
        }

        return $recipients;
    }

    /**
     * Register image objects from the email
     */
    public function registerImages($em, $space, $author)
    {
        $image_attachments = $this->email->getImages();

        $images = array();
        $image_filenames = array();
        foreach ($image_attachments as $image_attachment) {
            $image_type = $image_attachment->getContentType();
            $filename   = $image_attachment->getHeader('content-name') ?: $image_attachment->getHeader('disposition-filename');
            $line_count = $image_attachment->getHeader('line-count');
            $content_id = $image_attachment->getHeader('content-id') ?: $image_attachment->getSectionId();
            $binary     = $image_attachment->getBody();

            if (!isset($filename, $line_count, $content_id, $binary)) {
                // Skip invalid image
                continue;
            }

            // Resize image to a max dimension of 960 px
            $binary = $this->image_utils->initWithBlob($binary)->resize(Image::IMAGE_SIZE_MEDIUM);

            $image = new Image();
            $image->setContentType($image_type);
            $image->setSpace($space);
            $image->setAuthor($author);
            $image->setContentId($content_id);

            $image->setFilename($filename);

            // Check for uniqueness of the image filename
            $test_filename = $image->getFilename();
            $is_unique = false;
            $test_number = 1;
            while (!$is_unique) {
                if (isset($image_filenames[$test_filename])) {
                    $test_filename = preg_replace('/^(.*)(\..+?)$/', "$1_{$test_number}$2", $filename);
                    $image->setFilename($test_filename);

                    // Filename may be truncated if it gets too long, and we need to compare with the truncated version
                    $test_filename = $image->getFilename();
                    $test_number++;
                } else {
                    $is_unique = true;
                    $image->setFilename($test_filename);
                }
            }
            $image_filenames[$image->getFilename()] = true;

            $em->persist($image);

            // Upload image to S3
            $upload_result = $this->storage_utils->putWithType($image->getAwsKey(), $binary, $image_type);

            $images[] = $image;
            if (count($images) >= Image::IMAGE_UPLOAD_LIMIT) break;
        }

        return $images;
    }

    /**
     * Send an email to the author and recipients of an email, notifying them of the opening of the space
     */
    public function sendSpaceOpenedEmail($receivers, $space, $author)
    {
        $references = $this->email->getReferences();
        if ($references) {
            $references = $space->getMessageId() . ' ' . $references;
        } else {
            $references = $space->getMessageId();
        }

        $headers = 'From: add@re-fwd.com' . "\r\n"
            . 'In-Reply-To: ' . $space->getMessageId() . "\r\n"
            . 'References: ' . $references;

        if ($receivers) {
            foreach ($receivers as $receiver) {
                $receiver_addresses[] = $receiver->getAddressLineFormat();

                // $to = implode(', ', $receiver_addresses);
                $to      = $receiver->getAddressLineFormat();
                $subject = 'Re: ' . $space->getSubject();
                $body    = "{$author->getBestName()} included add@" . Space::SITE_DOMAIN . " in the email addressees, "
                         . 'creating a room to chat at '
                         . 'https://' . Space::SITE_DOMAIN . "/view/{$space->getToken()}"
                         . '?v=' . $receiver->getEmail();

                mail($to, $subject, $body, $headers);
            }
        }

        $to      = $author->getAddressLineFormat();
        $subject = 'Re: ' . $space->getSubject();
        $body    = 'See your post on https://' . Space::SITE_DOMAIN . "/view/{$space->getToken()}"
                 . '?v=' . $author->getEmail();

        if ($receivers) {
            $body .= "\n\nThis link was also sent to the following addresses:\n" . implode(', ', $receiver_addresses);
        }

        mail($to, $subject, $body, $headers);
    }

    /**
     * Send an email to the author and recipients of an email marked as spam, notifying them of such
     */
    public function sendSpamRejectedEmail()
    {
        $sender = $this->email->getSender();
        if (!empty($sender['name'])) {
            $to = "{$sender['name']} <{$sender['email']}>";
        } else {
            $to = $sender['email'];
        }

        $subject = 'Your submission to Re-Fwd was rejected by our spam filters';

        $body = 'We regret to inform you that your email was rejected as spam by our spam filters.' . "\n"
            . 'Please try again, avoiding uppercase characters in the subject line and other unusual formatting.';

        $references = $this->email->getReferences();
        if ($references) {
            $references = $this->email->getMessageId() . ' ' . $references;
        } else {
            $references = $this->email->getMessageId();
        }

        $headers = 'From: add@re-fwd.com' . "\r\n"
            . 'In-Reply-To: ' . $this->email->getMessageId() . "\r\n"
            . 'References: ' . $references;

        mail($to, $subject, $body, $headers);
    }
}
