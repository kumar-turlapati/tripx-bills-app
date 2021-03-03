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
use Taxes\Model\Taxes;
use ClothingRm\Finance\Model\CreditNote;
use ClothingRm\PromoOffers\Model\PromoOffers;
use ClothingRm\SalesIndent\Model\SalesIndent;
use BusinessUsers\Model\BusinessUsers;
use User\Model\User;
use SalesCategory\Model\SalesCategory; 

class GatePassController {

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

  // get bill no if not exists
  public function getInvoiceNo(Request $request) {
    // ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(false, false, true);

    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $invoice_no = isset($form_data['invoiceNo']) ? Utilities::clean_string($form_data['invoiceNo']) : 0;
      $location_code = isset($form_data['locationCode']) ? Utilities::clean_string($form_data['locationCode']) : '';
      if(is_numeric($invoice_no) && $invoice_no > 0 && $location_code !== '') {
        Utilities::redirect("/gate-pass/entry?invoiceNo=$invoice_no&lc=$location_code");
      }
    }

    // --------------- build variables -----------------
    $controller_vars = array(
      'page_title' => 'Invoice Details',
      'icon_name' => 'fa fa-money',
    );
    
    // ---------------- prepare form variables. ---------
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => $client_locations,
    );

    return array($this->template->render_view('get-invoice-no', $template_vars),$controller_vars);    
  }

  // create gate pass
  public function gatePassEntryAction(Request $request) {

    $invoice_no = !is_null($request->get('invoiceNo')) ? Utilities::clean_string($request->get('invoiceNo')) : '';
    $location_code = !is_null($request->get('lc')) ? Utilities::clean_string($request->get('lc')) : '';
    if($invoice_no === '' || $location_code === '') {
      Utilities::redirect('/get-invoice-no');
    } else {
      $sales_response = $this->sales->get_sales_details($invoice_no, true, $location_code);
      if($sales_response['status'] === false) {
        $page_error = $sales_response['apierror'];
        $this->flash->set_flash_message($page_error,1);
        Utilities::redirect('/get-invoice-no');
      } else {
        $form_data = $this->_map_invoice_data_with_form_data($sales_response['saleDetails']);
      }
    }

    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $gp_lot_nos = $submitted_data['itemDetails']['lotNo'];
      $invoice_lot_nos = $form_data['itemDetails']['lotNo'];
      $diff_lot_nos = array_diff($invoice_lot_nos, $gp_lot_nos);
      if(is_array($diff_lot_nos) && count($diff_lot_nos) > 0) {
        $this->flash->set_flash_message('<i class="fa fa-window-close" aria-hidden="true"></i>&nbsp;Unable to generate Gatepass! All the products in the invoice were not Scanned.....?',1);
        Utilities::redirect('/get-invoice-no');
      } else {
        $gp_data = ['invoiceNo' => $invoice_no, 'locationCode' => $location_code];
        $api_response = $this->sales->generate_gatepass($gp_data);
        if($api_response['status']) {
          $this->flash->set_flash_message('Gatepass with No. <b>`'.$api_response['gatePassNo'].'`</b> created successfully.');
          Utilities::redirect('/sales/list');
        } else {
          $this->flash->set_flash_message($api_response['apierror'], 1);
          Utilities::redirect('/get-invoice-no');
        }
      }
    }

    // --------------- build variables -----------------
    $controller_vars = array(
      'page_title' => 'Gatepass entry',
      'icon_name' => 'fa fa-truck',
    );
    
    // ---------------- prepare form variables. ---------
    $template_vars = array(
      'flash_obj' => $this->flash,
      'default_location' => $location_code,
      'form_data' => $form_data,
    );

    return array($this->template->render_view('gate-pass-entry', $template_vars),$controller_vars);
  }

  // delete gate pass
  public function gatePassRemoveAction(Request $request) {
    if($request->get('salesCode') && $request->get('salesCode')!=='') {
      $sales_code = Utilities::clean_string($request->get('salesCode'));
      $sales_response = $this->sales->get_sales_details($sales_code);
      if($sales_response['status']) {
        // delete gatepass
        $api_response = $this->sales->cancel_gatepass($sales_code);
        if($api_response['status']) {
          $this->flash->set_flash_message('Gatepass with No. <b>`'.$api_response['cancelledGatePassNo'].'`</b> cancelled successfully.');
          Utilities::redirect('/sales/list');
        } else {
          $this->flash->set_flash_message($api_response['apierror'], 1);
          Utilities::redirect('/get-invoice-no');
        }
      } else {
        $page_error = $sales_response['apierror'];
        $this->flash->set_flash_message($page_error,1);
        Utilities::redirect('/sales/list');
      }
    } else {
      $this->flash->set_flash_message('Invalid Invoice No. (or) Invoice No. does not exist.',1);
      Utilities::redirect('/sales/list');
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
    $split_payment_wallet = isset($form_data['splitPaymentWallet']) && is_numeric($form_data['splitPaymentWallet'])  ? Utilities::clean_string($form_data['splitPaymentWallet']) : 0;
    $cn_no = isset($form_data['cnNo']) ? Utilities::clean_string($form_data['cnNo']) : 0;
    $item_details = $form_data['itemDetails'];
    $executive_id = isset($form_data['saExecutive']) && $form_data['saExecutive'] !== '' ? Utilities::clean_string($form_data['saExecutive']) : '';
    $referral_code = is_numeric($form_data['refCode']) ? Utilities::clean_string($form_data['refCode']) : 0;
    $promo_code = isset($form_data['promoCode']) ? Utilities::clean_string($form_data['promoCode']) : '';
    $from_indent = isset($form_data['fi']) ? 'y': 'n';
    $customer_type = $form_data['customerType'];
    $credit_days = isset($form_data['saCreditDays']) ? Utilities::clean_string($form_data['saCreditDays']) : 0;
    $remarks_invoice = isset($form_data['remarksInvoice']) ? Utilities::clean_string($form_data['remarksInvoice']) : '';
    $sales_category = isset($form_data['salesCategory']) ? Utilities::clean_string($form_data['salesCategory']) : '';
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

        if(!is_numeric($item_discount)) {
          $item_discount = 0;
        }
        if(!is_numeric($item_tax_percent)) {
          $item_tax_percent = 0;
        }

        $item_total = $item_sold_qty > 0 && $item_rate ? round($item_sold_qty * $item_rate, 2) : 0;
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
      $cleaned_params['itemDetails']['itemType'][$key] = $item_details['itemType'];
      $cleaned_params['itemDetails']['itemServiceCode'][$key] = $item_details['itemServiceCode'];
    }
    $cleaned_params = array_merge($invoice_details, $cleaned_params);

    return $cleaned_params;
  }

}
