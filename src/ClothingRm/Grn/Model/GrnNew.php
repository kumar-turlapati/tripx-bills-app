<?php

namespace ClothingRm\Grn\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class GrnNew
{
	// create GRN api.
	public function createGRN($params=array()) {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'grn/v2/'.$client_id;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true,  'grnCode' => $response['response']['grnCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	// get GRN Details by GRN No or GRN Code
	public function get_grn_details($grn_code='') {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'grn/details/'.$client_id.'/'.$grn_code;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,array());
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'grnDetails' => $response['response']['grnDetails'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	// Get GRNs from the portal
	public function get_grns($page_no=1,$per_page=100,$search_params=[]) {
		$params = [];
		$params['pageNo']  = $page_no;
		$params['perPage'] = $per_page;
		if(count($search_params)>0) {
			if(isset($search_params['fromDate'])) {
				$fromDate = Utilities::clean_string($search_params['fromDate']);
				$params['fromDate'] = $fromDate;
			}
			if(isset($search_params['toDate'])) {
				$toDate = Utilities::clean_string($search_params['toDate']);
				$params['toDate'] = $toDate;
			}			
			if(isset($search_params['supplierID'])) {
				$supplierID = Utilities::clean_string($search_params['supplierID']);
				$params['supplierID'] = $supplierID;
			}	
		}

		// dump($search_params);
		// exit;

		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'grn/register/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'grns' => $response['response']['grns'], 
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

	// delete GRN
	public function deleteGRN($params = []) {
		$request_uri = 'grn/delete';
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}	
}