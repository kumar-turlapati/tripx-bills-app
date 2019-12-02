<?php

namespace ClothingRm\Ecommerce\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Catalog {

	private $api_caller;

	public function __construct() {
		$this->api_caller = new ApiCaller;
	}

	/** catalog create **/
	public function catalog_create($params=[]) {
		$request_uri = 'catalog';
		$response = $this->api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true, 'catalogCode' => $response['response']['catalogCode']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	/** catalog update **/
	public function catalog_update($params=[], $catalog_code='') {
		$request_uri = 'catalog/'.$catalog_code;
		$response = $this->api_caller->sendRequest('put',$request_uri,$params);
		$status = $response['status'];
		if($status === 'success') {
			return ['status' => true];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	/** catalog delete **/
	public function catalog_delete($catalog_code='') {
		$request_uri = 'catalog/'.$catalog_code;
		$response = $this->api_caller->sendRequest('delete',$request_uri,[]);
		$status = $response['status'];
		if($status === 'success') {
			return ['status' => true];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}	

	/** catalog list **/
	public function catalogs_list($params=[]) {
		$request_uri = 'catalog/list';
		$response = $this->api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true, 'catalogs' => $response['response']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	/** catalog details **/
	public function get_catalog_details($catalog_code = '') {
		$request_uri = 'catalog/details/'.$catalog_code;
		$response = $this->api_caller->sendRequest('get',$request_uri,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true, 'catalogDetails' => $response['response']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	public function catalog_items($catalog_code = '', $params=[]) {
		$request_uri = 'catalog/items/'.$catalog_code;
		$response = $this->api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'response' => $response['response']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

	public function add_item_to_catalog($lc='', $cc='', $ic='') {
		$request_uri = 'catalog/item/'.$lc.'/'.$cc.'/'.$ic;
		$response = $this->api_caller->sendRequest('post',$request_uri,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'response' => $response['response']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function remove_item_from_catalog($ic='') {
		$request_uri = 'catalog/item/'.$ic;
		$response = $this->api_caller->sendRequest('delete',$request_uri,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'response' => $response['response']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}
}