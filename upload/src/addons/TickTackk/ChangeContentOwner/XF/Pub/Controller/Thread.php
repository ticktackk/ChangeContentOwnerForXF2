<?php

namespace TickTackk\ChangeContentOwner\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class Thread extends XFCP_Thread
{
    public function actionChangeAuthor(ParameterBag $params)
    {
        /** @var \TickTackk\ChangeContentOwner\XF\Entity\Thread $thread */
        $thread = $this->assertViewableThread($params->thread_id);
        if (!$thread->canChangeAuthor($error))
        {
            return $this->noPermission($error);
        }
        $forum = $thread->Forum;

        if ($this->isPost())
        {
            $newAuthor = $this->em()->findOne('XF:User', ['username' => $this->filter('new_author_username', 'str')]);
            if (!$newAuthor)
            {
                return $this->error(\XF::phrase('requested_user_not_found'));
            }

            $canTargetView = \XF::asVisitor($newAuthor, function() use ($thread)
            {
                return $thread->canView();
            });
            if (!$canTargetView)
            {
                return $this->error(\XF::phrase('changeContentOwner_new_author_must_be_able_to_view_this_thread'));
            }

            /** @var \TickTackk\ChangeContentOwner\XF\Service\Thread\AuthorChanger $authorChangerService */
            $authorChangerService = $this->service('TickTackk\ChangeContentOwner\XF:Thread\AuthorChanger', $thread, $thread->FirstPost, $thread->Forum, $thread->User, $newAuthor);
            $authorChangerService->changeAuthor();

            return $this->redirect($this->buildLink('threads', $thread));
        }
        else
        {
            $viewParams = [
                'thread' => $thread,
                'forum' => $forum
            ];
            return $this->view('TickTackk\ChangeContentOwner\XF:Thread\ChangeAuthor', 'changeContentOwner_thread_change_author', $viewParams);
        }
    }
}