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
use BusinessUsers\Model\BusinessUsers;
use User\Model\User;
use SalesCategory\Model\SalesCategory;

class SalesEntryWoBarcode {
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
    $this->bu_model = new BusinessUsers;
    $this->sc_model = new SalesCategory;
	}

  // create sales transaction
  public function salesEntryAction(Request $request) {

    # -------- initialize variables ---------------------------
    $ages_a = $credit_days_a = $qtys_a = $offers_raw = [];
    $form_data = $errors = $form_errors = $offers = [];
    $taxes = $loc_states = $agents_a = [];
    $sa_categories = ['' => 'Choose'];
    $customer_types = Constants::$CUSTOMER_TYPES;    

    $page_error = $page_success = $promo_key = '';

    // ---------- end of initializing variables -----------------
    for($i=1;$i<=365;$i++) {
      $credit_days_a[$i] = $i;
    }

    $no_of_rows = 15;

    // ---------- get tax percents from api ----------------------
    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }

    // ---------- get live offers from api -------------------------
    $offers_response = $this->offers_model->getLivePromoOffers();
    if($offers_response['status'] && count($offers_response['response']['offers'])>0 ) {
      $offers_raw = $offers_response['response']['offers'];
      foreach($offers_raw as $offer_details) {
        $offers[$offer_details['promoCode']] = $offer_details['promoCode'];
      }
    }

    // get sales categories
    $sa_categories += $this->_get_sa_categories();

    // ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(false, false, true);

    // ---------- get business users --------------------------------
    if($_SESSION['__utype'] !== 3) {
      $sexe_response = $this->bu_model->get_business_users(['userType' => 92, 'returnActiveOnly' => 1, 'ignoreLocation' => 1]);
    } else {
      $sexe_response = $this->bu_model->get_business_users(['userType' => 92, 'locationCode' => $_SESSION['lc'], 'returnActiveOnly' => 1, 'ignoreLocation' => 1]);      
    }

    # ---------- get agents ----------------------------
    $agents_response = $this->bu_model->get_business_users(['userType' => 90, 'returnActiveOnly' => 1, 'ignoreLocation' => 1]);
    if($agents_response['status']) {
      foreach($agents_response['users'] as $user_details) {
        if($user_details['cityName'] !== '') {
          $agents_a[$user_details['userCode']] = $user_details['userName'].'__'.substr($user_details['cityName'],0,10);
        } else {
          $agents_a[$user_details['userCode']] = $user_details['userName'];
        }
      }
    }    

    // dump($sexe_response);
    // exit;

    if($sexe_response['status']) {
      foreach($sexe_response['users'] as $user_details) {
        $sa_executives[$user_details['userCode']] = $user_details['userName'];
      }
    } else {
      $sa_executives = [];
    }

    # ---------- check for last bill printing ----
    if($request->get('lastBill')) {
      $bill_to_print = $request->get('lastBill');
    } else {
      $bill_to_print = '';
    }
    if( !is_null($request->get('format')) && ($request->get('format') === 'bill' || $request->get('format') === 'invoice')) {
      $print_format = $request->get('format');
    } else {
      $print_format = 'bill';
    }    
    # ------------------------------------- check for form Submission --------------------------------
    # ------------------------------------------------------------------------------------------------
    if(count($request->request->all()) > 0) {

      $submitted_promo_key = !is_null($request->get('promoKey')) ? $request->get('promoKey') : '';
      $derived_promo_key = !is_null($request->get('promoCode')) ? md5($request->get('promoCode').$this->promo_key) : '';

      $op = $request->get('op');
      if($op === 'SaveandPrintInvoice') {
        $print_format = 'invoice';
      } else {
        $print_format = 'bill';
      }      

      // dump($submitted_promo_key, $derived_promo_key, $request->get('promoCode'));

      # check whether the url contains promo code or not.
      if(in_array($request->get('promoCode'), $offers) && $submitted_promo_key === $derived_promo_key && $submitted_promo_key !== '' && $derived_promo_key !== '') {

        $cleaned_params = $request->request->all();
        $api_response = $this->sales->create_sale($cleaned_params);
        if($api_response['status']) {
          $this->flash->set_flash_message('Sales transaction with Bill No. <b>`'.$api_response['billNo'].'`</b> created successfully.');
          Utilities::redirect('/sales/entry?lastBill='.$api_response['invoiceCode'].'&format='.$print_format);
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
              Utilities::redirect('/sales/entry?lastBill='.$api_response['invoiceCode'].'&format='.$print_format);              
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
              $this->flash->set_flash_message("Promo Code `$promo_code` applied successfully. Click on <span style='color:red;font-weight:bold;'><i class='fa fa-save'></i> Save Bill &amp; Print</span> button at the bottom of this page to save this transaction.");
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
      'offers' => array(''=>'Choose') + $offers,
      'offers_raw' => $offers_raw,
      'credit_days_a' => [0=>'Choose'] + $credit_days_a,
      'errors' => $form_errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'btn_label' => 'Create Invoice',
      'print_format' => $print_format,      
      'taxes' => $taxes,
      'form_data' => $form_data,
      'bill_to_print' => $bill_to_print,
      'taxcalc_opt_a' => array('e'=>'Exluding Item Rate', 'i' => 'Including Item Rate'),
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'sa_executives' => [''=>'Choose'] + $sa_executives,
      'promo_key' => $promo_key,
      'customer_types' => $customer_types,
      'no_of_rows' => $no_of_rows,
      'sa_categories' => $sa_categories,
      'wallets' => array(''=>'Choose') + Constants::$WALLETS,
      'agents' => [''=>'Choose']+$agents_a,
    );

    return array($this->template->render_view('sales-entry-wo-barcode', $template_vars),$controller_vars);
  }

  // update sales transaction
  public function salesUpdateAction(Request $request) {

    if(!Utilities::is_admin()) {
      $this->flash->set_flash_message(Constants::$ACCESS_DENIED, 1);
      Utilities::redirect('/sales/list');
    }

    // -------- initialize variables ---------------------------
    $ages_a = $credit_days_a = $qtys_a = $offers_raw = [];
    $form_data = $errors = $form_errors = $offers = [];
    $taxes = $loc_states = [];
    $agents_a = [];
    $sa_categories = ['' => 'Choose'];

    $customer_types = Constants::$CUSTOMER_TYPES;    

    $page_error = $page_success = $promo_key = '';

    if($request->get('salesCode') && $request->get('salesCode')!=='') {
      $sales_code = Utilities::clean_string($request->get('salesCode'));
      $sales_response = $this->sales->get_sales_details($sales_code);
      if($sales_response['status']) {
        // check no of items. if items are more than 15 redirect to barcode mode.
        if(isset($sales_response['itemDetails']) && count($sales_response['itemDetails']) > 15) {
          $flash->set_flash_message('There are more than 15 products in this Invoice. You should edit this invoice using Barcode only.', 1);
          Utilities::redirect('/sales/list');
        }
        $form_data = $this->_map_invoice_data_with_form_data($sales_response['saleDetails']);
      } else {
        $page_error = $sales_response['apierror'];
        $flash->set_flash_message($page_error,1);
        Utilities::redirect('/sales/list');
      }
      // dump($sales_response, $form_data);
      // exit;
    } else {
      $this->flash->set_flash_message('Invalid Invoice No. (or) Invoice No. does not exist.',1);
      Utilities::redirect('/sales/list');
    }    

    # ---------- end of initializing variables -----------------
    for($i=1;$i<=365;$i++) {
      $credit_days_a[$i] = $i;
    }

    $no_of_rows = 15;

    # ---------- get tax percents from api ----------------------
    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }

    // get sales categories
    $sa_categories += $this->_get_sa_categories();    

    // ---------- get live offers from api -------------------------
    $offers_response = $this->offers_model->getLivePromoOffers();
    if($offers_response['status'] && count($offers_response['response']['offers'])>0 ) {
      $offers_raw = $offers_response['response']['offers'];
      foreach($offers_raw as $offer_details) {
        $offers[$offer_details['promoCode']] = $offer_details['promoCode'];
      }
    }

    // ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations();

    // ---------- get business users --------------------------------
    if($_SESSION['__utype'] !== 3) {
      $sexe_response = $this->bu_model->get_business_users(['userType' => 92, 'returnActiveOnly' => 1, 'ignoreLocation' => 1]);
    } else {
      $sexe_response = $this->bu_model->get_business_users(['userType' => 92, 'locationCode' => $_SESSION['lc'], 'returnActiveOnly' => 1, 'ignoreLocation' => 1]);
    }

    // dump($sexe_response);
    // exit;

    if($sexe_response['status']) {
      foreach($sexe_response['users'] as $user_details) {
        $sa_executives[$user_details['userCode']] = $user_details['userName'];
      }
    } else {
      $sa_executives = [];
    }

    # ---------- get agents ----------------------------
    $agents_response = $this->bu_model->get_business_users(['userType' => 90, 'returnActiveOnly' => 1, 'ignoreLocation' => 1]);
    if($agents_response['status']) {
      foreach($agents_response['users'] as $user_details) {
        if($user_details['cityName'] !== '') {
          $agents_a[$user_details['userCode']] = $user_details['userName'].'__'.substr($user_details['cityName'],0,10);
        } else {
          $agents_a[$user_details['userCode']] = $user_details['userName'];
        }
      }
    }    

    # ---------- check for last bill printing ----
    if($request->get('lastBill')) {
      $bill_to_print = $request->get('lastBill');
    } else {
      $bill_to_print = '';
    }
    if( !is_null($request->get('format')) && ($request->get('format') === 'bill' || $request->get('format') === 'invoice')) {
      $print_format = $request->get('format');
    } else {
      $print_format = 'bill';
    }    
    # ------------------------------------- check for form Submission --------------------------------
    # ------------------------------------------------------------------------------------------------
    if(count($request->request->all()) > 0) {

      $submitted_promo_key = !is_null($request->get('promoKey')) ? $request->get('promoKey') : '';
      $derived_promo_key = !is_null($request->get('promoCode')) ? md5($request->get('promoCode').$this->promo_key) : '';

      $op = $request->get('op');
      if($op === 'SaveandPrintInvoice') {
        $print_format = 'invoice';
      } else {
        $print_format = 'bill';
      }      

      // dump($submitted_promo_key, $derived_promo_key, $request->get('promoCode'));

      # check whether the url contains promo code or not.
      if(in_array($request->get('promoCode'), $offers) && $submitted_promo_key === $derived_promo_key && $submitted_promo_key !== '' && $derived_promo_key !== '') {

        $cleaned_params = $request->request->all();
        $api_response = $this->sales->update_sale($cleaned_params, $sales_code);
        if($api_response['status']) {
          $this->flash->set_flash_message('Sales transaction with Bill No. <b>`'.$api_response['billNo'].'`</b> updated successfully.');
          Utilities::redirect('/sales/entry?lastBill='.$api_response['invoiceCode'].'&format='.$print_format);
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
            $api_response = $this->sales->update_sale($cleaned_params, $sales_code);
            if($api_response['status']) {
              $this->flash->set_flash_message('Sales transaction with Bill No. <b>`'.$api_response['billNo'].'`</b> updated successfully.');
              Utilities::redirect('/sales/entry?lastBill='.$api_response['invoiceCode'].'&format='.$print_format);              
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
              $this->flash->set_flash_message("Promo Code `$promo_code` applied successfully. Click on <span style='color:red;font-weight:bold;'><i class='fa fa-save'></i> Save Bill &amp; Print</span> button at the bottom of this page to save this transaction.");
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
      'page_title' => 'Update Invoice',
      'icon_name' => 'fa fa-inr',
    );
    
    # ---------------- prepare form variables. ---------
    $template_vars = array(
      'payment_methods' => Constants::$PAYMENT_METHODS_RC,
      'offers' => array(''=>'Choose') + $offers,
      'offers_raw' => $offers_raw,
      'credit_days_a' => array(0=>'Choose') + $credit_days_a,
      'errors' => $form_errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'btn_label' => 'Update Invoice',
      'print_format' => $print_format,      
      'taxes' => $taxes,
      'form_data' => $form_data,
      'bill_to_print' => $bill_to_print,
      'taxcalc_opt_a' => array('e'=>'Exluding Item Rate', 'i' => 'Including Item Rate'),
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'sa_executives' => [''=>'Choose'] + $sa_executives,
      'promo_key' => $promo_key,
      'customer_types' => $customer_types,
      'no_of_rows' => $no_of_rows,
      'ic' => $sales_code,
      'sa_categories' => $sa_categories,
      'wallets' => array(''=>'Choose') + Constants::$WALLETS,
      'agents' => [''=>'Choose'] + $agents_a,
    );

    return array($this->template->render_view('sales-update-wo-barcode', $template_vars),$controller_vars);
  }

  // update sales transaction
  public function salesViewAction(Request $request) {

    # -------- initialize variables ---------------------------
    $ages_a = $credit_days_a = $qtys_a = $offers_raw = [];
    $form_data = $errors = $form_errors = $offers = [];
    $taxes = $loc_states = $agents_a = [];
    $customer_types = Constants::$CUSTOMER_TYPES;    

    $page_error = $page_success = $promo_key = '';

    if($request->get('salesCode') && $request->get('salesCode')!=='') {
      $sales_code = Utilities::clean_string($request->get('salesCode'));
      $sales_response = $this->sales->get_sales_details($sales_code);
      if($sales_response['status']) {
        $form_data = $this->_map_invoice_data_with_form_data($sales_response['saleDetails']);
      } else {
        $page_error = $sales_response['apierror'];
        $this->flash->set_flash_message($page_error,1);
        Utilities::redirect('/sales/list');
      }
    } else {
      $this->flash->set_flash_message('Invalid Invoice No. (or) Invoice No. does not exist.',1);
      Utilities::redirect('/sales/list');
    }    

    # ---------- end of initializing variables -----------------
    for($i=1;$i<=365;$i++) {
      $credit_days_a[$i] = $i;
    }

    $no_of_rows = 15;

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

    # ---------- get business users --------------------------------
    if($_SESSION['__utype'] !== 3) {
      $sexe_response = $this->bu_model->get_business_users(['userType' => 92]);
    } else {
      $sexe_response = $this->bu_model->get_business_users(['userType' => 92, 'locationCode' => $_SESSION['lc']]);      
    }

    // dump($sexe_response);
    // exit;

    if($sexe_response['status']) {
      foreach($sexe_response['users'] as $user_details) {
        $sa_executives[$user_details['userCode']] = $user_details['userName'];
      }
    } else {
      $sa_executives = [];
    }

    # ---------- get agents ----------------------------
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

    # --------------- build variables -----------------
    $controller_vars = array(
      'page_title' => 'View Invoice :: Bill No - '.$form_data['billNo'],
      'icon_name' => 'fa fa-inr',
    );
    
    # ---------------- prepare form variables. ---------
    $template_vars = array(
      'payment_methods' => Constants::$PAYMENT_METHODS_RC,
      'offers' => array(''=>'Choose') + $offers,
      'offers_raw' => $offers_raw,
      'credit_days_a' => array(0=>'Choose') + $credit_days_a,
      'errors' => $form_errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'btn_label' => 'Update Invoice',
      'taxes' => $taxes,
      'form_data' => $form_data,
      'taxcalc_opt_a' => array('e'=>'Exluding Item Rate', 'i' => 'Including Item Rate'),
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'sa_executives' => ['Choose'] + $sa_executives,
      'promo_key' => $promo_key,
      'customer_types' => $customer_types,
      'no_of_rows' => count($form_data['itemDetails']['itemName']),
      'ic' => $sales_code,
      'agents' => [''=>'Choose'] + $agents_a,
    );

    return array($this->template->render_view('sales-view-invoice', $template_vars),$controller_vars);
  }  

  // sales register
  public function salesListAction(Request $request) {

    $total_pages = $total_records = $record_count = $page_no = 0;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';
    
    $search_params = $sales_a = $query_totals = $client_locations = [];
    $location_ids = $location_codes = [];
    $page_no = 1; $per_page = 200;

    $payment_methods = Constants::$PAYMENT_METHODS_RC;

    # ---------- get location codes from api ------------------
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
      $search_params['perPage'] = 200;
    } else {
      $search_params['perPage'] = $per_page = (int)$request->get('perPage');
    }
    if(is_null($request->get('fromDate'))) {
      $search_params['fromDate'] = date("01-m-Y");
    } else {
      $search_params['fromDate'] = Utilities::clean_string($request->get('fromDate'));
    }
    if(is_null($request->get('toDate'))) {
      $search_params['toDate'] = date("d-m-Y");
    } else {
      $search_params['toDate'] = Utilities::clean_string($request->get('toDate'));
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
      $search_params['locationCode'] = Utilities::clean_string($request->get('locationCode'));
    }
    if(is_null($request->get('saExecutiveCode'))) {
      $search_params['saExecutiveCode'] = '';
    } else {
      $search_params['saExecutiveCode'] = Utilities::clean_string($request->get('saExecutiveCode'));
    }
    if(is_null($request->get('custName'))) {
      $search_params['custName'] = '';
    } else {
      $search_params['custName'] = Utilities::clean_string($request->get('custName'));
    }

    $search_params['walletID'] = !is_null($request->get('walletID')) ? Utilities::clean_string($request->get('walletID')) : '';

    # ---------- get sales executive names from api -----------------------
    if($_SESSION['__utype'] !== 3) {
      $sexe_response = $this->bu_model->get_business_users(['userType' => 92]);
    } else {
      $sexe_response = $this->bu_model->get_business_users(['userType' => 92, 'locationCode' => $_SESSION['lc']]);      
    }
    if($sexe_response['status']) {
      foreach($sexe_response['users'] as $user_details) {
        $sa_executives[$user_details['userCode']] = $user_details['userName'];
      }
    } else {
      $sa_executives = [];
    }

    // hit API.
    $sales_api_call = $this->sales->get_sales($page_no,$per_page,$search_params);
    $api_status = $sales_api_call['status'];

    // check api status
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
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'wallets' => ['99' => 'All UPI/EMI Cards'] + Constants::$WALLETS,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Sales Register',
      'icon_name' => 'fa fa-inr',
    );

    # render template
    return array($this->template->render_view('sales-register', $template_vars),$controller_vars);
  }

  // sales register
  public function gatePassRegisterAction(Request $request) {

    $total_pages = $total_records = $record_count = $page_no = 0;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';
    
    $search_params = $sales_a = $query_totals = $client_locations = [];
    $location_ids = $location_codes = [];
    $page_no = 1; $per_page = 200;

    $payment_methods = Constants::$PAYMENT_METHODS_RC;

    # ---------- get location codes from api ------------------
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
      $search_params['perPage'] = 200;
    } else {
      $search_params['perPage'] = $per_page = (int)$request->get('perPage');
    }
    if(is_null($request->get('fromDate'))) {
      $search_params['fromDate'] = date("01-m-Y");
    } else {
      $search_params['fromDate'] = Utilities::clean_string($request->get('fromDate'));
    }
    if(is_null($request->get('toDate'))) {
      $search_params['toDate'] = date("d-m-Y");
    } else {
      $search_params['toDate'] = Utilities::clean_string($request->get('toDate'));
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
      $search_params['locationCode'] = Utilities::clean_string($request->get('locationCode'));
    }
    if(is_null($request->get('saExecutiveCode'))) {
      $search_params['saExecutiveCode'] = '';
    } else {
      $search_params['saExecutiveCode'] = Utilities::clean_string($request->get('saExecutiveCode'));
    }
    if(is_null($request->get('custName'))) {
      $search_params['custName'] = '';
    } else {
      $search_params['custName'] = Utilities::clean_string($request->get('custName'));
    }

    $search_params['walletID'] = !is_null($request->get('walletID')) ? Utilities::clean_string($request->get('walletID')) : '';

    # ---------- get sales executive names from api -----------------------
    if($_SESSION['__utype'] !== 3) {
      $sexe_response = $this->bu_model->get_business_users(['userType' => 92]);
    } else {
      $sexe_response = $this->bu_model->get_business_users(['userType' => 92, 'locationCode' => $_SESSION['lc']]);      
    }
    if($sexe_response['status']) {
      foreach($sexe_response['users'] as $user_details) {
        $sa_executives[$user_details['userCode']] = $user_details['userName'];
      }
    } else {
      $sa_executives = [];
    }

    // hit API.
    $sales_api_call = $this->sales->get_sales($page_no,$per_page,$search_params);
    $api_status = $sales_api_call['status'];

    // check api status
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
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'wallets' => ['99' => 'All UPI/EMI Cards'] + Constants::$WALLETS,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Sales Register',
      'icon_name' => 'fa fa-inr',
    );

    # render template
    return array($this->template->render_view('sales-register-gatepass', $template_vars),$controller_vars);
  }  

  // search sale bills
  public function saleBillsSearchAction(Request $request) {
    $search_params = $bills = [];
    $slno = 0;
    $page_success = $page_error = '';

    $search_by_a = array(
      'billno' => 'Bill No.',
      'date' => 'Date (yyyy-mm-dd)',
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

  public function salesShippingEntryAction(Request $request) {

    $page_error = $page_success = $default_location = '';
    $form_data = $client_locations = $form_errors = $submitted_data = [];
    $no_of_rows = 0;
    $yes_no_options = [0=>'No', 1=>'Yes'];

    // check if sales code is available or not.
    if($request->get('salesCode') && $request->get('salesCode') !== '') {
      $sales_code = Utilities::clean_string($request->get('salesCode'));
      $sales_response = $this->sales->get_sales_details($sales_code);
      // dump($sales_response);
      // exit;
      if($sales_response['status']) {
        $form_data = $sales_response['saleDetails'];
      } else {
        $page_error = $sales_response['apierror'];
        $this->flash->set_flash_message($page_error,1);
        Utilities::redirect('/sales/list');
      }
    } else {
      $this->flash->set_flash_message('Invalid Invoice No. (or) Invoice No. does not exist.',1);
      Utilities::redirect('/sales/list');
    }    
    
    // check form is submitted or not.
    if( count($request->request->all()) > 0) {
      $sales_code = Utilities::clean_string($request->get('salesCode'));      
      $submitted_data = $request->request->all();
      $validation = $this->_validate_shipping_info($submitted_data);
      if($validation['status'] === false ) {
        $form_errors = $validation['errors'];
        $this->flash->set_flash_message('You have errors in this Form.',1);
      } else {
        $cleaned_params = $validation['cleaned_params'];
        $api_response = $this->sales->update_shipping_info($cleaned_params, $sales_code);
        if($api_response['status']) {
          $message = '<i class="fa fa-check" aria-hidden="true"></i> Shipping details were updated successfully';
          if($api_response['smsFlag']) {
            $message .= ' <i class="fa fa-mobile" aria-hidden="true"></i> SMS sent successfully.';
          } elseif($api_response['smsStatus'] !== null) {
            $message .= '<span class="error-msg"><i class="fa fa-mobile" aria-hidden="true"></i> '.$api_response['smsStatus'].'</span>';
          }
          $this->flash->set_flash_message($message);
          Utilities::redirect('/sales/shipping-info/'.$sales_code);
        } else {
          $page_error = $api_response['apierror'];
          $this->flash->set_flash_message($page_error,1);
          $submitted_data = $cleaned_params;  
        }
      }
    }

    // get states
    $states_a = Constants::$LOCATION_STATES;
    asort($states_a);    

    // get client locations
    $client_locations_resp = $this->user_model->get_client_locations();
    if($client_locations_resp['status']) {
      foreach($client_locations_resp['clientLocations'] as $loc_details) {
        $client_locations[$loc_details['locationCode']] = $loc_details['locationName'];
      }
    }    

    // --------------- build variables -----------------
    $controller_vars = array(
      'page_title' => 'Add Shipping Information',
      'icon_name' => 'fa fa-truck',
    );
    
    // ---------------- prepare form variables. ---------
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_data' => $form_data,
      'submitted_data' => $submitted_data,
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'states' => [0=>'Choose'] + $states_a,
      'yes_no_options' => $yes_no_options,
      'errors' => $form_errors,
    );

    return array($this->template->render_view('sales-shipping-info', $template_vars),$controller_vars);
  }

  private function _validate_shipping_info($form_data = []) {
    $cleaned_params = $form_errors = [];

    $transporter_name = Utilities::clean_string($form_data['transporterName']);
    $lr_nos = Utilities::clean_string($form_data['lrNo']);
    $lr_date = Utilities::clean_string($form_data['lrDate']);
    $challan_no = Utilities::clean_string($form_data['challanNo']);
    
    $address1 = Utilities::clean_string($form_data['address1']);
    $city_name = Utilities::clean_string($form_data['cityName']);
    $state_id = Utilities::clean_string($form_data['stateID']);
    $pincode = Utilities::clean_string($form_data['pincode']);
    $mobile_no = Utilities::clean_string($form_data['mobileNo']);
    $phones = Utilities::clean_string($form_data['phones']);
    $way_bill_no = Utilities::clean_string($form_data['wayBillNo']);
    $send_sms = Utilities::clean_string($form_data['sendSMS']);
    $bill_no = Utilities::clean_string($form_data['billNo']);

    // dump($form_data);
    // exit;

    if($bill_no === '') {
      $form_errors['billNo'] = 'Invalid bill no.';
    } else {
      $cleaned_params['billNo'] = $bill_no;
    }

    // if($transporter_name === '') {
    //   $form_errors['transporterName'] = 'Invalid transporter name.';
    // } else {
      $cleaned_params['transporterName'] = $transporter_name;
    // }
    // if($lr_nos === '') {
    //   $form_errors['lrNo'] = 'Invalid LR No.';
    // } else {
      $cleaned_params['lrNos'] = $lr_nos;
    // }
    // if($lr_date === '') {
    //   $form_errors['lrDate'] = $lr_date;
    // } else {
      $cleaned_params['lrDate'] = $lr_date;
    // }
    if($address1 === '') {
      $form_errors['address1'] = 'Invalid address.';
    } else {
      $cleaned_params['address1'] = $address1;
    }
    if($city_name === '') {
      $form_errors['cityName'] = 'Invalid City name.';
    } else {
      $cleaned_params['cityName'] = $city_name;
    }
    if($state_id === '') {
      $form_errors['stateID'] = 'Invalid State name.';
    } else {
      $cleaned_params['stateID'] = $state_id;
    }
    if($mobile_no !== '' && Utilities::validateMobileNo($mobile_no)) {
      $cleaned_params['mobileNo'] = $mobile_no;
      if((int)$send_sms === 0 || (int)$send_sms === 1) {
        $cleaned_params['sendSMS'] = (int)$send_sms;
      } else {
        $form_errors['sendSMS'] = 'Invalid message choice.';
      }
    } else {
      $cleaned_params['sendSMS'] = 0;
      $cleaned_params['mobileNo'] = '';
    }

    /*
    if($challan_no === '') {
      $form_errors['challanNo'] = 'Invalid Challan No.';
    } else {
      $cleaned_params['challanNo'] = $challan_no;
    }*/
    $cleaned_params['wayBillNo'] = $way_bill_no;
    $cleaned_params['phones'] = $phones;
    $cleaned_params['pincode'] = $pincode;

    if(count($form_errors) > 0) {
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

    // dump($form_data);
    // exit;
    // $coupon_code = Utilities::clean_string($form_data['']);

    $cleaned_params = $form_errors = [];
    $tot_bill_value = $tot_discount_amount = $tot_billable_value = $tot_tax_value = $round_off = $net_pay = 0;

    $payment_methods_a = Constants::$PAYMENT_METHODS_RC;
    $one_item_found = false;
    $split_payment_found = 0;

    $coupon_code = '';
    $customer_types = array_keys(Constants::$CUSTOMER_TYPES);

    $sale_date = Utilities::clean_string($form_data['saleDate']);
    $payment_method = (int)Utilities::clean_string($form_data['paymentMethod']);
    $discount_method = isset($form_data['discountMethod']) ? Utilities::clean_string($form_data['discountMethod']) : '';
    $mobile_no = Utilities::clean_string($form_data['mobileNo']);
    $name = Utilities::clean_string($form_data['name']);
    $tax_calc_option = Utilities::clean_string($form_data['taxCalcOption']);
    $card_no = Utilities::clean_string($form_data['cardNo']);
    $auth_code = Utilities::clean_string($form_data['authCode']);
    $split_payment_cash = isset($form_data['splitPaymentCash']) && is_numeric($form_data['splitPaymentCash']) ? Utilities::clean_string($form_data['splitPaymentCash']) : 0;
    $split_payment_card = isset($form_data['splitPaymentCard']) && is_numeric($form_data['splitPaymentCard'])  ? Utilities::clean_string($form_data['splitPaymentCard']) : 0;
    $split_payment_cn = isset($form_data['splitPaymentCn']) && is_numeric($form_data['splitPaymentCn'])  ? Utilities::clean_string($form_data['splitPaymentCn']) : 0;
    $split_payment_wallet = isset($form_data['splitPaymentWallet']) && is_numeric($form_data['splitPaymentWallet'])  ? Utilities::clean_string($form_data['splitPaymentWallet']) : 0;    $cn_no = isset($form_data['cnNo']) ? Utilities::clean_string($form_data['cnNo']) : 0;
    $item_details = $form_data['itemDetails'];
    $executive_id = isset($form_data['saExecutive']) && $form_data['saExecutive'] !== '' ? Utilities::clean_string($form_data['saExecutive']) : '';
    $referral_code = is_numeric($form_data['refCode']) ? Utilities::clean_string($form_data['refCode']) : 0;
    $promo_code = isset($form_data['promoCode']) ? Utilities::clean_string($form_data['promoCode']) : '';
    $from_indent = isset($form_data['fi']) ? 'y': 'n';
    $customer_type = $form_data['customerType'];
    $credit_days = isset($form_data['saCreditDays']) ? Utilities::clean_string($form_data['saCreditDays']) : 0;
    $remarks_invoice = isset($form_data['remarksInvoice']) ? Utilities::clean_string($form_data['remarksInvoice']) : '';
    $sales_category =  isset($form_data['salesCategory']) ? Utilities::clean_string($form_data['salesCategory']) : '';
    $billing_rate = isset($form_data['billingRate']) ? Utilities::clean_string($form_data['billingRate']) : 'mrp';
    $ic = isset($form_data['ic']) ? Utilities::clean_string($form_data['ic']) : '';
    $indent_no = isset($form_data['indentNo']) ? Utilities::clean_string($form_data['indentNo']) : '';

    $packing_charges =  Utilities::clean_string($form_data['packingCharges']);
    $shipping_charges = Utilities::clean_string($form_data['shippingCharges']);
    $insurance_charges = Utilities::clean_string($form_data['insuranceCharges']);
    $other_charges = Utilities::clean_string($form_data['otherCharges']);
    $transporter_name = Utilities::clean_string($form_data['transporterName']);
    $lr_no = Utilities::clean_string($form_data['lrNos']);
    $lr_date = Utilities::clean_string($form_data['lrDate']);
    $chalan_no = Utilities::clean_string($form_data['challanNo']);

    $agent_code = Utilities::clean_string($form_data['agentCode']);

    $wallet_id = isset($form_data['walletID']) ? Utilities::clean_string($form_data['walletID']) : 0;
    $wallet_ref_no = isset($form_data['walletRefNo']) ? Utilities::clean_string($form_data['walletRefNo']) : '';
    $is_combo_bill = isset($form_data['isComboBill']) ? Utilities::clean_string($form_data['isComboBill']) : 0;

    // validate customer type
    if( in_array($customer_type, $customer_types) ) {
      $cleaned_params['customerType'] = $customer_type;
    } else {
      $form_errors['customerType'] = 'Invalid customer type.';      
    }

    // validate location code
    if( isset($form_data['locationCode']) && ctype_alnum($form_data['locationCode']) ) {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['locationCode'] = 'Invalid location code.';
    }

    // validate transaction details.
    if( in_array($payment_method, array_keys($payment_methods_a)) === false ) {
      $form_errors['paymentMethod'] = 'Invalid payment method.';
    } else {
      $cleaned_params['paymentMethod'] = $payment_method;
    }

    // validate mobile number.
    if( $mobile_no !== '' && !is_numeric($mobile_no) && strlen($mobile_no) !== 10) {
      $form_errors['mobileNo'] = 'Invalid mobile number.';
    } else {
      $cleaned_params['mobileNo'] = $mobile_no;
    }

    // validate name.
    if($name !== '') {
      $cleaned_params['name'] = $name;      
    }

    //validate various charges.
    if($packing_charges !== '' && !is_numeric($packing_charges)) {
      $form_errors['packingCharges'] = 'Invalid input. Must be numeric.';
    } else {
      $cleaned_params['packingCharges'] = $packing_charges;
    }
    if($shipping_charges !== '' && !is_numeric($shipping_charges)) {
      $form_errors['shippingCharges'] = 'Invalid input. Must be numeric.';
    } else {
      $cleaned_params['shippingCharges'] = $shipping_charges;
    }
    if($insurance_charges !== '' && !is_numeric($insurance_charges)) {
      $form_errors['insuranceCharges'] = 'Invalid input. Must be numeric.';
    } else {
      $cleaned_params['insuranceCharges'] = $insurance_charges;
    }
    if($other_charges !== '' && !is_numeric($other_charges)) {
      $form_errors['otherCharges'] = 'Invalid input. Must be numeric.';
    } else {
      $cleaned_params['otherCharges'] = $other_charges;
    }

    // validate for credit days.
    if( (int)$payment_method === 3 && (int)$credit_days === 0) {
      $form_errors['saCreditDays'] = 'Credit days are required for Credit payment method.';
    } else {
      $cleaned_params['saCreditDays'] = $credit_days;
    }

    // validate card no, auth code when the card value is more than zero
    if( ($split_payment_card > 0 || $payment_method === 1) && ($card_no === '' || $auth_code === '') ) {
      $form_errors['cardNo'] = 'Card number is mandatory for Card or Split payment.';
      $form_errors['authCode'] = 'Auth code is mandatory for Card or Split payment.';
    }

    // validate card no.
    if($card_no !== '' && (!is_numeric($card_no) || strlen($card_no) !== 4) ) {
      $form_errors['cardNo'] = 'Invalid card number.';
    } else {
      $cleaned_params['cardNo'] = $card_no;
      $cleaned_params['authCode'] = $auth_code;
    }

    if((int)$payment_method === 2) {
      if($split_payment_cash > 0) {
        $split_payment_found += 1;
      }
      if($split_payment_card > 0) {
        $split_payment_found += 1;
      }
      if($split_payment_wallet > 0) {
        $split_payment_found += 1;
      }      
      if($split_payment_cn > 0) {
        $split_payment_found += 1;
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
      if($split_payment_found <= 1) {
        $form_errors['paymentMethod'] = 'Cash Value , Credit Value and Credit Note Value. Two payment modes are required for Split Payment.';
      }
    }

    // validate item details.
    for($item_key=0;$item_key<count($item_details['itemName']);$item_key++) {
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

        $cleaned_params['itemDetails']['itemName'][$item_key] = $item_name;

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
        if($item_sold_qty > $item_ava_qty) {
          $form_errors['itemDetails']['itemSoldQty'][$item_key] = 'Invalid sold qty.';
        }
      }
    }

    $net_pay = round($tot_billable_value + $tot_tax_value, 0);
    // dump('net pay is...'.$net_pay);

    // if no items are available through an error.
    if($one_item_found === false) {
      $form_errors['itemDetails']['itemName'][0] = 'Invalid item name.';
      $form_errors['itemDetails']['itemAvailQty'][0] = 'Invalid available qty.';
      $form_errors['itemDetails']['itemSoldQty'][0] = 'Invalid sold qty.';
      $form_errors['itemDetails']['itemRate'][0] = 'Invalid item rate.';
      $form_errors['itemDetails']['itemDiscount'][0] = 'Invalid item rate.';      
      $form_errors['itemDetails']['itemTaxPercent'][0] = 'Invalid tax rate.';      
    }

    // validate payment method.
    if($payment_method === 2 && ($split_payment_card <= 0 && $split_payment_cash <= 0 && $split_payment_cn <= 0 && $split_payment_wallet <= 0) ) {
      $form_errors['paymentMethod'] = 'Cash, Card, Wallet or Cnote payment value is required.';
    } elseif($payment_method === 1 || $payment_method === 0) {
      $cleaned_params['splitPaymentCard'] = 0;
      $cleaned_params['splitPaymentCash'] = 0;
      $cleaned_params['splitPaymentCn'] = 0;
      $cleaned_params['splitPaymentWallet'] = 0;
    } elseif($payment_method === 4) {
      if($wallet_id >=1 && $wallet_id <=8 ) {
        $cleaned_params['walletID'] = $wallet_id;
        $cleaned_params['walletRefNo'] = $wallet_ref_no;
      } else {
        $form_errors['walletID'] = 'Invalid Wallet name';
      }
    } else {
      $cleaned_params['splitPaymentCard'] = $split_payment_card;
      $cleaned_params['splitPaymentCash'] = $split_payment_cash;
      $cleaned_params['splitPaymentCn'] = $split_payment_cn;
      $cleaned_params['splitPaymentWallet'] = $split_payment_wallet;
      if($wallet_id >=1 && $wallet_id <=8 ) {
        $cleaned_params['walletID'] = $wallet_id;
        $cleaned_params['walletRefNo'] = $wallet_ref_no;
      }
    }

    if($payment_method === 2 && ( (float)$split_payment_card + $split_payment_cash + $split_payment_cn + $split_payment_wallet !== (float)$net_pay) ) {
      $form_errors['paymentMethod'] = 'Cash / Card / Cnote / Wallet value must be equal to bill value.';      
    }

    if($promo_code !== '' && !ctype_alnum($promo_code)) {
      $form_errors['promoCode'] = 'Invalid promo code format.';
    } else {
      $cleaned_params['promoCode'] = $promo_code;
    }

    // add misc parameters.
    if(Utilities::is_valid_fin_date($sale_date)) {
      $cleaned_params['saleDate'] = $sale_date;
    } else {
      $form_errors['saleDate'] = 'Invoice Date is out of Financial year dates.';
    }

    $cleaned_params['taxCalcOption'] = $tax_calc_option;
    $cleaned_params['saExecutiveId'] = $executive_id;
    $cleaned_params['cnNo'] = $cn_no;
    $cleaned_params['refCode'] = $referral_code;
    $cleaned_params['transporterName'] = $transporter_name;
    $cleaned_params['lrNos'] = $lr_no;
    $cleaned_params['lrDate'] = $lr_date;
    $cleaned_params['challanNo'] = $chalan_no;
    $cleaned_params['fromIndent'] = $from_indent;
    $cleaned_params['remarksInvoice'] = $remarks_invoice;  
    $cleaned_params['salesCategory'] = $sales_category;
    $cleaned_params['isComboBill'] = $is_combo_bill;
    $cleaned_params['agentCode'] = $agent_code;
    $cleaned_params['billingRate'] = $billing_rate;
    $cleaned_params['indentCode'] = $ic;
    $cleaned_params['indentNo'] = $indent_no;

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

  // apply promo code
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
  
  // apply bill discount
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

  // apply price off for items
  private function _apply_price_off_for_items($item_details = [], $offer_details = []) {

    $discount_applied_items = 0;

    $total_qty_per_order = floatval($offer_details['totalQty']);
    $free_qty_per_order = floatval($offer_details['freeQty']);
    $total_rows = count($item_details['itemName']);
    $total_bill_qty = floatval(array_sum($item_details['itemSoldQty']));
    $min_mrp = floatval(min($item_details['itemRate']));

    $max_mrp_count = floatval(0);
    $max_mrp_items_required = $total_qty_per_order - $free_qty_per_order;

    // check for promo offer validity.
    if($total_qty_per_order !== $total_bill_qty) {
      return [
        'status' => false,
        'reason' => 'Total qty. must be [ '.$total_qty_per_order.' ] per bill.',
      ];
    }    

    // if total items are one in the bill process and return.
    if($total_rows === 1) {
      $item_rate = $item_details['itemRate'][0];
      $discount_amount = $item_rate * $free_qty_per_order;
      $item_details['itemDiscount'][0] = $discount_amount;
      return [
        'status' => true,
        'processed_data' => $item_details,
      ];
    }

    $new_item_details = $final_item_details = [
      'itemName' => [],
      'itemAvailQty' => [],
      'itemSoldQty' => [],
      'itemRate' => [],
      'itemDiscount' => [],
      'itemTaxPercent' => [],
      'lotNo' => [],
    ];

    $item_cntr = 0;

    // loop through item qtys. and expand qtys.
    foreach($item_details['itemName'] as $item_key => $item_name) {
      $item_avail_qty = $item_details['itemAvailQty'][$item_key];
      $sold_qty = $item_details['itemSoldQty'][$item_key];
      $item_rate = $item_details['itemRate'][$item_key];
      $item_discount = 0;
      $item_tax_percent = $item_details['itemTaxPercent'][$item_key];
      $lot_no = $item_details['lotNo'][$item_key];
      // expand items for applying promo code.
      for($j=0; $j < $sold_qty; $j++) {
        $new_item_details['itemName'][$item_cntr] = $item_name;
        $new_item_details['itemAvailQty'][$item_cntr] = $item_avail_qty;
        $new_item_details['itemSoldQty'][$item_cntr] = 1;
        $new_item_details['itemRate'][$item_cntr] = $item_rate;
        $new_item_details['itemDiscount'][$item_cntr] = $item_discount;
        $new_item_details['itemTaxPercent'][$item_cntr] = $item_tax_percent;
        $new_item_details['lotNo'][$item_cntr] = $lot_no;
        $item_cntr++;
      }
    }


    // for($i=0; $i < $total_rows-1; $i++) {
    //   $item_name = $item_details['itemName'][$i];
    // }

    // check max mrp criteria met or not.
    foreach($new_item_details['itemRate'] as $key => $rate) {
      if($rate > $min_mrp) {
        $max_mrp_count++;
      }
    }

    // throw error if max mrp criteria not met.
    if($max_mrp_count < $max_mrp_items_required) {
      return [
        'status' => false,
        'reason' => 'Max MRP items must be [ '.$max_mrp_items_required.' ] in this Promo code. We found only [ '.$max_mrp_count.' ] in this Bill.',
      ];
    }

    arsort($new_item_details['itemRate']);

    foreach($new_item_details['itemRate'] as $item_key => $item_rate) {
      $final_item_details['itemName'][$item_key] = $new_item_details['itemName'][$item_key];
      $final_item_details['itemAvailQty'][$item_key] = $new_item_details['itemAvailQty'][$item_key];
      $final_item_details['itemSoldQty'][$item_key] = $new_item_details['itemSoldQty'][$item_key];
      $final_item_details['itemRate'][$item_key] = $new_item_details['itemRate'][$item_key];
      $final_item_details['itemDiscount'][$item_key] = $new_item_details['itemDiscount'][$item_key];
      $final_item_details['itemTaxPercent'][$item_key] = $new_item_details['itemTaxPercent'][$item_key];
      $final_item_details['lotNo'][$item_key] = $new_item_details['lotNo'][$item_key];      
    }

    $item_rates_reverse = array_reverse($final_item_details['itemRate'], true);
    foreach($item_rates_reverse as $item_key => $item_rate) {
      if($discount_applied_items < $free_qty_per_order) {
        $final_item_details['itemDiscount'][$item_key] = $item_rate;
        $discount_applied_items++;
      }      
    }

    // dump($final_item_details);
    // exit;

    return [
      'status' => true,
      'processed_data' => $final_item_details,
    ];
  }

  // map invoice data with form data
  public function _map_invoice_data_with_form_data($invoice_details = []) {
    $cleaned_params = [];

    $sale_items = $invoice_details['itemDetails'];
    unset($invoice_details['itemDetails']);

    if(isset($invoice_details['customerType'])) {
      $customer_type = $invoice_details['customerType'] === 'b' ? 'b2b' : 'b2c';
      $invoice_details['customerType'] = $customer_type;
    }

    foreach($sale_items as $key => $item_details) {

      // we need to add ordered qty to closing qty while editing invoice.
      $available_qty = $item_details['closingQty'] + $item_details['itemQty'];
 
      $cleaned_params['itemDetails']['itemName'][$key] = $item_details['itemName'];
      $cleaned_params['itemDetails']['itemSoldQty'][$key] = $item_details['itemQty'];
      $cleaned_params['itemDetails']['itemRate'][$key] = $item_details['mrp'];
      $cleaned_params['itemDetails']['itemTaxPercent'][$key] = $item_details['taxPercent'];
      $cleaned_params['itemDetails']['itemAvailQty'][$key] = $available_qty;
      $cleaned_params['itemDetails']['lotNo'][$key] = $item_details['lotNo'];
      $cleaned_params['itemDetails']['barcode'][$key] = $item_details['barcode'];
      $cleaned_params['itemDetails']['itemDiscount'][$key] = $item_details['discountAmount'];
      $cleaned_params['itemDetails']['cnos'][$key] = $item_details['cno'];
    }
    $cleaned_params = array_merge($invoice_details, $cleaned_params);

    return $cleaned_params;
  }

  private function _get_sa_categories() {
    $sa_categories_response = $this->sc_model->list_sales_categories(['status'=>1]);
    if($sa_categories_response['status']) {
      $cat_keys = array_column($sa_categories_response['response'], 'salesCategoryCode');
      $cat_names = array_column($sa_categories_response['response'], 'salesCategoryName');
      $sa_categories = array_combine($cat_keys, $cat_names);
      return $sa_categories;
    } else {
      return [];
    }
  }
}
