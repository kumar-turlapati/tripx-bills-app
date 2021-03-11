<?php

namespace Whatsapp\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;
use Curl\Curl;

class Whatsapp {

	# push shipping update
	public function push_shipping_update($form_data = []) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','whatsapp/push-shipping-update',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'sentMessages' => $response['response']['sent']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}
}