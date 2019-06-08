<?php

namespace ClothingRm\Finance\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class PettyCash {
	
	public function create_pc_voucher($req_params=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','fin/pc-voucher',$req_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function update_pc_voucher($form_data=[], $voc_no='') {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put','fin/pc-voucher/'.$voc_no,$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}	

	public function get_pc_voucher_details($voc_no = '', $location_code='') {
		$req_params['locationCode'] = $location_code;
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','fin/pc-voucher/details/'.$voc_no,$req_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function get_pc_vouchers($req_params=[], $cn_no='') {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','fin/pc-voucher',$req_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'response' => $response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror' => $response['reason']);
		}		
	}

	public function get_cash_book($location_code='', $req_params=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get', 'reports/petty-cashbook/'.$location_code, $req_params);
		// dump($response);
		// exit;
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'response' => $response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror' => $response['reason']);
		}		
	}	

	public function delete_pc_voucher($voc_no='', $location_code='') {
		$end_point = 'fin/pc-voucher/'.$voc_no;
		$params = ['locationCode' => $location_code];
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('delete',$end_point,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_sales_cash_postings($req_params = []) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get', 'fin/sales-cash-post-list', $req_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'response' => $response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror' => $response['reason']);
		}
	}

	public function post_sc_to_cb($req_params = []) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post', 'fin/post-sc-to-cb', $req_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'response' => $response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror' => $response['reason']);
		}
	}	
}