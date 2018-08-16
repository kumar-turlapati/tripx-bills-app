<?php

namespace ClothingRm\Openings\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Openings 
{

	public function opbal_list($search_params=[]) {
		// fetch client id
		$search_params['clientID'] = Utilities::get_current_client_id();
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','opbal/list',$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'openings' => $response['response']['results'], 
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

	public function createOpBal($params=[]) {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'opbal/create/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}		

	public function updateOpBal($params=[], $opbal_code='')	{
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'opbal/'.$client_id.'/'.$opbal_code;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

	public function get_opbal_details($op_code='') {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'opbal/'.$client_id.'/'.$op_code;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'opDetails' => $response['response']['opDetails'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	public function upload_inventory($products=[], $upload_type='', $location_code='') {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inventory/upload-from-xl/'.$client_id;
		$params = array(
			'products' => $products,
			'uploadType' => $upload_type,
			'locationCode' => $location_code,
		);
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$params);
		return $response;
	}
}