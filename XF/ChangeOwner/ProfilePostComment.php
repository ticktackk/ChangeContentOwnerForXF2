<?php

namespace TickTackk\ChangeContentOwner\XF\ChangeOwner;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;
use TickTackk\ChangeContentOwner\XF\Entity\ProfilePostComment as ExtendedProfilePostCommentEntity;
use XF\Mvc\Entity\Entity;

/**
 * Class ProfilePostComment
 *
 * @package TickTackk\ChangeContentOwner\XF\ChangeOwner
 */
class ProfilePostComment extends AbstractHandler
{
    /**
     * @param Entity|ExtendedProfilePostCommentEntity $content
     *
     * @return array
     */
    public function getBreadcrumbs(Entity $content): array
    {
        $breadcrumbs = [];

        $profilePost = $content->ProfilePost;
        $breadcrumbs[] = [
            'value' => \XF::phrase('profile_post_by_x', ['name' => $profilePost->username]),
            'href' => $this->app()->router('public')->buildLink('profile-posts', $profilePost)
        ];

        $breadcrumbs[] = [
            'value' => $this->getContentTitle($content),
            'href' => $this->getContentLink($content)
        ];

        return $breadcrumbs;
    }

    /**
     * @param Entity|ExtendedProfilePostCommentEntity $content
     *
     * @return int
     */
    public function getOldTimestamp(Entity $content): int
    {
        return $content->comment_date;
    }

    /**
     * @param Entity|ExtendedProfilePostCommentEntity $content
     *
     * @return string|void|\XF\Phrase
     *
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public function getContentTitle(Entity $content)
    {
        $user = $this->getOldOwner($content);

        return \XF::phrase('profile_post_comment_by_x', ['username' => $user->username]);
    }

    /**
     * @param Entity|ExtendedProfilePostCommentEntity $content
     *
     * @return string
     */
    public function getContentRoute(Entity $content): string
    {
        return 'profile-posts/comments';
    }
}