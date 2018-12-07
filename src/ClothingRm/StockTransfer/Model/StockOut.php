<?php

namespace ClothingRm\StockTransfer\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class StockOut 
{

	public function create_stock_out_entry($params=[]) {
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','stock-out',$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'billNo' => $response['response']['billNo']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_stock_out_entry_details($transfer_code = '') {
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','stock-out/details/'.$transfer_code,[]);
		$status = $response['status'];
		if($status === 'success') {
			return array('status'=>true,'stoDetails' => $response['response']['stoDetails']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_stockout_transactions_list($search_params = []) {
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','stock-out/register',$search_params);
		// dump($response);
		// exit;
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}
}