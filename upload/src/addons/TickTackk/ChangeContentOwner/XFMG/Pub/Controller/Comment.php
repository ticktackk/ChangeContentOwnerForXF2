<?php

namespace TickTackk\ChangeContentOwner\XFMG\Pub\Controller;

use XF\Mvc\ParameterBag;

/**
 * Class Comment
 *
 * @package TickTackk\ChangeContentOwner
 */
class Comment extends XFCP_Comment
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
        /** @var \TickTackk\ChangeContentOwner\XFMG\Entity\Comment $comment */
        /** @noinspection PhpUndefinedFieldInspection */
        $comment = $this->assertViewableComment($params->comment_id);
        if (!$comment->canChangeAuthor($error))
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

            /** @var \TickTackk\ChangeContentOwner\XFMG\Service\Comment\AuthorChanger $authorChangerService */
            $authorChangerService = $this->service('TickTackk\ChangeContentOwner\XFMG:Comment\AuthorChanger', $comment, $newAuthor);
            $authorChangerService->changeAuthor();
            if (!$authorChangerService->validate($errors))
            {
                return $this->error($errors);
            }
            $comment = $authorChangerService->save();

            return $this->redirect($this->buildLink('media/comments', $comment));
        }

        $content = $comment->Content;

        $viewParams = [
            'comment' => $comment,
            'content' => $content
        ];
        return $this->view('TickTackk\ChangeContentOwner\XFMG:Comment\ChangeAuthor', 'changeContentOwner_xfmg_comment_change_author', $viewParams);
    }
}