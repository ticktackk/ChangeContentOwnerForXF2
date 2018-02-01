<?php

namespace TickTackk\ChangeContentOwner\XF\Entity;

class Thread extends XFCP_Thread
{
    public function canChangeAuthor(&$error = null)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        $nodeId = $this->node_id;

        return $visitor->hasNodePermission($nodeId, 'changeThreadAuthor');
    }
}