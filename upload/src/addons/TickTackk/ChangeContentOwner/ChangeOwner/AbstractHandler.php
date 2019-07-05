<?php

namespace TickTackk\ChangeContentOwner\ChangeOwner;

use TickTackk\ChangeContentOwner\Entity\ContentInterface;
use TickTackk\ChangeContentOwner\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\ChangeContentOwner\Service\Content\AbstractOwnerChanger as AbstractOwnerChangerSvc;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;
use XF\Repository\User as UserRepo;
use XF\Service\AbstractService;

/**
 * Class AbstractHandler
 *
 * @package TickTackk\ChangeContentOwner\ChangeOwner
 */
abstract class AbstractHandler
{
    /**
     * @var string
     */
    protected $contentType;

    /**
     * AbstractHandler constructor.
     *
     * @param string $contentType
     */
    public function __construct(string $contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @param Entity $content
     *
     * @return string|\XF\Phrase
     */
    abstract public function getContentTitle(Entity $content);

    /**
     * @param Entity $content
     *
     * @return array
     */
    abstract public function getBreadcrumbs(Entity $content): array;

    /**
     * @param Entity $content
     *
     * @return string
     */
    abstract public function getContentRoute(Entity $content): string;

    /**
     * @param Entity $content
     *
     * @return string
     */
    public function getContentLink(Entity $content) : string
    {
        return $this->app()->router('public')->buildLink('canonical:' . $this->getContentRoute($content), $content);
    }

    /**
     * @param Entity $content
     *
     * @return string
     */
    public function getChangeOwnerLink(Entity $content) : string
    {
        return $this->app()->router('public')->buildLink($this->getContentRoute($content) . '/change-owner', $content);
    }

    /**
     * @param Entity $content
     *
     * @return UserEntity
     */
    public function getOldOwner(Entity $content): UserEntity
    {
        $oldContentOwner = null;
        try
        {
            $oldContentOwner = $content->User;
        }
        catch (\InvalidArgumentException $e)
        {
            throw new \LogicException('Could not determine content owner; please override');
        }

        return $oldContentOwner ?: $this->getFallbackOldOwner($content);
    }

    /**
     * @param Entity $content
     *
     * @return int
     */
    public function getOldDate(Entity $content) : int
    {
        try
        {
            return $content->get($content->getEntityContentType() . '_date');
        }
        catch (\InvalidArgumentException $e)
        {
            throw new \LogicException('Could not determine content date; please override');
        }
    }

    /**
     * @param Entity $content
     * @param string $userIdField
     * @param string $usernameField
     *
     * @return UserEntity
     */
    protected function getFallbackOldOwner(Entity $content, string $userIdField = 'user_id', string $usernameField = 'username'): UserEntity
    {
        try
        {
            $userId = $content->get($userIdField);
            $username = $content->get($usernameField);
        }
        catch (\InvalidArgumentException $e)
        {
            throw new \LogicException('Could not determine fallback old content owner; please override');
        }

        $userRepo = $this->getUserRepo();
        $fallbackOwner = $userRepo->getGuestUser($username);
        $fallbackOwner->setReadOnly(false);
        $fallbackOwner->set('user_id', $userId);
        $fallbackOwner->setReadOnly(true);

        return $fallbackOwner;
    }

    /**
     * @param Entity     $content
     * @param UserEntity $newOwner
     * @param \XF\Phrase $error
     *
     * @return bool
     * @throws \Exception
     */
    public function canNewOwnerViewContent(Entity $content, UserEntity $newOwner, &$error) : bool
    {
        if (!method_exists($content, 'canView'))
        {
            throw new \LogicException('Could not determine content viewability; please override');
        }

        return \XF::asVisitor($newOwner, function () use($content, $error)
        {
            return $content->canView($error);
        });
    }

    /**
     * @param Entity|ContentInterface          $content
     * @param UserEntity|null $newOwner
     * @param null            $error
     *
     * @return bool
     */
    public function canChangeOwner(Entity $content, UserEntity $newOwner = null, &$error = null): bool
    {
        $this->assertContentExtended($content);

        return $content->canChangeOwner($newOwner, $error);
    }

    /**
     * @param Entity|ContentInterface $content
     * @param null                    $error
     *
     * @return null|
     */
    public function canChangeDate(Entity $content, &$error = null)
    {
        $this->assertContentExtended($content);

        return $content->canChangeDate($error);
    }

    /**
     * @param bool $plural
     *
     * @return \XF\Phrase
     */
    public function getContentTypePhrase(bool $plural = false) : \XF\Phrase
    {
        return $this->app()->getContentTypePhrase($this->contentType, $plural);
    }

    /**
     * @param Entity $content
     */
    protected function assertContentExtended(Entity $content): void
    {
        if (!$content instanceof ContentInterface)
        {
            throw new \LogicException('Content entity must implement ContentInterface.');
        }
    }

    /**
     * @return Repository|UserRepo
     */
    protected function getUserRepo(): UserRepo
    {
        return $this->app()->repository('XF:User');
    }

    /**
     * @return \XF\App
     */
    protected function app()
    {
        return \XF::app();
    }
}