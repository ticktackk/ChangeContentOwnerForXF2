<?php

namespace TickTackk\ChangeContentOwner\XFMG\Pub\Controller;

use XF\Mvc\ParameterBag;

class Album extends XFCP_Album
{
    public function actionChangeOwner(ParameterBag $params)
    {
        /** @var \TickTackk\ChangeContentOwner\XFMG\Entity\Album $album */
        /** @noinspection PhpUndefinedFieldInspection */
        $album = $this->assertViewableAlbum($params->album_id);
        if (!$album->canChangeOwner($error))
        {
            return $this->noPermission($error);
        }

        if ($this->isPost())
        {
            $newAuthor = $this->em()->findOne('XF:User', ['username' => $this->filter('new_owner_username', 'str')]);
            if (!$newAuthor)
            {
                return $this->error(\XF::phrase('requested_user_not_found'));
            }

            /** @var \TickTackk\ChangeContentOwner\XFMG\Service\Album\AuthorChanger $authorChangerService */
            $authorChangerService = $this->service('TickTackk\ChangeContentOwner\XFMG:Album\AuthorChanger', $album, $album->User, $newAuthor);
            $authorChangerService->changeAuthor();
            if (!$authorChangerService->validate($errors))
            {
                return $this->error($errors);
            }
            $album = $authorChangerService->save();

            return $this->redirect($this->buildLink('media/albums', $album));
        }
        else
        {
            $viewParams = [
                'album' => $album,
                'addUsers' => $this->em()->findByIds('XF:User', $album->add_users ?: [])->pluckNamed('username'),
                'viewUsers' => $this->em()->findByIds('XF:User', $album->view_users ?: [])->pluckNamed('username')
            ];
            return $this->view('TickTackk\ChangeContentOwner\XFMG:Album\ChangeAuthor', 'changeContentOwner_xfmg_album_change_owner', $viewParams);
        }
    }
}