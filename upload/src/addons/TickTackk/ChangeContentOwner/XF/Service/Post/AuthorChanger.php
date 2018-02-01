<?php

namespace TickTackk\ChangeContentOwner\XF\Service\Post;

use XF\Entity\Thread;
use XF\Entity\Forum;
use XF\Entity\Post;
use XF\Entity\User;

class AuthorChanger extends \XF\Service\AbstractService
{
    /**
     * @var Thread
     */
    protected $thread;

    /**
     * @var Post
     */
    protected $post;

    /**
     * @var Forum
     */
    protected $forum;

    /**
     * @var User
     */
    protected $newAuthor;

    /**
     * @var User
     */
    protected $oldAuthor;

    protected $performValidations = true;

    public function __construct(\XF\App $app, Thread $thread, Post $post, Forum $forum, User $oldAuthor, User $newAuthor)
    {
        parent::__construct($app);
        $this->thread = $thread;
        $this->post = $post;
        $this->forum = $forum;
        $this->oldAuthor = $oldAuthor;
        $this->newAuthor = $newAuthor;
    }

    public function getThread()
    {
        return $this->thread;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function getForum()
    {
        return $this->forum;
    }

    public function getNewAuthor()
    {
        return $this->newAuthor;
    }

    public function getOldAuthor()
    {
        return $this->oldAuthor;
    }

    public function changeAuthor()
    {
        $oldAuthor = $this->getOldAuthor();
        $newAuthor = $this->getNewAuthor();
        $thread = $this->getThread();
        $post = $this->getPost();
        $forum = $this->getForum();

        $db = $this->db();
        $db->beginTransaction();

        $post->user_id = $newAuthor->user_id;
        $post->username = $newAuthor->username;

        if (!$post->preSave())
        {
            throw new \XF\PrintableException($thread->getErrors());
        }
        $post->save();

        $thread->rebuildCounters();
        if (!$thread->preSave())
        {
            throw new \XF\PrintableException($thread->getErrors());
        }
        $thread->save();

        $forum->rebuildLastPost();
        if (!$forum->preSave())
        {
            throw new \XF\PrintableException($thread->getErrors());
        }
        $forum->save();

        if ($post->isVisible())
        {
            $this->adjustUserMessageCountIfNeeded($thread, $oldAuthor, -1);
            $this->adjustThreadUserPostCount($thread, $oldAuthor, -1);

            $this->adjustUserMessageCountIfNeeded($thread, $newAuthor, 1);
            $this->adjustThreadUserPostCount($thread, $newAuthor, 1);
        }

        $db->commit();
    }

    protected function adjustUserMessageCountIfNeeded(Thread $thread, User $user, $amount)
    {
        if ($user->user_id
            && !empty($thread->Forum->count_messages)
            && $thread->discussion_state == 'visible'
        )
        {
            $this->db()->query("
				UPDATE xf_user
				SET message_count = GREATEST(0, message_count + ?)
				WHERE user_id = ?
			", [$amount, $user->user_id]);
        }
    }

    protected function adjustThreadUserPostCount(Thread $thread, User $user, $amount)
    {
        if ($user->user_id)
        {
            $db = $this->db();

            if ($amount > 0)
            {
                $db->insert('xf_thread_user_post', [
                    'thread_id' => $thread->thread_id,
                    'user_id' => $user->user_id,
                    'post_count' => $amount
                ], false, 'post_count = post_count + VALUES(post_count)');
            }
            else
            {
                $existingValue = $db->fetchOne("
					SELECT post_count
					FROM xf_thread_user_post
					WHERE thread_id = ?
						AND user_id = ?
				", [$thread->thread_id, $user->user_id]);
                if ($existingValue !== null)
                {
                    $newValue = $existingValue + $amount;
                    if ($newValue <= 0)
                    {
                        $this->db()->delete('xf_thread_user_post',
                            'thread_id = ? AND user_id = ?', [$thread->thread_id, $user->user_id]
                        );
                    }
                    else
                    {
                        $this->db()->update('xf_thread_user_post',
                            ['post_count' => $newValue],
                            'thread_id = ? AND user_id = ?', [$thread->thread_id, $user->user_id]
                        );
                    }
                }
            }
        }
    }
}