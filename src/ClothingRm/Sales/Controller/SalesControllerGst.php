<?php 

namespace ClothingRm\Sales\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Sales\Model\Sales;
use ClothingRm\Suppliers\Model\Supplier;
use ClothingRm\Taxes\Model\Taxes;
use ClothingRm\Finance\Model\CreditNote;
use ClothingRm\PromoOffers\Model\PromoOffers;
use User\Model\User;

class SalesControllerGst {
	protected $views_path;

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->supplier_model = new Supplier;
    $this->taxes_model = new Taxes;
    $this->flash = new Flash;
    $this->sales = new Sales;
    $this->offers_model = new PromoOffers;
    $this->user_model = new User;
    $this->cn_model = new CreditNote;
    $this->promo_key = 'pr0M0Aplied';
	}

  # create sales transaction
  public function salesEntryGstAction(Request $request) {

    # -------- initialize variables ---------------------------
    $ages_a = $credit_days_a = $qtys_a = $offers_raw = [];
    $form_data = $errors = $form_errors = $offers = [];
    $taxes = $loc_states = [];

    $page_error = $page_success = $promo_key = '';

    # ---------- end of initializing variables -----------------
    for($i=1;$i<=500;$i++) {
      if($i <= 365) {
        $credit_days_a[$i] = $i;
      }
      $qtys_a[$i] = $i;
    }

    # ---------- get tax percents from api ----------------------
    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }

    # ---------- get live offers from api -------------------------
    $offers_response = $this->offers_model->getLivePromoOffers();
    if($offers_response['status'] && count($offers_response['response']['offers'])>0 ) {
      $offers_raw = $offers_response['response']['offers'];
      foreach($offers_raw as $offer_details) {
        $offers[$offer_details['promoCode']] = $offer_details['promoCode'];
      }
    }

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations();

    # ---------- get sales executive names from api -----------------------
    $result = $this->user_model->get_users(['userType' => 4, 'locationCode' => $_SESSION['lc']]);
    if($result['status']) {
      $users = $result['users'];
      foreach($users as $user_details) {
        $sa_executives[$user_details['uuid']] = $user_details['userName'];
      }
    } else {
      $sa_executives = [];
    }       

    # ---------- check for last bill printing ----
    if($request->get('lastBill') && is_numeric($request->get('lastBill'))) {
      $bill_to_print = $request->get('lastBill');
    } else {
      $bill_to_print = 0;
    }

    # ------------------------------------- check for form Submission --------------------------------
    # ------------------------------------------------------------------------------------------------
    if(count($request->request->all()) > 0) {

      $submitted_promo_key = !is_null($request->get('promoKey')) ? $request->get('promoKey') : '';
      $derived_promo_key = !is_null($request->get('promoCode')) ? md5($request->get('promoCode').$this->promo_key) : '';

      // dump($submitted_promo_key, $derived_promo_key, $request->get('promoCode'));

      # check whether the url contains promo code or not.
      if(in_array($request->get('promoCode'), $offers) && $submitted_promo_key === $derived_promo_key && $submitted_promo_key !== '' && $derived_promo_key !== '') {

        $cleaned_params = $request->request->all();
        $api_response = $this->sales->create_sale($cleaned_params);
        if($api_response['status']) {
          $this->flash->set_flash_message('Sales transaction with Bill No. <b>`'.$api_response['billNo'].'`</b> created successfully.');
          Utilities::redirect('/sales/entry?lastBill='.$api_response['billNo']);
        } else {
          $page_error = $api_response['apierror'];
          $this->flash->set_flash_message($page_error,1);  
        }

      } else {
      # if no promo code is available process the transaction as is.
        $form_data = $request->request->all();
        $validation = $this->_validate_form_data($form_data);
        if( $validation['status'] === false ) {
          $form_errors = $validation['errors'];
          $this->flash->set_flash_message('You have errors in this Bill. Please fix them before saving this transaction.',1);
        } else {
          # check if any promo code is applied.
          $cleaned_params = $validation['cleaned_params'];
          $promo_code = $cleaned_params['promoCode'];
          if($promo_code === '') {
            $api_response = $this->sales->create_sale($cleaned_params);
            if($api_response['status']) {
              $this->flash->set_flash_message('Sales transaction with Bill No. <b>`'.$api_response['billNo'].'`</b> created successfully.');
            } else {
              $page_error = $api_response['apierror'];
              $this->flash->set_flash_message($page_error,1);
              $form_data = $cleaned_params;  
            }
          # if applied we will validate and re-process the form.
          } else {
            $promo_code_processing = $this->_apply_promo_code($cleaned_params, $promo_code, $offers_raw, $offers);
            // dump($promo_code_processing);
            // exit;
            # if the promo code applied successfully reload the page with processed data.
            if($promo_code_processing['status']) {
              $cleaned_params['itemDetails'] = $promo_code_processing['processed_data'];
              $this->flash->set_flash_message("Promo Code `$promo_code` applied successfully. Click on <span style='color:red;font-weight:bold;'><i class='fa fa-save'></i> Save &amp; Print</span> button at the bottom of this page to save this transaction.");
              $promo_key = md5($promo_code.$this->promo_key);
            } else {
              $promo_error = $promo_code_processing['reason'];
              $this->flash->set_flash_message("Unable to apply Promo Code : $promo_error", 1);  
            }
            $form_data = $cleaned_params;
          }
        }
      }
    }

    # --------------- build variables -----------------
    $controller_vars = array(
      'page_title' => 'Create Invoice',
      'icon_name' => 'fa fa-inr',
    );
    
    # ---------------- prepare form variables. ---------
    $template_vars = array(
      'payment_methods' => Constants::$PAYMENT_METHODS_RC,
      'offers' => array(''=>'Choose')+$offers,
      'offers_raw' => $offers_raw,
      'qtys_a' => array(0=>'Choose')+$qtys_a,
      'errors' => $form_errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'btn_label' => 'Create Invoice',
      'taxes' => $taxes,
      'form_data' => $form_data,
      'bill_to_print' => $bill_to_print,
      'taxcalc_opt_a' => array('e'=>'Exluding Item Rate', 'i' => 'Including Item Rate'),
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'sa_executives' => $sa_executives,
      'promo_key' => $promo_key,
    );

    return array($this->template->render_view('sales-entry-gst', $template_vars),$controller_vars);
  }

  # update sales transaction
  public function salesUpdateGstAction(Request $request) {

    # -------- initialize variables ---------------------------
    $ages_a = $credit_days_a = $qtys_a = $offers_raw = [];
    $form_data = $errors = $form_errors = [];
    $taxes = $loc_states = [];

    $page_error = $page_success = '';

    $offers = [1 => 'Coupon Code', 2 => 'Promo Offer', 3 => 'Credit Note'];

    # ---------- end of initializing variables -----------------
    for($i=1;$i<=500;$i++) {
      if($i<=365) {
        $credit_days_a[$i] = $i;
      }
      $qtys_a[$i] = $i;
    }

    # ---------- get tax percents from api ----------------------
    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }

    # ---------- get live offers from api -------------------------
    $offers_response = $this->offers_model->getLivePromoOffers();
    if($offers_response['status'] && count($offers_response['response']['offers'])>0 ) {
      $offers_raw = $offers_response['response']['offers'];
      foreach($offers_raw as $offer_details) {
        $offers[$offer_details['promoCode']] = $offer_details['offerName'];
      }
    }

    # ---------- get location codes from api -----------------------
    $client_locations_resp = $this->user_model->get_client_locations();
    if($client_locations_resp['status']) {
      foreach($client_locations_resp['clientLocations'] as $loc_details) {
        $client_locations[$loc_details['locationCode']] = $loc_details['locationName'];
      }
    }    

    # ---------- check for last bill printing ----
    if($request->get('lastBill') && is_numeric($request->get('lastBill'))) {
      $bill_to_print = $request->get('lastBill');
    } else {
      $bill_to_print = 0;
    }

    # ---------- check for print format ----
    if( $request->get('pFormat') && $request->get('lastBill') && is_numeric($request->get('lastBill')) ) {
      $print_format = 'bill';
    } else {
      $print_format = '';
    }

    # ------------------------------------- check for form Submission --------------------------------
    # ------------------------------------------------------------------------------------------------
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $validation = $this->_validate_form_data($form_data);
      if( $validation['status'] === false ) {
        $form_errors = $validation['errors'];
        $this->flash->set_flash_message('You have errors in this Bill. Please fix them before saving this transaction.',1);
      } else {
        # hit api and process sales transaction.
        $cleaned_params = $validation['cleaned_params'];
        $api_response = $this->sales->create_sale($cleaned_params);
        if($api_response['status'] === true) {
          $this->flash->set_flash_message('Sales transaction with Bill No. <b>`'.$api_response['billNo'].'`</b> created successfully.');
          Utilities::redirect('/sales/entry');
        } else {
          $page_error = $api_response['apierror'];
        }
      }
    }

    # --------------- build variables -----------------
    $controller_vars = array(
      'page_title' => 'Create Invoice',
      'icon_name' => 'fa fa-inr',
    );
    
    # ---------------- prepare form variables. ---------
    $template_vars = array(
      'payment_methods' => Constants::$PAYMENT_METHODS_RC,
      'offers' => array(0=>'Choose')+$offers,
      'offers_raw' => $offers_raw,
      'qtys_a' => array(0=>'Choose')+$qtys_a,
      'errors' => $form_errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'btn_label' => 'Create Invoice',
      'taxes' => $taxes,
      'form_data' => $form_data,
      'bill_to_print' => $bill_to_print,
      'print_format' => $print_format,
      'taxcalc_opt_a' => array('e'=>'Exluding Item Rate', 'i' => 'Including Item Rate'),
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'sa_executives' => array('' => 'Choose'),
    );

    return array($this->template->render_view('sales-update-gst', $template_vars),$controller_vars);
  }  

  # sales register
  public function salesListAction(Request $request) {

    $total_pages = $total_records = $record_count = $page_no = 0;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';
    
    $search_params = $sales_a = $query_totals = $client_locations = [];

    $client_locations = Utilities::get_client_locations();
 
    $page_no = 1;
    $per_page = 200;

    $payment_methods = Constants::$PAYMENT_METHODS_RC;

    # ---------- get sales executive names from api -----------------------
    $result = $this->user_model->get_users(['userType' => 4, 'locationCode' => $_SESSION['lc']]);
    if($result['status']) {
      $users = $result['users'];
      foreach($users as $user_details) {
        $sa_executives[$user_details['uuid']] = $user_details['userName'];
      }
    } else {
      $sa_executives = [];
    }    

    # check for filter variables.
    if(is_null($request->get('pageNo'))) {
      $search_params['pageNo'] = 1;
    } else {
      $search_params['pageNo'] = $page_no = (int)$request->get('pageNo');
    }
    if(is_null($request->get('perPage'))) {
      $search_params['perPage'] = 200;
    } else {
      $search_params['perPage'] = $per_page = (int)$request->get('perPage');
    }
    if(is_null($request->get('fromDate'))) {
      $search_params['fromDate'] = date("d-m-Y");
    } else {
      $search_params['fromDate'] = $request->get('fromDate');
    }
    if(is_null($request->get('toDate'))) {
      $search_params['toDate'] = date("d-m-Y");
    } else {
      $search_params['toDate'] = $request->get('toDate');
    }        
    if(is_null($request->get('paymentMethod'))) {
      $search_params['paymentMethod'] = 99;
    } elseif( !is_null($request->get('paymentMethod')) && (int)$request->get('paymentMethod')===99) {
      $search_params['paymentMethod'] = '';
    } elseif(is_numeric($request->get('paymentMethod'))) {
      $search_params['paymentMethod'] = Utilities::clean_string($request->get('paymentMethod'));
    } else {
      $search_params['paymentMethod'] = '';      
    }
    if($search_params['paymentMethod'] === 99) {
      $search_params['paymentMethod'] = '';
    }
    if(is_null($request->get('locationCode'))) {
      $search_params['locationCode'] = '';
    } else {
      $search_params['locationCode'] = $request->get('locationCode');
    }
    if(is_null($request->get('saExecutiveCode'))) {
      $search_params['saExecutiveCode'] = '';
    } else {
      $search_params['saExecutiveCode'] = $request->get('saExecutiveCode');
    }

    # hit API.
    $sales_api_call = $this->sales->get_sales($page_no,$per_page,$search_params);
    $api_status = $sales_api_call['status'];

    # check api status
    if($api_status) {
      if(count($sales_api_call['sales'])>0) {
        $slno = Utilities::get_slno_start(count($sales_api_call['sales']), $per_page, $page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;
        }
        if($sales_api_call['total_pages']<$page_links_to_end) {
          $page_links_to_end = $sales_api_call['total_pages'];
        }
        if($sales_api_call['record_count'] < $per_page) {
          $to_sl_no = ($slno+$sales_api_call['record_count'])-1;
        }
        $sales_a = $sales_api_call['sales'];
        $total_pages = $sales_api_call['total_pages'];
        $total_records = $sales_api_call['total_records'];
        $record_count = $sales_api_call['record_count'];
        $query_totals = $sales_api_call['query_totals'];
      } else {
        $page_error = $sales_api_call['apierror'];
      }
    } else {
      $this->flash->set_flash_message($sales_api_call['apierror'], 1);
    }

    # prepare form variables.
    $template_vars = array(
      'sales' => $sales_a,
      'payment_methods' => array(99=>'All payment methods')+$payment_methods,
      'total_pages' => $total_pages,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'query_totals' => $query_totals,
      'client_locations' => ['' => 'All Stores'] + $client_locations,
      'sa_executives' =>  array('' => 'All executives') + $sa_executives,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Sales Register',
      'icon_name' => 'fa fa-inr',
    );

    # render template
    return array($this->template->render_view('sales-register', $template_vars),$controller_vars);
  }

  # search sale bills.
  public function saleBillsSearchAction(Request $request) {
    $search_params = $bills = [];
    $slno = 0;
    $page_success = $page_error = '';

    $search_by_a = array(
      'billno' => 'Bill No.',
      'date' => 'Date',
      'name' => 'Name',
      'mobile' => 'Mobile No.',
    );

    # check for filter variables.
    if(count($request->request->all()) > 0) {
      $search_params = $request->request->all();

      # hit Api.
      $sales_api_call = $this->sales->search_sale_bills($search_params);
      $api_status = $sales_api_call['status'];

      # check api status
      if($api_status) {
        # check whether we got products or not.
        if(count($sales_api_call['bills'])>0) {
          $bills = $sales_api_call['bills'];
        } else {
          $page_error = $sales_api_call['apierror'];
        }
      } else {
        $page_error = $sales_api_call['apierror'];
      }
    }

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations();    

    # prepare form variables.
    $template_vars = array(
      'bills' => $bills,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'search_params' => $search_params,
      'search_by_a' => [''=>'Choose'] + $search_by_a,
      'sl_no' => 1,
      'client_locations' => $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Search Sale Bills',
      'icon_name' => 'fa fa-search',
    );

    # render template
    return array($this->template->render_view('search-sale-bills', $template_vars),$controller_vars);    
  }

  # remove sales transaction.
  public function salesRemoveAction(Request $request) {
    if($request->get('salesCode') && $request->get('salesCode')!='') {
      $sales_code = Utilities::clean_string($request->get('salesCode'));
    } else {
      Utilities::set_flash_message("Invalid Sales Code",1);
      Utilities::redirect('/sales/list');
    }

    # initiate sales model.
    $sales = new Sales; 
    
    $sales_response = $sales->get_sales_details($sales_code);
    if($sales_response['status']===true) {
      $sales_details = $sales_response['saleDetails'];
    } else {
      Utilities::set_flash_message("Invalid Sales Code",1);
      Utilities::redirect('/sales/list');
    }

    $remove_response = $sales->removeSalesTransaction($sales_code);
    if($remove_response['status']===true) {
      $flash_message = "Sale transaction with code [$sales_code] removed successfully";
      Utilities::set_flash_message($flash_message);            
    } else {
      $flash_message = "Unable to remove Sales transaction. Please contact Administrator.";
      Utilities::set_flash_message($flash_message,1);
    }
    
    Utilities::redirect('/sales/list');
  }

  /******************************* private functions should go from here ******************************/
  private function _validate_form_data($form_data=[]) {

    // dump($form_data);
    // $coupon_code = Utilities::clean_string($form_data['']);

    $cleaned_params = $form_errors = [];
    $tot_bill_value = $tot_discount_amount = $tot_billable_value = $tot_tax_value = $round_off = $net_pay = 0;

    $payment_methods_a = Constants::$PAYMENT_METHODS_RC;
    $one_item_found = $split_payment_found = false;

    $coupon_code = '';
    $sale_date = Utilities::clean_string($form_data['saleDate']);
    $payment_method = (int)Utilities::clean_string($form_data['paymentMethod']);
    $discount_method = isset($form_data['discountMethod']) ? Utilities::clean_string($form_data['discountMethod']) : '';
    $mobile_no = Utilities::clean_string($form_data['mobileNo']);
    $name = Utilities::clean_string($form_data['name']);
    $tax_calc_option = Utilities::clean_string($form_data['taxCalcOption']);
    $card_no = Utilities::clean_string($form_data['cardNo']);
    $auth_code = Utilities::clean_string($form_data['authCode']);
    $split_payment_cash = isset($form_data['splitPaymentCash']) ? Utilities::clean_string($form_data['splitPaymentCash']) : 0;
    $split_payment_card = isset($form_data['splitPaymentCard']) ? Utilities::clean_string($form_data['splitPaymentCard']) : 0;
    $split_payment_cn = isset($form_data['splitPaymentCn']) ? Utilities::clean_string($form_data['splitPaymentCn']) : 0;
    $cn_no = isset($form_data['cnNo']) ? Utilities::clean_string($form_data['cnNo']) : 0;
    $item_details = $form_data['itemDetails'];
    $executive_id = Utilities::clean_string($form_data['saExecutive']);
    $referral_code = is_numeric($form_data['refCode']) ? Utilities::clean_string($form_data['refCode']) : 0;
    $promo_code = isset($form_data['promoCode']) ? Utilities::clean_string($form_data['promoCode']) : '';

    # validate location code
    if( isset($form_data['locationCode']) && ctype_alnum($form_data['locationCode']) ) {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['locationCode'] = 'Invalid location code.';
    }

    # validate transaction details.
    if( in_array($payment_method, array_keys($payment_methods_a)) === false ) {
      $form_errors['paymentMethod'] = 'Invalid payment method.';
    } else {
      $cleaned_params['paymentMethod'] = $payment_method;
    }

    # validate mobile number.
    if( $mobile_no !== '' && !is_numeric($mobile_no) && strlen($mobile_no) !== 10) {
      $form_errors['mobileNo'] = 'Invalid mobile number.';
    } else {
      $cleaned_params['mobileNo'] = $mobile_no;
    }

    # validate name.
    if( $name !== '' && !ctype_alpha(str_replace(' ', '', $name)) ) {
      $form_errors['name'] = 'Invalid name.';      
    } else {
      $cleaned_params['name'] = $name;      
    }

    # validate card no, auth code when the card value is more than zero
    if( ($split_payment_card > 0 || $payment_method === 1) && ($card_no === '' || $auth_code === '') ) {
      $form_errors['cardNo'] = 'Card number is mandatory for Card or Split payment.';
      $form_errors['authCode'] = 'Auth code is mandatory for Card or Split payment.';
    }

    # validate card no.
    if($card_no !== '' && (!is_numeric($card_no) || strlen($card_no) !== 4) ) {
      $form_errors['cardNo'] = 'Invalid card number.';
    } else {
      $cleaned_params['cardNo'] = $card_no;
      $cleaned_params['authCode'] = $auth_code;
    }

    if((int)$payment_method === 2) {
      if($split_payment_cash > 0) {
        $split_payment_found = true;
      }
      if($split_payment_card > 0) {
        $split_payment_found = true;
      }
      if($split_payment_cn > 0) {
        $split_payment_found = true;
        if($cn_no <= 0) {
          $form_errors['cnNo'] = 'Credit note number is required.';
        } else {
          # validate credit note no.
          $cn_details = $this->cn_model->get_credit_note_details([], $cn_no);
          if($cn_details['status'] === false) {
            $form_errors['paymentMethod'] = 'Invalid Credit Note No.';
          } else {
            $balance_value = $cn_details['data']['vocDetails']['balanceValue'];
            if($balance_value < $split_payment_cn) {
              $form_errors['paymentMethod'] = 'Credit note value exceeds than actual.';
            }
          }
        }
      }
      if(!$split_payment_found) {
        $form_errors['paymentMethod'] = 'At least one payment mode value is required.';      
      }
    }

    # validate item details.
    for($item_key=0;$item_key<=9;$item_key++) {
      if($item_details['itemName'][$item_key] !== '') {
        $one_item_found = true;

        $item_name = Utilities::clean_string($item_details['itemName'][$item_key]);
        $item_ava_qty = Utilities::clean_string($item_details['itemAvailQty'][$item_key]);
        $item_sold_qty = Utilities::clean_string($item_details['itemSoldQty'][$item_key]);
        $item_rate = Utilities::clean_string($item_details['itemRate'][$item_key]);
        $item_discount = Utilities::clean_string($item_details['itemDiscount'][$item_key]);
        $item_tax_percent = Utilities::clean_string($item_details['itemTaxPercent'][$item_key]);
        $lot_no = Utilities::clean_string($item_details['lotNo'][$item_key]);

        $item_total = round($item_sold_qty * $item_rate, 2);
        $item_total_billable = $item_total - $item_discount;
        if($tax_calc_option === 'i') {
          $item_tax_amount = 0;
        } else {
          $item_tax_amount = round(($item_total_billable*$item_tax_percent)/100, 2);
        }

        $tot_billable_value += $item_total_billable;
        $tot_discount_amount += $item_discount;
        $tot_tax_value += $item_tax_amount;

        # validate item name.
        if(ctype_alnum(str_replace([' ', '-', '_'], ['','',''], $item_name)) === false) {
          $form_errors['itemDetails']['itemName'][$item_key] = 'Invalid item name.';
        } else {
          $cleaned_params['itemDetails']['itemName'][$item_key] = $item_name;
        }

        # validate item avaiable qty.
        if(!is_numeric($item_ava_qty) || $item_ava_qty<=0) {
          $form_errors['itemDetails']['itemAvailQty'][$item_key] = 'Invalid available qty.';
        } else {
          $cleaned_params['itemDetails']['itemAvailQty'][$item_key] = $item_ava_qty;
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

        # validate item discount.
        if($item_discount !== '' && (!is_numeric($item_discount) || $item_discount < 0) ) {
          $form_errors['itemDetails']['itemDiscount'][$item_key] = 'Invalid item discount.';
        } else {
          $cleaned_params['itemDetails']['itemDiscount'][$item_key] = $item_discount;
        }

        # validate item tax.
        if(!is_numeric($item_tax_percent) || $item_tax_percent<0) {
          $form_errors['itemDetails']['itemTaxPercent'][$item_key] = 'Invalid tax rate.';
        } else {
          $cleaned_params['itemDetails']['itemTaxPercent'][$item_key] = $item_tax_percent;
        }

        # validate lot no.
        if(ctype_alnum($lot_no)) {
          $cleaned_params['itemDetails']['lotNo'][$item_key] = $lot_no;
        } else {
          $form_errors['itemDetails']['lotNo'][$item_key] = 'Invalid Lot No.';  
        }        

        # validate if sold qty. is more than available qty.
        if($item_sold_qty>$item_ava_qty) {
          $form_errors['itemDetails']['itemSoldQty'][$item_key] = 'Invalid sold qty.';
        }
      }
    }

    $net_pay = round($tot_billable_value + $tot_tax_value, 0);
    // dump('net pay is...'.$net_pay);

    # if no items are available through an error.
    if($one_item_found === false) {
      $form_errors['itemDetails']['itemName'][0] = 'Invalid item name.';
      $form_errors['itemDetails']['itemAvailQty'][0] = 'Invalid available qty.';
      $form_errors['itemDetails']['itemSoldQty'][0] = 'Invalid sold qty.';
      $form_errors['itemDetails']['itemRate'][0] = 'Invalid item rate.';
      $form_errors['itemDetails']['itemDiscount'][0] = 'Invalid item rate.';      
      $form_errors['itemDetails']['itemTaxPercent'][0] = 'Invalid tax rate.';      
    }

    # validate payment method.
    if($payment_method === 2 && ($split_payment_card <= 0 && $split_payment_cash <= 0 && $split_payment_cn <= 0) ) {
      $form_errors['paymentMethod'] = 'Cash, Card or Cnote payment value is required.';
    } elseif($payment_method === 1 || $payment_method === 0) {
      $cleaned_params['splitPaymentCard'] = 0;
      $cleaned_params['splitPaymentCash'] = 0;
      $cleaned_params['splitPaymentCn']   = 0;
    } else {
      $cleaned_params['splitPaymentCard'] = $split_payment_card;
      $cleaned_params['splitPaymentCash'] = $split_payment_cash;
      $cleaned_params['splitPaymentCn'] = $split_payment_cn;
    }

    if($payment_method === 2 && ( (float)$split_payment_card + $split_payment_cash + $split_payment_cn !== (float)$net_pay) ) {
      $form_errors['paymentMethod'] = 'Cash / Card / Cnote value must be equal to bill value.';      
    }

    if($promo_code !== '' && !ctype_alnum($promo_code)) {
      $form_errors['promoCode'] = 'Invalid promo code format.';
    } else {
      $cleaned_params['promoCode'] = $promo_code;
    }

    # add misc parameters.
    $cleaned_params['saleDate'] = $sale_date;
    $cleaned_params['taxCalcOption'] = $tax_calc_option;
    $cleaned_params['saExecutiveId'] = $executive_id;
    $cleaned_params['cnNo'] = $cn_no;
    $cleaned_params['refCode'] = $referral_code;

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

  private function _apply_promo_code($form_data=[], $promo_code='', $offers_raw=[]) {
    $sel_promo_type = '';
    $promo_params = [];

    $promo_codes_a = array_column($offers_raw, 'promoCode');
    $promo_types_a = array_column($offers_raw, 'promoType');
    $promo_combined = array_combine($promo_codes_a, $promo_types_a);

    # check whether the promo code is valid or not.
    if(!in_array($promo_code, $promo_codes_a)) {
      return [
        'status' => false,
        'reason' => 'Invalid promo code applied.',
      ];
    } else {
      $sel_promo_type = $promo_combined[$promo_code];
      $sel_promo_key = array_search($promo_code, array_column($offers_raw, 'promoCode'));
      if(isset($offers_raw[$sel_promo_key])) {
        $promo_params = $offers_raw[$sel_promo_key];
      }
    }

    switch($sel_promo_type) {
      case 0:
        $processed_data = $this->_apply_discount_on_item($form_data['itemDetails'], $promo_params);
        break;
      case 1:
        $processed_data = $this->_apply_price_off_for_items($form_data['itemDetails'], $promo_params);
        break;
      case 2:
        $processed_data = $this->_apply_bill_discount($form_data['itemDetails'], $promo_params);
        break;
    }

    return $processed_data;
  }
  
  private function _apply_bill_discount($item_details = [], $offer_details=[]) {
    $gross_amount = 0;

    $total_rows = count($item_details['itemName']);

    for($i=0; $i < $total_rows; $i++) {
      $item_rate = $item_details['itemRate'][$i];
      $item_qty = $item_details['itemSoldQty'][$i];
      $item_amount = $item_rate*$item_qty;
      $gross_amount += $item_amount;
    }

    $discount_allowed_bill_value = $offer_details['billValue'];
    $discount_percent = $offer_details['discountPercent'];

    if($gross_amount < $discount_allowed_bill_value) {
      return [
        'status' => false,
        'reason' => 'Bill value must be greater than or equal to Rs.'.$discount_allowed_bill_value,
      ];
    }

    # apply discount on each item we have.
    for($i=0; $i < $total_rows; $i++) {
      $item_rate = $item_details['itemRate'][$i];
      $item_qty = $item_details['itemSoldQty'][$i];
      $item_value = $item_rate * $item_qty;
      if($item_value>0) {
        $item_discount = round(($item_value*$discount_percent)/100, 2);
        $item_details['itemDiscount'][$i] = $item_discount;
      }
    }
    return [
      'status' => true,
      'processed_data' => $item_details,
    ];    
  }

  private function _apply_price_off_for_items($item_details = [], $offer_details=[]) {
    $total_qty_per_order = $offer_details['totalQty'] + 0;
    $free_qty_per_order = $offer_details['freeQty'] + 0;
    $total_rows = count($item_details['itemName']);

    # find max mrp from rates array.
    $max_mrp = max($item_details['itemRate']) + 0;
    $max_mrp_key = array_search($max_mrp, $item_details['itemRate']);

    // dump($max_mrp_key, $max_mrp);
    // exit;

    if($total_qty_per_order !== $total_rows) {
      return [
        'status' => false,
        'reason' => 'Total items must be [ '.$total_qty_per_order.' ] items per order. Same item with more than 1 qty. not allowed.',
      ];
    }

    # apply discount on each item we have.
    for($i=0; $i < $total_rows; $i++) {
      $item_rate = $item_details['itemRate'][$i];
      $item_value = $item_rate * 1;
      if( (int)$i !== (int)$max_mrp_key) {
        $item_details['itemDiscount'][$i] = $item_value;
      }
      $item_details['itemSoldQty'][$i] = 1;
    }

    // dump($item_details);
    // exit;
    return [
      'status' => true,
      'processed_data' => $item_details,
    ];
  }

}