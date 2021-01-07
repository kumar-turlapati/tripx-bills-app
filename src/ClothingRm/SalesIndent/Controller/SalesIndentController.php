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
    $agents_response = $this->bu_model->get_business_users(['userType' => 90, 'returnActiveOnly' => 1]);
    $executives_response = $this->bu_model->get_business_users(['userType' => 91, 'returnActiveOnly' => 1]);
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

    if(!is_null($request->get('br'))) {
      $def_billing_rate = $request->get('br');
    } else {
      $def_billing_rate = 'mrp';
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
        $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i>&nbsp;You have errors in this form. Please fix them before you save', 1);
        $form_errors = $form_validation['errors'];
      } else {
        $api_response = $this->sindent_model->create_sindent($form_data);
        if($api_response['status']) {
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i>&nbsp;Sales indent with Indent No. <b>`'.$api_response['indentNo'].'`</b> created successfully.');
          Utilities::redirect('/sales-indent/create?lastIndent='.$api_response['indentNo'].'&lc='.$default_location.'&it='.$form_data['op']);
        } else {
          $page_error = $api_response['apierror'];
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i>&nbsp;'.$page_error,1);
        }
      }
    }

    # --------------- build variables -----------------
    $controller_vars = array(
      'page_title' => 'Create Sales Indent',
      'icon_name' => 'fa fa-delicious',
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
      'def_billing_rate' => $def_billing_rate,
    );

    return array($this->template->render_view('indent-create', $template_vars),$controller_vars);
  }

  // update indent
  public function updateIndent(Request $request) {

    # -------- initialize variables ---------------------------
    $page_error = $page_success = $indent_code = '';
    $form_errors = $agents_a = $campaigns_a = $form_data = [];
    $executives_a = [];
    $list_url = '/sales-indents/list';
    $indent_number = '';

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
    $agents_response = $this->bu_model->get_business_users(['userType' => 90, 'returnActiveOnly' => 1]);
    $executives_response = $this->bu_model->get_business_users(['userType' => 91, 'returnActiveOnly' => 1]);
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

      // validate submitted indent code and url indent code is same. otherwise 
      // redirect user to indents list with error message.
      $submitted_indent_code = Utilities::clean_string($form_data['ic']);
      $submitted_indent_no = Utilities::clean_string($form_data['in']);
      $url_indent_code = !is_null($request->get('indentCode')) ? $request->get('indentCode') : '';
      if($submitted_indent_code !== $url_indent_code) {
        $error = "Unable to proceed! Invalid parameters detected.";
        $this->flash->set_flash_message($error,1);
        Utilities::redirect($list_url);
      }

      if(isset($form_data['locationCode'])) {
        $default_location = $form_data['locationCode'];
      }
      $form_validation = $this->_validate_form_data($form_data);
      if($form_validation['status']===false) {
        $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;You have errors in this form. Please fix them before you save', 1);
        $form_errors = $form_validation['errors'];
      } else {
        $api_response = $this->sindent_model->update_sindent($form_data, $url_indent_code);
        if($api_response['status']) {
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i>&nbsp;Sales Indent No. `' .$submitted_indent_no.'` updated successfully with code [ '.$submitted_indent_code.' ]');
          Utilities::redirect($list_url);
        } else {
          $page_error = $api_response['apierror'];
          $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;'.$page_error,1);
        }
      }
    } elseif( !is_null($request->get('indentCode')) ) {
      $indent_code = Utilities::clean_string($request->get('indentCode'));
      $indent_api_response = $this->sindent_model->get_indent_details($indent_code, true);
      if($indent_api_response['status']) {
        $indent_number = $indent_api_response['response']['indentDetails']['tranDetails']['indentNo'];
        $form_data = $this->_map_indent_reponse_with_form_data($indent_api_response['response']['indentDetails']);
      } else {
        $this->flash->set_flash_message('Invalid indent code.', 1);
        Utilities::redirect($list_url);
      }
    } else {
      $this->set_flash_message('Invalid indent code.', 1);
      Utilities::redirect($list_url);
    }

    # --------------- build variables -----------------
    $controller_vars = array(
      'page_title' => 'Update Sales Indent',
      'icon_name' => 'fa fa-delicious',
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
      'indent_code' => $indent_code,
      'indent_number' => $indent_number
    );

    return array($this->template->render_view('indent-update', $template_vars),$controller_vars);
  }

  public function updateIndentStatus(Request $request) {

    # allow this option only to the administrator.
    if(isset($_SESSION['utype']) && (int)$_SESSION['utype'] !== 3 && (int)$_SESSION['utype'] !== 9) {
      $this->flash->set_flash_message("Permission Error: You are not authorized to perform this action.", 1);
      Utilities::redirect('/sales-indents/list');
    }

    # -------- initialize variables ---------------------------
    $page_error = $page_success = $indent_code = '';
    $form_errors = $agents_a = $campaigns_a = $form_data = [];
    $executives_a = [];
    $list_url = '/sales-indents/list';
    $indent_no = $indent_code = '';
    $indent_status_a = [-1=>'Choose', 0=>'Pending', 1=>'Approved', 2=>'Rejected', 4=>'On Hold', 5=>'Cancel'];

    # ------- fetch indent details -----------------------------
    $indent_code = Utilities::clean_string($request->get('indentCode'));
    $indent_api_response = $this->sindent_model->get_indent_details($indent_code, true);
    if($indent_api_response['status']) {
      $indent_number = $indent_api_response['response']['indentDetails']['tranDetails']['indentNo'];
      $form_data = $this->_map_indent_reponse_with_form_data($indent_api_response['response']['indentDetails']);
    } else {
      $this->flash->set_flash_message('Invalid indent code.', 1);
      Utilities::redirect($list_url);
    }    

    # ------------------------------------- check for form Submission --------------------------------
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_ar_indent_data($submitted_data);
      if($form_validation['status']===false) {
        $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;You have errors in this form. Please fix them before you save', 1);
        $form_errors = $form_validation['errors'];
        $form_data['ic'] = $indent_code;
        $form_data['in'] = $indent_number;
      } else {
        $api_response = $this->sindent_model->change_sindent_status($form_validation['cleaned_params'], $indent_code);
        if($api_response['status']) {
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i>&nbsp;Sales indent with Indent No. <b>`'.$indent_number.'`</b> updated successfully.');
          Utilities::redirect($list_url);
        } else {
          $page_error = $api_response['apierror'];
          $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;'.$page_error,1);
        }
      }
    }
    # --------------- build variables ------------------------------------------------------------------
    $controller_vars = array(
      'page_title' => 'Approve / Reject Indent No. - '.$indent_number,
      'icon_name' => 'fa fa-delicious',
    );
    # ---------------- prepare form variables. ---------
    $template_vars = array(
      'form_data' => $form_data,
      'errors' => $form_errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'btn_label' => 'Save',
      'flash_obj' => $this->flash,
      'indent_code' => $indent_code,
      'indent_number' => $indent_number,
      'indent_status_a' => $indent_status_a,
      'form_errors' => $form_errors,
    );

    return array($this->template->render_view('change-indent-status', $template_vars),$controller_vars);    
  }

  // list indents
  public function listIndents(Request $request) {
    $locations = $vouchers = $search_params = $indents_a = $agents_a = [];
    $executives_a = [];
    $campaigns_a = [];
    $campaign_code = $page_error = $agent_code = '';
    if(isset($_SESSION['utype']) && (int)$_SESSION['utype']===15) {
      $status_a = [1=>'Approved'];
      $def_indent_status = 1;
    } else {
      $status_a = [99 => 'All Statuses', 0=>'Pending', 1=>'Approved', 2=>'Rejected', 4=>'On Hold', 5=>'Cancelled', 6=>'Billed'];
      $def_indent_status = '';
    }
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    # ---------- get business users ----------------------------
    $agents_response = $this->bu_model->get_business_users(['userType' => 90, 'returnActiveOnly' => 1]);
    if($agents_response['status']) {
      foreach($agents_response['users'] as $user_details) {
        if($user_details['cityName'] !== '') {
          $agents_a[$user_details['userCode']] = $user_details['userName'].'__'.substr($user_details['cityName'],0,10);
        } else {
          $agents_a[$user_details['userCode']] = $user_details['userName'];
        }
      }
    }

    # ---------- get live campaigns ---------------------------------
    $campaigns_response = $this->camp_model->list_campaigns();
    if($campaigns_response['status']) {
      $campaign_keys = array_column($campaigns_response['campaigns']['campaigns'], 'campaignCode');
      $campaign_names = array_column($campaigns_response['campaigns']['campaigns'], 'campaignName');
      $campaigns_a = array_combine($campaign_keys, $campaign_names);
    }

    # ---------- get executives ---------------------------------
    $executives_response = $this->bu_model->get_business_users(['userType' => 91, 'ignoreLocation' => 1, 'returnActiveOnly' => 1]);
    if($executives_response['status']) {
      foreach($executives_response['users'] as $user_details) {
        if($user_details['cityName'] !== '') {
          $executives_a[$user_details['userCode']] = $user_details['userName'].'__'.substr($user_details['cityName'],0,10);
        } else {
          $executives_a[$user_details['userCode']] = $user_details['userName'];
        }
      }
    }    

    // parse request parameters.
    $per_page = 100;
    $from_date = $request->get('fromDate') !== null ? Utilities::clean_string($request->get('fromDate')):'01-'.date('m').'-'.date("Y");
    $to_date = $request->get('toDate') !== null ? Utilities::clean_string($request->get('toDate')):date("d-m-Y");
    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')):1;
    $campaign_code = $request->get('campaignCode') !== null ? Utilities::clean_string($request->get('campaignCode')):'';
    $agent_code = $request->get('agentCode') !== null ? Utilities::clean_string($request->get('agentCode')):'';
    $executive_code = $request->get('executiveCode') !== null ? Utilities::clean_string($request->get('executiveCode')):'';
    $customer_name = $request->get('custName') !== null ? Utilities::clean_string($request->get('custName')):'';
    $status = $request->get('status') !== null && (int)$request->get('status') !== 99 ? Utilities::clean_string($request->get('status')) : $def_indent_status;
    $indent_type = $request->get('indentType') !== null && $request->get('indentType') !== '' ? Utilities::clean_string($request->get('indentType')) : '';

    $search_params = array(
      'fromDate' => $from_date,
      'toDate' => $to_date,
      'pageNo' => $page_no,
      'perPage' => $per_page,
      'campaignCode' => $campaign_code,
      'agentCode' => $agent_code,
      'executiveCode' => $executive_code,
      'custName' => $customer_name,
      'status' => $status,
      'indentType' => $indent_type,
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
      'agents' => [''=>'Referred by'] + $agents_a,
      'campaigns' =>  [''=>'Campaign Name'] + $campaigns_a,
      'executives' => [''=>'All Executives'] + $executives_a,
      'campaignCode' => $campaign_code,
      'agentCode' => $agent_code,
      'status_a' => $status_a,
      'indent_types_a' => ['' => 'All Indent Types', 'online' => 'Online Indents', 'offline' => 'Offline Indents'], 
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Sales Indent Register',
      'icon_name' => 'fa fa-delicious',
    );

    // render template
    return array($this->template->render_view('indents-list', $template_vars), $controller_vars);
  }

  public function indentVsSales(Request $request) {

    $sales = $search_params = $sales_a = $agents_a = $campaigns_a = [];
    $campaign_code = $page_error = $agent_code = '';
    $sa_executives = [];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    # ---------- get business users ----------------------------
    $agents_response = $this->bu_model->get_business_users(['userType' => 90, 'returnActiveOnly' => 1]);
    if($agents_response['status']) {
      foreach($agents_response['users'] as $user_details) {
        if($user_details['cityName'] !== '') {
          $agents_a[$user_details['userCode']] = $user_details['userName'].'__'.substr($user_details['cityName'],0,10);
        } else {
          $agents_a[$user_details['userCode']] = $user_details['userName'];
        }
      }
    }

    # ---------- get live campaigns ---------------------------------
    $campaigns_response = $this->camp_model->list_campaigns();
    if($campaigns_response['status']) {
      $campaign_keys = array_column($campaigns_response['campaigns']['campaigns'], 'campaignCode');
      $campaign_names = array_column($campaigns_response['campaigns']['campaigns'], 'campaignName');
      $campaigns_a = array_combine($campaign_keys, $campaign_names);
    }

    // sales exe response
    $sexe_response = $this->bu_model->get_business_users(['userType' => 91, 'returnActiveOnly' => 1, 'ignoreLocation' => 1]);
    if($sexe_response['status']) {
      foreach($sexe_response['users'] as $user_details) {
        $sa_executives[$user_details['userCode']] = $user_details['userName'];
      }
    }

    // parse request parameters.
    $per_page = 100;
    $from_date = $request->get('fromDate') !== null ? Utilities::clean_string($request->get('fromDate')):'01-'.date('m').'-'.date("Y");
    $to_date = $request->get('toDate') !== null ? Utilities::clean_string($request->get('toDate')):date("d-m-Y");
    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')):1;
    $campaign_code = $request->get('campaignCode') !== null ? Utilities::clean_string($request->get('campaignCode')):'';
    $agent_code = $request->get('agentCode') !== null ? Utilities::clean_string($request->get('agentCode')):'';
    $customer_name = $request->get('custName') !== null ? Utilities::clean_string($request->get('custName')):'';
    $qty_type = $request->get('qtyType') !== null ? Utilities::clean_string($request->get('qtyType')) : 'all';
    $exe_code = $request->get('executiveCode') !== null ? Utilities::clean_string($request->get('executiveCode')) : '';

    $search_params = array(
      'fromDate' => $from_date,
      'toDate' => $to_date,
      'pageNo' => $page_no,
      'perPage' => $per_page,
      'campaignCode' => $campaign_code,
      'agentCode' => $agent_code,
      'custName' => $customer_name,
      'qtyType' => $qty_type,
      'executiveCode' => $exe_code,
    );

    // dump($search_params);
    // exit;

    $api_response = $this->sindent_model->indent_vs_sales($search_params);
    // dump($api_response);
    // exit;
    if($api_response['status']) {
      if(count($api_response['response']['sales'])>0) {
        $slno = Utilities::get_slno_start(count($api_response['response']['sales']),$per_page,$page_no);
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
        $sales_a = $api_response['response']['sales'];
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
      'page_error' => $page_error,
      'sales' => $sales_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
      'agents' => [''=>'Agent/Wholesaler'] + $agents_a,
      'campaigns' =>  [''=>'Campaign Name'] + $campaigns_a,
      'campaignCode' => $campaign_code,
      'agentCode' => $agent_code,
      'qty_types' => ['all' => 'Pending And Completed', 'pending' => 'Pending', 'completed' => 'Completed'],
      'sa_executives' => ['All Executives'] + $sa_executives,      
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Indent vs Sales',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('indent-vs-sales', $template_vars), $controller_vars);
  }

  public function indentVsSalesByItem(Request $request) {

    $sales = $search_params = $sales_a = $agents_a = $campaigns_a = [];
    $campaign_code = $page_error = $agent_code = '';

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    # ---------- get business users ----------------------------
    $agents_response = $this->bu_model->get_business_users(['userType' => 90]);
    if($agents_response['status']) {
      foreach($agents_response['users'] as $user_details) {
        if($user_details['cityName'] !== '') {
          $agents_a[$user_details['userCode']] = $user_details['userName'].'__'.substr($user_details['cityName'],0,10);
        } else {
          $agents_a[$user_details['userCode']] = $user_details['userName'];
        }
      }
    }

    # ---------- get live campaigns ---------------------------------
    $campaigns_response = $this->camp_model->list_campaigns();
    if($campaigns_response['status']) {
      $campaign_keys = array_column($campaigns_response['campaigns']['campaigns'], 'campaignCode');
      $campaign_names = array_column($campaigns_response['campaigns']['campaigns'], 'campaignName');
      $campaigns_a = array_combine($campaign_keys, $campaign_names);
    }

    // sales exe response
    $sexe_response = $this->bu_model->get_business_users(['userType' => 91, 'returnActiveOnly' => 1, 'ignoreLocation' => 1]);
    if($sexe_response['status']) {
      foreach($sexe_response['users'] as $user_details) {
        $sa_executives[$user_details['userCode']] = $user_details['userName'];
      }
    }    

    // parse request parameters.
    $per_page = 100;
    $from_date = $request->get('fromDate') !== null ? Utilities::clean_string($request->get('fromDate')):'01-'.date('m').'-'.date("Y");
    $to_date = $request->get('toDate') !== null ? Utilities::clean_string($request->get('toDate')):date("d-m-Y");
    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')):1;
    $campaign_code = $request->get('campaignCode') !== null ? Utilities::clean_string($request->get('campaignCode')):'';
    $agent_code = $request->get('agentCode') !== null ? Utilities::clean_string($request->get('agentCode')):'';
    $customer_name = $request->get('custName') !== null ? Utilities::clean_string($request->get('custName')):'';
    $qty_type = $request->get('qtyType') !== null ? Utilities::clean_string($request->get('qtyType')) : 'all';
    $exe_code = $request->get('executiveCode') !== null ? Utilities::clean_string($request->get('executiveCode')) : '';

    $search_params = array(
      'fromDate' => $from_date,
      'toDate' => $to_date,
      'pageNo' => $page_no,
      'perPage' => $per_page,
      'campaignCode' => $campaign_code,
      'agentCode' => $agent_code,
      'custName' => $customer_name,
      'infoType' => 'item',
      'qtyType' => $qty_type,
      'executiveCode' => $exe_code,
    );

    $api_response = $this->sindent_model->indent_vs_sales($search_params);
    // dump($api_response);
    // exit;
    if($api_response['status']) {
      if(count($api_response['response']['sales'])>0) {
        $slno = Utilities::get_slno_start(count($api_response['response']['sales']),$per_page,$page_no);
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
        $sales_a = $api_response['response']['sales'];
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
      'page_error' => $page_error,
      'sales' => $sales_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
      'agents' => [''=>'Agent/Wholesaler'] + $agents_a,
      'campaigns' =>  [''=>'Campaign Name'] + $campaigns_a,
      'campaignCode' => $campaign_code,
      'agentCode' => $agent_code,
      'qty_types' => ['all' => 'Pending And Completed', 'pending' => 'Pending', 'completed' => 'Completed'],
      'sa_executives' => ['All Executives'] + $sa_executives,      
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Indent vs Sales - Itemwise',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('indent-vs-sales-itemwise', $template_vars), $controller_vars);
  }  

  // mobile indent form
  public function createIndentMobileView(Request $request) {

    #------------------------------------- check for form Submission ------------------------
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $op = $form_data['op'];
      if($op === 'SaveandCustomer') {
        Utilities::redirect('/sales-indent/create/mobile/step2');
      } elseif($op === 'SaveandItems') {
        $validation = $this->_validate_mobile_indent_data($form_data);
        if($validation['status']) {
          $_SESSION['indentItemsM'][] = $validation['cleaned_params'];
          $this->flash->set_flash_message('Item `'.$validation['cleaned_params']['itemName'].'` added successfully.');
        } else {
          $form_errors = $validation['form_errors'];
          $form_error = count($form_errors > 0) ? implode(' | ', $form_errors) : ''; 
          $this->flash->set_flash_message('Errors: '.$form_error, 1);
        }
        Utilities::redirect('/sales-indent/create/mobile');
      }
    }

    # controller and template variables.
    $controller_vars = array(
      'disable_sidebar' => true,
      'disable_footer' => true,
      'show_page_name' => false,
      'body_class_name' => 'loginPage',
    );

    $template_vars = array(
      'flash_obj' => $this->flash,
    );

    // render template
    return array($this->template->render_view('indent-create-mobile-view', $template_vars), $controller_vars);    
  }

  public function createIndentMobileViewStep2(Request $request) {
    if(isset($_SESSION['indentItemsM']) && count($_SESSION['indentItemsM']) > 0) {
      $indent_items = $_SESSION['indentItemsM'];
    } else {
      $this->flash->set_flash_message('No items are available in indent.', 1);
      Utilities::redirect('/sales-indent/create/mobile');
    }

    $executives_a = $campaigns_a = [];

    # ---------- get live campaigns ---------------------------------
    $campaigns_response = $this->camp_model->list_campaigns();
    if($campaigns_response['status']) {
      $campaign_keys = array_column($campaigns_response['campaigns']['campaigns'], 'campaignCode');
      $campaign_names = array_column($campaigns_response['campaigns']['campaigns'], 'campaignName');
      $campaigns_a = array_combine($campaign_keys, $campaign_names);
    }

    $executives_response = $this->bu_model->get_business_users(['userType' => 91, 'returnActiveOnly' => 1]);
    if($executives_response['status']) {
      foreach($executives_response['users'] as $user_details) {
        if($user_details['cityName'] !== '') {
          $executives_a[$user_details['userCode']] = $user_details['userName'].'__'.substr($user_details['cityName'],0,10);
        } else {
          $executives_a[$user_details['userCode']] = $user_details['userName'];
        }
      }
    }
    #------------------------------------- check for form Submission ------------------------
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $op = $form_data['op'];
      if($op === 'SaveIndent') {
        $cleaned_params = [];
        $executive_code = isset($form_data['executiveCode']) ? Utilities::clean_string($form_data['executiveCode']) : '';
        $cleaned_params['name'] = Utilities::clean_string($form_data['customerName']);
        $cleaned_params['remarks'] = Utilities::clean_string($form_data['remarks']);
        $cleaned_params['campaignCode'] = Utilities::clean_string($form_data['campaignCode']);
        $cleaned_params['billingRate'] = 'wholesale';
        $cleaned_params['locationCode'] = '';
        $cleaned_params['indentDate'] = date("d-m-Y");
        $cleaned_params['executiveCode'] = $executive_code;
        $cleaned_params['isAutoIndent'] = 1;
        foreach($indent_items as $item_key => $indent_item_details) {
          $cleaned_params['itemDetails']['itemName'][$item_key] = $indent_item_details['itemName'];
          $cleaned_params['itemDetails']['itemSoldQty'][$item_key] = $indent_item_details['orderQty'];
          $cleaned_params['itemDetails']['lotNo'][$item_key] = $indent_item_details['lotNo'];
          $cleaned_params['itemDetails']['itemRate'][$item_key] = $indent_item_details['mrp'];          
        }

        // dump($cleaned_params);
        // exit;

        $api_response = $this->sindent_model->create_sindent($cleaned_params);
        if($api_response['status']) {
          unset($_SESSION['indentItemsM']);
          $indent_no = $api_response['indentNo'];
          $success_message = 'Indent saved successfully.<p style="font-size:12px;font-weight:bold;"><i class="fa fa-print"></i>&nbsp;<a href="/print-indent?indentNo='.$indent_no.'" target="_blank">Print with Rate</a> | <i class="fa fa-print"></i>&nbsp;<a href="/print-indent-wor?indentNo='.$indent_no.'" target="_blank">Print without Rate</a></p>';
          $this->flash->set_flash_message($success_message);
          Utilities::redirect('/sales-indent/create/mobile');
        } else {
          $page_error = $api_response['apierror'];
          $this->flash->set_flash_message($page_error,1);
          Utilities::redirect('/sales-indent/create/mobile/step2', 1);          
        }
      } elseif($op === 'CancelIndent') {
        if(isset($_SESSION['indentItemsM'])) {
          unset($_SESSION['indentItemsM']);
          $this->flash->set_flash_message('Indent cancelled successfully');
          Utilities::redirect('/sales-indent/create/mobile');
        }
      }
    }    

    # controller and template variables.
    $controller_vars = array(
      'disable_sidebar' => true,
      'disable_footer' => true,
      'show_page_name' => false,
      'body_class_name' => 'loginPage',
    );

    $template_vars = array(
      'flash_obj' => $this->flash,
      'campaigns' =>  [''=>'Campaign Name'] + $campaigns_a,
      'executives' => ['' => 'Choose'] + $executives_a,
    );

    // render template
    return array($this->template->render_view('indent-create-mobile-view2', $template_vars), $controller_vars);    
  }

  // validate ar data
  private function _validate_ar_indent_data($form_data= []) {
    $cleaned_params = $form_errors = [];
    $indent_status = isset($form_data['arStatus']) ? (int)Utilities::clean_string($form_data['arStatus']) : -1;
    $indent_remarks = isset($form_data['arRemarks']) ? Utilities::clean_string($form_data['arRemarks']) : '';
    if($indent_status === 1 || $indent_status === 2 || $indent_status === 4 || $indent_status === 5) {
      $cleaned_params['arStatus'] = $indent_status;
    } else {
      $form_errors['arStatus'] = 'Invalid Status.';
    }

    $cleaned_params['arRemarks'] = $indent_remarks;

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
    $remarks = isset($form_data['remarks']) ? Utilities::clean_string($form_data['remarks']) : '';
    $billing_rate = isset($form_data['billingRate']) ? Utilities::clean_string($form_data['billingRate']) : '';

    $item_details = $form_data['itemDetails'];

    # this code is not mandatory. it will change per each item.
    $cleaned_params['locationCode'] = '';
    $cleaned_params['name'] = $name;

    # validate location code
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

    # validate item details.
    for($item_key=0;$item_key<count($item_details['itemName']);$item_key++) {
      if($item_details['itemName'][$item_key] !== '') {
        $one_item_found = true;

        $item_name = Utilities::clean_string($item_details['itemName'][$item_key]);
        $lot_no = Utilities::clean_string($item_details['lotNo'][$item_key]);
        $item_sold_qty = Utilities::clean_string($item_details['itemSoldQty'][$item_key]);
        $item_rate = Utilities::clean_string($item_details['itemRate'][$item_key]);

        $item_total = round($item_sold_qty * $item_rate, 2);

        // validate item name.
        if( $item_name === '') {
        // if(ctype_alnum(str_replace($item_special_chars, array_fill(0, count($item_special_chars)-1,''), $item_name)) === false) {
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
    $cleaned_params['remarks'] = $remarks;
    $cleaned_params['billing_rate'] = $billing_rate;

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

  // map indent response data with form data.
  private function _map_indent_reponse_with_form_data($api_data=[]) {
    // dump($api_data);
    // exit;
    $form_data = [];
    $form_data['indentDate'] = $api_data['tranDetails']['indentDate'];
    $form_data['primaryMobileNo'] = $api_data['tranDetails']['primaryMobileNo'];
    $form_data['alternativeMobileNo'] = $api_data['tranDetails']['alterMobileNo'];
    $form_data['name'] = $api_data['tranDetails']['customerName'];
    $form_data['agentCode'] = $api_data['tranDetails']['agentCode'];        
    $form_data['executiveCode'] = $api_data['tranDetails']['executiveCode'];
    $form_data['campaignCode'] = $api_data['tranDetails']['campaignCode'];
    $form_data['remarks'] = $api_data['tranDetails']['remarks'];
    $form_data['remarks2'] = $api_data['tranDetails']['remarks2'];
    $form_data['indentStatus'] = $api_data['tranDetails']['indentStatus'];
    $form_data['agentName'] = $api_data['tranDetails']['agentName'];
    $form_data['campaignName'] = $api_data['tranDetails']['campaignName'];    
    $form_data['executiveName'] = $api_data['tranDetails']['executiveName'];
    $form_data['billingRate'] = $api_data['tranDetails']['billingRate'];

    $form_data['itemDetails']['itemName'] = array_column($api_data['itemDetails'], 'itemName');
    $form_data['itemDetails']['lotNo'] = array_column($api_data['itemDetails'], 'lotNo');
    $form_data['itemDetails']['itemSoldQty'] = array_column($api_data['itemDetails'], 'itemQty');
    $form_data['itemDetails']['itemRate'] = array_column($api_data['itemDetails'], 'itemRateIndent');
    $form_data['itemDetails']['barcode'] = array_column($api_data['itemDetails'], 'barcode');

    return $form_data;
  }

  private function _validate_mobile_indent_data($form_data = []) {
    $form_errors = $cleaned_params = [];
    $item_name = Utilities::clean_string($form_data['itemName']);
    $lot_no = Utilities::clean_string($form_data['lotNo']);
    $order_qty = Utilities::clean_string($form_data['orderQty']);
    $mrp = Utilities::clean_string($form_data['mrp']);

    if($item_name === '') {
      $form_errors['itemName'] = 'Item name is mandatory.';
    } else {
      $cleaned_params['itemName'] = $item_name;
    }
    if($lot_no === '') {
      $form_errors['lotNo'] = 'Lot No. is mandatory.';
    } else {
      $cleaned_params['lotNo'] = $lot_no;
    }
    if(is_numeric($order_qty) && $order_qty >0) {
      $cleaned_params['orderQty'] = $order_qty;
    } else {
      $form_errors['orderQty'] = 'Invalid Order Qty.';
    }
    if(is_numeric($mrp) && $mrp >0) {
      $cleaned_params['mrp'] = $mrp;
    } else {
      $form_errors['mrp'] = 'Invalid MRP';      
    }

    if(count($form_errors)>0) {
      return [
        'status' => false,
        'form_errors' => $form_errors,
      ];
    } else {
      return [
        'status' => true,
        'cleaned_params' => $cleaned_params,
      ];
    }
  }
}