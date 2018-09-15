<?php

namespace ClothingRm\Customers\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Customers 
{
	public function createCustomer($params = array()) 
	{
		$client_id = Utilities::get_current_client_id();

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','customers/'.$client_id,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true,  'customerCode' => $response['response']['customerCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

	public function updateCustomer($params=array(), $customer_code='') {		
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'customers/'.$client_id.'/'.$customer_code;
		# call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put', $request_uri, $params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_customer_details($customer_code='') {

		// fetch client id
		$client_id = Utilities::get_current_client_id();

		$request_uri = 'customers/details/'.$client_id.'/'.$customer_code;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri);
		// dump($response);
		// exit;
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'customerDetails' => $response['response']['customerDetails'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	public function get_customers($search_params=[]) {
		// fetch client id
		$client_id = Utilities::get_current_client_id();

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','customers/'.$client_id,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'customers' => $response['response']['customers'], 
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

	public function upload_debtors($debtors=[], $upload_type='') {
		$request_uri = 'upload-debtors';
		# call api.
		$api_caller = new ApiCaller();
		$params = array(
			'debtors' => $debtors,
			'uploadType' => $upload_type
		);
		$response = $api_caller->sendRequest('post', $request_uri, $params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}
}