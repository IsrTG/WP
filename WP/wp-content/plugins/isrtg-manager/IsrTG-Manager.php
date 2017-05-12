<?php
/*
Plugin Name: IsrTG Manager
Description: Manage IsrTG Clan for WordPress
Version:     1.0
Author:      Omer Efrat
*/
define("ISRTG_PLUGIN_NAME", "IsrTG Manager");
define("ISRTG_PLUGIN_PATH", plugin_dir_path( __FILE__ ));
/*
function isrtg_init()
{
	$dir = plugin_dir_path( __FILE__ );
	
    function isrtg_shortcode_isrtg($atts = [], $content = null)
    {
		//isrtg_error("Unknown page");
		ob_start();
		include($dir.$atts["page"]);
		$content = ob_get_clean();
		
		return $content;
    }
    add_shortcode('isrtg', 'isrtg_shortcode_isrtg');
}
add_action('init', 'isrtg_init');

function isrtg_error($error) {
	return ISRTG_PLUGIN_NAME.": ".$error; 
}
*/
//require_once "functions.php";
include 'constants.php';
function myfunction() {
    require_once "functions.php";
}
add_action('init', 'myfunction');

function plugin_activation() {
    if (! wp_next_scheduled ( 'isrtg_hourly_event' )) {
        wp_schedule_event(time(), 'hourly', 'isrtg_hourly_event');
    }
}
register_activation_hook(__FILE__, 'plugin_activation');

function plugin_deactivation() {
    wp_clear_scheduled_hook('isrtg_hourly_event');
}
register_deactivation_hook(__FILE__, 'plugin_deactivation');

include "cron.php";
include "calendar_widget.php";
include "info_widget.php";
?>