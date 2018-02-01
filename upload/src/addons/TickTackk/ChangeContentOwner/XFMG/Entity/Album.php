<?php

namespace TickTackk\ChangeContentOwner\XFMG\Entity;

class Album extends XFCP_Album
{
    public function canChangeOwner(&$error = null)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        return $this->hasPermission('changeAlbumOwner');
    }
}