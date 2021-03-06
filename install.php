<?php

$sql = 'CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'spodagoraexporter_snapshot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `htmlcode` text,
  `dataletsGraph` text,
  `commentsGraph` text,
  `subject` varchar(255),
  `body` varchar(255),
  `comments` int,
  `opendata` int,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8';

OW::getDbo()->query($sql);

OW::getPluginManager()->addPluginSettingsRouteName('spodagoraexporter', 'spodagoraexporter-settings');
