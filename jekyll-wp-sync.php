<?php

/**
 * @package jws
 * @version 0.1
 */
/*
Plugin Name: Jekyll Wordpress Sync
Description: Import posts from a jekyll site that hosted on GitHub.
Author: Kyshel
Version: 0.1
Plugin URI: https://github.com/kyshel/jekyll-wordpress-sync
Author URI: http://kyshel.me
License: GPLv2 or later
*/



require_once(dirname( __FILE__ ) . '/load.php');

add_action( 'admin_init', 'jws_get_settings');
add_action( 'admin_footer', 'jws_javascript' ); 
add_action( 'admin_menu', 'jws_add_menu' );

add_action( 'wp_ajax_jws_show_diff', 'jws_jk2wp_show_diff' );
add_action( 'wp_ajax_jws_sync', 'jws_jk2wp_sync' );