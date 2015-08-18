<?php

namespace Aught\SpaceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Space
 *
 * @ORM\Table(name="space")
 * @ORM\Entity(repositoryClass="Aught\SpaceBundle\Entity\SpaceRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Space
{
    const TOKEN_LENGTH = 12;

    const SITE_DOMAIN = 're-fwd.com';

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
     * @ORM\Column(name="token", type="string", length=255, unique=true)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     * @Assert\NotBlank()
     */
    private $body;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="space")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="Relish", mappedBy="space")
     */
    private $relishes;

    /**
     * @ORM\OneToMany(targetEntity="Image", mappedBy="space")
     */
    private $images;

    /**
     * @ORM\OneToMany(targetEntity="SpaceLink", mappedBy="space")
     */
    private $spaceLinks;

    /**
     * @var integer
     *
     * @ORM\Column(name="view_count", type="integer", options={"default" = 0})
     */
    private $viewCount = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="message_id", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    private $messageId;

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


    public function __construct()
    {
        $this->comments   = new ArrayCollection();
        $this->relishes   = new ArrayCollection();
        $this->images     = new ArrayCollection();
        $this->spaceLinks = new ArrayCollection();
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
     * Set token
     *
     * @param string $token
     * @return Space
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return Space
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return Space
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set viewCount
     *
     * @param integer $viewCount
     * @return Space
     */
    public function setViewCount($viewCount)
    {
        $this->viewCount = $viewCount;

        return $this;
    }

    /**
     * Get viewCount
     *
     * @return integer 
     */
    public function getViewCount()
    {
        return $this->viewCount;
    }

    /**
     * Set messageId
     *
     * @param string $messageId
     * @return Space
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;

        return $this;
    }

    /**
     * Get messageId
     *
     * @return string 
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Space
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
     * @return Space
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
     * Add comments
     *
     * @param \Aught\SpaceBundle\Entity\Comment $comments
     * @return Space
     */
    public function addComment(\Aught\SpaceBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    /**
     * Remove comments
     *
     * @param \Aught\SpaceBundle\Entity\Comment $comments
     */
    public function removeComment(\Aught\SpaceBundle\Entity\Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add relishes
     *
     * @param \Aught\SpaceBundle\Entity\Relish $relishes
     * @return Space
     */
    public function addRelish(\Aught\SpaceBundle\Entity\Relish $relishes)
    {
        $this->relishes[] = $relishes;

        return $this;
    }

    /**
     * Remove relishes
     *
     * @param \Aught\SpaceBundle\Entity\Relish $relishes
     */
    public function removeRelish(\Aught\SpaceBundle\Entity\Relish $relishes)
    {
        $this->relishes->removeElement($relishes);
    }

    /**
     * Get relishes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRelishes()
    {
        return $this->relishes;
    }

    /**
     * Add images
     *
     * @param \Aught\SpaceBundle\Entity\Image $images
     * @return Space
     */
    public function addImage(\Aught\SpaceBundle\Entity\Image $images)
    {
        $this->images[] = $images;

        return $this;
    }

    /**
     * Remove images
     *
     * @param \Aught\SpaceBundle\Entity\Image $images
     */
    public function removeImage(\Aught\SpaceBundle\Entity\Image $images)
    {
        $this->images->removeElement($images);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Add spaceLinks
     *
     * @param \Aught\SpaceBundle\Entity\SpaceLink $spaceLinks
     * @return Space
     */
    public function addSpaceLink(\Aught\SpaceBundle\Entity\SpaceLink $spaceLinks)
    {
        $this->spaceLinks[] = $spaceLinks;

        return $this;
    }

    /**
     * Remove spaceLinks
     *
     * @param \Aught\SpaceBundle\Entity\SpaceLink $spaceLinks
     */
    public function removeSpaceLink(\Aught\SpaceBundle\Entity\SpaceLink $spaceLinks)
    {
        $this->spaceLinks->removeElement($spaceLinks);
    }

    /**
     * Get spaceLinks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSpaceLinks()
    {
        return $this->spaceLinks;
    }
}
