<?php

/**
 * @package jws
 * @version 0.1
 */
/*
Plugin Name: Jekyll Wordpress Sync
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: Jekyll Wordpress Sync
Author: Kyshel
Version: 0.1
Author URI: http://kyshel.me
*/

require_once(dirname( __FILE__ ) . '/load.php');

add_action( 'admin_menu', 'jws_add_menu' );

function jws_show_data(){


	//echo 'wp <pre>' . var_export(jws_get_wp_posts(), true) . '</pre>';

	//echo 'jk <pre>' . var_export(jws_get_jk_posts(), true) . '</pre>';

	 //jws_jk2wp_show_diff();

	jws_jk2wp_sync();

}


