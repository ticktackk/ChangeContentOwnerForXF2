<?php

namespace TickTackk\ChangeContentOwner\XF\Pub\Controller;

use TickTackk\ChangeContentOwner\ControllerPlugin\Content as ContentPlugin;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\View as ViewReply;

/**
 * Class Thread
 *
 * @package TickTackk\ChangeContentOwner\XF\Pub\Controller
 */
class Thread extends XFCP_Thread
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
        $thread = $this->assertViewableThread($parameterBag->thread_id);

        /** @var ContentPlugin $contentPlugin */
        $contentPlugin = $this->plugin('TickTackk\ChangeContentOwner:Content');
        return $contentPlugin->actionChangeOwner(
            $thread,
            'TickTackk\ChangeContentOwner\XF:Thread\OwnerChanger',
            'XF:Thread',
            'TickTackk\ChangeContentOwner\XF:Thread\ChangeOwner'
        );
    }
}