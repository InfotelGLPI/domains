DROP TABLE IF EXISTS `glpi_plugin_domain`;
CREATE TABLE `glpi_plugin_domain` (
  `ID`              INT(11)                 NOT NULL AUTO_INCREMENT,
  `FK_entities`     INT(11)                 NOT NULL DEFAULT '0',
  `name`            VARCHAR(255)
                    COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `others`          VARCHAR(100)
                    COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `deleted`         SMALLINT(6)             NOT NULL DEFAULT '0',
  `type`            TINYINT(4)              NOT NULL DEFAULT '1',
  `creation_date`   DATE                    NOT NULL DEFAULT '0000-00-00',
  `expiration_date` DATE                    NOT NULL DEFAULT '0000-00-00',
  `tech`            INT(4)                  NOT NULL DEFAULT '0',
  `comments`        TEXT,
  `FK_enterprise`   SMALLINT(6)             NOT NULL DEFAULT '0',
  `notes`           LONGTEXT,
  PRIMARY KEY (`ID`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_domain_device`;
CREATE TABLE `glpi_plugin_domain_device` (
  `ID`          INT(11) NOT NULL AUTO_INCREMENT,
  `FK_domain`   INT(11) NOT NULL DEFAULT '0',
  `FK_device`   INT(11) NOT NULL DEFAULT '0',
  `device_type` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `FK_domain` (`FK_domain`, `FK_device`, `device_type`),
  KEY `FK_domain_2` (`FK_domain`),
  KEY `FK_device` (`FK_device`, `device_type`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_domain_type`;
CREATE TABLE `glpi_dropdown_plugin_domain_type` (
  `ID`          INT(11)                 NOT NULL AUTO_INCREMENT,
  `FK_entities` INT(11)                 NOT NULL DEFAULT '0',
  `name`        VARCHAR(255)
                COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comments`    TEXT,
  PRIMARY KEY (`ID`),
  KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_domain_profiles`;
CREATE TABLE `glpi_plugin_domain_profiles` (
  `ID`         INT(11)                 NOT NULL        AUTO_INCREMENT,
  `name`       VARCHAR(255)
               COLLATE utf8_unicode_ci                 DEFAULT NULL,
  `interface`  VARCHAR(50)
               COLLATE utf8_unicode_ci NOT NULL        DEFAULT 'domain',
  `is_default` SMALLINT(6)             NOT NULL        DEFAULT '0',
  `domain`     CHAR(1)
               COLLATE utf8_unicode_ci                 DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `interface` (`interface`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_domain_config`;
CREATE TABLE `glpi_plugin_domain_config` (
  `ID`    INT(11)                 NOT NULL AUTO_INCREMENT,
  `delay` VARCHAR(50)
          COLLATE utf8_unicode_ci NOT NULL DEFAULT '30',
  PRIMARY KEY (`ID`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_plugin_domain_config` VALUES (1, '30');

DROP TABLE IF EXISTS `glpi_plugin_domain_mailing`;
CREATE TABLE `glpi_plugin_domain_mailing` (
  `ID`        INT(11) NOT NULL        AUTO_INCREMENT,
  `type`      VARCHAR(255)
              COLLATE utf8_unicode_ci DEFAULT NULL,
  `FK_item`   INT(11) NOT NULL        DEFAULT '0',
  `item_type` INT(11) NOT NULL        DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `mailings` (`type`, `FK_item`, `item_type`),
  KEY `type` (`type`),
  KEY `FK_item` (`FK_item`),
  KEY `item_type` (`item_type`),
  KEY `items` (`item_type`, `FK_item`)
)
  ENGINE = MyISAM
  AUTO_INCREMENT = 14
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO glpi_plugin_domain_mailing VALUES ('1', 'domain', '1', '1');
INSERT INTO `glpi_display` (`ID`, `type`, `num`, `rank`, `FK_users`) VALUES (NULL, '4400', '2', '3', '0');
INSERT INTO `glpi_display` (`ID`, `type`, `num`, `rank`, `FK_users`) VALUES (NULL, '4400', '3', '1', '0');
INSERT INTO `glpi_display` (`ID`, `type`, `num`, `rank`, `FK_users`) VALUES (NULL, '4400', '4', '2', '0');
INSERT INTO `glpi_display` (`ID`, `type`, `num`, `rank`, `FK_users`) VALUES (NULL, '4400', '6', '4', '0');
INSERT INTO `glpi_display` (`ID`, `type`, `num`, `rank`, `FK_users`) VALUES (NULL, '4400', '7', '5', '0');