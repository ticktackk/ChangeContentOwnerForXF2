<?php

namespace TickTackk\ChangeContentOwner\XFMG\InlineMod\Comment;

use TickTackk\ChangeContentOwner\InlineMod\AbstractOwnerChangerAction;

/**
 * Class ChangeOwner
 *
 * @package TickTackk\ChangeContentOwner\XFMG\InlineMod\Comment
 */
class ChangeOwner extends AbstractOwnerChangerAction
{
    /**
     * @return string
     */
    protected function abstractServiceName(): string
    {
        return 'TickTackk\ChangeContentOwner\XFMG:Comment\OwnerChanger';
    }

    /**
     * @return string
     */
    protected function getFormViewClass(): string
    {
        return 'TickTackk\ChangeContentOwner\XFMG:Public:InlineMod\Comment\ChangeOwner';
    }
}