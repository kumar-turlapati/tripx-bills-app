<?php

namespace ClothingRm\Inward\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Inward
{

	// create inward entry in the system
	public function createInward($params=array()) {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inward-entry/'.$client_id;

		// echo json_encode($params);
		// exit;

		# call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'inwardCode' => $response['response']['purchaseCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

	// update inward entry in the system
	public function updateInward($params=array(), $inward_code='') {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inward-entry/'.$inward_code.'/'.$client_id;

		# call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function change_inward_status($params = [], $po_code = '') {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inward-entry/update-status/'.$po_code;

		# call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_purchase_details($purchase_code='', $by_po_no=false) {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$params['clientID'] = $client_id;
		if($by_po_no) {
			$params['poNo'] = $purchase_code;
		} else {
			$params['purchaseCode'] = $purchase_code;
		}

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get', 'purchases', $params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'purchaseDetails' => $response['response']['purchaseDetails'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	public function get_purchases($page_no=1, $per_page=100, $search_params=[]) {

		$params = array();
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
		$params['clientID'] = $client_id;

		// dump($params);

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get', 'purchases/register', $params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'purchases' => $response['response']['purchases'], 
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

	public function search_purchase_bills($search_params = []) {
		$request_uri = 'inward-entry/search';

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$search_params);
		$status = $response['status'];
		if($status === 'success') {
			return array('status'=>true, 'bills' => $response['response']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function delete_po($form_data=[]) {
		$request_uri = 'purchases/delete';
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$form_data);
		$status = $response['status'];
		if($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}
}