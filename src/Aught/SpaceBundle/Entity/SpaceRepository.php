<?php

namespace Aught\SpaceBundle\Entity;

use Doctrine\ORM\EntityRepository;

use Aught\SpaceBundle\Entity\Space;
use Aught\SpaceBundle\Entity\User;

/**
 * SpaceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SpaceRepository extends EntityRepository
{
    public function getUniqueId()
    {
        $characters = 'abcdefghijkmnpqrstuvwxyz23456789';
        do {
            $token = '';
            for ($i = 0; $i < Space::TOKEN_LENGTH; $i++) {
                $token .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
        } while ($this->findByToken($token));
        return $token;
    }

    public function findParticipantsBySpaceId($id)
    {
        return $this->getEntityManager()->getRepository('AughtSpaceBundle:User')
            ->createQueryBuilder('u')
            ->select(array('u', 'partial l.{id, relation}'))
            ->innerJoin('u.spaceLinks', 'l')
            ->innerJoin('l.space', 's')
            ->where('s.id = :space_id')
            ->setParameter('space_id', $id)
            ->getQuery()->getResult();
    }

    public function findRecipientsBySpaceId($id)
    {
        return $this->getEntityManager()->getRepository('AughtSpaceBundle:User')
            ->createQueryBuilder('u')
            ->select(array('u', 'partial l.{id, relation}'))
            ->innerJoin('u.spaceLinks', 'l')
            ->innerJoin('l.space', 's')
            ->where('s.id = :space_id')
            ->andWhere('l.relation <> :relation')
            ->setParameter('space_id', $id)
            ->setParameter('relation', SpaceLink::RELATION_FROM)
            ->getQuery()->getResult();
    }

    public function getAuthorBySpaceId($id)
    {
        $result = $this->getEntityManager()->getRepository('AughtSpaceBundle:SpaceLink')
            ->createQueryBuilder('l')
            ->select(array('u'))
            ->innerJoin('l.space', 's')
            ->innerJoin('l.author', 'u')
            ->where('l.relation = :relation')
            ->andWhere('s.id = :space_id')
            ->setParameter('space_id', $id)
            ->setParameter('relation', SpaceLink::RELATION_FROM)
            ->getQuery()->getResult();
        return $result ? $result[0]->getUser() : array();
    }
}
