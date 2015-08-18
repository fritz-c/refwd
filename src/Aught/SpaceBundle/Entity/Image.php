<?php

namespace Aught\SpaceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Image
 *
 * @ORM\Table(name="image")
 * @ORM\Entity(repositoryClass="Aught\SpaceBundle\Entity\ImageRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Image
{
    const IMAGE_UPLOAD_LIMIT = 20;

    const IMAGE_SIZE_LARGE  = 960;
    const IMAGE_SIZE_MEDIUM = 640;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="content_id", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $contentId;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255)
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(name="content_type", type="string", length=255)
     */
    private $contentType;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="images")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="Space", inversedBy="images")
     * @ORM\JoinColumn(name="space_id", referencedColumnName="id")
     */
    private $space;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * Set filename
     *
     * @param string $filename
     * @return Image
     */
    public function setFilename($filename)
    {
        // If the filename exceeds the varchar size, save just the last 255 characters
        if (mb_strlen($filename, 'utf-8') > 255) {
            $filename = mb_substr($filename, -255, null, 'utf-8');
        }

        $this->filename = $filename;

        return $this;
    }

    /**
     * Get Amazon S3 storage token
     *
     * @return string
     */
    public function getAwsKey()
    {
        return $this->getSpace()->getId() . '_' . $this->getFilename();
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTime();
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set contentId
     *
     * @param string $contentId
     * @return Image
     */
    public function setContentId($contentId)
    {
        $this->contentId = $contentId;

        return $this;
    }

    /**
     * Get contentId
     *
     * @return string 
     */
    public function getContentId()
    {
        return $this->contentId;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set contentType
     *
     * @param string $contentType
     * @return Image
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Get contentType
     *
     * @return string 
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Image
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Image
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set author
     *
     * @param \Aught\SpaceBundle\Entity\User $author
     * @return Image
     */
    public function setAuthor(\Aught\SpaceBundle\Entity\User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \Aught\SpaceBundle\Entity\User 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set space
     *
     * @param \Aught\SpaceBundle\Entity\Space $space
     * @return Image
     */
    public function setSpace(\Aught\SpaceBundle\Entity\Space $space = null)
    {
        $this->space = $space;

        return $this;
    }

    /**
     * Get space
     *
     * @return \Aught\SpaceBundle\Entity\Space 
     */
    public function getSpace()
    {
        return $this->space;
    }
}
