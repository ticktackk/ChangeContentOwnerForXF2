<?php

namespace TickTackk\ChangeContentOwner\XF\Pub\Controller;

use TickTackk\ChangeContentOwner\Pub\Controller\ContentTrait;
use TickTackk\ChangeContentOwner\Service\Content\EditorInterface as EditorSvcInterface;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Error as ErrorReply;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\View as ViewReply;
use XF\Service\Thread\Editor as ThreadEditor;
use XF\Entity\Thread as ThreadEntity;

/**
 * Class Thread
 *
 * @package TickTackk\ChangeContentOwner\XF\Pub\Controller
 */
class Thread extends XFCP_Thread
{
    use ContentTrait;

    /**
     * @param ParameterBag $parameterBag
     *
     * @return RedirectReply|ViewReply
     * @throws ExceptionReply
     */
    public function actionChangeOwner(ParameterBag $parameterBag)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $thread = $this->assertViewableThread($parameterBag->thread_id);

        return $this->getChangeContentOwnerPlugin()->actionChangeOwner(
            $thread,
            'TickTackk\ChangeContentOwner\XF:Thread\OwnerChanger',
            'XF:Thread'
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
            'thread'
        );

        return $reply;
    }

    /**
     * @param ThreadEntity $thread
     *
     * @return EditorSvcInterface|ThreadEditor
     * @throws ExceptionReply
     */
    protected function setupThreadEdit(ThreadEntity $thread)
    {
        /** @var ThreadEditor|EditorSvcInterface $editor */
        $editor = parent::setupThreadEdit($thread);

        $this->getChangeContentOwnerPlugin()->extendEditorService($thread, $editor);

        return $editor;
    }
}