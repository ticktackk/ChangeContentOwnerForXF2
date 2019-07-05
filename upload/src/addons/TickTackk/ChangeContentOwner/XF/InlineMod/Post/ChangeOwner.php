<?php

namespace TickTackk\ChangeContentOwner\XF\InlineMod\Post;

use TickTackk\ChangeContentOwner\InlineMod\AbstractOwnerChangerAction;

/**
 * Class ChangeOwner
 *
 * @package TickTackk\ChangeContentOwner\XF\InlineMod\Post
 */
class ChangeOwner extends AbstractOwnerChangerAction
{
    /**
     * @return string
     */
    protected function abstractServiceName(): string
    {
        return 'TickTackk\ChangeContentOwner\XF:Post\ChangeOwner';
    }

    /**
     * @return string
     */
    protected function getFormViewClass(): string
    {
        return 'TickTackk\ChangeContentOwner\XF:Public:InlineMod\Post\ChangeOwner';
    }
}