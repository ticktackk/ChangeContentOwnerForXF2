<?php

namespace TickTackk\ChangeContentOwner\XFMG\Service\Album;

use XF\Service\AbstractService;
use XF\Entity\User;
use XFMG\Entity\Album;
use XFMG\Entity\Category;

class AuthorChanger extends AbstractService
{
    use \XF\Service\ValidateAndSavableTrait;

    /**
     * @var Album
     */
    protected $album;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var User
     */
    protected $newAuthor;

    /**
     * @var User
     */
    protected $oldAuthor;

    protected $performValidations = true;

    public function __construct(/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \XF\App $app, Album $album, User $oldAuthor, User $newAuthor)
    {
        parent::__construct($app);
        $this->category = $album->Category;
        $this->album = $album;
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

    public function getCategory()
    {
        return $this->category;
    }

    public function getAlbum()
    {
        return $this->album;
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
        $album = $this->getAlbum();

        $album->user_id = $newAuthor->user_id;
        $album->username = $newAuthor->username;
    }

    protected function finalSetup()
    {
    }

    protected function _validate()
    {
        $this->finalSetup();

        $newAuthor = $this->getNewAuthor();
        $album = $this->getAlbum();
        $album->preSave();
        $errors = $album->getErrors();

        if ($this->performValidations)
        {
            $canTargetView = \XF::asVisitor($newAuthor, function() use ($album)
            {
                return $album->canView();
            });

            if (!$canTargetView)
            {
                $errors[] = \XF::phraseDeferred('changeContentOwner_new_author_must_be_able_to_view_this_xfmg_album');
            }
        }

        return $errors;
    }

    protected function _save()
    {
        $oldAuthor = $this->getOldAuthor();
        $newAuthor = $this->getNewAuthor();

        $album = $this->getAlbum();

        $db = $this->db();
        $db->beginTransaction();

        $album->save();

        if ($album->isVisible())
        {
            $this->adjustUserAlbumCountIfNeeded($oldAuthor, -1);

            $this->adjustUserAlbumCountIfNeeded($newAuthor, -1);
        }

        if ($album->getOption('log_moderator'))
        {
            $this->app->logger()->logModeratorAction('xfmg_album', $album, 'owner_change');
        }

        $db->commit();

        return $album;
    }

    protected function adjustUserAlbumCountIfNeeded(User $user, $amount)
    {
        $this->db()->query("
            UPDATE xf_user
            SET xfmg_album_count = GREATEST(0, xfmg_album_count + ?)
            WHERE user_id = ?
        ", [$amount, $user->user_id]);
    }
}