<?php

namespace TickTackk\ChangeContentOwner\XF\Service\Post;

use XF\Service\AbstractService;
use XF\Entity\Thread;
use XF\Entity\Forum;
use XF\Entity\Post;
use XF\Entity\User;

class AuthorChanger extends AbstractService
{
    use \XF\Service\ValidateAndSavableTrait;

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

    /**
     * AuthorChanger constructor.
     * @param \XF\App $app
     * @param Post $post
     * @param User $oldAuthor
     * @param User $newAuthor
     */
    public function __construct(/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \XF\App $app, Post $post, User $oldAuthor, User $newAuthor)
    {
        parent::__construct($app);
        $this->post = $post;
        $this->thread = $post->Thread;
        $this->forum = $post->Thread->Forum;
        $this->oldAuthor = $oldAuthor;
        $this->newAuthor = $newAuthor;
    }

    public function setPerformValidations($perform)
    {
        $this->performValidations = (bool)$perform;
    }

    /**
     * @return bool
     */
    public function getPerformValidations()
    {
        return $this->performValidations;
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
        $newAuthor = $this->getNewAuthor();
        $post = $this->getPost();
        $thread = $this->getThread();
        $forum = $this->getForum();

        $post->user_id = $newAuthor->user_id;
        $post->username = $newAuthor->username;

        if ($thread->last_post_id === $post->post_id)
        {
            $thread->last_post_user_id = $post->user_id;
            $thread->last_post_username = $post->username;
        }

        if ($forum->last_post_id === $post->post_id)
        {
            $forum->last_post_user_id = $post->user_id;
            $forum->last_post_username = $post->username;
        }
    }

    protected function finalSetup()
    {
    }

    protected function _validate()
    {
        $this->finalSetup();

        $newAuthor = $this->getNewAuthor();
        $thread = $this->getThread();
        $post = $this->getPost();
        $forum = $this->getForum();

        $post->preSave();
        $postErrors = $post->getErrors();

        $thread->preSave();
        $threadErrors = $thread->getErrors();

        $forum->preSave();
        $forumErrors = $forum->getErrors();

        $errors = array_merge($forumErrors, $threadErrors, $postErrors);

        if ($this->performValidations)
        {
            $canTargetView = \XF::asVisitor($newAuthor, function() use ($post)
            {
                return $post->canView();
            });

            if (!$canTargetView)
            {
                $errors[] = \XF::phraseDeferred('changeContentOwner_new_author_must_be_able_to_view_this_post');
            }
        }

        return $errors;
    }

    protected function _save()
    {
        $oldAuthor = $this->getOldAuthor();
        $newAuthor = $this->getNewAuthor();
        $thread = $this->getThread();
        $post = $this->getPost();
        $forum = $this->getForum();

        $db = $this->db();
        $db->beginTransaction();

        $post->save();
        $thread->save();
        $forum->save();

        if ($post->isVisible())
        {
            $this->adjustUserMessageCountIfNeeded($thread, $oldAuthor, -1);
            $this->adjustThreadUserPostCount($thread, $oldAuthor, -1);

            $this->adjustUserMessageCountIfNeeded($thread, $newAuthor, 1);
            $this->adjustThreadUserPostCount($thread, $newAuthor, 1);
        }

        if ($post->getOption('log_moderator'))
        {
            $this->app->logger()->logModeratorAction('post', $post, 'author_change');
        }

        $db->commit();

        return $post;
    }

    /**
     * @param Thread $thread
     * @param User $user
     * @param $amount
     */
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

    /**
     * @param Thread $thread
     * @param User $user
     * @param $amount
     */
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