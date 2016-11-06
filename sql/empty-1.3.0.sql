DROP TABLE IF EXISTS `glpi_plugin_domains_domains`;
CREATE TABLE `glpi_plugin_domains_domains` (
  `id`                            INT(11)    NOT NULL     AUTO_INCREMENT,
  `entities_id`                   INT(11)    NOT NULL     DEFAULT '0',
  `is_recursive`                  TINYINT(1) NOT NULL     DEFAULT '0',
  `name`                          VARCHAR(255)
                                  COLLATE utf8_unicode_ci DEFAULT NULL,
  `plugin_domains_domaintypes_id` INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_domains_domaintypes (id)',
  `date_creation`                 DATE                    DEFAULT NULL,
  `date_expiration`               DATE                    DEFAULT NULL,
  `users_id`                      INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_users (id)',
  `groups_id`                     INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_groups (id)',
  `suppliers_id`                  INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_suppliers (id)',
  `comment`                       TEXT COLLATE utf8_unicode_ci,
  `notepad`                       LONGTEXT COLLATE utf8_unicode_ci,
  `others`                        VARCHAR(255)
                                  COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_helpdesk_visible`           INT(11)    NOT NULL     DEFAULT '1',
  `date_mod`                      DATETIME                DEFAULT NULL,
  `is_deleted`                    TINYINT(1) NOT NULL     DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `entities_id` (`entities_id`),
  KEY `plugin_domains_domaintypes_id` (`plugin_domains_domaintypes_id`),
  KEY `users_id` (`users_id`),
  KEY `groups_id` (`groups_id`),
  KEY `suppliers_id` (`suppliers_id`),
  KEY `date_mod` (`date_mod`),
  KEY `is_helpdesk_visible` (`is_helpdesk_visible`),
  KEY `is_deleted` (`is_deleted`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_domains_domains_items`;
CREATE TABLE `glpi_plugin_domains_domains_items` (
  `id`                        INT(11)                 NOT NULL AUTO_INCREMENT,
  `plugin_domains_domains_id` INT(11)                 NOT NULL DEFAULT '0',
  `items_id`                  INT(11)                 NOT NULL DEFAULT '0'
  COMMENT 'RELATION to various tables, according to itemtype (id)',
  `itemtype`                  VARCHAR(100)
                              COLLATE utf8_unicode_ci NOT NULL
  COMMENT 'see .class.php file',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_domains_domains_id`, `itemtype`, `items_id`),
  KEY `FK_device` (`items_id`, `itemtype`),
  KEY `item` (`itemtype`, `items_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_domains_domaintypes`;
CREATE TABLE `glpi_plugin_domains_domaintypes` (
  `id`          INT(11) NOT NULL        AUTO_INCREMENT,
  `entities_id` INT(11) NOT NULL        DEFAULT '0',
  `name`        VARCHAR(255)
                COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment`     TEXT COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_domains_profiles`;
CREATE TABLE `glpi_plugin_domains_profiles` (
  `id`          INT(11) NOT NULL        AUTO_INCREMENT,
  `profiles_id` INT(11) NOT NULL        DEFAULT '0'
  COMMENT 'RELATION to glpi_profiles (id)',
  `domains`     CHAR(1)
                COLLATE utf8_unicode_ci DEFAULT NULL,
  `open_ticket` CHAR(1)
                COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profiles_id` (`profiles_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_domains_configs`;
CREATE TABLE `glpi_plugin_domains_configs` (
  `id`                INT(11)                 NOT NULL AUTO_INCREMENT,
  `delay_expired`     VARCHAR(50)
                      COLLATE utf8_unicode_ci NOT NULL DEFAULT '30',
  `delay_whichexpire` VARCHAR(50)
                      COLLATE utf8_unicode_ci NOT NULL DEFAULT '30',
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_plugin_domains_configs` VALUES (1, '30', '30');

INSERT INTO `glpi_notificationtemplates`
VALUES (NULL, 'Alert Domains', 'PluginDomainsDomain', '2010-02-24 21:34:46', '');

INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginDomainsDomain', '2', '3', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginDomainsDomain', '3', '1', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginDomainsDomain', '4', '2', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginDomainsDomain', '6', '4', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginDomainsDomain', '7', '5', '0');