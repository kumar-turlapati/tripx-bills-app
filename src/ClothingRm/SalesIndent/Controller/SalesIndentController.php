<?php 

namespace ClothingRm\SalesIndent\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\SalesIndent\Model\SalesIndent;
use BusinessUsers\Model\BusinessUsers;
use Campaigns\Model\Campaigns;

class SalesIndentController {

  protected $views_path;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash;
    $this->sindent_model = new SalesIndent;
    $this->bu_model = new BusinessUsers;
    $this->camp_model = new Campaigns;
  }

  // create indent
  public function createIndent(Request $request) {

    # -------- initialize variables ---------------------------
    $page_error = $page_success = '';
    $form_errors = $agents_a = $campaigns_a = $form_data = [];
    $executives_a = [];

    # ---------- get location codes from api ------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    # ---------- get default location --------------------------
    if(!is_null($request->get('lc')) && $request->get('lc') !== '') {
      $default_location = Utilities::clean_string($request->get('lc'));
    } elseif(isset($_SESSION['lc']) && $_SESSION['lc'] !== '') {
      $default_location = $_SESSION['lc'];
    } else {
      $default_location = '';
    }

    # ---------- get business users ----------------------------
    $agents_response = $this->bu_model->get_business_users(['userType' => 90]);
    $executives_response = $this->bu_model->get_business_users(['userType' => 91]);
    if($agents_response['status']) {
      foreach($agents_response['users'] as $user_details) {
        if($user_details['cityName'] !== '') {
          $agents_a[$user_details['userCode']] = $user_details['userName'].'__'.substr($user_details['cityName'],0,10);
        } else {
          $agents_a[$user_details['userCode']] = $user_details['userName'];
        }
      }
    }
    if($executives_response['status']) {
      foreach($executives_response['users'] as $user_details) {
        if($user_details['cityName'] !== '') {
          $executives_a[$user_details['userCode']] = $user_details['userName'].'__'.substr($user_details['cityName'],0,10);
        } else {
          $executives_a[$user_details['userCode']] = $user_details['userName'];
        }
      }
    }

    # ---------- get live campaigns ---------------------------------
    $campaigns_response = $this->camp_model->get_live_campaigns();
    if($campaigns_response['status']) {
      $campaign_keys = array_column($campaigns_response['campaigns'], 'campaignCode');
      $campaign_names = array_column($campaigns_response['campaigns'], 'campaignName');
      $campaigns_a = array_combine($campaign_keys, $campaign_names);
    }

    if(!is_null($request->get('lastIndent')) && is_numeric($request->get('lastIndent'))) {
      $last_indent_no = (int)$request->get('lastIndent');
    } else {
      $last_indent_no = false;
    }

    if(!is_null($request->get('it'))) {
      $indent_print_option = $request->get('it');
    } else {
      $indent_print_option = false;
    }

    # ------------------------------------- check for form Submission --------------------------------
    # ------------------------------------------------------------------------------------------------
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      if(isset($form_data['locationCode'])) {
        $default_location = $form_data['locationCode'];
      }
      $form_validation = $this->_validate_form_data($form_data);
      if($form_validation['status']===false) {
        $this->flash->set_flash_message('You have errors in this form. Please fix them before you save', 1);
        $form_errors = $form_validation['errors'];
      } else {
        $api_response = $this->sindent_model->create_sindent($form_data);
        if($api_response['status']) {
          $this->flash->set_flash_message('Sales indent with Indent No. <b>`'.$api_response['indentNo'].'`</b> created successfully.');
          Utilities::redirect('/sales-indent/create?lastIndent='.$api_response['indentNo'].'&lc='.$default_location.'&it='.$form_data['op']);
        } else {
          $page_error = $api_response['apierror'];
          $this->flash->set_flash_message($page_error,1);
        }
      }
    }

    # --------------- build variables -----------------
    $controller_vars = array(
      'page_title' => 'Create Sales Indent',
      'icon_name' => 'fa fa-inr',
    );
    
    # ---------------- prepare form variables. ---------
    $template_vars = array(
      'form_data' => $form_data,
      'errors' => $form_errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'btn_label' => 'Save',
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'default_location' => $default_location,
      'agents' => ['' => 'Choose'] + $agents_a,
      'executives' => ['' => 'Choose'] + $executives_a,
      'campaigns' => ['' => 'Choose'] + $campaigns_a,
      'last_indent_no' => $last_indent_no,
      'indent_print_option' => $indent_print_option,
    );

    return array($this->template->render_view('indent-create', $template_vars),$controller_vars);
  }

  // list indents
  public function listIndents(Request $request) {
    $locations = $vouchers = $search_params = $indents_a = [];
    $location_code = $page_error = '';
    
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];
    }

    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';

    // parse request parameters.
    $per_page = 100;
    $from_date = $request->get('fromDate') !== null ? Utilities::clean_string($request->get('fromDate')):'01-'.date('m').'-'.date("Y");
    $to_date = $request->get('toDate') !== null ? Utilities::clean_string($request->get('toDate')):date("d-m-Y");
    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')):1;
    $location_code = $request->get('locationCode')!== null ? Utilities::clean_string($request->get('locationCode')) : $default_location;

    $search_params = array(
      'fromDate' => $from_date,
      'toDate' => $to_date,
      'locationCode' => $location_code,
      'pageNo' => $page_no,
      'perPage' => $per_page,
    );

    $api_response = $this->sindent_model->get_all_indents($search_params);
    if($api_response['status']) {
      if(count($api_response['response']['indents'])>0) {
        $slno = Utilities::get_slno_start(count($api_response['response']['indents']),$per_page,$page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;        
        }
        if($api_response['response']['total_pages'] < $page_links_to_end) {
          $page_links_to_end = $api_response['response']['total_pages'];
        }
        if($api_response['response']['this_page'] < $per_page) {
          $to_sl_no = ($slno+$api_response['response']['this_page'])-1;
        }
        $indents_a = $api_response['response']['indents'];
        $total_pages = $api_response['response']['total_pages'];
        $total_records = $api_response['response']['total_records'];
        $record_count = $api_response['response']['this_page'];
      } else {
        $page_error = $api_response['apierror'];
      }
    } else {
      $page_error = $api_response['apierror'];
    }

     // prepare form variables.
    $template_vars = array(
      'location_code' => $location_code,
      'page_error' => $page_error,
      'indents' => $indents_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => $default_location,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Sales Indent Register',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('indents-list', $template_vars), $controller_vars);
  }

  // validate form data
  private function _validate_form_data($form_data=[]) {
    $cleaned_params = $form_errors = [];
    $tot_billable_value = $round_off = $net_pay = 0;
    $item_special_chars = [' ', '-', '_', '#', '/', '.', ',',':'];

    $one_item_found = false;

    $indent_date = Utilities::clean_string($form_data['indentDate']);
    $primary_mobile_no = Utilities::clean_string($form_data['primaryMobileNo']);
    $alter_mobile_no = Utilities::clean_string($form_data['alterMobileNo']);
    $name = Utilities::clean_string($form_data['name']);
    $agent_code = isset($form_data['agentCode']) ? Utilities::clean_string($form_data['agentCode']) : '';
    $executive_code = isset($form_data['executiveCode']) ? Utilities::clean_string($form_data['executiveCode']) : '';
    $campaign_code = isset($form_data['campaignCode']) ? Utilities::clean_string($form_data['campaignCode']) : '';
    $item_details = $form_data['itemDetails'];

    # this code is not mandatory. it will change per each item.
    $cleaned_params['locationCode'] = '';

    # validate location code
/*    if( isset($form_data['locationCode']) && ctype_alnum($form_data['locationCode']) ) {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['locationCode'] = 'Invalid location code.';
    }*/

    # validate primary mobile number.
    if( $primary_mobile_no !== '' && (!is_numeric($primary_mobile_no) || strlen($primary_mobile_no) !== 10) ) {
      $form_errors['primaryMobileNo'] = 'Invalid mobile number.';
    } else {
      $cleaned_params['primaryMobileNo'] = $primary_mobile_no;
    }

    # validate secondary mobile number.
    if( $alter_mobile_no !== '' && (!is_numeric($alter_mobile_no) || strlen($alter_mobile_no) !== 10)) {
      $form_errors['alterMobileNo'] = 'Invalid mobile number.';
    } else {
      $cleaned_params['alterMobileNo'] = $alter_mobile_no;
    }    

    # validate name.
    if( $name !== '' && !ctype_alnum(str_replace(' ', '', $name)) ) {
      $form_errors['name'] = 'Invalid name.';      
    } else {
      $cleaned_params['name'] = $name;
    }

    # validate item details.
    for($item_key=0;$item_key<count($item_details['itemName']);$item_key++) {
      if($item_details['itemName'][$item_key] !== '') {
        $one_item_found = true;

        $item_name = Utilities::clean_string($item_details['itemName'][$item_key]);
        $lot_no = Utilities::clean_string($item_details['lotNo'][$item_key]);
        $item_sold_qty = Utilities::clean_string($item_details['itemSoldQty'][$item_key]);
        $item_rate = Utilities::clean_string($item_details['itemRate'][$item_key]);

        $item_total = round($item_sold_qty * $item_rate, 2);

        # validate item name.
        if(ctype_alnum(str_replace($item_special_chars, array_fill(0, count($item_special_chars)-1,''), $item_name)) === false) {
          $form_errors['itemDetails']['itemName'][$item_key] = 'Invalid item name.';
        } else {
          $cleaned_params['itemDetails']['itemName'][$item_key] = $item_name;
        }

        # validate sold qty.
        if(!is_numeric($item_sold_qty) || $item_sold_qty<=0) {
          $form_errors['itemDetails']['itemSoldQty'][$item_key] = 'Invalid sold qty.';
        } else {
          $cleaned_params['itemDetails']['itemSoldQty'][$item_key] = $item_sold_qty;
        }

        # validate item rate.
        if(!is_numeric($item_rate) || $item_rate<=0) {
          $form_errors['itemDetails']['itemRate'][$item_key] = 'Invalid item rate.';
        } else {
          $cleaned_params['itemDetails']['itemRate'][$item_key] = $item_rate;
        }

        # validate lot no.
        if(ctype_alnum($lot_no)) {
          $cleaned_params['itemDetails']['lotNo'][$item_key] = $lot_no;
        } else {
          $form_errors['itemDetails']['lotNo'][$item_key] = 'Invalid Lot No.';  
        }        
      }
    }

    # if no items are available through an error.
    if($one_item_found === false) {
      $form_errors['itemDetails']['itemName'][0] = 'Invalid item name.';
      $form_errors['itemDetails']['itemSoldQty'][0] = 'Invalid sold qty.';
      $form_errors['itemDetails']['itemRate'][0] = 'Invalid item rate.';
    }

    # add misc parameters.
    $cleaned_params['indentDate'] = $indent_date;
    $cleaned_params['campaignCode'] = $campaign_code;
    $cleaned_params['agentCode'] = $agent_code;
    $cleaned_params['executiveCode'] = $executive_code;    

    # return response.
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