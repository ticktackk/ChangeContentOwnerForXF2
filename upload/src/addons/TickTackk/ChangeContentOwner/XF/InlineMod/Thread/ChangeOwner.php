<?php

namespace TickTackk\ChangeContentOwner\XF\InlineMod\Thread;

use TickTackk\ChangeContentOwner\InlineMod\AbstractOwnerChangerAction;

/**
 * Class ChangeOwner
 *
 * @package TickTackk\ChangeContentOwner\XF\InlineMod\Thread
 */
class ChangeOwner extends AbstractOwnerChangerAction
{
    /**
     * @return string
     */
    protected function abstractServiceName(): string
    {
        return 'TickTackk\ChangeContentOwner\XF:Thread\OwnerChanger';
    }

    /**
     * @return string
     */
    protected function getFormViewClass(): string
    {
        return 'TickTackk\ChangeContentOwner\XF:Public:InlineMod\Thread\ChangeOwner';
    }
}