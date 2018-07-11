<?php

namespace ClothingRm\Finance\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class CreditNote 
{

	public function create_credit_note($form_data=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post', 'fin/cn', $form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'cnNo'=>$response['response']['cnNo']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function get_credit_notes($req_params=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','fin/cn',$req_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function delete_credit_note($cn_no='') {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('delete','fin/cn/'.$cn_no,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function get_credit_note_details($req_params=[], $cn_no='') {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','fin/cn/details/'.$cn_no,$req_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}
}