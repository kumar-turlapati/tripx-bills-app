<?php

namespace Campaigns\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Campaigns {

	private $api_caller;

	public function __construct() {
		$this->api_caller = new ApiCaller();
	}

	public function create_campaign($form_data = []) {
		$response = $this->api_caller->sendRequest('post','campaign/create',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'campaignCode' => $response['response']['campCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}
	
	public function update_campaign($form_data = [], $campaign_code = '') {
		$response = $this->api_caller->sendRequest('put','campaign/update/'.$campaign_code,$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_campaign_details($campaign_code = '') {
		$response = $this->api_caller->sendRequest('get','campaign/details/'.$campaign_code,[]);
		if($response['status'] === 'success') {
			return ['status'=>true,'campaign_details'=>$response['response']['campaignDetails']];
		} else {
			return ['status'=>false,'apierror'=>$response['reason']];
		}
	}

	public function list_campaigns() {
		$response = $this->api_caller->sendRequest('get','campaign/list',[]);
		if($response['status']==='success') {
			return ['status'=>true,'campaigns' => $response['response']];
		} else {
			return ['status'=>false,'apierror' => $response['reason']];
		}
	}

	public function get_live_campaigns() {
		$response = $this->api_caller->sendRequest('get','campaign/live',[]);
		if($response['status']==='success') {
			return ['status'=>true,'campaigns' => $response['response']];
		} else {
			return ['status'=>false,'campaigns' => []];
		}		
	}

}