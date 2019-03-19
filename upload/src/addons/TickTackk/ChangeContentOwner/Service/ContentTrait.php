<?php

namespace TickTackk\ChangeContentOwner\Service;

use XF\Entity\User;
use XF\Mvc\Entity\Entity;

/**
 * Trait ContentTrait
 *
 * @package TickTackk\ChangeContentOwner\Service
 */
trait ContentTrait
{
    /**
     * @param Entity $content
     * @param User   $oldUser
     * @param User   $newUser
     */
    public function updateNewsFeed(Entity $content, User $oldUser, User $newUser)
    {
        /** @var \XF\Db\AbstractAdapter $db */
        $db = $this->db();

        $db->update('xf_news_feed', [
            'user_id' => $newUser->user_id,
            'username' => $newUser->username
        ], 'content_type = ? AND content_id = ? AND user_id = ? AND username = ?', [
            $content->getEntityContentType(), $content->getEntityId(), $oldUser->user_id, $oldUser->username
        ]);
    }
}