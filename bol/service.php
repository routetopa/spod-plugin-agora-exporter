<?php

class SPODAGORAEXPORTER_BOL_Service
{
    /**
     * Singleton instance.
     *
     * @var ODE_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ODE_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function takeSnapshot($roomId, $htmlCode,
                                 $dataletsGraph,
                                 $subject, $body, $opendata, $comments)
    {
        $snapshot = new SPODAGORAEXPORTER_BOL_Snapshot();
        $snapshot->roomId        = $roomId;
        $snapshot->htmlcode      = $htmlCode;
        $snapshot->dataletsGraph = $dataletsGraph;
        $snapshot->subject       = $subject;
        $snapshot->body          = $body;
        $snapshot->opendata      = $opendata;
        $snapshot->comments      = $comments;
        $snapshot->commentsGraph = $this->createCommentTree($roomId);

        SPODAGORAEXPORTER_BOL_SnapshotDao::getInstance()->save($snapshot);
    }

    public function createCommentTree($roomId)
    {
        $comments = SPODAGORA_BOL_Service::getInstance()->getCommentByParentId($roomId);

        foreach ($comments as &$comment)
        {
            $comment->children = SPODAGORA_BOL_Service::getInstance()->getCommentByParentId($comment->id);
        }

        return json_encode($comments);
    }

    public function getSnapshotById($snapshootId)
    {
        return SPODAGORAEXPORTER_BOL_SnapshotDao::getInstance()->findById($snapshootId);
    }

    public function snapshootForRoom($roomId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('roomId', $roomId);

        return SPODAGORAEXPORTER_BOL_SnapshotDao::getInstance()->findListByExample($ex);
    }

    public function getSnapshotOfDeletedRooms($roomIds)
    {
        if(count($roomIds) > 0)
        {
            $ex = new OW_Example();
            $ex->andFieldNotInArray('roomId', $roomIds);
            return SPODAGORAEXPORTER_BOL_SnapshotDao::getInstance()->findListByExample($ex);
        }
        else
        {
            return SPODAGORAEXPORTER_BOL_SnapshotDao::getInstance()->findAll();
        }
    }

    public function deleteSnapshot($roomId)
    {
        return SPODAGORAEXPORTER_BOL_SnapshotDao::getInstance()->deleteById($roomId);
    }
}