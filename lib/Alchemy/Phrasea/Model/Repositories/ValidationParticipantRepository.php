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

use Alchemy\Phrasea\Model\Entities\ValidationParticipant;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class ValidationParticipantRepository extends EntityRepository
{

    /**
     * Retrieve all not reminded participants where the validation has not expired
     *
     * @param $timeLeftPercent float        Percent of the time left before the validation expires.
     * @param $today DateTime               fake "today" to allow to get past/future events
     *                                      (used by SendValidationRemindersCommand.php to debug with --dry)
     * @return ValidationParticipant[]
     */
    public function findNotConfirmedAndNotRemindedParticipantsByTimeLeftPercent($timeLeftPercent, DateTime $today=null)
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata('Alchemy\Phrasea\Model\Entities\ValidationParticipant', 'p');
        $selectClause = $rsm->generateSelectClause();

        $sql = '
            SELECT ' . $selectClause . '
            FROM ValidationParticipants p
            INNER JOIN ValidationSessions s on p.validation_session_id = s.id
            INNER JOIN Baskets b on b.id = s.basket_id
            WHERE p.is_confirmed = 0
            AND p.reminded IS NULL
            AND s.expires > '. ($today===null ? 'CURRENT_TIMESTAMP()' : ':today') . '
            AND DATE_SUB(s.expires, INTERVAL FLOOR((TO_SECONDS(s.expires) -  TO_SECONDS(s.created)) * :percent) SECOND) <= '. ($today===null ? 'CURRENT_TIMESTAMP()' : ':today')
        ;

        $q = $this->_em->createNativeQuery($sql, $rsm);
        $q->setParameter('percent', (float)($timeLeftPercent/100));

        if($today !== null) {
            $q->setParameter('today', $today, Type::DATETIME);
        }

        return $q->getResult();
    }
}
