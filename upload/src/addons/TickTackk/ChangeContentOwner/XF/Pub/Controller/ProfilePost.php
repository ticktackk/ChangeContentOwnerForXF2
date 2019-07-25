<?php

namespace TickTackk\ChangeContentOwner\XF\Pub\Controller;

use TickTackk\ChangeContentOwner\Pub\Controller\ContentTrait;
use TickTackk\ChangeContentOwner\Service\Content\EditorInterface as EditorSvcInterface;
use XF\Mvc\Entity\Entity;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Error as ErrorReply;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\View as ViewReply;
use XF\Service\ProfilePost\Editor as ProfilePostEditor;
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
            'XF:ProfilePost'
        );
    }

    /**
     * @param ParameterBag $params
     *
     * @return ErrorReply|RedirectReply|ViewReply
     * @throws \Exception
     */
    public function actionEdit(ParameterBag $params)
    {
        $reply = parent::actionEdit($params);

        $this->getChangeContentOwnerPlugin()->extendContentEditAction(
            $reply,
            'profilePost'
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

        $this->getChangeContentOwnerPlugin()->extendEditorService($profilePost, $editor);

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
        /** @noinspection PhpUndefinedFieldInspection */
        $comment = $this->assertViewableComment($parameterBag->profile_post_comment_id);

        return $this->getChangeContentOwnerPlugin()->actionChangeOwner(
            $comment,
            'TickTackk\ChangeContentOwner\XF:ProfilePostComment\OwnerChanger',
            'XF:ProfilePostComment'
        );
    }

    /**
     * @param ParameterBag $params
     *
     * @return ErrorReply|RedirectReply|ViewReply
     * @throws \Exception
     */
    public function actionCommentsEdit(ParameterBag $params)
    {
        $reply = parent::actionCommentsEdit($params);

        $this->getChangeContentOwnerPlugin()->extendContentEditAction($reply, 'comment');

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

        $this->getChangeContentOwnerPlugin()->extendEditorService($comment, $editor);

        return $editor;
    }
}