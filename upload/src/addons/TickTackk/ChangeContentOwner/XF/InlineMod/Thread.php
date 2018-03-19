<?php

namespace TickTackk\ChangeContentOwner\XF\InlineMod;

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