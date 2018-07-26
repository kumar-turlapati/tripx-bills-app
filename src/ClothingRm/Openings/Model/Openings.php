<?php

namespace ClothingRm\Openings\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Openings 
{

	public function opbal_list($page_no=1,$per_page=100,$search_params=array()) {

		$params['pageNo']  = $page_no;
		$params['perPage'] = $per_page;

		if(count($search_params)>0) {
			if(isset($search_params['batchNo'])) {
				$batchNo = Utilities::clean_string($search_params['batchNo']);
				$params['batchNo'] = $batchNo;
			}
			if(isset($search_params['itemName'])) {
				$itemName = Utilities::clean_string($search_params['itemName']);
				$params['itemName'] = $itemName;
			}			
			if(isset($search_params['category'])) {
				$category = Utilities::clean_string($search_params['category']);
				$params['category'] = $category;
			}
			if(isset($search_params['locationCode'])) {
				$params['locationCode'] = $search_params['locationCode'];
			}
		}

		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$params['clientID'] = $client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','opbal/list',$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'openings' => $response['response']['results'], 
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

	public function updateOpBal($params=array(),$opbal_code='')	{
		$valid_result = $this->_validateFormData($params,$opbal_code);
		if($valid_result['status'] === false) {
			return $valid_result;
		}

		$client_id = Utilities::get_current_client_id();
		$request_uri = 'opbal/'.$client_id.'/'.$opbal_code;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

	public function createOpBal($params=array()) {
		$valid_result = $this->_validateFormData($params);
		if($valid_result['status'] === false) {
			return $valid_result;
		}

		$client_id = Utilities::get_current_client_id();
		$request_uri = 'opbal/create/'.$client_id;

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

	public function get_opbal_details($op_code='') {

		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'opbal/'.$client_id.'/'.$op_code;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'opDetails' => $response['response']['opDetails'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	/** upload inventory **/
	public function upload_inventory($products=[], $upload_type='', $location_code='') {

		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inventory/upload-from-xl/'.$client_id;

		$params = array(
			'products' => $products,
			'uploadType' => $upload_type,
			'locationCode' => $location_code,
		);

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$params);
		return $response;
	}

	/***************************************************************************************************
	 * Private functions should start from here....
	****************************************************************************************************/
	
	# validate form data.
	private function _validateFormData($params = array(),$opbal_code='') {

		$api_params = $this->_getApiParams();
		$errors = array();

		if($opbal_code === '') {

			// check for mandatory params
			$mand_param_errors = Utilities::checkMandatoryParams(array_keys($params), $api_params['mandatory']);
			if(is_array($mand_param_errors) && count($mand_param_errors)>0) {
				return array('status' => false, 'errors' => $this->_mapErrorMessages($mand_param_errors) );
			}

			// check for data in posted forms
			if($params['itemName']=='') {
					$errors['itemName'] = $this->_errorDescriptions('itemName');
			}
			if($params['opQty'] === '') {
					$errors['opQty'] = $this->_errorDescriptions('opQty');
			}
			if($params['batchNo'] === '') {
					$errors['batchNo'] = $this->_errorDescriptions('batchNo');
			}
		}

		if($params['opRate'] === '') {
				$errors['opRate'] = $this->_errorDescriptions('opRate');
		}

		if($params['expMonth'] === '') {
				$errors['expMonth'] = $this->_errorDescriptions('expMonth');
		}

		if($params['expYear'] === '') {
				$errors['expYear'] = $this->_errorDescriptions('expYear');
		}

		if(count($errors)>0) {
			return array('status'=>false, 'errors' => $errors);
		} else {
			return array('status'=>true, 'errors' => $errors);
		}

	}

	# get api parameters.
	private function _getApiParams() {
		$api_params = array(
			'mandatory' => array(
				'itemName','opQty','opRate','batchNo','expMonth','expYear','taxPercent',
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
				'opQty' => 'Opening qty. is mandatory',
				'opRate' => 'Opening rate is mandatory',
				'batchNo' => 'Batch number is mandatory',
				'expMonth' => 'Expiry month is mandatory',
				'expYear' => 'Expiry year is mandatory',
				'taxPercent' => 'Tax percent is mandatory',
		);

		if($field_name != '') {
			return $descriptions[$field_name];
		} else {
			return $descriptions;
		}
	}	

}