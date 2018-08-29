ALTER TABLE `glpi_plugin_domains_domains`
   ADD COLUMN `automatically_request_whoisdb` INT(11) NOT NULL DEFAULT '1',
   ADD KEY `automatically_request_whoisdb` (`automatically_request_whoisdb`) ;
   
