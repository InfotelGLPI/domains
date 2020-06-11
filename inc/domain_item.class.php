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
 * Class PluginDomainsDomain_Item
 */
class PluginDomainsDomain_Item extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1 = "PluginDomainsDomain";
   static public $items_id_1 = 'plugin_domains_domains_id';

   static public $itemtype_2 = 'itemtype';
   static public $items_id_2 = 'items_id';

   static $rightname = 'plugin_domains';

   /**
    * @param CommonDBTM $item
    */
   static function cleanForItem(CommonDBTM $item) {

      $temp = new self();
      $temp->deleteByCriteria(
         ['itemtype' => $item->getType(),
               'items_id' => $item->getField('id')]
      );
   }


   /**
    * @param CommonGLPI $item
    * @param int        $withtemplate
    *
    * @return array|string|translated
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
         if ($item->getType() == 'PluginDomainsDomain'
             && count(PluginDomainsDomain::getTypes(false))
         ) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(_n('Associated item', 'Associated items', 2), self::countForDomain($item));
            }
            return _n('Associated item', 'Associated items', 2);

         } else if (in_array($item->getType(), PluginDomainsDomain::getTypes(true))
                    && Session::haveRight('plugin_domains', READ)
         ) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(PluginDomainsDomain::getTypeName(2), self::countForItem($item));
            }
            return PluginDomainsDomain::getTypeName(2);
         }
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

      if ($item->getType() == 'PluginDomainsDomain') {

         self::showForDomain($item);

      } else if (in_array($item->getType(), PluginDomainsDomain::getTypes(true))) {

         self::showForItem($item);

      }
      return true;
   }

   /**
    * @param PluginDomainsDomain $item
    *
    * @return int
    */
   static function countForDomain(PluginDomainsDomain $item) {

      $types = $item->getTypes();
      if (count($types) == 0) {
         return 0;
      }
      $dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_domains_domains_items',
                                        ["plugin_domains_domains_id" => $item->getID(),
                                         "itemtype"                  => $types
                                        ]);
   }


   /**
    * @param CommonDBTM $item
    *
    * @return int
    */
   static function countForItem(CommonDBTM $item) {
      $dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_domains_domains_items',
                                        ["itemtype" => $item->getType(),
                                         "items_id" => $item->getID()]);
   }

   /**
    * @param $plugin_domains_domains_id
    * @param $items_id
    * @param $itemtype
    *
    * @return bool
    */
   function getFromDBbyDomainsAndItem($plugin_domains_domains_id, $items_id, $itemtype) {
      global $DB;

      $query = "SELECT * FROM `" . $this->getTable() . "` " .
               "WHERE `plugin_domains_domains_id` = '" . $plugin_domains_domains_id . "' 
         AND `itemtype` = '" . $itemtype . "'
         AND `items_id` = '" . $items_id . "'";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetchAssoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }

   /**
    * @param $values
    */
   function addItem($values) {

      $this->add(['plugin_domains_domains_id' => $values["plugin_domains_domains_id"],
                       'items_id'                  => $values["items_id"],
                       'itemtype'                  => $values["itemtype"]]);

   }

   /**
    * @param $plugin_domains_domains_id
    * @param $items_id
    * @param $itemtype
    */
   function deleteItemByDomainsAndItem($plugin_domains_domains_id, $items_id, $itemtype) {

      if ($this->getFromDBbyDomainsAndItem($plugin_domains_domains_id, $items_id, $itemtype)) {
         $this->delete(['id' => $this->fields["id"]]);
      }
   }

   /**
    * Show items links to a domain
    *
    * @since version 0.84
    *
    * @param $domain PluginDomainsDomain object
    *
    * @return nothing (HTML display)
    **/
   public static function showForDomain(PluginDomainsDomain $domain) {
      global $DB;

      $instID = $domain->fields['id'];
      if (!$domain->can($instID, READ)) {
         return false;
      }
      $canedit = $domain->can($instID, UPDATE);
      $rand    = mt_rand();
      $dbu     = new DbUtils();

      $query = "SELECT DISTINCT `itemtype`
            FROM `glpi_plugin_domains_domains_items`
            WHERE `plugin_domains_domains_id` = '" . $instID . "'
            ORDER BY `itemtype`
            LIMIT " . count(PluginDomainsDomain::getTypes(true));

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (Session::isMultiEntitiesMode()) {
         $colsup = 1;
      } else {
         $colsup = 0;
      }

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form method='post' name='domain_form$rand'
         id='domain_form$rand'  action='" . Toolbox::getItemTypeFormURL("PluginDomainsDomain") . "'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='" . ($canedit ? (5 + $colsup) : (4 + $colsup)) . "'>" .
              __('Add an item') . "</th></tr>";

         echo "<tr class='tab_bg_1'><td colspan='" . (3 + $colsup) . "' class='center'>";
         Dropdown::showSelectItemFromItemtypes(['items_id_name' => 'items_id',
                                                     'itemtypes'     => PluginDomainsDomain::getTypes(true),
                                                     'entity_restrict'
                                                                     => ($domain->fields['is_recursive']
                                                        ? $dbu->getSonsOf('glpi_entities',
                                                                    $domain->fields['entities_id'])
                                                                     : $domain->fields['entities_id']),
                                                     'checkright'
                                                                     => true,
                                               ]);
         echo "</td><td colspan='2' class='center' class='tab_bg_1'>";
         echo "<input type='hidden' name='plugin_domains_domains_id' value='$instID'>";
         echo "<input type='submit' name='additem' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
         $massiveactionparams = [];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";

      if ($canedit && $number) {
         echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
      }

      echo "<th>" . __('Type') . "</th>";
      echo "<th>" . __('Name') . "</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>" . __('Entity') . "</th>";
      }
      echo "<th>" . __('Serial number') . "</th>";
      echo "<th>" . __('Inventory number') . "</th>";
      echo "</tr>";

      for ($i = 0; $i < $number; $i++) {
         $itemtype = $DB->result($result, $i, "itemtype");

         if (!($item = $dbu->getItemForItemtype($itemtype))) {
            continue;
         }

         if ($item->canView()) {
            $column = "name";

            $itemTable = $dbu->getTableForItemType($itemtype);
            $query     = " SELECT `" . $itemTable . "`.*,
                              `glpi_plugin_domains_domains_items`.`id` AS items_id,
                              `glpi_entities`.id AS entity "
                         . " FROM `glpi_plugin_domains_domains_items`, `" . $itemTable
                         . "` LEFT JOIN `glpi_entities`
                     ON (`glpi_entities`.`id` = `" . $itemTable . "`.`entities_id`) "
                         . " WHERE `" . $itemTable . "`.`id` = `glpi_plugin_domains_domains_items`.`items_id`
                     AND `glpi_plugin_domains_domains_items`.`itemtype` = '$itemtype'
                     AND `glpi_plugin_domains_domains_items`.`plugin_domains_domains_id` = '$instID' "
                         . $dbu->getEntitiesRestrictRequest(" AND ", $itemTable, '', '', $item->maybeRecursive());

            if ($item->maybeTemplate()) {
               $query .= " AND " . $itemTable . ".is_template='0'";
            }

            $query .= " ORDER BY `glpi_entities`.`completename`, `" . $itemTable . "`.`$column` ";

            if ($result_linked = $DB->query($query)) {
               if ($DB->numrows($result_linked)) {

                  Session::initNavigateListItems($itemtype, PluginDomainsDomain::getTypeName(2) . " = " . $domain->fields['name']);

                  while ($data = $DB->fetchAssoc($result_linked)) {

                     $item->getFromDB($data["id"]);

                     Session::addToNavigateListItems($itemtype, $data["id"]);

                     $ID = "";

                     if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                        $ID = " (" . $data["id"] . ")";
                     }

                     $link = Toolbox::getItemTypeFormURL($itemtype);
                     $name = "<a href=\"" . $link . "?id=" . $data["id"] . "\">"
                             . $data["name"] . "$ID</a>";

                     echo "<tr class='tab_bg_1'>";

                     if ($canedit) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox(__CLASS__, $data["items_id"]);
                        echo "</td>";
                     }
                     echo "<td class='center'>" . $item->getTypeName(1) . "</td>";

                     echo "<td class='center' " . (isset($data['is_deleted']) && $data['is_deleted'] ? "class='tab_bg_2_2'" : "") .
                          ">" . $name . "</td>";
                     if (Session::isMultiEntitiesMode()) {
                        echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities", $data['entity']) . "</td>";
                     }
                     echo "<td class='center'>" . (isset($data["serial"]) ? "" . $data["serial"] . "" : "-") . "</td>";
                     echo "<td class='center'>" . (isset($data["otherserial"]) ? "" . $data["otherserial"] . "" : "-") . "</td>";

                     echo "</tr>";
                  }
               }
            }
         }
      }
      echo "</table>";

      if ($canedit && $number) {
         $paramsma['ontop'] = false;
         Html::showMassiveActions($paramsma);
         Html::closeForm();
      }
      echo "</div>";

   }

   /**
    * @since version 0.84
    **/
   function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }

   /**
    * Show domains associated to an item
    *
    * @since version 0.84
    *
    * @param $item            CommonDBTM object for which associated domains must be displayed
    * @param $withtemplate (default '')
    *
    * @return bool
    */
   static function showForItem(CommonDBTM $item, $withtemplate = '') {
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

      $canedit      = $item->canAddItem('PluginDomainsDomain');
      $rand         = mt_rand();
      $is_recursive = $item->isRecursive();
      $dbu          = new DbUtils();

      $query = "SELECT `glpi_plugin_domains_domains_items`.`id` AS assocID,
                       `glpi_entities`.`id` AS entity,
                       `glpi_plugin_domains_domains`.`name` AS assocName,
                       `glpi_plugin_domains_domains`.*
                FROM `glpi_plugin_domains_domains_items`
                LEFT JOIN `glpi_plugin_domains_domains`
                 ON (`glpi_plugin_domains_domains_items`.`plugin_domains_domains_id`=`glpi_plugin_domains_domains`.`id`)
                LEFT JOIN `glpi_entities` ON (`glpi_plugin_domains_domains`.`entities_id`=`glpi_entities`.`id`)
                WHERE `glpi_plugin_domains_domains_items`.`items_id` = '$ID'
                      AND `glpi_plugin_domains_domains_items`.`itemtype` = '" . $item->getType() . "' ";

      $query .= $dbu->getEntitiesRestrictRequest(" AND", "glpi_plugin_domains_domains", '', '', true);

      $query .= " ORDER BY `assocName`";

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

      if ($canedit && $withtemplate < 2) {
         // Restrict entity for knowbase
         $entities = "";
         $entity   = $_SESSION["glpiactive_entity"];

         if ($item->isEntityAssign()) {
            /// Case of personal items : entity = -1 : create on active entity (Reminder case))
            if ($item->getEntityID() >= 0) {
               $entity = $item->getEntityID();
            }

            if ($item->isRecursive()) {
               $entities = $dbu->getSonsOf('glpi_entities', $entity);
            } else {
               $entities = $entity;
            }
         }
         $limit = $dbu->getEntitiesRestrictRequest(" AND ", "glpi_plugin_domains_domains", '', $entities, true);
         $q     = "SELECT COUNT(*)
               FROM `glpi_plugin_domains_domains`
               WHERE `is_deleted` = '0'
               $limit";

         $result = $DB->query($q);
         $nb     = $DB->result($result, 0, 0);

         echo "<div class='firstbloc'>";

         if (Session::haveRight('plugin_domains', READ)
             && ($nb > count($used))
         ) {
            echo "<form name='domain_form$rand' id='domain_form$rand' method='post'
                   action='" . Toolbox::getItemTypeFormURL('PluginDomainsDomain') . "'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='4' class='center'>";
            echo "<input type='hidden' name='entities_id' value='$entity'>";
            echo "<input type='hidden' name='is_recursive' value='$is_recursive'>";
            echo "<input type='hidden' name='itemtype' value='" . $item->getType() . "'>";
            echo "<input type='hidden' name='items_id' value='$ID'>";
            if ($item->getType() == 'Ticket') {
               echo "<input type='hidden' name='tickets_id' value='$ID'>";
            }

            PluginDomainsDomain::dropdownDomains(['entity' => $entities,
                                                       'used'   => $used]);

            echo "</td><td class='center' width='20%'>";
            echo "<input type='submit' name='additem' value=\"" .
                 __('Associate a domain', 'domains') . "\" class='submit'>";
            echo "</td>";
            echo "</tr>";
            echo "</table>";
            Html::closeForm();
         }

         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number && ($withtemplate < 2)) {
         Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
         $massiveactionparams = ['num_displayed' => $number];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      if ($canedit && $number && ($withtemplate < 2)) {
         echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
      }
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
            if ($canedit && ($withtemplate < 2)) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["assocID"]);
               echo "</td>";
            }
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
      if ($canedit && $number && ($withtemplate < 2)) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }
}
