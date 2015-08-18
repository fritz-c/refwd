<?php

namespace Aught\SpaceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Relish
 *
 * @ORM\Table(
 *     name="relish",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="space_author_unique", columns={"space_id", "author_id"})}
 * )
 * @ORM\Entity(repositoryClass="Aught\SpaceBundle\Entity\RelishRepository")
 * @UniqueEntity(
 *     fields={"author","space"},
 *     message="This author has already relished this space"
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class Relish
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="relishes")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="Space", inversedBy="relishes")
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Relish
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
     * @return Relish
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
     * @return Relish
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
     * @return Relish
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
