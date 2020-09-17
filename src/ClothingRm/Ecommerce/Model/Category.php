<?php

namespace ClothingRm\Ecommerce\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Category {

	private $api_caller;

	public function __construct() {
		$this->api_caller = new ApiCaller;
	}

	/** category create **/
	public function category_create($params=[]) {
		$request_uri = 'ecom/category';
		$response = $this->api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true, 'galleryCode' => $response['response']['galleryCode']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	/** category update **/
	public function category_update($params=[], $category_id='') {
		$request_uri = 'ecom/category/'.$category_id;
		$response = $this->api_caller->sendRequest('put',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}	

	/** categories list **/
	public function categories_list($params=[]) {
		$request_uri = 'ecom/category/list';
		$response = $this->api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true, 'categories' => $response['response']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	/** delete category **/
	public function category_delete($category_id='') {
		$request_uri = 'ecom/category/'.$category_id;
		$response = $this->api_caller->sendRequest('delete',$request_uri,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}	

	/** category details **/
	public function get_category_details($category_id = '') {
		$request_uri = 'ecom/category/details/'.$category_id;
		$response = $this->api_caller->sendRequest('get',$request_uri,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true, 'categoryDetails' => $response['response']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

}