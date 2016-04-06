<?php

class SPODAGORAEXPORTER_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function settings($params)
    {
        $this->setPageTitle('AGORA EXPORTER');
        $this->setPageHeading('AGORA EXPORTER');

        $roomsId = [];
        $groupedSn = [];
        $agora = SPODPUBLIC_BOL_Service::getInstance()->getAgora();

        foreach ($agora as $room)
        {
            $snapshots = SPODAGORAEXPORTER_BOL_Service::getInstance()->snapshootForRoom($room->id);
            $roomsId[] = $room->id;
            if($snapshots)
            {
                $room->snapshots = $snapshots;
            }
        }

        $sn = SPODAGORAEXPORTER_BOL_Service::getInstance()->getSnapshotOfDeletedRooms($roomsId);

        foreach ($sn as $sns)
        {
            $groupedSn[$sns->roomId][] = $sns;
        }

        $this->assign('publicRoom', $agora);
        $this->assign('snapshoots', $groupedSn);

        $deleteUrl = OW::getRouter()->urlFor(__CLASS__, 'export');
        $this->assign('exportUrl', $deleteUrl);

        $showUrl = OW::getRouter()->urlFor(__CLASS__, 'show');
        $this->assign('showUrl', $showUrl);
    }

    public function export()
    {
        $roomId = $_REQUEST["id"];

        $options  = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'));
        $context  = stream_context_create($options);
        $htmlCode = file_get_contents('http://localhost/public-room/' . $roomId, false, $context);
        $room = SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomById($roomId);
        SPODAGORAEXPORTER_BOL_Service::getInstance()->takeSnapshot($roomId,
                                                                   $htmlCode,
                                                                   $room->subject,
                                                                   $room->body,
                                                                   $room->comments,
                                                                   $room->opendata);

        $this->redirect(OW::getRouter()->urlForRoute('spodagoraexporter-settings'));
    }

    public function show()
    {
        $snapshootId = $_REQUEST["id"];
        $snapshoot = SPODAGORAEXPORTER_BOL_Service::getInstance()->getSnapshotById($snapshootId);
        $this->assign('snapshoot', $snapshoot);
    }
}