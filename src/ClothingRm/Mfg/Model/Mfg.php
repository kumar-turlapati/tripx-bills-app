<?php

namespace ClothingRm\Mfg\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Mfg {

	private $api_caller;
	
	public function __construct() {
		$this->api_caller = new ApiCaller();
	}

	// get all mfgs
	public function get_mfgs($search_params=[]) {
		$response = $this->api_caller->sendRequest('get', 'mfg', $search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'mfgs' => $response['response']
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}	
	}

	// create mfg
	public function create_mfg($form_data=[]) {
		$response = $this->api_caller->sendRequest('post', 'mfg', $form_data);
		$status = $response['status'];
		if($status === 'success') {
			return ['status' => true, 'mfgCode' => $response['response']['mfgCode']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	// update mfg
	public function update_mfg($form_data=[], $mfg_code='') {
		$response = $this->api_caller->sendRequest('put', 'mfg/'.$mfg_code, $form_data);
		$status = $response['status'];
		if($status === 'success') {
			return ['status' => true];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	// get mfg details
	public function get_mfg_details($mfg_code = '', $location_code='') {
		$response = $this->api_caller->sendRequest('get', 'mfg/details/'.$mfg_code.'?lc='.$location_code, []);
		$status = $response['status'];
		if ($status === 'success') {
			return $response['response'];
		} elseif($status === 'failed') {
			return false;
		}
	}
}