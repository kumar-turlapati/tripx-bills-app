<?php 

namespace ClothingRm\Customers\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Customers\Model\Customers;

class CustomersController
{
	protected $template, $customer_api_call, $flash_obj;

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->customer_api_call = new Customers;
    $this->flash_obj = new Flash;
	}

  public function customerCreateAction(Request $request) {
    $form_errors = $submitted_data = [];
    $page_error = $page_success = $cust_code = '';
    $redirect_url = '/customers/create';

    $states_a = Constants::$LOCATION_STATES;
    asort($states_a);
    $countries_a = Constants::$LOCATION_COUNTRIES;

    $ages_a[0] = 'Choose';
    for($i=1;$i<=150;$i++) {
      $ages_a[$i] = $i;
    }

    $client_details = Utilities::get_client_details();
    $client_business_state = $client_details['locState'];

    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $new_customer = $this->customer_api_call->createCustomer($cleaned_params);
        $status = $new_customer['status'];
        if($status === false) {
          $page_error = $result['apierror'];
          $this->flash_obj->set_flash_message($page_error, 1);
          Utilities::redirect($redirect_url);
        } else {
          $page_success = 'Customer information added successfully with code `'.$new_customer['customerCode'].'`';
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
      'genders' => [''=>'Choose'] + Constants::$GENDERS,
      'ages' => $ages_a,
      'age_categories' => Constants::$AGE_CATEGORIES,
      'customer_types' => ['c' => 'Retail Customer', 'b' => 'Business'],
      'flash_obj' => $this->flash_obj,
      'states' => [0=>'Choose'] + $states_a,
      'countries' => [0=>'Choose'] + $countries_a,
      'client_business_state' => $client_business_state,
    );
      
    # build variables
    $controller_vars = array(
      'page_title' => 'Customers',
      'icon_name' => 'fa fa-smile-o',
    );

    # render template
    return array($this->template->render_view('customer-create', $template_vars), $controller_vars);
  }

  public function customerUpdateAction(Request $request) {
    $form_errors = $submitted_data = $customer_details = [];
    $page_error = $page_success = $cust_code = '';

    $redirect_url = '/customers/list';

    $states_a = Constants::$LOCATION_STATES;
    asort($states_a);
    $countries_a = Constants::$LOCATION_COUNTRIES;    

    $ages_a[0] = 'Choose';
    for($i=1;$i<=150;$i++) {
        $ages_a[$i] = $i;
    }

    $client_details = Utilities::get_client_details();
    $client_business_state = $client_details['locState'];    

    if( count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $customer_code = $request->get('customerCode');
      $new_customer = $this->customer_api_call->updateCustomer($submitted_data,$customer_code);
      $status = $new_customer['status'];
      if($status === false) {
        if(isset($new_customer['errors'])) {
          $errors     =   $new_customer['errors'];
        } elseif(isset($new_customer['apierror'])) {
          $page_error =   $new_customer['apierror'];
        }
        $submitted_data = $submitted_data;
        $this->flash_obj->set_flash_message($page_error,1);           
      } else {
        $page_success = 'Customer information updated successfully';
        $this->flash_obj->set_flash_message($page_success);     
        Utilities::redirect($redirect_url);        
      }
    } elseif($request->get('customerCode') && $request->get('customerCode') !== '') {
      $customer_code = Utilities::clean_string($request->get('customerCode'));
      $api_response = $this->customer_api_call->get_customer_details($customer_code);
      if($api_response['status']) {
        $submitted_data = $api_response['customerDetails'];
      } else {
        $page_error = $api_response['apierror'];
        $this->flash_obj->set_flash_message($page_error,1);
        Utilities::redirect($redirect_url);
      }
    } else {
      $this->flash->set_flash_message("Invalid Customer Code",1);
      Utilities::redirect($redirect_url);
    }

    # prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'submitted_data' => $submitted_data,
      'errors' => $form_errors,
      'genders' => [''=>'Choose'] + Constants::$GENDERS,
      'ages' => $ages_a,
      'age_categories' => Constants::$AGE_CATEGORIES,
      'customer_types' => ['c' => 'Retail Customer', 'b' => 'Business'],
      'flash_obj' => $this->flash_obj,
      'states' => [0=>'Choose'] + $states_a,
      'countries' => [0=>'Choose'] + $countries_a,
      'client_business_state' => $client_business_state,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Customers',
      'icon_name' => 'fa fa-smile-o',
    );

    # render template
    return array($this->template->render_view('customer-update', $template_vars), $controller_vars);
  }

  public function customerListAction(Request $request) {

    $customers_list = $customers = $search_params = array();

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';

    $customers_model = new Customers;
    $flash_obj = new Flash;

    if( $request->get('pageNo') ) {
      $page_no = $request->get('pageNo');
    } else {
      $page_no = 1;
    }

    if( $request->get('perPage') ) {
      $per_page = $request->get('perPage');
    } else {
      $per_page = 100;
    }

    $customers_list = $customers_model->get_customers($page_no,$per_page,$search_params);
    $api_status = $customers_list['status'];

      # check api status
      if($api_status) {

          # check whether we got products or not.
          if(count($customers_list['customers']) >0) {
              $slno = Utilities::get_slno_start(count($customers_list['customers']),$per_page,$page_no);
              $to_sl_no = $slno+$per_page;
              $slno++;

              if($page_no<=3) {
                  $page_links_to_start = 1;
                  $page_links_to_end = 10;
              } else {
                  $page_links_to_start = $page_no-3;
                  $page_links_to_end = $page_links_to_start+10;            
              }

              if($customers_list['total_pages']<$page_links_to_end) {
                  $page_links_to_end = $customers_list['total_pages'];
              }

              if($customers_list['record_count'] < $per_page) {
                  $to_sl_no = ($slno+$customers_list['record_count'])-1;
              }

              $customers = $customers_list['customers'];
              $total_pages = $customers_list['total_pages'];
              $total_records = $customers_list['total_records'];
              $record_count = $customers_list['record_count'];
          } else {
              $page_error = $customers_list['apierror'];
          }

      } else {
          $page_error = $customers_list['apierror'];
      }

       // prepare form variables.
      $template_vars = array(
          'page_error' => $page_error,
          'page_success' => $page_success,
          'customers' => $customers,
          'total_pages' => $total_pages ,
          'total_records' => $total_records,
          'record_count' =>  $record_count,
          'sl_no' => $slno,
          'to_sl_no' => $to_sl_no,
          'search_params' => $search_params,            
          'page_links_to_start' => $page_links_to_start,
          'page_links_to_end' => $page_links_to_end,
          'current_page' => $page_no,
          'customer_types' => [],
          'genders' => Constants::$GENDERS,
      );

      // build variables
      $controller_vars = array(
        'page_title' => 'Customers',
        'icon_name' => 'fa fa-smile-o',
      );

      # render template
      return array($this->template->render_view('customers-list', $template_vars), $controller_vars);
  }

  # private functions should go from here.
  private function _validate_form_data($form_data=[]) {
    $form_errors = $cleaned_params = [];

    $customer_type = Utilities::clean_string($form_data['customerType']);
    $customer_name = Utilities::clean_string($form_data['customerName']);
    $mobile_no = Utilities::clean_string($form_data['mobileNo']);
    $country_id = Utilities::clean_string($form_data['countryID']);
    $state_id = Utilities::clean_string($form_data['stateID']);
    $city_name = Utilities::clean_string($form_data['cityName']);
    $address = Utilities::clean_string($form_data['address']);
    $pincode = Utilities::clean_string($form_data['pincode']);
    $phone = Utilities::clean_string($form_data['phone']);
    $gst_no = Utilities::clean_string($form_data['gstNo']);
    $age = Utilities::clean_string($form_data['age']);
    $age_category = Utilities::clean_string($form_data['ageCategory']);
    $gender = Utilities::clean_string($form_data['gender']) === '' ? 'o' : Utilities::clean_string($form_data['gender']);
    $dob = Utilities::clean_string($form_data['dob']);
    $dor = Utilities::clean_string($form_data['dor']);

    if($customer_name !== '') {
      $cleaned_params['customerName'] = $customer_name;
    } else {
      $form_errors['customerName'] = 'Invalid customer name.';
    }
    if($mobile_no !== '' && strlen($mobile_no) === 10 && is_numeric($mobile_no)) {
      $cleaned_params['mobileNo'] = $customer_name;
    } else {
      $cleaned_params['mobileNo'] = '';
    }
    if($state_id > 0 && $state_id <= 99) {
      $cleaned_params['stateID'] = $state_id;
    } else {
      $cleaned_params['stateID'] = 0;
    }
    if($city_name !== '' && !ctype_alnum(str_replace([' '], [''], $city_name)) ) {
      $form_errors['cityName'] = 'Invalid city name.';
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
    if($dob !== '' && !Utilities::validateDate($dob)) {
      $form_errors['dob'] = 'Invalid date of birth.';
    } else {
      $cleaned_params['dob'] = $dob;
    }
    if($dor !== '' && !Utilities::validateDate($dor)) {
      $form_errors['dor'] = 'Invalid date of marriage.';
    } else {
      $cleaned_params['dor'] = $dor;
    }    

    $cleaned_params['countryID'] = $country_id;
    $cleaned_params['age'] = is_numeric($age) ? $age : 0;
    $cleaned_params['ageCategory'] = $age_category;
    $cleaned_params['customerType'] = $customer_type;
    $cleaned_params['address'] = $address;
    $cleaned_params['gender'] = $gender;

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