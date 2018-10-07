<?php

namespace Settings\Model;

use Atawa\ApiCaller;
use Curl\Curl;

class FinySlno {
	
	protected $api_caller;
	
	public function __construct() {
		$this->api_caller = new ApiCaller;
	}

	// add finy
	public function create_finy_slnos($form_data=[]) {
		$response = $this->api_caller->sendRequest('post','finy-slnos',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true, 'finySlnoCode'=>$response['response']['finySlnoCode']);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}

	// update finy
	public function update_finy_slnos($form_data = [], $finy_slno_code = '') {
		$response = $this->api_caller->sendRequest('put', 'finy-slnos/'.$finy_slno_code, $form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	// get all finys
	public function get_finy_slnos($params=[]) {
		$response = $this->api_caller->sendRequest('get', 'finy-slnos', $params);
		$status = $response['status'];
		if($status === 'success') {
			return array('status'=>true,'slnos' => $response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	// get finy details
	public function get_finy_slno_details($finy_code = '') {
		$response = $this->api_caller->sendRequest('get','finy-slnos/details/'.$finy_code,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'finySlnoDetails'=>$response['response']['finySlnoDetails']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}
}