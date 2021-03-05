<?php

namespace User\Model;

use Atawa\ApiCaller;
use Curl\Curl;
use Atawa\Utilities;

class Login
{

	public function validateUser($user_id='', $password='', $whatsapp_optin = 0) {

		$api_caller = new ApiCaller();

		$ip_address = Utilities::get_real_user_ip();
		$req_source = Utilities::is_mobile_device() ? 'mobile' : 'computer';

		// dump($user_id, $password, $whatsapp_optin);
		// exit;

		$request_array = array(
			'username' => $user_id,
			'password' => $password,
			'whatsappOptIn' => $whatsapp_optin,
			'grant_type' => 'password',
			'ip_address' => $ip_address,
			'req_source' => $req_source,
		);

		$response = $api_caller->sendRequest('post','authorize',$request_array);
		$status = $response['status'];
		if ($status === 'success') {
			return $this->setLoginCookie($response['response']);
		} else {
			return $response;
		}
	}

	public function validateGoogleCaptcha($private_key='', $user_response='') {
		$curl = new Curl();
		$param_array = array(
			'secret' => $private_key,
			'response' => $user_response,
		);
		$curl->setOpt(CURLOPT_SSL_VERIFYPEER,false);
		$response = $curl->post('https://www.google.com/recaptcha/api/siteverify',$param_array);
		if(isset($response->success)) {
			return $response->success;
		} else {
			return false;
		}
	}	

	public function sendOTP($email_id='') {
		$api_caller = new ApiCaller();
		$request_array = array(
			'emailID' => $email_id
		);
		$api_response = $api_caller->sendRequest('post','forgot-password',$request_array);
		return $api_response;
	}

	public function resetPassword($email_id='', $otp='', $password='') {
		$api_caller = new ApiCaller();
		$request_array = array(
			'emailID' => $email_id,
			'password' => $password,
			'otp' => $otp,
		);
		$api_response = $api_caller->sendRequest('post', 'reset-password', $request_array);
		return $api_response;
	}	

	public function logout() {
		$api_caller = new ApiCaller();
		$request_array = ['action' => 'logout'];
		$api_response = $api_caller->sendRequest('post', 'logout', $request_array);
		return $api_response;
	}

	private function setLoginCookie($response=[]) {
		$devices_a = [];
		if (
					isset($response['access_token']) &&
					isset($response['token_type']) &&
					isset($response['refresh_token']) &&
					isset($response['scope']) &&
					isset($response['expires_in']) &&
					isset($response['uid']) && 
					isset($response['ccode']) &&
					isset($response['uname']) &&
					isset($response['utype']) &&
					isset($response['bc']) &&
					isset($response['lc']) &&
					isset($response['lname']) &&
					isset($response['devices'])
			 )
		{
			// $expires_in = time() + (int)$response['expires_in'];
			$expires_in = time()+(int)$response['expires_in'];
			$cookie_string = $response['access_token'].'##'.$response['refresh_token'].'##'.$expires_in.
											 '##'.$response['cname'].'##'.$response['ccode'].'##'.$response['uid'].
											 '##'.$response['uname'].'##'.$response['utype'].'##'.$response['bc'].
											 '##'.$response['lc'].'##'.$response['lname'].'##'.$response['mrpEditable'].
											 '##'.$response['uidn'].'##'.$response['allowManDiscount'].
											 '##'.$response['edaysInvoice'].'##'.$response['edaysIndent'];

			if(isset($_SESSION['__allowed_devices'])) {
				unset($_SESSION['__allowed_devices']);
				unset($_SESSION['__just_logged_in']);				
			}
			$devices_a = count($response['devices']) > 0 ? array_column($response['devices'],'device_name') : [];
			$_SESSION['__allowed_devices'] = $devices_a;
			$_SESSION['__just_logged_in'] = true;

			// set cookie
			if (setcookie('__ata__',base64_encode($cookie_string),0,'/')) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}	
}