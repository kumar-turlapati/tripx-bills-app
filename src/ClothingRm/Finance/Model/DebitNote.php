<?php

namespace ClothingRm\Finance\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class DebitNote 
{
	private $api_caller;

	public function __construct() {
		$this->api_caller = new ApiCaller;
	}

	public function create_debit_note($form_data=[]) {
		$response = $this->api_caller->sendRequest('post', 'fin/dn', $form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'cnNo'=>$response['response']['cnNo']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function get_debit_notes($req_params=[]) {
		$response = $this->api_caller->sendRequest('get','fin/dn',$req_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function delete_debit_note($dn_code = '') {
		$response = $this->api_caller->sendRequest('delete','fin/dn/'.$dn_code,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function get_debit_note_details($dn_code='') {
		$response = $this->api_caller->sendRequest('get', 'fin/dn/details/'.$dn_code, []);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}
}