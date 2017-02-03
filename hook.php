<?php
require_once('load.php');


if (is_secret_valid()) {
	echo "Your identity has verified \n";
}else{
	echo "You are banned!";
	die();
}

$data = get_raw_data();
$obj_data=json_decode($data);
//var_dump($obj_data);

$json_string = json_encode($obj_data,  JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
file_put_contents('a.json', $json_string);

$commit_id=$obj_data->head_commit ? $obj_data->head_commit->id : null;
echo 'commit_id is: '.$commit_id.'<br>';


