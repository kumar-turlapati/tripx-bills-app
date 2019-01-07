<?php 

namespace ClothingRm\StockAudit\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\StockAudit\Model\StockAudit;
use ClothingRm\Products\Model\Products;

class StockAuditController
{
	protected $template, $audit_model, $flash;

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');    
    $this->audit_model = new StockAudit;
    $this->flash = new Flash;
    $this->product_api_call = new Products;
	}

  public function createStockAudit(Request $request) {

    $submitted_data = $form_errors = [];
    $status_options = array(1 => 'OPEN', 2 => 'LOCKED');
    $audit_types_a = array('int' => 'Internal', 'ext' => 'External');
    if(isset($_SESSION['utype']) && (int)$_SESSION['utype'] === 3) {
      $status_options[4] = 'APPROVED';
      $status_options[3] = 'DELETED';
    }

    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';
    $audit_code = '';

    // ---------- get location codes from api -----------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data, $location_codes, array_keys($status_options), array_keys($audit_types_a));
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->audit_model->create_audit($cleaned_params);
        if($result['status']) {
          $audit_code = $result['auditCode'];
          $this->flash->set_flash_message('Audit entry created successfully. Please start adding your count below.');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/stock-audit/items/'.$audit_code);
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'status_options' => $status_options,
      'form_data' => $submitted_data,
      'form_errors' => $form_errors,
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => $default_location,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'audit_types' => ['' => 'Select'] + $audit_types_a,
      'audit_code' => $audit_code,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Stock Audit',
      'icon_name' => 'fa fa-check',
    );

    // render template
    return array($this->template->render_view('add-stock-audit',$template_vars),$controller_vars);
  }

  public function stockAuditsRegister(Request $request) {
    $total_pages = $total_records = $record_count = $page_no = 0;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';
    
    $client_locations = $location_ids = $location_codes = $audits_a = [];
    $page_no = 1; $per_page = 100;

    // ---------- get location codes from api ------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    // check for filter variables.
    if(is_null($request->get('pageNo'))) {
      $search_params['pageNo'] = 1;
    } else {
      $search_params['pageNo'] = $page_no = (int)$request->get('pageNo');
    }
    if(is_null($request->get('perPage'))) {
      $search_params['perPage'] = 100;
    } else {
      $search_params['perPage'] = $per_page = (int)$request->get('perPage');
    }
    if(is_null($request->get('fromDate'))) {
      $search_params['fromDate'] = date("01-m-Y");
    } else {
      $search_params['fromDate'] = $request->get('fromDate');
    }
    if(is_null($request->get('toDate'))) {
      $search_params['toDate'] = date("d-m-Y");
    } else {
      $search_params['toDate'] = $request->get('toDate');
    }
    if(is_null($request->get('locationCode'))) {
      $search_params['locationCode'] = $_SESSION['lc'];
    } else {
      $search_params['locationCode'] = $request->get('locationCode');
    }

    // hit API.
    $audit_api_call = $this->audit_model->get_audit_register($search_params);
    $api_status = $audit_api_call['status'];

    // check api status
    if($api_status) {
      if(count($audit_api_call['records']['records'])>0) {
        $slno = Utilities::get_slno_start(count($audit_api_call['records']['records']), $per_page, $page_no);
        $to_sl_no = $slno + $per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;
        }
        if($audit_api_call['records']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $audit_api_call['records']['total_pages'];
        }
        if($audit_api_call['records']['total_records'] < $per_page) {
          $to_sl_no = ($slno+$audit_api_call['records']['total_records'])-1;
        }

        $audits_a = $audit_api_call['records']['records'];
        $total_pages = $audit_api_call['records']['total_pages'];
        $total_records = $audit_api_call['records']['total_records'];
        $record_count = $audit_api_call['records']['total_records'];
      } else {
        $page_error = $audit_api_call['apierror'];
      }
    } else {
      $this->flash->set_flash_message($audit_api_call['apierror'], 1);
    }

    // prepare form variables.
    $template_vars = array(
      'audits' => $audits_a,
      'total_pages' => $total_pages,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'client_locations' => ['' => 'All Stores'] + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'flash_obj' => $this->flash,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Stock Audit Register',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('stock-audit-list', $template_vars),$controller_vars);
  }

  public function stockAuditItems(Request $request) {

    $register_url = '/stock-audit/register';

    // pagination variables.
    $total_pages = $total_records = $record_count = $page_no = 0;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';
    $fetch_pattern_a = ['all' => 'All Items', 'phy' => 'Counted Items'];
    $audit_status = 1;
    
    $client_locations = $location_ids = $location_codes = $items_a = [];

    // get location codes from api
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    if(!is_null($request->get('auditCode'))) {
      $audit_code = Utilities::clean_string($request->get('auditCode'));
      $audit_details = $this->audit_model->get_audit_details($audit_code);
      if(is_array($audit_details) && count($audit_details)>0) {
        $audit_location_id = $audit_details['locationID'];
        $audit_status = (int)$audit_details['status'];
        // check for filter variables.
        if(isset($location_codes[$audit_location_id])) {
          $audit_location_code = $location_codes[$audit_location_id];
        } else {
          $this->flash->set_flash_message('Invalid audit location', 1);
          Utilities::redirect($register_url);
        }
        // check whether the audit is locked and operated by non admin.
        if( $audit_status === 2 && (int)$_SESSION['utype'] !== 3) {
          $this->flash->set_flash_message('<i class="fa fa-lock"></i>&nbsp;&nbsp; The Audit you are trying to edit has been locked and in verification. If you wish to unlock contact Administrator.', 1);
          Utilities::redirect($register_url.'?locationCode='.$audit_location_code);
        }        
      } else {
        $this->flash->set_flash_message('Invalid Audit Location', 1);
        Utilities::redirect($register_url);
      }
    } else {
      $this->flash->set_flash_message('Invalid Audit Sequence...', 1);
      Utilities::redirect($register_url);
    }

    // check whether the audit is submitted for verification.
    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      if(isset($submitted_data['op']) && $submitted_data['op'] === 'saLockSubmit') {
        $result = $this->audit_model->lock_audit($audit_code);
        if($result['status']) {
          $this->flash->set_flash_message('<i class="fa fa-lock"></i>&nbsp;<i class="fa fa-check"></i>&nbsp; This Audit has been locked and successfully submitted for verification.');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect($register_url.'?locationCode='.$audit_location_code);
      } elseif(isset($submitted_data['op']) && $submitted_data['op'] === 'saPhyQty') {
        $result = $this->audit_model->post_system_qty($audit_code);
        if($result['status']) {
          $this->flash->set_flash_message('<i class="fa fa-database"></i>&nbsp;System Qty. posted successfully for '.$result['records'].' items.');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/stock-audit/items/'.$audit_code);
      }
    }

    $search_params['locationCode'] = $audit_location_code;
    if(is_null($request->get('pageNo'))) {
      $search_params['pageNo'] = $page_no = 1;
    } else {
      $search_params['pageNo'] = $page_no = (int)$request->get('pageNo');
    }
    if(is_null($request->get('perPage'))) {
      $search_params['perPage'] = $per_page = 100;
    } else {
      $search_params['perPage'] = $per_page = (int)$request->get('perPage');
    }
    if(is_null($request->get('category'))) {
      $search_params['category'] = '';
    } else {
      $search_params['category'] = Utilities::clean_string($request->get('category'));
    }
    if(is_null($request->get('brandName'))) {
      $search_params['brandName'] = '';
    } else {
      $search_params['brandName'] = Utilities::clean_string($request->get('brandName'));
    }
    if(is_null($request->get('psName'))) {
      $search_params['psName'] = '';
    } else {
      $search_params['psName'] = Utilities::clean_string($request->get('psName'));
    }
    if(is_null($request->get('fetchPattern'))) {
      $search_params['fetchPattern'] = 'all';
    } else {
      $search_params['fetchPattern'] = Utilities::clean_string($request->get('fetchPattern'));
    }

    // dump($search_params);
    // exit;

    // get categories
    $categories = array('' => 'All Categories')+$this->product_api_call->get_product_categories($search_params['locationCode']);    

    // hit API.
    $items_api_call = $this->audit_model->get_audit_items($search_params, $audit_code);
    $api_status = $items_api_call['status'];

    // check api status
    if($api_status) {
      if(count($items_api_call['response']['items'])>0) {
        $audit_totals = $items_api_call['response']['totals'];
        $slno = Utilities::get_slno_start(count($items_api_call['response']['items']), $per_page, $page_no);
        $to_sl_no = $slno + $per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;
        }
        if($items_api_call['response']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $items_api_call['response']['total_pages'];
        }
        if($items_api_call['response']['total_records'] < $per_page) {
          $to_sl_no = ($slno+$items_api_call['response']['total_records'])-1;
        }

        $items_a = $items_api_call['response']['items'];
        $total_pages = $items_api_call['response']['total_pages'];
        $total_records = $items_api_call['response']['total_records'];
        $record_count = $items_api_call['response']['total_records'];
      } else {
        $page_error = $items_api_call['apierror'];
      }
    } else {
      $this->flash->set_flash_message($items_api_call['apierror'], 1);
    }    

    // prepare form variables.
    $template_vars = array(
      'items' => $items_a,
      'total_pages' => $total_pages,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'client_locations' => ['' => 'All Stores'] + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'flash_obj' => $this->flash,
      'audit_code' => $audit_code,
      'categories' => $categories,
      'audit_location_code' => $audit_location_code,
      'fetch_pattern_a' => $fetch_pattern_a,
      'audit_status' => $audit_status,
      'audit_totals' => $audit_totals,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Stock Audit - Update Physical Quantities',
      'icon_name' => 'fa fa-database',
    );

    // render template
    return array($this->template->render_view('stock-audit-items', $template_vars),$controller_vars);    
  }

  private function _validate_form_data($form_data=[], $location_codes=[], $status_options=[], $audit_types=[]) {
    $cleaned_params = $errors = [];
    
    $location_code = isset($form_data['locationCode']) ? Utilities::clean_string($form_data['locationCode']) : '';
    $audit_type = isset($form_data['auditType']) ? Utilities::clean_string($form_data['auditType']) : '';
    $audit_start_date = isset($form_data['auditStartDate']) ? Utilities::clean_string($form_data['auditStartDate']) : '';
    $cb_date = isset($form_data['cbDate']) ? Utilities::clean_string($form_data['cbDate']) : '';
    $status = isset($form_data['status']) ? Utilities::clean_string($form_data['status']) : 0;

    // dump($audit_type, $audit_types, $status_options, $status);
    // exit;

    if(in_array($location_code, $location_codes) === false) {
      $errors['locationCode'] = 'Invalid store name.';
    } else {
      $cleaned_params['locationCode'] = $location_code;
    }
    if(in_array($audit_type, $audit_types) === false) {
      $errors['auditType'] = 'Invalid audit type.';
    } else {
      $cleaned_params['auditType'] = $audit_type;
    }
    if(Utilities::validateDate($audit_start_date)) {
      $cleaned_params['auditStartDate'] = $audit_start_date;
    } else {
      $errors['auditStartDate'] = 'Invalid audit start date.';
    }
    if(Utilities::validateDate($cb_date)) {
      $cleaned_params['cbDate'] = $cb_date;
    } else {
      $errors['cbDate'] = 'Invalid closing balance date.';
    }
    if(in_array($status, $status_options) === false) {
      $errors['status'] = 'Invalid status.';
    } else {
      $cleaned_params['status'] = $status;
    }
    if( strtotime($audit_start_date) === strtotime($cb_date) ) {
      $errors['cbDate'] = 'Audit start date and closing balance date should not be same.';
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

/*  public function updateStockAudit(Request $request) {
    $submitted_data = $form_errors = array();
    $status_options = array(''=>'Select','1'=>'Active','0'=>'Inactive');

    if( is_null($request->get('mfgCode')) || is_null($request->get('lc'))) {
      $this->flash->set_flash_message('Invalid Operation.');
      Utilities::redirect('/mfgs/list');
    } else {
      $mfg_code = Utilities::clean_string($request->get('mfgCode'));
      $mfg_location = Utilities::clean_string($request->get('lc'));
    }

    // ---------- get location codes from api -----------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    $mfg_details = $this->mfg_model->get_mfg_details($mfg_code, $mfg_location);
    if($mfg_details === false) {
      $this->flash->set_flash_message('Invalid brand / mfg. code', 1);
      Utilities::redirect('/mfgs/list');
    } else {
      $submitted_data = $mfg_details;
    }

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->mfg_model->update_mfg($cleaned_params,$mfg_code);
        if($result['status']) {
          $this->flash->set_flash_message('Brand / Mfg details updated successfully');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/mfgs/list');
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'status_options' => $status_options,
      'submitted_data' => $submitted_data,
      'form_errors' => $form_errors,
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,      
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Product Brands / Manufacturers',
      'icon_name' => 'fa fa-thumbs-o-up',
    );

    # render template
    return array($this->template->render_view('update-stock-audit',$template_vars),$controller_vars);
  }

  public function listStockAudits(Request $request) {
    $mfgs_list = $search_params = $mfgs = [];
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];
    }

    $per_page = 100;
    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';
    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')):1;
    $location_code = $request->get('locationCode')!== null ? Utilities::clean_string($request->get('locationCode')) : $default_location;

    $search_params = array(
      'locationCode' => $location_code,
      'pageNo' => $page_no,
      'perPage' => $per_page,
    );

    $api_response = $this->mfg_model->get_mfgs($search_params);
    if($api_response['status']) {
      if(count($api_response['mfgs']) >0) {
        $slno = Utilities::get_slno_start(count($api_response['mfgs']['mfgs']),$per_page,$page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;        
        }
        if($api_response['mfgs']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $api_response['mfgs']['total_pages'];
        }
        if($api_response['mfgs']['this_page'] < $per_page) {
          $to_sl_no = ($slno+$api_response['mfgs']['this_page'])-1;
        }
        $mfgs_a = $api_response['mfgs']['mfgs'];
        $total_pages = $api_response['mfgs']['total_pages'];
        $total_records = $api_response['mfgs']['total_records'];
        $record_count = $api_response['mfgs']['this_page'];
      } else {
        $page_error = $api_response['apierror'];
        $this->flash->set_flash_message($page_error, 1);
      }
    } else {
      $page_error = $api_response['apierror'];
      $this->flash->set_flash_message($page_error);      
    }

    # build variables
    $controller_vars = array(
      'page_title' => 'Product Brands / Manufacturers',
      'icon_name' => 'fa fa-thumbs-o-up',
    );
    $template_vars = array(
      'mfgs' => $mfgs_a,
      'sl_no' => $slno,
      'search_params' => $search_params,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,      
    );

    return array($this->template->render_view('mfgs-list', $template_vars), $controller_vars);        
  }

  */
}