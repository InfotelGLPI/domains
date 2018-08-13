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

// Init the hooks of the plugins -Needed
function plugin_init_domains() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['domains']   = true;
   $PLUGIN_HOOKS['change_profile']['domains']   = ['PluginDomainsProfile', 'initProfile'];
   $PLUGIN_HOOKS['assign_to_ticket']['domains'] = true;

   if (Session::getLoginUserID()) {

      Plugin::registerClass('PluginDomainsDomain', [
         'linkuser_tech_types'         => true,
         'linkgroup_tech_types'        => true,
         'document_types'              => true,
         'contract_types'              => true,
         'ticket_types'                => true,
         'helpdesk_visible_types'      => true,
         'notificationtemplates_types' => true,
         'link_types'                  => true
      ]);

      Plugin::registerClass('PluginDomainsConfig',
                            ['addtabon' => 'CronTask']);

      Plugin::registerClass('PluginDomainsProfile',
                            ['addtabon' => 'Profile']);

      Plugin::registerClass('PluginDomainsDomain',
                            ['addtabon' => 'Supplier']);

      $plugin = new Plugin();
      if (!$plugin->isActivated('environment')
          && Session::haveRight("plugin_domains", READ)) {

         $PLUGIN_HOOKS['menu_toadd']['domains'] = ['assets' => 'PluginDomainsMenu'];
      }

      if (Session::haveRight("plugin_domains", CREATE)) {
         $PLUGIN_HOOKS['use_massive_action']['domains'] = 1;
      }

      if (class_exists('PluginAccountsAccount')) {
         PluginAccountsAccount::registerType('PluginDomainsDomain');
      }

      if (class_exists('PluginDomainsDomain_Item')) { // only if plugin activated
         $PLUGIN_HOOKS['plugin_datainjection_populate']['domains'] =
            'plugin_datainjection_populate_domains';
      }
      // Import from Data_Injection plugin
      $PLUGIN_HOOKS['migratetypes']['domains'] = 'plugin_datainjection_migratetypes_domains';

      // End init, when all types are registered
      $PLUGIN_HOOKS['post_init']['domains'] = 'plugin_domains_postinit';
   }
}

// Get the name and the version of the plugin - Needed
/**
 * @return array
 */
function plugin_version_domains() {

   return [
      'name'           => _n('Domain', 'Domains', 2, 'domains'),
      'version'        => '2.0.1',
      'oldname'        => 'domain',
      'license'        => 'GPLv2+',
      'author'         => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
      'homepage'       => 'https://github.com/InfotelGLPI/domains',
      'minGlpiVersion' => '9.3',// For compatibility / no install in version < 9.
   ];

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
/**
 * @return bool
 */
function plugin_domains_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.3', 'lt') || version_compare(GLPI_VERSION, '9.4', 'ge')) {
      echo __('This plugin requires GLPI >= 9.3');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
/**
 * @return bool
 */
function plugin_domains_check_config() {
   return true;
}


/**
 * @param $types
 *
 * @return mixed
 */
function plugin_datainjection_migratetypes_domains($types) {
   $types[4400] = 'PluginDomainsDomain';
   return $types;
}
