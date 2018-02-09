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
 * Class PluginDomainsNotificationTargetDomain
 */
class PluginDomainsNotificationTargetDomain extends NotificationTarget {

   /**
    * @return array
    */
   function getEvents() {
      return ['ExpiredDomains'     => __('Expired domains', 'domains'),
                   'DomainsWhichExpire' => __('Expiring domains', 'domains')];
   }

   /**
    * @param       $event
    * @param array $options
    */
   function addDataForTemplate($event, $options = []) {

      $this->data['##domain.entity##']      =
         Dropdown::getDropdownName('glpi_entities',
                                   $options['entities_id']);
      $this->data['##lang.domain.entity##'] = __('Entity');
      $this->data['##domain.action##']      = ($event == "ExpiredDomains" ? __('Expired domains', 'domains') :
         __('Expiring domains', 'domains'));

      $this->data['##lang.domain.name##']           = __('Name');
      $this->data['##lang.domain.dateexpiration##'] = __('Expiration date');

      foreach ($options['domains'] as $id => $domain) {
         $tmp = [];

         $tmp['##domain.name##']           = $domain['name'];
         $tmp['##domain.dateexpiration##'] = Html::convDate($domain['date_expiration']);

         $this->data['domains'][] = $tmp;
      }
   }

   /**
    *
    */
   function getTags() {

      $tags = ['domain.name'           => __('Name'),
                    'domain.dateexpiration' => __('Expiration date')];
      foreach ($tags as $tag => $label) {
         $this->addTagToList(['tag'   => $tag, 'label' => $label,
                                   'value' => true]);
      }

      $this->addTagToList(['tag'     => 'domains',
                                'label'   => __('Expired or expiring domains', 'domains'),
                                'value'   => false,
                                'foreach' => true,
                                'events'  => ['DomainsWhichExpire', 'ExpiredDomains']]);

      asort($this->tag_descriptions);
   }
}
