<?php

namespace TickTackk\ChangeContentOwner\XF\Entity;

/**
 * Class Forum
 *
 * @package TickTackk\ChangeContentOwner
 */
class Forum extends XFCP_Forum
{
    /**
     * @param null $error
     *
     * @return bool
     */
    public function canChangeThreadAuthor(/** @noinspection PhpUnusedParameterInspection */ &$error = null)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        return $visitor->hasNodePermission($this->node_id, 'changeThreadAuthor');
    }

    /**
     * @param null $error
     *
     * @return bool
     */
    public function canChangePostAuthor(/** @noinspection PhpUnusedParameterInspection */ &$error = null)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        return $visitor->hasNodePermission($this->node_id, 'changePostAuthor');
    }
}