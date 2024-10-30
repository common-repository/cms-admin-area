<?php
/**
Plugin Name: CMS admin area
Plugin URI: http://wordpress.org/extend/plugins/cms-admin-area/
Description: Simple plugin adds CMS-like features:  user-friendly horizontal menu and  dashboard metaboxes, completely remove blogging plarform widgets. It gives your clients a better experience of their new website.
Author: Piotr Bielecki
Version: 1.1
Author URI: http://netbiel.pl/
*/

require_once('classes/admin_area_class.php');
require_once('classes/admin_area_helper.php');

function cms_admin_area_language_init() {
  load_plugin_textdomain( 'cms-admin-area',null, basename(dirname(__FILE__)).'/languages' );

}
add_action('init', 'cms_admin_area_language_init');


add_action( 'plugins_loaded', create_function( '', '$admin_area_class = new Admin_Area_Class;' ) );


