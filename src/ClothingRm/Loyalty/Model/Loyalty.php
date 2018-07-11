<?php

namespace ClothingRm\Loyalty\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Loyalty {

	public function add_loyalty_member($form_data=array()) {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','loyalty/member',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'taxCode' => $response['response']['taxCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function update_loyalty_member($form_data=[], $member_code='') {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put','loyalty/member/'.$member_code,$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_loyalty_members($req_params = []) {
		$api_caller = new ApiCaller();
		$client_id = Utilities::get_current_client_id();
		$response = $api_caller->sendRequest('get', 'loyalty/member', $req_params);
		if($response['status']==='success') {
			return ['status'=>true,'data'=>$response['response']];
		} else {
			return ['status'=>false,'apierror' => $response['reason']];
		}
	}
	
	public function get_loyalty_member_details($member_code = '') {
		$api_caller = new ApiCaller();
		$client_id = Utilities::get_current_client_id();
		$response = $api_caller->sendRequest('get', 'loyalty/member/details/'.$member_code, []);
		if($response['status']==='success') {
			return ['status'=>true,'member_details'=>$response['response']['memberDetails']];
		} else {
			return ['status'=>false,'apierror'=>$response['reason']];
		}
	}

	public function get_loyalty_member_ledger($member_code = '') {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get', 'loyalty/member/ledger/'.$member_code, []);
		if($response['status']==='success') {
			return ['status'=>true,'ledger'=>$response['response']];
		} else {
			return ['status'=>false,'apierror'=>$response['reason']];
		}		
	}

}