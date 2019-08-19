<?php

namespace TickTackk\ChangeContentOwner\XF\Service;

/**
 * Trait RebuildThreadUserPostCounterTrait
 *
 * @package TickTackk\ChangeContentOwner\XF\Service
 */
trait RebuildThreadUserPostCounterTrait
{
    /**
     * @param int $threadId
     *
     * @throws \XF\Db\Exception
     */
    public function rebuildThreadUserPostCounters(int $threadId)
    {
        /** @var \XF\Db\AbstractAdapter $db */
        $db = $this->db();

        $db->delete('xf_thread_user_post', 'thread_id = ?', $threadId);
        $db->query("
			INSERT INTO xf_thread_user_post (thread_id, user_id, post_count)
			SELECT thread_id, user_id, COUNT(*)
			FROM xf_post
			WHERE thread_id = ?
				AND message_state = 'visible'
				AND user_id > 0
			GROUP BY user_id
		", $threadId);
    }
}