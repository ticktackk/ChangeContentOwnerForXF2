<?php

namespace TickTackk\ChangeContentOwner\XFMG\InlineMod\Album;

use TickTackk\ChangeContentOwner\InlineMod\AbstractOwnerChangerAction;

/**
 * Class ChangeOwner
 *
 * @package TickTackk\ChangeContentOwner\XFMG\InlineMod\Album
 */
class ChangeOwner extends AbstractOwnerChangerAction
{
    /**
     * @return string
     */
    protected function abstractServiceName(): string
    {
        return 'TickTackk\ChangeContentOwner\XFMG:Album\OwnerChanger';
    }

    /**
     * @return string
     */
    protected function getFormViewClass(): string
    {
        return 'TickTackk\ChangeContentOwner\XFMG:Public:InlineMod\Album\ChangeOwner';
    }
}