<?php

namespace ClothingRm\SalesCombos\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class SalesCombos
{
	/** create promo offer in the system **/
	public function create_sales_combo($params=[]) {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'sales-combo/create';

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'comboCode' => $response['response']['comboCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	/** get combo details **/
	public function get_combo_details($combo_code='') {
		$request_uri = 'sales-combo/details/'.$combo_code;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,[]);
		$status = $response['status'];
		if($status === 'success') {
			return array('status' => true,'comboDetails' => $response['response']['comboDetails']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}	

	/** update promo offer in the system **/
	public function update_sales_combo($params=[], $combo_code='') {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'sales-combo/update/'.$combo_code;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	/** list sales combos in the system **/
	public function get_all_sales_combos($params=[]) {
		$client_id = Utilities::get_current_client_id();
		$request_uri = '/sales-combo/list';
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'response' => $response['response']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}
}