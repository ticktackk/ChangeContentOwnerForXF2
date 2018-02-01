<?php

namespace TickTackk\ChangeContentOwner\XFMG\Pub\Controller;

use XF\Mvc\ParameterBag;

class Media extends XFCP_Media
{
    public function actionChangeAuthor(ParameterBag $params)
    {
        /** @var \TickTackk\ChangeContentOwner\XFMG\Entity\MediaItem $mediaItem */
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

            $canTargetView = \XF::asVisitor($newAuthor, function() use ($mediaItem)
            {
                return $mediaItem->canView();
            });
            if (!$canTargetView)
            {
                return $this->error(\XF::phrase('changeContentOwner_new_author_must_be_able_to_view_this_xfmg_media'));
            }

            /** @var \TickTackk\ChangeContentOwner\XFMG\Service\MediaItem\AuthorChanger $authorChangerService */
            $authorChangerService = $this->service('TickTackk\ChangeContentOwner\XFMG:MediaItem\AuthorChanger', $mediaItem->Album, $mediaItem, $mediaItem->User, $newAuthor);
            $authorChangerService->changeAuthor();

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