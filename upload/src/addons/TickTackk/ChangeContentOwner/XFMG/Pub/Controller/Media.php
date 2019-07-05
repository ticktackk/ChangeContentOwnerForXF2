<?php

namespace TickTackk\ChangeContentOwner\XFMG\Pub\Controller;

use TickTackk\ChangeContentOwner\ControllerPlugin\Content as ContentPlugin;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\View as ViewReply;

/**
 * Class Media
 *
 * @package TickTackk\ChangeContentOwner\XFMG\Pub\Controller
 */
class Media extends XFCP_Media
{
    /**
     * @param ParameterBag $parameterBag
     *
     * @return RedirectReply|ViewReply
     * @throws ExceptionReply
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    public function actionChangeOwner(ParameterBag $parameterBag)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $mediaItem = $this->assertViewableMediaItem($parameterBag->media_id);

        /** @var ContentPlugin $contentPlugin */
        $contentPlugin = $this->plugin('TickTackk\ChangeContentOwner:Content');
        return $contentPlugin->actionChangeOwner(
            $mediaItem,
            'TickTackk\ChangeContentOwner\XFMG:MediaItem\OwnerChanger',
            'XFMG:MediaItem',
            'TickTackk\ChangeContentOwner\XFMG:Media\ChangeOwner',
            'XFMG:Media'
        );
    }
}