ALTER TABLE `glpi_plugin_domain`
  ADD `helpdesk_visible` INT(11) NOT NULL DEFAULT '1',
  ADD `date_mod` DATETIME DEFAULT NULL;