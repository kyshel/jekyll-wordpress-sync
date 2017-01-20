<?php

class JekyllWordpressSync{


	function get_posts() {
		$link_posts = 'https://api.github.com/repos/'.JK_WP_SYNC_REPO.'/contents/_posts';
		$posts=jws_get_api_obj($link_posts);

		return $posts;
	}


	function get_raw_data() {
		return file_get_contents( 'php://input' );
	}


	function headers() {
		if ( function_exists( 'getallheaders' ) ) {
			return getallheaders();
		}
		/**
		 * Nginx and pre 5.4 workaround.
		 * @see http://www.php.net/manual/en/function.getallheaders.php
		 */
		$headers = array();
		foreach ( $_SERVER as $name => $value ) {
			if ( 'HTTP_' === substr( $name, 0, 5 ) ) {
				$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
			}
		}

		return $headers;
	}

	/**
	 * Validates the header's secret.
	 *
	 * @return true|WP_Error
	 */

	function is_secret_valid() {
		$headers=headers();

		$raw_data=get_raw_data();

	// Validate request secret.
		$hash = hash_hmac( 'sha1', $raw_data, JK_WP_SYNC_SECRET );
		if ( 'sha1=' . $hash !== $headers['X-Hub-Signature'] ) {
			return false;
		}

		return true;
	}




}

?>