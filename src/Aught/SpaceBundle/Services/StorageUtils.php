<?php

namespace Aught\SpaceBundle\Services;

use Aws\S3\S3Client;
use Aws\S3\Enum\Group;
use Aws\S3\Model\AcpBuilder;

class StorageUtils
{
    private static $client = null;

    private static $acp = null;

    private $bucket_name = null;

    public function __construct($aws_key, $aws_secret, $bucket_name, $acp_owner) {
        $this->bucket_name = $bucket_name;
        if (self::$client == null) {
            try {
                // Instantiate the S3 client with AWS credentials
                self::$client = S3Client::factory(array(
                    'key'    => $aws_key,
                    'secret' => $aws_secret,
                ));

                // Prepare the access control policy (only allow direct viewing by authenticated AWS users)
                self::$acp = AcpBuilder::newInstance()
                    ->setOwner($acp_owner)
                    ->addGrantForGroup('READ', Group::AUTHENTICATED_USERS)
                    ->build();
            } catch (\Exception $e) {
                throw new \Exception('Error connecting to S3: ' . $e->getMessage(), 1);
            }
        }
    }

    /**
     * Upload an object to S3
     */
    public function put($key, $body, $options = array())
    {
        try {
            $params = array(
                'Bucket' => $this->bucket_name,
                'Key'    => $key,
                'Body'   => $body,
                'ACP'    => self::$acp
            );
            $params = array_merge($params, $options);

            $result = self::$client->putObject($params);
        } catch (\Exception $e) {
            throw new \Exception("Error putting object (key: '{$key}') into S3: " . $e->getMessage(), 1);
        }

        return $result;
    }

    /**
     * Upload image to S3
     */
    public function putWithType($key, $body, $content_type, $options = array())
    {
        $options = array_merge(array('ContentType' => $content_type), $options);
        return $this->put($key, $body, $options);
    }

    /**
     * Get object from S3
     */
    public function get($key, $options = array())
    {
        try {
            $params = array(
                'Bucket' => $this->bucket_name,
                'Key'    => $key,
            );
            $params = array_merge($params, $options);

            $result = self::$client->getObject($params);
        } catch (\Exception $e) {
            throw new \Exception("Error getting object (key: '{$key}') from S3: " . $e->getMessage(), 1);
        }

        return $result['Body'];
    }

    /**
     * Get signed url for s3 object
     */
    public function getUrl($key, $duration = "+10 minutes", $is_https = true, $bucket = null)
    {
        if (!$bucket) $bucket = $this->bucket_name;

        try {
            $signed_url = self::$client->getObjectUrl($bucket, $key, $duration, array('https' => $is_https));
        } catch (\Exception $e) {
            throw new \Exception("Error getting URL (key: '{$key}') from S3: " . $e->getMessage(), 1);
        }

        return $signed_url;
    }
}
