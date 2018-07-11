<?php

namespace ClothingRm\Taxes\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Taxes {

	public function add_tax($form_data=array()) {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','taxes/'.$client_id,$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'taxCode' => $response['response']['taxCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}
	public function update_tax($form_data=array(), $tax_code='') {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put','taxes/'.$tax_code.'/'.$client_id,$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function list_taxes() {
		$api_caller = new ApiCaller();
		$client_id = Utilities::get_current_client_id();
		$response = $api_caller->sendRequest('get','taxes-list/'.$client_id,array());
		if($response['status']==='success') {
			return ['status'=>true,'taxes'=>$response['response']];
		} else {
			return ['status'=>true,'taxes'=>[]];
		}
	}

	public function get_tax_details($tax_code='') {
		$api_caller = new ApiCaller();
		$client_id = Utilities::get_current_client_id();
		$response = $api_caller->sendRequest('get','taxes/'.$tax_code.'/'.$client_id,array());
		if($response['status']==='success') {
			return ['status'=>true,'tax_details'=>$response['response']];
		} else {
			return ['status'=>false,'apierror'=>$response['reason']];
		}
	}	

}