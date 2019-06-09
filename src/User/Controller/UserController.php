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
        if($result['status']===true) {
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

  public function listUsersAction(Request $request) {

    $users = array();
    $flash = new Flash();

    $user_model = new User();
    $result = $user_model->get_users();

    if($result['status']) {
      $users = $result['users'];
    } else {
      $message = $result['apierror'];
      $flash->set_flash_message($message,1);      
    }

    // prepare form variables.
    $template_vars = array(
      'users' => $users,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'User management',
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
      'page_title' => 'Users Online',
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

  /*********************************** validate form data ********************************/

  private function _validate_form_data($form_data=array(),$edit_mode=false,$user_model) {
    $errors = $cleaned_params = array();

    $user_name = Utilities::clean_string($form_data['userName']);
    $user_type = Utilities::clean_string($form_data['userType']);
    $user_phone = Utilities::clean_string($form_data['userPhone']);
    $status = Utilities::clean_string($form_data['status']);
    $location_code = Utilities::clean_string($form_data['locationCode']);

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
    if(!is_numeric($user_phone) && strlen($user_phone)!==10) {
      $errors['userPhone'] = 'Mobile number should contain digits.';
    } else {
      $cleaned_params['userPhone'] = $user_phone;
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