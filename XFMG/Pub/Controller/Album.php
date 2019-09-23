<?php

namespace TickTackk\ChangeContentOwner\XFMG\Pub\Controller;

use TickTackk\ChangeContentOwner\Pub\Controller\ContentTrait;
use TickTackk\ChangeContentOwner\Service\Content\EditorInterface as EditorSvcInterface;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Error as ErrorReply;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\View as ViewReply;
use XFMG\Entity\Album as AlbumEntity;
use XFMG\Service\Album\Editor as AlbumEditorSvc;

/**
 * Class Album
 *
 * @package TickTackk\ChangeContentOwner\XFMG\Pub\Controller
 */
class Album extends XFCP_Album
{
    use ContentTrait;

    /**
     * @param ParameterBag $parameterBag
     *
     * @return RedirectReply|ViewReply
     * @throws ExceptionReply
     */
    public function actionChangeOwner(ParameterBag $parameterBag)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $album = $this->assertViewableAlbum($parameterBag->album_id);

        return $this->getChangeContentOwnerPlugin()->actionChangeOwner(
            $album,
            'TickTackk\ChangeContentOwner\XFMG:Album\OwnerChanger',
            'XFMG:Album'
        );
    }

    /**
     * @param ParameterBag $params
     *
     * @return ErrorReply|RedirectReply|ViewReply
     * @throws \Exception
     */
    public function actionEdit(ParameterBag $params)
    {
        $reply = parent::actionEdit($params);

        $this->getChangeContentOwnerPlugin()->extendContentEditAction(
            $reply,
            'album'
        );

        return $reply;
    }

    /**
     * @param AlbumEntity $album
     *
     * @return EditorSvcInterface|AlbumEditorSvc
     * @throws ExceptionReply
     */
    protected function setupAlbumEdit(AlbumEntity $album)
    {
        /** @var AlbumEditorSvc|EditorSvcInterface $editor */
        $editor = parent::setupAlbumEdit($album);

        $this->getChangeContentOwnerPlugin()->extendEditorService($album, $editor);

        return $editor;
    }
}