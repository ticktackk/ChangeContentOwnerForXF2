<?php

namespace TickTackk\ChangeContentOwner\XF\Entity;

class Post extends XFCP_Post
{
    /**
     * @param null|string $error
     *
     * @return bool
     */
    public function canChangeAuthor(/** @noinspection PhpUnusedParameterInspection */
        &$error = null)
    {
        $thread = $this->Thread;
        $visitor = \XF::visitor();
        if (!$visitor->user_id || !$thread)
        {
            return false;
        }

        $nodeId = $thread->node_id;

        return ($visitor->hasNodePermission($nodeId, 'changePostAuthor') && !($thread->first_post_id == $this->post_id));
    }
}