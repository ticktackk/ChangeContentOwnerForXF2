<?php

namespace TickTackk\ChangeContentOwner\XF\InlineMod\ProfilePost;

use TickTackk\ChangeContentOwner\InlineMod\AbstractOwnerChangerAction;

/**
 * Class ChangeOwner
 *
 * @package TickTackk\ChangeContentOwner\XF\InlineMod\ProfilePost
 */
class ChangeOwner extends AbstractOwnerChangerAction
{
    /**
     * @return string
     */
    protected function abstractServiceName(): string
    {
        return 'TickTackk\ChangeContentOwner\XF:ProfilePost\OwnerChanger';
    }

    /**
     * @return string
     */
    protected function getFormViewClass(): string
    {
        return 'TickTackk\ChangeContentOwner\XF:Public:InlineMod\ProfilePost\ChangeOwner';
    }
}