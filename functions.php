<?php


function jws_jk2wp_sync(){
	$insert=jws_jk2wp_get_insert();
	$inserted=[];

	if (!empty($insert)) {
		foreach ($insert as $insert_index => $insert_post) {
			$wp_post_inserted_id = wp_insert_post( $insert_post, true );

			if ($wp_post_inserted_id ) {
				$inserted[]=get_post( $wp_post_inserted_id );
				$message = 'Sync success!';
			}else{
				$message = 'Sync fail!';
			}
		}
		jws_jk2wp_result($inserted);
	}else{
		$message = 'No post need to sync!';
	}?>

	<div ><p><strong><?php _e($message, 'jws' ); ?></strong></p></div>

<?php
	wp_die();
}

function jws_jk2wp_result($sync_ok){
	echo '<div class="postbox">Synced list:<br>';
	foreach (array_reverse($sync_ok)  as $key => $post) {
		echo $post->post_title.'<br>';
	}
	echo '</div>';
}

function jws_jk2wp_get_insert(){
	$Parsedown = new Parsedown();
	$diff=jws_jk2wp_get_diff();
	$insert=[];



	foreach ($diff['jk_add'] as $jk_add_index => $jk_posts_index) {
		$jk_post=$diff['jk_posts'][$jk_posts_index];
		$post_title=jws_cut_jk_filename($jk_post->name);
		if ($post_title == JWS_JK_WRONG_POST_NAME) {
			continue;
		}

		$file=jws_get_api_obj($jk_post->_links->self);
		$post_content_raw=jws_base64_to_md($file->content);
		$post_content= JWS_AUTO_MD2HTML ? $Parsedown->text($post_content_raw) : $post_content_raw ;
		$post_date = jws_get_jk_post_date($jk_post->name);

		$my_post = array(
			'post_title'    => jws_cut_jk_filename($jk_post->name),
			'post_content'  => $post_content,
			'post_date'  => $post_date,
			'post_status'   => 'publish',
			'meta_input'   => array(
				JWS_POST_META_SHA_KEY => $jk_post->sha,
				),
			//'post_author'   => 1,
			);
		//echo '<pre>' . var_export($my_post, true) . '</pre>';
		$insert[]=$my_post;
	}

	foreach ($diff['jk_update'] as $jk_update_index => $jk_posts_index) {
		$wp_post_name_exist_index = $diff['wp_update'][$jk_update_index];
		$post_id=$diff['wp_posts'][$wp_post_name_exist_index] -> ID;
			
		$jk_post=$diff['jk_posts'][$jk_posts_index];
		$post_title=jws_cut_jk_filename($jk_post->name);
		if ($post_title == JWS_JK_WRONG_POST_NAME) {
			continue;
		}

		$post_date = jws_get_jk_post_date($jk_post->name);

		$file=jws_get_api_obj($jk_post->_links->self);
		$post_content_raw=jws_base64_to_md($file->content);
		$post_content= JWS_AUTO_MD2HTML ? $Parsedown->text($post_content_raw) : $post_content_raw ;

		$my_post = array(
			'ID' => $post_id,
			'post_title'    => $post_title,
			'post_content'  => $post_content,
			'post_date'  => $post_date,
			'post_status'   => 'publish',
			'meta_input'   => array(
				JWS_POST_META_SHA_KEY => $jk_post->sha,
				),
			//'post_author'   => 1,
			);
		//echo '<pre>' . var_export($my_post, true) . '</pre>';
		$insert[]=$my_post;
	}

	return $insert;
}



function jws_base64_to_md($str){
	$raw_md=base64_decode ($str,true);

	$needle	='---';
	if ((strtok(ltrim($raw_md), "\n") == $needle) || (strtok(ltrim($raw_md), "\n\r") == $needle) ) {
		$pos1 = strpos($raw_md, $needle);
		$pos2 = strpos($raw_md, $needle, $pos1 + strlen($needle));
		$md_without_frontmatter=substr($raw_md , $pos2 + 3);
	}else{
		$md_without_frontmatter = $raw_md;
	}

	return ltrim($md_without_frontmatter); 
}

function jws_jk2wp_show_diff(){

		if (JK_WP_SYNC_REPO == '') {
			echo 'not_config';
			die();
		}?>

		<div><p><strong><?php _e('Analyze result:', 'jws' ); ?></strong></p></div>

<?php

	$diff=jws_jk2wp_get_diff();

	//kred($diff['jk_add'] );

	echo '<div class="postbox">Will add:<br>';
	if (!empty($diff['jk_add'])) {
		foreach ($diff['jk_add'] as $index => $posts_index) {	
			echo $diff['jk_posts'][$posts_index]->name.'<br>';
		}
	}else{
		echo 'None';
	}
	echo '</div>';

	echo '<div class="postbox">Will update:<br>';
	if (!empty($diff['jk_update'])) {
		foreach ($diff['jk_update'] as $index => $posts_index) {	
			echo $diff['jk_posts'][$posts_index]->name.'<br>';
		}
	}else{
		echo 'None';
	}
	echo '</div>';
/*
	echo '<div class="postbox">Keep reserve jk-wp:<br>';
	foreach ($diff['jk_reserve'] as $index => $posts_index) {
		echo $diff['jk_posts'][$posts_index]->name.'<br>';
	}
	echo '</div>';

	echo '<div class="postbox">Not touch wp:<br>';
	foreach ($diff['wp_beyond'] as $index => $posts_index) {
		echo $diff['wp_posts'][$posts_index]->post_title.'<br>';
	}
	echo '</div>';
*/

	// echo 'Will add jk:<pre>' . var_export($diff['jk_add'], true) . '</pre>';
	// echo 'Will update jk-wp:<pre>' . var_export($diff['jk_update'], true) . '</pre>';
	// echo 'Keep reserve jk-wp:<pre>' . var_export($diff['jk_reserve'], true) . '</pre>';
	// echo 'Not touch wp:<pre>' . var_export($diff['wp_beyond'], true) . '</pre>';

	wp_die();
}



function jws_cut_jk_filename($jk_filename){

	$jk_post_name = JWS_JK_WRONG_POST_NAME;
	$jekyll_post_pattern='/^(.+\/)*(\d+-\d+-\d+)-(.*)(\.[^.]+)$/';

	if ( 1 == preg_match($jekyll_post_pattern,$jk_filename) ) {
		$jk_post_name_explode=explode('-',$jk_filename,4);
		$jk_post_name_array= pathinfo($jk_post_name_explode[3]);
		$jk_post_name=$jk_post_name_array['filename'];
		$jk_post_name=rtrim($jk_post_name);
	}

	return $jk_post_name;
}

function jws_get_jk_post_date($jk_filename){

	$jk_post_date = 'not_set';
	$jekyll_post_pattern='/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/';

	if ( 1 == preg_match($jekyll_post_pattern,$jk_filename,$matches) ) {
		$jk_post_date=$matches[0];
	}

	return $jk_post_date;
}



function jws_jk2wp_get_diff(){

	$meta_sha_name=JWS_POST_META_SHA_KEY;

	$jk_posts=jws_get_jk_posts();
	$wp_posts=jws_get_wp_posts();
	//echo '<pre>' . var_export($wp_posts, true) . '</pre>';
	//echo '<pre>' . var_export($jk_posts, true) . '</pre>';

	
	$wp_post_names=[];
	$wp_post_ids=[];
	$jk_post_names=[];
	$jk_post_shas=[];

	foreach ($wp_posts as $wp_index => $wp_post) {
		$wp_post_names[]=$wp_post->post_title;
		$wp_post_ids[]=$wp_post->ID; // could remove
	}

	foreach ($jk_posts as $jk_index => $jk_post) {	
		$jk_post_names[]= jws_cut_jk_filename($jk_post->name);
		$jk_post_shas[]=$jk_post->sha; // could remove
	}

	
	//echo '<pre>jk_post_names:' . var_export($jk_post_names, true) . '</pre>';
	//echo '<pre>jk_post_shas:' . var_export($jk_post_shas, true) . '</pre>';
	//echo '<pre>wp_post_names:' . var_export($wp_post_names, true) . '</pre>';
	//echo '<pre>wp_post_ids:' . var_export($wp_post_ids, true) . '</pre>';

	// foreach ($jk_post_names as $jk_post_index => $jk_post_name){
	// 	$wp_post_name_exist_index = array_search($jk_post_name, $wp_post_names);
	// 	if ($wp_post_name_exist_index) {
	// 		echo 'same_name:'.$wp_post_names[$wp_post_name_exist_index].'<br>';
	// 	}
	// }




	$jk_reserve=[];
	$jk_update=[];
	$jk_add=[];

	$wp_reserve=[];
	$wp_update=[];
	$wp_beyond=[];

	foreach ($jk_post_names as $jk_post_index => $jk_post_name) {
		if (false !== $wp_post_name_exist_index = array_search($jk_post_name, $wp_post_names)) {
			// has same name, is_has_sha ?

			//echo 'same_name:'.$wp_post_names[$wp_post_name_exist_index].'<br>';

			if ('' != $wp_post_git_sha=get_post_meta( $wp_post_ids[$wp_post_name_exist_index],$meta_sha_name,true)) {
				// has sha, is_update?

				if ($wp_post_git_sha == $jk_post_shas[$jk_post_index]) {
					// finish , has sha
					$jk_reserve[]=$jk_post_index;
					//$wp_reserve[]=$wp_post_ids[$wp_post_name_exist_index];	
					$wp_reserve[]=$wp_post_name_exist_index;	
				}else{
					// update , has sha
					$jk_update[]=$jk_post_index;
					//$wp_update[]=$wp_post_ids[$wp_post_name_exist_index];
					$wp_update[]=$wp_post_name_exist_index;	
				}
				
			}else{
				// same name but not has sha
				$jk_add[]=$jk_post_index;
			}

		}else{
			// new name
			$jk_add[]=$jk_post_index;
		}

	}

	if (count($wp_post_names) != 0) {
		$wp_beyond=array_diff(range(0, count($wp_post_names)-1),$wp_reserve,$wp_update);
	}
	


	// kred('<pre>wp_reserve ' . var_export($wp_reserve, true) . '</pre>');
	// kred('<pre>wp_update ' . var_export($wp_update, true) . '</pre>');
	// kred('<pre>wp_beyond ' . var_export($wp_beyond, true) . '</pre>');

	$jk2wp_diff =[
	'jk_reserve' => $jk_reserve,
	'jk_update' => $jk_update,
	'jk_add' => $jk_add,
	'wp_reserve' => $wp_reserve,
	'wp_update' => $wp_update,
	'wp_beyond' => $wp_beyond,

	'jk_posts' => $jk_posts,
	'wp_posts' => $wp_posts
	];

	return $jk2wp_diff;
}


function jws_get_wp_posts(){
	$args = array(
		'post_status' => 'publish',
		'numberposts' => -1,
		);

	$posts=get_posts($args);
	//echo '<pre>' . var_export($posts, true) . '</pre>';
	return $posts;
}



function jws_get_jk_posts(){
	$jws = new JekyllWordpressSync();
	$posts = $jws -> get_posts();

	return $posts;
}

/*
function _jws_get_jk_posts(){
	$str = file_get_contents(dirname( __FILE__ ) . '/b.json');
	$posts = json_decode($str);

	return $posts;
}
*/


function jws_get_api_obj($file_link){
	if ( JWS_GITHUB_TOKEN != '' ) {
		$opts = 
		[
		'http' => [
		'method' => 'GET',
		'header' => [
		'User-Agent: PHP',
		'Authorization:token '.JWS_GITHUB_TOKEN
		]
		]
		];
	}else{
		$opts = 
		[
		'http' => [
		'method' => 'GET',
		'header' => [
		'User-Agent: PHP'
		]
		]
		];
	}
	

	$context = stream_context_create($opts);
	$file_str=file_get_contents($file_link, false, $context);

	$file_obj=json_decode($file_str);

	return $file_obj;
}












function jws_add_menu() {

	add_menu_page( 'Jekyll-WP-Sync', 'Jekyll-WP-Sync', 'manage_options', 'jws_menu', 'jws_jk2wp_page' );

	// the fifth param 'jws_menu' was compromise
	add_submenu_page( 'jws_menu', 'JWS Sync', 'Sync', 'manage_options', 'jws_menu', 'jws_jk2wp_page');
	add_submenu_page( 'jws_menu', 'JWS Setting', 'Setting', 'manage_options', 'jws_setting', 'jws_setting_page');
}

function jws_jk2wp_page() {
    //must check that the user has the required capability 
	if (!current_user_can('manage_options'))
	{
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	echo '<div class="wrap">';
	echo "<h2>" . __( 'Jekyll Wordpress Sync', 'jws' ) . "</h2>";?>

	<br>
	<button type="button" id="jws_jk2wp_analyze" class="button" >Analyze</button>
	<button type="button" id="jws_jk2wp_sync" class="button button-primary" style="display: none;">Sync Now</button>
	<a href="admin.php?page=jws_menu" ><button type="button" class="button" id="jws_jk2wp_cancel" style="display: none;">Cancel</button></a>
	<a href="admin.php?page=jws_menu"><button type="button" class="button" id="jws_jk2wp_done" style="display: none;">Done</button></a>

	<span class="spinner" id="jws_jk2wp_analyze_spinner" style="float:initial;"></span>

	<div id="jws_ajax_response"></div>

<?php
	echo '</div>'; // div-wrap
}


function jws_setting_page() {
    //must check that the user has the required capability 
	if (!current_user_can('manage_options'))
	{
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	$opt_name = jws_get_opt_name();



	if (isset($_POST[ $opt_name['repo'] ]) && isset($_POST[ $opt_name['token'] ]) ) {
		if( !empty($_POST[ $opt_name['repo'] ]) && !empty($_POST[ $opt_name['token'] ]) ) {

			update_option( $opt_name['repo'], $_POST[ $opt_name['repo'] ] );
			update_option( $opt_name['token'], $_POST[ $opt_name['token'] ]);
			//update_option( $opt_name['secret'], $_POST[ $opt_name['secret'] ]);

			?><div class="updated"><p><strong><?php _e('Settings saved.', 'jws' ); ?></strong></p></div><?php
		}else{
			?><div class="error"><p><strong><?php _e('Repository and Github Token must not empty!', 'jws' ); ?></strong></p></div><?php
		}
	}


	// Read in existing option value from database
	$repo = get_option( $opt_name['repo'] );
	$token = get_option( $opt_name['token'] );
	$secret = get_option( $opt_name['secret'] );
	
	echo '<div class="wrap">';
	echo "<h2>" . __( 'Jekyll Wordpress Sync Setting', 'jws' ) . "</h2>";
	?>

	<form name="form1" method="post" action="">


		<table class="form-table">
			<tr>
				<th><?php _e("Repository", 'jws' ); ?></th>
				<td>
					<input type="text" name="<?php echo $opt_name['repo']; ?>" value="<?php echo get_option( $opt_name['repo'] ); ?>" size="20" required>
					<p class="description">
						Format:  <code>[OWNER]/[REPOSITORY]</code>
						Example:  <code>kyshel/kyshel.github.io</code> 
					</p>
				</td>
			</tr>

			<tr>
				<th><?php _e("Github Token", 'jws' ); ?></th>
				<td>
					<input type="text" name="<?php echo $opt_name['token']; ?>" value="<?php echo get_option( $opt_name['token'] ); ?>" size="40" required>
					<p class="description">
						A <a href="https://github.com/settings/tokens/new">personal oauth token</a> , aims to improve the maximum number of requests that the consumer is permitted to make per hour, from 60/h to 5000/h.
					</p>
				</td>
			</tr>

			<!--tr>
				<th><?php _e("Webhook Secret:", 'jws' ); ?></th>
				<td>
					<input type="text" name="<?php echo $opt_name['secret']; ?>" value="<?php echo get_option( $opt_name['secret'] ); ?>" size="20">
					<p class="description">
						The webhook's secret phrase.
					</p>
				</td>
			</tr-->



		</table>

		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
		</p>
	</form>
<?php
	echo '</div>'; // div-wrap
}

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
					if (response == 'not_config'){
						$spinner.removeClass('is-active');
						$('#jws_ajax_response').html('<div class="error"><p><strong>You did not set repo in settings, please <a href="admin.php?page=jws_setting">click here</a> to set!</strong></p></div>');
					}else{
						$('#jws_ajax_response').html( response );

						$button.removeClass('disabled');
						$spinner.removeClass('is-active');

						$('#jws_jk2wp_analyze').hide();
						$('#jws_jk2wp_done').hide();
						$('#jws_jk2wp_sync').show();
						$('#jws_jk2wp_cancel').show();
					}


					
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


?>