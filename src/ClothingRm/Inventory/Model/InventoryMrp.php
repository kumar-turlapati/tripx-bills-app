<?php

namespace ClothingRm\Inventory\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class InventoryMrp
{
	private $api_caller;

	public function __construct() {
		$this->api_caller = new ApiCaller();
	}

	public function selling_price_bulk_update($form_data=[]) {
		$end_point = '/inventory/change-mrp-bulk';
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$end_point,$form_data);
		// dump($response);
		// exit;
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,
				'response' => $response['response']
			);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}
}