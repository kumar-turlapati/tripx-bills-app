<?php

namespace ClothingRm\Customers\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Customers 
{
	public function createCustomer($params = array()) 
	{
		$client_id = Utilities::get_current_client_id();

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','customers/'.$client_id,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true,  'customerCode' => $response['response']['customerCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

	public function updateCustomer($params=array(), $customer_code='') {		
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'customers/'.$client_id.'/'.$customer_code;

		# call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put', $request_uri, $params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

/*	private function _validateFormData($params = array()) {

		$api_params = $this->_getApiParams();
		$errors = array();

		$customer_name = Utilities::clean_string($params['customerName']);
		$mobile_number = Utilities::clean_string($params['mobile']);
		$reg_no = Utilities::clean_string($params['regNo']);
		$age = Utilities::clean_string($params['age']);
		$age_category = Utilities::clean_string($params['ageCategory']);
		$dob = strtotime(Utilities::clean_string($params['dob']));
		$dor = strtotime(Utilities::clean_string($params['dor']));
		
		// check for mandatory params
		$mand_param_errors = Utilities::checkMandatoryParams(array_keys($params), $api_params['mandatory']);
		if(is_array($mand_param_errors) && count($mand_param_errors)>0) {
			return array('status' => false, 'errors' => $this->_mapErrorMessages($mand_param_errors) );
		}

		if( $customer_name === '' || !ctype_alnum(str_replace(' ', '', $params['customerName'])) ) {
			$errors['customerName'] = 'Customer name is mandatory and contains alphabets and digits.';
		} else {
			$cleand_params['customerName'] = $customer_name;
		}

		if($mobile_number !== '' && !is_numeric($mobile_number) && strlen($mobile_number) !== '') {
			$errors['mobile'] = 'Invalid mobile number.';
		} else {
			$cleand_params['mobile'] = $mobile_number;
		}

		if($dob !== '' && !Utilities::validate_date($dob)) {
			$errors['dob'] = 'Invalid date of birth.';
		} else {
			$cleand_params['dob'] = $dob;
		}

		if($dor !== '' && !Utilities::validate_date($dor)) {
			$errors['dor'] = 'Invalid date of marriage.';
		} else {
			$cleand_params['dor'] = $dor;
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
				'customerName'
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
			'customerName' => 'Customer name should contain only alphabets',
		);

		if($field_name != '') {
			return $descriptions[$field_name];
		} else {
			return $descriptions;
		}
	}*/

	public function get_customer_details($customer_code='') {

		// fetch client id
		$client_id = Utilities::get_current_client_id();

		$request_uri = 'customers/details/'.$client_id.'/'.$customer_code;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri);
		// dump($response);
		// exit;
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'customerDetails' => $response['response']['customerDetails'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	public function get_customers($page_no=1,$per_page=100,$search_params=array()) {

		$params = array();

		$params['pageNo']  = $page_no;
		$params['perPage'] = $per_page;

		if(count($search_params)>0) {
			if(isset($search_params['custName'])) {
				$cust_name = Utilities::clean_string($search_params['custName']);
				$params['custName'] = $cust_name;
			}
		}

		// dump($search_params);
		// exit;

		// fetch client id
		$client_id = Utilities::get_current_client_id();

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','customers/'.$client_id,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'customers' => $response['response']['customers'], 
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