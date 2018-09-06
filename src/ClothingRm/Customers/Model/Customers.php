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

	public function get_customers($page_no=1, $per_page=100, $search_params=[]) {
		$params = [];
		$params['pageNo']  = $page_no;
		$params['perPage'] = $per_page;
		if(count($search_params)>0) {
			if(isset($search_params['custName'])) {
				$cust_name = Utilities::clean_string($search_params['custName']);
				$params['custName'] = $cust_name;
			}
		}

		// fetch client id
		$client_id = Utilities::get_current_client_id();

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','customers/'.$client_id,$params);
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
}