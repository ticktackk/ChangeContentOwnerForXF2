<?php

namespace TickTackk\ChangeContentOwner;

use XF\InlineMod\AbstractHandler as InlineMod_AbstractHandler;
use XF\Pub\App;

/**
 * Class Listener
 *
 * @package TickTackk\ChangeContentOwner
 */
class Listener
{
    /**
     * @param InlineMod_AbstractHandler $handler
     * @param \XF\Pub\App               $app
     * @param array                     $actions
     */
    public static function inlineModActions_thread(InlineMod_AbstractHandler $handler, /** @noinspection PhpUnusedParameterInspection */
                                                   App $app, array &$actions)
    {
        $actions['change_thread_author'] = $handler->getActionHandler('TickTackk\ChangeContentOwner\XF:Thread\ChangeAuthor');
    }

    /**
     * @param InlineMod_AbstractHandler $handler
     * @param \XF\Pub\App               $app
     * @param array                     $actions
     */
    public static function inlineModActions_post(InlineMod_AbstractHandler $handler, /** @noinspection PhpUnusedParameterInspection */
                                                 App $app, array &$actions)
    {
        $actions['change_post_author'] = $handler->getActionHandler('TickTackk\ChangeContentOwner\XF:Post\ChangeAuthor');
    }

    /**
     * @param InlineMod_AbstractHandler $handler
     * @param \XF\Pub\App               $app
     * @param array                     $actions
     */
    public static function inlineModActions_profile_post(InlineMod_AbstractHandler $handler, /** @noinspection PhpUnusedParameterInspection */
                                                         App $app, array &$actions)
    {
        $actions['change_profile_post_author'] = $handler->getActionHandler('TickTackk\ChangeContentOwner\XF:ProfilePost\ChangeAuthor');
    }

    /**
     * @param InlineMod_AbstractHandler $handler
     * @param \XF\Pub\App               $app
     * @param array                     $actions
     */
    public static function inlineModActions_xfmg_album(InlineMod_AbstractHandler $handler, /** @noinspection PhpUnusedParameterInspection */
                                                       App $app, array &$actions)
    {
        $actions['change_album_owner'] = $handler->getActionHandler('TickTackk\ChangeContentOwner\XFMG:Album\ChangeOwner');
    }

    /**
     * @param InlineMod_AbstractHandler $handler
     * @param \XF\Pub\App               $app
     * @param array                     $actions
     */
    public static function inlineModActions_xfmg_media(InlineMod_AbstractHandler $handler, /** @noinspection PhpUnusedParameterInspection */
                                                       App $app, array &$actions)
    {
        $actions['change_media_author'] = $handler->getActionHandler('TickTackk\ChangeContentOwner\XFMG:Media\ChangeAuthor');
    }

    /**
     * @param InlineMod_AbstractHandler $handler
     * @param \XF\Pub\App               $app
     * @param array                     $actions
     */
    public static function inlineModActions_xfmg_comment(InlineMod_AbstractHandler $handler, /** @noinspection PhpUnusedParameterInspection */
                                                         App $app, array &$actions)
    {
        $actions['change_comment_author'] = $handler->getActionHandler('TickTackk\ChangeContentOwner\XFMG:Comment\ChangeAuthor');
    }
}