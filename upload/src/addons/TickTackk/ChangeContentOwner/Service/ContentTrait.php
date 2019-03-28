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
     * @param Entity              $content
     * @param User|array|int|null $oldUser
     * @param User                $newUser
     */
    public function updateNewsFeed(Entity $content, $oldUser, User $newUser)
    {
        $oldUserId = null;
        $oldUsername = null;
        if ($oldUser instanceof User)
        {
            $oldUserId = $oldUser->user_id;
            $oldUsername = $oldUser->username;
        }
        else if (is_int($oldUser))
        {
            $oldUserId = $oldUser;
        }
        else if (is_array($oldUser))
        {
            $oldUserId = $oldUser['user_id'];
        }

        /** @var \XF\Db\AbstractAdapter $db */
        $db = $this->db();

        if ($oldUserId && $oldUsername)
        {
            $db->update('xf_news_feed', [
                'user_id' => $newUser->user_id,
                'username' => $newUser->username
            ], 'content_type = ? AND content_id = ? AND user_id = ? AND username = ?', [
                $content->getEntityContentType(), $content->getEntityId(), $oldUserId, $oldUsername
            ]);
        }
        else if ($oldUserId)
        {
            $db->update('xf_news_feed', [
                'user_id' => $newUser->user_id,
                'username' => $newUser->username
            ], 'content_type = ? AND content_id = ? AND user_id = ?', [
                $content->getEntityContentType(), $content->getEntityId(), $oldUserId
            ]);
        }
        else
        {
            throw new \LogicException('Invalid old user provided for updating newsfeed.');
        }
    }
}