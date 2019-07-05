<?php

namespace TickTackk\ChangeContentOwner\XF\Pub\Controller;

use TickTackk\ChangeContentOwner\ControllerPlugin\Content as ContentPlugin;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View as ViewReply;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Mvc\Reply\Redirect as RedirectReply;

/**
 * Class Post
 *
 * @package TickTackk\ChangeContentOwner\XF\Pub\Controller
 */
class Post extends XFCP_Post
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
        $post = $this->assertViewablePost($parameterBag->post_id);

        /** @var ContentPlugin $contentPlugin */
        $contentPlugin = $this->plugin('TickTackk\ChangeContentOwner:Content');
        return $contentPlugin->actionChangeOwner(
            $post,
            'TickTackk\ChangeContentOwner\XF:Post\OwnerChanger',
            'XF:Post',
            'TickTackk\ChangeContentOwner\XF:Post\ChangeOwner'
        );
    }
}