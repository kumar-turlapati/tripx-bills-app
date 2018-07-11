<?php

namespace ClothingRm\Location\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Location {

	# create client location details.
	public function create_client_location($form_data=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post', 'clients/locations', $form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true, 'locationCode'=>$response['response']['locationCode']);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}		

	# update client location details.
	public function update_client_location($location_code = '', $form_data=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put','clients/locations/'.$location_code,$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true, 'locationDetails'=>$response['response']['locationDetails']);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}	

	# get client locations.
	public function get_client_locations($search_params=[], $with_ids=false, $full_details=false) {
		$request_uri = 'clients/locations';
		$url_params = [];
		if($with_ids) {
			$url_params[] = 'ids=true';
		}
		if($full_details) {
			$url_params[] = 'fd=true';
		}
		if(count($url_params)>0) {
			$request_uri .= '?'.http_build_query($url_params);
		}

		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true, 'clientLocations'=>$response['response']['clientLocations']);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}

	# get client location details.
	public function get_client_location_details($location_code = '') {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','clients/location/details/'.$location_code,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true, 'locationDetails'=>$response['response']['locationDetails']);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}
}