<?php

namespace TickTackk\ChangeContentOwner\ControllerPlugin;

use TickTackk\ChangeContentOwner\Repository\ContentInterface as ContentRepoInterface;
use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger as AbstractOwnerChangerSvc;
use XF\Entity\User as UserEntity;
use XF\ControllerPlugin\AbstractPlugin;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\View as ViewReply;
use XF\Mvc\Reply\Exception as ExceptionReply;

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

        if (!$handler->canChangeOwner($content, null, $error) && !$handler->canChangeDate($content, $error))
        {
            throw $this->exception($this->noPermission($error));
        }

        if ($this->isPost())
        {
            $newOwner = null;
            $newOwnerUsername = $this->filter('username', 'str');

            if ($newOwnerUsername)
            {
                $newOwner = $this->assertViewableUser($newOwnerUsername);
                if (!$handler->canChangeOwner($content, $newOwner, $error))
                {
                    throw $this->exception($this->noPermission($error));
                }
            }

            $newDate = $this->filter('date', 'datetime');
            if ($newDate)
            {
                if (!$handler->canChangeDate($content, $error))
                {
                    throw $this->exception($this->noPermission($error));
                }
            }

            if (!$newOwner && !$newDate)
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

            /** @var AbstractOwnerChangerSvc $ownerChangerSvc */
            $ownerChangerSvc = $this->service($serviceName, $content);
            if ($newOwner)
            {
                $ownerChangerSvc->setNewOwner($newOwner);
            }

            if ($newDate)
            {
                $ownerChangerSvc->setNewDate($newDate);
            }

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