<?php

namespace ClothingRm\Categories\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Categories 
{

	# get product categories with count
	public function get_categories($page_no=1, $per_page=100, $search_params=array()) {

		$params = array();
		$params['pageNo']  = $page_no;
		$params['perPage'] = $per_page;
		if(count($search_params)>0) {
			if(isset($search_params['catname'])) {
				$cat_name = Utilities::clean_string($search_params['catname']);
				$params['catName'] = $cat_name;
			}				
		}

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get', 'categories/wic', $params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'categories' => $response['response']
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}	
	}

	# get product categories
	public function get_product_categories() {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get', 'categories');
		$status = $response['status'];
		if ($status === 'success') {
			return $response['response'];
		} elseif($status === 'failed') {
			return array();
		}
	}

	# create a category.
	public function create_product_category($form_data=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post', 'categories', $form_data);
		$status = $response['status'];
		if($status === 'success') {
			return ['status' => true, 'categoryCode' => $response['response']['categoryCode'] ];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	# update a category.
	public function update_product_category($form_data=[], $category_code='') {
		$api_caller = new ApiCaller();
		$form_data['categoryCode'] = $category_code;
		$response = $api_caller->sendRequest('put', 'categories', $form_data);
		$status = $response['status'];
		if($status === 'success') {
			return ['status' => true, ];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	# get product details.
	public function get_category_details($category_code='', $category_location = '') {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get', 'categories/details?categoryCode='.$category_code.'&locationCode='.$category_location, []);
		$status = $response['status'];
		if ($status === 'success') {
			return $response['response']['categoryDetails'];
		} elseif($status === 'failed') {
			return false;
		}
	}

}