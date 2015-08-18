<?php

namespace Aught\SpaceBundle\Entity;

/**
 * EmailSection (not stored in database)
 *
 */
class EmailSection
{
    private static $mail_resource;
    private static $mail_data;

    private $section_id;
    private $headers;
    private $body;

    public static function setMailData($mail_data)
    {
        if (!isset(self::$mail_data)) {
            self::$mail_data = $mail_data;
        }
    }

    public static function setMailResource($mail_resource)
    {
        if (!isset(self::$mail_resource)) {
            self::$mail_resource = $mail_resource;
        }
    }

    public function __construct($section_id)
    {
        if (!isset(self::$mail_resource, self::$mail_data)) {
            throw new \Exception("Must initialize mail resource and data before creating EmailSection object", 1);
        }

        $this->section_id = $section_id;

        // get a handle on the message resource for a subsection
        $section_handle = mailparse_msg_get_part(self::$mail_resource, $this->section_id);
        // get content-type, encoding and header information for the section
        $headers = mailparse_msg_get_part_data($section_handle);
        $this->setHeaders($headers);
    }

    /**
     * Set section headers
     */
    private function setHeaders($headers)
    {
        // Unset the unparsed headers, or flatten them if they belong to the top-level section
        if (isset($headers['headers']['from'])) {
            $bighead = $headers['headers'];
            $is_top = true;
            unset($headers['headers']);

            // Flatten the headers into a single level array, merging the parsed headers in preferentially
            $headers = array_merge($bighead, $headers);
        } else {
            $is_top = false;
            unset($headers['headers']);
        }

        // Decode possibly encoded fields
        $possibly_encoded = array(
            'content-name',
            'disposition-filename',
            'from',
            'subject',
            'to',
            'cc',
        );
        foreach ($possibly_encoded as $key) {
            if (isset($headers[$key])) {
                $headers[$key] = $this->mimeDecode($headers[$key]);
            }
        }

        // In the top-level header, parse out names and emails
        if ($is_top) {
            if (isset($headers['from'])) {
                $from = $this->parseAddresses($headers['from']);
                $headers['from'] = reset($from);
            }

            if (isset($headers['to'])) {
                $headers['to'] = $this->parseAddresses($headers['to']);
            }

            if (isset($headers['cc'])) {
                $headers['cc'] = $this->parseAddresses($headers['cc']);
            }
        }

        $this->headers = $headers;
    }

    /**
     * Get header value corresponding to key
     */
    public function getBody()
    {
        if ($this->body) {
            return $this->body;
        }

        // get the data in this part
        $sec = mailparse_msg_get_part(self::$mail_resource, $this->section_id);
        ob_start();
        // extract the part from the message
        mailparse_msg_extract_part($sec, self::$mail_data);
        $this->body = ob_get_contents();
        ob_end_clean();

        return $this->body;
    }

    /**
     * Get content type header
     */
    public function getContentType()
    {
        return $this->getHeader('content-type');
    }

    /**
     * Get section id
     */
    public function getSectionId()
    {
        return $this->section_id;
    }

    /**
     * Get header value corresponding to key
     */
    public function getHeader($key)
    {
        return isset($this->headers[$key]) ? $this->headers[$key] : null;
    }

    /**
     * Check for availability of header
     */
    public function hasHeader($key)
    {
        return isset($this->headers[$key]);
    }

    /**
     * Set header value for the specified key
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /**
     * Mime decode a string
     */
    private function mimeDecode($string)
    {
        return iconv_mime_decode($string, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, "utf-8");
    }

    /**
     * Break down an address list into names and emails
     */
    private function parseAddresses($string)
    {
        $parsed = mailparse_rfc822_parse_addresses($string);
        foreach ($parsed as $p) {
            $address_set['email'] = $p['address'];
            $address_set['name'] = $p['display'] !== $p['address'] ? $p['display'] : null;
            $address_sets[] = $address_set;
        }

        return $address_sets;
    }
}
