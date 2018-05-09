<?php

namespace TickTackk\ChangeContentOwner\XFMG\InlineMod;

/**
 * Class Album
 *
 * @package TickTackk\ChangeContentOwner
 */
class Album extends XFCP_Album
{
    /**
     * @return \XF\InlineMod\AbstractAction[]
     */
    public function getActions()
    {
        return $this->actions;
    }
}