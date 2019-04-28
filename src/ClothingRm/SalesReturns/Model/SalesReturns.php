<?php

namespace ClothingRm\SalesReturns\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class SalesReturns 
{

	// create sales return
	public function createSalesReturn($params=array(),$sales_code='',$sale_item_details=array()) {
		$request_params = array();
		$valid_result = $this->_validateFormData($params,$sale_item_details);
		if($valid_result['status'] === false) {
			return $valid_result;
		} else {
			$request_params['returnDetails'] = $valid_result['cleaned_params']['return_items'];
			$request_params['returnDate'] = $valid_result['cleaned_params']['return_date'];
		}

		$client_id = Utilities::get_current_client_id();
		$end_point = 'sales-return/'.$client_id.'/'.$sales_code;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$end_point,$request_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status'=>true,
				'returnCode' => $response['response']['returnCode'],
				'mrnNo' => $response['response']['mrnNo'],
				'cnNo' => $response['response']['cnNo'],
			);
		} elseif($status === 'failed') {
			return array(
				'status'=>false, 
				'apierror' => $response['reason']
			);
		}		
	}

	public function updateSalesReturn ($params=array(),$return_code='',$sale_item_details=array()) {
		$request_params = array();		
		$valid_result = $this->_validateFormData($params,$sale_item_details);
		if($valid_result['status'] === false) {
			return $valid_result;
		} else {
			$return_items = $valid_result['return_items'];
			$request_params['returnDetails'] = $return_items;
		}

		if(isset($params['returnDate']) && $params['returnDate'] !== '') {
			$request_params['returnDate'] = Utilities::clean_string($params['returnDate']);
		}

		$client_id = Utilities::get_current_client_id();
		$end_point = 'sales-return/'.$client_id.'/'.$return_code;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put',$end_point,$request_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}	

	private function _validateFormData($params=[], $sale_item_details=[]) {

		$errors = $filter_items = [];
		$return_items = $params['itemInfo'];

		$return_date = Utilities::clean_string($params['returnDate']);
    if(Utilities::is_valid_fin_date($return_date)) {
      $cleaned_params['returnDate'] = $return_date;
    } else {
      $errors['returnDate'] = 'Sales Return Date is out of Financial year dates.';
    }

		unset($params['itemInfo']);
		unset($params['returnDate']);
		unset($params['status']);

		// dump($params);
		// dump($sale_item_details);


		// $sold_item_names = array_column($sale_item_details,'itemName');
		// $sold_item_qtys =  array_column($sale_item_details,'itemQty');
		// $sold_item_codes = array_column($sale_item_details,'itemCode');

		// dump($sold_item_qtys);
		// dump($sold_item_codes);
		// dump($sold_item_names);
		foreach($sale_item_details as $key=>$item_details) {
			$item_index = $item_details['itemCode'].'_'.$key;
			$sold_items_a[$item_index] = $item_details['itemQty'];
			$sold_item_codes_a[$item_index] = $item_details['itemName'];
		}

		// $sold_items_a = array_combine($sold_item_codes, $sold_item_qtys);
		// $sold_item_codes_a = array_combine($sold_item_codes,$sold_item_names);

		// dump($sold_items_a);
		// dump($sold_item_codes_a);
		// dump($params);
		// exit;

		foreach($params as $return_key=>$return_qty) {
			if((int)$return_qty>0) {
				$item_a = explode("_", $return_key);
				// $return_item_code = $item_a[1];
				$return_item_index = $item_a[1].'_'.$item_a[2];
				if( isset($sold_item_codes_a[$return_item_index]) ) {
					$sold_item_qty = $sold_items_a[$return_item_index];
					if($return_qty>$sold_item_qty || $return_qty<0) {
						$errors['itemDetails'] = 'Excess return qty. not accepted';
						break;
					} else {
						$filter_item_name = $sold_item_codes_a[$return_item_index].'_'.$item_a[2];
						$filter_items[$filter_item_name] = $return_qty;
					}
				}
			}
		}

		if(count($filter_items)<=0) {
			$errors['itemDetails'] = 'No Items are available for return.';
		}

		$cleaned_params = [];
		$cleaned_params['return_items'] = $filter_items;
		$cleaned_params['return_date'] = $return_date;

		// dump($filter_items);
		// exit;

		if(count($errors)>0) {
			return array('status' =>false,'errors'=>$errors);
		} else {
			return array('status' =>true,'errors'=>$errors, 'cleaned_params' => $cleaned_params);
		}
	}

	private function _mapErrorMessages($form_fields=array()) {

		$errors = array();
		foreach($form_fields as $key=>$field_name) {
			$errors[$field_name] = $this->_errorDescriptions($field_name);
		}

		return $errors;
	}	

	# get sales returns.
	public function get_all_sales_returns($search_params=[]) {

		// fetch client id
		$client_id = Utilities::get_current_client_id();

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','sales-return-register/'.$client_id,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'sales_returns' => $response['response']['sales_returns'], 
				'total_pages' => $response['response']['total_pages'],
				'total_records' => $response['response']['total_records'],
				'record_count' =>  $response['response']['this_page']
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}	
	}

	# get sales return details.
	public function get_sales_return_details($return_code='') {

		$params = array();

		// fetch client id
		$client_id = Utilities::get_current_client_id();

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','sales-return/'.$client_id.'/'.$return_code,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'returnDetails' => $response['response']['returnDetails'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	# Sales Return Itemwise.
	public function get_itemwise_sales_returns($params=array()) {

		$client_id = Utilities::get_current_client_id();
		$end_point = 'reports/sales-return-itemwise/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
			  'status' => true,
				'returns' => $response['response']['returns'], 
				'total_pages' => $response['response']['total_pages'],
				'total_records' => $response['response']['total_records'],
				'record_count' =>  $response['response']['this_page']			  
			);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}
}