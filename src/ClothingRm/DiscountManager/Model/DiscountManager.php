<?php

namespace ClothingRm\DiscountManager\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class DiscountManager
{
	/** discount manager **/
	public function discount_manager($params=[]) {
		$request_uri = 'discount-rules/register';
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'response' => $response['response']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	/** delete discount entry **/
	public function delete_discount_entry($params=[]) {
		$request_uri = 'discount-rules';
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('delete',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}
}