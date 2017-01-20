<?php

/**
 * @package jws
 * @version 0.1
 */
/*
Plugin Name: jws
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: Jekyll Wordpress sync
Author: Kyshel
Version: 0.1
Author URI: http://kyshel.me
*/

require_once(dirname( __FILE__ ) . '/load.php');

add_action( 'admin_menu', 'jws_add_menu' );


