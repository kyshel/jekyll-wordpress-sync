<?php

// kyshel_big_noise
function kbn($str,$var_name =''){
	echo '<h1>'.$var_name.' ->'.$str.'-<</h1>';
}


function kred($var = 'U did not set var',$var_name=''){
	if (is_string($var)  ) {
		echo '<div style="color:red;">'.$var_name.' <pre>'.$var.'</pre></div>';
	}elseif (is_object($var)) {
		echo '<div style="color:red;">'.$var_name.' '.'<pre>' . var_export($var, true) . '</pre>'.'</div>';
	}elseif (is_array($var)) {
		echo '<div style="color:red;">'.$var_name.' '.'<pre>' . var_export($var, true) . '</pre>'.'</div>';
	}else{
		echo '<div style="color:red;">'.$var_name.' '.'<pre>' . var_export($var, true) . '</pre>'.'</div>';
	}
	
}

function kgreen($var = 'U did not set var',$var_name=''){
	if (is_string($var)) {
		echo '<div style="color:green;">'.$var_name.' <pre>'.$var.'</pre></div>';
	}else{
		echo '<div style="color:green;">'.$var_name.' '.'<pre>' . var_export($var, true) . '</pre>'.'</div>';
	}
	
}


?>