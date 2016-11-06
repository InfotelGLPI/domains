ALTER TABLE `glpi_plugin_domain`
  CHANGE `creation_date` `creation_date` DATE NULL DEFAULT NULL;
UPDATE `glpi_plugin_domain`
SET `creation_date` = NULL
WHERE `creation_date` = '0000-00-00';
ALTER TABLE `glpi_plugin_domain`
  CHANGE `expiration_date` `expiration_date` DATE NULL DEFAULT NULL;
UPDATE `glpi_plugin_domain`
SET `expiration_date` = NULL
WHERE `expiration_date` = '0000-00-00';
ALTER TABLE `glpi_plugin_domain`
  ADD `FK_groups` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_domain_profiles`
  DROP COLUMN `interface`,
  DROP COLUMN `is_default`;
ALTER TABLE `glpi_plugin_domain`
  CHANGE `tech` `FK_users` INT(4);
ALTER TABLE `glpi_plugin_domain_profiles`
  ADD `open_ticket` CHAR(1) DEFAULT NULL;