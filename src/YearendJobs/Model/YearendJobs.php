<?php

namespace YearendJobs\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class YearendJobs {
	
	private $api_caller;

	// constructor
	public function __construct() {
		$this->api_caller = new ApiCaller;
	}

	// post inventory balances
	public function post_inventory($form_data = []) {
		$response = $this->api_caller->sendRequest('post','yep/post-inventory-cb',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,  'recordsCreated' => $response['response']['recordsCreated']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror' => $response['reason']);
		}
	}

	// post barcodes
	public function post_barcodes($form_data = []) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','yep/post-barcodes',$form_data);
		if($response['status']==='success') {
			return ['status'=>true,  'recordsCreated' => $response['response']['recordsCreated']];
		} else {
			return ['status'=>false, 'apierror'=> $response['reason']];
		}
	}

	// post barcodes
	public function post_debtors($form_data = []) {
		$response = $this->api_caller->sendRequest('post', 'yep/post-debtors-cb', $form_data);
		$status = $response['status'];
		if($status === 'success') {
			return array('status' => true,  'recordsCreated' => $response['response']['recordsCreated']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	// post creditors
	public function post_creditors($form_data = []) {
		$response = $this->api_caller->sendRequest('post','yep/post-creditors-cb',$form_data);
		if($response['status']==='success') {
			return ['status'=>true,  'recordsCreated' => $response['response']['recordsCreated']];
		} else {
			return ['status'=>false, 'apierror'=>$response['reason']];
		}
	}

}