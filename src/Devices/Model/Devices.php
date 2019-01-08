<?php

namespace Devices\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Devices {

	// add device
	public function add_device($device_details=[]) {
		$request_uri = 'devices';
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$device_details);
		// dump($response);
		// exit;
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true, 'deviceCode'=>$response['response']['deviceCode']);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}

	// update device
	public function update_device($device_details = '', $device_code = '') {
		$request_uri = 'devices/'.$device_code;
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put',$request_uri,$device_details);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	// get all devices
	public function get_devices($search_params=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','devices/list',$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	// get device details
	public function get_device_details($device_code = '') {
		$request_uri = 'devices/details/'.$device_code;
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'deviceDetails'=>$response['response']['deviceDetails']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	// delete device.
	public function delete_device($device_code = '') {
		$request_uri = 'devices/'.$device_code;
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('delete',$request_uri,[]);
		$status = $response['status'];
		if($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}		
	}

}