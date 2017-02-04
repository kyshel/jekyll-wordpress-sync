<?php

/**
 * @package jws
 * @version 0.1
 */
/*
Plugin Name: Jekyll Wordpress Sync
Description: Jekyll Wordpress Sync
Author: Kyshel
Version: 0.1
Author URI: http://kyshel.me
*/



require_once(dirname( __FILE__ ) . '/load.php');

add_action( 'admin_init', 'jws_get_settings');
add_action( 'admin_menu', 'jws_add_menu' );


function jws_show_data(){

}

function jws_get_opt_name(){
	$opt_name = array(
		'repo' => 'jws_repo', 
		'token' => 'jws_github_token', 
		'secret' => 'jws_webhook_secret', 
		);
	return $opt_name;
}

function jws_get_settings(){
	$opt_name = jws_get_opt_name();

	define("JK_WP_SYNC_REPO",get_option($opt_name['repo']) );
	define("JWS_GITHUB_TOKEN", get_option($opt_name['token']));
	//define("JK_WP_SYNC_SECRET",get_option($opt_name['secret']));
}


add_action( 'admin_footer', 'jws_javascript' ); // Write our JS below here

function jws_javascript() { ?>
<script type="text/javascript" >
	jQuery(document).ready(function($) {

		var $spinner =$('#jws_jk2wp_analyze_spinner');

		$('#jws_jk2wp_sync').hide();
		$('#jws_jk2wp_cancel').hide();
		$('#jws_jk2wp_done').hide();

		$('#jws_jk2wp_analyze').click(function(e){ 

			var $button = $(this);
			$button.addClass('disabled');
			$spinner.addClass('is-active');

			var data = {
				'action': 'jws_show_diff',
				'whatever': 1234
			};


			$.post(
				ajaxurl,
				data,
				function( response ) {
					//console.log('Got this from the server: ' + response);

					$('#jws_ajax_response').empty();
					$('#jws_ajax_response').html( response );

					$button.removeClass('disabled');
					$spinner.removeClass('is-active');

					$('#jws_jk2wp_analyze').hide();
					$('#jws_jk2wp_done').hide();
					$('#jws_jk2wp_sync').show();
					$('#jws_jk2wp_cancel').show();
				}
				);
		});


		$('#jws_jk2wp_sync').click(function(e){ 

			var $button = $(this);
			$button.addClass('disabled');
			$('#jws_jk2wp_cancel').addClass('disabled');
			$spinner.addClass('is-active');

			var data = {
				'action': 'jws_sync',
				'whatever': 1234
			};


			$.post(
				ajaxurl,
				data,
				function( response ) {
					//console.log('Got this from the server: ' + response);

					$('#jws_ajax_response').empty();
					$('#jws_ajax_response').html( response );

					$button.removeClass('disabled');
					$('#jws_jk2wp_cancel').removeClass('disabled');
					$spinner.removeClass('is-active');

					$('#jws_jk2wp_analyze').hide();
					$('#jws_jk2wp_sync').hide();
					$('#jws_jk2wp_cancel').hide();
					$('#jws_jk2wp_done').show();
				}
				);
		});
	});

</script><?php
}

add_action( 'wp_ajax_jws_show_diff', 'jws_jk2wp_show_diff' );
add_action( 'wp_ajax_jws_sync', 'jws_jk2wp_sync' );