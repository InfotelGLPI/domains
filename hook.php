<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 domains plugin for GLPI
 Copyright (C) 2009-2016 by the domains Development Team.

 https://github.com/InfotelGLPI/domains
 -------------------------------------------------------------------------

 LICENSE

 This file is part of domains.

 domains is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 domains is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with domains. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * @return bool
 */
function plugin_domains_install() {
   global $DB;

   include_once(GLPI_ROOT . "/plugins/domains/inc/profile.class.php");

   $install  = false;
   $update78 = false;

   if (!$DB->tableExists("glpi_plugin_domain") && !$DB->tableExists("glpi_plugin_domains_domains")) {

      $install = true;
      $DB->runFile(GLPI_ROOT . "/plugins/domains/sql/empty-2.0.2.sql");

   } else if ($DB->tableExists("glpi_plugin_domain") && !$DB->fieldExists("glpi_plugin_domain", "recursive")) {

      $update78 = true;
      $DB->runFile(GLPI_ROOT . "/plugins/domains/sql/update-1.1.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/domains/sql/update-1.2.0.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/domains/sql/update-1.2.1.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/domains/sql/update-1.3.0.sql");

   } else if ($DB->tableExists("glpi_plugin_domain_profiles") && $DB->fieldExists("glpi_plugin_domain_profiles", "interface")) {

      $update78 = true;
      $DB->runFile(GLPI_ROOT . "/plugins/domains/sql/update-1.2.0.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/domains/sql/update-1.2.1.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/domains/sql/update-1.3.0.sql");

   } else if ($DB->tableExists("glpi_plugin_domain") && !$DB->fieldExists("glpi_plugin_domain", "helpdesk_visible")) {

      $update78 = true;
      $DB->runFile(GLPI_ROOT . "/plugins/domains/sql/update-1.2.1.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/domains/sql/update-1.3.0.sql");

   } else if (!$DB->tableExists("glpi_plugin_domains_domains")) {

      $update78 = true;
      $DB->runFile(GLPI_ROOT . "/plugins/domains/sql/update-1.3.0.sql");
   }

   //from 1.3 version
   if ($DB->tableExists("glpi_plugin_domains_domains")
       && !$DB->fieldExists("glpi_plugin_domains_domains", "users_id_tech")) {
      $DB->runFile(GLPI_ROOT . "/plugins/domains/sql/update-1.5.0.sql");
   }

   if (!$DB->fieldExists("glpi_plugin_domains_domaintypes", "is_recursive")) {
      $DB->runFile(GLPI_ROOT . "/plugins/domains/sql/update-2.0.2.sql");
   }


      if ($DB->tableExists("glpi_plugin_domains_profiles")) {

      $notepad_tables = ['glpi_plugin_domains_domains'];

      foreach ($notepad_tables as $t) {
         // Migrate data
         if ($DB->fieldExists($t, 'notepad')) {
            $query = "SELECT id, notepad
                      FROM `$t`
                      WHERE notepad IS NOT NULL
                            AND notepad <>'';";
            foreach ($DB->request($query) as $data) {
               $iq = "INSERT INTO `glpi_notepads`
                             (`itemtype`, `items_id`, `content`, `date`, `date_mod`)
                              VALUES ('PluginDomainsDomain', '" . $data['id'] . "',
                              '" . addslashes($data['notepad']) . "', NOW(), NOW())";
               $DB->queryOrDie($iq, "0.85 migrate notepad data");
            }
            $query = "ALTER TABLE `glpi_plugin_domains_domains` DROP COLUMN `notepad`;";
            $DB->query($query);
         }
      }
   }

   if ($install || $update78) {

      //Do One time on 0.78
      $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginDomainsDomain' AND `name` = 'Alert Domains'";
      $result = $DB->query($query_id) or die ($DB->error());
      $itemtype = $DB->result($result, 0, 'id');

      $query = "INSERT INTO `glpi_notificationtemplatetranslations`
                                 VALUES(NULL, " . $itemtype . ", '','##domain.action## : ##domain.entity##',
                        '##lang.domain.entity## :##domain.entity##
   ##FOREACHdomains##
   ##lang.domain.name## : ##domain.name## - ##lang.domain.dateexpiration## : ##domain.dateexpiration##
   ##ENDFOREACHdomains##',
                        '&lt;p&gt;##lang.domain.entity## :##domain.entity##&lt;br /&gt; &lt;br /&gt;
                        ##FOREACHdomains##&lt;br /&gt;
                        ##lang.domain.name##  : ##domain.name## - ##lang.domain.dateexpiration## :  ##domain.dateexpiration##&lt;br /&gt; 
                        ##ENDFOREACHdomains##&lt;/p&gt;');";
      $DB->query($query);

      /***** Begin Alert Expired Domains *****/
      $query = "INSERT INTO `glpi_notifications`(`name`, `entities_id`, `itemtype`, `event`, `is_recursive`, `is_active`) 
                VALUES ('Alert Expired Domains', 0, 'PluginDomainsDomain', 'ExpiredDomains', 1, 1);";
      $DB->query($query);
      //retrieve notification id
      $query_id = "SELECT `id` FROM `glpi_notifications`
               WHERE `name` = 'Alert Expired Domains' AND `itemtype` = 'PluginDomainsDomain' AND `event` = 'ExpiredDomains'";
      $result = $DB->query($query_id) or die ($DB->error());
      $notification = $DB->result($result, 0, 'id');

      $query = "INSERT INTO `glpi_notifications_notificationtemplates` (`notifications_id`, `mode`, `notificationtemplates_id`) 
               VALUES (" . $notification . ", 'mailing', " . $itemtype . ");";
      $DB->query($query);
      /***** End Alert Expired Domains *****/

      /***** Begin Alert Domains Which Expire *****/
      $query = "INSERT INTO `glpi_notifications`(`name`, `entities_id`, `itemtype`, `event`, `is_recursive`, `is_active`) 
               VALUES ('Alert Domains Which Expire', 0, 'PluginDomainsDomain', 'DomainsWhichExpire', 1, 1);";
      $DB->query($query);
      //retrieve notification id
      $query_id = "SELECT `id` FROM `glpi_notifications`
               WHERE `name` = 'Alert Domains Which Expire' AND `itemtype` = 'PluginDomainsDomain' AND `event` = 'DomainsWhichExpire'";
      $result = $DB->query($query_id) or die ($DB->error());
      $notification = $DB->result($result, 0, 'id');

      $query = "INSERT INTO `glpi_notifications_notificationtemplates` (`notifications_id`, `mode`, `notificationtemplates_id`) 
               VALUES (" . $notification . ", 'mailing', " . $itemtype . ");";
      $DB->query($query);
      /***** End Alert Domains Which Expire *****/
   }
   if ($update78) {
      $query_  = "SELECT *
            FROM `glpi_plugin_domains_profiles` ";
      $result_ = $DB->query($query_);
      if ($DB->numrows($result_) > 0) {

         while ($data = $DB->fetchArray($result_)) {
            $query = "UPDATE `glpi_plugin_domains_profiles`
                  SET `profiles_id` = '" . $data["id"] . "'
                  WHERE `id` = '" . $data["id"] . "';";
            $DB->query($query);

         }
      }

      $query = "ALTER TABLE `glpi_plugin_domains_profiles`
               DROP `name` ;";
      $DB->query($query);

      Plugin::migrateItemType(
         [4400 => 'PluginDomainsDomain'],
         ["glpi_savedsearches", "glpi_savedsearches_users", "glpi_displaypreferences",
               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"],
         ["glpi_plugin_domains_domains_items"]);

      Plugin::migrateItemType(
         [1200 => "PluginAppliancesAppliance", 1300 => "PluginWebapplicationsWebapplication"],
         ["glpi_plugin_domains_domains_items"]);
   }

   CronTask::Register('PluginDomainsDomain', 'DomainsAlert', DAY_TIMESTAMP);

   PluginDomainsProfile::initProfile();
   PluginDomainsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   $migration = new Migration("1.8.0");
   $migration->dropTable('glpi_plugin_domains_profiles');
   return true;
}

/**
 * @return bool
 */
function plugin_domains_uninstall() {
   global $DB;

   include_once(GLPI_ROOT . "/plugins/domains/inc/profile.class.php");
   include_once(GLPI_ROOT . "/plugins/domains/inc/menu.class.php");

   $tables = ["glpi_plugin_domains_domains",
                   "glpi_plugin_domains_domains_items",
                   "glpi_plugin_domains_domaintypes",
                   "glpi_plugin_domains_profiles",
                   "glpi_plugin_domains_configs"];

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   //old versions
   $tables = ["glpi_plugin_domain",
                   "glpi_plugin_domain_profiles",
                   "glpi_plugin_domain_device",
                   "glpi_dropdown_plugin_domain_type",
                   "glpi_plugin_domains_config",
                   "glpi_plugin_domain_mailing",
                   "glpi_plugin_domains_default"];

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   $notif   = new Notification();
   $options = ['itemtype' => 'PluginDomainsDomain',
                    'event'    => 'ExpiredDomains',
                    'FIELDS'   => 'id'];
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = ['itemtype' => 'PluginDomainsDomain',
                    'event'    => 'DomainsWhichExpire',
                    'FIELDS'   => 'id'];
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }

   //templates
   $template    = new NotificationTemplate();
   $translation = new NotificationTemplateTranslation();
   $notif_template = new Notification_NotificationTemplate();
   $options     = ['itemtype' => 'PluginDomainsDomain',
                        'FIELDS'   => 'id'];
   foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
      $options_template = ['notificationtemplates_id' => $data['id'],
                                'FIELDS'                   => 'id'];

      foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
         $translation->delete($data_template);
      }
      $template->delete($data);

      foreach ($DB->request('glpi_notifications_notificationtemplates', $options_template) as $data_template) {
         $notif_template->delete($data_template);
      }
   }

   $tables_glpi = ["glpi_displaypreferences",
                        "glpi_documents_items",
                        "glpi_savedsearches",
                        "glpi_logs",
                        "glpi_items_tickets",
                        "glpi_contracts_items",
                        "glpi_notepads"];

   foreach ($tables_glpi as $table_glpi) {
      $DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` LIKE 'PluginDomainsDomain%';");
   }

   if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(['itemtype' => 'PluginDomainsDomain']);
   }

   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginDomainsProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(['name' => $right['field']]);
   }
   PluginDomainsMenu::removeRightsFromSession();

   PluginDomainsProfile::removeRightsFromSession();

   return true;
}

function plugin_domains_postinit() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['domains'] = [];

   foreach (PluginDomainsDomain::getTypes(true) as $type) {

      $PLUGIN_HOOKS['item_purge']['domains'][$type]
         = ['PluginDomainsDomain_Item', 'cleanForItem'];

      CommonGLPI::registerStandardTab($type, 'PluginDomainsDomain_Item');
   }
}

/**
 * @param $types
 *
 * @return mixed
 */
function plugin_domains_AssignToTicket($types) {

   if (Session::haveRight("plugin_domains_open_ticket", "1")) {
      $types['PluginDomainsDomain'] = PluginDomainsDomain::getTypeName(2);
   }
   return $types;
}


// Define dropdown relations
/**
 * @return array
 */
function plugin_domains_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("domains")) {
      return ["glpi_plugin_domains_domaintypes" => ["glpi_plugin_domains_domains" => "plugin_domains_domaintypes_id"],
                   "glpi_users"                      => ["glpi_plugin_domains_domains" => "users_id_tech"],
                   "glpi_groups"                     => ["glpi_plugin_domains_domains" => "groups_id_tech"],
                   "glpi_suppliers"                  => ["glpi_plugin_domains_domains" => "suppliers_id"],
                   "glpi_plugin_domains_domains"     => ["glpi_plugin_domains_domains_items" => "plugin_domains_domains_id"],
                   "glpi_entities"                   => ["glpi_plugin_domains_domains"     => "entities_id",
                                                              "glpi_plugin_domains_domaintypes" => "entities_id"]];
   } else {
      return [];
   }
}

// Define Dropdown tables to be manage in GLPI :
/**
 * @return array
 */
function plugin_domains_getDropdown() {

   $plugin = new Plugin();

   if ($plugin->isActivated("domains")) {
      return ['PluginDomainsDomainType' => PluginDomainsDomainType::getTypeName(2)];
   } else {
      return [];
   }
}

////// SEARCH FUNCTIONS ///////() {

/**
 * @param $itemtype
 *
 * @return array
 */
function plugin_domains_getAddSearchOptions($itemtype) {

   $sopt = [];

   if (in_array($itemtype, PluginDomainsDomain::getTypes(true))) {
      if (Session::haveRight("plugin_domains", READ)) {
         $sopt[4410]['table']         = 'glpi_plugin_domains_domains';
         $sopt[4410]['field']         = 'name';
         $sopt[4410]['name']          = PluginDomainsDomain::getTypeName(2) . " - " .
                                        __('Name');
         $sopt[4410]['forcegroupby']  = true;
         $sopt[4410]['datatype']      = 'itemlink';
         $sopt[4410]['itemlink_type'] = 'PluginDomainsDomain';
         $sopt[4410]['massiveaction'] = false;
         $sopt[4410]['joinparams']    = ['beforejoin'
                                              => ['table'      => 'glpi_plugin_domains_domains_items',
                                                       'joinparams' => ['jointype' => 'itemtype_item']]];

         $sopt[4411]['table']         = 'glpi_plugin_domains_domaintypes';
         $sopt[4411]['field']         = 'name';
         $sopt[4411]['name']          = PluginDomainsDomain::getTypeName(2) . " - " .
                                        PluginDomainsDomainType::getTypeName(1);
         $sopt[4411]['forcegroupby']  = true;
         $sopt[4411]['datatype']      = 'dropdown';
         $sopt[4411]['massiveaction'] = false;
         $sopt[4411]['joinparams']    = ['beforejoin' => [
            ['table'      => 'glpi_plugin_domains_domains',
                  'joinparams' => $sopt[4410]['joinparams']]]];
      }
   }
   return $sopt;
}

/**
 * @param $type
 * @param $ID
 * @param $data
 * @param $num
 *
 * @return string
 */
function plugin_domains_displayConfigItem($type, $ID, $data, $num) {

   $searchopt =& Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   switch ($table . '.' . $field) {
      case "glpi_plugin_domains_domains.date_expiration" :
         if ($data[$num][0]['name'] <= date('Y-m-d') && !empty($data[$num][0]['name'])) {
            return " class=\"deleted\" ";
         }
         break;
   }
   return "";
}

/**
 * @param $type
 * @param $ID
 * @param $data
 * @param $num
 *
 * @return date|string|translated
 */
function plugin_domains_giveItem($type, $ID, $data, $num) {
   global $DB;

   $searchopt =& Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   switch ($table . '.' . $field) {
      case "glpi_plugin_domains_domains.date_expiration" :
         if (empty($data[$num][0]['name'])) {
            $out = __('Does not expire', 'domains');
         } else {
            $out = Html::convDate($data[$num][0]['name']);
         }
         return $out;
         break;
      case "glpi_plugin_domains_domains_items.items_id" :
         $query_device  = "SELECT DISTINCT `itemtype`
                     FROM `glpi_plugin_domains_domains_items`
                     WHERE `plugin_domains_domains_id` = '" . $data['id'] . "'
                     ORDER BY `itemtype`";
         $result_device = $DB->query($query_device);
         $number_device = $DB->numrows($result_device);
         $out           = '';
         $domains       = $data['id'];
         if ($number_device > 0) {
            for ($i = 0; $i < $number_device; $i++) {
               $column   = "name";
               $itemtype = $DB->result($result_device, $i, "itemtype");

               if (!class_exists($itemtype)) {
                  continue;
               }
               $item = new $itemtype();
               $dbu  = new DbUtils();
               if ($item->canView()) {
                  $table_item = $dbu->getTableForItemType($itemtype);
                  $query      = "SELECT `" . $table_item . "`.*, `glpi_entities`.`ID` AS entity "
                                . " FROM `glpi_plugin_domains_domains_items`, `" . $table_item
                                . "` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `" . $table_item . "`.`entities_id`) "
                                . " WHERE `" . $table_item . "`.`id` = `glpi_plugin_domains_domains_items`.`items_id`
                  AND `glpi_plugin_domains_domains_items`.`itemtype` = '$itemtype'
                  AND `glpi_plugin_domains_domains_items`.`plugin_domains_domains_id` = '" . $domains . "' "
                                . $dbu->getEntitiesRestrictRequest(" AND ", $table_item, '', '', $item->maybeRecursive());

                  if ($item->maybeTemplate()) {
                     $query .= " AND `" . $table_item . "`.`is_template` = '0'";
                  }
                  $query .= " ORDER BY `glpi_entities`.`completename`, `" . $table_item . "`.`$column`";

                  if ($result_linked = $DB->query($query)) {
                     if ($DB->numrows($result_linked)) {
                        $item = new $itemtype();
                        while ($data = $DB->fetchAssoc($result_linked)) {
                           if ($item->getFromDB($data['id'])) {
                              $out .= $item->getTypeName() . " - " . $item->getLink() . "<br>";
                           }
                        }
                     } else {
                        $out .= ' ';
                     }
                  }
               } else {
                  $out .= ' ';
               }
            }
         }
         return $out;
         break;
   }
   return "";
}

////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

/**
 * @param $type
 *
 * @return array
 */
function plugin_domains_MassiveActions($type) {
   $plugin = new Plugin();
   if ($plugin->isActivated('domains')) {
      if (in_array($type, PluginDomainsDomain::getTypes(true))) {
         return ['PluginDomainsDomain' . MassiveAction::CLASS_ACTION_SEPARATOR . 'plugin_domains_add_item' =>
                    __('Associate a domain', 'domains')];
      }
   }
   return [];
}
