<?php

namespace TickTackk\ChangeContentOwner\XF\Entity;

class Post extends XFCP_Post
{
    public function canChangeAuthor(&$error = null)
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