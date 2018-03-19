<?php

namespace TickTackk\ChangeContentOwner\XF\InlineMod;

class Post extends XFCP_Post
{
    /**
     * @return \XF\InlineMod\AbstractAction[]
     */
    public function getActions()
    {
        return $this->actions;
    }
}