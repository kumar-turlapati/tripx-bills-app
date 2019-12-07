<?php 

namespace ClothingRm\Finance\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Finance\Model\PettyCash;
use ClothingRm\Sales\Model\Sales;

class PettyCashController {
	
  protected $views_path;

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->pettycash_model = new PettyCash;
    $this->flash = new Flash;
    $this->sales_model = new Sales;
	}

  // voucher create action
	public function pettyCashVoucherCreateAction(Request $request) {

    $page_error = $page_success = $bank_code = '';
    $submitted_data = $form_errors = [];
    $parties = array(''=>'Choose');

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations();

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all());
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $this->pettycash_model->create_pc_voucher($form_data);
        if($result['status']) {
          $message = 'Petty cash voucher created successfully with Voucher No. ` '.$result['data']['vocNo'].' `';
          $this->flash->set_flash_message($message);
        } else {
          $this->flash->set_flash_message($result['apierror'],1);          
        }
        Utilities::redirect('/fin/cash-voucher/create');
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $request->request->all();
      }
    }

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_errors' => $form_errors,
      'submitted_data' => $submitted_data,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',      
      'pc_tran_types' => ['' => 'Choose'] + Constants::$PETTY_CASH_VOC_TRAN_TYPES,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Cash Vouchers',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('pc-voucher-create', $template_vars), $controller_vars);
	}

  // voucher update action.
  public function pettyCashVoucherUpdateAction(Request $request) {

    $page_error = $page_success = $bank_code = '';
    $submitted_data = $form_errors = $location_ids = $location_codes = [];
    $parties = array(''=>'Choose');
    $voc_no = 0;
    
    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();

      $validate_form = $this->_validate_form_data($submitted_data);
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $this->pettycash_model->update_pc_voucher($form_data, $submitted_data['curVocNo']);
        if($result['status']) {
          $message = 'Payment voucher no. `'.$submitted_data['curVocNo'].'` updated successfully';
          $this->flash->set_flash_message($message);
        } else {
          $message = '<i class="fa fa-times" aria-hidden="true"></i> Error: '.$result['apierror'];
          $this->flash->set_flash_message($message, 1);          
        }
        Utilities::redirect('/fin/cash-vouchers');
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $request->request->all();
      }
    } elseif(!is_null($request->get('vocNo'))) {
      $voc_no = $request->get('vocNo');
      $location_code = $request->get('l');
      $voucher_details = $this->pettycash_model->get_pc_voucher_details($voc_no, $location_code);
      if($voucher_details['status']===false) {
        $this->flash->set_flash_message('Invalid voucher number (or) voucher not exists',1);         
        Utilities::redirect('/fin/cash-vouchers');
      } else {
        $submitted_data = $voucher_details['data']['vocDetails'];
      }
    } else {
      $this->flash->set_flash_message('Invalid voucher number (or) voucher not exists',1);         
      Utilities::redirect('/fin/payment-vouchers');
    }

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_errors' => $form_errors,
      'submitted_data' => $submitted_data,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'pc_tran_types' => ['' => 'Choose'] + Constants::$PETTY_CASH_VOC_TRAN_TYPES,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Cash Vouchers',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('pc-voucher-update', $template_vars), $controller_vars);
  }

  // vouchers list action
  public function pettyCashVoucherListAction(Request $request) {

    $locations = $vouchers = $search_params = $vouchers_a = [];
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

    $api_response = $this->pettycash_model->get_pc_vouchers($search_params);
    // dump($api_response);
    // exit;
    if($api_response['status']) {
      if(count($api_response['response']['vouchers'])>0) {
        $slno = Utilities::get_slno_start(count($api_response['response']['vouchers']),$per_page,$page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;        
        }
        if($api_response['response']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $api_response['response']['total_pages'];
        }
        if($api_response['response']['this_page'] < $per_page) {
          $to_sl_no = ($slno+$api_response['response']['this_page'])-1;
        }
        $vouchers_a = $api_response['response']['vouchers'];
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
      'vouchers' => $vouchers_a,
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
      'page_title' => 'Finance Management - Cash Vouchers List',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('pc-vouchers-list', $template_vars), $controller_vars);    
  }

  // voucher delete action
  public function pettyCashVoucherDeleteAction(Request $request) {
    $voc_no = !is_null($request->get('vocNo')) ? Utilities::clean_string($request->get('vocNo')) : '';
    $location_code = !is_null($request->get('l')) ? $request->get('l') : '';
    if(ctype_alnum($location_code) && is_numeric($voc_no)) {
      $voucher_details = $this->pettycash_model->get_pc_voucher_details($voc_no, $location_code);
      if($voucher_details['status'] === false) {
        $this->flash->set_flash_message('Invalid voucher number (or) voucher not exists',1);         
        Utilities::redirect('/fin/cash-vouchers');
      }
      $api_response = $this->pettycash_model->delete_pc_voucher($voc_no, $location_code);
      $status = $api_response['status'];
      if($status===false) {
        $message = '<i class="fa fa-times" aria-hidden="true"></i> Error: '.$api_response['apierror'];
        $this->flash->set_flash_message($message, 1);          
      } else {
        $this->flash->set_flash_message('Voucher with No. <b>'.$voc_no. '</b> deleted successfully');
      }
    } else {
      $this->flash->set_flash_message('Please choose a Voucher number to delete.');
    }

    Utilities::redirect('/fin/cash-vouchers'); 
  }

  // petty cash book
  public function pettyCashBookAction(Request $request) {
    $locations = $vouchers = $search_params = $vouchers_a = $location_names = $query_totals = [];
    $location_code = $page_error = '';
    $excess_dates = false;
    
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];
      $location_names[$location_key_a[0]] = $location_value;
    }

    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';

    // dump($request->get('fromDate'), $request->get('toDate'), $request->get('locationCode'));

    // parse request parameters.
    $per_page = 100;
    $from_date = $request->get('fromDate') !== null ? Utilities::clean_string($request->get('fromDate')):'01-'.date('m').'-'.date("Y");
    $to_date = $request->get('toDate') !== null ? Utilities::clean_string($request->get('toDate')):date("d-m-Y");
    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')):1;
    $location_code = $request->get('locationCode') !== null ? Utilities::clean_string($request->get('locationCode')) : $default_location;

    $search_params = array(
      'fromDate' => $from_date,
      'toDate' => $to_date,
      'pageNo' => $page_no,
      'perPage' => $per_page,
    );

    $diff_days_obj = date_diff( date_create(date("Y-m-d",strtotime($from_date))), date_create(date("Y-m-d",strtotime($to_date))) );
    if($diff_days_obj !== false) {
      $diff_days = $diff_days_obj->days + 1;
    }

    if($diff_days <= 61) {

      $api_response = $this->pettycash_model->get_cash_book($location_code, $search_params);
      // dump($api_response);
      // exit;
      if($api_response['status']) {
        if(count($api_response['response']['vouchers'])>0) {
          $slno = Utilities::get_slno_start(count($api_response['response']['vouchers']),$per_page,$page_no);
          $to_sl_no = $slno+$per_page;
          $slno++;
          if($page_no<=3) {
            $page_links_to_start = 1;
            $page_links_to_end = 10;
          } else {
            $page_links_to_start = $page_no-3;
            $page_links_to_end = $page_links_to_start+10;        
          }
          if($api_response['response']['total_pages']<$page_links_to_end) {
            $page_links_to_end = $api_response['response']['total_pages'];
          }
          if($api_response['response']['this_page'] < $per_page) {
            $to_sl_no = ($slno+$api_response['response']['this_page'])-1;
          }
          $vouchers_a = $api_response['response']['vouchers'];
          $query_totals = $api_response['response']['queryTotals'];
          $total_pages = $api_response['response']['total_pages'];
          $total_records = $api_response['response']['total_records'];
          $record_count = $api_response['response']['this_page'];
        } else {
          $page_error = $api_response['apierror'];
        }
      } else {
        $page_error = $api_response['apierror'];
      }
    } else {
      $excess_dates = true;
    }

    // prepare form variables.
    $template_vars = array(
      'location_code' => $location_code,
      'page_error' => $page_error,
      'vouchers' => $vouchers_a,
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
      'location_names' => $location_names,
      'sel_location' => $location_code,
      'query_totals' => $query_totals,
      'excess_dates' => $excess_dates,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Cash Book',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('pc-book', $template_vars), $controller_vars);
  }

  // sales cash to cash book register
  public function salesCashToCashBookRegister(Request $request) {
    $page_error = $page_success = '';
    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';
    $client_locations = $search_params = $day_sales = [];
    $cash_postings = [];

    // ---------- get location codes from api ------------------------------------
    $client_locations = Utilities::get_client_locations();
    
    $per_page = 100;
    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')):1;
    $from_date = $request->get('fromDate') !== null ? Utilities::clean_string($request->get('fromDate')):'01-'.date('m').'-'.date("Y");
    $to_date = $request->get('toDate') !== null ? Utilities::clean_string($request->get('toDate')):date("d-m-Y");
    $location_code = $request->get('locationCode') !== null ? Utilities::clean_string($request->get('locationCode')) : $default_location;
    
    $search_params = array(
      'fromDate' => $from_date,
      'toDate' => $to_date,
      'perPage' => $per_page,
      'pageNo' => $page_no,
      'locationCode' => $location_code,
    );

    $diff_days_obj = date_diff( date_create(date("Y-m-d",strtotime($from_date))), date_create(date("Y-m-d",strtotime($to_date))) );
    if($diff_days_obj !== false) {
      $diff_days = $diff_days_obj->days + 1;
      if($diff_days > 31) {
        $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i> From and To date difference should not be more than a month.', 1);
      } else {
        // hit API
        $sales_api_response = $this->sales_model->get_sales_summary_bymon($search_params);
        $pc_api_response = $this->pettycash_model->get_sales_cash_postings($search_params);
        if($sales_api_response['status']) {
          $day_sales_a = $sales_api_response['summary']['daywiseSales'];
          $day_sales_amounts = array_column($day_sales_a, 'cashPayments');
          $day_sales_dates = array_column($day_sales_a, 'tranDate');
          $day_sales = array_combine($day_sales_dates, $day_sales_amounts);
        } else {
          $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i> No data available. Change search filters and try again.', 1);
        }
        if($pc_api_response['status']) {
          $cash_postings = isset($pc_api_response['response']['records']) ? $pc_api_response['response']['records'] : [];
        }
      }
    }

    // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => $default_location,
      'search_params' => $search_params,
      'day_sales' => $day_sales,
      'cash_postings' => $cash_postings,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Post Cash Sales to Cash Book',
      'icon_name' => 'fa fa-arrow-right',
    );

    // render template
    return array($this->template->render_view('sales-to-cash-book-register', $template_vars), $controller_vars);
  }

  // post sales entry to cash book
  public function postSalesCashToCashBook(Request $request) {
    $response = $api_params = $errors = [];
    $validation_status = false;
    if(count($request->request->all()) > 0) {
      $posted_data = $request->request->all();
      $sales_date = Utilities::clean_string($posted_data['dt']);
      $sales_amount = Utilities::clean_string($posted_data['amt']);
      $location_code = Utilities::clean_string($posted_data['locationCode']);
      if($sales_date !== '' && Utilities::validate_date($sales_date)) {
        $api_params['saleDate'] = $sales_date;
      } else {
        $errors['dt'] = 'Invalid date.';
      }
      if(is_numeric($sales_amount) && $sales_amount > 0) {
        $api_params['salesAmount'] = $sales_amount;
      } else {
        $errors['amt'] = 'Invalid amount.';
      }
      if($location_code === '') {
        $errors['locationCode'] = 'Invalid location.';
      } else {
        $api_params['locationCode'] = $location_code;
      }
    } else {
      $errors['dt'] = 'Invalid date.';
      $errors['amt'] = 'Invalid amount.';
      $errors['locationCode'] = 'Invalid location.';
    }

    if(count($errors)>0) {
      $response = [
        'status' => false,
        'errors' => $errors,
      ];
    } else {
      // hit api and save data.
      $api_response = $this->pettycash_model->post_sc_to_cb($api_params);
      if($api_response['status']) {
        $response = [
          'status' => true,
          'vocNo' => $api_response['response']['vocNo'],
        ];
      } else {
        $response = [
          'status' => false,
          'errorMessage' => $api_response['apierror'],
        ];
      }
    }

    header("Content-type: application/json");
    echo json_encode($response);
    exit;
  }

  // validate form data
  private function _validate_form_data($form_data=array()) {
    $errors = $cleaned_params = array();
    $actions = array_keys(Constants::$PETTY_CASH_VOC_TRAN_TYPES);

    $tran_date = Utilities::clean_string($form_data['tranDate']);
    $action = Utilities::clean_string($form_data['action']);
    $amount = Utilities::clean_string($form_data['amount']);
    $narration = Utilities::clean_string($form_data['narration']);
    $ref_no = Utilities::clean_string($form_data['refNo']);
    $ref_date = Utilities::clean_string($form_data['refDate']);
    $location_code = Utilities::clean_string($form_data['locationCode']);
    $cn_no = isset($form_data['cnNo']) ? Utilities::clean_string($form_data['cnNo']) : 0;

    if(!is_numeric($amount)) {
      $errors['amount'] = 'Invalid amount.';
    } else {
      $cleaned_params['amount'] = $amount;
    }

    if(!in_array($action, $actions)) {
      $errors['action'] = 'Invalid voucher type.';
    } else {
      $cleaned_params['action'] = $action;
    }

    if($narration === '') {
      $errors['narration'] = 'Narration is required.';
    } else {
      $cleaned_params['narration'] = $narration;
    }

    if(count($errors)>0) {
      return array('status'=>false, 'errors'=>$errors);
    } else {
      $cleaned_params['tranDate'] = $tran_date;
      $cleaned_params['refNo'] = $ref_no;
      $cleaned_params['refDate'] = $ref_date;
      $cleaned_params['locationCode'] = $location_code;
      $cleaned_params['cnNo'] = $cn_no;
      return array('status'=>true, 'cleaned_params'=>$cleaned_params);
    }
  }
}