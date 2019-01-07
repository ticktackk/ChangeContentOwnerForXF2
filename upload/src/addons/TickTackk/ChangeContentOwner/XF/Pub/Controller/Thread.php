<?php

namespace TickTackk\ChangeContentOwner\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

/**
 * Class Thread
 *
 * @package TickTackk\ChangeContentOwner
 */
class Thread extends XFCP_Thread
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
        /** @var \TickTackk\ChangeContentOwner\XF\Entity\Thread $thread */
        /** @noinspection PhpUndefinedFieldInspection */
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

            /** @var \TickTackk\ChangeContentOwner\XF\Service\Thread\AuthorChanger $authorChangerService */
            $authorChangerService = $this->service('TickTackk\ChangeContentOwner\XF:Thread\AuthorChanger', $thread, $newAuthor);
            $authorChangerService->changeAuthor();
            if (!$authorChangerService->validate($errors))
            {
                return $this->error($errors);
            }
            $thread = $authorChangerService->save();

            return $this->redirect($this->buildLink('threads', $thread));
        }

        $viewParams = [
            'thread' => $thread,
            'forum' => $forum
        ];
        return $this->view('TickTackk\ChangeContentOwner\XF:Thread\ChangeAuthor', 'changeContentOwner_thread_change_author', $viewParams);
    }
}