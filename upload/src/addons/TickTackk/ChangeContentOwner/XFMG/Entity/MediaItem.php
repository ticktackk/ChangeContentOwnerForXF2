<?php

namespace TickTackk\ChangeContentOwner\XFMG\Entity;

/**
 * Class MediaItem
 *
 * @package TickTackk\ChangeContentOwner
 */
class MediaItem extends XFCP_MediaItem
{
    /**
     * @param null|string $error
     *
     * @return bool
     */
    public function canChangeAuthor(/** @noinspection PhpUnusedParameterInspection */
        &$error = null)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        return $this->hasPermission('changeMediaOwner');
    }
}