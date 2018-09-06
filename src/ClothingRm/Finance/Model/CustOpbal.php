<?php

namespace ClothingRm\Finance\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

final class CustOpbal
{
	private $api_caller;
	public function __construct() {
		$this->api_caller = new ApiCaller();
	}

	public function create_customer_opbal($params = []) {
		// call api.
		$response = $this->api_caller->sendRequest('post','cust-opening',$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'opBalCode' => $response['response']['opBalCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function update_customer_opbal($params=[], $opbal_code='') {
		$end_point = 'cust-opening/'.$opbal_code;
		$response = $this->api_caller->sendRequest('put',$end_point,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}	
	}

	public function get_cust_opbal_details($opbal_code='') {
		$response = $this->api_caller->sendRequest('get', 'cust-opening/details/'.$opbal_code, []);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'opBalDetails' => $response['response']['opBalDetails']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_cust_opbal_list($params=[]) {
		$response = $this->api_caller->sendRequest('get','cust-opening',$params);
		$status = $response['status'];
		if($status === 'success') {
			return array('status'=>true, 'response' => $response['response']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function delete_customer_opbal($opBalCode = '') {
		$end_point = 'cust-opening/'.$opBalCode;
		// call api.
		$response = $this->api_caller->sendRequest('delete',$end_point,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}
}