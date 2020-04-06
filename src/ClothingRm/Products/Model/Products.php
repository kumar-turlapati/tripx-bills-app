<?php

namespace ClothingRm\Products\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Products 
{

	public function get_products($page_no=1, $per_page=100, $search_params=[]) {

		$params = array();
		$params['pageNo']  = $page_no;
		$params['perPage'] = $per_page;
		if(count($search_params)>0) {
			$params = array_merge($params, $search_params);
		}

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get', 'products', $params);

		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'products' => $response['response']['results'], 
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

	public function get_product_categories($location_code='') {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','categories?lc='.$location_code);
		$status = $response['status'];
		if($status === 'success') {
			return $response['response'];
		} elseif($status === 'failed') {
			return array();
		}
	}

	public function get_product_details($product_code='', $location_code='') {
		$api_caller = new ApiCaller();

		$end_point = 'products/details/'.$product_code;

		$response = $api_caller->sendRequest('get',$end_point,['locationCode' => $location_code]);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,
				'productDetails' => $response['response']
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}		
	}

	public function get_product_details_with_name($product_name='', $location_code='') {
		$api_caller = new ApiCaller();
		$end_point = 'products/details-with-name';
		$response = $api_caller->sendRequest('get',$end_point,['pn' => $product_name, 'locationCode' => $location_code]);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,
				'productDetails' => $response['response']
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
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
		if($params['itemName'] === '') {
			$errors['itemName'] = $this->_errorDescriptions('itemName');
		}
		if($params['locationCode'] === '') {
			$errors['locationCode'] = $this->_errorDescriptions('locationCode');
		}
		if($params['hsnSacCode'] !== '' && (!is_numeric($params['hsnSacCode']) || strlen($params['hsnSacCode']) > 8) ) {
			$errors['hsnSacCode'] = 'Invalid HSN / SAC code.';
		}
		if($params['comboCode'] !== '') {
			if( !is_numeric($params['comboCode']) || strlen($params['comboCode']) !== 2) {
			  $errors['comboCode'] = 'Invalid Combo Code. Must be 00-99';
			}
		}
		if($params['serviceCode'] !== '' && !is_numeric($params['serviceCode'])) {
			$errors['serviceCode'] = 'Invalid service code. Only digits are accepted.';	
		}

		if(count($errors)>0) {
			return array('status' => false, 'errors' => $errors);
		} else {
			return array('status' => true, 'errors' => $errors);
		}
	}

	/**
	 * Create Product
	**/
	public function createProduct($params = []) {
		$valid_result = $this->_validateFormData($params);
		if($valid_result['status'] === false) {
			return $valid_result;
		}

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','products',$params);

		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'itemCode' => $response['response']['itemCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

	/**
	 * Update Product
	**/
	public function updateProduct($params=array(), $item_code='') {
		$valid_result = $this->_validateFormData($params);
		if($valid_result['status'] === false) {
			return $valid_result;
		}
		$end_point = 'products/'.$item_code;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put',$end_point,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'itemCode' => $response['response']['itemCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}	

	private function _getApiParams() {
		$api_params = array(
			'mandatory' => array(
				'itemName',
			),
			'optional' => array(
			),			
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
				'itemName' => 'Item name is required/Invalid Item name',
				'unitsPerPack' => 'Units per pack is required',
				'locationCode' => 'Store name is mandatory',
		);

		if($field_name != '') {
			return $descriptions[$field_name];
		} else {
			return $descriptions;
		}
	}	

}