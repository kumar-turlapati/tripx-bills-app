<?php

namespace ClothingRm\Barcode\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Barcode {

	private $api_caller;

	public function __construct() {
		$this->api_caller = new ApiCaller;
	}

	# add barcode
	public function generate_barcode($purchase_code='', $new_barcodes=[]) {
		$request_uri = 'barcode/'.$purchase_code;
		$response = $this->api_caller->sendRequest('post',$request_uri,$new_barcodes);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true, 'barcodes'=>$response['response']['barCodes']);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}

	# generate barcode for opening balances.
	public function generate_barcode_opening($new_barcodes=[]) {
		$request_uri = 'barcode-opening';
		$response = $this->api_caller->sendRequest('post',$request_uri,$new_barcodes);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true, 'barcodes'=>$response['response']['barCodes']);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}	

	# get all barcodes
	public function get_barcodes($filter_params=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','barcode',$filter_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'response' => $response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror' => $response['reason']);
		}		
	}

}