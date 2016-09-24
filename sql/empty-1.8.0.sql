DROP TABLE IF EXISTS `glpi_plugin_domains_domains`;
CREATE TABLE `glpi_plugin_domains_domains` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `plugin_domains_domaintypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_domains_domaintypes (id)',
   `date_creation` date default NULL,
   `date_expiration` date default NULL,
   `users_id_tech` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   `groups_id_tech` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
   `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
   `comment` text collate utf8_unicode_ci,
   `notepad` longtext collate utf8_unicode_ci,
   `others` varchar(255) collate utf8_unicode_ci default NULL,
   `is_helpdesk_visible` int(11) NOT NULL default '1',
   `date_mod` datetime default NULL,
   `is_deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `plugin_domains_domaintypes_id` (`plugin_domains_domaintypes_id`),
   KEY `users_id_tech` (`users_id_tech`),
   KEY `groups_id_tech` (`groups_id_tech`),
   KEY `suppliers_id` (`suppliers_id`),
   KEY `date_mod` (`date_mod`),
   KEY `is_helpdesk_visible` (`is_helpdesk_visible`),
   KEY `is_deleted` (`is_deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_domains_domains_items`;
CREATE TABLE `glpi_plugin_domains_domains_items` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_domains_domains_id` int(11) NOT NULL default '0',
   `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
   `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   PRIMARY KEY  (`id`),
   UNIQUE KEY `unicity` (`plugin_domains_domains_id`,`itemtype`,`items_id`),
   KEY `FK_device` (`items_id`,`itemtype`),
   KEY `item` (`itemtype`,`items_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_domains_domaintypes`;
CREATE TABLE `glpi_plugin_domains_domaintypes` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_domains_profiles`;
CREATE TABLE `glpi_plugin_domains_profiles` (
   `id` int(11) NOT NULL auto_increment,
   `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   `domains` char(1) collate utf8_unicode_ci default NULL,
   `open_ticket` char(1) collate utf8_unicode_ci default NULL,
   PRIMARY KEY  (`id`),
   KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_domains_configs`;
CREATE TABLE `glpi_plugin_domains_configs` (
   `id` int(11) NOT NULL auto_increment,
   `delay_expired` varchar(50) collate utf8_unicode_ci NOT NULL default '30',
   `delay_whichexpire` varchar(50) collate utf8_unicode_ci NOT NULL default '30',
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_domains_configs` VALUES (1, '30', '30');

INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Alert Domains', 'PluginDomainsDomain', '2010-02-24 21:34:46','',NULL, '2010-02-24 21:34:46');

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginDomainsDomain','2','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginDomainsDomain','3','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginDomainsDomain','4','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginDomainsDomain','6','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginDomainsDomain','7','5','0');