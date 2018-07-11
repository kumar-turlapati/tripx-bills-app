<?php

namespace ClothingRm\Finance\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

final class SuppOpbal
{
	public function create_supplier_opbal($params=array()) {
		$client_id = Utilities::get_current_client_id();
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','fin/supp-opbal/'.$client_id,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'opBalCode' => $response['response']['opCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function update_supplier_opbal($opbal_code='',$params=array()) {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'fin/supp-opbal/'.$client_id.'/'.$opbal_code;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put',$end_point,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}	
	}

	public function get_supp_opbal_details($opbal_code='') {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'fin/supp-opbal/details/'.$client_id.'/'.$opbal_code;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,array());
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'opBalDetails' => $response['response']['opBalDetails']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_supp_opbal_list($params=array()) {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'fin/supp-opbal/'.$client_id;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'balances' => $response['response']['records']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_supp_billwise_outstanding($params=array()) {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'reports/suppliers-outstanding/'.$client_id;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'balances' => $response['response']['records']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_supp_billwise_os_ason($params=array()) {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'reports/suppliers-outstanding-ason/'.$client_id;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'balances' => $response['response']['records']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_supplier_ledger($supplier_code='') {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'reports/suppliers-ledger/'.$client_id;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,array('supplierCode'=>$supplier_code));
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response['response']['records']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	/** payables monthwise **/
	public function payables_monthwise($params=[]) {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'reports/payables/month-wise/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response['response']['records']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

}