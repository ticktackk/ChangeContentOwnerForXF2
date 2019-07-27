<?php

namespace TickTackk\ChangeContentOwner\InlineMod;

use TickTackk\ChangeContentOwner\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger as AbstractOwnerChangerSvc;
use XF\Entity\User as UserEntity;
use XF\Http\Request;
use XF\InlineMod\AbstractAction;
use XF\Mvc\Controller;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Reply\View as ReplyView;

/**
 * Class AbstractOwnerChangerAction
 *
 * @package TickTackk\ChangeContentOwner\InlineMod
 */
abstract class AbstractOwnerChangerAction extends AbstractAction
{
    /**
     * @var UserEntity
     */
    protected $newOwner;

    /**
     * @return \XF\Phrase
     */
    public function getTitle() : \XF\Phrase
    {
        return \XF::phrase('tckChangeContentOwner_change_x_owner_or_date...', [
            'content_type_plural' => $this->getContentTypePlural()
        ]);
    }

    /**
     * @return string
     */
    protected function getContentType() : string
    {
        return $this->handler->getContentType();
    }

    /**
     * @return string
     */
    protected function getContentTypePlural() : string
    {
        return utf8_strtolower($this->app()->getContentTypePhrase($this->getContentType(), true));
    }

    /**
     * @return string
     */
    abstract protected function getFormViewClass() : string;

    /**
     * @param AbstractCollection|ContentEntityInterface[] $contents
     * @param Controller         $controller
     *
     * @return ReplyView
     */
    public function renderForm(AbstractCollection $contents, Controller $controller) : ReplyView
    {
        $canChangeOwner = false;
        $canChangeDate = false;

        foreach ($contents AS $content)
        {
            if ($content->canChangeOwner())
            {
                $canChangeOwner = true;
            }

            if ($content->canChangeDate())
            {
                $canChangeDate = true;
            }

            if ($canChangeOwner && $canChangeDate)
            {
                continue;
            }
        }

        $viewParams = [
            'contentType' => $this->getContentType(),
            'contentTypePlural' => $this->getContentTypePlural(),

            'canChangeOwner' => $canChangeOwner,
            'canChangeDate' => $canChangeDate,

            'contents' => $contents,
            'total' => \count($contents)
        ];

        return $controller->view($this->getFormViewClass(), 'inline_mod_content_change_owner', $viewParams);
    }

    /**
     * @param AbstractCollection $contents
     * @param array              $options
     * @param                    $error
     *
     * @return bool
     */
    protected function canApplyInternal(AbstractCollection $contents, array $options, &$error) : bool
    {
        $newOwnerUsername = $options['username'];
        if ($newOwnerUsername)
        {
            $user = $this->assertViewableUser($newOwnerUsername, $error);
            if (!$user)
            {
                return false;
            }
            $this->newOwner = $user;
        }

        return parent::canApplyInternal($contents, $options, $error);
    }

    /**
     * @param Entity|ContentEntityInterface $content
     * @param array  $options
     * @param null   $error
     *
     * @return bool
     */
    protected function canApplyToEntity(Entity $content, array $options, &$error = null) : bool
    {
        return $content->canChangeOwner(null,$error) || $content->canChangeDate(null, $error);
    }

    /**
     * @return array
     */
    public function getBaseOptions() : array
    {
        return [
            'username' => null,
            'date' => null,
            'date_time_interval' => null,
            'bump_time' => null
        ];
    }

    /**
     * @param AbstractCollection $contents
     * @param Request            $request
     *
     * @return array
     */
    public function getFormOptions(AbstractCollection $contents, Request $request) : array
    {
        $options = [
            'username' => $request->filter('username', 'str'),
            'change_date' => $request->filter('change_date', 'bool'),
            'change_time' => $request->filter('change_time', 'bool'),
            'apply_time_interval' => $request->filter('apply_time_interval', 'bool')
        ];

        /**
         * @param string $input
         * @param array  $keys
         *
         * @return array
         */
        $filterArray = function (string $input, array $keys) use($request)
        {
            return $request->filter([
                $input => $keys
            ])[$input];
        };

        $newDate = explode('-', $request->filter('new_date', 'str'));
        if (count($newDate) === 3)
        {
            [$year, $month, $day] = $newDate;
            $options['new_date'] = [
                'year' => (int) $year,
                'month' => (int) $month,
                'day' => (int) $day
            ];
        }
        $options['new_time'] = $filterArray('new_time', [
            'hour' => 'int',
            'minute' => 'int',
            'second' => 'int'
        ]);
        $options['time_interval'] = $filterArray('time_interval', [
            'hour' => 'int',
            'minute' => 'int',
            'second' => 'int'
        ]);

        return $options;
    }

    /**
     * @return string
     */
    abstract protected function abstractServiceName() : string;

    /**
     * @param AbstractCollection $contents
     *
     * @return AbstractOwnerChangerSvc
     */
    protected function getOwnerChangerSvc(AbstractCollection $contents) : AbstractOwnerChangerSvc
    {
        return $this->app()->service($this->abstractServiceName(), $contents);
    }

    /**
     * @param AbstractCollection $contents
     * @param array              $options
     *
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    protected function applyInternal(AbstractCollection $contents, array $options) : void
    {
        $ownerChangerSvc = $this->getOwnerChangerSvc($contents);
        if ($this->newOwner)
        {
            $ownerChangerSvc->setNewOwner($this->newOwner);
        }

        if ($options['change_date'])
        {
            $ownerChangerSvc->setNewDate($options['new_date']);
        }

        if ($options['change_time'])
        {
            $ownerChangerSvc->setNewTime($options['new_time']);
        }

        if ($options['apply_time_interval'])
        {
            $ownerChangerSvc->setTimeInterval($options['time_interval']);
        }

        $ownerChangerSvc->apply();

        if ($ownerChangerSvc->validate())
        {
            $ownerChangerSvc->save();
        }
    }

    /**
     * @param Entity $entity
     * @param array  $options
     */
    protected function applyToEntity(Entity $entity, array $options) : void
    {
        throw new \LogicException('An error occurred while applying selected action on the contents. Please try again later.'); // dont
    }

    /**
     * @return array
     */
    protected function userExtraWith() : array
    {
        return ['Option', 'Privacy', 'Profile'];
    }

    /**
     * @param string $username
     * @param null   $error
     *
     * @return bool|UserEntity
     */
    protected function assertViewableUser(string $username, &$error = null)
    {
        $extraWith = array_unique($this->userExtraWith());

        /** @var UserEntity $user */
        $user = $this->app()->em()->findOne('XF:User', ['username' => $username], $extraWith);
        if (!$user)
        {
            $error[] = \XF::phrase('requested_user_not_found');
            return false;
        }

        return $user;
    }
}