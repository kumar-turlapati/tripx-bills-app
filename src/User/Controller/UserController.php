<?php 

namespace User\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Flash;
use Atawa\Template;

use User\Model\User;

class UserController {
  protected $views_path;

  public function __construct() {
    $this->views_path = __DIR__.'/../Views/';
  }

  public function createUserAction(Request $request) {

    $user_details = $submitted_data = $form_errors = array();
    $client_locations = [];

    $user_model = new User();
    $flash = new Flash();

    # get client locations
    $client_locations_resp = $user_model->get_client_locations();
    if($client_locations_resp['status']) {
      foreach($client_locations_resp['clientLocations'] as $loc_details) {
        $client_locations[$loc_details['locationCode']] = $loc_details['locationName'];
      }
    }

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all(),false,$user_model);
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $user_model->create_user($form_data);
        if($result['status']===true) {
          $message = 'User details were created successfully with uid `'.$result['uuid'].'`';
          $flash->set_flash_message($message);
          Utilities::redirect('/users/list');
        } else {
          $message = $result['apierror'];
          $flash->set_flash_message($message,1);
          Utilities::redirect('/users/list');          
        }
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $request->request->all();
      }
    }

    // prepare form variables.
    $template_vars = array(
      'submitted_data' => $submitted_data,
      'user_types' => array(''=>'Choose')+Utilities::get_user_types(),
      'status_a' => array(''=>'Choose')+Utilities::get_user_status(),
      'client_locations' => array(''=>'Choose') + $client_locations,
      'form_errors' => $form_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'User management',
      'icon_name' => 'fa fa-users',      
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('user-create',$template_vars),$controller_vars);
  }

  public function updateUserAction(Request $request) {

    $user_details = $submitted_data = $form_errors = array();
    $uuid = $page_error = $page_success = '';
    $client_locations = [];

    $user_model = new User();
    $flash = new Flash();

    # get client locations
    $client_locations_resp = $user_model->get_client_locations();
    if($client_locations_resp['status']) {
      foreach($client_locations_resp['clientLocations'] as $loc_details) {
        $client_locations[$loc_details['locationCode']] = $loc_details['locationName'];
      }
    }    

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all(),true,$user_model);
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $user_model->update_user($form_data,$form_data['uuid']);
        if($result['status']) {
          $message = 'User details were updated successfully';
          $flash->set_flash_message($message);
          Utilities::redirect('/users/list');
        } elseif($result['status']===false) {
          $page_error = $result['apierror'];
          $submitted_data = $request->request->all();
          $submitted_data['email'] = $request->get('hEmail');
        } else {
          $message = 'An error occurred while updating user details.';
          $flash->set_flash_message($message,1);
          Utilities::redirect('/users/list');          
        }
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $request->request->all();
      }
    } else {
      $uuid = Utilities::clean_string($request->get('uuid'));
      $user_details = $user_model->get_user_details($uuid);
      if($user_details['status']) {
        $submitted_data = $user_details['userDetails'];
      } else {
        $flash->set_flash_message('Invalid user details. Please contact administrator.',1);
        Utilities::redirect('/users/list');        
      }
    }

    // prepare form variables.
    $template_vars = array(
      'submitted_data' => $submitted_data,
      'uuid' => $uuid,
      'user_types' => array(''=>'Choose')+Utilities::get_user_types(),
      'status_a' => array(''=>'Choose')+Utilities::get_user_status(),
      'form_errors' => $form_errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'client_locations' => array(''=>'Choose') + $client_locations,      
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'User management',
      'icon_name' => 'fa fa-users',      
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('user-update',$template_vars),$controller_vars);    
  }

  public function deleteUserAction(Request $request) {
    $user_details = [];
    $uuid = $page_error = $page_success = '';
    $user_model = new User();
    $flash = new Flash();

    $uuid = Utilities::clean_string($request->get('uuid'));
    $user_details = $user_model->get_user_details($uuid);
    if(!$user_details['status']) {
      $flash->set_flash_message('Invalid user details. Please contact administrator.',1);
      Utilities::redirect('/users/list');        
    } else {
      $user_name = $user_details['userDetails']['userName'];
    }

    $delete_response = $user_model->delete_user($uuid);

    /* delete user */
    if($delete_response['status']) {
      $flash->set_flash_message('<i class="fa fa-check aria-hidden="true"></i>&nbsp;User [ '.$user_name.' ] deleted successfully!');
    } else {
      $flash->set_flash_message('<i class="fa fa-times aria-hidden="true"></i>&nbsp;'.$user_details['apierror'], 1);
    }

    Utilities::redirect('/users/list');        
  }

  public function updateUserActionApp(Request $request) {
    $user_details = $submitted_data = $form_errors = array();
    $uuid = $page_error = $page_success = '';
    $client_locations = [];

    $user_model = new User();
    $flash = new Flash();

    # get client locations
    $client_locations_resp = $user_model->get_client_locations();
    if($client_locations_resp['status']) {
      foreach($client_locations_resp['clientLocations'] as $loc_details) {
        $client_locations[$loc_details['locationCode']] = $loc_details['locationName'];
      }
    }    

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all(),true,$user_model);
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $user_model->update_user($form_data,$form_data['uuid']);
        if($result['status']) {
          $message = '<i class="fa fa-check aria-hidden="true"></i>&nbsp;User details were updated successfully';
          $flash->set_flash_message($message);
          Utilities::redirect('/users/app');
        } elseif($result['status']===false) {
          $page_error = $result['apierror'];
          $submitted_data = $request->request->all();
          $submitted_data['email'] = $request->get('hEmail');
        } else {
          $message = '<i class="fa fa-exclamation" aria-hidden="true"></i> An error occurred while updating user details.';
          $flash->set_flash_message($message,1);
          Utilities::redirect('/users/app');          
        }
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $request->request->all();
      }
    } else {
      $uuid = Utilities::clean_string($request->get('uuid'));
      $user_details = $user_model->get_user_details($uuid);
      if($user_details['status']) {
        $submitted_data = $user_details['userDetails'];
      } else {
        $flash->set_flash_message('<i class="fa fa-exclamation" aria-hidden="true"></i>Invalid user details. Please contact administrator.',1);
        Utilities::redirect('/users/app');        
      }
    }

    // prepare form variables.
    $template_vars = array(
      'submitted_data' => $submitted_data,
      'uuid' => $uuid,
      'user_types' => array(''=>'Choose')+Utilities::get_user_types(),
      'status_a' => array(''=>'Choose')+Utilities::get_user_status(),
      'form_errors' => $form_errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'client_locations' => array(''=>'Choose') + $client_locations,      
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'User management',
      'icon_name' => 'fa fa-users',      
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('user-update-app',$template_vars),$controller_vars);
  }

  public function listUsersAction(Request $request) {

    $users = [];
    $flash = new Flash();
    $user_model = new User();

    // parse request parameters.
    $mobile_no = $request->get('mobileNo')!==null ? Utilities::clean_string($request->get('mobileNo')) : '';
    $user_type = $request->get('userType')!==null ? Utilities::clean_string($request->get('userType')) : '';
    $status = $request->get('status') !== null ? Utilities::clean_string($request->get('status')) : '-1';
    $page_no = $request->get('pageNo')!==null ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = 500;

    $search_params = array(
      'mobileNo' => $mobile_no,
      'userType' => $user_type,
      'status' => $status,
      'pageNo' => $page_no,
      'perPage' => $per_page,
    );

    $result = $user_model->get_users($search_params);

    if($result['status']) {
      $users = $result['users'];
    } else {
      $message = $result['apierror'];
      $flash->set_flash_message($message,1);      
    }

    // prepare form variables.
    $template_vars = array(
      'users' => $users,
      'user_types' => ['' => 'All User Types'] + Utilities::get_user_types(),
      'user_status' => ['-1' => 'All statuses', 1 => 'Active', 0 => 'Inactive'],
      'search_params' => $search_params,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Platform Users',
      'icon_name' => 'fa fa-users',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('users-list',$template_vars),$controller_vars);
  }

  public function listOnlineUsersAction(Request $request) {
    $users = [];
    $flash = new Flash();

    $client_locations = $location_key_a = $location_ids = $location_codes = [];

    // ---------- get location codes from api ------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    $user_model = new User();
    $result = $user_model->get_online_users();

    if($result['status']) {
      $users = $result['users'];
    } else {
      $message = $result['apierror'];
      $flash->set_flash_message($message,1);      
    }

    // prepare form variables.
    $template_vars = array(
      'users' => $users,
      'client_locations' => ['' => 'All Stores'] + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,      
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Live Users',
      'icon_name' => 'fa fa-wifi',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('users-online-list',$template_vars),$controller_vars);
  }

  public function editProfileAction(Request $request) {
    $user_details = $submitted_data = $form_errors = array();
    $page_error = $page_success = '';

    $user_model = new User();
    $flash = new Flash();
    
    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data_self_update($request->request->all(),false,$user_model);
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $user_model->update_user_profile($form_data);
        if($result['status']===true) {
          $message = 'Profile updated successfully. New Password will be updated after logout from current session.';
          $flash->set_flash_message($message);
          Utilities::redirect('/me');
        } else {
          $message = 'An error occurred while updating profile.';
          $flash->set_flash_message($message,1);
          Utilities::redirect('/me');
        }
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $request->request->all();
      }
    } else {
      $uuid = $_SESSION['uid'];
      $user_details = $user_model->get_user_details($uuid);
      // dump($user_details);
      // exit;
      if($user_details['status']) {
        $submitted_data = $user_details['userDetails'];
      } else {
        $flash->set_flash_message('Invalid user details. Please contact administrator.',1);
        Utilities::redirect('/dashboard');        
      }      
    }

    // prepare form variables.
    $template_vars = array(
      'submitted_data' => $submitted_data,
      'form_errors' => $form_errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'User management',
      'icon_name' => 'fa fa-users',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('me',$template_vars),$controller_vars);    
  }

  public function createAppUserAction(Request $request) {

    $user_details = $submitted_data = $form_errors = array();
    $client_locations = [];

    $user_model = new User();
    $flash = new Flash();

    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $validate_form = $this->_validate_form_data_app_user($submitted_data,false,$user_model);
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $user_model->create_user($form_data);
        if($result['status']) {
          $message = '<i class="fa fa-check" aria-hidden="true"></i>&nbsp;User created successfully with uid `'.$result['uuid'].'`';
          $flash->set_flash_message($message);
          Utilities::redirect('/app-user/create');
        } else {
          $message = '<i class="fa fa-times" aria-hidden="true"></i>&nbsp;'.$result['apierror'];
          $flash->set_flash_message($message,1);
        }
      } else {
        $form_errors = $validate_form['errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'submitted_data' => $submitted_data,
      'user_types' => array(''=>'Choose')+Utilities::get_user_types(),
      'status_a' => array(''=>'Choose')+Utilities::get_user_status(),
      'form_errors' => $form_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'User management',
      'icon_name' => 'fa fa-users',      
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('user-create-app',$template_vars),$controller_vars);
  }

  public function listAppUsersAction(Request $request) {

    $users = [];
    $flash = new Flash();

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0; 

    $page_no = !is_null($request->get('pageNo')) ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = !is_null($request->get('perPage')) ? Utilities::clean_string($request->get('perPage')) : 100;
    $customer_name = !is_null($request->get('customerName')) ? Utilities::clean_string($request->get('customerName')) : '';
    $from_date = !is_null($request->get('fromDate')) ? Utilities::clean_string($request->get('fromDate')) : '';
    $to_date = !is_null($request->get('toDate')) ? Utilities::clean_string($request->get('toDate')) : '';
    $mobile_no = !is_null($request->get('mobileNo')) ? Utilities::clean_string($request->get('mobileNo')) : '';
    $status = !is_null($request->get('status')) ? Utilities::clean_string($request->get('status')) : '';

    $search_params = [
      'customerName' => $customer_name,
      'mobileNo' => $mobile_no,
      'fromDate' => $from_date,
      'toDate' => $to_date,
      'pageNo' => $page_no,
      'perPage' => $per_page,
      'appUsers' => true,
      'status' => $status,
    ];

    // dump($search_params);

    $user_model = new User();
    $result = $user_model->get_users($search_params, true);
    if($result['status']) {
      $users = $result['users']['users'];
      // check whether we got products or not.
      if(count($users) >0) {
        $slno = Utilities::get_slno_start(count($users), $per_page, $page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;            
        }
        if($result['users']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $result['users']['total_pages'];
        }
        if(count($result['users']['users']) < $per_page) {
          $to_sl_no = ($slno+count($result['users']['users']))-1;
        }
        $users = $result['users']['users'];
        $total_pages = $result['users']['total_pages'];
        $total_records = $result['users']['total_records'];
        $record_count = $result['users']['total_records'];
      } else {
        $page_error = $result['apierror'];
      }
    } else {
      $message = $result['apierror'];
      $flash->set_flash_message($message,1);      
    }

    // prepare form variables.
    $template_vars = array(
      'users' => $users,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
      'page_no' => $page_no, 
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'App Users',
      'icon_name' => 'fa fa-mobile',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('users-app-list',$template_vars),$controller_vars);
  }  

  private function _validate_form_data($form_data=array(),$edit_mode=false,$user_model) {
    $errors = $cleaned_params = array();

    $user_name = Utilities::clean_string($form_data['userName']);
    $user_type = Utilities::clean_string($form_data['userType']);
    $user_phone = Utilities::clean_string($form_data['userPhone']);
    $status = Utilities::clean_string($form_data['status']);
    $location_code = Utilities::clean_string($form_data['locationCode']);
    $app_user = isset($form_data['appUser']) ? (int)Utilities::clean_string($form_data['appUser']) : 0;

    if(!$app_user) {
      if($user_name == '') {
        $errors['userName'] = 'User name is required.';
      } else {
        $cleaned_params['userName'] = $user_name;
      }
      if($user_type === '') {
        $errors['userType'] = 'Invalid user type.';
      } else {
        $cleaned_params['userType'] = $user_type;      
      }
    }

    if(!is_numeric($user_phone) && strlen($user_phone)!==10) {
      $errors['userPhone'] = 'Mobile number should contain digits.';
    } else {
      if($app_user) {
        $cleaned_params['mobileNo'] = $user_phone;
      } else {
        $cleaned_params['userPhone'] = $user_phone;
      }
    }

    if(!is_numeric($status)) {
      $errors['status'] = 'Invalid status';
    } else {
      $cleaned_params['status'] = $status;
    }
    if($location_code !== '' && ctype_alnum($location_code)) {
      $cleaned_params['locationCode'] = $location_code;
    }
    if($edit_mode) {
      $uuid = Utilities::clean_string($form_data['uuid']);
      $user_details = $user_model->get_user_details($uuid);
      if($user_details['status']===false) {
        $errors['userCode'] = 'Invalid user information.';
      } else {
        $cleaned_params['uuid'] = $uuid;
      }
    } else {
      $cleaned_params['emailID'] = Utilities::clean_string($form_data['emailID']);
    }
    $cleaned_params['userPass'] = Utilities::clean_string($form_data['userPass']);
    $cleaned_params['appUser'] = $app_user;

    // dump($errors);
    // exit;

    if(count($errors)>0) {
      return array('status'=>false, 'errors'=>$errors);
    } else {
      return array('status'=>true, 'cleaned_params'=>$cleaned_params);
    }
  }

  private function _validate_form_data_app_user($form_data=[],$edit_mode=false,$user_model) {
    $errors = $cleaned_params = array();

    $mobile_no = Utilities::clean_string($form_data['mobileNo']);
    $status = Utilities::clean_string($form_data['status']);
    
    $cleaned_params['userType'] = 299;
    $cleaned_params['isAppUser'] = 1;
    if(!is_numeric($mobile_no) && strlen($mobile_no)!==10) {
      $errors['mobileNo'] = 'Mobile number should contain digits.';
    } else {
      $cleaned_params['mobileNo'] = Utilities::clean_string($form_data['mobileNo']);
    }
    if(!is_numeric($status)) {
      $errors['status'] = 'Invalid status';
    } else {
      $cleaned_params['status'] = $status;
    }

    if(count($errors)>0) {
      return array('status'=>false, 'errors'=>$errors);
    } else {
      return array('status'=>true, 'cleaned_params'=>$cleaned_params);
    }
  }  

  private function _validate_form_data_self_update($form_data=array()) {
    $errors = $cleaned_params = array();

    $user_name = Utilities::clean_string($form_data['userName']);
    $user_phone = Utilities::clean_string($form_data['userPhone']);
    $password = Utilities::clean_string($form_data['password']);

    if($user_name == '') {
      $errors['userName'] = 'User name is required.';
    } else {
      $cleaned_params['userName'] = $user_name;
    }
    if(!is_numeric($user_phone) && strlen($user_phone)!==10) {
      $errors['userPhone'] = 'Mobile number should contain digits.';
    } else {
      $cleaned_params['userPhone'] = $user_phone;
    }
    if($password !== '') {
      $cleaned_params['password'] = $form_data['password'];
    }

    if(count($errors)>0) {
      return array('status'=>false, 'errors'=>$errors);
    } else {
      return array('status'=>true, 'cleaned_params'=>$cleaned_params);
    }
  }  

}