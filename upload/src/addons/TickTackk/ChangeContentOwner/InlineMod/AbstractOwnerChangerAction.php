<?php

namespace TickTackk\ChangeContentOwner\InlineMod;

use TickTackk\ChangeContentOwner\Entity\ContentInterface;
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
     * @return \XF\Phrase
     */
    public function getTitle() : \XF\Phrase
    {
        return \XF::phrase('tckChangeContentOwner_change_content_date');
    }

    /**
     * @return array
     */
    public function getBaseOptions() : array
    {
        return [
            'username' => null,
            'date' => null
        ];
    }

    /**
     * @return string
     */
    abstract protected function getFormViewClass() : string;

    /**
     * @param AbstractCollection $contents
     * @param Controller         $controller
     *
     * @return ReplyView
     */
    public function renderForm(AbstractCollection $contents, Controller $controller) : ReplyView
    {
        $viewParams = [
            'contents' => $contents,
            'total' => \count($contents)
        ];

        return $controller->view($this->getFormViewClass(), 'inline_mod_content_change_owner', $viewParams);
    }

    /**
     * @var UserEntity
     */
    protected $newUser;

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
        }

        return parent::canApplyInternal($contents, $options, $error);
    }

    /**
     * @param Entity|ContentInterface $content
     * @param array  $options
     * @param null   $error
     *
     * @return bool
     */
    protected function canApplyToEntity(Entity $content, array $options, &$error = null) : bool
    {
        if ($this->newUser && !$content->canChangeOwner($this->newUser, $error))
        {
            return false;
        }

        if ($options['date'] && !$content->canChangeDate($error))
        {
            return false;
        }

        return $content->canChangeOwner(null ,$error) || $content->canChangeDate($error);
    }

    /**
     * @param AbstractCollection $contents
     * @param Request            $request
     *
     * @return array
     */
    public function getFormOptions(AbstractCollection $contents, Request $request) : array
    {
        return [
            'username' => $request->filter('username', 'str'),
            'date' => $request->filter('date', 'datetime', [
                'tz' => \XF::visitor()->timezone
            ])
        ];
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
        if ($this->newUser)
        {
            $ownerChangerSvc->setNewOwner($this->newUser);
        }

        if ($options['date'])
        {
            $ownerChangerSvc->setNewDate($options['date']);
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
    protected function applyToEntity(Entity $entity, array $options)
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