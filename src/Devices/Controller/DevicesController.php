<?php 

namespace Devices\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;
use Devices\Model\Devices;
use User\Model\User;

class DevicesController
{
	protected $views_path;

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->device_model = new Devices;
    $this->flash = new Flash;
    $this->user_model = new User;
	}

  public function addDeviceAction(Request $request) {
    $submitted_data = $form_errors = $users_a = [];

    // ---------- get location codes from api ------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    // ---------- get users from api ------------------------------
    $result = $this->user_model->get_users();
    if($result['status']) {
      $users_a = $result['users'];
      foreach($users_a as $user_details) {
        $users[$user_details['uuid']] = $user_details['userName'].' [ '.$location_ids[$user_details['locationID']].' ]';
      }
    }

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all());
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $this->device_model->add_device($form_data);
        if($result['status']===true) {
          $message = 'Device added successfully with device code `'.$result['deviceCode'].'`';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/devices/list');
        } else {
          $message = $result['apierror'];
          $this->flash->set_flash_message($message,1);
          Utilities::redirect('/devices/list');          
        }
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $request->request->all();
      }
    }

    // prepare form variables.
    $template_vars = array(
      'submitted_data' => $submitted_data,
      'users' => array(''=>'Choose')+$users,
      'status_a' => array(99 => 'Choose', 1 => 'Active', 0 => 'Inactive'),
      'form_errors' => $form_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Devices management',
      'icon_name' => 'fa fa-cogs',      
    );

    // render template
    return array($this->template->render_view('device-add', $template_vars), $controller_vars);
  }

  public function updateDeviceAction(Request $request) {
    $submitted_data = $form_errors = $users_a = [];

    // ---------- get location codes from api ------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    // ---------- get users from api ------------------------------
    $result = $this->user_model->get_users();
    if($result['status']) {
      $users_a = $result['users'];
      foreach($users_a as $user_details) {
        $users[$user_details['uuid']] = $user_details['userName'].' [ '.$location_ids[$user_details['locationID']].' ]';
      }
    }

    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $url_device_code = Utilities::clean_string($request->get('deviceCode'));
      if($form_data['deviceCode'] !== $url_device_code) {
        $this->flash->set_flash_message('Invalid device name for editing.',1);
        Utilities::redirect('/devices/list');
      }

      $validate_form = $this->_validate_form_data($form_data, $url_device_code);
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $device_code = Utilities::clean_string($form_data['deviceCode']);
        $result = $this->device_model->update_device($form_data, $device_code);
        if($result['status']) {
          $message = 'Device updated successfully with device code `'.$device_code.'`';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/devices/list');
        } else {
          $message = $result['apierror'];
          $this->flash->set_flash_message($message,1);
          Utilities::redirect('/devices/list');          
        }
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $form_data;
      }
    } elseif( !is_null($request->get('deviceCode')) ) {
      $device_code = Utilities::clean_string($request->get('deviceCode'));
      $device_details = $this->device_model->get_device_details($device_code);
      if($device_details['status']) {
        $submitted_data = $device_details['deviceDetails'];
      } else {
        $this->flash->set_flash_message('Invalid device code.',1);
        Utilities::redirect('/devices/list');
      }
    } else {
      $this->flash->set_flash_message('Device code is required for editing a device.',1);
      Utilities::redirect('/devices/list');
    }

    // prepare form variables.
    $template_vars = array(
      'submitted_data' => $submitted_data,
      'users' => array(''=>'Choose')+$users,
      'status_a' => array(99 => 'Choose', 1 => 'Active', 0 => 'Inactive'),
      'form_errors' => $form_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Devices management',
      'icon_name' => 'fa fa-cogs',      
    );

    // render template
    return array($this->template->render_view('device-update', $template_vars), $controller_vars);    
  }

  public function deleteDeviceAction(Request $request) {
    if(!is_null($request->get('deviceCode'))) {
      $device_code = Utilities::clean_string($request->get('deviceCode'));
      $device_details = $this->device_model->get_device_details($device_code);
      if($device_details['status']) {
        $submitted_data = $device_details['deviceDetails'];
      } else {
        $this->flash->set_flash_message('Invalid device code.',1);
        Utilities::redirect('/devices/list');
      }
    } else {
      $this->flash->set_flash_message('Invalid Device Name',1);
      Utilities::redirect('/devices/list');
    }

    $response = $this->device_model->delete_device($device_code);
    if($response['status']) {
      $this->flash->set_flash_message('<i class="fa fa-times"></i>&nbsp;&nbsp;Device deleted successfully.');
    } else {
      $message = $result['apierror'];
      $this->flash->set_flash_message($message,1);
    }
    
    Utilities::redirect('/devices/list');
  }

  public function listDevicesAction(Request $request) {
    $devices_a = $search_params = $users_a = $users = [];
    $page_error = '';
    
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    // ---------- get location codes from api ------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    // get users from api
    $result = $this->user_model->get_users();
    if($result['status']) {
      $users_a = $result['users'];
      $uuid_a = array_column($users_a, 'uuid');
      $uid_a =  array_column($users_a, 'uid');
      $users_final = array_combine($uuid_a, $uid_a);
      foreach($users_a as $user_details) {
        $users[$user_details['uuid']] = $user_details['userName'].' - '.$location_ids[$user_details['locationID']];
      }
    }

    // parse request parameters.
    $uuid = $request->get('uuid') !== null ? Utilities::clean_string($request->get('uuid')) : '';
    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')) : 1;
    $location_code = $request->get('locationCode') !== null ? Utilities::clean_string($request->get('locationCode')) : '';
    $per_page = 100;

    $search_params = array(
      'pageNo' => $page_no,
      'perPage' => $per_page,
      'uid' => $uuid !== '' && isset($users_final[$uuid]) ? $users_final[$uuid] : 0,
      'locationCode' => $location_code,
    );

    // dump($search_params);
    // exit;

    $api_response = $this->device_model->get_devices($search_params);
    // dump($api_response);
    // exit;
    if($api_response['status']) {
      if(count($api_response['data']['devices'])>0) {
          $slno = Utilities::get_slno_start(count($api_response['data']['devices']),$per_page,$page_no);
          $to_sl_no = $slno+$per_page;
          $slno++;
          if($page_no <= 3) {
            $page_links_to_start = 1;
            $page_links_to_end = 10;
          } else {
            $page_links_to_start = $page_no-3;
            $page_links_to_end = $page_links_to_start+10;            
          }
          if($api_response['data']['total_pages']<$page_links_to_end) {
            $page_links_to_end = $api_response['data']['total_pages'];
          }
          if($api_response['data']['this_page'] < $per_page) {
            $to_sl_no = ($slno+$api_response['data']['this_page'])-1;
          }
          $devices_a = $api_response['data']['devices'];
          $total_pages = $api_response['data']['total_pages'];
          $total_records = $api_response['data']['total_records'];
          $record_count = $api_response['data']['this_page'];
      } else {
        $page_error = $api_response['apierror'];
      }
    } else {
      $page_error = $api_response['apierror'];
    }

    // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'devices' => $devices_a,
      'users' => ['' => 'All Users'] + $users,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params + ['uuid' => $uuid],
      'client_locations' => ['' => 'All Stores'] + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,      
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Devices management',
      'icon_name' => 'fa fa-cogs',      
    );

    // render template
    return array($this->template->render_view('devices-list', $template_vars), $controller_vars);    
  }

  public function showDeviceName(Request $request) {

    if(!isset($_SESSION['__bq_fp'])) {
      session_destroy();
      Utilities::redirect('/login');
    }

    // prepare form variables.
    $template_vars = array(
      'device_name' => $_SESSION['__bq_fp'],
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Device Name',
      'icon_name' => 'fa fa-cogs',
    );

    // render template
    return array($this->template->render_view('device-show-name', $template_vars), $controller_vars);     
  }

  private function _validate_form_data($form_data=[], $device_code='') {
    $cleaned_params = $errors = [];
    $uid = isset($form_data['uid']) && $form_data['uid'] !== '' ? Utilities::clean_string($form_data['uid']) : '';
    $device_name = isset($form_data['deviceName']) && $form_data['deviceName'] !== '' ? Utilities::clean_string($form_data['deviceName']) : '';
    $status = isset($form_data['deviceName']) && $form_data['status'] !== '' ? Utilities::clean_string($form_data['status']) : 1;

    if($uid === '') {
      $errors['uid'] = 'User name is mandatory.';
    } else {
      $cleaned_params['uid'] = $uid;
    }
    if(strlen($device_name) !== 32) {
      $errors['deviceName'] = 'Invalid device name. Must be 32 characters length.';
    } else {
      $cleaned_params['deviceName'] = $device_name;
    }    
    if(!is_numeric($status) || (int)$status === 99) {
      $errors['status'] = 'Invalid status';
    } else {
      $cleaned_params['status'] = $status;
    }

    if(isset($form_data['deviceCode'])) {
      $cleaned_params['deviceCode'] = Utilities::clean_string($form_data['deviceCode']);
    }

    if(count($errors)>0) {
      return array(
        'status' => false,
        'errors' => $errors,
      );
    } else {
      return array(
        'status' => true,
        'cleaned_params' => $cleaned_params,
      );
    }
  }

}