<?php

namespace TickTackk\ChangeContentOwner\XF\Pub\Controller;

use TickTackk\ChangeContentOwner\Pub\Controller\ContentTrait;
use TickTackk\ChangeContentOwner\Service\Content\EditorInterface as EditorSvcInterface;
use TickTackk\ChangeContentOwner\XF\Service\Post\Editor as PostEditorSvc;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Error as ErrorReply;
use XF\Mvc\Reply\View as ViewReply;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Entity\Post as PostEntity;

/**
 * Class Post
 *
 * @package TickTackk\ChangeContentOwner\XF\Pub\Controller
 */
class Post extends XFCP_Post
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
        $post = $this->assertViewablePost($parameterBag->post_id);

        return $this->getChangeContentOwnerPlugin()->actionChangeOwner(
            $post,
            'TickTackk\ChangeContentOwner\XF:Post\OwnerChanger',
            'XF:Post'
        );
    }

    /**
     * @param PostEntity $post
     *
     * @return EditorSvcInterface|PostEditorSvc
     * @throws ExceptionReply
     */
    protected function setupPostEdit(PostEntity $post)
    {
        /** @var PostEditorSvc|EditorSvcInterface $editor */
        $editor = parent::setupPostEdit($post);

        $this->getChangeContentOwnerPlugin()->extendEditorService($post, $editor);

        return $editor;
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
            'post'
        );

        return $reply;
    }
}