<?php

namespace TickTackk\ChangeContentOwner\XF\InlineMod;

/**
 * Class Thread
 *
 * @package TickTackk\ChangeContentOwner
 */
class Thread extends XFCP_Thread
{
    /**
     * @return \XF\InlineMod\AbstractAction[]
     */
    public function getActions()
    {
        return $this->actions;
    }
}