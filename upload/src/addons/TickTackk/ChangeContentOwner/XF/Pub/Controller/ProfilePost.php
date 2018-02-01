<?php

namespace TickTackk\ChangeContentOwner\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class ProfilePost extends XFCP_ProfilePost
{
    public function actionChangeAuthor(ParameterBag $params)
    {
        /** @var \TickTackk\ChangeContentOwner\XF\Entity\ProfilePost $profilePost */
        $profilePost = $this->assertViewableProfilePost($params->profile_post_id);
        if (!$profilePost->canChangeAuthor($error))
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

            $canTargetView = \XF::asVisitor($newAuthor, function() use ($profilePost)
            {
                return $profilePost->canView();
            });
            if (!$canTargetView)
            {
                return $this->error(\XF::phrase('changeContentOwner_new_author_must_be_able_to_view_this_profile_post'));
            }

            /** @var \TickTackk\ChangeContentOwner\XF\Service\ProfilePost\AuthorChanger $authorChangerService */
            $authorChangerService = $this->service('TickTackk\ChangeContentOwner\XF:ProfilePost\AuthorChanger', $profilePost, $profilePost->ProfileUser, $profilePost->User, $newAuthor);
            $authorChangerService->changeAuthor();

            return $this->redirect($this->buildLink('profile-posts', $profilePost));
        }
        else
        {
            $viewParams = [
                'profilePost' => $profilePost,
                'profileUser' => $profilePost->ProfileUser
            ];
            return $this->view('TickTackk\ChangeContentOwner\XF:ProfilePost\ChangeAuthor', 'changeContentOwner_profile_post_change_author', $viewParams);
        }
    }
}