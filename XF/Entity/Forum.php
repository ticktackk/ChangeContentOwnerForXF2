<?php

namespace TickTackk\ChangeContentOwner\XF\Entity;

use XF\Entity\User as UserEntity;

/**
 * Class Forum
 *
 * @package TickTackk\ChangeContentOwner
 */
class Forum extends XFCP_Forum
{
    /**
     * @param UserEntity|null $newOwner
     * @param null            $error
     *
     * @return bool
     */
    public function canChangeThreadOwner(/** @noinspection PhpUnusedParameterInspection */UserEntity $newOwner = null, &$error = null) : bool
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        return $visitor->hasNodePermission($this->node_id, 'changeThreadOwner');
    }

    /**
     * @param null $error
     *
     * @return bool
     */
    public function canChangeThreadDate(/** @noinspection PhpUnusedParameterInspection */&$error = null) : bool
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        return $visitor->hasNodePermission($this->node_id, 'changeThreadDate');
    }

    /**
     * @param UserEntity|null $newOwner
     * @param null            $error
     *
     * @return bool
     */
    public function canChangePostOwner(/** @noinspection PhpUnusedParameterInspection */UserEntity $newOwner = null, &$error = null) : bool
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        return $visitor->hasNodePermission($this->node_id, 'changePostOwner');
    }

    /**
     * @param null $error
     *
     * @return bool
     */
    public function canChangePostDate(/** @noinspection PhpUnusedParameterInspection */&$error = null) : bool
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        return $visitor->hasNodePermission($this->node_id, 'changePostDate');
    }
}