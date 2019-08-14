<?php

namespace TickTackk\ChangeContentOwner\ChangeOwner;

use TickTackk\ChangeContentOwner\Entity\ContentInterface;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;
use XF\Repository\User as UserRepo;

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
            /** @noinspection PhpUndefinedFieldInspection */
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
    abstract public function getOldTimestamp(Entity $content) : int;

    /**
     * @param Entity $content
     * @param bool   $visitorTimezone
     *
     * @return \DateTime
     * @throws \Exception
     */
    public function getOldDateTime(Entity $content, bool $visitorTimezone = false) : \DateTime
    {
        $timezone = $visitorTimezone ? \XF::visitor()->timezone : 'UTC';
        $dateTimeObj = new \DateTime('now', new \DateTimeZone($timezone));
        $dateTimeObj->setTimestamp($this->getOldTimestamp($content));

        return $dateTimeObj;
    }

    protected function getStrictUnitValues(array $data)
    {
        foreach ($data AS $unit => $value)
        {
            $data[$unit] = (int) $value;
        }

        return $data;
    }

    /**
     * @param Entity $content
     * @param bool   $visitorTimezone
     * @param bool   $strict
     *
     * @return array
     * @throws \Exception
     */
    public function getOldDate(Entity $content, bool $visitorTimezone = false, bool $strict = false) : array
    {
        $oldDateTime = $this->getOldDateTime($content, $visitorTimezone);

        $oldDate = [
            'year' => $oldDateTime->format('Y'),
            'month' => $oldDateTime->format('m'),
            'day' => $oldDateTime->format('d')
        ];

        if ($strict)
        {
            $oldDate = $this->getStrictUnitValues($oldDate);
        }

        return $oldDate;
    }

    /**
     * @param Entity $content
     * @param bool   $visitorTimezone
     * @param bool   $strict
     *
     * @return array
     * @throws \Exception
     */
    public function getOldTime(Entity $content, bool $visitorTimezone = false, bool $strict = false) : array
    {
        $oldDateTime = $this->getOldDateTime($content, $visitorTimezone);

        $oldTime = [
            'hour' => $oldDateTime->format('H'),
            'minute' => $oldDateTime->format('i'),
            'second' => $oldDateTime->format('s')
        ];

        if ($strict)
        {
            $oldTime = $this->getStrictUnitValues($oldTime);
        }

        return $oldTime;
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
        $fallbackOwner->set('user_id', $userId, [
            'forceSet' => true
        ]);
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
            /** @noinspection PhpUndefinedMethodInspection */
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
     * @param Entity|ContentInterface   $content
     * @param int|null $newDate
     * @param null     $error
     *
     * @return mixed
     */
    public function canChangeDate(Entity $content, int $newDate = null, &$error = null)
    {
        $this->assertContentExtended($content);

        return $content->canChangeDate($newDate, $error);
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
    protected function app() : \XF\App
    {
        return \XF::app();
    }
}