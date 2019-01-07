<?php

namespace TickTackk\ChangeContentOwner\XF\Entity;

/**
 * Class Post
 *
 * @package TickTackk\ChangeContentOwner
 *
 * @property \TickTackk\ChangeContentOwner\XF\Entity\Thread Thread
 */
class Post extends XFCP_Post
{
    /**
     * @param null $error
     *
     * @return bool
     */
    public function canChangeAuthor(&$error = null)
    {
        if (!$this->Thread)
        {
            return false;
        }

        $thread = $this->Thread;
        if ($thread->first_post_id === $this->post_id)
        {
            return \XF::visitor()->hasNodePermission($thread->node_id, 'manageAnyThread');
        }

        return $thread->canChangePostAuthor($error);
    }
}