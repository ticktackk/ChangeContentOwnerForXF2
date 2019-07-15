<?php

namespace TickTackk\ChangeContentOwner\ControllerPlugin;

use TickTackk\ChangeContentOwner\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\ChangeContentOwner\Repository\ContentInterface as ContentRepoInterface;
use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger as AbstractOwnerChangerSvc;
use TickTackk\ChangeContentOwner\Service\Content\EditorInterface as EditorSvcInterface;
use XF\Entity\User as UserEntity;
use XF\ControllerPlugin\AbstractPlugin;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\View as ViewReply;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Service\AbstractService;

/**
 * Class Content
 *
 * @package TickTackk\ChangeContentOwner\ControllerPlugin
 */
class Content extends AbstractPlugin
{
    /**
     * @param Entity      $content
     * @param string      $serviceName
     * @param string      $entityIdentifier
     * @param string      $view
     * @param string|null $repoIdentifier
     *
     * @return RedirectReply|ViewReply
     * @throws ExceptionReply
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    public function actionChangeOwner(Entity $content, string $serviceName, string $entityIdentifier, string $view, string $repoIdentifier = null)
    {
        $repoIdentifier = $repoIdentifier ?: $entityIdentifier;
        $contentRepo = $this->getContentRepo($repoIdentifier);
        $handler = $contentRepo->getChangeOwnerHandler($content, true);

        if (!$handler->canChangeOwner($content, null, $error) && !$handler->canChangeDate($content, null, $error))
        {
            throw $this->exception($this->noPermission($error));
        }

        if ($this->isPost())
        {
            /** @var AbstractOwnerChangerSvc $ownerChangerSvc */
            $ownerChangerSvc = $this->service($serviceName, $content);

            $this->setNewOwnerAndDate($ownerChangerSvc, $content, $repoIdentifier);

            $ownerChangerSvc->apply();
            if (!$ownerChangerSvc->validate($errors))
            {
                throw $this->exception($this->error($errors));
            }

            $ownerChangerSvc->save();

            return $this->redirect($handler->getContentLink($content));
        }

        $viewParams = [
            'content' => $content,
            'handler' => $handler,
            'breadcrumbs' => $handler->getBreadcrumbs($content),
            'contentTitle' => $handler->getContentTitle($content),
            'contentLink' => $handler->getContentLink($content),
            'changeOwnerLink' => $handler->getChangeOwnerLink($content)
        ];

        return $this->view(
            $view,
            'tckChangeContentOwner_change_content_owner',
            $viewParams
        );
    }

    /**
     * @param AbstractReply $reply
     * @param string        $contentParamName
     * @param string        $repoName
     */
    public function extendContentEditAction(AbstractReply $reply, string $contentParamName, string $repoName) : void
    {
        if ($reply instanceof ViewReply)
        {
            /** @var Entity|ContentEntityInterface $content */
            $content = $reply->getParam($contentParamName);
            if ($content && !$this->isPost())
            {
                /** @var Repository|ContentRepoInterface $contentRepo */
                $contentRepo = $this->repository($repoName);
                $handler = $contentRepo->getChangeOwnerHandler($content);
                $reply->setParam('changeOwnerHandler', $handler);
            }
        }
    }

    /**
     * @param Entity|ContentEntityInterface             $content
     * @param EditorSvcInterface $editor
     * @param string             $repoIdentifier
     *
     * @throws ExceptionReply
     */
    public function extendEditorService(Entity $content, EditorSvcInterface $editor, string $repoIdentifier) : void
    {
        if ($this->isPost())
        {
            $editor->setupOwnerChanger();
            $this->setNewOwnerAndDate($editor, $content, $repoIdentifier);
            $editor->applyOwnerChanger();
        }
    }

    /**
     * @param AbstractService|EditorSvcInterface|AbstractOwnerChangerSvc        $service
     * @param ContentEntityInterface|Entity $content
     * @param string                 $repoIdentifier
     *
     * @throws ExceptionReply
     */
    protected function setNewOwnerAndDate(AbstractService $service, ContentEntityInterface $content, string $repoIdentifier) : void
    {
        $contentRepo = $this->getContentRepo($repoIdentifier);
        $handler = $contentRepo->getChangeOwnerHandler($content, true);

        $newOwnerUsername = $this->filter('username', 'str');
        $newDate = $this->filter('date', 'datetime', [
            'tz' => \XF::visitor()->timezone
        ]);

        if ($newOwnerUsername)
        {
            $newOwner = $this->assertViewableUser($newOwnerUsername);
            if (!$newOwner)
            {
                throw $this->exception($this->error(\XF::phrase('please_enter_valid_name')));
            }

            if (!$handler->canChangeOwner($content, $newOwner, $error))
            {
                throw $this->exception($this->noPermission($error));
            }

            $service->setNewOwner($newOwner);
        }

        if ($newDate)
        {
            if (!$handler->canChangeDate($content, $newDate, $error))
            {
                throw $this->exception($this->noPermission($error));
            }

            $service->setNewDate($newDate);
        }

        if (!$newOwnerUsername && !$newDate && !$service instanceof EditorSvcInterface)
        {
            if ($handler->canChangeOwner($content) && $handler->canChangeDate($content))
            {
                throw $this->exception($this->error(\XF::phrase('tckChangeContentOwner_you_must_either_change_owner_or_date_of_this_x', [
                    'content_type_phrase' => $handler->getContentTypePhrase()
                ])));
            }

            if ($handler->canChangeOwner($content))
            {
                throw $this->exception($this->error(\XF::phrase('please_enter_valid_name')));
            }

            if ($handler->canChangeDate($content))
            {
                throw $this->exception($this->error(\XF::phrase('tckChangeContentOwner_please_enter_valid_date')));
            }
        }
    }

    /**
     * @param string $repoIdentifier
     *
     * @return Repository|ContentRepoInterface
     */
    protected function getContentRepo(string $repoIdentifier) : Repository
    {
        return $this->repository($repoIdentifier);
    }

    /**
     * @param string $username
     * @param array  $extraWith
     *
     * @return UserEntity
     * @throws ExceptionReply
     */
    protected function assertViewableUser(string $username, array $extraWith = []) : UserEntity
    {
        $extraWith[] = 'Option';
        $extraWith[] = 'Privacy';
        $extraWith[] = 'Profile';
        array_unique($extraWith);

        /** @var UserEntity $user */
        $user = $this->em->findOne('XF:User', ['username' => $username], $extraWith);
        if (!$user)
        {
            throw $this->exception($this->notFound(\XF::phrase('requested_user_not_found')));
        }

        return $user;
    }
}