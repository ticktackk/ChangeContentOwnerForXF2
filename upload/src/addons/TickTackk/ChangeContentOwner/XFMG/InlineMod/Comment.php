<?php

namespace TickTackk\ChangeContentOwner\XFMG\InlineMod;

class Comment extends XFCP_Comment
{
    /**
     * @return \XF\InlineMod\AbstractAction[]
     */
    public function getActions()
    {
        return $this->actions;
    }
}