<?php

namespace ClothingRm\Inward\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Inward
{

	/** create inward entry in the system **/
	public function createInward($params=array()) {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inward-entry/'.$client_id;		

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

	/** update inward entry in the system **/
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

	public function updateInwardAfterGrn($params=array(), $inward_code='') {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inw-update-after-grn/'.$inward_code.'/'.$client_id;

		# call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put',$request_uri,$params);
		var_dump($response);
		exit;
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}
}