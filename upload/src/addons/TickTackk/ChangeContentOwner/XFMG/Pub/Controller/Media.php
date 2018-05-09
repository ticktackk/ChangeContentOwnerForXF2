<?php

namespace TickTackk\ChangeContentOwner\XFMG\Pub\Controller;

use XF\Mvc\ParameterBag;

/**
 * Class Media
 *
 * @package TickTackk\ChangeContentOwner
 */
class Media extends XFCP_Media
{
    /**
     * @param ParameterBag $params
     *
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     *
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionChangeAuthor(ParameterBag $params)
    {
        /** @var \TickTackk\ChangeContentOwner\XFMG\Entity\MediaItem $mediaItem */
        /** @noinspection PhpUndefinedFieldInspection */
        $mediaItem = $this->assertViewableMediaItem($params->media_id);
        if (!$mediaItem->canChangeAuthor($error))
        {
            return $this->noPermission($error);
        }

        if ($this->isPost())
        {
            $newAuthor = $this->em()->findOne('XF:User', ['username' => $this->filter('new_author_username', 'str')]);
            if (!$newAuthor)
            {
                return $this->error(\XF::phrase('requested_user_not_found'));
            }

            /** @var \TickTackk\ChangeContentOwner\XFMG\Service\MediaItem\AuthorChanger $authorChangerService */
            $authorChangerService = $this->service('TickTackk\ChangeContentOwner\XFMG:MediaItem\AuthorChanger', $mediaItem, $newAuthor);
            $authorChangerService->changeAuthor();
            if (!$authorChangerService->validate($errors))
            {
                return $this->error($errors);
            }
            $mediaItem = $authorChangerService->save();

            return $this->redirect($this->buildLink('media', $mediaItem));
        }
        else
        {
            $viewParams = [
                'mediaItem' => $mediaItem
            ];
            return $this->view('TickTackk\ChangeContentOwner\XFMG:Media\ChangeAuthor', 'changeContentOwner_xfmg_media_change_author', $viewParams);
        }
    }
}