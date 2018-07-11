<?php

namespace ClothingRm\Purchases\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Purchases 
{

	public function createPurchase($params = array()) 
	{

		$valid_result = $this->_validateFormData($params);
		if($valid_result['status'] === false) {
			return $valid_result;
		}

		$params['clientID'] = Utilities::get_current_client_id();

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','purchases',$params,true);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true,  'purchaseCode' => $response['response']['purchaseCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

	public function updatePurchase($params = array(), $purchase_code='') 
	{
		$valid_result = $this->_validateFormData($params);
		if($valid_result['status'] === false) {
			return $valid_result;
		}

		$params['clientID'] = Utilities::get_current_client_id();
		$params['purchaseCode'] = $purchase_code;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put', 'purchases', $params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

	public function removePurchase($supplier_code='', $params = array()) {

		$params['clientID'] = Utilities::get_current_client_id();

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('delete', 'suppliers/'.$supplier_code, $params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	private function _validateFormData($params = array()) {

		$api_params = $this->_getApiParams();
		$errors = array();

		// check for mandatory params
		$mand_param_errors = Utilities::checkMandatoryParams(array_keys($params), $api_params['mandatory']);
		if(is_array($mand_param_errors) && count($mand_param_errors)>0) {
			return array('status' => false, 'errors' => $this->_mapErrorMessages($mand_param_errors) );
		}

		// check for data in posted forms
		if( isset($params['status']) && ((int)$params['status'] !==0 && (int)$params['status'] !==1) ) {
			$errors['status'] = $this->_errorDescriptions('status');
		}

		if( isset($params['supplierID']) && $params['supplierID'] == '') {
			$errors['supplierID'] = $this->_errorDescriptions('supplierID');
		}

		// if( isset($params['billNo']) && $params['billNo'] == '') {
		// 	$errors['billNo'] = $this->_errorDescriptions('billNo');
		// }

		if( isset($params['creditDays']) && $params['creditDays'] != '') {
			if(!is_numeric($params['creditDays'])) 
			{
				$errors['creditDays'] = $this->_errorDescriptions('creditDays');
			}
		}

		if( isset($params['billDiscount']) && $params['billDiscount'] != '') {
			if(!is_numeric($params['billDiscount'])) 
			{
				$errors['billDiscount'] = $this->_errorDescriptions('billDiscount');
			}
		}

		if( isset($params['adjAmount']) && $params['adjAmount'] !== '') {
			if(!is_numeric($params['adjAmount'])) 
			{
				$errors['itemDetails'] = 'Invalid adjustment amount';
			}
		}

		# validate item details.
		$item_detail_errors = array();
		$item_exists = false;

		$item_names_a = $params['itemDetails']['itemName'];
		$rcvd_qtys_a = $params['itemDetails']['inwardQty'];
		$free_qtys_a = $params['itemDetails']['freeQty'];
		$batch_nos_a = $params['itemDetails']['batchNo'];
		$exp_dates_a = $params['itemDetails']['expDate'];
		$mrps_a = $params['itemDetails']['mrp'];
		$item_rates_a = $params['itemDetails']['itemRate'];
		
		foreach($item_names_a as $key=>$item_name) {

			$error_index = $key+1;

			if( trim($item_name) !== '' ) {

				$item_exists = true;

				$rcvd_qty = trim($rcvd_qtys_a[$key]);
				$free_qty = trim($free_qtys_a[$key]);
				$batch_no = trim($batch_nos_a[$key]);
				$exp_date = trim($exp_dates_a[$key]);
				$mrp = trim($mrps_a[$key]);
				$item_rate = trim($item_rates_a[$key]);

				if(is_null($free_qty) || $free_qty==='') {
					$free_qty = 0;
				}

				if( !is_numeric($rcvd_qty) || !is_numeric($free_qty) ) {
					$item_detail_errors[] = 'Item - '.$error_index.': Free Qty. and Received Qty. must be of numeric values';
				} elseif( (int)$free_qty > (int)$rcvd_qty ) {
					$item_detail_errors[] = 'Item - '.$error_index.': Free Qty. must be less than or equal to Received Qty.';
				} else {
					$params['itemDetails']['inwardQty'][$key] = $rcvd_qty;
					$params['itemDetails']['freeQty'][$key] = $free_qty;
				}

				if($batch_no === '') {
					$item_detail_errors[] = 'Item - '.$error_index.': Batch No. is required.';
				} elseif(strlen($batch_no)>12) {
					$item_detail_errors[] = 'Item - '.$error_index.': Batch No. should be less than 12 characters.';
				} else {
					$params['itemDetails']['batchNo'][$key] = $batch_no;
				}

				if( !is_numeric(str_replace('/', '', $exp_date)) ) {
					$item_detail_errors[] = 'Item - '.$error_index.': Invalid expiry date format.';
				} else {
					$exp_date_a = explode('/', $exp_date);
					if(count($exp_date_a) !== 2) {
						$item_detail_errors[] = 'Item - '.$error_index.': Expiry date with insufficient string.';
					} elseif( (int)($exp_date_a[1]+2000) < (int)date("Y") ) {
						$item_detail_errors[] = 'Item - '.$error_index.': Invalid expiry year. Should be greater than or equal to current year.';
					} elseif( (int)$exp_date_a[0]<1 || (int)$exp_date_a[0] > 12 ) {
						$item_detail_errors[] = 'Item - '.$error_index.': Invalid expiry month. Should be 1 to 12.';
					} else {
						$params['itemDetails']['expDate'][$key] = $exp_date;
					}
				}

				if( !is_numeric($mrp) || !is_numeric($item_rate) ) {
					$item_detail_errors[] = 'Item - '.$error_index.': MRP and item rate must be of numeric values.';
				} elseif( (int)$mrp < (int)$item_rate ) {
					$item_detail_errors[] = 'Item - '.$error_index.': MRP must be greater than or equal to item rate.';
				} else {
					$params['itemDetails']['mrp'][$key] = $mrp;
					$params['itemDetails']['itemRate'][$key] = $item_rate;
				}
			}
		}

		if($item_exists === false) {
			$item_detail_errors[] = 'At least one item information is mandatory in PO.';
		}

		if(count($item_detail_errors)>0) {
			$errors['itemDetails'] = '<b>You have errors in form</b><br />'.implode('<br />', $item_detail_errors);
		}

		if(count($errors)>0) {
			return array('status' => false, 'errors' => $errors);
		} else {
			return array('status' => true, 'errors' => $errors);
		}
	}

	private function _getApiParams() {
    $api_params = array(
    	'mandatory' => array(
      	'itemDetails','supplierID'
      ),
      'optional' => array(
      	'purchaseDate', 'poNo', 'paymentMethod', 'creditDays',
        'billDiscount'
      )
    );

		return $api_params;
	}

	private function _mapErrorMessages($form_fields=array()) {

		$errors = array();
		foreach($form_fields as $key=>$field_name) {
			$errors[$field_name] = $this->_errorDescriptions($field_name);
		}

		return $errors;
	}

	private function _errorDescriptions($field_name = '') {

		$descriptions = array(
				'supplierID' => 'Invalid Supplier name',
				'status' => 'Status is required / Invalid Status',
				'billNo' => 'Bill No is mandatory',
				'creditDays' => 'Credit days should contain digits only',
				'discountPercent' => 'Discount percent should contain digits only',
				'itemDetails' => 'At least one item is required to create PO',
		);

		if($field_name != '') {
			return $descriptions[$field_name];
		} else {
			return $descriptions;
		}
	}

	public function get_purchase_details($purchase_code='', $by_po_no=false) {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$params['clientID'] = $client_id;
		if($by_po_no) {
			$params['poNo'] = $purchase_code;
		} else {
			$params['purchaseCode'] = $purchase_code;
		}

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get', 'purchases', $params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'purchaseDetails' => $response['response']['purchaseDetails'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	public function get_suppliers($page_no=1, $per_page=50, $search_params=array()) {

		$params = array();
		$params['pageNo']  = $page_no;
		$params['perPage'] = $per_page;
		if(count($search_params)>0) {
			if(isset($search_params['suppName'])) {
				$supp_name = Utilities::clean_string($search_params['suppName']);
				$params['suppName'] = $supp_name;
			}
			if(isset($search_params['category'])) {
				$category = Utilities::clean_string($search_params['category']);
				$params['category'] = $category;
			}			
		}

		// dump($search_params);
		// exit;

		// fetch client id
		$client_id = Utilities::get_current_client_id();

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get', 'suppliers/'.$client_id, $params);

		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'suppliers' => $response['response']['results'], 
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

	public function get_purchases($page_no=1, $per_page=50, $search_params=array()) {

		$params = array();
		$params['pageNo']  = $page_no;
		$params['perPage'] = $per_page;
		if(count($search_params)>0) {
			if(isset($search_params['fromDate'])) {
				$fromDate = Utilities::clean_string($search_params['fromDate']);
				$params['fromDate'] = $fromDate;
			}
			if(isset($search_params['toDate'])) {
				$toDate = Utilities::clean_string($search_params['toDate']);
				$params['toDate'] = $toDate;
			}			
			if(isset($search_params['supplierID'])) {
				$supplierID = Utilities::clean_string($search_params['supplierID']);
				$params['supplierID'] = $supplierID;
			}			
		}

		// dump($search_params);
		// exit;

		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$params['clientID'] = $client_id;

		// dump($params);

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get', 'purchases/register', $params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'purchases' => $response['response']['purchases'], 
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

}