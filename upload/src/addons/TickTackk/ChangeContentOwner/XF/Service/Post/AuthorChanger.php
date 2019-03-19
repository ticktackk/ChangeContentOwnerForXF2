<?php

namespace TickTackk\ChangeContentOwner\XF\Service\Post;

use TickTackk\ChangeContentOwner\Service\ContentTrait;
use XF\Entity\Forum;
use XF\Entity\Post;
use XF\Entity\Thread;
use XF\Entity\User;
use XF\Service\AbstractService;
use XF\Service\ValidateAndSavableTrait;

/**
 * Class AuthorChanger
 *
 * @package TickTackk\ChangeContentOwner
 */
class AuthorChanger extends AbstractService
{
    use ValidateAndSavableTrait, ContentTrait;

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

    /**
     * @var bool
     */
    protected $performValidations = true;

    /**
     * AuthorChanger constructor.
     *
     * @param \XF\App $app
     * @param Post    $post
     * @param User    $newAuthor
     */
    public function __construct(/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \XF\App $app, Post $post, User $newAuthor)
    {
        parent::__construct($app);
        $this->post = $post;
        $this->thread = $post->Thread;
        $this->forum = $post->Thread->Forum;
        $this->oldAuthor = $post->User;
        $this->newAuthor = $newAuthor;
    }

    /**
     * @return bool
     */
    public function getPerformValidations()
    {
        return $this->performValidations;
    }

    /**
     * @param $perform
     */
    public function setPerformValidations($perform)
    {
        $this->performValidations = (bool)$perform;
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

    /**
     * @return User
     */
    public function getNewAuthor()
    {
        return $this->newAuthor;
    }

    /**
     * @return Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @return Thread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @return Forum
     */
    public function getForum()
    {
        return $this->forum;
    }

    /**
     * @return array
     * @throws \Exception
     */
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
            $canTargetView = \XF::asVisitor($newAuthor, function () use ($post)
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

    protected function finalSetup()
    {
    }

    /**
     * @return Post
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
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

        if (\XF::$versionId >= 2010010)
        {
            /** @noinspection PhpUndefinedFieldInspection */
            if ($reactionContent = $post->Reactions[$newAuthor->user_id])
            {
                /** @noinspection PhpUndefinedMethodInspection */
                $reactionContent->delete();
            }
        }
        else if ($likedContent = $post->Likes[$newAuthor->user_id])
        {
            /** @noinspection PhpUndefinedMethodInspection */
            $likedContent->delete();
        }

        if ($post->isVisible())
        {
            if ($oldAuthor)
            {
                $this->adjustUserMessageCountIfNeeded($thread, $oldAuthor, -1);
                $this->adjustThreadUserPostCount($thread, $oldAuthor, -1);
            }

            $this->adjustUserMessageCountIfNeeded($thread, $newAuthor, 1);
            $this->adjustThreadUserPostCount($thread, $newAuthor, 1);

            $this->updateNewsFeed($post, $oldAuthor, $newAuthor);
        }

        if ($post->getOption('log_moderator'))
        {
            $this->app->logger()->logModeratorAction('post', $post, 'author_change');
        }

        $db->commit();

        return $post;
    }

    /**
     * @return User
     */
    public function getOldAuthor()
    {
        return $this->oldAuthor;
    }

    /**
     * @param Thread $thread
     * @param User   $user
     * @param int $amount
     *
     * @throws \XF\Db\Exception
     */
    protected function adjustUserMessageCountIfNeeded(Thread $thread, User $user, $amount)
    {
        if ($user->user_id && $thread->Forum->count_messages && $thread->discussion_state === 'visible')
        {
            if ($amount < 0)
            {
                $func = 'LEAST';
                $sign = '-';
            }
            else
            {
                $func = 'GREATEST';
                $sign = '+';
            }
            $this->db()->query("
				UPDATE xf_user
				SET message_count = {$func}(0, message_count {$sign} ?)
				WHERE user_id = ?
			", [$amount, $user->user_id]);
        }
    }

    /**
     * @param Thread $thread
     * @param User   $user
     * @param int $amount
     */
    protected function adjustThreadUserPostCount(Thread $thread, User $user, $amount)
    {
        if ($user->user_id)
        {
            $db = $this->db();

            if (!$amount)
            {
                $db->insert('xf_thread_user_post', [
                    'thread_id' => $thread->thread_id,
                    'user_id' => $user->user_id,
                    'post_count' => $amount
                ], false, 'post_count = post_count + VALUES(post_count)');
            }
            else
            {
                $existingValue = $db->fetchOne('
					SELECT post_count
					FROM xf_thread_user_post
					WHERE thread_id = ?
						AND user_id = ?
				', [$thread->thread_id, $user->user_id]);

                if ($existingValue !== null)
                {
                    $newValue = $existingValue + $amount;
                    if ($newValue <= 0)
                    {
                        $this->db()->delete('xf_thread_user_post',
                            'thread_id = ? AND user_id = ?', [
                                $thread->thread_id,
                                $user->user_id
                            ]
                        );
                    }
                    else
                    {
                        $this->db()->update('xf_thread_user_post',
                            ['post_count' => $newValue],
                            'thread_id = ? AND user_id = ?', [
                                $thread->thread_id,
                                $user->user_id
                            ]
                        );
                    }
                }
            }
        }
    }
}