<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2014 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Model\Repositories;

use Doctrine\ORM\EntityRepository;
use Alchemy\Phrasea\Model\Entities\User;

/**
 * User
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository
{
    /**
     * Finds admins.
     *
     * @return array
     */
    public function findAdmins()
    {
        $qb = $this->createQueryBuilder('u');

        $qb->where($qb->expr()->eq('u.admin', $qb->expr()->literal(true)))
            ->andWhere($qb->expr()->isNull('u.modelOf'))
            ->andWhere($qb->expr()->eq('u.deleted', $qb->expr()->literal(false)));

        return $qb->getQuery()->getResult();
    }

    /**
     * Finds a user by login.
     *
     * @param string $login
     *
     * @return null|User
     */
    public function findByLogin($login)
    {
        return $this->findOneBy(['login' => $login]);
    }

    /**
     * Finds a user by email.
     *
     * @param string $email
     *
     * @return null|User
     */
    public function findByEmail($email)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->where($qb->expr()->eq('u.email', $qb->expr()->literal($email)))
            ->andWhere($qb->expr()->isNotNull('u.email'))
            ->andWhere($qb->expr()->eq('u.deleted', $qb->expr()->literal(false)));

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Finds a user that is not deleted, not a model and not a guest.
     *
     * @param $login
     *
     * @return null|User
     */
    public function findRealUserByLogin($login)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->where($qb->expr()->eq('u.login', $qb->expr()->literal($login)))
            ->andWhere($qb->expr()->isNotNull('u.email'))
            ->andWhere($qb->expr()->eq('u.modelOf', $qb->expr()->literal('0')))
            ->andWhere($qb->expr()->eq('u.guest', $qb->expr()->literal('0')))
            ->andWhere($qb->expr()->eq('u.deleted', $qb->expr()->literal(false)));

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Finds model of given user.
     *
     * @param User $user
     *
     * @return array
     */
    public function findModelOf(User $user)
    {
        return $this->findBy(['modelOf' => $user->getId()]);
    }
}
