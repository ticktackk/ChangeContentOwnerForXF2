<?php

namespace TickTackk\ChangeContentOwner\XFMG\Pub\Controller;

use TickTackk\ChangeContentOwner\ControllerPlugin\Content as ContentPlugin;
use TickTackk\ChangeContentOwner\Pub\Controller\ContentTrait;
use TickTackk\ChangeContentOwner\Service\Content\EditorInterface as EditorSvcInterface;
use XF\ControllerPlugin\AbstractPlugin;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Error as ErrorReply;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\View as ViewReply;
use XFMG\Entity\Comment as CommentEntity;
use XFMG\Service\Comment\Editor as CommentEditorSvc;

/**
 * Class Comment
 *
 * @package TickTackk\ChangeContentOwner\XFMG\Pub\Controller
 */
class Comment extends XFCP_Comment
{
    use ContentTrait;

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

        return $this->getChangeContentOwnerPlugin()->actionChangeOwner(
            $comment,
            'TickTackk\ChangeContentOwner\XFMG:Comment\OwnerChanger',
            'XFMG:Comment',
            'TickTackk\ChangeContentOwner\XFMG:Comment\ChangeOwner'
        );
    }

    /**
     * @param ParameterBag $params
     *
     * @return ErrorReply|RedirectReply|ViewReply
     */
    public function actionEdit(ParameterBag $params)
    {
        $reply = parent::actionEdit($params);

        $this->getChangeContentOwnerPlugin()->extendContentEditAction(
            $reply,
            'comment',
            'XFMG:Comment'
        );

        return $reply;
    }

    /**
     * @param CommentEntity $comment
     *
     * @return EditorSvcInterface|CommentEditorSvc
     * @throws ExceptionReply
     */
    protected function setupCommentEdit(CommentEntity $comment)
    {
        /** @var CommentEditorSvc|EditorSvcInterface $editor */
        $editor = parent::setupCommentEdit($comment);

        $this->getChangeContentOwnerPlugin()->extendEditorService($comment, $editor, 'XFMG:Comment');

        return $editor;
    }
}