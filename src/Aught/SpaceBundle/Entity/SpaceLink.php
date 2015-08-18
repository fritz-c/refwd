<?php

namespace Aught\SpaceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * SpaceLink
 *
 * @ORM\Table(name="space_link")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class SpaceLink
{
    const RELATION_TO   = 0;
    const RELATION_FROM = 1;
    const RELATION_CC   = 2;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="spaceLinks")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Space", inversedBy="spaceLinks")
     * @ORM\JoinColumn(name="space_id", referencedColumnName="id")
     */
    private $space;

    /**
     * @var integer
     *
     * @ORM\Column(name="relation", type="smallint")
     */
    private $relation = false;

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
     * Set relation
     *
     * @param integer $relation
     * @return SpaceLink
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * Get relation
     *
     * @return integer 
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return SpaceLink
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
     * @return SpaceLink
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
     * Set user
     *
     * @param \Aught\SpaceBundle\Entity\User $user
     * @return SpaceLink
     */
    public function setUser(\Aught\SpaceBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Aught\SpaceBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set space
     *
     * @param \Aught\SpaceBundle\Entity\Space $space
     * @return SpaceLink
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
