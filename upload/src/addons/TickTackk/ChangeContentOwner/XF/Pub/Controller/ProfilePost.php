<?php

namespace TickTackk\ChangeContentOwner\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class ProfilePost extends XFCP_ProfilePost
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
        /** @var \TickTackk\ChangeContentOwner\XF\Entity\ProfilePost $profilePost */
        /** @noinspection PhpUndefinedFieldInspection */
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

            /** @var \TickTackk\ChangeContentOwner\XF\Service\ProfilePost\AuthorChanger $authorChangerService */
            $authorChangerService = $this->service('TickTackk\ChangeContentOwner\XF:ProfilePost\AuthorChanger', $profilePost, $newAuthor);
            $authorChangerService->changeAuthor();
            if (!$authorChangerService->validate($errors))
            {
                return $this->error($errors);
            }
            $profilePost = $authorChangerService->save();

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