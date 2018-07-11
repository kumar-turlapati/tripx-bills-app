<?php

namespace ClothingRm\PurchaseReturns\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class PurchaseReturns
{
	public function createPurchaseReturn($params=[], $purchase_code='') {
		$request_uri = 'purchase-returns/'.$purchase_code;		
		# call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'returnCode' => $response['response']['returnCode'], 'mrnNo' => $response['response']['mrnNo']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function purchase_return_register($params=[]) {
		$request_uri = 'purchase-returns/register';		
		# call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if($status === 'success') {
			return array('status'=>true,'response' => $response['response']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_purchase_return_details($pr_code='') {
		$request_uri = 'purchase-returns/details/'.$pr_code;		
		# call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,[]);
		$status = $response['status'];
		if($status === 'success') {
			return array('status'=>true,'prDetails' => $response['response']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function delete_purchase_return($pr_code = '') {
		$request_uri = 'purchase-returns/'.$pr_code;		
		# call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('delete',$request_uri,[]);
		$status = $response['status'];
		if($status === 'success') {
			return array('status'=>true,'prDetails' => $response['response']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}
}