<?php

namespace TickTackk\ChangeContentOwner\XF\Pub\Controller;

use TickTackk\ChangeContentOwner\ControllerPlugin\Content as ContentPlugin;
use TickTackk\ChangeContentOwner\Pub\Controller\ContentTrait;
use TickTackk\ChangeContentOwner\Service\Content\EditorInterface as EditorSvcInterface;
use XF\ControllerPlugin\AbstractPlugin;
use XF\Entity\Thread as ThreadEntity;
use XF\Mvc\Entity\Entity;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Error as ErrorReply;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\View as ViewReply;
use XF\Service\ProfilePost\Editor as ProfilePostEditor;
use XF\Entity\ProfilePost as ProfilePostEntity;
use XF\Service\ProfilePostComment\Editor as ProfilePostCommentEditor;
use XF\Entity\ProfilePostComment as ProfilePostCommentEntity;

/**
 * Class ProfilePost
 *
 * @package TickTackk\ChangeContentOwner\XF\Pub\Controller
 */
class ProfilePost extends XFCP_ProfilePost
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
        $profilePost = $this->assertViewableProfilePost($parameterBag->profile_post_id);

        return $this->getChangeContentOwnerPlugin()->actionChangeOwner(
            $profilePost,
            'TickTackk\ChangeContentOwner\XF:ProfilePost\OwnerChanger',
            'XF:ProfilePost',
            'TickTackk\ChangeContentOwner\XF:ProfilePost\ChangeOwner'
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
            'profilePost',
            'XF:ProfilePost'
        );

        return $reply;
    }

    /**
     * @param Entity $profilePost
     *
     * @return EditorSvcInterface|ProfilePostEditor
     * @throws ExceptionReply
     */
    protected function setupEdit(Entity $profilePost)
    {
        /** @var ProfilePostEditor|EditorSvcInterface $editor */
        $editor = parent::setupEdit($profilePost);

        $this->getChangeContentOwnerPlugin()->extendEditorService($profilePost, $editor, 'XF:ProfilePost');

        return $editor;
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

        return $this->getChangeContentOwnerPlugin()->actionChangeOwner(
            $comment,
            'TickTackk\ChangeContentOwner\XF:ProfilePostComment\OwnerChanger',
            'XF:ProfilePostComment',
            'TickTackk\ChangeContentOwner\XF:ProfilePost\Comments\ChangeOwner',
            'XF:ProfilePost'
        );
    }

    /**
     * @param ParameterBag $params
     *
     * @return ErrorReply|RedirectReply|ViewReply
     */
    public function actionCommentsEdit(ParameterBag $params)
    {
        $reply = parent::actionCommentsEdit($params);

        $this->getChangeContentOwnerPlugin()->extendContentEditAction($reply, 'comment', 'XF:ProfilePostComment');

        return $reply;
    }

    /**
     * @param ProfilePostCommentEntity $comment
     *
     * @return EditorSvcInterface|ProfilePostCommentEditor
     * @throws ExceptionReply
     */
    protected function setupCommentEdit(ProfilePostCommentEntity $comment)
    {
        /** @var ProfilePostCommentEditor|EditorSvcInterface $editor */
        $editor = parent::setupCommentEdit($comment);

        $this->getChangeContentOwnerPlugin()->extendEditorService($comment, $editor, 'XF:ProfilePost');

        return $editor;
    }
}