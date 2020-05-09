<?php

namespace Appointments\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;
use Curl\Curl;

class Appointments {

	// create a appointment
	public function createAppointment($form_data = []) {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','crm-object/create/appointment',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'appointmentCode' => $response['response']['objCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	// update appointment
	public function updateAppointment($form_data = [], $appointment_code='') {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put','crm-object/update/appointment/'.$appointment_code,$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	// delete appointment
	public function deleteAppointment($appointment_code='') {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('delete','crm-object/delete/appointment/'.$appointment_code,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	// get appointment details
	public function appointmentDetails($appointment_code = '') {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','crm-object/details/appointment/'.$appointment_code,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'appointmentDetails' => $response['response']['objectDetails']);
		} elseif($status === 'failed') {
			return array('status' => false);
		}
	}	

	# get all appointments
	public function getAllAppointments($filter_params = []) {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','crm-object/list/appointment', $filter_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'appointmentsObject' => $response['response']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}
}