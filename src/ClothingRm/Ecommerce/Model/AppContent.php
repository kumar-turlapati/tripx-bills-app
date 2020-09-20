<?php

namespace ClothingRm\Ecommerce\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class AppContent {

	private $api_caller;

	public function __construct() {
		$this->api_caller = new ApiCaller;
	}

	/** content create **/
	public function content_create($params=[]) {
		$request_uri = 'ecom/app-content';
		$response = $this->api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true, 'contentCode' => $response['response']['contentCode']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	/** content update **/
	public function content_update($params=[], $content_id='') {
		$request_uri = 'ecom/app-content/'.$content_id;
		$response = $this->api_caller->sendRequest('put',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}	

	/** content list **/
	public function content_list($params=[]) {
		$request_uri = 'ecom/app-content/list';
		$response = $this->api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true, 'content' => $response['response']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	/** delete content **/
	public function content_delete($category_id='') {
		$request_uri = 'ecom/app-content/'.$category_id;
		$response = $this->api_caller->sendRequest('delete',$request_uri,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	/** content details **/
	public function get_content_details($content_id = '') {
		$request_uri = 'ecom/app-content/details/'.$content_id;
		$response = $this->api_caller->sendRequest('get',$request_uri,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true, 'contentDetails' => $response['response']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}
}