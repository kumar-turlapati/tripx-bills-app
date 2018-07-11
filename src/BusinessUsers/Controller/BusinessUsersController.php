<?php 

namespace BusinessUsers\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use BusinessUsers\Model\BusinessUsers;

class BusinessUsersController {
  
  protected $template, $bu_api_call, $flash_obj;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->bu_api_call = new BusinessUsers;
    $this->flash_obj = new Flash;
  }

  # bu create action
  public function buCreateAction(Request $request) {
    $form_errors = $submitted_data = [];
    $page_error = $page_success = $cust_code = '';
    $redirect_url = '/bu/create';

    $states_a = Constants::$LOCATION_STATES;
    asort($states_a);
    $countries_a = Constants::$LOCATION_COUNTRIES;

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations();

    $client_details = Utilities::get_client_details();
    $client_business_state = $client_details['locState'];

    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $new_user = $this->bu_api_call->create_business_user($cleaned_params);
        $status = $new_user['status'];
        if($status === false) {
          $page_error = $result['apierror'];
          $this->flash_obj->set_flash_message($page_error, 1);
          Utilities::redirect($redirect_url);
        } else {
          $page_success = 'Business user added successfully with code `'.$new_user['userCode'].'`';
          $this->flash_obj->set_flash_message($page_success);     
          Utilities::redirect($redirect_url);
        }
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    # prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'submitted_data' => $submitted_data,
      'errors' => $form_errors,
      'bu_types' => [''=>'Choose'] + Utilities::get_business_user_types(),
      'flash_obj' => $this->flash_obj,
      'states' => [0=>'Choose'] + $states_a,
      'countries' => $countries_a,
      'client_business_state' => $client_business_state,
      'client_locations' => array(''=>'Choose') + $client_locations,      
    );
      
    # build variables
    $controller_vars = array(
      'page_title' => 'Business Users',
      'icon_name' => 'fa fa-user-circle-o',
    );

    # render template
    return array($this->template->render_view('bu-create', $template_vars), $controller_vars);
  }

  # bu update action
  public function buUpdateAction(Request $request) {
    $form_errors = $submitted_data = $customer_details = [];
    $page_error = $page_success = $user_code = '';

    $redirect_url = '/bu/list';

    $states_a = Constants::$LOCATION_STATES;
    asort($states_a);
    $countries_a = Constants::$LOCATION_COUNTRIES;

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    $client_details = Utilities::get_client_details();
    $client_business_state = $client_details['locState'];    

    if( count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $user_code = $request->get('userCode');
      $new_user = $this->bu_api_call->update_business_user($submitted_data,$user_code);
      $status = $new_user['status'];
      if($status === false) {
        if(isset($new_user['errors'])) {
          $errors = $new_user['errors'];
        } elseif(isset($new_user['apierror'])) {
          $page_error = $new_user['apierror'];
        }
        $submitted_data = $submitted_data;
        $this->flash_obj->set_flash_message($page_error,1);           
      } else {
        $page_success = 'User information updated successfully';
        $this->flash_obj->set_flash_message($page_success);     
        Utilities::redirect($redirect_url);        
      }
    } elseif($request->get('userCode') && $request->get('userCode') !== '') {
      $user_code = Utilities::clean_string($request->get('userCode'));
      $api_response = $this->bu_api_call->get_business_user_details($user_code);
      if($api_response['status']) {
        $submitted_data = $api_response['userDetails'];
      } else {
        $page_error = $api_response['apierror'];
        $this->flash_obj->set_flash_message($page_error,1);
        Utilities::redirect($redirect_url);
      }
    } else {
      $this->flash->set_flash_message("Invalid User Code", 1);
      Utilities::redirect($redirect_url);
    }

    # prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'submitted_data' => $submitted_data,
      'errors' => $form_errors,
      'bu_types' => [''=>'Choose'] + Utilities::get_business_user_types(),
      'flash_obj' => $this->flash_obj,
      'states' => [0=>'Choose'] + $states_a,
      'countries' => $countries_a,
      'client_business_state' => $client_business_state,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,      
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Business Users',
      'icon_name' => 'fa fa-user-circle-o',
    );

    # render template
    return array($this->template->render_view('bu-update', $template_vars), $controller_vars);
  }

  # bu list action
  public function buListAction(Request $request) {

    $users_list = $users = $search_params = [];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations();

    $states_a = Constants::$LOCATION_STATES;
    asort($states_a); 

    $user_type = !is_null($request->get('userType')) && is_numeric($request->get('userType')) ? $request->get('userType') : '';
    $user_name = !is_null($request->get('userName')) && $request->get('userName') !== '' ? $request->get('userName') : '';
    $state_id =  !is_null($request->get('stateID')) && is_numeric($request->get('stateID')) ? $request->get('stateID') : '';
    $page_no = !is_null($request->get('pageNo')) && is_numeric($request->get('pageNo')) ? $request->get('pageNo') : 1;
    $per_page = !is_null($request->get('perPage')) && is_numeric($request->get('perPage')) ? $request->get('perPage') : 100;
    $location_code = !is_null($request->get('locationCode')) && ctype_alnum($request->get('locationCode')) ? $request->get('locationCode') : '';

    $search_params = [
      'pageNo' => $page_no,
      'perPage' => $per_page,
      'userType' => $user_type,
      'userName' => $user_name,
      'stateID' => $state_id,
      'locationCode' => $location_code,
    ];

    $users_list = $this->bu_api_call->get_business_users($search_params);
    $api_status = $users_list['status'];

    // check api status
    if($api_status) {
      # check whether we got products or not.
      if(count($users_list['users']) >0) {
        $slno = Utilities::get_slno_start(count($users_list['users']),$per_page,$page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;

        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;            
        }

        if($users_list['total_pages']<$page_links_to_end) {
          $page_links_to_end = $users_list['total_pages'];
        }

        if($users_list['record_count'] < $per_page) {
          $to_sl_no = ($slno+$users_list['record_count'])-1;
        }

        $users = $users_list['users'];
        $total_pages = $users_list['total_pages'];
        $total_records = $users_list['total_records'];
        $record_count = $users_list['record_count'];
      } else {
        $page_error = $users_list['apierror'];
      }
    } else {
      $page_error = $users_list['apierror'];
    }

    // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'users' => $users,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'bu_types' => [''=>'All user types'] + Utilities::get_business_user_types(),
      'states' => [0=>'All States'] + $states_a,
      'client_locations' => array(''=>'All Stores') + $client_locations,         
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Business Users',
      'icon_name' => 'fa fa-user-circle-o',
    );

    # render template
    return array($this->template->render_view('bu-list', $template_vars), $controller_vars);       
  }

  private function _validate_form_data($form_data=[]) {
    $form_errors = $cleaned_params = [];

    $user_types = array_keys(Utilities::get_business_user_types());
    $user_type = Utilities::clean_string($form_data['userType']);
    $user_name = Utilities::clean_string($form_data['userName']);
    $mobile_no = Utilities::clean_string($form_data['mobileNo']);
    $country_id = Utilities::clean_string($form_data['countryID']);
    $state_id = Utilities::clean_string($form_data['stateID']);
    $city_name = Utilities::clean_string($form_data['cityName']);
    $address = Utilities::clean_string($form_data['address']);
    $pincode = Utilities::clean_string($form_data['pincode']);
    $phone = Utilities::clean_string($form_data['phone']);
    $gst_no = Utilities::clean_string($form_data['gstNo']);

    if(ctype_alnum(str_replace([' ', '.'], ['',''], $user_name)) ) {
      $cleaned_params['userName'] = $user_name;
    } else {
      $form_errors['userName'] = 'Invalid user name. Only space and dot allowed.';
    }
    if($mobile_no !== '') {
      if(strlen($mobile_no) === 10 && is_numeric($mobile_no)) {
        $cleaned_params['mobileNo'] = $mobile_no;
      } else {
        $form_errors['mobileNo'] = 'Invalid mobile no.';        
      }
    } else {
      $cleaned_params['mobileNo'] = '';
    }
    if($state_id > 0 && $state_id <= 99) {
      $cleaned_params['stateID'] = $state_id;
    } else {
      $cleaned_params['stateID'] = 0;
    }
    if($city_name !== '' && !ctype_alnum(str_replace([' '], [''], $city_name)) ) {
      $form_errors['cityName'] = 'Invalid city name. Only alphabets, digits and space is allowed.';
    } else {
      $cleaned_params['cityName'] = $city_name;
    }
    if($pincode !== '' && !is_numeric($pincode) ) {
      $form_errors['pincode'] = 'Invalid pincode.';
    } else {
      $cleaned_params['pincode'] = $pincode;
    }
    if($phone !== '' && !ctype_alnum(str_replace([',', '-', ','], ['','',''], $phone))) {
      $form_errors['phone'] = 'Invalid phone. Only comma, hyphen and space is allowed';
    } else {
      $cleaned_params['phone'] = $phone;      
    }
    if($gst_no !== '' && !Utilities::validate_gst_no($gst_no)) {
      $form_errors['gstNo'] = 'Invalid GST No.';
    } else {
      $cleaned_params['gstNo'] = $gst_no;
    }
    if(is_numeric($user_type) && in_array($user_type, $user_types)) {
      $cleaned_params['userType'] = $user_type;
    } else {
      $form_errors['userType'] = 'Invalid user type.';
    }
    if($form_data['locationCode'] !== '') {
      if(ctype_alnum($form_data['locationCode'])) {
        $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
      } else {
        $form_errors['locationCode'] = 'Invalid location code.';
      }
    }

    $cleaned_params['countryID'] = $country_id;
    $cleaned_params['address'] = $address;

    if(count($form_errors)>0) {
      return [
        'status' => false,
        'errors' => $form_errors,
      ];
    } else {
      return [
        'status' => true,
        'cleaned_params' => $cleaned_params,
      ];
    }
  }
}