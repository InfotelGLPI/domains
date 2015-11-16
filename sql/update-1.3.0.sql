ALTER TABLE `glpi_plugin_domain` RENAME `glpi_plugin_domains_domains`;
ALTER TABLE `glpi_plugin_domain_device` RENAME `glpi_plugin_domains_domains_items`;
ALTER TABLE `glpi_dropdown_plugin_domain_type` RENAME `glpi_plugin_domains_domaintypes`;
ALTER TABLE `glpi_plugin_domain_profiles` RENAME `glpi_plugin_domains_profiles`;
ALTER TABLE `glpi_plugin_domain_config` RENAME `glpi_plugin_domains_configs`;
DROP TABLE IF EXISTS `glpi_plugin_domain_mailing`;

ALTER TABLE `glpi_plugin_domains_domains` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `recursive` `is_recursive` tinyint(1) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `type` `plugin_domains_domaintypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_domains_domaintypes (id)',
   CHANGE `creation_date` `date_creation` date default NULL,
   CHANGE `expiration_date` `date_expiration` date default NULL,
   CHANGE `FK_users` `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   CHANGE `FK_groups` `groups_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
   CHANGE `FK_enterprise` `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
   CHANGE `others` `others` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `helpdesk_visible` `is_helpdesk_visible` int(11) NOT NULL default '1',
   CHANGE `comments` `comment` text collate utf8_unicode_ci,
   CHANGE `notes` `notepad` longtext collate utf8_unicode_ci,
   CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL default '0',
   ADD INDEX (`name`),
   ADD INDEX (`entities_id`),
   ADD INDEX (`plugin_domains_domaintypes_id`),
   ADD INDEX (`users_id`),
   ADD INDEX (`groups_id`),
   ADD INDEX (`suppliers_id`),
   ADD INDEX (`date_mod`),
   ADD INDEX (`is_helpdesk_visible`),
   ADD INDEX (`is_deleted`);

ALTER TABLE `glpi_plugin_domains_domains_items` 
   DROP INDEX `FK_domain`,
   DROP INDEX `FK_domain_2`,
   DROP INDEX `FK_device`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_domain` `plugin_domains_domains_id` int(11) NOT NULL default '0',
   CHANGE `FK_device` `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
   CHANGE `device_type` `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   ADD UNIQUE `unicity` (`plugin_domains_domains_id`,`itemtype`,`items_id`),
   ADD INDEX `FK_device` (`items_id`,`itemtype`),
   ADD INDEX `item` (`itemtype`,`items_id`);

ALTER TABLE `glpi_plugin_domains_domaintypes` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_domains_profiles` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   CHANGE `domain` `domains` char(1) collate utf8_unicode_ci default NULL,
   CHANGE `open_ticket` `open_ticket` char(1) collate utf8_unicode_ci default NULL,
   ADD INDEX (`profiles_id`);

ALTER TABLE `glpi_plugin_domains_configs` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `delay` `delay_expired` varchar(50) collate utf8_unicode_ci NOT NULL default '30',
   ADD `delay_whichexpire` varchar(50) collate utf8_unicode_ci NOT NULL default '30';

INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Alert Domains', 'PluginDomainsDomain', '2010-02-24 21:34:46','',NULL);