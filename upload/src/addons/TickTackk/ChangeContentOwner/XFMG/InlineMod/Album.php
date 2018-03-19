<?php

namespace TickTackk\ChangeContentOwner\XFMG\InlineMod;

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