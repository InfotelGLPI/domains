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

include('../../../inc/includes.php');

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$domain = new PluginDomainsDomain();
$domain_item = new PluginDomainsDomain_Item();

if (isset($_POST["add"])) {
   $domain->check(-1, CREATE, $_POST);
   $newID = $domain->add($_POST);
   if ($_SESSION['glpibackcreated']) {
      Html::redirect($domain->getFormURL() . "?id=" . $newID);
   }
   Html::back();
} else if (isset($_POST["delete"])) {
   $domain->check($_POST['id'], DELETE);
   $domain->delete($_POST);
   $domain->redirectToList();

} else if (isset($_POST["restore"])) {
   $domain->check($_POST['id'], PURGE);
   $domain->restore($_POST);
   $domain->redirectToList();

} else if (isset($_POST["purge"])) {
   $domain->check($_POST['id'], PURGE);
   $domain->delete($_POST, 1);
   $domain->redirectToList();

} else if (isset($_POST["update"])) {
   $domain->check($_POST['id'], UPDATE);
   $domain->update($_POST);
   Html::back();

} else if (isset($_POST["additem"])) {

   if (!empty($_POST['itemtype']) && $_POST['items_id'] > 0) {
      $domain_item->check(-1, UPDATE, $_POST);
      $domain_item->addItem($_POST);
   }
   Html::back();

} else if (isset($_POST["deleteitem"])) {
   foreach ($_POST["item"] as $key => $val) {
      $input = ['id' => $key];
      if ($val == 1) {
         $domain_item->check($key, UPDATE);
         $domain_item->delete($input);
      }
   }
   Html::back();

} else if (isset($_POST["deletedomains"])) {
   $input = ['id' => $_POST["id"]];
   $domain_item->check($_POST["id"], UPDATE);
   $domain_item->delete($input);
   Html::back();

} else {

   $domain->checkGlobal(READ);

   $plugin = new Plugin();
   if ($plugin->isActivated("environment")) {
      Html::header(PluginDomainsDomain::getTypeName(2), '', "assets", "pluginenvironmentdisplay", "domains");
   } else {
      Html::header(PluginDomainsDomain::getTypeName(2), '', "assets", "plugindomainsmenu");
   }
   $domain->display($_GET);

   Html::footer();
}
