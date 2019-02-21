<?php

namespace HsnSacCodes\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class HsnSacCodes {
	
	private $api_caller;

	public function __construct() {
		$this->api_caller = new ApiCaller;
	}

	public function add_hsnsac_code($form_data = []) {
		$response = $this->api_caller->sendRequest('post','hsnsac-codes',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'hsnSacUniqueCode' => $response['response']['hsnSacUniqueCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function list_hsnsac_codes($search_params=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','hsnsac-codes',$search_params);
		if($response['status']==='success') {
			return ['status'=>true,'response'=>$response['response']];
		} else {
			return ['status'=>false,'apierror'=> $response['reason']];
		}
	}

	public function update_hsnsac_code($form_data=[], $hsnsac_unique_code='') {
		$response = $this->api_caller->sendRequest('put', 'hsnsac-codes/'.$hsnsac_unique_code, $form_data);
		$status = $response['status'];
		if($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_hsnsac_details($hsnsac_unique_code = '') {
		$response = $this->api_caller->sendRequest('get','hsnsac-codes/details/'.$hsnsac_unique_code,[]);
		if($response['status']==='success') {
			return ['status'=>true,'hsnsac_details'=>$response['response']];
		} else {
			return ['status'=>false,'apierror'=>$response['reason']];
		}
	}	

}