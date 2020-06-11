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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginDomainsDomain
 */
class PluginDomainsDomain extends CommonDBTM {

   public    $dohistory        = true;
   static    $rightname        = 'plugin_domains';
   protected $usenotepadrights = true;
   protected $usenotepad       = true;
   static    $types            = ['Computer', 'Monitor', 'NetworkEquipment', 'Peripheral',
                                       'Phone', 'Printer', 'Software'];
   static    $tags             = '[DOMAIN_NAME]';

   /**
    * @param int $nb
    *
    * @return translated
    */
   static function getTypeName($nb = 0) {

      return _n('Domain', 'Domains', $nb, 'domains');
   }

   /**
    * @param CommonGLPI $item
    * @param int        $withtemplate
    *
    * @return array|string|translated
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType() == 'Supplier') {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(self::getTypeName(2), self::countForItem($item));
         }
         return self::getTypeName(2);
      }
      return '';
   }

   /**
    * @param CommonGLPI $item
    * @param int        $tabnum
    * @param int        $withtemplate
    *
    * @return bool
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'Supplier') {
         self::showForSupplier($item);
      }
      return true;
   }

   /**
    * @param CommonDBTM $item
    *
    * @return int
    */
   static function countForItem(CommonDBTM $item) {
      $dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_domains_domains',
                                        ["suppliers_id" => $item->getID()]);
   }

   /**
    *
    */
   function cleanDBonPurge() {

      $temp = new PluginDomainsDomain_Item();
      $temp->deleteByCriteria(['plugin_domains_domains_id' => $this->fields['id']]);
   }

   /**
    * @return array
    */
   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'                 => 'common',
         'name'               => self::getTypeName(2)
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'itemlink_type'      => $this->getType(),
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => 'glpi_plugin_domains_domaintypes',
         'field'              => 'name',
         'name'               => __('Type'),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'linkfield'          => 'users_id_tech',
         'name'               => __('Technician in charge of the hardware'),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => 'glpi_suppliers',
         'field'              => 'name',
         'name'               => __('Supplier'),
         'datatype'           => 'dropdown',
         'itemlink_type'      => 'Supplier',
         'forcegroupby'       => true
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => $this->getTable(),
         'field'              => 'date_creation',
         'name'               => __('Creation date'),
         'datatype'           => 'date'
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'date_expiration',
         'name'               => __('Expiration date'),
         'datatype'           => 'date'
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => $this->getTable(),
         'field'              => 'comment',
         'name'               => __('Comments'),
         'datatype'           => 'text'
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => 'glpi_plugin_domains_domains_items',
         'field'              => 'items_id',
         'nosearch'           => true,
         'massiveaction'      => false,
         'name'               => _n('Associated items', 'Associated items', 2),
         'forcegroupby'       => true,
         'joinparams'         => [
            'jointype'           => 'child'
         ]
      ];

      $tab[] = [
         'id'                 => '9',
         'table'              => $this->getTable(),
         'field'              => 'others',
         'name'               => __('Others')
      ];

      $tab[] = [
         'id'                 => '10',
         'table'              => 'glpi_groups',
         'field'              => 'name',
         'linkfield'          => 'groups_id_tech',
         'name'               => __('Group in charge of the hardware'),
         'condition'          => '`is_assign`',
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '11',
         'table'              => $this->getTable(),
         'field'              => 'is_helpdesk_visible',
         'name'               => __('Associable to a ticket'),
         'datatype'           => 'bool'
      ];

      $tab[] = [
         'id'                 => '12',
         'table'              => $this->getTable(),
         'field'              => 'date_mod',
         'massiveaction'      => false,
         'name'               => __('Last update'),
         'datatype'           => 'datetime'
      ];

      $tab[] = [
         'id'                 => '18',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __('Child entities'),
         'datatype'           => 'bool'
      ];

      $tab[] = [
         'id'                 => '30',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'datatype'           => 'number'
      ];

      $tab[] = [
         'id'                 => '80',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __('Entity'),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '81',
         'table'              => 'glpi_entities',
         'field'              => 'entities_id',
         'name'               => __('Entity-ID')
      ];

      return $tab;
   }

   /**
    * @param array $options
    *
    * @return array
    */
   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginDomainsDomain_Item', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Contract_Item', $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Link', $ong, $options);
      $this->addStandardTab('Notepad', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   /**
    * @param datas $input
    *
    * @return datas
    */
   function prepareInputForAdd($input) {

      if (isset($input['date_creation']) && empty($input['date_creation'])) {
         $input['date_creation'] = 'NULL';
      }
      if (isset($input['date_expiration']) && empty($input['date_expiration'])) {
         $input['date_expiration'] = 'NULL';
      }

      return $input;
   }

   /**
    * @param datas $input
    *
    * @return datas
    */
   function prepareInputForUpdate($input) {

      if (isset($input['date_creation']) && empty($input['date_creation'])) {
         $input['date_creation'] = 'NULL';
      }
      if (isset($input['date_expiration']) && empty($input['date_expiration'])) {
         $input['date_expiration'] = 'NULL';
      }

      return $input;
   }

   /*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */

   /**
    * @return string
    */
   function getSelectLinkedItem() {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_domains_domains_items`
              WHERE `plugin_domains_domains_id`='" . $this->fields['id'] . "'";
   }

   /**
    * @param       $ID
    * @param array $options
    *
    * @return bool
    */
   function showForm($ID, $options = []) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Name') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";

      echo "<td>" . __('Others') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "others");
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Supplier') . "</td>";
      echo "<td>";
      Dropdown::show('Supplier', ['name'   => "suppliers_id",
                                       'value'  => $this->fields["suppliers_id"],
                                       'entity' => $this->fields["entities_id"]]);
      echo "</td>";

      echo "<td>" . __('Creation date') . "</td>";
      echo "<td>";
      Html::showDateField("date_creation", ['value' => $this->fields["date_creation"]]);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Type') . "</td><td>";
      Dropdown::show('PluginDomainsDomainType', ['name'   => "plugin_domains_domaintypes_id",
                                                      'value'  => $this->fields["plugin_domains_domaintypes_id"],
                                                      'entity' => $this->fields["entities_id"]]);
      echo "</td>";

      echo "<td>" . __('Expiration date');
      echo "&nbsp;";
      Html::showToolTip(nl2br(__('Empty for infinite', 'domains')));
      echo "</td>";
      echo "<td>";
      Html::showDateField("date_expiration", ['value' => $this->fields["date_expiration"]]);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Technician in charge of the hardware') . "</td><td>";
      User::dropdown(['name'   => "users_id_tech",
                           'value'  => $this->fields["users_id_tech"],
                           'entity' => $this->fields["entities_id"],
                           'right'  => 'interface']);
      echo "</td>";

      echo "<td>" . __('Associable to a ticket') . "</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible', $this->fields['is_helpdesk_visible']);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Group in charge of the hardware') . "</td>";
      echo "<td>";
      Dropdown::show('Group', ['name'      => "groups_id_tech",
                                    'value'     => $this->fields["groups_id_tech"],
                                    'entity'    => $this->fields["entities_id"],
                                    'condition' => ['is_assign' => 1]]);
      echo "</td>";

      echo "<td class='center' colspan='2'>";
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>";
      echo __('Comments') . "</td>";
      echo "<td colspan = '3' class='center'>";
      echo "<textarea cols='115' rows='5' name='comment' >" . $this->fields["comment"] . "</textarea>";
      echo "</td>";

      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }

   /**
    * Make a select box for link domains
    *
    * Parameters which could be used in options array :
    *    - name : string / name of the select (default is documents_id)
    *    - entity : integer or array / restrict to a defined entity or array of entities
    *                   (default -1 : no restriction)
    *    - used : array / Already used items ID: not to display in dropdown (default empty)
    *
    * @param $options array of possible options
    *
    * @return nothing (print out an HTML select box)
    * */
   static function dropdownDomains($options = []) {

      global $DB, $CFG_GLPI;

      $p['name']    = 'plugin_domains_domains_id';
      $p['entity']  = '';
      $p['used']    = [];
      $p['display'] = true;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      $rand = mt_rand();
      $dbu = new DbUtils();

      $where = " WHERE `glpi_plugin_domains_domains`.`is_deleted` = '0' ";
      $where .= $dbu->getEntitiesRestrictRequest("AND", 'glpi_plugin_domains_domains', '', $p['entity'], true);

      if (count($p['used'])) {
         $where .= " AND `id` NOT IN (0, " . implode(",", $p['used']) . ")";
      }

      $query  = "SELECT *
        FROM `glpi_plugin_domains_domaintypes`
        WHERE `id` IN (SELECT DISTINCT `plugin_domains_domaintypes_id`
                       FROM `glpi_plugin_domains_domains`
                       $where)
        ORDER BY `name`";
      $result = $DB->query($query);

      $values = [0 => Dropdown::EMPTY_VALUE];

      while ($data = $DB->fetchAssoc($result)) {
         $values[$data['id']] = $data['name'];
      }

      $out      = Dropdown::showFromArray('_domaintype', $values, ['width'   => '30%',
                                                                        'rand'    => $rand,
                                                                        'display' => false]);
      $field_id = Html::cleanId("dropdown__domaintype$rand");

      $params = ['domaintypes' => '__VALUE__',
                      'entity'      => $p['entity'],
                      'rand'        => $rand,
                      'myname'      => $p['name'],
                      'used'        => $p['used']
      ];

      $out .= Ajax::updateItemOnSelectEvent($field_id, "show_" . $p['name'] . $rand, $CFG_GLPI["root_doc"] . "/plugins/domains/ajax/dropdownTypeDomains.php", $params, false);

      $out .= "<span id='show_" . $p['name'] . "$rand'>";
      $out .= "</span>\n";

      $params['domaintype'] = 0;
      $out .= Ajax::updateItem("show_" . $p['name'] . $rand, $CFG_GLPI["root_doc"] . "/plugins/domains/ajax/dropdownTypeDomains.php", $params, false);
      if ($p['display']) {
         echo $out;
         return $rand;
      }
      return $out;
   }

   //Massive action

   /**
    * @param null $checkitem
    *
    * @return array
    */
   function getSpecificMassiveActions($checkitem = null) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         if ($isadmin) {
            $actions['PluginDomainsDomain' . MassiveAction::CLASS_ACTION_SEPARATOR . 'install']   = _x('button', 'Associate');
            $actions['PluginDomainsDomain' . MassiveAction::CLASS_ACTION_SEPARATOR . 'uninstall'] = _x('button', 'Dissociate');
            $actions['PluginDomainsDomain' . MassiveAction::CLASS_ACTION_SEPARATOR . 'duplicate']  = _x('button', 'Duplicate');
            if (Session::haveRight('transfer', READ) && Session::isMultiEntitiesMode()
            ) {
               $actions['PluginDomainsDomain' . MassiveAction::CLASS_ACTION_SEPARATOR . 'transfer'] = __('Transfer');
            }
         }
      }
      return $actions;
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
    *
    * @param MassiveAction $ma
    *
    * @return bool|false
    */
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case 'plugin_domains_add_item':
            self::dropdownDomains([]);
            echo "&nbsp;" .
                 Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
         case "install" :
            Dropdown::showSelectItemFromItemtypes(['items_id_name' => 'item_item',
                                                        'itemtype_name' => 'typeitem',
                                                        'itemtypes'     => self::getTypes(true),
                                                        'checkright'
                                                                        => true,
                                                  ]);
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
         case "uninstall" :
            Dropdown::showSelectItemFromItemtypes(['items_id_name' => 'item_item',
                                                        'itemtype_name' => 'typeitem',
                                                        'itemtypes'     => self::getTypes(true),
                                                        'checkright'
                                                                        => true,
                                                  ]);
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
         case "transfer" :
            Dropdown::show('Entity');
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;

         case "duplicate" :
            Dropdown::show('Entity');
            break;
      }
      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    *
    * @param MassiveAction $ma
    * @param CommonDBTM    $item
    * @param array         $ids
    *
    * @return nothing|void
    */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {

      $domain_item = new PluginDomainsDomain_Item();

      switch ($ma->getAction()) {
         case "plugin_domains_add_item":
            $input = $ma->getInput();
            foreach ($ids as $id) {
               $input = ['plugin_domains_domains_id' => $input['plugin_domains_domains_id'],
                              'items_id'                  => $id,
                              'itemtype'                  => $item->getType()];
               if ($domain_item->can(-1, UPDATE, $input)) {
                  if ($domain_item->add($input)) {
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                  }
               } else {
                  $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
               }
            }
            return;

         case "transfer" :
            $input = $ma->getInput();
            if ($item->getType() == 'PluginDomainsDomain') {
               foreach ($ids as $key) {
                  $item->getFromDB($key);
                  $type = PluginDomainsDomainType::transfer($item->fields["plugin_domains_domaintypes_id"], $input['entities_id']);
                  if ($type > 0) {
                     $values["id"]                            = $key;
                     $values["plugin_domains_domaintypes_id"] = $type;
                     $item->update($values);
                  }
                  unset($values);
                  $values["id"]          = $key;
                  $values["entities_id"] = $input['entities_id'];

                  if ($item->update($values)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
            return;

         case 'install' :
            $input = $ma->getInput();
            foreach ($ids as $key) {
               if ($item->can($key, UPDATE)) {
                  $values = ['plugin_domains_domains_id' => $key,
                                  'items_id'                  => $input["item_item"],
                                  'itemtype'                  => $input['typeitem']];
                  if ($domain_item->add($values)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               } else {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_NORIGHT);
                  $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
               }
            }
            return;

         case 'uninstall':
            $input = $ma->getInput();
            foreach ($ids as $key) {
               if ($domain_item->deleteItemByDomainsAndItem($key, $input['item_item'], $input['typeitem'])) {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
               } else {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
               }
            }
            return;

         case "duplicate" :
            if ($item->getType() == 'PluginDomainsDomain') {
               $input     = $ma->getInput();
               foreach ($ids as $key => $val) {
                  $item->getFromDB($key);
                  unset($item->fields["id"]);
                  $item->fields["name"]    = addslashes($item->fields["name"]);
                  $item->fields["comment"] = addslashes($item->fields["comment"]);
                  $item->fields["entities_id"] = $input['entities_id'];
                  if ($item->add($item->fields)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
            break;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }

   // Cron action
   /**
    * @param $name
    *
    * @return array
    */
   static function cronInfo($name) {

      switch ($name) {
         case 'DomainsAlert':
            return [
               'description' => __('Expired or expiring domains', 'domains')];   // Optional
            break;
      }
      return [];
   }

   /**
    * @return string
    */
   static function queryExpiredDomains() {

      $config = new PluginDomainsConfig();

      $config->getFromDB('1');
      $delay = $config->fields["delay_expired"];

      $query = "SELECT * 
         FROM `glpi_plugin_domains_domains`
         WHERE `date_expiration` IS NOT NULL
         AND `is_deleted` = '0'
         AND DATEDIFF(CURDATE(),`date_expiration`) > $delay 
         AND DATEDIFF(CURDATE(),`date_expiration`) > 0 ";

      return $query;
   }

   /**
    * @return string
    */
   static function queryDomainsWhichExpire() {

      $config = new PluginDomainsConfig();
      $config->getFromDB('1');
      $delay = $config->fields["delay_whichexpire"];

      $query = "SELECT *
         FROM `glpi_plugin_domains_domains`
         WHERE `date_expiration` IS NOT NULL
         AND `is_deleted` = '0'
         AND DATEDIFF(CURDATE(),`date_expiration`) > -$delay 
         AND DATEDIFF(CURDATE(),`date_expiration`) < 0 ";

      return $query;
   }

   /**
    * Cron action on domains : ExpiredDomains or DomainsWhichExpire
    *
    * @param $task for log, if NULL display
    *
    *
    * @return int
    */
   static function cronDomainsAlert($task = null) {
      global $DB, $CFG_GLPI;

      if (!$CFG_GLPI["notifications_mailing"]) {
         return 0;
      }

      $message     = [];
      $cron_status = 0;

      $query_expired     = self::queryExpiredDomains();
      $query_whichexpire = self::queryDomainsWhichExpire();

      $querys = [Alert::NOTICE => $query_whichexpire, Alert::END => $query_expired];

      $domain_infos    = [];
      $domain_messages = [];

      foreach ($querys as $type => $query) {
         $domain_infos[$type] = [];
         foreach ($DB->request($query) as $data) {
            $entity                         = $data['entities_id'];
            $message                        = $data["name"] . ": " .
                                              Html::convDate($data["date_expiration"]) . "<br>\n";
            $domain_infos[$type][$entity][] = $data;

            if (!isset($domain_messages[$type][$entity])) {
               $domain_messages[$type][$entity] = __('Domains expired since more', 'domains') . "<br />";
            }
            $domain_messages[$type][$entity] .= $message;
         }
      }

      foreach ($querys as $type => $query) {

         foreach ($domain_infos[$type] as $entity => $domains) {
            Plugin::loadLang('domains');

            if (NotificationEvent::raiseEvent(($type == Alert::NOTICE ? "DomainsWhichExpire" : "ExpiredDomains"),
               new PluginDomainsDomain(),
               ['entities_id' => $entity,
                     'domains'     => $domains])) {
               $message     = $domain_messages[$type][$entity];
               $cron_status = 1;
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities", $entity) . ":  $message\n");
                  $task->addVolume(1);
               } else {
                  Toolbox::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities", $entity) . ":  $message");
               }
            } else {
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities", $entity) .
                             ":  Send domains alert failed\n");
               } else {
                  Toolbox::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities", $entity) .
                                                   ":  Send domains alert failed", false, ERROR);
               }
            }
         }
      }

      return $cron_status;
   }

   /**
    * @param $target
    */
   static function configCron($target) {

      $config = new PluginDomainsConfig();

      $config->showForm($target, 1);
   }

   /**
    * For other plugins, add a type to the linkable types
    *
    * @since version 1.3.0
    *
    * @param $type string class name
    * */
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }

   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
    * */
   static function getTypes($all = false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }

   /**
    * Show domains associated to a supplier
    *
    * @since version 0.84
    *
    * @param $item            CommonDBTM object for which associated domains must be displayed
    * @param $withtemplate (default '')
    *
    * @return bool
    */
   static function showForSupplier(CommonDBTM $item, $withtemplate = '') {
      global $DB, $CFG_GLPI;

      $ID = $item->getField('id');

      if ($item->isNewID($ID)) {
         return false;
      }
      if (!Session::haveRight('plugin_domains', READ)) {
         return false;
      }

      if (!$item->can($item->fields['id'], READ)) {
         return false;
      }

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }
      $withtemplate = 0;

      $dbu = new DbUtils();

      $query = "SELECT `glpi_plugin_domains_domains`.`id` AS assocID,
                       `glpi_entities`.`id` AS entity,
                       `glpi_plugin_domains_domains`.`name` AS assocName,
                       `glpi_plugin_domains_domains`.* "
               . "FROM `glpi_plugin_domains_domains` "
               . " LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_domains_domains`.`entities_id`) "
               . " WHERE `suppliers_id` = '$ID' "
               . $dbu->getEntitiesRestrictRequest(" AND ", "glpi_plugin_domains_domains", '', '', true);
      $query .= " ORDER BY `assocName` ";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i      = 0;

      $domains = [];
      $domain  = new PluginDomainsDomain();
      $used    = [];
      if ($numrows = $DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $domains[$data['assocID']] = $data;
            $used[$data['id']]         = $data['id'];
         }
      }

      echo "<div class='spaced'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      echo "<th>" . __('Name') . "</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>" . __('Entity') . "</th>";
      }
      echo "<th>" . __('Group in charge of the hardware') . "</th>";
      echo "<th>" . __('Supplier') . "</th>";
      echo "<th>" . __('Technician in charge of the hardware') . "</th>";
      echo "<th>" . __('Type') . "</th>";
      echo "<th>" . __('Creation date') . "</th>";
      echo "<th>" . __('Expiration date') . "</th>";
      echo "</tr>";
      $used = [];

      if ($number) {

         Session::initNavigateListItems('PluginDomainsDomain',
            //TRANS : %1$s is the itemtype name,
            //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                $item->getTypeName(1), $item->getName()));

         foreach ($domains as $data) {
            $domainID = $data["id"];
            $link     = NOT_AVAILABLE;

            if ($domain->getFromDB($domainID)) {
               $link = $domain->getLink();
            }

            Session::addToNavigateListItems('PluginDomainsDomain', $domainID);

            $used[$domainID] = $domainID;

            echo "<tr class='tab_bg_1" . ($data["is_deleted"] ? "_2" : "") . "'>";
            echo "<td class='center'>$link</td>";
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities", $data['entities_id']) .
                    "</td>";
            }
            echo "<td class='center'>" . Dropdown::getDropdownName("glpi_groups", $data["groups_id_tech"]) . "</td>";
            echo "<td>";
            echo "<a href=\"" . $CFG_GLPI["root_doc"] . "/front/enterprise.form.php?ID=" . $data["suppliers_id"] . "\">";
            echo Dropdown::getDropdownName("glpi_suppliers", $data["suppliers_id"]);
            if ($_SESSION["glpiis_ids_visible"] == 1) {
               echo " (" . $data["suppliers_id"] . ")";
            }
            echo "</a></td>";
            echo "<td class='center'>" . $dbu->getUserName($data["users_id_tech"]) . "</td>";
            echo "<td class='center'>" . Dropdown::getDropdownName("glpi_plugin_domains_domaintypes", $data["plugin_domains_domaintypes_id"]) . "</td>";
            echo "<td class='center'>" . Html::convDate($data["date_creation"]) . "</td>";
            if ($data["date_expiration"] <= date('Y-m-d')
                && !empty($data["date_expiration"])
            ) {
               echo "<td class='center'><div class='deleted'>" . Html::convDate($data["date_expiration"]) . "</div></td>";
            } else if (empty($data["date_expiration"])) {
               echo "<td class='center'>" . __('Does not expire', 'domains') . "</td>";
            } else {
               echo "<td class='center'>" . Html::convDate($data["date_expiration"]) . "</td>";
            }
            echo "</tr>";
            $i++;
         }
      }

      echo "</table>";
      echo "</div>";
   }

   /**
    * @param string     $link
    * @param CommonDBTM $item
    *
    * @return array
    */
   static function generateLinkContents($link, CommonDBTM $item) {

      if (strstr($link, "[DOMAIN_NAME]")) {
         $link = str_replace("[DOMAIN_NAME]", $item->getName(), $link);
         return [$link];
      }

      return parent::generateLinkContents($link, $item);
   }

}
