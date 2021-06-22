<?php

namespace Atawa;

use Curl\Curl;
use Atawa\Utilities;

class ApiCaller
{

	private static $instance = null;

	private $api_url = '';

	public function __construct($return_object=false) {

		if(self::$instance == null) {
    	self::$instance = new Curl();
    }

    if($return_object) {
    	return self::$instance;
    }
	}

	public function sendRequest($method='',$uri='',$param_array=[],$process_response=true,$debug=false,$app_token='') {

		// prepare headers.
		self::$instance->setHeader('Content-Type', 'application/json');
		if($uri !== 'authorize' && $uri !== 'send-otp' && $uri !== 'forgot-password' && $uri !== 'reset-password') {
			// get access token
			if($app_token !== '') {
				$access_token = $app_token;
			} else {
				$access_token = Utilities::getAuthToken();
			}
			self::$instance->setHeader('Access-Token', $access_token);
		}

		// get api environment
		$this->api_url = Utilities::get_api_environment();

		// prepare end point
		$end_point = $this->api_url.'/'.$uri;
		// dump($param_array);
		// echo 'end point is....'.$end_point;
		// exit;

		# initiate CURL request.
		switch ($method) {
			case 'post':
				self::$instance->post($end_point, $param_array);	
				break;
			case 'get':
				self::$instance->get($end_point, $param_array);	
				break;
			case 'update':
				break;
			case 'put':
				self::$instance->put($end_point, $param_array);			
				break;
			case 'delete':
				self::$instance->delete($end_point, $param_array);			
				break;			
		}

		// dump(self::$instance->response);
		// echo 'uri is....'.$uri;
		// exit;

		// if($uri=='fin/payments/GxhJXWNSC3MNALH') {
		// 	echo 'kumar';
		// 	dump(self::$instance->response);
		// 	exit;
		// }

		if (self::$instance->error) {
			if((int)self::$instance->errorCode===500) {
				$response = json_decode(self::$instance->response, true);
				if(isset($response['errorcode']) && $response['errortext']) {
					return array('status'=>'failed', 'reason' => $response['errorcode'].'#'.$response['errortext']);
				} else {
					return ['status'=>'failed', 'reason' => '#Unknown error.'];
				}
			} else {
				return array(
					'status'=>'failed', 
					'reason'=> '( '.self::$instance->errorCode.' ) '.self::$instance->errorMessage
				);
			}
		} elseif($process_response) {
			return $this->processResponse($uri,self::$instance->response,self::$instance->httpStatusCode,$debug);
		} else {
			return self::$instance->response;
		}
	}

	public function processResponse($uri='',$api_response='',$http_status_code=0,$debug=false) {
		// var_dump($api_response);
		// exit;
		if($debug) {
			echo '<pre>';
			var_dump($api_response);
			echo '</pre>';
			exit; 
		}

		$response = false;
		$data = json_decode($api_response, true);

		if( is_array($data) && count($data)>0 && $data['status']==='failed' ) {
			$response = array('status'=>$data['status'], 'reason'=>$data['errorcode'].'#'.$data['errortext']);
		} elseif(is_array($data) && count($data)>0 && $data['status']=='success') {
			$response = array('status'=>'success', 'response'=>$data['response']);
		} elseif((int)$http_status_code===204) {# for delete methods.
			$response = array('status'=>'success');
		} elseif($uri == 'authorize') {
			$data = base64_decode($api_response);
			$response = json_decode($data, true);
		}

		if($response === false) {
			// $response = array(
			// 	'status' => 'failed',
			// 	'reason' => 'No response returned by Upstream server.'
			// );
			echo '<pre>';
			if($_SERVER['appEnvironment'] === 'local') {
				var_dump($api_response, '...');
			}
			echo 'Something went wrong :(';
			echo '</pre>';
			exit;
		}

		return $response;
	}

	public function sendRequestExternal($method='',$uri='',$param_array=[],$process_response=true,$debug=false) {
		if(isset($_SESSION['servicesToken'])) {
			$services_token = $_SESSION['servicesToken'];
		} else {
			$services_token = Utilities::get_services_token();
		}

		if( strlen($services_token) === 0) Utilities::redirect('/error-404');

		// prepare headers.
		self::$instance->setHeader('Content-Type', 'application/json');
		self::$instance->setHeader('domain', 'QwikBills');
		self::$instance->setHeader('client_secret', $services_token);

		// get api environment
		$this->api_url = Utilities::get_services_url();

		// prepare end point
		$end_point = $this->api_url.'/'.$uri;
		// dump($param_array);
		// echo 'end point is....'.$end_point;
		// exit;

		# initiate CURL request.
		switch ($method) {
			case 'post':
				self::$instance->post($end_point, $param_array);	
				break;
			case 'get':
				self::$instance->get($end_point, $param_array);	
				break;
			case 'update':
				break;
			case 'put':
				self::$instance->put($end_point, $param_array);			
				break;
			case 'delete':
				self::$instance->delete($end_point, $param_array);			
				break;			
		}

		// dump(self::$instance);
		// exit;

		if (self::$instance->error) {
			if((int)self::$instance->errorCode===500) {
				$response = json_decode(self::$instance->response, true);
				if(isset($response['errorcode']) && $response['errortext']) {
					return array('status'=>'failed', 'reason' => $response['errorcode'].'#'.$response['errortext']);
				} else {
					return ['status'=>'failed', 'reason' => '#Unknown error.'];
				}
			} else {
				return array(
					'status'=>'failed', 
					'reason'=> '( '.self::$instance->errorCode.' ) '.self::$instance->errorMessage,
					'error_message' => isset(self::$instance->response->message) ? self::$instance->response->message : '',
				);
			}
		} else {
			return json_decode(json_encode(self::$instance->response), true);
		}
	}
}