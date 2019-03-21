<?php

namespace StockAdjReasons\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class StockAdjReasons {
	
	private $api_caller;

	public function __construct() {
		$this->api_caller = new ApiCaller;
	}

	public function add_adj_reason($form_data = []) {
		$response = $this->api_caller->sendRequest('post','stock-adj-reason',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'adjReasonCode' => $response['response']['adjReasonCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function list_adj_reasons($search_params=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','stock-adj-reasons',$search_params);
		if($response['status']==='success') {
			return ['status'=>true,'response'=>$response['response']];
		} else {
			return ['status'=>false,'apierror'=> $response['reason']];
		}
	}

	public function update_adj_reason($form_data = [], $reason_code = '') {
		$response = $this->api_caller->sendRequest('put', 'stock-adj-reason/'.$reason_code, $form_data);
		$status = $response['status'];
		if($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_adj_reason_details($reason_code = '') {
		$response = $this->api_caller->sendRequest('get','stock-adj-reason/details/'.$reason_code,[]);
		if($response['status']==='success') {
			return ['status'=>true,'reason_details'=>$response['response']];
		} else {
			return ['status'=>false,'apierror'=>$response['reason']];
		}
	}	

}