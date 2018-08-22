<?php

namespace ClothingRm\Suppliers\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Supplier 
{

	public function createSupplier($params = array()) 
	{
		$valid_result = $this->_validateFormData($params);
		if($valid_result['status'] === false) {
			return $valid_result;
		}

		$params['clientID'] = Utilities::get_current_client_id();

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post', 'suppliers', $params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true,  'supplierCode' => $response['response']['supplierCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

	public function updateSupplier($params = array(), $supplier_code='') 
	{
		$valid_result = $this->_validateFormData($params);
		if($valid_result['status'] === false) {
			return $valid_result;
		}

		$params['clientID'] = Utilities::get_current_client_id();

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put', 'suppliers/'.$supplier_code, $params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

	public function removeSupplier($supplier_code='', $params = array()) {

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
		$errors = [];

		// check for mandatory params
		$mand_param_errors = Utilities::checkMandatoryParams(array_keys($params), $api_params['mandatory']);
		if(is_array($mand_param_errors) && count($mand_param_errors)>0) {
			return array('status' => false, 'errors' => $this->_mapErrorMessages($mand_param_errors) );
		}

		// check for data in posted forms
		if($params['supplierName'] === '' || !Utilities::validateName($params['supplierName'])) {
			$errors['supplierName'] = $this->_errorDescriptions('supplierName');
		}

		if( isset($params['mobileNo']) && $params['mobileNo'] != '') {
			if(!Utilities::validateMobileNo($params['mobileNo'])) {
				$errors['mobileNo'] = $this->_errorDescriptions('mobileNo');
			}
		}

		if( isset($params['status']) && $params['status'] != '') {
			if(	(int)$params['status'] !== 0 && (int)$params['status'] !== 1 ) 
			{
				$errors['status'] = $this->_errorDescriptions('status');
			}
		}

		if( isset($params['supplierType']) && $params['supplierType'] != '') {
			if(	$params['supplierType'] !=='phar' && $params['supplierType'] !=='gene' ) 
			{
				$errors['supplierType'] = $this->_errorDescriptions('supplierType');
			}
		}

		if( isset($params['pincode']) && $params['pincode'] != '') {
			if(!is_numeric($params['pincode'])) 
			{
				$errors['pincode'] = $this->_errorDescriptions('pincode');
			}
		}

		if( isset($params['phone1']) && $params['phone1'] != '') {
			if(!is_numeric($params['phone1'])) 
			{
				$errors['phone1'] = $this->_errorDescriptions('phone1');
			}
		}

		if( isset($params['phone2']) && $params['phone2'] != '') {
			if(!is_numeric($params['phone2'])) 
			{
				$errors['phone2'] = $this->_errorDescriptions('phone2');
			}
		}

		if( isset($params['emailID']) && $params['emailID'] != '') {
			if(!Utilities::validateEmail($params['emailID'])) 
			{
				$errors['emailID'] = $this->_errorDescriptions('emailID');
			}
		}

		if( isset($params['website']) && $params['website'] != '') {
			if(!Utilities::validateUrl($params['website'])) 
			{
				$errors['website'] = $this->_errorDescriptions('website');
			}
		}

		if( isset($params['contactPersonName']) && $params['contactPersonName'] != '') {
			if(!Utilities::validateName($params['contactPersonName'])) 
			{
				$errors['contactPersonName'] = $this->_errorDescriptions('contactPersonName');
			}
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
				'supplierName'
			),
			'optional' => array(
				'supplierCategory','dlNo','taxNo','status', 
				'address1', 'address2','pincode','phone1',
				'phone2','mobileNo','emailID','website','contactPersonName'
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
				'supplierName' => 'Supplier name is required/Invalid Supplier name',
				'supplierType' => 'Only Pharmacy and General supplier categories are allowed',
				'dlNo' => 'Status is required.',
				'taxNo' => 'Sale date should contain digits and dash (-)',
				'status' => 'Status must be Active or Inactive',
				'address1' => 'Payment method is required and must be Cash, Credit or Credit card',
				'address2' => 'Credit days should contain digits only',
				'phone1' => 'Phone1 should contain only digits',
				'phone2' => 'Phone2 should contain only digits',
				'emailID' => 'Invalid Email ID',
				'mobileNo' => 'Mobile number should contain 10 digits',
				'website' => 'Invalid Website',
				'contactPersonName' => 'Only alphabets and space is allowed',
				'pincode' => 'Pincode should contain only digits'
		);

		if($field_name != '') {
			return $descriptions[$field_name];
		} else {
			return $descriptions;
		}
	}

	public function get_supplier_details($supplier_code='') {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$params['clientID'] = $client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get', 'suppliers/details/'.$supplier_code, $params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'supplierDetails' => $response['response']['supplierDetails'],
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
		$pagination = 'no';
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
			if(isset($search_params['pagination'])) {
				$pagination = Utilities::clean_string($search_params['pagination']);
				$params['pagination'] = $pagination;
			}
		}

		// fetch client id
		$client_id = Utilities::get_current_client_id();

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get', 'suppliers/'.$client_id, $params);
		$status = $response['status'];
		if ($status === 'success') {
			if($pagination==='yes') {
				return array(
					'status' => true,  
					'suppliers' => $response['response']['results'], 
					'total_pages' => $response['response']['total_pages'],
					'total_records' => $response['response']['total_records'],
					'record_count' =>  $response['response']['this_page']
				);				
			} else {
				return array(
					'status' => true,  
					'suppliers' => $response['response']['results']
				);
			}
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}	
	}

	public function get_supplier_payments_due($params=array()) {

		// fetch client id
		$client_id = Utilities::get_current_client_id();

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get', 'reports/suppliers-payment-due/'.$client_id, $params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'suppliers' => $response['response']['records']
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

}