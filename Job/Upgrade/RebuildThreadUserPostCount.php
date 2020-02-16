<?php

namespace TickTackk\ChangeContentOwner\Job\Upgrade;

use XF\Job\AbstractRebuildJob;
use XF\App as BaseApp;
use XF\Mvc\Entity\Repository;
use XF\Repository\Thread as ThreadRepo;

/**
 * Class RebuildThreadUserPostCount
 *
 * @package TickTackk\ChangeContentOwner\Job\Upgrade
 */
class RebuildThreadUserPostCount extends AbstractRebuildJob
{
    /**
     * @param int $start
     * @param int $batch
     *
     * @return array
     */
    protected function getNextIds($start, $batch) : array
    {
        $db = $this->app->db();

        return $db->fetchAllColumn($db->limit(
            "
				SELECT thread_id
				FROM xf_thread
				WHERE thread_id > ?
				ORDER BY thread_id ASC
			", $batch
        ), $start);
    }

    /**
     * @param int $id
     */
    protected function rebuildById($id) : void
    {
        $threadRepo = $this->getThreadRepo();
        $threadRepo->rebuildThreadUserPostCounters($id);
    }

    /**
     * @return string
     */
    protected function getStatusType() : string
    {
        return '';
    }

    /**
     * @return BaseApp
     */
    protected function app() : BaseApp
    {
        return $this->app;
    }

    /**
     * @param string $identifier
     *
     * @return Repository
     */
    protected function repository(string $identifier) : Repository
    {
        return $this->app()->repository($identifier);
    }

    /**
     * @return Repository|ThreadRepo
     */
    public function getThreadRepo() : ThreadRepo
    {
        return $this->repository('XF:Thread');
    }
}