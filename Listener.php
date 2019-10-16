<?php

namespace TickTackk\ChangeContentOwner;

use XF\InlineMod\AbstractHandler as InlineModAbstractHandler;
use XF\App as XFApp;

/**
 * Class Listener
 *
 * @package TickTackk\ChangeContentOwner
 */
class Listener
{
    /**
     * @param InlineModAbstractHandler $abstractHandler
     * @param XFApp                    $app
     * @param array                    $actions
     */
    public static function inlineModActions(InlineModAbstractHandler $abstractHandler, XFApp $app, array &$actions) : void
    {
        switch ($abstractHandler->getContentType())
        {
            case 'thread':
                $actions['change_owner'] = $abstractHandler->getActionHandler('TickTackk\ChangeContentOwner\XF:Thread\ChangeOwner');
                break;

            case 'post':
                $actions['change_owner'] = $abstractHandler->getActionHandler('TickTackk\ChangeContentOwner\XF:Post\ChangeOwner');
                break;

            case 'profile_post':
                $actions['change_owner'] = $abstractHandler->getActionHandler('TickTackk\ChangeContentOwner\XF:ProfilePost\ChangeOwner');
                break;

            case 'xfmg_album':
                $actions['change_owner'] = $abstractHandler->getActionHandler('TickTackk\ChangeContentOwner\XFMG:Album\ChangeOwner');
                break;

            case 'xfmg_media':
                $actions['change_owner'] = $abstractHandler->getActionHandler('TickTackk\ChangeContentOwner\XFMG:Media\ChangeOwner');
                break;

            case 'xfmg_comment':
                $actions['change_owner'] = $abstractHandler->getActionHandler('TickTackk\ChangeContentOwner\XFMG:Comment\ChangeOwner');
                break;
        }
    }
}