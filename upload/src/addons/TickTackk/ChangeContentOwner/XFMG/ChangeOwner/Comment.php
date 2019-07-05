<?php

namespace TickTackk\ChangeContentOwner\XFMG\ChangeOwner;

use TickTackk\ChangeContentOwner\ChangeOwner\AbstractHandler;
use TickTackk\ChangeContentOwner\XFMG\Entity\Comment as ExtendedCommentEntity;
use XF\Mvc\Entity\Entity;

/**
 * Class Comment
 *
 * @package TickTackk\ChangeContentOwner\XFMG\ChangeOwner
 */
class Comment extends AbstractHandler
{
    /**
     * @param Entity|ExtendedCommentEntity $content
     *
     * @return array
     */
    public function getBreadcrumbs(Entity $content): array
    {
        $breadcrumbs = [];
        if ($content->Media)
        {
            $breadcrumbs = $content->Media->getBreadcrumbs();
        }
        else if ($content->Album)
        {
            $breadcrumbs = $content->Album->getBreadcrumbs();
        }

        $breadcrumbs[] = [
            'value' => \XF::phrase('xfmg_comment_by_x', [
                'user' => $content->username
            ]),
            'href' => $this->app()->router('public')->buildLink('media/comments')
        ];

        return $breadcrumbs;
    }

    /**
     * @param Entity|ExtendedCommentEntity $content
     *
     * @return string
     */
    public function getContentRoute(Entity $content): string
    {
        return 'media/albums';
    }

    /**
     * @param Entity|ExtendedCommentEntity $content
     *
     * @return string|\XF\Phrase
     */
    public function getContentTitle(Entity $content)
    {
        $mediaItem = $content->Media;
        if ($mediaItem)
        {
            return \XF::phrase('xfmg_comment_by_x_in_media_y', [
                'user' => $content->username,
                'title' => \XF::app()->stringFormatter()->censorText($mediaItem->title)
            ]);
        }

        $album = $content->Album;
        if ($album)
        {
            return \XF::phrase('xfmg_comment_by_x_in_album_y', [
                'user' => $album->username,
                'title' => \XF::app()->stringFormatter()->censorText($album->title)
            ]);
        }

        return \XF::phrase('xfmg_comment_by_x', [
            'user' => $content->username
        ]);
    }
}