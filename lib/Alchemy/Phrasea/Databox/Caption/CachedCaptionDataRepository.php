<?php
/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2016 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Databox\Caption;

use Alchemy\Phrasea\Cache\MultiAdapter;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\MultiGetCache;
use Doctrine\Common\Cache\MultiPutCache;

class CachedCaptionDataRepository implements CaptionDataRepository
{
    /**
     * @var CaptionDataRepository
     */
    private $decorated;
    /**
     * @var Cache|MultiGetCache|MultiPutCache
     */
    private $cache;
    /**
     * @var string
     */
    private $baseKey;

    /**
     * @var int
     */
    private $lifeTime = 0;

    /**
     * CachedCaptionDataRepository constructor.
     * @param CaptionDataRepository $decorated
     * @param Cache $cache
     * @param string $baseKey
     */
    public function __construct(CaptionDataRepository $decorated, Cache $cache, $baseKey)
    {
        $this->decorated = $decorated;
        $this->cache = $cache instanceof MultiGetCache && $cache instanceof MultiPutCache
            ? $cache
            : new MultiAdapter($cache);
        $this->baseKey = $baseKey;
    }

    /**
     * @return int
     */
    public function getLifeTime()
    {
        return $this->lifeTime;
    }

    /**
     * @param int $lifeTime
     */
    public function setLifeTime($lifeTime)
    {
        $this->lifeTime = (int)$lifeTime;
    }

    /**
     * @param array $recordIds
     * @return \array[]
     */
    public function findByRecordIds(array $recordIds)
    {
        $keys = $this->computeKeys($recordIds);

        $data = $this->cache->fetchMultiple($keys);

        if (count($data) === count($keys)) {
            return $data;
        }

        $data = $this->decorated->findByRecordIds($recordIds);

        $this->cache->saveMultiple(array_combine(array_values($keys), array_values($data)));

        return $data;
    }

    /**
     * @param int[] $recordIds
     * @return string[]
     */
    private function computeKeys(array $recordIds)
    {
        return array_map(function ($recordId) {
            return $this->baseKey . 'caption' . json_encode([(int)$recordId]);
        }, $recordIds);
    }
}
