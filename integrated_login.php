<?php 
require_once 'api/smf_2_api.php';
define('CHECK_USER_ENDPOINT', "http://localhost/rest/authentication/phpbb");
define('LATROQUETTE_TOKEN', "ltq_token");
define('LATROQUETTE_LOGIN', "ltq_usr");
define('SMF_INTEGRATION_SETTINGS', serialize(array(
'integrate_change_email' => 'change_email_function',
'integrate_change_member_data' => 'change_member_data_function',
'integrate_reset_pass' => 'reset_pass_function',
'integrate_exit' => 'exit_function',
'integrate_logout' => 'logout_function',
'integrate_outgoing_email' => 'outgoing_email_function',
'integrate_login' => 'login_function',
'integrate_validate_login' => 'validate_login_function',
'integrate_redirect' => 'redirect_function',
'integrate_delete_member' => 'delete_member_function',
'integrate_register' => 'register_function',
'integrate_pre_load' => 'pre_load_function',
'integrate_whos_online' => 'whos_online_function',
)));

function check_user($username, $password, $byToken = true){
	//set POST variables
	$fields = array(
			'login' => urlencode($username),
			'password' => urlencode($password),
			'byToken' => $byToken ? 'true' : 'false'
	);
	$fields_string = '';
	//url-ify the data for the POST
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	$fields_string = rtrim($fields_string, '&');

	//open connection
	$request = curl_init();

	//set the url, number of POST vars, POST data
	curl_setopt($request,CURLOPT_URL, CHECK_USER_ENDPOINT);
	curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($request,CURLOPT_POST, count($fields));
	curl_setopt($request,CURLOPT_POSTFIELDS, $fields_string);
	//execute post
	$result = json_decode(curl_exec($request));
	//close connection
	curl_close($request);
	return $result;
}

function is_valid_user_info($user_info){
	return isset($user_info['idSet']) && $user_info['idSet'];
}

function integrate_verify_user(){
	$token = $_COOKIE[LATROQUETTE_TOKEN];
	$username = $_COOKIE[LATROQUETTE_LOGIN];
	$user_info = check_user($username, $password);
	if(is_valid_user_info($user_info)){
		$user_info = smfapi_getUserByUsername($user_info['login']);
		if($user_info){
			return $user_info['id'];
		}else{
			//TODO for now it will simply not work. Warn this user in admin panel
			return 0;
		}
	}
	return 0;
}


?>