<?php

namespace TickTackk\ChangeContentOwner\XFMG\InlineMod;

class Media extends XFCP_Media
{
    /**
     * @return \XF\InlineMod\AbstractAction[]
     */
    public function getActions()
    {
        return $this->actions;
    }
}