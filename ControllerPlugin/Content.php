<?php

namespace TickTackk\ChangeContentOwner\ControllerPlugin;

use TickTackk\ChangeContentOwner\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\ChangeContentOwner\Entity\ContentTrait as ContentEntityTrait;
use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger as AbstractOwnerChangerSvc;
use TickTackk\ChangeContentOwner\Service\Content\EditorInterface as EditorSvcInterface;
use XF\Entity\User as UserEntity;
use XF\ControllerPlugin\AbstractPlugin;
use XF\Mvc\Entity\Entity;
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
     * @param Entity|ContentEntityInterface|ContentEntityTrait $content
     * @param string $serviceName
     * @param string $view
     *
     * @return RedirectReply|ViewReply
     * @throws ExceptionReply
     * @throws \Exception
     */
    public function actionChangeOwner(Entity $content, string $serviceName, string $view)
    {
        $handler = $content->getChangeOwnerHandler(true);

        if (!$handler->canChangeOwner($content, null, $error) && !$handler->canChangeDate($content, null, $error))
        {
            throw $this->exception($this->noPermission($error));
        }

        if ($this->isPost())
        {
            /** @var AbstractOwnerChangerSvc $ownerChangerSvc */
            $ownerChangerSvc = $this->service($serviceName, $content);

            $this->setNewOwnerDateTimeAndInterval($ownerChangerSvc, $content);

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
     *
     * @throws \Exception
     */
    public function extendContentEditAction(AbstractReply $reply, string $contentParamName) : void
    {
        if ($reply instanceof ViewReply)
        {
            /** @var Entity|ContentEntityInterface|ContentEntityTrait $content */
            $content = $reply->getParam($contentParamName);
            if ($content && !$this->isPost())
            {
                $handler = $content->getChangeOwnerHandler(true);
                $reply->setParam('changeOwnerHandler', $handler);
                $reply->setParam('tckCCO_fullWidth', $this->filter('_xfWithData', 'bool'));
            }
        }
    }

    /**
     * @param Entity|ContentEntityInterface             $content
     * @param EditorSvcInterface $editor
     *
     * @throws ExceptionReply
     */
    public function extendEditorService(Entity $content, EditorSvcInterface $editor) : void
    {
        if ($this->isPost())
        {
            $editor->setupOwnerChanger();

            $this->setNewOwnerDateTimeAndInterval($editor, $content);
        }
    }

    /**
     * @param AbstractService|EditorSvcInterface|AbstractOwnerChangerSvc        $service
     * @param ContentEntityInterface|ContentEntityTrait|Entity $content
     *
     * @throws ExceptionReply
     * @throws \Exception
     */
    protected function setNewOwnerDateTimeAndInterval(AbstractService $service, ContentEntityInterface $content) : void
    {
        $handler = $content->getChangeOwnerHandler(true);
        $newOwnerUsername = $this->filter('username', 'str');

        if ($newOwnerUsername)
        {
            $newOwner = $this->assertViewableUser($newOwnerUsername);
            if (!$handler->canChangeOwner($content, $newOwner, $error))
            {
                throw $this->exception($this->noPermission($error));
            }

            $service->setNewOwner($newOwner);
        }

        /**
         * @param string $input
         * @param array  $keys
         *
         * @return array
         */
        $filterArray = function (string $input, array $keys)
        {
            return $this->filter([
                $input => $keys
            ])[$input];
        };

        $changeDate = $this->filter('change_date', 'bool');
        if ($changeDate)
        {
            $newDate = explode('-', $this->filter('new_date', 'str'));
            if (count($newDate) === 3)
            {
                [$year, $month, $day] = $newDate;
                $service->setNewDate([
                    'year' => (int) $year,
                    'month' => (int) $month,
                    'day' => (int) $day
                ]);
            }
        }

        $changeTime = $this->filter('change_time', 'bool');
        if ($changeTime)
        {
            $service->setNewTime($filterArray('new_time', [
                'hour' => 'int',
                'minute' => 'int',
                'second' => 'int'
            ]));
        }

        $applyTimeInterval = $this->filter('apply_time_interval', 'bool');
        if ($applyTimeInterval)
        {
            $service->setTimeInterval($filterArray('time_interval', [
                'hour' => 'int',
                'minute' => 'int',
                'second' => 'int'
            ]));
        }

        if ($service instanceof AbstractOwnerChangerSvc)
        {
            $oldTimestamp = $service->getOldTimestamp($content);
            $newTimestamp = $service->getNewTimestamp($content);
        }
        else
        {
            $ownerChangerSvc = $service->getOwnerChangerSvc();
            $oldTimestamp = $ownerChangerSvc->getOldTimestamp($content);
            $newTimestamp = $ownerChangerSvc->getNewTimestamp($content);
        }

        if ($oldTimestamp !== $newTimestamp && !$handler->canChangeDate($content, $newTimestamp, $error))
        {
            throw $this->exception($this->noPermission($error));
        }
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
        $extraWith = array_unique($extraWith);

        /** @var UserEntity $user */
        $user = $this->em->findOne('XF:User', ['username' => $username], $extraWith);
        if (!$user)
        {
            throw $this->exception($this->notFound(\XF::phrase('requested_user_not_found')));
        }

        return $user;
    }
}