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
	}
	
	

	kbn($message);
}

function jws_jk2wp_result($sync_ok){
	echo '<div class="postbox">Synced list:<br>';
	foreach ($sync_ok as $key => $post) {
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

		$file=jws_get_api_obj($jk_post->_links->self);
		$post_content_raw=jws_base64_to_md($file->content);
		$post_content= JWS_AUTO_MD2HTML ? $Parsedown->text($post_content_raw) : $post_content_raw ;

		$my_post = array(
			'ID' => $post_id,
			'post_title'    => $post_title,
			'post_content'  => $post_content,
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

	?>
	<form method="post" action="">
		<button type="submit" name="jws_jk2wp_sync">jk2wp_sync</button>
	</form>
	<?php

	$diff=jws_jk2wp_get_diff();

	//kred($diff['jk_add'] );

	echo '<div class="postbox">Will add jk:<br>';
	foreach ($diff['jk_add'] as $index => $posts_index) {	
		echo $diff['jk_posts'][$posts_index]->name.'<br>';
	}
	echo '</div>';

	echo '<div class="postbox">Will update jk-wp:<br>';
	foreach ($diff['jk_update'] as $index => $posts_index) {		
		echo $diff['jk_posts'][$posts_index]->name.'<br>';		
	}
	echo '</div>';

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


	// echo 'Will add jk:<pre>' . var_export($diff['jk_add'], true) . '</pre>';
	// echo 'Will update jk-wp:<pre>' . var_export($diff['jk_update'], true) . '</pre>';
	// echo 'Keep reserve jk-wp:<pre>' . var_export($diff['jk_reserve'], true) . '</pre>';
	// echo 'Not touch wp:<pre>' . var_export($diff['wp_beyond'], true) . '</pre>';

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

	$a=get_posts($args);
	//echo '<pre>' . var_export($a, true) . '</pre>';
	return $a;
}



function jws_get_jk_posts(){
	$jws = new JekyllWordpressSync();
	$posts = $jws -> get_posts();

	return $posts;
}

function _jws_get_jk_posts(){
	$str = file_get_contents(dirname( __FILE__ ) . '/b.json');
	$posts = json_decode($str);

	return $posts;
}



function jws_get_api_obj($file_link){
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

	$context = stream_context_create($opts);
	$file_str=file_get_contents($file_link, false, $context);

	$file_obj=json_decode($file_str);

	return $file_obj;
}












function jws_add_menu() {

	add_menu_page( 'Page: Jekyll-WP-Sync', 'Jekyll-WP-Sync', 'manage_options', 'jws_menu', 'jws_jk2wp_page' );

	// the fifth param 'jws_menu' was compromise
	add_submenu_page( 'jws_menu', 'Jekyll -> WP Page', 'Jekyll->WP', 'manage_options', 'jws_menu', 'jws_jk2wp_page');
	add_submenu_page( 'jws_menu', 'Jekyll-WP-Sync Setting', 'Setting', 'manage_options', 'jws_setting', 'jws_setting_page');
}

function jws_jk2wp_page() {
    //must check that the user has the required capability 
	if (!current_user_can('manage_options'))
	{
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}


	if( isset($_POST['jws_jk2wp_analyze'])) {
		?>
		<div class="updated"><p><strong><?php _e('Jekyll -> Wordpress Analyze Finish!', 'jws' ); ?></strong></p></div>
		<?php
	}





	echo '<div class="wrap">';
	echo "<h2>" . __( 'Jekyll -> Worpdress', 'jws' ) . "</h2>";
	?>

	<form method="post" action="">
		<button type="submit" name="jws_jk2wp_analyze">jk2wp_analyze</button>
	</form>

	<?php
	if( isset($_POST['jws_jk2wp_analyze'])) {
		//require_once(dirname( __FILE__ ) . '/import.php');
		jws_show_data();
	}
	if( isset($_POST['jws_jk2wp_sync'])) {
		jws_jk2wp_sync();
	}

	echo '</div>'; // div-wrap
}


function jws_setting_page() {
    //must check that the user has the required capability 
	if (!current_user_can('manage_options'))
	{
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	$opt_name = jws_get_opt_name();
	$hidden_field_name = 'jws_submit_hidden';


	if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
		update_option( $opt_name['repo'], $_POST[ $opt_name['repo'] ] );
		update_option( $opt_name['token'], $_POST[ $opt_name['token'] ]);
		update_option( $opt_name['secret'], $_POST[ $opt_name['secret'] ]);

		?>
		<div class="updated"><p><strong><?php _e('settings saved.', 'jws' ); ?></strong></p></div>
		<?php
	}

	// Read in existing option value from database
	$repo = get_option( $opt_name['repo'] );
	$token = get_option( $opt_name['token'] );
	$secret = get_option( $opt_name['secret'] );
	
	echo '<div class="wrap">';
	echo "<h2>" . __( 'Jekyll Wordpress Sync Settings', 'jws' ) . "</h2>";
	?>

	<form name="form1" method="post" action="">
		<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

		<p><?php _e("Repo name:", 'jws' ); ?> 
			<input type="text" name="<?php echo $opt_name['repo']; ?>" value="<?php echo $repo; ?>" size="20">
		</p><hr />

		<p><?php _e("Github Token:", 'jws' ); ?> 
			<input type="text" name="<?php echo $opt_name['token']; ?>" value="<?php echo $token; ?>" size="20">
		</p><hr />

		<p><?php _e("Webhook Secret:", 'jws' ); ?> 
			<input type="text" name="<?php echo $opt_name['secret']; ?>" value="<?php echo $secret; ?>" size="64">
		</p><hr />

		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
		</p>
	</form>
	<?php
	echo '</div>'; // div-wrap
}

// kyshel_big_noise
function kbn($str,$var_name =''){
	echo '<h1>'.$var_name.' ->'.$str.'-<</h1>';
}



?>

