<?php 

namespace Appointments\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;
use Atawa\Importer;
use Atawa\CrmUtilities;
use User\Model\User;

use Appointments\Model\Appointments;

class AppointmentsController
{

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->appt_model = new Appointments;
    $this->flash = new Flash;
    $this->user_model = new User;
	}

	# appt create action
	public function appointmentCreateAction(Request $request) {

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
        $api_action = $this->appt_model->createAppointment($form_data);
        if($api_action['status']) {
          $appointment_code = $api_action['appointmentCode'];
          $message = '<i class="fa fa-check-circle-o" aria-hidden="true"></i>&nbsp;Appointment created successfully with code `'.$appointment_code.'`';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/appointment/create');
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
      'appointment_types_a' => array(''=>'Choose') + CrmUtilities::get_appointment_types(),
      'appointment_purpose_a' => array(''=>'Choose') + CrmUtilities::get_appointment_purpose(),
      'appointment_status_a' => array(''=>'Choose') + CrmUtilities::get_appointment_status(),
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'time_array_a' => $time_array_a,
      'users' => $users,
      'flash' => $this->flash,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'CRM - Create Appointment',
      'icon_name' => 'fa fa-bolt',
    );

    return array($this->template->render_view('appointment-create', $template_vars),$controller_vars);		
	}

	# appt update action
	public function appointmentUpdateAction(Request $request) {
    $page_error = $page_success = '';
    $lead_source_id = $lead_status_id = $lead_rating_id = '';
    $industry_id = $lead_emprange_id = '';
    $appointment_code = !is_null($request->get('appointmentCode')) ? Utilities::clean_string($request->get('appointmentCode')) : '';

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
        $api_action = $this->appt_model->updateAppointment($form_data, $appointment_code);
        if($api_action['status']) {
          $message = '<i class="fa fa-check-circle-o" aria-hidden="true"></i>&nbsp;Appointment updated successfully with code `'.$appointment_code.'`';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/appointment/update/'.$appointment_code);
        } else {
          $form_errors = Utilities::format_api_error_messages($api_action['apierror']);
          $message = 'You have errors in the Form. Please fix them before you hit Save.';
          $this->flash->set_flash_message($message, 1);
        }
      }      

    # get appointment details
    } elseif( !is_null($request->get('appointmentCode')) ) {
      $appointment_code = Utilities::clean_string($request->get('appointmentCode'));
      $appointment_details_response = $this->appt_model->appointmentDetails($appointment_code);
      if($appointment_details_response['status'] === false) {
        $this->flash->set_flash_message('Invalid appointment object', 1);
        Utilities::redirect('/appointments/list');
      } else {
        $form_data = $appointment_details_response['appointmentDetails'];
        $start_time_array = explode(' ', $form_data['appointmentStartDate']);
        $end_time_array = explode(' ', $form_data['appointmentEndDate']);
        $form_data['appointmentStartDate'] = date("d-m-Y", strtotime($start_time_array[0]));
        $form_data['appointmentEndDate'] = date("d-m-Y", strtotime($end_time_array[0]));
        $form_data['appointmentStartTime'] = substr(str_replace(':', '', $start_time_array[1]),0,4);
        $form_data['appointmentEndTime'] = substr(str_replace(':', '', $end_time_array[1]),0,4);
      }
    } else {
      $this->flash->set_flash_message('Invalid appointment object (or) appointment does not exists',1);         
      Utilities::redirect('/appointments/list');
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
      if(isset($user_ids_uuids[$form_data['appointmentOwnerId']])) {
        $form_data['appointmentOwnerId'] = $user_ids_uuids[$form_data['appointmentOwnerId']];
      } else {
        $form_data['appointmentOwnerId'] = '';
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
      'appointment_types_a' => array(''=>'Choose') + CrmUtilities::get_appointment_types(),
      'appointment_purpose_a' => array(''=>'Choose') + CrmUtilities::get_appointment_purpose(),
      'appointment_status_a' => array(''=>'Choose') + CrmUtilities::get_appointment_status(),
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

    return array($this->template->render_view('appointment-update', $template_vars),$controller_vars);
	}

	# appt remove action
	public function appointmentRemoveAction(Request $request) {
    if( !is_null($request->get('appointmentCode')) ) {
      $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')) : 1;      
      $appointment_code = Utilities::clean_string($request->get('appointmentCode'));
      $appointment_details_response = $this->appt_model->appointmentDetails($appointment_code);
      if($appointment_details_response['status'] === false) {
        $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;Invalid appointment object', 1);
        Utilities::redirect('/appointments/list');
      } else {
        $appointment_api_response = $this->appt_model->deleteAppointment($appointment_code);
        if($appointment_api_response['status']) {
          $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;Appointment removed successfully.');
        } else {
          $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;An error occurred while removing this appointment.', 1);          
        }
        Utilities::redirect('/appointments/list/'.$page_no);
      }
    } else {
      $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;Invalid entry', 1);          
      Utilities::redirect('/appointments/list');
    }
	}

	# appt list action
	public function appointmentListAction(Request $request) {

    $appointments = $appointment_status_a = $appointment_response_a = [];
    $appointment_types_a = $filter_params = [];
    $appointments_orderby = ['' => 'Appointment created date', 'startDate' => 'Appointment start date', 'endDate' => 'Appointment end date'];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    $page_error = $page_success = '';

    # check page no and per page variables.
    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')):1;
    $per_page = $request->get('perPage') !== null ? Utilities::clean_string($request->get('perPage')):100;
    $appointment_status_id = $request->get('appointmentStatusId') !== null ? Utilities::clean_string($request->get('appointmentStatusId')) : '';
    $appointment_type_id = $request->get('appointmentTypeId') !== null ? Utilities::clean_string($request->get('appointmentTypeId')) : '';
    $appointment_purpose_id = $request->get('appointmentPurposeId') !== null ? Utilities::clean_string($request->get('appointmentPurposeId')) : '';
    $appointment_time =  $request->get('appointmentTime') !== null ? Utilities::clean_string($request->get('appointmentTime')) : 'future';
    $order_by = $request->get('orderBy') !== null ? Utilities::clean_string($request->get('orderBy')) : '';

    $filter_params['appointmentStatusId'] = $appointment_status_id;
    $filter_params['appointmentTypeId'] = $appointment_type_id;
    $filter_params['appointmentPurposeId'] = $appointment_purpose_id;
    $filter_params['appointmentTime'] = $appointment_time;
    $filter_params['orderBy'] = $order_by;
    $filter_params['pageNo'] = $page_no;
    $filter_params['perPage'] = $per_page;

    # hit api and get the status.
    $api_action = $this->appt_model->getAllAppointments($filter_params);
    $api_status = $api_action['status'];

    // dump($api_action);

    # check api status
    if($api_status) {
      # check whether we got leads or not.
      if(count($api_action['appointmentsObject']['appointments']) >0) {

        $slno = Utilities::get_slno_start(count($api_action['appointmentsObject']['appointments']),$per_page,$page_no);
        $to_sl_no = $slno+$per_page;

        $slno++;

        if($page_no <= 3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;
        }
        if($api_action['appointmentsObject']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $api_action['appointmentsObject']['total_pages'];
        }
        if($api_action['appointmentsObject']['this_page'] < $per_page) {
          $to_sl_no = ($slno+$api_action['appointmentsObject']['this_page'])-1;
        }
        $appointments = $api_action['appointmentsObject']['appointments'];
        $total_pages = $api_action['appointmentsObject']['total_pages'];
        $total_records = $api_action['appointmentsObject']['total_records'];
        $record_count = $api_action['appointmentsObject']['total_records'];
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
      'appointments' => $appointments,
      'total_pages' => $total_pages,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'filter_params' => $filter_params,
      'appointment_types_a' => array(''=>'All appointment types') + CrmUtilities::get_appointment_types(),
      'appointment_purpose_a' => array(''=>'All appointment purposes') + CrmUtilities::get_appointment_purpose(),
      'appointment_status_a' => array(''=>'All appointment statuses') + CrmUtilities::get_appointment_status(),
      'appointments_orderby' => $appointments_orderby,
      'appointment_times' => ['future' => 'Upcoming', 'all' => 'All'],
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'CRM - Tasks',
      'icon_name' => 'fa fa-bolt',
    );

    return array($this->template->render_view('appointment-list', $template_vars),$controller_vars);
	}

  # validate form data
  private function _validate_form_data($form_data=[]) {
    $errors = [];
    $appointment_owner_id = Utilities::clean_string($form_data['appointmentOwnerId']);
    $appointment_start_date = Utilities::clean_string($form_data['appointmentStartDate']);
    $appointment_start_time = Utilities::clean_string($form_data['appointmentStartTime']);
    $appointment_end_date = Utilities::clean_string($form_data['appointmentEndDate']);
    $appointment_end_time = Utilities::clean_string($form_data['appointmentEndTime']);
    $appointment_title = Utilities::clean_string($form_data['appointmentTitle']);
    $appointment_description = Utilities::clean_string($form_data['appointmentDescription']); 
    $appointment_type =  Utilities::clean_string($form_data['appointmentTypeId']);
    $appointment_purpose = Utilities::clean_string($form_data['appointmentPurposeId']);
    $appointment_status = Utilities::clean_string($form_data['appointmentStatusId']);
    $appointment_customer_name = Utilities::clean_string($form_data['appointmentCustomerName']);
    $appointment_start_time_ts = strtotime($appointment_start_date.' '.$appointment_start_time);
    $appointment_end_time_ts = strtotime($appointment_end_date.' '.$appointment_end_time);

    // dump($appointment_start_time_ts, $appointment_end_time_ts);
    // exit;

    if($appointment_owner_id === '') {
      $errors['appointmentOwnerId'] = 'Invalid owner';
    } else {
      $cleaned_params['appointmentOwnerId'] = $appointment_owner_id; 
    }
    if($appointment_start_date === '') {
      $errors['appointmentStartDate'] = 'Invalid start date';
    } else {
      $cleaned_params['appointmentStartDate'] = $appointment_start_date; 
    }
    if($appointment_end_date === '') {
      $errors['appointmentEndDate'] = 'Invalid end date';
    } else {
      $cleaned_params['appointmentEndDate'] = $appointment_end_date; 
    }
    if($appointment_start_time === '') {
      $errors['appointmentStartTime'] = 'Invalid start time';
    } else {
      $cleaned_params['appointmentStartTime'] = $appointment_start_time; 
    }
    if($appointment_end_time === '') {
      $errors['appointmentEndTime'] = 'Invalid end time';
    } else {
      $cleaned_params['appointmentEndTime'] = $appointment_end_time; 
    }
    if($appointment_title === '') {
      $errors['appointmentTitle'] = 'Appointment title required';
    } else {
      $cleaned_params['appointmentTitle'] = $appointment_title; 
    }
    if($appointment_type === '') {
      $errors['appointmentTypeId'] = 'Appointment type required';
    } else {
      $cleaned_params['appointmentTypeId'] = $appointment_type; 
    }
    if($appointment_purpose === '') {
      $errors['appointmentPurposeId'] = 'Appointment purpose required';
    } else {
      $cleaned_params['appointmentPurposeId'] = $appointment_purpose; 
    }
    if($appointment_status === '') {
      $errors['appointmentStatusId'] = 'Appointment status required';
    } else {
      $cleaned_params['appointmentStatusId'] = $appointment_status; 
    }
    if($appointment_customer_name === '') {
      $errors['appointmentCustomerName'] = 'Customer or Lead name required.';
    } else {
      $cleaned_params['appointmentCustomerName'] = $appointment_customer_name; 
    }    
    if($appointment_end_time_ts <= $appointment_start_time_ts) {
      $errors['appointmentEndTime'] = 'Must be greater than Start time';
    }
    $cleaned_params['appointmentDescription'] = $appointment_description;

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