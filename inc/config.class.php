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
 * Class PluginDomainsConfig
 */
class PluginDomainsConfig extends CommonDBTM
{

   /**
    * @param CommonGLPI $item
    * @param int $withtemplate
    * @return string|translated
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType() == 'CronTask' && $item->getField('name') == "DomainsAlert") {
         return __('Plugin Setup', 'domains');
      }
      return '';
   }


   /**
    * @param CommonGLPI $item
    * @param int $tabnum
    * @param int $withtemplate
    * @return bool
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      global $CFG_GLPI;

      if ($item->getType() == 'CronTask') {

         $target = $CFG_GLPI["root_doc"] . "/plugins/domains/front/config.form.php";
         PluginDomainsDomain::configCron($target);
      }
      return true;
   }

   /**
    * @param $target
    * @param $ID
    */
   function showForm($target, $ID) {

      $this->getFromDB($ID);
      $delay_expired = $this->fields["delay_expired"];
      $delay_whichexpire = $this->fields["delay_whichexpire"];
      echo "<div align='center'>";
      echo "<form method='post' action=\"$target\">";
      echo "<table class='tab_cadre_fixe' cellpadding='5'><tr><th>";
      echo __('Time of checking of validity of domains', 'domains') . "</th></tr>";
      echo "<tr class='tab_bg_1'><td><div align='center'>";

      $delay_stamp_first = mktime(0, 0, 0, date("m"), date("d") - $delay_expired, date("y"));
      $delay_stamp_next = mktime(0, 0, 0, date("m"), date("d") + $delay_whichexpire, date("y"));
      $date_first = date("Y-m-d", $delay_stamp_first);
      $date_next = date("Y-m-d", $delay_stamp_next);

      echo "<tr class='tab_bg_1'><td><div align='left'>";
      echo __('Domains expired since more', 'domains');
      echo "&nbsp;<input type='text' size='5' name='delay_expired' value=\"$delay_expired\">";
      echo "&nbsp;" . _n('Day', 'Days', 2);
      echo "&nbsp;( >" . Html::convDate($date_first) . ")<br>";
      echo __('Domains expiring in less than', 'domains');
      echo "&nbsp;<input type='text' size='5' name='delay_whichexpire' value=\"$delay_whichexpire\">";
      echo "&nbsp;" . _n('Day', 'Days', 2);
      echo "&nbsp;( <" . Html::convDate($date_next) . ")";

      echo "</td>";
      echo "</tr>";

      echo "<tr><th>";
      echo "<input type='hidden' name='id' value='" . $ID . "'>";
      echo "<div align='center'>";
      echo "<input type='submit' name='update' value=\"" . _x('button', 'Post') . "\" class='submit' >";
      echo "</div></th></tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }
}
