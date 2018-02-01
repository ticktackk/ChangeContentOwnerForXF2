<?php

namespace TickTackk\ChangeContentOwner\XFMG\Entity;

class MediaItem extends XFCP_MediaItem
{
    public function canChangeAuthor(&$error = null)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        return $this->hasPermission('changeCommentAuthor');
    }
}