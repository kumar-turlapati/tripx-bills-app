<?php 

namespace ClothingRm\Location\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;
use Atawa\Constants;

use ClothingRm\Location\Model\Location;

class LocationController
{
	protected $views_path,$flash,$tax_model;

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash();
    $this->location_model = new Location();
	}

  public function addLocation(Request $request) {

    $submitted_data = $form_errors = [];
    $mrp_editing_a = [0=>'No', 1=>'Yes'];

    $states_a = Constants::$LOCATION_STATES;
    asort($states_a);
    $countries_a = Constants::$LOCATION_COUNTRIES;    
    $client_details = Utilities::get_client_details();
    $client_business_state = $client_details['locState'];

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->location_model->create_client_location($cleaned_params);
        if($result['status']) {
          $this->flash->set_flash_message('Store created successfully.');
          Utilities::redirect('/location/create');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'submitted_data' => $submitted_data,
      'form_errors' => $form_errors,
      'states' => [0=>'Choose'] + $states_a,
      'countries' => [0=>'Choose'] + $countries_a,
      'client_business_state' => $client_business_state,
      'mrp_editing_a' => $mrp_editing_a,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Store Locations',
      'icon_name' => 'fa fa-location-arrow',
    );

    // render template
    return array($this->template->render_view('create-location',$template_vars),$controller_vars);
  }

  public function updateLocation(Request $request) {

    $submitted_data = $form_errors = [];
    $sel_location_code = '';
    $mrp_editing_a = [0=>'No', 1=>'Yes'];

    $states_a = Constants::$LOCATION_STATES;
    asort($states_a);
    $countries_a = Constants::$LOCATION_COUNTRIES;    

    $client_details = Utilities::get_client_details();
    $client_business_state = $client_details['locState'];

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      # check location code and get location details.
      if(!is_null($request->get('locationCode')) && $request->get('locationCode') !== $submitted_data['locationCode']) {
        $this->flash->set_flash_message('Invalid location code.', 1);
        Utilities::redirect('/locations/list');
      } else {
        $location_code = $sel_location_code = Utilities::clean_string($submitted_data['locationCode']);
      }
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->location_model->update_client_location($location_code, $cleaned_params);
        if($result['status']) {
          $this->flash->set_flash_message('Store with code `'.$location_code.'` updated successfully.');
          Utilities::redirect('/locations/list');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
      } else {
        $form_errors = $form_validation['errors'];
      }
    } elseif(!is_null($request->get('locationCode'))) {
      $location_code = $request->get('locationCode');
      $location_details = $this->location_model->get_client_location_details($location_code);
      $sel_location_code = $location_code;
      if($location_details['status']===false) {
        $this->flash->set_flash_message('Invalid location (or) location does not exists',1);         
        Utilities::redirect('/locations/list');
      } else {
        $submitted_data = $location_details['locationDetails'];
      }
    } else {
      $this->flash->set_flash_message('Invalid member code',1);         
      Utilities::redirect('/loyalty-members/list');
    }

    // prepare form variables.
    $template_vars = array(
      'submitted_data' => $submitted_data,
      'form_errors' => $form_errors,
      'states' => [0=>'Choose'] + $states_a,
      'countries' => [0=>'Choose'] + $countries_a,
      'client_business_state' => $client_business_state,  
      'sel_location_code' => $sel_location_code,
      'mrp_editing_a' => $mrp_editing_a,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Store Locations',
      'icon_name' => 'fa fa-location-arrow',
    );

    // render template
    return array($this->template->render_view('update-location',$template_vars),$controller_vars);
  }

  public function listLocations(Request $request) {
    $locations_a = []; $page_error = '';
    
    $api_response = $this->location_model->get_client_locations([]);
    if($api_response['status'] && count($api_response['clientLocations']) > 0) {
      $locations_a = $api_response['clientLocations'];
    } else {
      $page_error = $api_response['apierror'];
    }

    // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'locations' => $locations_a,
      'states_a' => Constants::$LOCATION_STATES,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Stores List',
      'icon_name' => 'fa fa-location-arrow',
    );

    // render template
    return array($this->template->render_view('locations-list',$template_vars),$controller_vars);
  }

  private function _validate_form_data($form_data=[]) {
    $cleaned_params = $errors = [];

    $location_name = Utilities::clean_string($form_data['locationName']);
    $location_name_short = Utilities::clean_string($form_data['locationNameShort']);
    $address1 = Utilities::clean_string($form_data['address1']);
    $address2 = Utilities::clean_string($form_data['address2']);
    $country_id = (int)Utilities::clean_string($form_data['countryID']);
    $state_id = (int)Utilities::clean_string($form_data['stateID']);
    $city_name = Utilities::clean_string($form_data['cityName']);
    $pincode = Utilities::clean_string($form_data['pincode']);
    $phone = Utilities::clean_string($form_data['phone']);
    $gst_no = Utilities::clean_string($form_data['locGstNo']);

    $sms_sender_id = Utilities::clean_string($form_data['smsSenderID']);
    $sms_company_name = Utilities::clean_string($form_data['smsCompanyShortName']);
    $allow_mrp_editing = (int)Utilities::clean_string($form_data['mrpEditing']);

    if($sms_sender_id !== '') {
      if(strlen($sms_sender_id) === 6 && ctype_alpha($sms_sender_id)) {
        $cleaned_params['smsSenderID'] = $sms_sender_id;
      } else {
        $errors['smsSenderID'] = 'Invalid Sender ID.';
      }
    }
    
    if(strlen($sms_company_name) <= 20) {
      $cleaned_params['smsCompanyShortName'] = $sms_company_name;
    } else {
      $errors['smsCompanyShortName'] = 'Invalid Company Short Name.';
    }

    if(ctype_alnum(str_replace([' ', "'"], ['', ''], $location_name))) {
      $cleaned_params['locationName'] = $location_name;
    } else {
      $errors['locationName'] = 'Invalid location name. Only digits, alphabets, and single quote allowed.';
    }
    if(ctype_alnum(str_replace([' ', "'"], ['', ''], $location_name_short))) {
      $cleaned_params['locationNameShort'] = $location_name_short;
    } else {
      $errors['locationNameShort'] = 'Invalid location short name.';
    }    
    if($address1 !== '' && ctype_alnum(str_replace([' ', '-', '#', ',', '/'], ['','','','',''], $address1))) {
      $cleaned_params['address1'] = $address1;
    } else {
      $errors['address1'] = 'Only alphabets, numbers, space, -, #, / and comma symbols are allowed.';
    }
    if($address2 !== '' && ctype_alnum(str_replace([' ', '-', '#', ',', '/'], ['','','','',''], $address2))) {
      $cleaned_params['address2'] = $address2;
    } else {
      $errors['address2'] = 'Only alphabets, numbers, space, -, #, / and comma symbols are allowed.';
    }
    if($country_id !== '' && $country_id>0) {
      $cleaned_params['countryID'] = $country_id;
    } else {
      $errors['countryID'] = 'Invalid country name.';
    }
    if($state_id !== '' && $state_id>0) {
      $cleaned_params['stateID'] = $state_id;
    } else {
      $errors['stateID'] = 'Invalid state name.';
    }
    if(ctype_alnum(str_replace(' ', '', $city_name))) {
      $cleaned_params['cityName'] = $city_name;
    } else {
      $errors['cityName'] = 'Invalid city name.';
    }
    if($pincode !== '' && is_numeric((int)$pincode)) {
      $cleaned_params['pincode'] = $pincode;
    } else {
      $errors['pincode'] = 'Invalid pincode.';
    }
    if($phone !== '') {
      if(ctype_alnum(str_replace([' ', '-', ','], ['','',''], $phone))) {
        $cleaned_params['phone'] = $phone;
      } else {
        $errors['phone'] = 'Invalid phone. Only numbers, space, - and comma symbols are allowed.';
      }
    }
    if($gst_no !== '') {
      if(!Utilities::validate_gst_no($gst_no)) {
        $errors['locGstNo'] = 'Invalid GST Number.';      
      } else {
        $cleaned_params['locGstNo'] = $gst_no;
      }
    }
    if($allow_mrp_editing === 0 || $allow_mrp_editing === 1) {
      $cleaned_params['allowMrpEditing'] = $allow_mrp_editing;
    } else {
      $errors['allowMrpEditing'] = 'Invalid MRP Editing.';
    }
    if(count($errors)>0) {
      return array('status' => false, 'errors' => $errors);
    } else {
      return array('status' => true,'cleaned_params' => $cleaned_params);
    }
  }
}