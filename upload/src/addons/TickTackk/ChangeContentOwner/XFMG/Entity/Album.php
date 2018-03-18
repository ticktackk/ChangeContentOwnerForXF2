<?php

namespace TickTackk\ChangeContentOwner\XFMG\Entity;

class Album extends XFCP_Album
{
    /**
     * @param null|string $error
     *
     * @return bool
     */
    public function canChangeOwner(/** @noinspection PhpUnusedParameterInspection */
        &$error = null)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        return $this->hasPermission('changeAlbumOwner');
    }
}