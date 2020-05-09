<?php 

namespace Tasks\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;
use Atawa\Importer;
use Atawa\CrmUtilities;
use User\Model\User;

use Tasks\Model\Task;

class TasksController
{

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->task_model = new Task;
    $this->flash = new Flash;
    $this->user_model = new User;
	}

	# task create action
	public function taskCreateAction(Request $request) {

		$page_error = $page_success = '';
    $form_data = $form_errors = [];
    $users = $users_a = [];
    $time_array_a = [];

    # form submit
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      # validate form data
      $form_validation = $this->_validate_form_data($form_data);
      if($form_validation['status'] === false) {
        $form_errors = $form_validation['errors'];
        $message = 'You have errors in the Form. Please fix them before you hit Save.';
        $this->flash->set_flash_message($message, 1);
      } else {
        # hit api and get the status.
        $api_action = $this->task_model->createTask($form_data);
        if($api_action['status']) {
          $task_code = $api_action['taskCode'];
          $message = '<i class="fa fa-check-circle-o" aria-hidden="true"></i>&nbsp;Task created successfully with code `'.$task_code.'`';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/task/create');
        } else {
          $form_errors = Utilities::format_api_error_messages($api_action['apierror']);
          $message = 'You have errors in the Form. Please fix them before you hit Save.';
          $this->flash->set_flash_message($message, 1);
        }
      }
    }

    // get users from api
    $result = $this->user_model->get_users();
    if($result['status']) {
      $users_a = $result['users'];
      foreach($users_a as $user_details) {
        $users[$user_details['uuid']] = $user_details['userName'];
      }
    }

    // time_array
    for($i=0;$i<=23;$i++) {
      for($j=0;$j<60;$j++) {
        $string_hours = $i > 9 ? $i : '0'.$i;
        $string_minutes = $j > 9 ? $j : '0'.$j;
        $time_array_a[$string_hours.$string_minutes] = $string_hours.':'.$string_minutes;
      }
    }

    # prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'task_types_a' => array(''=>'Choose') + CrmUtilities::get_task_types(),
      'task_response_a' => array(''=>'Choose') + CrmUtilities::get_task_response(),
      'task_status_a' => array(''=>'Choose') + CrmUtilities::get_task_status(),
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'time_array_a' => $time_array_a,
      'users' => $users,
      'flash' => $this->flash,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'CRM - Create Task',
      'icon_name' => 'fa fa-bolt',
    );

    return array($this->template->render_view('task-create', $template_vars),$controller_vars);		
	}

	# task update action
	public function taskUpdateAction(Request $request) {
    $page_error = $page_success = '';
    $lead_source_id = $lead_status_id = $lead_rating_id = '';
    $industry_id = $lead_emprange_id = '';
    $task_code = !is_null($request->get('taskCode')) ? Utilities::clean_string($request->get('taskCode')) : '';

    $form_data = $form_errors = [];
    $users = $users_a = [];

    # form submit
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      # validate form data
      $form_validation = $this->_validate_form_data($form_data);
      if($form_validation['status'] === false) {
        $form_errors = $form_validation['errors'];
        $message = 'You have errors in the Form. Please fix them before you hit Save.';
        $this->flash->set_flash_message($message, 1);
      } else {
        # hit api and get the status.
        $api_action = $this->task_model->updateTask($form_data, $task_code);
        if($api_action['status']) {
          $message = '<i class="fa fa-check-circle-o" aria-hidden="true"></i>&nbsp;Task updated successfully with code `'.$task_code.'`';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/task/update/'.$task_code);
        } else {
          $form_errors = Utilities::format_api_error_messages($api_action['apierror']);
          $message = 'You have errors in the Form. Please fix them before you hit Save.';
          $this->flash->set_flash_message($message, 1);
        }
      }      

    # get task details
    } elseif( !is_null($request->get('taskCode')) ) {
      $task_code = Utilities::clean_string($request->get('taskCode'));
      $task_details_response = $this->task_model->taskDetails($task_code);
      if($task_details_response['status'] === false) {
        $this->flash->set_flash_message('Invalid task object', 1);
        Utilities::redirect('/tasks/list');
      } else {
        $form_data = $task_details_response['taskDetails'];
        $start_time_array = explode(' ', $form_data['taskStartDate']);
        $end_time_array = explode(' ', $form_data['taskEndDate']);
        $form_data['taskStartDate'] = date("d-m-Y", strtotime($start_time_array[0]));
        $form_data['taskEndDate'] = date("d-m-Y", strtotime($end_time_array[0]));
        $form_data['taskStartTime'] = substr(str_replace(':', '', $start_time_array[1]),0,4);
        $form_data['taskEndTime'] = substr(str_replace(':', '', $end_time_array[1]),0,4);
        // dump($form_data);
      }
    } else {
      $this->flash->set_flash_message('Invalid task object (or) task does not exists',1);         
      Utilities::redirect('/tasks/list');
    }

    // get users from api
    $result = $this->user_model->get_users();
    if($result['status']) {
      $users_a = $result['users'];
      $user_ids = array_column($users_a, 'uid');
      $user_uuids = array_column($users_a, 'uuid');
      $user_ids_uuids = array_combine($user_ids, $user_uuids);
      foreach($users_a as $user_details) {
        $users[$user_details['uuid']] = $user_details['userName'];
      }
      if(isset($user_ids_uuids[$form_data['taskOwnerId']])) {
        $form_data['taskOwnerId'] = $user_ids_uuids[$form_data['taskOwnerId']];
      } else {
        $form_data['taskOwnerId'] = '';
      }
    }

    // time_array
    for($i=0;$i<23;$i++) {
      for($j=0;$j<60;$j++) {
        $string_hours = $i > 9 ? $i : '0'.$i;
        $string_minutes = $j > 9 ? $j : '0'.$j;
        $time_array_a[$string_hours.$string_minutes] = $string_hours.':'.$string_minutes;
      }
    }    

    # prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'task_types_a' => array(''=>'Choose') + CrmUtilities::get_task_types(),
      'task_response_a' => array(''=>'Choose') + CrmUtilities::get_task_response(),
      'task_status_a' => array(''=>'Choose') + CrmUtilities::get_task_status(),
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'time_array_a' => $time_array_a,
      'users' => $users,
      'flash' => $this->flash,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'CRM - Update Task',
      'icon_name' => 'fa fa-bolt',
    );

    return array($this->template->render_view('task-update', $template_vars),$controller_vars);
	}

	# task remove action
	public function taskRemoveAction(Request $request) {
    if( !is_null($request->get('taskCode')) ) {
      $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')) : 1;      
      $task_code = Utilities::clean_string($request->get('taskCode'));
      $task_details_response = $this->task_model->taskDetails($task_code);
      if($task_details_response['status'] === false) {
        $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;Invalid task object', 1);
        Utilities::redirect('/tasks/list');
      } else {
        $lead_api_response = $this->task_model->deleteTask($task_code);
        if($lead_api_response['status']) {
          $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;Task removed successfully.');
        } else {
          $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;An error occurred while removing this task.', 1);          
        }
        Utilities::redirect('/tasks/list/'.$page_no);
      }
    } else {
      $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;Invalid entry', 1);          
      Utilities::redirect('/tasks/list');
    }
	}

	# task list action
	public function taskListAction(Request $request) {

    $tasks = $task_status_a = $task_response_a = [];
    $task_types_a = $filter_params = [];
    $tasks_orderby = ['' => 'Task created date', 'startDate' => 'Task start date', 'endDate' => 'Task end date'];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    $page_error = $page_success = '';

    # check page no and per page variables.
    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')):1;
    $per_page = $request->get('perPage') !== null ? Utilities::clean_string($request->get('perPage')):100;
    $task_status_id = $request->get('taskStatusId') !== null ? Utilities::clean_string($request->get('taskStatusId')) : '';
    $task_type_id = $request->get('taskTypeId') !== null ? Utilities::clean_string($request->get('taskTypeId')) : '';
    $task_response_id = $request->get('taskResponseId') !== null ? Utilities::clean_string($request->get('taskResponseId')) : '';
    $task_time =  $request->get('taskTime') !== null ? Utilities::clean_string($request->get('taskTime')) : 'future';
    $order_by = $request->get('orderBy') !== null ? Utilities::clean_string($request->get('orderBy')) : '';

    $filter_params['taskStatusId'] = $task_status_id;
    $filter_params['taskTypeId'] = $task_type_id;
    $filter_params['taskResponseId'] = $task_response_id;
    $filter_params['taskTime'] = $task_time;
    $filter_params['orderBy'] = $order_by;
    $filter_params['pageNo'] = $page_no;
    $filter_params['perPage'] = $per_page;

    # hit api and get the status.
    $api_action = $this->task_model->getAllTasks($filter_params);
    $api_status = $api_action['status'];

    // dump($api_action);

    # check api status
    if($api_status) {
      # check whether we got leads or not.
      if(count($api_action['tasksObject']['tasks']) >0) {

        $slno = Utilities::get_slno_start(count($api_action['tasksObject']['tasks']),$per_page,$page_no);
        $to_sl_no = $slno+$per_page;

        $slno++;

        if($page_no <= 3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;
        }
        if($api_action['tasksObject']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $api_action['tasksObject']['total_pages'];
        }
        if($api_action['tasksObject']['this_page'] < $per_page) {
          $to_sl_no = ($slno+$api_action['tasksObject']['this_page'])-1;
        }
        $tasks = $api_action['tasksObject']['tasks'];
        $total_pages = $api_action['tasksObject']['total_pages'];
        $total_records = $api_action['tasksObject']['total_records'];
        $record_count = $api_action['tasksObject']['total_records'];
      } else {
        $page_error = $api_action['apierror'];
      }
    } else {
      $page_error = $api_action['apierror'];
    }    

    # prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'tasks' => $tasks,
      'total_pages' => $total_pages,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'filter_params' => $filter_params,
      'task_types_a' => array(''=>'All task types') + CrmUtilities::get_task_types(),
      'task_response_a' => array(''=>'All task responses') + CrmUtilities::get_task_response(),
      'task_status_a' => array(''=>'All task statuses') + CrmUtilities::get_task_status(),
      'task_times' => ['future' => 'Upcoming', 'all' => 'All'],
      'tasks_orderby' => $tasks_orderby,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'CRM - Tasks',
      'icon_name' => 'fa fa-bolt',
    );

    return array($this->template->render_view('task-list', $template_vars),$controller_vars);
	}

  # task details
  public function leadDetailsAction(Request $request) {
  }

  # validate form data
  private function _validate_form_data($form_data=[]) {
    $errors = [];
    $task_owner_id = Utilities::clean_string($form_data['taskOwnerId']);
    $task_start_date = Utilities::clean_string($form_data['taskStartDate']);
    $task_start_time = Utilities::clean_string($form_data['taskStartTime']);
    $task_end_date = Utilities::clean_string($form_data['taskEndDate']);
    $task_end_time = Utilities::clean_string($form_data['taskEndTime']);
    $task_title = Utilities::clean_string($form_data['taskTitle']);
    $task_description = Utilities::clean_string($form_data['taskDescription']); 
    $task_type =  Utilities::clean_string($form_data['taskTypeId']);
    $task_response = Utilities::clean_string($form_data['taskResponseId']);
    $task_status = Utilities::clean_string($form_data['taskStatusId']);
    $task_start_time_ts = strtotime($task_start_date.' '.$task_start_time);
    $task_end_time_ts = strtotime($task_end_date.' '.$task_end_time);

    if($task_owner_id === '') {
      $errors['taskOwnerId'] = 'Invalid owner';
    } else {
      $cleaned_params['taskOwnerId'] = $task_owner_id; 
    }
    if($task_start_date === '') {
      $errors['taskStartDate'] = 'Invalid start date';
    } else {
      $cleaned_params['taskStartDate'] = $task_start_date; 
    }
    if($task_end_date === '') {
      $errors['taskEndDate'] = 'Invalid end date';
    } else {
      $cleaned_params['taskEndDate'] = $task_end_date; 
    }
    if($task_start_time === '') {
      $errors['taskStartTime'] = 'Invalid start time';
    } else {
      $cleaned_params['taskStartTime'] = $task_start_time; 
    }
    if($task_end_time === '') {
      $errors['taskEndTime'] = 'Invalid end time';
    } else {
      $cleaned_params['taskEndTime'] = $task_end_time; 
    }
    if($task_title === '') {
      $errors['taskTitle'] = 'Task title required';
    } else {
      $cleaned_params['taskTitle'] = $task_title; 
    }
    if($task_type === '') {
      $errors['taskTypeId'] = 'Task type required';
    } else {
      $cleaned_params['taskTypeId'] = $task_type; 
    }
    if($task_response === '') {
      $errors['taskResponseId'] = 'Task response required';
    } else {
      $cleaned_params['taskResponseId'] = $task_response; 
    }
    if($task_status === '') {
      $errors['taskStatusId'] = 'Task response required';
    } else {
      $cleaned_params['taskStatusId'] = $task_status; 
    }
    if($task_end_time <= $task_start_time) {
      // $errors['taskEndDate'] = 'Must be greater than Start date.';
      $errors['taskEndTime'] = 'Must be greater than Start time';
    }
    $cleaned_params['taskDescription'] = $task_description;

    if(count($errors) > 0) {
      return [
        'status' => false,
        'errors' => $errors,
      ];
    } else {
      return [
        'status' => true,
        'cleaned_params' => $cleaned_params,
      ];  
    }
  }
}