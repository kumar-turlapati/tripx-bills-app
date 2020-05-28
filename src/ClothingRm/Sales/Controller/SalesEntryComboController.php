<?php 

namespace ClothingRm\Sales\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;
use Atawa\ApiCaller;

use ClothingRm\Sales\Model\Sales;
use ClothingRm\Suppliers\Model\Supplier;
use ClothingRm\Taxes\Model\Taxes;
use ClothingRm\Finance\Model\CreditNote;
use ClothingRm\PromoOffers\Model\PromoOffers;
use ClothingRm\SalesIndent\Model\SalesIndent;

use BusinessUsers\Model\BusinessUsers;
use User\Model\User;
use SalesCategory\Model\SalesCategory;

class SalesEntryComboController
{
  protected $views_path,$finmodel;

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
    $this->api_caller = new ApiCaller;
  }

  // Sales Entry Combo action.
  public function salesEntryAction(Request $request) {

    $page_error = $page_success = $from_location = $to_location = '';
    $form_data = $form_errors = $taxes = [];

    // ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true, false, true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];
      $location_names[$location_key_a[0]] = $location_value;
    }

    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $validate_form = $this->_validate_form_data($form_data, $location_names);
      $status = $validate_form['status'];
      if($status) {
        $combo_array = $this->_assign_lotnos($validate_form['cleaned_params']);
        $sales_array = $combo_array;
        $sales_array['itemDetails']['itemSoldQty'] = $sales_array['itemDetails']['comboItemSoldQty'];
        unset($sales_array['itemDetails']['comboItemCode']);
        unset($sales_array['itemDetails']['comboItemSoldQty']);
        $sales_api_response = $this->sales->create_sale($sales_array);
        // dump($sales_api_response, $sales_array);
        // exit;
        if($sales_api_response['status']) {
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i> Sales transaction with Bill No. <b>`'.$sales_api_response['billNo'].'`</b> created successfully.');
          Utilities::redirect('/sales-entry/combos?lastBill='.$sales_api_response['invoiceCode'].'&format=combo');
        } else {
          $page_error = $sales_api_response['apierror'];
          $this->flash->set_flash_message($page_error,1);
          $form_data = $combo_array;  
        }        
      } else {
        $form_errors = $validate_form['errors'];
      }
    }

    #---------- check for last bill printing -----------
    if($request->get('lastBill')) {
      $bill_to_print = $request->get('lastBill');
    } else {
      $bill_to_print = '';
    }
    $print_format = 'combo';

    // build variables
    $controller_vars = array(
      'page_title' => 'Sales Entry - Combos',
      'icon_name' => 'fa fa-inr',
    );

    $template_vars = array(
      'payment_methods' => array(''=>'Choose') + Constants::$PAYMENT_METHODS_WALLETS,
      'wallets' => array(''=>'Choose') + Constants::$WALLETS,
      'errors' => $form_errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'btn_label' => 'Create Sale',
      'form_data' => $form_data,
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'bill_to_print' => $bill_to_print,
      'print_format' => $print_format,
    );

    // render template
    return array($this->template->render_view('sales-combo-entry', $template_vars),$controller_vars);
  }

  private function _validate_form_data($form_data=[], $location_names = []) {

    $cleaned_params = $form_errors = [];
    $tot_bill_value = $tot_discount_amount = $tot_billable_value = $tot_tax_value = $round_off = $net_pay = 0;

    $payment_methods_a = Constants::$PAYMENT_METHODS_WALLETS;
    $one_item_found = false;
    $split_payment_found = 0;

    // get form data.
    $sale_date = isset($form_data['saleDate']) ? Utilities::clean_string($form_data['saleDate']) : '';
    $location_code = isset($form_data['locationCode']) ? Utilities::clean_string($form_data['locationCode']) : '';
    $payment_method = isset($form_data['paymentMethod']) ? (int)Utilities::clean_string($form_data['paymentMethod']) : '';
    $mobile_no = isset($form_data['mobileNo']) ? Utilities::clean_string($form_data['mobileNo']) : '';

    $card_no = isset($form_data['cardNo']) && $form_data['cardNo'] !== '' ? Utilities::clean_string($form_data['cardNo']) : '9999';
    $auth_code = isset($form_data['authCode']) && $form_data['authCode'] !== ''  ? Utilities::clean_string($form_data['authCode']) : '9999';
    $cn_no = isset($form_data['cnNo']) ? Utilities::clean_string($form_data['cnNo']) : 0;

    $split_payment_cash = isset($form_data['splitPaymentCash']) ? Utilities::clean_string($form_data['splitPaymentCash']) : 0;
    $split_payment_card = isset($form_data['splitPaymentCard']) ? Utilities::clean_string($form_data['splitPaymentCard']) : 0;
    $split_payment_cn = isset($form_data['splitPaymentCn']) ? Utilities::clean_string($form_data['splitPaymentCn']) : 0;
    $split_payment_wallet = isset($form_data['splitPaymentWallet']) ? Utilities::clean_string($form_data['splitPaymentWallet']) : 0;

    $wallet_id = isset($form_data['walletID']) ? Utilities::clean_string($form_data['walletID']) : 0;
    $wallet_ref_no = isset($form_data['walletRefNo']) ? Utilities::clean_string($form_data['walletRefNo']) : '';
    $name = isset($form_data['name']) ? Utilities::clean_string($form_data['name']) : '';

    $combo_discount = isset($form_data['comboDiscount']) ? Utilities::clean_string($form_data['comboDiscount']) : 0;

    $item_details = $form_data['itemDetails'];

    $cleaned_params['name'] = $name;      

    // add misc parameters.
    if(Utilities::is_valid_fin_date($sale_date)) {
      $cleaned_params['saleDate'] = $sale_date;
    } else {
      $form_errors['saleDate'] = 'Invoice Date is out of Financial year dates.';
    }

    // validate location code.
    if($location_code !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['locationCode'] = 'Invalid location code.';
    }

    // validate payment method
    if( in_array($payment_method, array_keys($payment_methods_a)) === false ) {
      $form_errors['paymentMethod'] = 'Invalid payment method.';
    } else {
      $cleaned_params['paymentMethod'] = $payment_method;
    }    

    // validate mobile number.
    if( $mobile_no !== '' && (!is_numeric($mobile_no) || strlen($mobile_no) !== 10)) {
      $form_errors['mobileNo'] = 'Invalid mobile number.';
    } else {
      $cleaned_params['mobileNo'] = $mobile_no;
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
          // validate credit note no.
          $cn_details = $this->cn_model->get_credit_note_details([], $cn_no);
          if($cn_details['status'] === false) {
            $form_errors['paymentMethod'] = 'Invalid Credit Note No.';
          } else {
            $balance_value = $cn_details['data']['vocDetails']['balanceValue'];
            if($balance_value < $split_payment_cn) {
              $form_errors['paymentMethod'] = 'Credit note value exceeds than credit note balance of Rs. '.number_format($balance_value,2,'.','');
            }
          }
        }
      }
      if($split_payment_found <= 1) {
        $form_errors['paymentMethod'] = 'By Cash , By Card, By Wallet and Credit Note Value. Two payment modes are required for Split Payment.';
      }
    }    

    // validate item details.
    $item_index = 0;
    for($item_key=0;$item_key<6;$item_key++) {
      if($item_details['itemName'][$item_key] !== '') {
        $one_item_found = true;

        $barcode = isset($item_details['barcode'][$item_key]) ? Utilities::clean_string($item_details['barcode'][$item_key]) : '';
        $item_code = Utilities::clean_string($item_details['comboItemCode'][$item_key]);
        $item_name = Utilities::clean_string($item_details['itemName'][$item_key]);
        $item_ava_qty = Utilities::clean_string($item_details['itemAvailQty'][$item_key]);
        $item_sold_qty = Utilities::clean_string($item_details['comboItemSoldQty'][$item_key]);
        $item_rate = Utilities::clean_string($item_details['itemRate'][$item_key]);
        $item_discount = Utilities::clean_string($item_details['itemDiscount'][$item_key]);
        $item_tax_amount = 0;

        $item_total = round($item_sold_qty * $item_rate, 2);
        $item_total_billable = $item_total - $item_discount;

        $tot_billable_value += $item_total_billable;
        $tot_discount_amount += $item_discount;
        $tot_tax_value += $item_tax_amount;

        $cleaned_params['itemDetails']['itemName'][$item_index] = $item_name;

        // validate barcode
        // if($barcode !== '' && is_numeric($barcode)) {
        //   $cleaned_params['itemDetails']['barcode'][$item_key] = $barcode;
        // } else {
        //   $form_errors['itemDetails']['barcode'][$item_key] = 'Invalid barcode.';
        // }

        // validate item code
        if($item_code !== '' && is_numeric($item_code)) {
          $cleaned_params['itemDetails']['comboItemCode'][$item_index] = $item_code;
        } else {
          $form_errors['itemDetails']['comboItemCode'][$item_index] = 'Invalid combo item code';
        }

        // validate item avaiable qty.
        if(!is_numeric($item_ava_qty) || $item_ava_qty <= 0) {
          $form_errors['itemDetails']['itemAvailQty'][$item_index] = 'Invalid available qty.';
        } else {
          $cleaned_params['itemDetails']['itemAvailQty'][$item_index] = $item_ava_qty;
        }

        // validate sold qty.
        if(!is_numeric($item_sold_qty) || $item_sold_qty <= 0) {
          $form_errors['itemDetails']['comboItemSoldQty'][$item_index] = 'Invalid sold qty.';
        } else {
          $cleaned_params['itemDetails']['comboItemSoldQty'][$item_index] = $item_sold_qty;
        }

        // validate item rate.
        if(!is_numeric($item_rate) || $item_rate<=0) {
          $form_errors['itemDetails']['itemRate'][$item_index] = 'Invalid item rate.';
        } else {
          $cleaned_params['itemDetails']['itemRate'][$item_index] = $item_rate;
        }

        // validate item discount.
        if($item_discount !== '' && !is_numeric($item_discount) ) {
          $form_errors['itemDetails']['itemDiscount'][$item_index] = 'Invalid item discount.';
        } else {
          $cleaned_params['itemDetails']['itemDiscount'][$item_index] = $item_discount;
        }

        // validate if sold qty. is more than available qty.
        if($item_sold_qty > $item_ava_qty) {
          $form_errors['itemDetails']['comboItemSoldQty'][$item_index] = 'Invalid sold qty.';
        }

        $item_index++;
      }
    }

    // if no items are available through an error.
    if($one_item_found === false) {
      $form_errors['itemDetails']['comboItemCode'][0] = 'Invalid item code.';
      $form_errors['itemDetails']['itemName'][0] = 'Invalid item name.';
      $form_errors['itemDetails']['itemAvailQty'][0] = 'Invalid available qty.';
      $form_errors['itemDetails']['comboItemSoldQty'][0] = 'Invalid sold qty.';
      $form_errors['itemDetails']['itemRate'][0] = 'Invalid item rate.';
      $form_errors['itemDetails']['itemDiscount'][0] = 'Invalid item rate.';      
    } else {
      $net_pay = round($tot_billable_value + $tot_tax_value, 0);
    }

    // validate payment method.
    if($payment_method === 2 && ($split_payment_card <= 0 && $split_payment_cash <= 0 && $split_payment_cn <= 0 && $split_payment_wallet <= 0) ) {
      $form_errors['paymentMethod'] = 'Cash, Card, Wallet or Cnote payment value is required.';
    } elseif($payment_method === 1 || $payment_method === 0) {
      $cleaned_params['splitPaymentCard'] = 0;
      $cleaned_params['splitPaymentCash'] = 0;
      $cleaned_params['splitPaymentCn'] = 0;
      $cleaned_params['splitPaymentWallet'] = 0;
    } elseif( $payment_method === 4) {
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
    }

    $cleaned_params['cardNo'] = $card_no;
    $cleaned_params['authCode'] = $auth_code;
    $cleaned_params['isComboBill'] = 1;
    $cleaned_params['comboDiscount'] = $combo_discount;
    $cleaned_params['cnNo'] = $form_data['cnNo'];

    // dump((float)$split_payment_card + $split_payment_cash + $split_payment_cn + $split_payment_wallet);
    // dump((float)$net_pay);

    if($payment_method === 2 && ( (float)$split_payment_card + $split_payment_cash + $split_payment_cn + $split_payment_wallet !== (float)$net_pay) ) {
      $form_errors['paymentMethod'] = 'Cash / Card / Cnote / Wallet value must be equal to bill value.';      
    }

    // dump($form_data, $form_errors, $cleaned_params);
    // exit;

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

  private function _assign_lotnos($cleaned_params=[], $location_code='') {
    $end_point = 'inventory/available-qty';
    $item_names = $cleaned_params['itemDetails']['itemName'];
    $order_qtys = $cleaned_params['itemDetails']['comboItemSoldQty'];
    $location_code = $cleaned_params['locationCode'];
    $lot_nos = $tax_percents = [];
    foreach($item_names as $item_key => $item_name) {
      $params = [
        'locationCode' => $location_code,
        'itemName' => $item_name,
      ];
      $response = $this->api_caller->sendRequest('get',$end_point,$params);
      if($response['status'] === 'success') {
        foreach($response['response'] as $lot_details) {
          $closing_qty = $lot_details['closingQty'];
          if($closing_qty > 0 && $closing_qty >= $order_qtys[$item_key]) {
            $lot_nos[$item_key] = $lot_details['lotNo'];
            $tax_percents[$item_key] = $lot_details['taxPercent'];
            break;
          }
        }
      }
    }

    $cleaned_params['itemDetails']['lotNo'] = $lot_nos;
    $cleaned_params['itemDetails']['itemTaxPercent'] = $tax_percents;

    // dump('hello world....', $cleaned_params);
    // exit;

    return $cleaned_params;
  }
}
