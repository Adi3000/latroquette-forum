<?php 
require_once 'smf_2_api.php';
define('CHECK_USER_ENDPOINT', "http://localhost/rest/authentication/smf/auth");
define('REGISTER_USER_ENDPOINT', "http://localhost/rest/authentication/smf/register");
define('LATROQUETTE_TOKEN', "ltq_token");
define('LATROQUETTE_LOGIN', "ltq_user");
define('SMF_INTEGRATION_SETTINGS', serialize(array(
'integrate_verify_user' => 'ltq_verify_user',
// 'integrate_change_email' => 'change_email_function',
// 'integrate_change_member_data' => 'change_member_data_function',
// 'integrate_reset_pass' => 'reset_pass_function',
// 'integrate_exit' => 'exit_function',
'integrate_logout' => 'logout_function',
// 'integrate_outgoing_email' => 'outgoing_email_function',
// 'integrate_login' => 'login_function',
// 'integrate_validate_login' => 'validate_login_function',
// 'integrate_redirect' => 'redirect_function',
// 'integrate_delete_member' => 'delete_member_function',
'integrate_register' => 'ltq_register_function',
// 'integrate_pre_load' => 'pre_load_function',
// 'integrate_whos_online' => 'whos_online_function',
)));

function send_to_latroquette($endpoint,$fields){
	$fields_string = '';
	//url-ify the data for the POST
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	$fields_string = rtrim($fields_string, '&');
	//open connection
	$request = curl_init();
	
	//set the url, number of POST vars, POST data
	curl_setopt($request,CURLOPT_URL, $endpoint);
	curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($request,CURLOPT_POST, count($fields));
	curl_setopt($request,CURLOPT_POSTFIELDS, $fields_string);
	//execute post
	$output = curl_exec($request);
	$result = json_decode($output, true);
	//close connection
	curl_close($request);
	return $result;
}
function check_user($username, $password, $byToken = true){
	//set POST variables
	$fields = array(
			'login' => urlencode($username),
			'password' => urlencode($password),
			'byToken' => $byToken ? 'true' : 'false'
	);
	return send_to_latroquette(CHECK_USER_ENDPOINT);
}

function is_valid_user_info($user_info){
	return isset($user_info['id']) ;
}

function ltq_verify_user(){
	$token = $_COOKIE[LATROQUETTE_TOKEN];
	$username = $_COOKIE[LATROQUETTE_LOGIN];
	$user_info = check_user($username, $token);
	if(is_valid_user_info($user_info)){
		$user_info = smfapi_getUserByUsername($user_info['login']);
		if($user_info){
			smfapi_login($username);
			return $user_info['id'];
		}else{
			//TODO for now it will simply not work. Warn this user in admin panel
			return 0;
		}
	}
	return 0;
}

function ltq_register_user($regOptions){
	$loginState = 1;
	if($regOptions['require'] == 'normal'){
		$loginState = 2;
	}
	$fields = array(
			'login' => urlencode($regOptions['username']),
			'password' => urlencode($regOptions['password']),
			'email' => urlencode($regOptions['email']),
			'loginState' => $loginState
	);
	$result = send_to_latroquette($endpoint,$fields);
}
function ltq_logout_user($username){
	if (isset($_COOKIE[LATROQUETTE_LOGIN])) {
		unset($_COOKIE[LATROQUETTE_LOGIN]);
		setcookie(LATROQUETTE_LOGIN, null, 0, '/');
	}
	if (isset($_COOKIE[LATROQUETTE_TOKEN])) {
		unset($_COOKIE[LATROQUETTE_TOKEN]);
		setcookie(LATROQUETTE_TOKEN, null, 0, '/');
	}
}


?>