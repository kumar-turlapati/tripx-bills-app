<?php

namespace SalesCategory\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class SalesCategory {
	
	private $api_caller;

	public function __construct() {
		$this->api_caller = new ApiCaller;
	}

	public function add_sales_category($form_data = []) {
		$response = $this->api_caller->sendRequest('post','sales-category',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'salesCategoryCode' => $response['response']['salesCategoryCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function list_sales_categories($search_params=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','/sales-categories',$search_params);
		if($response['status']==='success') {
			return ['status'=>true,'response'=>$response['response']];
		} else {
			return ['status'=>false,'apierror'=> $response['reason']];
		}
	}

	public function update_sales_category($form_data = [], $category_code = '') {
		$response = $this->api_caller->sendRequest('put', 'sales-category/'.$category_code, $form_data);
		$status = $response['status'];
		if($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_sales_category_details($category_code = '') {
		$response = $this->api_caller->sendRequest('get','sales-category/details/'.$category_code,[]);
		if($response['status'] === 'success') {
			return ['status'=>true, 'category_details' => $response['response']];
		} else {
			return ['status'=>false, 'apierror' => $response['reason']];
		}
	}

}