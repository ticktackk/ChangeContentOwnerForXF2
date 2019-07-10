<?php

namespace TickTackk\ChangeContentOwner\XF\Pub\Controller;

use TickTackk\ChangeContentOwner\ControllerPlugin\Content as ContentPlugin;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\View as ViewReply;

/**
 * Class ProfilePost
 *
 * @package TickTackk\ChangeContentOwner\XF\Pub\Controller
 */
class ProfilePost extends XFCP_ProfilePost
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
        $profilePost = $this->assertViewableProfilePost($parameterBag->profile_post_id);

        /** @var ContentPlugin $contentPlugin */
        $contentPlugin = $this->plugin('TickTackk\ChangeContentOwner:Content');
        return $contentPlugin->actionChangeOwner(
            $profilePost,
            'TickTackk\ChangeContentOwner\XF:ProfilePost\OwnerChanger',
            'XF:ProfilePost',
            'TickTackk\ChangeContentOwner\XF:ProfilePost\ChangeOwner'
        );
    }

    /**
     * @param ParameterBag $parameterBag
     *
     * @return RedirectReply|ViewReply
     * @throws ExceptionReply
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    public function actionCommentsChangeOwner(ParameterBag $parameterBag)
    {
        $comment = $this->assertViewableComment($parameterBag->profile_post_comment_id);
        $profilePost = $this->assertViewableProfilePost($comment->profile_post_id);

        /** @var ContentPlugin $contentPlugin */
        $contentPlugin = $this->plugin('TickTackk\ChangeContentOwner:Content');
        return $contentPlugin->actionChangeOwner(
            $comment,
            'TickTackk\ChangeContentOwner\XF:ProfilePostComment\OwnerChanger',
            'XF:ProfilePostComment',
            'TickTackk\ChangeContentOwner\XF:ProfilePost\Comments\ChangeOwner',
            'XF:ProfilePost'
        );
    }
}