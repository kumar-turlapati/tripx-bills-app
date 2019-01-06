<?php

namespace ClothingRm\StockAudit\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class StockAudit {

	private $api_caller;
	
	public function __construct() {
		$this->api_caller = new ApiCaller();
	}

	// get all stock audit entries
	public function get_audit_register($search_params=[]) {
		$response = $this->api_caller->sendRequest('get', 'stockaudit/register', $search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'records' => $response['response']
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	// create audit entry
	public function create_audit($form_data=[]) {
		$response = $this->api_caller->sendRequest('post', 'stockaudit', $form_data);
		$status = $response['status'];
		if($status === 'success') {
			return ['status' => true, 'auditCode' => $response['response']['auditCode']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	// get audit items
	public function get_audit_items($search_params=[], $audit_code='') {
		$response = $this->api_caller->sendRequest('post', 'stockaudit/fetch-items/'.$audit_code, $search_params);
		$status = $response['status'];
		if($status === 'success') {
			return ['status' => true, 'response' => $response['response']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}	

	// update audit entry
	public function update_audit($form_data=[], $audit_code='') {
/*		$response = $this->api_caller->sendRequest('put', 'mfg/'.$mfg_code, $form_data);
		$status = $response['status'];
		if($status === 'success') {
			return ['status' => true];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}*/
	}

	// lock the audit.
	public function lock_audit($audit_code = '') {
		$response = $this->api_caller->sendRequest('post', 'stockaudit/lock/'.$audit_code,[]);
		$status = $response['status'];
		if($status === 'success') {
			return ['status' => true];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	// post system qty.
	public function post_system_qty($audit_code = '') {
		$response = $this->api_caller->sendRequest('post', 'stockaudit/post-system-qty/'.$audit_code,[]);
		// dump($response);
		// exit;
		$status = $response['status'];
		if($status === 'success') {
			return ['status' => true, 'records' => ($response['response']['upsert'])/2];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	// get audit entry details
	public function get_audit_details($audit_code = '') {
		$response = $this->api_caller->sendRequest('get', 'stockaudit/details/'.$audit_code, []);
		$status = $response['status'];
		if ($status === 'success') {
			return $response['response'];
		} elseif($status === 'failed') {
			return false;
		}
	}
}