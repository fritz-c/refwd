<?php

namespace Aught\SpaceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="Aught\SpaceBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class User
{
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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    private $email;

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
     * @ORM\OneToMany(targetEntity="SpaceLink", mappedBy="user")
     */
    private $spaceLinks;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="author")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="Relish", mappedBy="author")
     */
    private $relishes;

    /**
     * @ORM\OneToMany(targetEntity="Image", mappedBy="author")
     */
    private $images;

    public function __construct()
    {
        $this->spaceLinks = new ArrayCollection();
        $this->comments   = new ArrayCollection();
        $this->relishes   = new ArrayCollection();
        $this->images     = new ArrayCollection();
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

    public function getAddressLineFormat()
    {
        return $this->getName() ? "{$this->getName()} <{$this->getEmail()}>" : $this->getEmail();
    }

    public function getAddressLineFormatMuddle()
    {
        return $this->getName() ? "{$this->getName()} <{$this->getMuddleMail()}>" : $this->getMuddleMail();
    }

    public function getBestName()
    {
        return $this->getName() ?: $this->getEmail();
    }

    public function getBestNameMuddle()
    {
        return $this->getName() ?: $this->getMuddleMail();
    }

    /**
     * Get the user email with the latter part changed to asterisks
     */
    public function getMuddleMail()
    {
        return preg_replace('/(?<=@).*\./', '****.', $this->getEmail());
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
     * Set name
     *
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return User
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
     * @return User
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
     * Add spaceLinks
     *
     * @param \Aught\SpaceBundle\Entity\SpaceLink $spaceLinks
     * @return User
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

    /**
     * Add comments
     *
     * @param \Aught\SpaceBundle\Entity\Comment $comments
     * @return User
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
     * @return User
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
     * @return User
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
}
