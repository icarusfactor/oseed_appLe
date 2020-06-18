<?php

/**
 * @package RSS Organized Seed Feed AppLe
 */

/*
 Plugin Name: RSS Organized Seed Feed App
 Plugin URI: http://userspace.org
 Description: This app gathers RSS feed data from selected site and proitizes sites and requires the AppLepie project plugin.
 Version: 0.27.1
 Author: Daniel Yount IcarusFactor
 Author URI: http://userspace.org
 License: GPLv2 or later
 Text Domain: oseedfeed-appLe
 */

/*
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
defined('ABSPATH')or die('Hey, what are you doing here? You silly human!');
if(!class_exists('oseedfeedAppLe')&& class_exists('AppLePiePlugin')) {

    class oseedfeedAppLe {
        public $plugin;

        function __construct() {
            $this->plugin = plugin_basename(__FILE__);
        }

        function activate() {
            // Require parent plugin
            if(!is_plugin_active('applepie_plugin/applepie-plugin.php')and current_user_can('activate_plugins')) {
                // Stop activation redirect and show error
                wp_die('Sorry, but this plugin requires the Parent Plugin to be installed and active. <br><a href="' . 
                       admin_url('plugins.php'). 
                                 '">&laquo; Return to Plugins</a>');
            }
            require_once plugin_dir_path(__FILE__). 'inc/feed-app-activate.php';
            oseedfeedAppActivate::activate();
        }

        function start_up($atts) {
            $a2b =[[]];
            //Create RAWseed prioity instance.
            $RAWseed = new RAWseed();
            // Working on APTEXT 
            $a = shortcode_atts(array('id' => '1', 'section' => 'Linux News Feeds', 'media' => 'APTEXT'), $atts);
            //Grab RSS feed data and priority from the ID and section name.
            // This will return one row with the id priority based on date.
            $a2b = $RAWseed->priority_cast($a['id'], $a['section']);
            $ApplepiePlugin = new AppLePiePlugin();
            list($permrss, $titlerss, $daterss, $contentrss)= $ApplepiePlugin->feed_generate_process($a2b['rss'], 2, $a['media'], $a2b['id']);
            //Error check
            if(empty($permrss)|| empty($titlerss)) {
                $dat = array();
                if(empty($permrss)) {
                    $dat[0] = 1;
                }
                if(empty($titlerss)) {
                    $dat[1] = 1;
                }
                if(empty($daterss)) {
                    $dat[2] = 1;
                }
                if(empty($contentrss)) {
                    $dat[3] = 1;
                }
                $Content = "NO DATA FROM " . $a2b['site'];
                //$Content .= $ApplepiePlugin->feed_generate_headtofoot( $a['media'] );
                $Content .= $ApplepiePlugin->feed_generate_footer();
                return $Content;
            }
            $Content = $ApplepiePlugin->feed_generate_header();
            //the output only uses one item, will make this loop to count in future. 
            //now convert links to open new page.      
            $contentrss[1] = str_replace("<a href=", "<a target = '_blank'  href=", $contentrss[1]);
            $Content .= " <span ><a  target='_blank' href=\"" . $permrss[1] . "\" >" . $titlerss[1] . "</a></span><br>";
            $Content .= " <span style=\"font-size: 9px;text-decoration: underline overline; \" >/// " . $daterss[1] . " ///</span>";
            $Content .= "<a target='_blank' style=\"font-size: 9px;text-decoration: underline overline; \" href=\"" . $a2b['url'] . "\" >/// " . $a2b['site'] . " ///</a><br>";
            $Content .= $ApplepiePlugin->feed_generate_headtofoot($a['media']);
            $Content .= "<span style=\"font-size: 12px;\" >" . $contentrss[1] . "</span>";
            $Content .= $ApplepiePlugin->feed_generate_footer();
            return $Content;
        }
    }
    $oseedfeedApp = new oseedfeedAppLe();
    // activation
    register_activation_hook(__FILE__, array($oseedfeedApp, 'activate'));
    // deactivation
    require_once plugin_dir_path(__FILE__). 'inc/feed-app-deactivate.php';
    register_deactivation_hook(__FILE__, array('oseedfeedAppDeactivate', 'deactivate'));
    //Use hooks from parent plugin.  
    add_shortcode('oseedApp', array($oseedfeedApp, 'start_up'));
}
