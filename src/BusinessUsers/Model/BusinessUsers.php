<?php

namespace BusinessUsers\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class BusinessUsers
{
	public function create_business_user($params = []) {
		$client_id = Utilities::get_current_client_id();

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','business-user',$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true,  'userCode' => $response['response']['userCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

	public function update_business_user($params=[], $user_code='') {	
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put', 'business-user/'.$user_code, $params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

	public function get_business_user_details($user_code = '') {
		$request_uri = 'business-user/details/'.$user_code;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'userDetails' => $response['response']['userDetails'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	public function get_business_users($search_params=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','business-user',$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'users' => $response['response']['users'], 
				'total_pages' => $response['response']['total_pages'],
				'total_records' => $response['response']['total_records'],
				'record_count' =>  $response['response']['this_page']
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}	
	}
}