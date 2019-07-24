<?php

namespace TickTackk\ChangeContentOwner\InlineMod;

use TickTackk\ChangeContentOwner\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger as AbstractOwnerChangerSvc;
use XF\Entity\User as UserEntity;
use XF\Http\Request;
use XF\InlineMod\AbstractAction;
use XF\InlineMod\AbstractHandler;
use XF\Mvc\Controller;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Reply\View as ReplyView;
use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler as ContentAbstractHandler;

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
        return \XF::phrase('tckChangeContentOwner_change_owner_or_date');
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
        if ($this->newOwner && !$content->canChangeOwner($this->newOwner, $error))
        {
            return false;
        }

        if ($options['date'] && !$content->canChangeDate($options['date'], $error))
        {
            return false;
        }
        //TODO: maybe use change owner handler to get old date, add bump time to it and then pass it to change date checker
        // via 3rd argument saying yes its for bumping date time??
        else if ($options['bump_time'] && !$content->canChangeDate(null, $error))
        {
            return false;
        }

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
            'change_time_type' => $request->filter('change_time_type', 'str'),
            'date' => null,
            'date_time_interval' => null,
            'bump_time' => null
        ];

        $filterUnits = function (string $input) use($request)
        {
            return $request->filter([
                $input => [
                    'hours' => 'int',
                    'minutes' => 'int',
                    'seconds' => 'int'
                ]
            ])[$input];
        };

        switch ($options['change_time_type'])
        {
            case 'update_datetime':
                $options['date'] = $request->filter('date', 'datetime', [
                    'tz' => \XF::visitor()->timezone
                ]);
                $options['date_time_interval'] = $filterUnits('date_time_interval');
                break;

            case 'bump_time':
                $options['bump_time'] = $filterUnits('bump_time');
                break;
        }

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

        if ($options['change_time_type'] === 'update_datetime')
        {
            if ($options['date'])
            {
                $ownerChangerSvc->setNewDate($options['date']);
                $ownerChangerSvc->setNewDateTimeIntervals($options['date_time_interval']);
            }
        }
        else if ($options['change_time_type'] === 'bump_time')
        {
            $ownerChangerSvc->setBumpTimeBy($options['bump_time']);
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