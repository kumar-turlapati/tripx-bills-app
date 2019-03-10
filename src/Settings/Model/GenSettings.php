<?php

namespace Settings\Model;

use Atawa\ApiCaller;
use Curl\Curl;

class GenSettings {
	
	protected $api_caller;
	
	public function __construct() {
		$this->api_caller = new ApiCaller;
	}

	// move account to mainenance mode
	public function maintenance_mode($form_data = []) {
		$response = $this->api_caller->sendRequest('post','maintenance-mode',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}


}