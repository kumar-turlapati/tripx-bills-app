<?php

namespace ClothingRm\Ecommerce\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Gallery {

	private $api_caller;

	public function __construct() {
		$this->api_caller = new ApiCaller;
	}

	/** gallery create **/
	public function gallery_create($params=[]) {
		$request_uri = 'gallery';
		$response = $this->api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true, 'galleryCode' => $response['response']['galleryCode']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	/** gallery update **/
	public function gallery_update($params=[], $gallery_code='') {
		$request_uri = 'gallery/'.$gallery_code;
		$response = $this->api_caller->sendRequest('put',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}	

	/** galleries list **/
	public function galleries_list($params=[]) {
		$request_uri = 'gallery/list';
		$response = $this->api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true, 'galleries' => $response['response']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	/** gallery details **/
	public function get_gallery_details($location_code = '', $gallery_code = '') {
		$request_uri = 'gallery/details/'.$location_code.'/'.$gallery_code;
		$response = $this->api_caller->sendRequest('get',$request_uri,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true, 'galleryDetails' => $response['response']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

}