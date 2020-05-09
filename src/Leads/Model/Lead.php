<?php

namespace Leads\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;
use Curl\Curl;

class Lead {

	# create a lead
	public function createLead($form_data = []) {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','crm-object/create/lead',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'leadCode' => $response['response']['objCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	# update a lead
	public function updateLead($form_data = [], $lead_code='') {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put','crm-object/update/lead/'.$lead_code,$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	# delete a lead
	public function deleteLead($lead_code='') {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('delete','crm-object/delete/lead/'.$lead_code,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	# get lead details
	public function leadDetails($lead_code = '') {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','crm-object/details/lead/'.$lead_code,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'leadDetails' => $response['response']['objectDetails']);
		} elseif($status === 'failed') {
			return array('status' => false);
		}
	}	

	# get all leads
	public function getAllLeads($page_no=1, $per_page=100, $lead_status_id='') {
		$search_params = [
			'pageNo' => $page_no,
			'perPage' => $per_page,
			'leadStatusId' => $lead_status_id,
		];
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','crm-object/list/lead', $search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'leadsObject' => $response['response']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function bulkLeadsUpload($form_data=[]) {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','crm-object/import-data/lead',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'objectsInserted' => $response['response']['objectsInserted']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

}