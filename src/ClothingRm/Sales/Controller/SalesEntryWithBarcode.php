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
use ClothingRm\SalesIndent\Model\SalesIndent;
use BusinessUsers\Model\BusinessUsers;
use User\Model\User;
use SalesCategory\Model\SalesCategory; 

class salesEntryWithBarcode {

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
    $this->sindent_model = new SalesIndent;
    $this->bu_model = new BusinessUsers;
    $this->sc_model = new SalesCategory;
  }

  // create sales transaction
  public function salesEntryAction(Request $request) {

    // -------- initialize variables ---------------------------
    $ages_a = $credit_days_a = $qtys_a = $offers_raw = [];
    $form_data = $errors = $form_errors = $offers = [];
    $taxes = $loc_states = [];
    $customer_types = Constants::$CUSTOMER_TYPES;
    $sa_categories = ['' => 'Choose'];

    $from_indent = false;

    $page_error = $page_success = $promo_key = '';
    $print_format = 'bill';
    $indent_code = '';

    // get sales categories
    $sa_categories += $this->_get_sa_categories();

    // ---------- end of initializing variables -----------------
    for($i=1;$i<=500;$i++) {
      if($i <= 365) {
        $credit_days_a[$i] = $i;
      }
      $qtys_a[$i] = $i;
    }

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

    // ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations();

    // ---------- get sales executives ------------------------------
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

    // ---------- check for last bill printing ----
    if( !is_null($request->get('lastBill')) ) {
      $bill_to_print = $request->get('lastBill');
    } else {
      $bill_to_print = '';
    }
    if( !is_null($request->get('format')) && ($request->get('format') === 'bill' || $request->get('format') === 'invoice')) {
      $print_format = $request->get('format');
    } else {
      $print_format = 'bill';
    }
    // ------------------------------------- check for form Submission --------------------------------
    // ------------------------------------------------------------------------------------------------
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
          Utilities::redirect('/sales/entry-with-barcode?lastBill='.$api_response['invoiceCode'].'&format='.$print_format);
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
              Utilities::redirect('/sales/entry-with-barcode?lastBill='.$api_response['invoiceCode'].'&format='.$print_format);
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
    // check if indent code already exists and prefill the sales entry form.      
    } elseif(!is_null($request->get('ic')) && ctype_alnum($request->get('ic'))) {
      $indent_code = Utilities::clean_string($request->get('ic'));
      $indent_details = $this->sindent_model->get_indent_details($indent_code, true);
      if($indent_details['status']) {
        // map indent data with Sales invoice
        $form_data = $this->_map_indent_data_with_sales_entry($indent_details['response']['indentDetails']);
        $from_indent = true;
      } else {
        $this->flash->set_flash_message('Invalid Indent Code (or) Indent does not exists.');
        Utilities::redirect('/sales-indents/list');
      }
    }

    // --------------- build variables -----------------
    $controller_vars = array(
      'page_title' => 'Create Invoice',
      'icon_name' => 'fa fa-inr',
    );
    
    // ---------------- prepare form variables. ---------
    $template_vars = array(
      'payment_methods' => Constants::$PAYMENT_METHODS_RC,
      'offers' => array(''=>'Choose')+$offers,
      'offers_raw' => $offers_raw,
      'credit_days_a' => array(0=>'Choose') + $credit_days_a,      
      'qtys_a' => array(0=>'Choose')+$qtys_a,
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
      'client_locations' => $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'sa_executives' => ['Choose'] + $sa_executives,
      'promo_key' => $promo_key,
      'from_indent' => $from_indent,
      'ic' => $indent_code,
      'customer_types' => $customer_types,
      'sa_categories' => $sa_categories,
    );

    return array($this->template->render_view('sales-entry-with-barcode', $template_vars),$controller_vars);
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
    $customer_types = Constants::$CUSTOMER_TYPES;
    $sa_categories = ['' => 'Choose'];

    $from_indent = false;

    $page_error = $page_success = $promo_key = '';
    $print_format = 'bill';
    $indent_code = '';    

    if($request->get('salesCode') && $request->get('salesCode')!=='') {
      $sales_code = Utilities::clean_string($request->get('salesCode'));
      $sales_response = $this->sales->get_sales_details($sales_code);
      if($sales_response['status']) {
        $form_data = $this->_map_invoice_data_with_form_data($sales_response['saleDetails']);
      } else {
        $page_error = $sales_response['apierror'];
        $flash->set_flash_message($page_error,1);
        Utilities::redirect('/sales/list');
      }
    } else {
      $this->flash->set_flash_message('Invalid Invoice No. (or) Invoice No. does not exist.',1);
      Utilities::redirect('/sales/list');
    }

    // get sales categories
    $sa_categories += $this->_get_sa_categories();    

    // ---------- end of initializing variables -----------------
    for($i=1;$i<=500;$i++) {
      if($i <= 365) {
        $credit_days_a[$i] = $i;
      }
      $qtys_a[$i] = $i;
    }

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

    // ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations();

    // ---------- get sales executives ------------------------------
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

    // ---------- check for last bill printing ----
    if( !is_null($request->get('lastBill')) ) {
      $bill_to_print = $request->get('lastBill');
    } else {
      $bill_to_print = '';
    }
    if( !is_null($request->get('format')) && ($request->get('format') === 'bill' || $request->get('format') === 'invoice')) {
      $print_format = $request->get('format');
    } else {
      $print_format = 'bill';
    }

    // ------------------------------------- check for form Submission --------------------------------
    // ------------------------------------------------------------------------------------------------
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
          Utilities::redirect('/sales/entry-with-barcode?lastBill='.$api_response['invoiceCode'].'&format='.$print_format);
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
              Utilities::redirect('/sales/entry-with-barcode?lastBill='.$api_response['invoiceCode'].'&format='.$print_format);
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

    // --------------- build variables -----------------
    $controller_vars = array(
      'page_title' => 'Update Invoice',
      'icon_name' => 'fa fa-inr',
    );
    
    # ---------------- prepare form variables. ---------
    $template_vars = array(
      'payment_methods' => Constants::$PAYMENT_METHODS_RC,
      'offers' => array(''=>'Choose')+$offers,
      'offers_raw' => $offers_raw,
      'credit_days_a' => array(0=>'Choose') + $credit_days_a,      
      'qtys_a' => array(0=>'Choose')+$qtys_a,
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
      'client_locations' => $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'sa_executives' => ['Choose'] + $sa_executives,
      'promo_key' => $promo_key,
      'from_indent' => $from_indent,
      'ic' => $sales_code,
      'customer_types' => $customer_types,
      'sa_categories' => $sa_categories,
    );

    return array($this->template->render_view('sales-update-with-barcode', $template_vars),$controller_vars);
  }

  // validate form data
  private function _validate_form_data($form_data=[]) {

    // dump($form_data);
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
    $split_payment_cash = isset($form_data['splitPaymentCash']) ? Utilities::clean_string($form_data['splitPaymentCash']) : 0;
    $split_payment_card = isset($form_data['splitPaymentCard']) ? Utilities::clean_string($form_data['splitPaymentCard']) : 0;
    $split_payment_cn = isset($form_data['splitPaymentCn']) ? Utilities::clean_string($form_data['splitPaymentCn']) : 0;
    $cn_no = isset($form_data['cnNo']) ? Utilities::clean_string($form_data['cnNo']) : 0;
    $item_details = $form_data['itemDetails'];
    $executive_id = isset($form_data['saExecutive']) && $form_data['saExecutive'] !== '' ? Utilities::clean_string($form_data['saExecutive']) : '';
    $referral_code = is_numeric($form_data['refCode']) ? Utilities::clean_string($form_data['refCode']) : 0;
    $promo_code = isset($form_data['promoCode']) ? Utilities::clean_string($form_data['promoCode']) : '';
    $from_indent = isset($form_data['fi']) ? 'y': 'n';
    $customer_type = $form_data['customerType'];
    $credit_days = isset($form_data['saCreditDays']) ? Utilities::clean_string($form_data['saCreditDays']) : 0;
    $remarks_invoice = isset($form_data['remarksInvoice']) ? Utilities::clean_string($form_data['remarksInvoice']) : '';
    $sales_category =  isset($form_data['salesCategory']) ? Utilities::clean_string($form_data['salesCategory']) : '';

    $packing_charges =  Utilities::clean_string($form_data['packingCharges']);
    $shipping_charges = Utilities::clean_string($form_data['shippingCharges']);
    $insurance_charges = Utilities::clean_string($form_data['insuranceCharges']);
    $other_charges = Utilities::clean_string($form_data['otherCharges']);
    $transporter_name = Utilities::clean_string($form_data['transporterName']);
    $lr_no = Utilities::clean_string($form_data['lrNos']);
    $lr_date = Utilities::clean_string($form_data['lrDate']);
    $chalan_no = Utilities::clean_string($form_data['challanNo']);


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

        # validate item name.
/*        if(ctype_alnum(str_replace([' ', '-', '_'], ['','',''], $item_name)) === false) {
          $form_errors['itemDetails']['itemName'][$item_key] = 'Invalid item name.';
        } else {
          $cleaned_params['itemDetails']['itemName'][$item_key] = $item_name;
        }*/
        
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

    // return response.
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

  // maps indent data with sales data
  public function _map_indent_data_with_sales_entry($indent_details=[]) {
    $tran_details = $indent_details['tranDetails'];
    $indent_items = $indent_details['itemDetails'];
    $form_data['name'] = $tran_details['customerName'];
    $form_data['mobileNo'] = $tran_details['primaryMobileNo'];
    $form_data['indentNo'] = $tran_details['indentNo'];
    foreach($indent_items as $key => $item_details) {
      $form_data['itemDetails']['itemName'][$key] = $item_details['itemName'];
      $form_data['itemDetails']['itemSoldQty'][$key] = $item_details['itemQty'];
      $form_data['itemDetails']['itemRate'][$key] = $item_details['itemRate'];
      $form_data['itemDetails']['taxPercent'][$key] = $item_details['taxPercent'];
      $form_data['itemDetails']['itemAvailQty'][$key] = $item_details['closingQty'];
      $form_data['itemDetails']['lotNo'][$key] = $item_details['lotNo'];
      $form_data['itemDetails']['barcode'][$key] = $item_details['barcode'];
    }
    return $form_data;
  }

  // map invoice data with form data
  public function _map_invoice_data_with_form_data($invoice_details = []) {
    $cleaned_params = [];

    $sale_items = $invoice_details['itemDetails'];
    unset($invoice_details['itemDetails']);

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