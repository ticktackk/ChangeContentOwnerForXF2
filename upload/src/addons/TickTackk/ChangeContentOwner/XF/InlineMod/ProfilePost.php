<?php

namespace TickTackk\ChangeContentOwner\XF\InlineMod;

class ProfilePost extends XFCP_ProfilePost
{
    /**
     * @return \XF\InlineMod\AbstractAction[]
     */
    public function getActions()
    {
        return $this->actions;
    }
}