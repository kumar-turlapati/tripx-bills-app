<?php

namespace Settings\Model;

use Atawa\ApiCaller;
use Curl\Curl;

class Finy {
	
	protected $api_caller;
	
	public function __construct() {
		$this->api_caller = new ApiCaller;
	}

	// add finy
	public function create_finy($form_data=[]) {
		$response = $this->api_caller->sendRequest('post','finy',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true, 'finyCode'=>$response['response']['finyCode']);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}

	// update finy
	public function update_finy($form_data = [], $finy_code = '') {
		$response = $this->api_caller->sendRequest('put', 'finy/'.$finy_code, $form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	// get all finys
	public function get_finys() {
		$response = $this->api_caller->sendRequest('get', 'finy', []);
		$status = $response['status'];
		if($status === 'success') {
			return array('status'=>true,'finys' => $response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	// get finy details
	public function get_finy_details($finy_code = '') {
		$response = $this->api_caller->sendRequest('get','finy/details/'.$finy_code,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'finyDetails'=>$response['response']['finyDetails']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	// set active financial year.
	public function set_active_fin_year($form_data = []) {
		$response = $this->api_caller->sendRequest('post','finy/set-active',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}

	// switch financial year.
	public function switch_finy($form_data = []) {
		$response = $this->api_caller->sendRequest('post','finy/switch',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}

}