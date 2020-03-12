<?php

namespace TickTackk\ChangeContentOwner\InlineMod;

use TickTackk\ChangeContentOwner\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\ChangeContentOwner\Entity\ContentTrait as ContentEntityTrait;
use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger as AbstractOwnerChangerSvc;
use XF\Entity\User as UserEntity;
use XF\Http\Request;
use XF\InlineMod\AbstractAction;
use XF\Mvc\Controller;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\ArrayCollection;
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
     * @var null|int
     */
    protected $contentNewDateCounter;

    /**
     * @return \XF\Phrase
     */
    public function getTitle() : \XF\Phrase
    {
        return \XF::phrase('tckChangeContentOwner_change_x_owner_or_date...', [
            'content_type' => $this->getContentTypeSingular(),
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
     * @param bool $plural
     *
     * @return string
     */
    protected function getContentTypePhrase(bool $plural) : string
    {
        return utf8_strtolower($this->app()->getContentTypePhrase($this->getContentType(), $plural));
    }

    /**
     * @return string
     */
    protected function getContentTypeSingular() : string
    {
        return $this->getContentTypePhrase(false);
    }

    /**
     * @return string
     */
    protected function getContentTypePlural() : string
    {
        return $this->getContentTypePhrase(true);
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
            'contentTypeSingular' => $this->getContentTypeSingular(),
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
        return $content->canChangeOwner(null, $error) || $content->canChangeDate(null, $error);
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
            'bump_time' => null,
            'confirmed' => false
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
            'apply_time_interval' => $request->filter('apply_time_interval', 'bool'),
            'confirmed' => true
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
        else
        {
            $options['new_date'] = [
                'year' => null,
                'month' => null,
                'day' => null
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
     * @param Entity|ContentEntityInterface|ContentEntityTrait $entity
     * @param array $options
     *
     * @throws \Exception
     */
    protected function applyToEntity(Entity $entity, array $options) : void
    {
        $entities = new ArrayCollection([
            $entity->getEntityId() => $entity
        ]);
        $ownerChangerSvc = $this->getOwnerChangerSvc($entities);
        $newOwner = $this->newOwner;

        if ($newOwner && $entity->canChangeOwner($newOwner))
        {
            $ownerChangerSvc->setNewOwner($newOwner);
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

        $oldTimestamp = $ownerChangerSvc->getOldTimestamp($entity);
        $newTimestamp = $ownerChangerSvc->getNewTimestamp($entity);

        if ($oldTimestamp !== $newTimestamp && !$entity->canChangeDate($newTimestamp))
        {
            return;
        }

        $ownerChangerSvc->setPerformValidations(false);
        $ownerChangerSvc->setContentNewDateCounter($this->contentNewDateCounter);
        $ownerChangerSvc->save();

        if ($this->contentNewDateCounter === null)
        {
            $this->contentNewDateCounter = 0;
        }
        else
        {
            $this->contentNewDateCounter++;
        }
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