<?php

namespace Tasks\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;
use Curl\Curl;

class Task {

	# create a task
	public function createTask($form_data = []) {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','crm-object/create/task',$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'taskCode' => $response['response']['objCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	# update a task
	public function updateTask($form_data = [], $task_code='') {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put','crm-object/update/task/'.$task_code,$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	# delete a task
	public function deleteTask($task_code='') {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('delete','crm-object/delete/task/'.$task_code,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	# get task details
	public function taskDetails($task_code = '') {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','crm-object/details/task/'.$task_code,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'taskDetails' => $response['response']['objectDetails']);
		} elseif($status === 'failed') {
			return array('status' => false);
		}
	}	

	# get all tasks
	public function getAllTasks($filter_params = []) {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','crm-object/list/task', $filter_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'tasksObject' => $response['response']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}
}