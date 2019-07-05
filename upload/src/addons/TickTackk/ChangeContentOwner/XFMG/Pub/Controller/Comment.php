<?php

namespace TickTackk\ChangeContentOwner\XFMG\Pub\Controller;

use TickTackk\ChangeContentOwner\ControllerPlugin\Content as ContentPlugin;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\View as ViewReply;

/**
 * Class Comment
 *
 * @package TickTackk\ChangeContentOwner\XFMG\Pub\Controller
 */
class Comment extends XFCP_Comment
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
        $comment = $this->assertViewableComment($parameterBag->comment_id);

        /** @var ContentPlugin $contentPlugin */
        $contentPlugin = $this->plugin('TickTackk\ChangeContentOwner:Content');
        return $contentPlugin->actionChangeOwner(
            $comment,
            'TickTackk\ChangeContentOwner\XFMG:Comment\OwnerChanger',
            'XFMG:Comment',
            'TickTackk\ChangeContentOwner\XFMG:Comment\ChangeOwner'
        );
    }
}