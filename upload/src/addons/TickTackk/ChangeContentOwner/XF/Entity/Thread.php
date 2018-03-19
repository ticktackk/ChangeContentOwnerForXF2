<?php

namespace TickTackk\ChangeContentOwner\XF\Entity;

use XF\Mvc\Entity\Structure;

class Thread extends XFCP_Thread
{
    /**
     * @param null|string $error
     *
     * @return bool
     */
    public function canChangeAuthor(/** @noinspection PhpUnusedParameterInspection */
        &$error = null)
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