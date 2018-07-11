<?php

namespace ClothingRm\User\Model;

use Atawa\ApiCaller;
use Curl\Curl;

class Login
{

	public function validateUser($user_id, $password) {

		$api_caller = new ApiCaller();

		$request_array = array(
			'username' => $user_id,
			'password' => $password,
			'grant_type' => 'password'
		);

		$response = $api_caller->sendRequest('post','authorize',$request_array);
		$status = $response['status'];
		if ($status === 'success') {
			return $this->setLoginCookie($response['response']);
		} else {
			return false;
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

	private function setLoginCookie($response=array()) {
		if (
					isset($response['access_token']) &&
					isset($response['token_type']) &&
					isset($response['refresh_token']) &&
					isset($response['scope']) &&
					isset($response['expires_in']) &&
					isset($response['uid']) && 
					isset($response['ccode']) &&
					isset($response['uname'])
			 )
		{
			$expires_in = time()+(int)$response['expires_in'];
			$fin_year = isset($response['finY'])?$response['finY']:'';
			$cookie_string = $response['access_token'].'##'.$response['refresh_token'].'##'.$expires_in.
											 '##'.$response['cname'].'##'.$response['ccode'].'##'.$response['uid'].'##'.$response['uname'];
			// set cookie
			if (setcookie('__ata__',base64_encode($cookie_string),$expires_in,'/')) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}

	}

}