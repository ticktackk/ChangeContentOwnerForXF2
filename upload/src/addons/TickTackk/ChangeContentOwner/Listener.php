<?php

namespace TickTackk\ChangeContentOwner;

class Listener
{
    /**
     * @param \XF\InlineMod\AbstractHandler $handler
     * @param \XF\Pub\App $app
     * @param array $actions
     */
    public static function inlineModActions_thread(\XF\InlineMod\AbstractHandler $handler, \XF\Pub\App $app, array &$actions)
    {
        $actions['change_thread_author'] = $handler->getActionHandler('TickTackk\ChangeContentowner\XF:Thread\ChangeAuthor');
    }

    /**
     * @param \XF\InlineMod\AbstractHandler $handler
     * @param \XF\Pub\App $app
     * @param array $actions
     */
    public static function inlineModActions_post(\XF\InlineMod\AbstractHandler $handler, \XF\Pub\App $app, array &$actions)
    {
        $actions['change_post_author'] = $handler->getActionHandler('TickTackk\ChangeContentowner\XF:Post\ChangeAuthor');
    }

    /**
     * @param \XF\InlineMod\AbstractHandler $handler
     * @param \XF\Pub\App $app
     * @param array $actions
     */
    public static function inlineModActions_xfmg_album(\XF\InlineMod\AbstractHandler $handler, \XF\Pub\App $app, array &$actions)
    {
        $actions['change_album_owner'] = $handler->getActionHandler('TickTackk\ChangeContentowner\XFMG:Album\ChangeOwner');
    }

    /**
     * @param \XF\InlineMod\AbstractHandler $handler
     * @param \XF\Pub\App $app
     * @param array $actions
     */
    public static function inlineModActions_xfmg_media(\XF\InlineMod\AbstractHandler $handler, \XF\Pub\App $app, array &$actions)
    {
        $actions['change_media_author'] = $handler->getActionHandler('TickTackk\ChangeContentowner\XFMG:Album\ChangeAuthor');
    }

    /**
     * @param \XF\InlineMod\AbstractHandler $handler
     * @param \XF\Pub\App $app
     * @param array $actions
     */
    public static function inlineModActions_xfmg_comment(\XF\InlineMod\AbstractHandler $handler, \XF\Pub\App $app, array &$actions)
    {
        $actions['change_comment_author'] = $handler->getActionHandler('TickTackk\ChangeContentowner\XFMG:Comment\ChangeAuthor');
    }
}