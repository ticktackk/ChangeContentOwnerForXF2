<?php

namespace TickTackk\ChangeContentOwner\XF\Entity;

/**
 * Class ProfilePost
 *
 * @package TickTackk\ChangeContentOwner
 */
class ProfilePost extends XFCP_ProfilePost
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

        return $visitor->hasPermission('profilePost', 'changeProfilePostAuthor');
    }
}