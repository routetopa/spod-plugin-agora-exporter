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

        $exportUrl = OW::getRouter()->urlFor(__CLASS__, 'export');
        $this->assign('exportUrl', $exportUrl);

        $showUrl = OW::getRouter()->urlFor(__CLASS__, 'show');
        $this->assign('showUrl', $showUrl);

        $deleteUrl = OW::getRouter()->urlFor(__CLASS__, 'delete');
        $this->assign('deleteUrl', $deleteUrl);

        $downloadUrl = OW::getRouter()->urlFor(__CLASS__, 'download');
        $this->assign('downloadUrl', $downloadUrl);

        $downloadXLSUrl = OW::getRouter()->urlFor(__CLASS__, 'downloadAsXLS');
        $this->assign('downloadAsXLS', $downloadXLSUrl);
    }

    public function export()
    {
        $roomId = $_REQUEST["id"];

        $options  = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'));
        $context  = stream_context_create($options);
        $htmlCode = file_get_contents('http://localhost/public-room/' . $roomId . "?comments_pagination=false", false, $context);
        $room = SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomById($roomId);
        SPODAGORAEXPORTER_BOL_Service::getInstance()->takeSnapshot($roomId,
                                                                   $htmlCode,
            $this->sanitizeInput(json_encode(SPODPUBLIC_CLASS_Graph::getInstance()->getGraph($roomId, "comments"))),
            $this->sanitizeInput(json_encode(SPODPUBLIC_CLASS_Graph::getInstance()->getGraph($roomId, "datalets"))),
            $this->sanitizeInput(json_encode(SPODPUBLIC_CLASS_Graph::getInstance()->getGraph($roomId, "users"))),
            $this->sanitizeInput(json_encode(SPODPUBLIC_CLASS_Graph::getInstance()->getGraph($roomId, "complete"))),
                                                                   $room->subject,
                                                                   $room->body,
                                                                   $room->comments,
                                                                   $room->opendata);

        $this->redirect(OW::getRouter()->urlForRoute('spodagoraexporter-settings'));
    }

    public function show()
    {
        $snapshotId = $_REQUEST["id"];
        $snapshot = SPODAGORAEXPORTER_BOL_Service::getInstance()->getSnapshotById($snapshotId);

        //Init JS CONSTANTS
        $js = UTIL_JsGenerator::composeJsString('
                AGORAEXPORTER.completeGraph = {$complete_graph}
            ', array(
            'complete_graph' => $snapshot->completeGraph
        ));

        OW::getDocument()->addOnloadScript($js);
        OW::getDocument()->addOnloadScript("AGORAEXPORTER.init()");

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagoraexporter')->getStaticJsUrl() . 'agoraexporter.js', 'text/javascript');
    }

    public function delete()
    {
        $snapshotID = $_REQUEST["id"];
        SPODAGORAEXPORTER_BOL_Service::getInstance()->deleteSnapshot($snapshotID);
        $this->redirect(OW::getRouter()->urlForRoute('spodagoraexporter-settings'));
    }

    public function download()
    {
        $snapshotId = $_REQUEST["id"];
        $snapshot = SPODAGORAEXPORTER_BOL_Service::getInstance()->getSnapshotById($snapshotId);

        header('Content-disposition: attachment; filename=spod_public_room.json');
        header('Content-type: application/json');
        echo $snapshot->completeGraph;
        die();
    }
    
    public function downloadAsXLS()
    {
        require_once dirname(__FILE__) . '/../libs/PHPExcel-1.8/Classes/PHPExcel.php';

        $snapshotId = $_REQUEST["id"];
        $snapshot = SPODAGORAEXPORTER_BOL_Service::getInstance()->getSnapshotById($snapshotId);

        $objPHPExcel = new PHPExcel();


        $objPHPExcel->getProperties()->setCreator("ROUTETOPA Project")
            ->setLastModifiedBy("ROUTETOPA Project")
            ->setTitle("Agora Room Snapshot")
            ->setSubject("Agora Room Snapshot")
            ->setDescription("Agora Room Snapshot")
            ->setKeywords("Agora Room Snapshot")
            ->setCategory("Agora Room Snapshot");

        //$raw_data = str_replace('\\\\', '\\', $snapshot->completeGraph);
        $raw_data = $snapshot->completeGraph;
        $data = json_decode($raw_data);

        foreach ($data->nodes as $row => $node)
        {
            $level = 'A';

            if($node->level != null)
            {
                switch ($node->level)
                {
                    case 1 : $level = 'B'; break;
                    case 2 : $level = 'C'; break;
                    case 3 : $level = 'D'; break;
                }
            }

            $cell = $level . ($row+1);
            $date = isset($node->createStamp) ? date("d-F-Y , G:i:s", $node->createStamp) : "";
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell, $node->name . " : " . $node->content . " (".$date.")");

            $datalet_html = "";

            /*if(isset($node->datalet))
            {

                $datalet_html = '<script type="text/javascript" src="https://cdn.jsdelivr.net/webcomponentsjs/0.7.16/webcomponents-lite.min.js"></script>
                          <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.4.min.js"></script>';

                $datalet_html .= '<link rel="import" href="http://deep.routetopa.eu/COMPONENTS/datalets/' . $node->datalet->component . '/' . $node->datalet->component . '.html" />';

                $node->datalet->params = str_replace(array('"[', ']"'), array('[', ']'), $node->datalet->params);
                $datalet_params = json_decode($node->datalet->params);

                $datalet_html .= '<' . $node->datalet->component . ' ';
                $datalet_html .= "fields='[" . $node->datalet->fields . "]' ";
                foreach ($datalet_params as $key => $value)
                {
                    if(!is_array($value))
                    {
                        $datalet_html .= " " . $key . "='" . $value . "' ";
                    }
                    else
                    {
                        $datalet_html .= " " . $key . "='[";
                        foreach ($value as $key => $v)
                        {
                            $datalet_html .= '{"field":"' . $v->field . '",' . '"operation":"' . $v->operation . '"}' . ($key < count($value)-1 ? ',' : '');
                        }
                        $datalet_html .= "]'";
                    }
                }

                $datalet_html .= '></' . $node->datalet->component . '>';
            }*/

        }

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        //$objWriter->save(str_replace('.php', '.xlsx', __FILE__));

        // We'll be outputting an excel file
        header('Content-type: application/vnd.ms-excel');
        // It will be called file.xlsx
        header('Content-Disposition: attachment; filename="public_room.xlsx"');
        // Write file to the browser
        $objWriter->save('php://output');
        die();
    }


    protected function sanitizeInput($str)
    {
        return str_replace("'", "&#39;", !empty($str) ? $str : "");
    }
}