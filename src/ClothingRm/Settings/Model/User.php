<?php

namespace ClothingRm\User\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class User {

	public function get_users($search_params=array()) {
		$client_id = Utilities::get_current_client_id();
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','users/'.$client_id,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'users'=>$response['response']['users']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function get_user_details($uuid='',$search_params=array()) {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'users/'.$uuid.'/'.$client_id;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'userDetails'=>$response['response']['userDetails']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function update_user($user_details='',$uuid='') {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'users/'.$uuid.'/'.$client_id;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put',$request_uri,$user_details);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function create_user($user_details='') {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'users/'.$client_id;
		// dump($user_details);
		// exit;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$user_details);
		// dump($response);
		// exit;
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true, 'uuid'=>$response['response']['uid']);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}	

}