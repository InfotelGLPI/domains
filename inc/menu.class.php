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
 * Class PluginDomainsMenu
 */
class PluginDomainsMenu extends CommonGLPI
{
   static $rightname = 'plugin_domains';

   /**
    * @return translated
    */
   static function getMenuName() {
      return _n('Domain', 'Domains', 2, 'domains');
   }

   /**
    * @return array
    */
   static function getMenuContent() {

      $menu = [];
      $menu['title'] = self::getMenuName();
      $menu['page'] = "/plugins/domains/front/domain.php";
      $menu['links']['search'] = PluginDomainsDomain::getSearchURL(false);
      if (PluginDomainsDomain::canCreate()) {
         $menu['links']['add'] = PluginDomainsDomain::getFormURL(false);
      }

      return $menu;
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['assets']['types']['PluginDomainsMenu'])) {
         unset($_SESSION['glpimenu']['assets']['types']['PluginDomainsMenu']);
      }
      if (isset($_SESSION['glpimenu']['assets']['content']['plugindomainsmenu'])) {
         unset($_SESSION['glpimenu']['assets']['content']['plugindomainsmenu']);
      }
   }
}
