<?php 

namespace ClothingRm\ReportsByModule\Sales\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;
use Atawa\PDF;
use Atawa\PdfWoHeaderFooter;

use ClothingRm\Sales\Model\Sales;
use ClothingRm\Finance\Model\Finance;
use ClothingRm\Sales\Model\Einvoice;

use BusinessUsers\Model\BusinessUsers;

class SalesReportsController {

  protected $views_path;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->sales_model = new Sales;
    $this->bu_model = new BusinessUsers;
    $this->flash = new Flash;    
    $this->fin_model = new Finance;
    $this->einvoice = new Einvoice;
  }

  // print b2b sales invoice
  public function printB2BSalesInvoice(Request $request) {
    $sales_code = Utilities::clean_string($request->get('salesCode'));
    $slno = 0;
    $gst_percents_a = ['5.00','12.00','18.00','28.00'];
    $banks = [];

    $sales_response = $this->sales_model->get_sales_details($sales_code,false);
    $status = $sales_response['status'];
    if($status) {
      $sale_details = $sales_response['saleDetails'];
      $sale_item_details = $sale_details['itemDetails'];
      unset($sale_details['itemDetails']);
    } else {
      die('Invalid Sales Transaction.');
    }
    
    $bill_no = $sale_details['billNo'];
    $bill_date = date('d-M-Y',strtotime($sale_details['invoiceDate']));
    $bill_time = date('h:ia',strtotime($sale_details['createdTime']));
    $payment_method = Constants::$PAYMENT_METHODS_RC[$sale_details['paymentMethod']];
    $payment_method_num = (int)$sale_details['paymentMethod'];
    $tmp_cust_name = $sale_details['tmpCustName'];
    $customer_name  =  $sale_details['customerName'] !== '' ? $sale_details['customerName'] : '';
    $card_no = $sale_details['cardNo'] > 0 ? '* ****'.$sale_details['cardNo'] : '';
    $auth_code = $sale_details['authCode'] > 0 ? $sale_details['authCode'] : '****';

    $cn_no =  $sale_details['cnNo'];
    $referral_no = $sale_details['refCardNo'];
    $promo_code =  $sale_details['promoCode'];
    if($customer_name === '') {
      $customer_name = $tmp_cust_name;
    }

    $tax_calc_option = $sale_details['taxCalcOption'];

    $business_name  =   isset($sale_details['locationNameShort']) && $sale_details['locationNameShort'] !== '' ? $sale_details['locationNameShort'] : $sale_details['locationName'];
    $business_add1  =   $sale_details['locAddress1'];
    $business_add2  =   $sale_details['locAddress2'];
    $city_name      =   $sale_details['locCityName'];
    $state_name     =   Utilities::get_location_state_name($sale_details['locStateID']);
    $pincode        =   $sale_details['locPincode'];
    $business_add3  =   $city_name.', '.$state_name.' - '.$pincode;
    $phones         =   $sale_details['locPhones'];

    $gst_no         =   $sale_details['locGstNo'];
    $card_no        =   $sale_details['cardNo'] > 0 ? '* ****'.$sale_details['cardNo'] : '';
    $auth_code      =   $sale_details['authCode'] > 0 ? $sale_details['authCode'] : '****';

    $cn_no          =   $sale_details['cnNo'];
    $referral_no    =   $sale_details['refCardNo'];
    $net_pay        =   $sale_details['netPay'];
    $bank_id        =   $sale_details['bankID'];
    $remarks_invoice=   $sale_details['remarksInvoice'];
    if($bank_id > 0) {
      // get all banks from back-end.
      $result = $this->fin_model->banks_list();
      if($result['status']) {
        $banks = $result['banks'];
        $bank_key = array_search($bank_id, array_column($banks, 'bankID'));
      }
    }

    $loc_address = [
      'address1' => $business_add1,
      'address2' => $business_add2,
      'address3' => $business_add3,
      'phones' => $phones,
      'store_name' => $business_name,
      'gst_no' => $gst_no,
    ];

    // igst or (cgst and sgst)
    if((int)$sale_details['stateID'] > 0 && (int)$sale_details['locStateID'] > 0) {
      if((int)$sale_details['stateID'] === (int)$sale_details['locStateID']) {
        $gst_tax_type = 'intra';
      } else {
        $gst_tax_type = 'inter';
      }
    } else {
      $gst_tax_type = 'intra';
    }

    // customer array
    $customer_info['custom_invoice_no'] = $sale_details['customBillNo'] !== '' ? $sale_details['customBillNo'] : $sale_details['billNo'];
    $customer_info['bill_no'] = $sale_details['billNo'];
    $customer_info['indent_no'] = $sale_details['indentNo'];
    $customer_info['bill_date'] = date("d-M-Y",strtotime($sale_details['invoiceDate']));
    $customer_info['location_name'] = $sale_details['locationName'];
    $customer_info['location_name_short'] = $sale_details['locationNameShort'];
    $customer_info['executive_name'] = $sale_details['executiveName'];
    $customer_info['invoice_code'] = $sale_details['invoiceCode'];
    $customer_info['payment_method'] = $sale_details['paymentMethod'];
    $customer_info['credit_days'] = $sale_details['saCreditDays'];
    $customer_info['promo_code'] = $sale_details['promoCode'];
    $customer_info['billing'] = [
      'customer_name' => $customer_name,
      'address' => $sale_details['address'],
      'city_name' => $sale_details['cityName'],
      'state_name' => Utilities::get_location_state_name($sale_details['stateID']),
      'country_name' => Utilities::get_country_name($sale_details['countryID']),
      'pincode' => $sale_details['pincode'],
      'phones' => $sale_details['phones'],
      'gst_no' => $sale_details['customerGstNo'],
      'state_id' => $sale_details['stateID'],
    ];
    $customer_info['shipping'] = [
      'customer_name' => $customer_name,
      'address' => $sale_details['shippingAddress'] !== '' ? $sale_details['shippingAddress'] : $sale_details['address'],
      'city_name' => $sale_details['shippingCityName'] !== '' ? $sale_details['shippingCityName'] : $sale_details['cityName'],
      'state_name' => $sale_details['shippingStateID'] !== '' ? Utilities::get_location_state_name($sale_details['shippingStateID']) : Utilities::get_location_state_name($sale_details['stateID']),
      'country_name' => $sale_details['shippingCountryID'] !== '' ? Utilities::get_country_name($sale_details['shippingCountryID']) : Utilities::get_country_name($sale_details['countryID']),
      'pincode' => $sale_details['shippingPincode'] !== '' ? $sale_details['shippingPincode'] : $sale_details['pincode'],
      'phones' => $sale_details['shippingPhones'] !== '' ? $sale_details['shippingPhones'] : $sale_details['phones'],
      'gst_no' => $sale_details['customerGstNo'],
      'state_id' => $sale_details['shippingStateID'] !== '' ? $sale_details['shippingStateID'] : $sale_details['stateID'],
    ];
    $customer_info['transport_details'] = [
      'transporter_name' => $sale_details['transporterName'],
      'lr_no' => $sale_details['lrNo'],
      'lr_date' => $sale_details['lrDate'],
      'challan_no' => $sale_details['challanNo'],
      'way_bill_no' => $sale_details['wayBillNo'],
    ];

    // dump($customer_info);
    // exit;

    $pdf = PDF::getInstance(false, $loc_address);
    $pdf->AliasNbPages();
    $pdf->SetAutoPageBreak(true);
    $pdf->AddPage('P','A4');
    $pdf->setTitle('B2B Invoice'.' - '.date('jS F, Y'));

    // print sale items.
    $item_widths = [8,67,15,16,13,15,18,18,20];
              //    0, 1, 2, 3, 4, 5, 6, 7, 8, 9

    $this->_add_b2b_invoice_header($pdf, $customer_info, $item_widths);
    $sno = 0;
    $record_cntr = 0;
    $items_per_page = 20;
    $empty_rows_tobe_filled = count($sale_item_details) < $items_per_page ? $items_per_page-count($sale_item_details) : 0;
    $empty_rows_tobe_filled = 0;
    // dump($sale_item_details);
    // exit;
    $sale_item_details_filled = array_fill(count($sale_item_details)-1, $empty_rows_tobe_filled, []);
    $sale_item_final = array_merge($sale_item_details, $sale_item_details_filled);
    $unique_hsn_codes = array_unique(array_column($sale_item_details, 'hsnSacCode')); 

    // dump(count($sale_item_final));

    $taxable_values = $tax_amounts = $taxable_gst_value = [];
    $tot_bill_value = $tot_discount = $tot_taxable = $tot_items_qty = 0;
    $tot_tax_amount = 0;
    $hsn_code_qty = $hsn_code_taxable = $hsn_cgst_value = $hsn_sgst_value = 0;
    $cnos_a = $hsn_codes_array = $bnos_a = [];
    foreach($sale_item_final as $item_details) {
      if(is_array($item_details) && count($item_details) > 0) {
        $item_qty = $amount = $discount = $taxable = $tax_value = 0;
        $hsn_code_qty = $hsn_code_taxable = $hsn_cgst_value = $hsn_sgst_value = 0;

        $sno++;

        $item_name = $item_details['itemName'];
        $hsn_sac_code = $item_details['hsnSacCode'];
        $item_qty = (float)$item_details['itemQty'];
        $uom_name = $item_details['uomName'];
        $discount = $item_details['discountAmount'];
        $tax_percent = $item_details['taxPercent'];
        $mrp = $item_details['mrp'];
        if($item_details['cno'] !== '') {
          $cnos_a[] = $item_details['cno'];
        }
        if($item_details['bno'] !== '') {
          $bnos_a[] = $item_details['bno'];
        }        

        $amount = (float)round($item_qty*$mrp,2);
        $taxable = (float)round($amount-$discount,2);
        $tax_value = (float)round(($taxable*$tax_percent/100),2);

        // dump($tax_value, $taxable);

        $cgst_percent = $sgst_percent = round($tax_percent/2,2);
        $cgst_value = $sgst_value = round($tax_value/2,2);

        // dump($tax_value, $item_qty, $discount, $tax_percent, $mrp, 'hello...');

        $tot_items_qty += $item_qty;
        $tot_bill_value += $amount;
        $tot_discount += $discount;
        $tot_taxable += $taxable;
        $tot_tax_amount += $tax_value;

        if(isset($taxable_values[$tax_percent])) {
          $taxable_amount = $taxable_values[$tax_percent] + ($taxable);
          $gst_value = $taxable_gst_value[$tax_percent] + $tax_value;

          $taxable_values[$tax_percent] = $taxable_amount;
          $taxable_gst_value[$tax_percent] = $gst_value;
        } else {
          $taxable_values[$tax_percent] = $taxable;
          $taxable_gst_value[$tax_percent] = $tax_value;
        }

        if(isset($hsn_codes_array["$hsn_sac_code"])) {
          // $hsn_code_qty = $hsn_codes_array["$hsn_sac_code"]['qty'];
          // $hsn_code_taxable = $hsn_codes_array["$hsn_sac_code"]['taxable'];
          // $hsn_cgst_value = $hsn_codes_array["$hsn_sac_code"]['cgst_value'];
          // $hsn_sgst_value = $hsn_codes_array["$hsn_sac_code"]['sgst_value'];

          $hsn_codes_array["$hsn_sac_code"]['qty'] = $hsn_codes_array["$hsn_sac_code"]['qty']+$item_qty;
          $hsn_codes_array["$hsn_sac_code"]['taxable'] = $hsn_codes_array["$hsn_sac_code"]['taxable']+$taxable;
          $hsn_codes_array["$hsn_sac_code"]['cgst_value'] = (float)$hsn_codes_array["$hsn_sac_code"]['cgst_value']+(float)$cgst_value;
          $hsn_codes_array["$hsn_sac_code"]['sgst_value'] = (float)$hsn_codes_array["$hsn_sac_code"]['sgst_value']+(float)$sgst_value;

          // dump($hsn_codes_array, 'inside');

        } else {
          $hsn_codes_array["$hsn_sac_code"]['qty'] = $item_qty;
          $hsn_codes_array["$hsn_sac_code"]['taxable'] = $taxable;
          $hsn_codes_array["$hsn_sac_code"]['cgst_percent'] = $cgst_percent;
          $hsn_codes_array["$hsn_sac_code"]['sgst_percent'] = $sgst_percent;
          $hsn_codes_array["$hsn_sac_code"]['cgst_value'] = $cgst_value;
          $hsn_codes_array["$hsn_sac_code"]['sgst_value'] = $sgst_value;

          // dump($hsn_codes_array);          
        }

        // dump($hsn_codes_array);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell($item_widths[0],  6,$sno,'LR',0,'R');
        $pdf->Cell($item_widths[1],  6,$item_name,'R',0,'L');
        $pdf->Cell($item_widths[2],  6,$hsn_sac_code,'R',0,'L');
        $pdf->Cell($item_widths[4],  6,$uom_name,'R',0,'R');
        $pdf->Cell($item_widths[3],  6,number_format($mrp,2,'.',''),'R',0,'R');        
        $pdf->Cell($item_widths[5],  6,number_format($item_qty,2,'.',''),'R',0,'R');
        $pdf->Cell($item_widths[6],  6,number_format($amount,2,'.',''),'R',0,'R');
        $pdf->Cell($item_widths[7],  6,$discount > 0 ? number_format($discount,2,'.','') : '','R',0,'R');
        $pdf->Cell($item_widths[8],  6,number_format($taxable,2,'.',''),'R',0,'R');
      } else {
        $pdf->Cell($item_widths[0],  6,'','L',0,'R');
        $pdf->Cell($item_widths[1],  6,'','L',0,'L');
        $pdf->Cell($item_widths[2],  6,'','L',0,'L');
        $pdf->Cell($item_widths[4],  6,'','L',0,'R');
        $pdf->Cell($item_widths[3],  6,'','L',0,'R');        
        $pdf->Cell($item_widths[5],  6,'','L',0,'R');
        $pdf->Cell($item_widths[6],  6,'','L',0,'R');
        $pdf->Cell($item_widths[7],  6,'','L',0,'R');
        $pdf->Cell($item_widths[8],  6,'','LR',0,'R');
      }
      $pdf->Ln();
      $record_cntr++;
      // dump($record_cntr);
      // if($record_cntr === $items_per_page) {
      //   $pdf->AddPage('P','A4');
      //   $this->_add_b2b_invoice_header($pdf, $customer_info, $item_widths);
      //   $record_cntr = 0;
      // }
    }

    // exit;

    // dump($hsn_codes_array);
    // exit;

    // print totals
    $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3] + $item_widths[4];
    $gst_width = array_sum($item_widths) - $item_widths[8];
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($totals_width,    6,'Total Qty.','LT',0,'R');
    $pdf->Cell($item_widths[5],  6,number_format($tot_items_qty,2,'.',''),'T',0,'R');
    $pdf->Cell($item_widths[6]+$item_widths[7]+$item_widths[8],  6,'','TR',0,'R');
    $pdf->Ln();

    // print case nos if exists
    if(count($cnos_a)>0) {
      $pdf->SetFont('Arial','',8);
      $pdf->MultiCell(190,4,'Case/Container/Box Nos: '.implode(', ', $cnos_a),'LRB','L');
    }

    // print batch nos if exists
    if(count($bnos_a) > 0) {
      $pdf->SetFont('Arial','',8);
      $pdf->MultiCell(190,7,'Batch No(s): '.implode(',', array_unique($bnos_a)),'LRB','L');
    }

    // GST total
    $round_off = $net_pay - ($tot_taxable + $tot_tax_amount);

    $pdf->SetFont('Arial','B',8);
    $pdf->Cell($gst_width,  6,'Total Taxable Amount (Rs.)','L',0,'R');
    $pdf->Cell($item_widths[8],  6,number_format($tot_taxable,2,'.',''),'R',0,'R');
    $pdf->Ln(4);

    $pdf->Cell($gst_width,  6,'Total GST (Rs.)','L',0,'R');
    $pdf->Cell($item_widths[8],  6,number_format($tot_tax_amount,2,'.',''),'R',0,'R');
    $pdf->Ln(4);

    $pdf->Cell($gst_width,  6,'Rounding off (Rs.)','L',0,'R');
    $pdf->Cell($item_widths[8],  6,number_format($round_off,2,'.',''),'R',0,'R');
    $pdf->Ln(4);

    $pdf->Cell($gst_width,  6,'Invoice Value (Rs.)','L',0,'R');
    $pdf->SetFont('Arial','BU',10);
    $pdf->Cell($item_widths[8],  6,number_format($net_pay,2,'.',''),'R',0,'R');
    $pdf->Ln(3);

    $pdf->SetFont('Arial','',8);
    $pdf->Cell(190,6,'In words: '.Utilities::get_indian_currency($net_pay),'LRB',0,'C');
    $pdf->Ln();

    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(190,6,'GST Details','LRB',0,'C');
    $pdf->Ln();

    $pdf->SetFont('Arial','B',8);
    $pdf->Cell(15,6,'HSN/SAC','LRB',0,'C');
    $pdf->Cell(12,6,'Qty.','RB',0,'C');
    $pdf->Cell(18,6,'Taxable','RB',0,'C');
    $pdf->Cell(13,6,'IGST %','RB',0,'C');
    $pdf->Cell(20,6,'IGST (Rs.)','RB',0,'C');
    $pdf->Cell(13,6,'CGST %','RB',0,'C');
    $pdf->Cell(20,6,'CGST (Rs.)','RB',0,'C');
    $pdf->Cell(13,6,'SGST %','RB',0,'C');
    $pdf->Cell(20,6,'SGST (Rs.)','RB',0,'C');
    $pdf->Cell(20,6,'CESS (Rs.)','RB',0,'C');
    $pdf->Cell(26,6,'TOTAL GST (Rs.)','RB',0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',8);

    // dump($hsn_codes_array);
    // exit;

    $hsn_tot_billed_qty = $hsn_tot_tax = 0;
    $hsn_tot_taxable = $hsn_tot_igst_amount = $hsn_tot_cgst_amount = $hsn_tot_sgst_amount = 0;
    foreach($hsn_codes_array as $hsn_sac_code => $hsn_code_details) {
      $taxable_amount = $hsn_code_details['taxable'];
      $total_qty = $hsn_code_details['qty'];
      $cgst_percent = $hsn_code_details['cgst_percent'];
      $sgst_percent = $hsn_code_details['sgst_percent'];
      $cgst_amount = $hsn_code_details['cgst_value'];
      $sgst_amount = $hsn_code_details['sgst_value'];
      $total_tax = $hsn_code_details['cgst_value'] + $hsn_code_details['sgst_value'];
      $gst_percent = $hsn_code_details['cgst_percent'] + $hsn_code_details['sgst_percent'];
      if($gst_tax_type === 'intra') {
        $igst_percent = $igst_amount = 0;
      } else {
        $igst_percent = $cgst_percent + $sgst_percent;
        $igst_amount = $cgst_amount+$sgst_amount;
        $cgst_percent = $sgst_percent = $cgst_amount = $sgst_amount = 0;
      }

      $hsn_tot_igst_amount += $igst_amount;
      $hsn_tot_cgst_amount += $cgst_amount;
      $hsn_tot_sgst_amount += $sgst_amount;
      $hsn_tot_billed_qty += $total_qty;
      $hsn_tot_taxable += $taxable_amount;

      $hsn_tot_tax += ($igst_amount+$cgst_amount+$sgst_amount);

      $pdf->Cell(15,6,$hsn_sac_code,'LRB',0,'R');
      $pdf->Cell(12,6,number_format($total_qty, 2, '.', ''),'LB',0,'R');
      $pdf->Cell(18,6,number_format($taxable_amount, 2, '.', ''),'LRB',0,'R');
      $pdf->Cell(13,6,$igst_percent > 0 ? number_format($igst_percent, 2, '.', '').'%' : '','RB',0,'R');
      $pdf->Cell(20,6,$igst_amount > 0 ? number_format($igst_amount, 2, '.', '') : '','RB',0,'R');
      $pdf->Cell(13,6,$cgst_percent > 0 ? number_format($cgst_percent, 2, '.', '').'%' : '','RB',0,'R');
      $pdf->Cell(20,6,$cgst_amount > 0 ? number_format($cgst_amount, 2, '.', '') : '','RB',0,'R');
      $pdf->Cell(13,6,$sgst_percent > 0 ? number_format($sgst_percent, 2, '.', '').'%' : '','RB',0,'R');
      $pdf->Cell(20,6,$sgst_amount > 0 ? number_format($sgst_amount, 2, '.', '') : '','RB',0,'R');
      $pdf->Cell(20,6,'','RB',0,'R');
      $pdf->Cell(26,6,number_format($total_tax, 2, '.', ''),'RB',1,'R');
    }
    $pdf->SetFont('Arial','B',8);
    $pdf->Cell(15,6,'Totals','LRB',0,'R');
    $pdf->Cell(12,6,number_format($hsn_tot_billed_qty, 2, '.', ''),'LB',0,'R');
    $pdf->Cell(18,6,number_format($hsn_tot_taxable, 2, '.', ''),'LRB',0,'R');
    $pdf->Cell(13,6,'','RB',0,'R');
    $pdf->Cell(20,6,$igst_amount > 0 ? number_format($hsn_tot_igst_amount, 2, '.', '') : '','RB',0,'R');
    $pdf->Cell(13,6,'','RB',0,'R');
    $pdf->Cell(20,6,$cgst_amount > 0 ? number_format($hsn_tot_cgst_amount, 2, '.', '') : '','RB',0,'R');
    $pdf->Cell(13,6,'','RB',0,'R');
    $pdf->Cell(20,6,$sgst_amount > 0 ? number_format($hsn_tot_sgst_amount, 2, '.', '') : '','RB',0,'R');
    $pdf->Cell(20,6,'','RB',0,'R');
    $pdf->Cell(26,6,number_format($hsn_tot_tax, 2, '.', ''),'RB',1,'R');    
    // $remarks_invoice = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged';
    // $pdf->Ln();
    $pdf->SetFont('Arial','BU',8);
    $pdf->Cell(190,5,'Invoice Remarks','LR',0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',8);
    $pdf->Multicell(190,4,$remarks_invoice,'LRB','L');

    $pdf->Ln();
    $pdf->SetFont('Arial','BU',9);
    $pdf->Cell(130,6,'Invoice Terms & Conditions','',0,'C');
    $pdf->Cell(60,6,'Bank Details','',0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',9);

    // $tandc = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.';
    // $tandc = implode("\n", $tandc_a);

    $tandc_a = preg_split('/\r\n|[\r\n]/', $sale_details['tacB2B']);
    // dump($tandc_a);
    // exit;

    // $tandc_a = [];

    if(is_array($tandc_a) && count($tandc_a)>0) {
      $x = $pdf->getX();
      $y = $pdf->getY();
      foreach($tandc_a as $tandc) {
        $pdf->Multicell(130,5,$tandc,'','L');
      }
      $tac_x = $pdf->getX();
      $tac_y = $pdf->getY();

      $pdf->setXY($x+130, $y);
    } else {
      $tac_x = $x = $pdf->getX();
      $tac_y = $y = $pdf->getY();      

      $pdf->setXY($x+130, $y);
    }

    if(count($banks)>0 && isset($banks[$bank_key]) && $bank_key !== false) {
      $bank_account_no = 'Account No.: '.$banks[$bank_key]['accountNo'];
      $bank_name = 'Bank Name: '.$banks[$bank_key]['bankName'];
      $ifsc_code = 'IFSC Code: '.$banks[$bank_key]['ifscCode'];
      $bank_address = 'Bank Address: '.$banks[$bank_key]['bankAddress'];
      $pdf->Multicell(60,5,$bank_account_no,'','C');
      $x = $pdf->getX();
      $y = $pdf->getY();
      $pdf->setXY($x+130, $y);
      $pdf->Multicell(60,5,$bank_name,'','C');
      $x = $pdf->getX();
      $y = $pdf->getY();
      $pdf->setXY($x+130, $y);
      $pdf->Multicell(60,5,$ifsc_code,'','C');
    }

    // add signature headers
    $x = $pdf->getX();
    $y = $pdf->getY();

    if($tac_x > $x) {
      $final_x = $tac_x;
    } else {
      $final_x = $x;
    }
    if($tac_y > $y) {
      $final_y = $tac_y;
    } else {
      $final_y = $y;
    }
    
    $pdf->setXY($final_x, $final_y);

    // dump($taxable_values, $taxable_gst_value);
    // exit;

    $pdf->Output();
  }

  // print b2b sales invoice with IRN
  public function printB2BSalesInvoiceWithIrn(Request $request) {
    $sales_code = Utilities::clean_string($request->get('salesCode'));
    $slno = 0;
    $gst_percents_a = ['5.00','12.00','18.00','28.00'];
    $banks = [];

    $sales_response = $this->sales_model->get_sales_details($sales_code,false);
    $status = $sales_response['status'];
    if($status) {
      $sale_details = $sales_response['saleDetails'];
      $sale_item_details = $sale_details['itemDetails'];
      unset($sale_details['itemDetails']);
    } else {
      die('Invalid Sales Transaction.');
    }

    $bill_no = $sale_details['billNo'];
    $bill_date = date('d-M-Y',strtotime($sale_details['invoiceDate']));
    $bill_time = date('h:ia',strtotime($sale_details['createdTime']));
    $payment_method = Constants::$PAYMENT_METHODS_RC[$sale_details['paymentMethod']];
    $payment_method_num = (int)$sale_details['paymentMethod'];
    $tmp_cust_name = $sale_details['tmpCustName'];
    $customer_name  =  $sale_details['customerName'] !== '' ? $sale_details['customerName'] : '';
    $card_no = $sale_details['cardNo'] > 0 ? '* ****'.$sale_details['cardNo'] : '';
    $auth_code = $sale_details['authCode'] > 0 ? $sale_details['authCode'] : '****';

    $cn_no =  $sale_details['cnNo'];
    $referral_no = $sale_details['refCardNo'];
    $promo_code =  $sale_details['promoCode'];
    if($customer_name === '') {
      $customer_name = $tmp_cust_name;
    }

    $tax_calc_option = $sale_details['taxCalcOption'];

    $business_name  =   isset($sale_details['locationNameShort']) && $sale_details['locationNameShort'] !== '' ? $sale_details['locationNameShort'] : $sale_details['locationName'];
    $business_add1  =   $sale_details['locAddress1'];
    $business_add2  =   $sale_details['locAddress2'];
    $city_name      =   $sale_details['locCityName'];
    $state_name     =   Utilities::get_location_state_name($sale_details['locStateID']);
    $pincode        =   $sale_details['locPincode'];
    $business_add3  =   ucwords(strtolower($city_name)).', '.ucwords(strtolower($state_name)).' - '.$pincode;
    $phones         =   $sale_details['locPhones'];

    $gst_no         =   $sale_details['locGstNo'];
    $card_no        =   $sale_details['cardNo'] > 0 ? '* ****'.$sale_details['cardNo'] : '';
    $auth_code      =   $sale_details['authCode'] > 0 ? $sale_details['authCode'] : '****';

    $cn_no          =   $sale_details['cnNo'];
    $referral_no    =   $sale_details['refCardNo'];
    $net_pay        =   $sale_details['netPay'];
    $bank_id        =   $sale_details['bankID'];
    $remarks_invoice=   $sale_details['remarksInvoice'];
    if($bank_id > 0) {
      // get all banks from back-end.
      $result = $this->fin_model->banks_list();
      if($result['status']) {
        $banks = $result['banks'];
        $bank_key = array_search($bank_id, array_column($banks, 'bankID'));
      }
    }

    $loc_address = [
      'address1' => $business_add1,
      'address2' => $business_add2,
      'address3' => $business_add3,
      'phones' => $phones,
      'store_name' => $business_name,
      'gst_no' => $gst_no,
    ];

    // igst or (cgst and sgst)
    if((int)$sale_details['stateID'] > 0 && (int)$sale_details['locStateID'] > 0) {
      if((int)$sale_details['stateID'] === (int)$sale_details['locStateID']) {
        $gst_tax_type = 'intra';
      } else {
        $gst_tax_type = 'inter';
      }
    } else {
      $gst_tax_type = 'intra';
    }

    // customer array
    if($sale_details['customBillNo'] !== '') {
      $customer_info['custom_invoice_no'] = $sale_details['customBillNo'];
    } elseif($sale_details['customBillNo'] !== '') {
      $customer_info['custom_invoice_no'] = $sale_details['gstDocNo'];
    } else {
      $customer_info['custom_invoice_no'] = $sale_details['billNo'];
    }

    // $customer_info['custom_invoice_no'] = $sale_details['customBillNo'] !== '' ? $sale_details['customBillNo'] : $sale_details['billNo'];
    $customer_info['bill_no'] = $sale_details['billNo'];
    $customer_info['indent_no'] = $sale_details['indentNo'];
    $customer_info['bill_date'] = date("d-M-Y",strtotime($sale_details['invoiceDate']));
    $customer_info['location_name'] = $sale_details['locationName'];
    $customer_info['location_name_short'] = $sale_details['locationNameShort'];
    $customer_info['executive_name'] = $sale_details['executiveName'];
    $customer_info['agent_name'] = '';
    $customer_info['invoice_code'] = $sale_details['invoiceCode'];
    $customer_info['payment_method'] = $sale_details['paymentMethod'];
    $customer_info['credit_days'] = $sale_details['saCreditDays'];
    $customer_info['promo_code'] = $sale_details['promoCode'];
    $customer_info['campaign_name'] = $sale_details['campaignName'];
    $customer_info['promo_code_discount'] = $sale_details['promoDiscountPercent'];
    $customer_info['billing'] = [
      'customer_name' => $customer_name,
      'address' => $sale_details['address'],
      'city_name' => $sale_details['cityName'],
      'state_name' => Utilities::get_location_state_name($sale_details['stateID']),
      'country_name' => Utilities::get_country_name($sale_details['countryID']),
      'pincode' => $sale_details['pincode'],
      'phones' => $sale_details['phones'],
      'gst_no' => $sale_details['customerGstNo'],
      'state_id' => $sale_details['stateID'],
    ];
    $customer_info['shipping'] = [
      'customer_name' => $customer_name,
      'address' => $sale_details['shippingAddress'] !== '' ? $sale_details['shippingAddress'] : $sale_details['address'],
      'city_name' => $sale_details['shippingCityName'] !== '' ? $sale_details['shippingCityName'] : $sale_details['cityName'],
      'state_name' => $sale_details['shippingStateID'] !== '' ? Utilities::get_location_state_name($sale_details['shippingStateID']) : Utilities::get_location_state_name($sale_details['stateID']),
      'country_name' => $sale_details['shippingCountryID'] !== '' ? Utilities::get_country_name($sale_details['shippingCountryID']) : Utilities::get_country_name($sale_details['countryID']),
      'pincode' => $sale_details['shippingPincode'] !== '' ? $sale_details['shippingPincode'] : $sale_details['pincode'],
      'phones' => $sale_details['shippingPhones'] !== '' ? $sale_details['shippingPhones'] : $sale_details['phones'],
      'gst_no' => $sale_details['customerGstNo'],
      'state_id' => $sale_details['shippingStateID'] !== '' ? $sale_details['shippingStateID'] : $sale_details['stateID'],
    ];
    $customer_info['transport_details'] = [
      'transporter_name' => $sale_details['transporterName'],
      'lr_no' => $sale_details['lrNo'],
      'lr_date' => $sale_details['lrDate'] !== '0000-00-00' ? $sale_details['lrDate'] : '',
      'challan_no' => $sale_details['challanNo'],
      'way_bill_no' => $sale_details['wayBillNo'],
      'way_bill_date' => $sale_details['wayBillDate'] !== '00-00-0000' ? $sale_details['wayBillDate'] : '',
    ];
    $gst_details = [
      'irn' => $sale_details['gstIrn'],
      'gst_doc_no' => $sale_details['gstDocNo'],
      'gst_ack_no' => $sale_details['gstAckNo'],
      'gst_ack_date' => $sale_details['gstAckDate'],
    ];

    $brand_name = $sale_item_details[0]['brandName'];

    // dump($customer_info);
    // exit;

    $pdf = PdfWoHeaderFooter::getInstance();
    $pdf->AliasNbPages();
    $pdf->SetAutoPageBreak(false);
    $pdf->setMargins(5, 1, 1);
    $pdf->AddPage('P','A4');
    $pdf->setTitle('eInvoice'.'__'.$customer_info['custom_invoice_no'].'__'.date('jS F, Y'));

    // print sale items.
    $item_widths = [8,50,17,16,13,15,20,20,22];
              //    0, 1, 2, 3, 4, 5, 6, 7, 8

    $this->_add_einvoice_header($pdf, $customer_info, $item_widths, $loc_address, $gst_details, $brand_name);
    
    $sno = 0;
    $record_cntr = 0;
    $items_per_page = 20;
    $empty_rows_tobe_filled = count($sale_item_details) < $items_per_page ? $items_per_page-count($sale_item_details) : 0;
    $sale_item_details_filled = array_fill(count($sale_item_details)-1, $empty_rows_tobe_filled, []);
    $sale_item_final = array_merge($sale_item_details, $sale_item_details_filled);
    $unique_hsn_codes = array_unique(array_column($sale_item_details, 'hsnSacCode')); 

    // dump(count($sale_item_final));

    $taxable_values = $tax_amounts = $taxable_gst_value = [];
    $tot_bill_value = $tot_discount = $tot_taxable = $tot_items_qty = 0;
    $tot_tax_amount = 0;
    $hsn_code_qty = $hsn_code_taxable = $hsn_cgst_value = $hsn_sgst_value = 0;
    $cnos_a = $hsn_codes_array = $bnos_a = [];
    foreach($sale_item_final as $item_details) {
      if(is_array($item_details) && count($item_details) > 0) {
        $item_qty = $amount = $discount = $taxable = $tax_value = 0;
        $hsn_code_qty = $hsn_code_taxable = $hsn_cgst_value = $hsn_sgst_value = 0;

        $sno++;

        $item_name = $item_details['itemName'];
        $brand_name = $item_details['brandName'];
        $hsn_sac_code = $item_details['hsnSacCode'];
        $item_qty = (float)$item_details['itemQty'];
        $uom_name = $item_details['uomName'];
        $discount = $item_details['discountAmount'];
        $tax_percent = $item_details['taxPercent'];
        $mrp = $item_details['mrp'];
        $cno = $item_details['cno'];
        if($item_details['cno'] !== '') {
          $cnos_a[] = $item_details['cno'];
        }
        if($item_details['bno'] !== '') {
          $bnos_a[] = $item_details['bno'];
        }        

        $amount = (float)round($item_qty*$mrp,2);
        $taxable = (float)round($amount-$discount,2);
        $tax_value = (float)round(($taxable*$tax_percent/100),2);

        $cgst_percent = $sgst_percent = round($tax_percent/2,2);
        $cgst_value = $sgst_value = round($tax_value/2,2);

        $tot_items_qty += $item_qty;
        $tot_bill_value += $amount;
        $tot_discount += $discount;
        $tot_taxable += $taxable;
        $tot_tax_amount += $tax_value;

        if(isset($taxable_values[$tax_percent])) {
          $taxable_amount = $taxable_values[$tax_percent] + ($taxable);
          $gst_value = $taxable_gst_value[$tax_percent] + $tax_value;

          $taxable_values[$tax_percent] = $taxable_amount;
          $taxable_gst_value[$tax_percent] = $gst_value;
        } else {
          $taxable_values[$tax_percent] = $taxable;
          $taxable_gst_value[$tax_percent] = $tax_value;
        }

        if(isset($hsn_codes_array["$hsn_sac_code"])) {
          $hsn_codes_array["$hsn_sac_code"]['qty'] = $hsn_codes_array["$hsn_sac_code"]['qty']+$item_qty;
          $hsn_codes_array["$hsn_sac_code"]['taxable'] = $hsn_codes_array["$hsn_sac_code"]['taxable']+$taxable;
          $hsn_codes_array["$hsn_sac_code"]['cgst_value'] = (float)$hsn_codes_array["$hsn_sac_code"]['cgst_value']+(float)$cgst_value;
          $hsn_codes_array["$hsn_sac_code"]['sgst_value'] = (float)$hsn_codes_array["$hsn_sac_code"]['sgst_value']+(float)$sgst_value;
        } else {
          $hsn_codes_array["$hsn_sac_code"]['qty'] = $item_qty;
          $hsn_codes_array["$hsn_sac_code"]['taxable'] = $taxable;
          $hsn_codes_array["$hsn_sac_code"]['cgst_percent'] = $cgst_percent;
          $hsn_codes_array["$hsn_sac_code"]['sgst_percent'] = $sgst_percent;
          $hsn_codes_array["$hsn_sac_code"]['cgst_value'] = $cgst_value;
          $hsn_codes_array["$hsn_sac_code"]['sgst_value'] = $sgst_value;
        }

        // dump($hsn_codes_array);

        $pdf->SetFont('Arial','',9);
        $pdf->Cell($item_widths[0],  5,$sno,'LRB',0,'R');
        $pdf->Cell($item_widths[1],  5,$item_name,'RB',0,'L');
        $pdf->Cell($item_widths[2],  5,$hsn_sac_code,'RB',0,'L');
        $pdf->Cell(             21,  5,$cno,'RB',0,'L');
        $pdf->Cell($item_widths[5],  5,number_format($item_qty,2,'.',''),'RB',0,'R');
        $pdf->Cell($item_widths[3],  5,number_format($mrp,2,'.',''),'RB',0,'R');        
        $pdf->Cell($item_widths[4],  5,$uom_name,'RB',0,'R');
        $pdf->Cell($item_widths[6],  5,number_format($amount,2,'.',''),'RB',0,'R');
        $pdf->Cell($item_widths[7],  5,$discount > 0 ? number_format($discount,2,'.','') : '','RB',0,'R');
        $pdf->Cell($item_widths[8],  5,number_format($taxable,2,'.',''),'RB',0,'R');
      } else {
        $pdf->Cell($item_widths[0],  5,'','LR',0,'R');
        $pdf->Cell($item_widths[1],  5,'','R',0,'L');
        $pdf->Cell($item_widths[2],  5,'','R',0,'L');
        $pdf->Cell(             21,  5,'','R',0,'L');
        $pdf->Cell($item_widths[5],  5,'','R',0,'L');
        $pdf->Cell($item_widths[3],  5,'','R',0,'R');
        $pdf->Cell($item_widths[4],  5,'','R',0,'R');        
        $pdf->Cell($item_widths[6],  5,'','R',0,'R');
        $pdf->Cell($item_widths[7],  5,'','R',0,'R');
        $pdf->Cell($item_widths[8],  5,'','R',0,'R');
      }
      $pdf->Ln();
      $record_cntr++;
      if($record_cntr === 30) {
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Cell($item_widths[0]+$item_widths[1]+$item_widths[2]+
                   $item_widths[5]+$item_widths[3]+$item_widths[4]+
                   $item_widths[6]+$item_widths[7]+$item_widths[8]+21,  5,"cont...",0,0,'L')                
        $pdf->setMargins(5, 1, 1);
        $pdf->AddPage('P','A4');        
        $this->_add_einvoice_header($pdf, $customer_info, $item_widths, $loc_address, $gst_details, $brand_name);
        $record_cntr=0;
      }
    }

    $round_off = $net_pay - ($tot_taxable + $tot_tax_amount);

    // print batch nos if exists
    if(count($bnos_a) > 0) {
      $pdf->SetFont('Arial','',8);
      $pdf->MultiCell(202,5,'Batch No(s): '.implode(',', array_unique($bnos_a)),'LRBT','L');
    }

    // print totals
    $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + 23;
    $gst_width = array_sum($item_widths) - $item_widths[8];
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(25,    6,'Total Qty.','LT',0,'C');
    $pdf->Cell(25,    6,'Gross Value','LT',0,'C');
    $pdf->Cell(25,    6,'Discount','LT',0,'C');
    $pdf->Cell(25,    6,'Taxable (in Rs.)','LT',0,'C');
    $pdf->Cell(25,    6,'GST (in Rs.)','LT',0,'C');
    $pdf->Cell(25,    6,'Roff (in Rs.)','LT',0,'C');
    $pdf->Cell(52,    6,'Invoice Value (in Rs.)','LTR',0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(25,    6, number_format($tot_items_qty,2,'.',''),'LTB',0,'R');
    $pdf->Cell(25,    6, number_format($tot_bill_value,2,'.',''),'LTB',0,'R');
    $pdf->Cell(25,    6, number_format($tot_discount,2,'.',''),'LTB',0,'R');
    $pdf->Cell(25,    6, number_format($tot_taxable,2,'.',''),'LTB',0,'R');
    $pdf->Cell(25,    6, number_format($tot_tax_amount,2,'.',''),'LTB',0,'R');
    $pdf->Cell(25,    6, number_format($round_off,2,'.',''),'LTB',0,'R');
    $pdf->SetFont('Arial','B',13);
    $pdf->Cell(52,    6, number_format($net_pay,2,'.',''),'LTBR',0,'R');
    $pdf->Ln();

    // GST total
    $pdf->SetFont('Arial','I',8);
    $pdf->Cell(202,  5,'( In words: '.Utilities::get_indian_currency($net_pay).' )','LR',0,'R');
    $pdf->Ln();

    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(202,5,'GST Details','LRB',0,'C');
    $pdf->Ln();

    $pdf->SetFont('Arial','B',8);
    $pdf->Cell(27,4,'HSN/SAC','LRB',0,'C');
    $pdf->Cell(12,4,'Qty.','RB',0,'C');
    $pdf->Cell(18,4,'Taxable','RB',0,'C');
    $pdf->Cell(13,4,'IGST %','RB',0,'C');
    $pdf->Cell(20,4,'IGST (Rs.)','RB',0,'C');
    $pdf->Cell(13,4,'CGST %','RB',0,'C');
    $pdf->Cell(20,4,'CGST (Rs.)','RB',0,'C');
    $pdf->Cell(13,4,'SGST %','RB',0,'C');
    $pdf->Cell(20,4,'SGST (Rs.)','RB',0,'C');
    $pdf->Cell(20,4,'CESS (Rs.)','RB',0,'C');
    $pdf->Cell(26,4,'TOTAL GST (Rs.)','RB',0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',8);

    // dump($hsn_codes_array);
    // exit;

    $hsn_tot_billed_qty = $hsn_tot_tax = 0;
    $hsn_tot_taxable = $hsn_tot_igst_amount = $hsn_tot_cgst_amount = $hsn_tot_sgst_amount = 0;
    foreach($hsn_codes_array as $hsn_sac_code => $hsn_code_details) {
      $taxable_amount = $hsn_code_details['taxable'];
      $total_qty = $hsn_code_details['qty'];
      $cgst_percent = $hsn_code_details['cgst_percent'];
      $sgst_percent = $hsn_code_details['sgst_percent'];
      $cgst_amount = $hsn_code_details['cgst_value'];
      $sgst_amount = $hsn_code_details['sgst_value'];
      $total_tax = $hsn_code_details['cgst_value'] + $hsn_code_details['sgst_value'];
      $gst_percent = $hsn_code_details['cgst_percent'] + $hsn_code_details['sgst_percent'];
      if($gst_tax_type === 'intra') {
        $igst_percent = $igst_amount = 0;
      } else {
        $igst_percent = $cgst_percent + $sgst_percent;
        $igst_amount = $cgst_amount+$sgst_amount;
        $cgst_percent = $sgst_percent = $cgst_amount = $sgst_amount = 0;
      }

      $hsn_tot_igst_amount += $igst_amount;
      $hsn_tot_cgst_amount += $cgst_amount;
      $hsn_tot_sgst_amount += $sgst_amount;
      $hsn_tot_billed_qty += $total_qty;
      $hsn_tot_taxable += $taxable_amount;

      $hsn_tot_tax += ($igst_amount+$cgst_amount+$sgst_amount);

      $pdf->Cell(27,4,$hsn_sac_code,'LB',0,'R');
      $pdf->Cell(12,4,number_format($total_qty, 2, '.', ''),'LB',0,'R');
      $pdf->Cell(18,4,number_format($taxable_amount, 2, '.', ''),'LRB',0,'R');
      $pdf->Cell(13,4,$igst_percent > 0 ? number_format($igst_percent, 2, '.', '').'%' : '','RB',0,'R');
      $pdf->Cell(20,4,$igst_amount > 0 ? number_format($igst_amount, 2, '.', '') : '','RB',0,'R');
      $pdf->Cell(13,4,$cgst_percent > 0 ? number_format($cgst_percent, 2, '.', '').'%' : '','RB',0,'R');
      $pdf->Cell(20,4,$cgst_amount > 0 ? number_format($cgst_amount, 2, '.', '') : '','RB',0,'R');
      $pdf->Cell(13,4,$sgst_percent > 0 ? number_format($sgst_percent, 2, '.', '').'%' : '','RB',0,'R');
      $pdf->Cell(20,4,$sgst_amount > 0 ? number_format($sgst_amount, 2, '.', '') : '','RB',0,'R');
      $pdf->Cell(20,4,'','RB',0,'R');
      $pdf->Cell(26,4,number_format($total_tax, 2, '.', ''),'RB',1,'R');
    }

    $pdf->SetFont('Arial','B',8);
    $pdf->Cell(27,4,'Totals','LB',0,'R');
    $pdf->Cell(12,4,number_format($hsn_tot_billed_qty, 2, '.', ''),'LB',0,'R');
    $pdf->Cell(18,4,number_format($hsn_tot_taxable, 2, '.', ''),'LRB',0,'R');
    $pdf->Cell(13,4,'','RB',0,'R');
    $pdf->Cell(20,4,$igst_amount > 0 ? number_format($hsn_tot_igst_amount, 2, '.', '') : '','RB',0,'R');
    $pdf->Cell(13,4,'','RB',0,'R');
    $pdf->Cell(20,4,$cgst_amount > 0 ? number_format($hsn_tot_cgst_amount, 2, '.', '') : '','RB',0,'R');
    $pdf->Cell(13,4,'','RB',0,'R');
    $pdf->Cell(20,4,$sgst_amount > 0 ? number_format($hsn_tot_sgst_amount, 2, '.', '') : '','RB',0,'R');
    $pdf->Cell(20,4,'','RB',0,'R');
    $pdf->Cell(26,4,number_format($hsn_tot_tax, 2, '.', ''),'RB',1,'R');    
    $pdf->SetFont('Arial','BU',8);
    $pdf->Cell(202,5,'Invoice Remarks','LR',0,'C');
    $pdf->Ln(1);
    $pdf->SetFont('Arial','',8);
    $pdf->Multicell(202,5,$remarks_invoice,'LRB','L');

    $tandc_a = preg_split('/\r\n|[\r\n]/', $sale_details['tacB2B']);

    $pdf->Ln(1);
    $pdf->SetFont('Arial','I',9);
    $pdf->MultiCell(202,4,implode(' ', $tandc_a),0,'LR',false);
    if(count($banks)>0 && isset($banks[$bank_key]) && $bank_key !== false) {
      $bank_account_no = 'Account No.: '.$banks[$bank_key]['accountNo'];
      $bank_name = 'Bank Name: '.$banks[$bank_key]['bankName'];
      $ifsc_code = 'IFSC Code: '.$banks[$bank_key]['ifscCode'];
      $bank_address = 'Bank Address: '.$banks[$bank_key]['bankAddress'];

      $bank_details_string = $bank_account_no.', '.$bank_name.', '.$ifsc_code.', '.$bank_address;
      $pdf->SetFont('Arial','',8);
      $pdf->Ln(1);
      $pdf->MultiCell(202,4,'Bank Details:: '.$bank_details_string,0,'L',false);
    }

    $pdf->Output();
  }

  // prints sales register.
  public function printSalesRegister(Request $request) {
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 1000;
    $total_records = [];
    $page_url = '/reports/sales-register';

    $client_locations = Utilities::get_client_locations();
    $sa_executives = $this->_get_sales_executives();


    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_form_data($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
      } else {
        $error_message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error: '.json_encode($validation['form_errors']);
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect($page_url);        
      }

      // hit api
      $sales_api_response = $this->sales_model->get_sales($page_no, $per_page, $form_data);
      if($sales_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect($page_url);
      } else {
        $total_records = $sales_api_response['sales'];
        $total_pages = $sales_api_response['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $sales_api_response = $this->sales_model->get_sales($i, $per_page, $form_data);
            if($sales_api_response['status']) {
              $total_records = array_merge($total_records,$sales_api_response['sales']);
            }
          }
        }

        if(is_array($client_locations) && count($client_locations)>0 && isset($form_data['locationCode']) && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }
        $heading1 = 'Daywise Sales Register';
        $heading2 = '( from '.$form_data['fromDate'].' to '.$form_data['toDate'].' )';
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }
        $csv_headings = [ [$heading1], [$heading2] ];
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        $total_records = $this->_format_sales_register_for_csv($total_records);
        Utilities::download_as_CSV_attachment('SalesRegister', $csv_headings, $total_records);
        return;
      }

      // dump($total_records);
      // exit;

      // start PDF printing.
      $item_widths = array(10,25,35,28,25,25,25,25,25,54);
      //                    0, 1, 2, 3, 4, 5, 6, 7, 8, 9
      $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2];
      $slno = 0;

      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('L','A4');

      // Print Bill Information.
      $pdf->SetFont('Arial','B',16);
      $pdf->Cell(0,0,$heading1,'',1,'C');
      $pdf->SetFont('Arial','B',10);
      $pdf->Ln(5);
      $pdf->Cell(0,0,$heading2,'',1,'C');

      $pdf->SetFont('Arial','B',9);
      $pdf->Ln(5);
      $pdf->Cell($item_widths[0],6,'SNo.','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Payment Mode','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'Bill No. & Date','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'Gross Amt. (Rs.)','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'Discount (Rs.)','RTB',0,'C');
      $pdf->Cell($item_widths[5],6,'Billed (Rs.)','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'Taxable (Rs.)','RTB',0,'C');
      $pdf->Cell($item_widths[7],6,'GST (Rs.)','RTB',0,'C');
      $pdf->Cell($item_widths[8],6,'RndOff (Rs.)','RTB',0,'C');  
      $pdf->Cell($item_widths[9],6,'CustomerName','RTB',0,'C');
      $pdf->SetFont('Arial','',9);

      $tot_gross_amount = $tot_discount = $tot_taxable = $tot_gst = $tot_round_off = $tot_net_pay = 0;
      foreach($total_records as $record_details) {
        $slno++;
        $gross_amount = $discount = $taxable = $gst = $round_off = $net_pay = 0;
        $payment_method = Constants::$PAYMENT_METHODS_RC_SHORT[$record_details['paymentMethod']];
        $bill_info = $record_details['billNo'].' / '.date("d-m-y", strtotime($record_details['invoiceDate']));
        $tran_info = date("d-M-Y h:ia", strtotime($record_details['createdOn']));
        if($record_details['customerName'] !== '') {
          $customer_name = $record_details['customerName'];
        } elseif($record_details['customerName'] !== '') {
          $customer_name = $record_details['tmpCustName'];          
        } else {
          $customer_name = '';
        }

        $gross_amount = $record_details['billAmount'];
        $discount = $record_details['discountAmount'];
        $taxable = $record_details['totalAmount'];
        $gst = $record_details['taxAmount'];
        $round_off = $record_details['roundOff'];
        $net_pay = $record_details['netPay'];

        $tot_gross_amount += $gross_amount;
        $tot_discount += $discount;
        $tot_taxable += $taxable;
        $tot_gst += $gst;
        $tot_round_off += $round_off;
        $tot_net_pay += $net_pay;
        
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$payment_method,'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$bill_info,'RTB',0,'R');
        $pdf->Cell($item_widths[3],6,number_format($gross_amount,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[4],6,number_format($discount,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,number_format($net_pay,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,number_format($taxable,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,number_format($gst,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,number_format($round_off,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[9],6,substr($customer_name,0,20),'RTB',0,'L');  
      }
    
      $pdf->Ln();
      $pdf->SetFont('Arial','B',9);    
      $pdf->Cell($totals_width,6,'Totals','LRTB',0,'R');
      $pdf->Cell($item_widths[3],6,number_format($tot_gross_amount,2,'.',''),'LRTB',0,'R');
      $pdf->Cell($item_widths[4],6,number_format($tot_discount,2,'.',''),'LRTB',0,'R');    
      $pdf->Cell($item_widths[5],6,number_format($tot_net_pay,2,'.',''),'LRTB',0,'R');
      $pdf->Cell($item_widths[6],6,number_format($tot_taxable,2,'.',''),'LRTB',0,'R');
      $pdf->Cell($item_widths[7],6,number_format($tot_gst,2,'.',''),'LRTB',0,'R');
      $pdf->Cell($item_widths[8],6,number_format($tot_round_off,2,'.',''),'LRTB',0,'R');

      $pdf->Output();
    }

    $controller_vars = array(
      'page_title' => 'Print Sales Register',
      'icon_name' => 'fa fa-inr',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'sa_executives' => array('' => 'All Executives') + $sa_executives,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
    );

    // render template
    return [$this->template->render_view('print-sales-register', $template_vars), $controller_vars];
  }

  // prints itemwise sales register
  public function itemwiseSalesRegister(Request $request) {
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 1000;
    $total_records = [];
    $sort_by_a = ['item' => 'SortBy - Itemwise', 'qty' => 'SortBy - Qtywise'];    

    $client_locations = Utilities::get_client_locations();
    $sa_executives = $this->_get_sales_executives();

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_form_data_itemwise_sr($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
      } else {
        $error_message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error: '.json_encode($validation['form_errors']);
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/itemwise-sales-register');        
      }

      // hit api
      $sales_api_response = $this->sales_model->get_itemwise_sales_report($form_data);
      // dump($sales_api_response);
      // exit;
      if($sales_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/itemwise-sales-register');
      } else {
        $total_records = $sales_api_response['summary']['results'];
        $total_pages = $sales_api_response['summary']['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $form_data['pageNo'] = $i;
            $sales_api_response = $this->sales_model->get_itemwise_sales_report($form_data);
            if($sales_api_response['status']) {
              $total_records = array_merge($total_records,$sales_api_response['summary']['results']);
            }
          }
        }
        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }
        $heading1 = 'Itemwise Sales Register';
        $heading2 = '( from '.$form_data['fromDate'].' to '.$form_data['toDate'].' )';
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }
        $csv_headings = [ [$heading1], [$heading2] ];
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        $total_records = $this->_format_itemwise_sales_register_for_csv($total_records);
        Utilities::download_as_CSV_attachment('ItemwiseSalesRegister', $csv_headings, $total_records);
        return;
      }

      // start PDF printing.
      $item_widths = array(10,38,25,25,18,16,21,21,21);
                        //  0, 1, 2, 3, 4, 5, 6, 7, 8
      $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3] + $item_widths[4];
      $slno = 0;

      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('P','A4');

      // Print Bill Information.
      $pdf->SetFont('Arial','B',16);
      $pdf->Cell(0,0,$heading1,'',1,'C');
      $pdf->SetFont('Arial','B',10);
      $pdf->Ln(5);
      $pdf->Cell(0,0,$heading2,'',1,'C');

      $pdf->SetFont('Arial','B',9);
      $pdf->Ln(5);
      $pdf->Cell($item_widths[0],6,'SNo.','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'Brand','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'Category','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'Item Rate','RTB',0,'C');
      $pdf->Cell($item_widths[5],6,'Sold Qty.','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'Total Amt.','RTB',0,'C');  
      $pdf->Cell($item_widths[7],6,'Total Disc.','RTB',0,'C');
      $pdf->Cell($item_widths[8],6,'Net Value','RTB',0,'C');
      $pdf->SetFont('Arial','',9);

      $tot_sold_qty = $tot_amount = $tot_discount = $tot_net_pay = 0;
      $slno = 0;
      foreach($total_records as $record_details) {
        $slno++;
        $net_pay = $record_details['saleValue'] - $record_details['discountAmount'];
        $tot_sold_qty += $record_details['soldQty'];
        $tot_amount += $record_details['saleValue'];
        $tot_discount += $record_details['discountAmount'];
        $tot_net_pay += $net_pay;
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,substr($record_details['itemName'],0,18),'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,substr($record_details['brandName'],0,12),'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,substr($record_details['categoryName'],0,12),'RTB',0,'L');            
        $pdf->Cell($item_widths[4],6,number_format($record_details['saleRate'],2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,number_format($record_details['soldQty'],2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,number_format($record_details['saleValue'],2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,number_format($record_details['discountAmount'],2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,number_format($net_pay,2,'.',''),'RTB',0,'R');  
      }

      $pdf->Ln();
      $pdf->SetFont('Arial','B',10);    
      $pdf->Cell($totals_width,6,'Totals','LTB',0,'R');
      $pdf->Cell($item_widths[5],6,number_format($tot_sold_qty,2,'.',''),'LTB',0,'R');
      $pdf->Cell($item_widths[6],6,number_format($tot_amount,2,'.',''),'LTB',0,'R');    
      $pdf->Cell($item_widths[7],6,number_format($tot_discount,2,'.',''),'LTB',0,'R');
      $pdf->Cell($item_widths[8],6,number_format($tot_net_pay,2,'.',''),'LRTB',0,'R');

      $pdf->Output();
    }

    $controller_vars = array(
      'page_title' => 'Print Itemwise Sales Register',
      'icon_name' => 'fa fa-print',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'sa_executives' => array('' => 'All Executives') + $sa_executives,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
      'sort_by_a' => $sort_by_a,
    );

    // render template
    return [$this->template->render_view('itemwise-sales-register', $template_vars), $controller_vars];
  }

  // prints billwise and itemwise sales register
  public function salesBillwiseItemwise(Request $request) {
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 1000;
    $total_records = [];

    $client_locations = Utilities::get_client_locations();

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_form_data_billwise_itemwise($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
      } else {
        $error_message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error: '.json_encode($validation['form_errors']);
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-billwise-itemwise');        
      }

      // hit api
      $sales_api_response = $this->sales_model->get_billwise_itemwise_sales($form_data);
      if($sales_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-billwise-itemwise');
      } else {
        $total_records = $sales_api_response['summary']['sales'];
        $total_pages = $sales_api_response['summary']['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $form_data['pageNo'] = $i;
            $sales_api_response = $this->sales_model->get_billwise_itemwise_sales($form_data);
            if($sales_api_response['status']) {
              $total_records = array_merge($total_records,$sales_api_response['summary']['sales']);
            }
          }
        }
        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }
        $heading1 = 'Billwise and Itemwise Sales Register';
        $heading2 = '( from '.$form_data['fromDate'].' to '.$form_data['toDate'].' )';
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }
        $csv_headings = [ [$heading1], [$heading2] ];
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        $total_records = $this->_format_billwise_itemwise_sr_for_csv($total_records);
        Utilities::download_as_CSV_attachment('BillwiseItemwiseSalesRegister', $csv_headings, $total_records);
        return;
      }

      // start PDF printing.
      $item_widths = array(10,16,18,42,13,16,18,18,18,22);
                        //  0, 1, 2, 3, 4, 5, 6, 7, 8, 9
      $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3] + $item_widths[4];
      $slno = 0;

      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('P','A4');

      // Print Bill Information.
      $pdf->SetFont('Arial','B',16);
      $pdf->Cell(0,0,$heading1,'',1,'C');
      $pdf->SetFont('Arial','B',10);
      $pdf->Ln(5);
      $pdf->Cell(0,0,$heading2,'',1,'C');

      $pdf->SetFont('Arial','B',8);
      $pdf->Ln(5);
      $pdf->Cell($item_widths[0],6,'SNo.','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Bill No.','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'Bill Date','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'Item Name','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'Qty.','RTB',0,'C');
      $pdf->Cell($item_widths[5],6,'Item Rate','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'Gross Amt.','RTB',0,'C');  
      $pdf->Cell($item_widths[7],6,'Discount','RTB',0,'C');
      $pdf->Cell($item_widths[8],6,'Net Amount','RTB',0,'C');
      $pdf->Cell($item_widths[9],6,'Cust.Name','RTB',0,'C');      
      $pdf->SetFont('Arial','',8);

      $tot_sold_qty = $tot_amount = $tot_discount = $tot_net_pay = 0;
      $slno = 0;
      $old_bill_no = $new_bill_no = $total_records[0]['invoiceNo'];
      $bill_qty = 0;
      foreach($total_records as $key => $record_details) {
        $slno++;
        $new_bill_no = $record_details['invoiceNo'];
        if($old_bill_no !== $new_bill_no) {

          $bill_total = $total_records[$key-1]['billAmount'];
          $bill_discount = $total_records[$key-1]['billDiscount'];
          $netpay =  $total_records[$key-1]['netpay'];

          $pdf->Ln();
          $pdf->SetFont('Arial','B',8);
          $pdf->Cell($item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3],6,'BILL TOTALS','LRTB',0,'R');
          $pdf->Cell($item_widths[4],6,number_format($bill_qty,2,'.',''),'RTB',0,'R');
          $pdf->Cell($item_widths[5],6,'','RTB',0,'R');
          $pdf->Cell($item_widths[6],6,number_format($bill_total, 2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[7],6,number_format($bill_discount, 2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[8],6,number_format($netpay, 2, '.', ''),'RTB',0,'R');  
          $pdf->Cell($item_widths[9],6,'','RTB',0,'L');
          $pdf->SetFont('Arial','',8);

          $tot_sold_qty += $bill_qty;
          $tot_amount += $bill_total;
          $tot_discount += $bill_discount;
          $tot_net_pay += $netpay;

          $old_bill_no = $new_bill_no;
          $bill_qty = $bill_total = $bill_discount = $netpay = 0;
        }        

        $bill_qty += $record_details['soldQty'];
        $item_amount = round($record_details['soldQty']*$record_details['mrp'], 2);
        $item_value = $item_amount - $record_details['itemDiscount'];

        if($record_details['customerName'] !== '') {
          $customer_name = $record_details['customerName'];
        } elseif($record_details['tmpCustomerName'] !== '') {
          $customer_name = $record_details['tmpCustomerName'];
        } else {
          $customer_name = '';
        }

        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$record_details['invoiceNo'],'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,date("d-m-Y", strtotime($record_details['invoiceDate'])),'RTB',0,'L');            
        $pdf->Cell($item_widths[3],6,substr($record_details['itemName'],0,18),'RTB',0,'L');
        $pdf->Cell($item_widths[4],6,number_format($record_details['soldQty'],2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,number_format($record_details['mrp'],2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,number_format($item_amount,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,number_format($record_details['itemDiscount'],2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,number_format($item_value,2,'.',''),'RTB',0,'R');  
        $pdf->Cell($item_widths[9],6,substr($customer_name,0,10),'RTB',0,'L');
      }

      $bill_total = $total_records[$key]['billAmount'];
      $bill_discount = $total_records[$key]['billDiscount'];
      $netpay =  $total_records[$key]['netpay'];

      $tot_sold_qty += $bill_qty;
      $tot_amount += $bill_total;
      $tot_discount += $bill_discount;
      $tot_net_pay += $netpay;      

      $pdf->Ln();
      $pdf->SetFont('Arial','B',8);
      $pdf->Cell($item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3],6,'BILL TOTALS','LRTB',0,'R');
      $pdf->Cell($item_widths[4],6,number_format($bill_qty,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[6],6,number_format($bill_total, 2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[7],6,number_format($bill_discount, 2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[8],6,number_format($netpay, 2, '.', ''),'RTB',0,'R');  
      $pdf->Cell($item_widths[9],6,'','RTB',0,'L');

      $pdf->Ln();
      $pdf->Cell($item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3],6,'REPORT TOTALS','LRTB',0,'R');
      $pdf->Cell($item_widths[4],6,number_format($tot_sold_qty,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[6],6,number_format($tot_amount, 2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[7],6,number_format($tot_discount, 2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[8],6,number_format($tot_net_pay, 2, '.', ''),'RTB',0,'R');  
      $pdf->Cell($item_widths[9],6,'','RTB',0,'L');
      $pdf->SetFont('Arial','',8);      

      $pdf->Output();
    }

    $controller_vars = array(
      'page_title' => 'Print Billwise and Itemwise Sales Register',
      'icon_name' => 'fa fa-print',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
    );

    // render template
    return [$this->template->render_view('item-bill-wise-sales-register', $template_vars), $controller_vars];
  }

  // prints billwise and itemwise sales register
  public function salesBillwiseItemwiseCasewise(Request $request) {
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 1000;
    $total_records = [];

    $client_locations = Utilities::get_client_locations();

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_form_data_billwise_itemwise($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
      } else {
        $error_message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error: '.json_encode($validation['form_errors']);
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-billwise-itemwise');        
      }

      // hit api
      $sales_api_response = $this->sales_model->get_billwise_itemwise_sales($form_data);
      if($sales_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-billwise-itemwise');
      } else {
        $total_records = $sales_api_response['summary']['sales'];
        $total_pages = $sales_api_response['summary']['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $form_data['pageNo'] = $i;
            $sales_api_response = $this->sales_model->get_billwise_itemwise_sales($form_data);
            if($sales_api_response['status']) {
              $total_records = array_merge($total_records,$sales_api_response['summary']['sales']);
            }
          }
        }

        // dump($total_records);
        // exit;

        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }
        $heading1 = 'Billwise, Itemwise and Casewise Sales Register';
        $heading2 = '( from '.$form_data['fromDate'].' to '.$form_data['toDate'].' )';
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }
        $csv_headings = [ [$heading1], [$heading2] ];
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        $total_records = $this->_format_billwise_itemwise_sr_for_csv($total_records);
        Utilities::download_as_CSV_attachment('BillwiseItemwiseCasewiseSalesRegister', $csv_headings, $total_records);
        return;
      }

      // start PDF printing.
      $item_widths = array(10,14,17,42,13,16,18,18,20,18,20,40,30);
                        //  0, 1, 2, 3, 4, 5, 6, 7, 8, 9,10,11,12
      $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3] + $item_widths[4];
      $slno = 0;

      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('L','A4');

      // Print Bill Information.
      $pdf->SetFont('Arial','B',16);
      $pdf->Cell(0,0,$heading1,'',1,'C');
      $pdf->SetFont('Arial','B',10);
      $pdf->Ln(5);
      $pdf->Cell(0,0,$heading2,'',1,'C');

      $pdf->SetFont('Arial','B',8);
      $pdf->Ln(5);
      $pdf->Cell($item_widths[0],6,'SNo.','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Bill No.','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'Bill Date','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'Item Name','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'Qty.','RTB',0,'C');
      $pdf->Cell($item_widths[5],6,'CASE No.','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'Item Rate','RTB',0,'C');
      $pdf->Cell($item_widths[7],6,'Gross Amt.','RTB',0,'C');  
      $pdf->Cell($item_widths[8],6,'Discount','RTB',0,'C');
      $pdf->Cell($item_widths[9],6,'Tax','RTB',0,'C');
      $pdf->Cell($item_widths[10],6,'Net Amount','RTB',0,'C');
      $pdf->Cell($item_widths[11],6,'Customer Name','RTB',0,'C');      
      $pdf->Cell($item_widths[12],6,'Remarks','RTB',0,'C');      
      $pdf->SetFont('Arial','',8);
      
      $tot_sold_qty = $tot_amount = $tot_discount = $tot_net_pay = 0;
      $slno = $tot_bill_tax = 0;
      $old_bill_no = $new_bill_no = $total_records[0]['invoiceNo'];
      $bill_qty = 0;
      foreach($total_records as $key => $record_details) {
        $slno++;
        $new_bill_no = $record_details['invoiceNo'];
        if($old_bill_no !== $new_bill_no) {

          $bill_total = $total_records[$key-1]['billAmount'];
          $bill_discount = $total_records[$key-1]['billDiscount'];
          $bill_tax = $total_records[$key-1]['taxAmount'];
          $netpay =  $total_records[$key-1]['netpay'];

          $pdf->Ln();
          $pdf->SetFont('Arial','B',8);
          $pdf->Cell($item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3],6,'BILL TOTALS','LRTB',0,'R');
          $pdf->Cell($item_widths[4],6,number_format($bill_qty,2,'.',''),'RTB',0,'R');
          $pdf->Cell($item_widths[5],6,'','RTB',0,'R');
          $pdf->Cell($item_widths[6],6,'','RTB',0,'R');
          $pdf->Cell($item_widths[7],6,number_format($bill_total, 2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[8],6,number_format($bill_discount, 2, '.', ''),'RTB',0,'R');
          if($tax_calc_option === 'e') {
            $pdf->Cell($item_widths[9],6,number_format($bill_tax, 2, '.', ''),'RTB',0,'R');
          } else {
            $pdf->Cell($item_widths[9],6,'Inclusive','RTB',0,'R');            
          }
          $pdf->Cell($item_widths[10],6,number_format($netpay, 2, '.', ''),'RTB',0,'R');  
          $pdf->Cell($item_widths[11],6,'','RTB',0,'L');
          $pdf->Cell($item_widths[12],6,'','RTB',0,'L');
          $pdf->SetFont('Arial','',8);

          $tot_sold_qty += $bill_qty;
          $tot_amount += $bill_total;
          $tot_discount += $bill_discount;
          $tot_net_pay += $netpay;
          $tot_bill_tax += $bill_tax;

          $old_bill_no = $new_bill_no;
          $bill_qty = $bill_total = $bill_discount = $netpay = $bill_tax = 0;
        }        

        $bill_qty += $record_details['soldQty'];
        $item_amount = round($record_details['soldQty']*$record_details['mrp'], 2);
        $item_value = $item_amount - $record_details['itemDiscount'];
        if($record_details['itemDiscount'] > 0 && $item_value > 0) {
          $disc_percent = round( ($record_details['itemDiscount']/$item_value)*100, 2);
          $disc_percent .= '%';
        } else {
          $disc_percent = '';
        }

        if($record_details['customerName'] !== '') {
          $customer_name = $record_details['customerName'];
        } elseif($record_details['tmpCustomerName'] !== '') {
          $customer_name = $record_details['tmpCustomerName'];
        } else {
          $customer_name = '';
        }

        if($record_details['remarksInvoice'] !== '') {
          $remarks_invoice = substr($record_details['remarksInvoice'],0,22);
        } else {
          $remarks_invoice = '';
        }

        $tax_calc_option = $total_records[$key]['taxCalcOption'];

        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$record_details['invoiceNo'],'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,date("d-m-Y", strtotime($record_details['invoiceDate'])),'RTB',0,'L');            
        $pdf->Cell($item_widths[3],6,substr($record_details['itemName'],0,18),'RTB',0,'L');
        $pdf->Cell($item_widths[4],6,number_format($record_details['soldQty'],2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,$record_details['cno'],'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,number_format($record_details['mrp'],2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,number_format($item_amount,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,number_format($record_details['itemDiscount'],2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[9],6,'','RTB',0,'R');
        $pdf->Cell($item_widths[10],6,number_format($item_value,2,'.',''),'RTB',0,'R');  
        $pdf->Cell($item_widths[11],6,substr($customer_name,0,20),'RTB',0,'L');
        $pdf->SetFont('Arial','',6);   
        $pdf->Cell($item_widths[12],6,$remarks_invoice,'RTB',0,'L');
        $pdf->SetFont('Arial','',8);        
      }

      $bill_total = $total_records[$key]['billAmount'];
      $bill_discount = $total_records[$key]['billDiscount'];
      $netpay =  $total_records[$key]['netpay'];
      $bill_tax = $total_records[$key]['taxAmount'];
      $netpay =  $total_records[$key]['netpay'];

      $tot_sold_qty += $bill_qty;
      $tot_amount += $bill_total;
      $tot_discount += $bill_discount;
      $tot_net_pay += $netpay;
      $tot_bill_tax += $bill_tax;

      $pdf->Ln();
      $pdf->SetFont('Arial','B',8);
      $pdf->Cell($item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3],6,'BILL TOTALS','LRTB',0,'R');
      $pdf->Cell($item_widths[4],6,number_format($bill_qty,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[6],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[7],6,number_format($bill_total, 2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[8],6,number_format($bill_discount, 2, '.', ''),'RTB',0,'R');
      if($tax_calc_option === 'e') {
        $pdf->Cell($item_widths[9],6,number_format($bill_tax, 2, '.', ''),'RTB',0,'R');
      } else {
        $pdf->Cell($item_widths[9],6,'Inclusive','RTB',0,'R');            
      }      
      $pdf->Cell($item_widths[10],6,number_format($netpay, 2, '.', ''),'RTB',0,'R');  
      $pdf->Cell($item_widths[11],6,'','RT',0,'L');
      $pdf->Cell($item_widths[12],6,'','RT',0,'L');

      $pdf->Ln();
      $pdf->Cell($item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3],6,'REPORT TOTALS','LRTB',0,'R');
      $pdf->Cell($item_widths[4],6,number_format($tot_sold_qty,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[6],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[7],6,number_format($tot_amount, 2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[8],6,number_format($tot_discount, 2, '.', ''),'RTB',0,'R');
      if($tax_calc_option === 'e') {
        $pdf->Cell($item_widths[9],6,number_format($tot_bill_tax, 2, '.', ''),'RTB',0,'R');
      } else {
        $pdf->Cell($item_widths[9],6,'Inclusive','RTB',0,'R');            
      }      
      $pdf->Cell($item_widths[10],6,number_format($tot_net_pay, 2, '.', ''),'RTB',0,'R');  
      $pdf->Cell($item_widths[11],6,'','RTB',0,'L');
      $pdf->Cell($item_widths[12],6,'','RTB',0,'L');
      $pdf->SetFont('Arial','',8);      

      $pdf->Output();
    }

    $controller_vars = array(
      'page_title' => 'Print Billwise, Itemwise and Casewise Sales Register',
      'icon_name' => 'fa fa-print',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
    );

    // render template
    return [$this->template->render_view('item-bill-wise-sales-register', $template_vars), $controller_vars];
  }   

  // day sales report
  public function daySalesReport(Request $request) {
    $default_location = $_SESSION['lc'];
    $client_locations = Utilities::get_client_locations();
    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_form_data_day_sales($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
      } else {
        $error_message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error: '.json_encode($validation['form_errors']);
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/day-sales');        
      }

      // hit api
      $sales_api_response = $this->sales_model->get_sales_summary_byday($form_data);
      if($sales_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-register');
      } else {
        $day_summary = $sales_api_response['summary'];
        $stock_balance = $sales_api_response['stock_balance'];
        $stock_balance_mtd = $sales_api_response['stock_balance_mtd'];
        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }
        $heading1 = 'Day Sales Report';
        $heading2 = 'Date: '.$form_data['saleDate'];
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }
        $csv_headings = [ [$heading1], [$heading2] ];

        $cash_sales = $day_summary[0]['cashSales'];
        $card_sales = $day_summary[0]['cardSales'];
        $split_sales = $day_summary[0]['splitSales'];
        $wallet_sales = $day_summary[0]['walletSales'];
        $credit_sales = $day_summary[0]['creditSales'];
        $sales_return = $day_summary[0]['returnAmount'];
        $cash_receipts = $day_summary[0]['cashReceipts'];
        $cash_payments = $day_summary[0]['cashPayments'];
        $cash_in_hand = ($day_summary[0]['cashInHand']+$cash_receipts)-$cash_payments;
        $day_sales = $cash_sales + $card_sales + $split_sales + $credit_sales;
        $total_sales = $day_sales - $sales_return;
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        Utilities::download_as_CSV_attachment('DaySalesReport', $csv_headings, $day_summary);
        return;
      }

      // dump($sales_api_response);
      // exit;

      // start PDF printing.

      $item_widths = array(10,55,35);
      $totals_width = $item_widths[0]+$item_widths[1];

      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('P','A4');

      $pdf->SetFont('Arial','B',16);
      $pdf->Cell(0,0,$heading1,'',1,'C');
      $pdf->SetFont('Arial','B',11);
      $pdf->Ln(5);
      $pdf->Cell(0,0,$heading2,'',1,'C');
      
      $pdf->SetFont('Arial','',13);

      $pdf->Ln(5);
      $pdf->Cell($item_widths[0],6,'a)','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Cash Sale','RTB',0,'L');
      $pdf->Cell($item_widths[2],6,number_format($cash_sales,2,'.',''),'RTB',0,'R');

      $pdf->Ln();
      $pdf->Cell($item_widths[0],6,'b)','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Card Sale','RTB',0,'L');
      $pdf->Cell($item_widths[2],6,number_format($card_sales,2,'.',''),'RTB',0,'R');

      $pdf->Ln();                
      $pdf->Cell($item_widths[0],6,'c)','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Split Sale','RTB',0,'L');
      $pdf->Cell($item_widths[2],6,number_format($split_sales,2,'.',''),'RTB',0,'R');      

      $pdf->Ln();                
      $pdf->Cell($item_widths[0],6,'d)','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'eWallet/UPI/EMI','RTB',0,'L');
      $pdf->Cell($item_widths[2],6,number_format($wallet_sales,2,'.',''),'RTB',0,'R');      

      $pdf->Ln();                
      $pdf->Cell($item_widths[0],6,'e)','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Credit Sale','RTB',0,'L');
      $pdf->Cell($item_widths[2],6,number_format($credit_sales,2,'.',''),'RTB',0,'R');

      $pdf->Ln();
      $pdf->SetFont('Arial','B');          
      $pdf->Cell($item_widths[0],6,'','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'(a) + (b) + (c) + (d) + (e)','RTB',0,'R');
      $pdf->Cell($item_widths[2],6,number_format($day_sales,2,'.',''),'RTB',0,'R');

      $pdf->Ln();
      $pdf->SetFont('Arial','');          
      $pdf->Cell($item_widths[0],6,'e)','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Sales Return (-)','LRTB',0,'L');
      $pdf->Cell($item_widths[2],6,number_format($sales_return,2,'.',''),'RTB',0,'R');

      $pdf->Ln();
      $pdf->SetFont('Arial','B');              
      $pdf->Cell($item_widths[0],6,'','LRTB',0,'C');                     
      $pdf->Cell($item_widths[1],6,'Total Sales','RTB',0,'R');
      $pdf->Cell($item_widths[2],6,number_format($total_sales,2,'.',''),'RTB',0,'R');

      $pdf->Ln();
      $pdf->SetFont('Arial','');              
      $pdf->Cell($item_widths[0],6,'','LRTB',0,'C');                     
      $pdf->Cell($item_widths[1],6,'Cash Receipts','RTB',0,'R');
      $pdf->Cell($item_widths[2],6,number_format($cash_receipts,2,'.',''),'RTB',0,'R');

      $pdf->Ln();
      $pdf->SetFont('Arial','');              
      $pdf->Cell($item_widths[0],6,'','LRTB',0,'C');                     
      $pdf->Cell($item_widths[1],6,'Cash Payments','RTB',0,'R');
      $pdf->Cell($item_widths[2],6,number_format($cash_payments,2,'.',''),'RTB',0,'R');

      $pdf->Ln();
      $pdf->SetFont('Arial','B');              
      $pdf->Cell($item_widths[0],6,'','LRTB',0,'C');                     
      $pdf->Cell($item_widths[1],6,'Cash in hand **','RTB',0,'R');
      $pdf->Cell($item_widths[2],6,number_format($cash_in_hand,2,'.',''),'RTB',0,'R');

      $pdf->Ln();
      $pdf->SetFont('Arial','B',9);              
      $pdf->Cell($item_widths[0] + $item_widths[1] + $item_widths[2], 6, '** Cash Sale + Cash Paid in Split Sale + Cash Receipts - Cash Payments');

      $pdf->Ln();
      $pdf->Ln();
      $pdf->SetFont('Arial','B',11);              
      $pdf->Cell(190,6,'Day Stock Status (Qtys.) as of : '.$form_data['saleDate'],'LRT',1,'C');
      $pdf->SetFont('Arial','B',9);         
      $pdf->Cell(25,6,'Opening - OP','LTB',0,'C');
      $pdf->Cell(24,6,'Purch. - PU','LTB',0,'C');
      $pdf->Cell(24,6,'Sa.Return - SR','LTB',0,'C');
      $pdf->Cell(23,6,'Adj. - AJ','LTB',0,'C');
      $pdf->Cell(23,6,'Transfers - ST','LTB',0,'C');
      $pdf->Cell(23,6,'Sales-SA','LTB',0,'C');
      $pdf->Cell(23,6,'P.Returns-PR','LTB',0,'C');
      $pdf->Cell(25,6,'Closing-CL','LRTB',1,'C');
      $pdf->SetFont('Arial','',9);

      $pdf->Cell(25,6,number_format($stock_balance['openingQty'], 2, '.', ''),'LTB',0,'C');
      $pdf->Cell(24,6,number_format($stock_balance['purchasedQty'], 2, '.', ''),'LTB',0,'C');
      $pdf->Cell(24,6,number_format($stock_balance['salesReturnQty'], 2, '.', ''),'LTB',0,'C');
      $pdf->Cell(23,6,number_format($stock_balance['adjustedQty'], 2, '.', ''),'LTB',0,'C');
      $pdf->Cell(23,6,number_format($stock_balance['transferQty'], 2, '.', ''),'LTB',0,'C');
      $pdf->Cell(23,6,number_format($stock_balance['soldQty'], 2, '.', ''),'LTB',0,'C');
      $pdf->Cell(23,6,number_format($stock_balance['purchaseReturnQty'], 2, '.', ''),'LTB',0,'C');
      $pdf->Cell(25,6,number_format($stock_balance['closingQty'], 2, '.', ''),'LRTB',0,'C');

      $pdf->Ln();
      $pdf->Ln();
      $pdf->SetFont('Arial','B',11);              
      $pdf->Cell(190,6,' Month-to-Date Stock Status (Qtys.) as of '.$form_data['saleDate'],'LRT',1,'C');
      $pdf->SetFont('Arial','B',9);         
      $pdf->Cell(25,6,'Opening - OP','LTB',0,'C');
      $pdf->Cell(24,6,'Purch. - PU','LTB',0,'C');
      $pdf->Cell(24,6,'Sa.Return - SR','LTB',0,'C');
      $pdf->Cell(23,6,'Adj. - AJ','LTB',0,'C');
      $pdf->Cell(23,6,'Transfers - ST','LTB',0,'C');
      $pdf->Cell(23,6,'Sales-SA','LTB',0,'C');
      $pdf->Cell(23,6,'P.Returns-PR','LTB',0,'C');
      $pdf->Cell(25,6,'Closing-CL','LRTB',1,'C');
      $pdf->SetFont('Arial','',9);

      $pdf->Cell(25,6,number_format($stock_balance_mtd['openingQty'], 2, '.', ''),'LTB',0,'C');
      $pdf->Cell(24,6,number_format($stock_balance_mtd['purchasedQty'], 2, '.', ''),'LTB',0,'C');
      $pdf->Cell(24,6,number_format($stock_balance_mtd['salesReturnQty'], 2, '.', ''),'LTB',0,'C');
      $pdf->Cell(23,6,number_format($stock_balance_mtd['adjustedQty'], 2, '.', ''),'LTB',0,'C');
      $pdf->Cell(23,6,number_format($stock_balance_mtd['transferQty'], 2, '.', ''),'LTB',0,'C');
      $pdf->Cell(23,6,number_format($stock_balance_mtd['soldQty'], 2, '.', ''),'LTB',0,'C');
      $pdf->Cell(23,6,number_format($stock_balance_mtd['purchaseReturnQty'], 2, '.', ''),'LTB',0,'C');
      $pdf->Cell(25,6,number_format($stock_balance_mtd['closingQty'], 2, '.', ''),'LRTB',0,'C');      

      $pdf->Output();
    }

    $controller_vars = array(
      'page_title' => 'Day Sales Report',
      'icon_name' => 'fa fa-inr',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'format_options' => ['pdf'=>'PDF Format'],
    );

    // render template
    return [$this->template->render_view('day-sales-report', $template_vars), $controller_vars];    
  }

  // sales summary by month.
  public function salesSummaryByMonth(Request $request) {

    $default_location = $_SESSION['lc'];
    $client_locations = Utilities::get_client_locations();

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_form_data_sales_summary_bymon($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
      } else {
        $error_message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error: '.json_encode($validation['form_errors']);
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/day-sales');        
      }

      // hit api
      $sales_api_response = $this->sales_model->get_sales_summary_bymon($form_data);
      // dump($sales_api_response);
      // exit;

      if($sales_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-summary-by-month');
      } else {
        $month_summary = $sales_api_response['summary']['daywiseSales'];
        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }
        $heading1 = 'Daywise Sales Summary';
        $heading2 = 'from '.$form_data['fromDate'].' to '.$form_data['toDate'];
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }
        $csv_headings = [ [$heading1], [$heading2] ];
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        $month_summary = $this->_format_daywise_sales_summary_for_csv($month_summary);
        Utilities::download_as_CSV_attachment('DaywiseSalesSummary', $csv_headings, $month_summary);
        return;
      }

      // start PDF printing.
      $item_widths = array(18,19,19,19,21,21,21,21,21,24,24,24,26);
      $totals_width = $item_widths[0]+$item_widths[1];
      $slno = 0;

      $discount_label  = '**Discount amount is shown for information purpose only. It was already included in Cash/Card/Cnote Sale';
      $net_sales_text  = '##Net Sales: (Cash Sales + Card Sales + Split Sales + UPI/EMIC Sales + Credit Sales) - Sales Return';
      $net_sales_text1 = '##Net Sales: (Paid By Cash + Paid By Card + Credit Notes + Credit Sales + UPI/EMIC Sales) - Sales Return';
 
      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('L','A4');

      $pdf->SetFont('Arial','B',10);
      $pdf->Cell(0,0,$heading1.' [ '.$heading2.' ]','',1,'C');
      $pdf->SetFont('Arial','B',8);
      $pdf->Ln(3);
      $pdf->Cell($item_widths[0],6,'Date','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Cash Sales','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'Card Sales','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'Split Sales','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'Credit Sales','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'UPI/EMIC Sales','RTB',0,'C');      
      $pdf->Cell($item_widths[5],6,'Gross Sales','RT',0,'C');
      $pdf->Cell($item_widths[6],6,'Sales Return','RT',0,'C');
      $pdf->Cell($item_widths[7],6,'Net Sales ##','RT',0,'C');
      $pdf->Cell($item_widths[8],6,'Paid By Cash','RTB',0,'C');
      $pdf->Cell($item_widths[9],6,'Paid By Card','RTB',0,'C');
      $pdf->Cell($item_widths[10],6,'Credit Notes','RTB',0,'C');
      $pdf->Cell($item_widths[11],6,'By UPI/EMIC','RTB',0,'C');
      // $pdf->Cell($item_widths[11],6,'Discount / Bills **','RTB',0,'C');
      $pdf->SetFont('Arial','',8);

      $tot_cash_sales = $tot_split_sales = $tot_card_sales = $tot_credit_sales = $tot_sales = 0;
      $tot_wallet_sales = 0;
      $tot_discounts = $tot_discount_bills = $tot_returns = 0;
      $tot_cash_payments = $tot_card_payments = $tot_cnote_payments = $tot_wallet_payments = 0;
      foreach($month_summary as $day_details) {
        $date = date("d-m-Y", strtotime($day_details['tranDate']));
        $week = date("l", strtotime($day_details['tranDate']));
        $day_sales = $day_details['cashSales'] + $day_details['splitSales'] + $day_details['cardSales'] + $day_details['creditSales'];

        $tot_cash_sales += $day_details['cashSales'];
        $tot_card_sales += $day_details['cardSales'];
        $tot_split_sales += $day_details['splitSales'];
        $tot_credit_sales += $day_details['creditSales'];
        $tot_wallet_sales += $day_details['walletSales'];
        $tot_returns += $day_details['returnAmount'];

        $tot_cash_payments += $day_details['cashPayments'];
        $tot_card_payments += $day_details['cardPayments'];
        $tot_cnote_payments += $day_details['cnotePayments'];
        $tot_wallet_payments += $day_details['walletPayments'];

        $tot_discounts += $day_details['discountGiven'];
        $tot_discount_bills += $day_details['totalDiscountBills'];

        $cash_sales = $day_details['cashSales'] > 0 ? number_format($day_details['cashSales'],2,'.','') : '';
        $card_sales = $day_details['cardSales'] > 0 ? number_format($day_details['cardSales'],2,'.','') : '';
        $split_sales = $day_details['splitSales'] > 0 ? number_format($day_details['splitSales'],2,'.','') : '';
        $credit_sales = $day_details['creditSales'] > 0 ? number_format($day_details['creditSales'],2,'.','') : '';
        $wallet_sales = $day_details['walletSales'] > 0 ? number_format($day_details['walletSales'],2,'.','') : '';

        $sales_return = $day_details['returnAmount'] > 0 ?  number_format($day_details['returnAmount'],2,'.','') : '';
        $net_sales = ($day_sales-$day_details['returnAmount']) > 0 ? number_format($day_sales-$day_details['returnAmount'],2,'.','') : '';

        $cash_payments = $day_details['cashPayments'] > 0 ? number_format($day_details['cashPayments'],2,'.','') : '' ;
        $card_payments = $day_details['cardPayments'] > 0 ? number_format($day_details['cardPayments'],2,'.','') : '' ;
        $cnote_payments = $day_details['cnotePayments'] > 0 || $day_details['cnotePayments'] < 0  ? number_format($day_details['cnotePayments'],2,'.','') : '' ;
        $wallet_payments = $day_details['walletPayments'] > 0 ? number_format($day_details['walletPayments'],2,'.','') : '' ;

        $total_sales = number_format($day_details['cashSales']+$day_details['cardSales']+$day_details['splitSales']+$day_details['creditSales']+$day_details['walletSales'],2,'.','');
        $discount_string = number_format($day_details['discountGiven'],2,'.','').' / '.$day_details['totalDiscountBills'];

        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$date,'LRTB',0,'L');
        $pdf->Cell($item_widths[1],6,$cash_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[2],6,$card_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[3],6,$split_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[4],6,$credit_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[4],6,$wallet_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,$total_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,$sales_return,'RTB',0,'R');
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell($item_widths[7],6,$net_sales,'RTB',0,'R');
        $pdf->SetFont('Arial','',8);
        $pdf->Cell($item_widths[8],6,$cash_payments,'RTB',0,'R');
        $pdf->Cell($item_widths[9],6,$card_payments,'RTB',0,'R');
        $pdf->Cell($item_widths[10],6,$cnote_payments,'RTB',0,'R');
        // $pdf->Cell($item_widths[11],6,$discount_string,'RTB',0,'R');
        $pdf->Cell($item_widths[11],6,$wallet_payments,'RTB',0,'R');
      }

      $tot_sales = $tot_cash_sales + $tot_credit_sales + $tot_split_sales + $tot_card_sales + $tot_wallet_sales;
      $tot_net_sales = $tot_sales - $tot_returns;

      $pdf->SetFont('Arial','B',8);      
      $pdf->Ln();
      $pdf->Cell($item_widths[0],6,'TOTALS','LB',0,'R');
      $pdf->Cell($item_widths[1],6,number_format($tot_cash_sales,2,'.',''),'LRB',0,'R');
      $pdf->Cell($item_widths[2],6,number_format($tot_card_sales,2,'.',''),'RB',0,'R');        
      $pdf->Cell($item_widths[3],6,number_format($tot_split_sales,2,'.',''),'RB',0,'R');
      $pdf->Cell($item_widths[4],6,number_format($tot_credit_sales,2,'.',''),'RB',0,'R');
      $pdf->Cell($item_widths[4],6,number_format($tot_wallet_sales,2,'.',''),'RB',0,'R');
      $pdf->Cell($item_widths[5],6,number_format($tot_sales,2,'.',''),'RB',0,'R');
      $pdf->Cell($item_widths[6],6,number_format($tot_returns,2,'.',''),'RB',0,'R');
      $pdf->Cell($item_widths[7],6,number_format($tot_net_sales,2,'.',''),'RB',0,'R');
      $pdf->Cell($item_widths[8],6,number_format($tot_cash_payments,2,'.',''),'RB',0,'R');
      $pdf->Cell($item_widths[9],6,number_format($tot_card_payments,2,'.',''),'RB',0,'R');        
      $pdf->Cell($item_widths[10],6,number_format($tot_cnote_payments,2,'.',''),'RB',0,'R');
      $pdf->Cell($item_widths[11],6,number_format($tot_wallet_payments,2,'.',''),'RB',1,'R');
      $pdf->SetFont('Arial','',8);
      $pdf->Ln(5);
      // $pdf->Cell(array_sum($item_widths),6,$discount_label,'',0,'R');
      // $pdf->Ln(4);
      $pdf->Cell(array_sum($item_widths),6,$net_sales_text,'',0,'R');
      $pdf->Ln(4);
      $pdf->Cell(array_sum($item_widths),6,$net_sales_text1,'',0,'R');

      $pdf->Output();      
    }

    // controller variables.
    $controller_vars = array(
      'page_title' => 'Sales Summary Report - By Month',
      'icon_name' => 'fa fa-inr',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
      'months' => Utilities::get_calender_months(), 
      'years' => Utilities::get_calender_years(1),
      'def_month' => date("m"),
      'def_year' => date("Y"),
    );

    // render template
    return [$this->template->render_view('sales-summary-by-month', $template_vars), $controller_vars];
  }

  public function salesByTaxRate(Request $request) {

    $default_location = $_SESSION['lc'];
    $client_locations = Utilities::get_client_locations();

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_form_data_sales_summary_bymon($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
      } else {
        $error_message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error: '.json_encode($validation['form_errors']);
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-by-tax-rate');        
      }

      // hit api
      $sales_api_response = $this->sales_model->get_sales_summary_bymon_tax_report($form_data);
      if($sales_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-by-tax-rate');
      } else {
        $sales_summary = $sales_api_response['summary'];
        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }        
        $heading1 = 'Sales by Tax Rate';
        $heading2 = '( from '.$form_data['fromDate'].' to '.$form_data['toDate'].' )';
        if($location_name !== '') {
          $heading1 .= ' :: '.$location_name;
        }        
        $csv_headings = [ [$heading1], [$heading2] ];
      }

      // dump($sales_summary);
      // exit;

      $format = $form_data['format'];
      if($format === 'csv') {
        $sales_summary = $this->_format_sales_by_tax_rate_report_for_csv($sales_summary);
        Utilities::download_as_CSV_attachment('SalesRegisterByTaxRate', $csv_headings, $sales_summary);
        return;
      }

      $item_widths = array(22,20,33,33,15,27,15,27,15,27,15,27);
      $totals_width = $item_widths[0]+$item_widths[1];
      $slno = 0;
      $gst_summary = [];

      $grand_tot_qty = $grand_billable = $grand_taxable = $grand_igst_value = 0;
      $grand_cgst_value = $grand_sgst_value = 0;

      // start PDF printing.

      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('L','A4');

      $pdf->SetFont('Arial','B',16);
      $pdf->Cell(0,0,$heading1,'',1,'C');
      $pdf->SetFont('Arial','B',11);
      $pdf->Ln(5);
      $pdf->Cell(0,0,$heading2,'',1,'C');

      $pdf->SetFont('Arial','B',9);
      $pdf->Ln(5);
      $pdf->Cell($item_widths[0],6,'Date','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Units Sold','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'Billed (Rs.)','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'Taxable (Rs.)','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'IGST%','RTB',0,'C');  
      $pdf->Cell($item_widths[5],6,'IGST Value (Rs.)','RTB',0,'C'); 
      $pdf->Cell($item_widths[6],6,'CGST%','RTB',0,'C');  
      $pdf->Cell($item_widths[7],6,'CGST Value (Rs.)','RTB',0,'C'); 
      $pdf->Cell($item_widths[8],6,'SGST%','RTB',0,'C');  
      $pdf->Cell($item_widths[9],6,'SGST Value (Rs.)','RTB',0,'C');
      $pdf->Cell($item_widths[10],6,'GST%','RTB',0,'C');  
      $pdf->Cell($item_widths[11],6,'GST Value (Rs.)','RTB',0,'C');
      $pdf->SetFont('Arial','',10);

      foreach($sales_summary as $day_details) {
        $date = date("d-m-Y", strtotime($day_details['tranDate']));
        $gst_summary = [];
        if($day_details['fivePercentItemQty'] > 0) {
          $gst_summary[5] = [
            'qty' => $day_details['fivePercentItemQty'],
            'billable' => $day_details['fivePercentBillable'],
            'taxable' => $day_details['fivePercentTaxable'],
            'igst' => $day_details['fivePercentIgstAmt'],
            'cgst' => $day_details['fivePercentCgstAmt'],
            'sgst' => $day_details['fivePercentSgstAmt'],
          ];
          $grand_tot_qty += $day_details['fivePercentItemQty'];
          $grand_billable += $day_details['fivePercentBillable'];
          $grand_taxable += $day_details['fivePercentTaxable'];

          $grand_igst_value += $day_details['fivePercentIgstAmt'];
          $grand_cgst_value += $day_details['fivePercentCgstAmt'];
          $grand_sgst_value += $day_details['fivePercentSgstAmt'];
        }
        if($day_details['twelvePercentItemQty'] > 0) {
          $gst_summary[12] = [
            'qty' => $day_details['twelvePercentItemQty'],
            'billable' => $day_details['twelvePercentBillable'],
            'taxable' => $day_details['twelvePercentTaxable'],
            'igst' => $day_details['twelvePercentIgstAmt'],
            'cgst' => $day_details['twelvePercentCgstAmt'],
            'sgst' => $day_details['twelvePercentSgstAmt'],
          ];
          $grand_tot_qty += $day_details['twelvePercentItemQty'];
          $grand_billable += $day_details['twelvePercentBillable'];
          $grand_taxable += $day_details['twelvePercentTaxable'];
          
          $grand_igst_value += $day_details['twelvePercentIgstAmt'];
          $grand_cgst_value += $day_details['twelvePercentCgstAmt'];        
          $grand_sgst_value += $day_details['twelvePercentSgstAmt'];
        }
        if($day_details['eighteenPercentItemQty'] > 0) {
          $gst_summary[18] = [
            'qty' => $day_details['eighteenPercentItemQty'],
            'billable' => $day_details['eighteenPercentBillable'],
            'taxable' => $day_details['eighteenPercentTaxable'],
            'igst' => $day_details['eighteenPercentIgstAmt'],
            'cgst' => $day_details['eighteenPercentCgstAmt'],
            'sgst' => $day_details['eighteenPercentSgstAmt'],
          ];
          $grand_tot_qty += $day_details['eighteenPercentItemQty'];
          $grand_billable += $day_details['eighteenPercentBillable'];
          $grand_taxable += $day_details['eighteenPercentTaxable'];

          $grand_igst_value += $day_details['eighteenPercentIgstAmt'];
          $grand_sgst_value += $day_details['eighteenPercentSgstAmt'];
          $grand_cgst_value += $day_details['eighteenPercentCgstAmt'];        
        }
        if($day_details['twentyEightPercentItemQty'] > 0) {
          $gst_summary[28] = [
            'qty' => $day_details['twentyEightPercentItemQty'],
            'billable' => $day_details['twentyEightPercentBillable'],
            'taxable' => $day_details['twentyEightPercentTaxable'],
            'igst' => $day_details['twentyEightPercentIgstAmt'],
            'cgst' => $day_details['twentyEightPercentCgstAmt'],
            'sgst' => $day_details['twentyEightPercentSgstAmt'],
          ];
          $grand_tot_qty += $day_details['twentyEightPercentItemQty'];
          $grand_billable += $day_details['twentyEightPercentBillable'];
          $grand_taxable += $day_details['twentyEightPercentTaxable'];

          $grand_igst_value += $day_details['twentyEightPercentIgstAmt'];
          $grand_cgst_value += $day_details['twentyEightPercentCgstAmt'];        
          $grand_sgst_value += $day_details['twentyEightPercentSgstAmt'];
        }

        // dump($gst_summary);
        // exit;

        foreach($gst_summary as $key => $gst_summary_details) {
          if($gst_summary_details['igst'] > 0) {
            $igst_amount = number_format($gst_summary_details['igst'],2,'.','');
            $igst_percent = number_format($key,2);
            $cgst_amount = $sgst_amount = '';
            $cgst_percent = $sgst_percent = '';
          } else {
            $cgst_amount = number_format($gst_summary_details['cgst'],2,'.','');
            $sgst_amount = number_format($gst_summary_details['sgst'],2,'.','');
            $cgst_percent = $sgst_percent = number_format($key/2, 2);
            $igst_percent = '';
            $igst_amount = '';
          }

          $pdf->Ln();
          $pdf->Cell($item_widths[0],6,$date,'LRTB',0,'L');
          $pdf->Cell($item_widths[1],6,number_format($gst_summary_details['qty'],2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[2],6,number_format($gst_summary_details['billable'],2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[3],6,number_format($gst_summary_details['taxable'],2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[4],6,$igst_percent,'RTB',0,'R');
          $pdf->Cell($item_widths[5],6,$igst_amount,'RTB',0,'R');
          $pdf->Cell($item_widths[6],6,$cgst_percent,'RTB',0,'R');
          $pdf->Cell($item_widths[7],6,$cgst_amount,'RTB',0,'R');
          $pdf->Cell($item_widths[8],6,$sgst_percent,'RTB',0,'R');
          $pdf->Cell($item_widths[9],6,$sgst_amount,'RTB',0,'R');
          $pdf->Cell($item_widths[10],6,number_format($key,2),'RTB',0,'R');
          $pdf->Cell($item_widths[11],6,number_format($gst_summary_details['igst']+$gst_summary_details['cgst']+$gst_summary_details['sgst'], 2, '.', ''),'RTB',0,'R');
        }
      }
      $pdf->Ln();
      $pdf->SetFont('Arial','B',11);
      $pdf->Cell($item_widths[0],6,'','LRTB',0,'L');
      $pdf->Cell($item_widths[1],6,number_format($grand_tot_qty,2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[2],6,number_format($grand_billable,2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[3],6,number_format($grand_taxable,2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[4],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[5],6,$grand_igst_value>0 ? number_format($grand_igst_value, 2, '.', '') : '' ,'RTB',0,'R');
      $pdf->Cell($item_widths[6],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[7],6,$grand_cgst_value>0 ? number_format($grand_cgst_value, 2, '.', '') : '','RTB',0,'R');
      $pdf->Cell($item_widths[8],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[9],6,$grand_sgst_value>0 ? number_format($grand_sgst_value, 2, '.', '') : '','RTB',0,'R');
      $pdf->Cell($item_widths[10],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[11],6,number_format($grand_igst_value+$grand_cgst_value+$grand_sgst_value, 2, '.', ''),'RTB',0,'R');
      $pdf->SetFont('Arial','B',9);

      $pdf->Output();      
    }

    // controller variables.
    $controller_vars = array(
      'page_title' => 'Sales by Tax Rate',
      'icon_name' => 'fa fa-inr',
    );    

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
      'months' => Utilities::get_calender_months(), 
      'years' => Utilities::get_calender_years(1),
      'def_month' => date("m"),
      'def_year' => date("Y"),
    );

    // render template
    return [$this->template->render_view('sales-by-tax-rate', $template_vars), $controller_vars];    
  }

  public function salesByHsnCodes(Request $request) {

    $default_location = $_SESSION['lc'];
    $client_locations = Utilities::get_client_locations();

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_form_data_sales_summary_bymon($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
      } else {
        $error_message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error: '.json_encode($validation['form_errors']);
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-by-tax-rate');        
      }

      // hit api
      $sales_api_response = $this->sales_model->get_sales_summary_by_hsnsac_code($form_data);
      // dump($sales_api_response);
      // exit;
      if($sales_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-by-tax-rate');
      } else {
        $sales_summary = $sales_api_response['summary']['items_list'];
        $daywise_summary = $sales_api_response['summary']['tot_records'];
        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }        
        $heading1 = 'Sales by HSN/SAC Code';
        $heading2 = '( from '.$form_data['fromDate'].' to '.$form_data['toDate'].' )';
        if($location_name !== '') {
          $heading1 .= ' :: '.$location_name;
        }        
        $csv_headings = [ [$heading1], [$heading2] ];
      }

      // inject day totals against the last repeat day of each date.
      $sales_sum_keys = array_column($sales_summary, 'tranDate');
      $rev_sales_sum_keys = array_reverse($sales_sum_keys, true);
      foreach($daywise_summary as $key => $day_details) {
        $last_key_of_the_day = array_search($day_details['tranDate'], $rev_sales_sum_keys);
        if($last_key_of_the_day !== false) {
          $sales_summary[$last_key_of_the_day]['cashPayments'] = $day_details['cashPayments'];
          $sales_summary[$last_key_of_the_day]['cardPayments'] = $day_details['cardPayments'];
          $sales_summary[$last_key_of_the_day]['creditSales'] = $day_details['creditSales'];
          $sales_summary[$last_key_of_the_day]['cnotePayments'] = $day_details['cnotePayments'];
          $sales_summary[$last_key_of_the_day]['returnAmount'] = $day_details['returnAmount'];
        }
      }

      // dump($sales_summary);
      // exit;

      $format = $form_data['format'];
      if($format === 'csv') {
        $sales_summary = $this->_format_sales_by_hsn_code_for_csv($sales_summary);
        Utilities::download_as_CSV_attachment('SalesRegisterByHsnCodes', $csv_headings, $sales_summary);
        return;
      }

      $item_widths = array(13,13,12,28,11,16,16,9,12,9,12,9,12,9,12,13,13,13,13,13,11,13);
      $totals_width = $item_widths[0]+$item_widths[1];
      $slno = 0;
      $gst_summary = [];

      $grand_tot_qty = $grand_billable = $grand_taxable = $grand_igst_value = 0;
      $grand_cgst_value = $grand_sgst_value = 0;

      // start PDF printing.

      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('L','A4');

      $pdf->SetFont('Arial','B',16);
      $pdf->Cell(0,0,$heading1,'',1,'C');
      $pdf->SetFont('Arial','B',11);
      $pdf->Ln(5);
      $pdf->Cell(0,0,$heading2,'',1,'C');

      $pdf->SetFont('Arial','B',6);
      $pdf->Ln(5);
      $pdf->Cell($item_widths[0],6,'Date','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Qty.','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'UOM','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'ItemName','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'HSN/SAC','RTB',0,'C');
      $pdf->Cell($item_widths[5],6,'Billed','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'Taxable','RTB',0,'C');
      $pdf->Cell($item_widths[7],6,'IGST%','RTB',0,'C');  
      $pdf->Cell($item_widths[8],6,'IGST','RTB',0,'C'); 
      $pdf->Cell($item_widths[9],6,'CGST%','RTB',0,'C');  
      $pdf->Cell($item_widths[10],6,'CGST','RTB',0,'C'); 
      $pdf->Cell($item_widths[11],6,'SGST%','RTB',0,'C');  
      $pdf->Cell($item_widths[12],6,'SGST','RTB',0,'C');
      $pdf->Cell($item_widths[13],6,'GST%','RTB',0,'C');  
      $pdf->Cell($item_widths[14],6,'GST','RTB',0,'C');
      $pdf->Cell($item_widths[15],6,'Cash','RTB',0,'C');
      $pdf->Cell($item_widths[16],6,'Card','RTB',0,'C');
      $pdf->Cell($item_widths[17],6,'Credit','RTB',0,'C');
      $pdf->Cell($item_widths[18],6,'Cnote','RTB',0,'C');
      $pdf->Cell($item_widths[19],6,'Returns','RTB',0,'C');
      $pdf->Cell($item_widths[20],6,'R.off','RTB',0,'C');      
      $pdf->Cell($item_widths[21],6,'NetSales','RTB',0,'C');
      $pdf->SetFont('Arial','',6);

      $codewise_taxable = $codewise_gst = 0;
      $tot_cash_payments = $tot_card_payments = $tot_credit_sales = $tot_cnote_payments = 0;
      $tot_return_amount = $tot_day_round_off = $tot_net_sales = 0;

      foreach($sales_summary as $day_details) {
        $date = date("d-m-Y", strtotime($day_details['tranDate']));
        $hsn_sac_code = substr($day_details['hsnSacCode'],0,4);
        $hsn_sac_short_name = substr($day_details['hsnsacDescShort'],0,25);
        $uom_name = substr($day_details['uomName'],0,4);

        $cash_payments = isset($day_details['cashPayments']) ? $day_details['cashPayments'] : 0;
        $card_payments = isset($day_details['cardPayments']) ? $day_details['cardPayments'] : 0;
        $credit_sales = isset($day_details['creditSales']) ? $day_details['creditSales'] : 0;
        $cnote_payments = isset($day_details['cnotePayments']) ? $day_details['cnotePayments'] : 0;
        $return_amount = isset($day_details['returnAmount']) ? $day_details['returnAmount'] : 0;
        $day_round_off = 0;
        $net_day_sales = ($cash_payments+$card_payments+$credit_sales+$cnote_payments) - $return_amount;

        $gst_summary = [];
        if($day_details['fivePercentItemQty'] > 0) {
          $gst_summary[5] = [
            'qty' => $day_details['fivePercentItemQty'],
            'billable' => $day_details['fivePercentBillable'],
            'taxable' => $day_details['fivePercentTaxable'],
            'igst' => $day_details['fivePercentIgstAmt'],
            'cgst' => $day_details['fivePercentCgstAmt'],
            'sgst' => $day_details['fivePercentSgstAmt'],
          ];
          $grand_tot_qty += $day_details['fivePercentItemQty'];
          $grand_billable += $day_details['fivePercentBillable'];
          $grand_taxable += $day_details['fivePercentTaxable'];

          $grand_igst_value += $day_details['fivePercentIgstAmt'];
          $grand_cgst_value += $day_details['fivePercentCgstAmt'];
          $grand_sgst_value += $day_details['fivePercentSgstAmt'];
        }
        if($day_details['twelvePercentItemQty'] > 0) {
          $gst_summary[12] = [
            'qty' => $day_details['twelvePercentItemQty'],
            'billable' => $day_details['twelvePercentBillable'],
            'taxable' => $day_details['twelvePercentTaxable'],
            'igst' => $day_details['twelvePercentIgstAmt'],
            'cgst' => $day_details['twelvePercentCgstAmt'],
            'sgst' => $day_details['twelvePercentSgstAmt'],
          ];
          $grand_tot_qty += $day_details['twelvePercentItemQty'];
          $grand_billable += $day_details['twelvePercentBillable'];
          $grand_taxable += $day_details['twelvePercentTaxable'];
          
          $grand_igst_value += $day_details['twelvePercentIgstAmt'];
          $grand_cgst_value += $day_details['twelvePercentCgstAmt'];        
          $grand_sgst_value += $day_details['twelvePercentSgstAmt'];
        }
        if($day_details['eighteenPercentItemQty'] > 0) {
          $gst_summary[18] = [
            'qty' => $day_details['eighteenPercentItemQty'],
            'billable' => $day_details['eighteenPercentBillable'],
            'taxable' => $day_details['eighteenPercentTaxable'],
            'igst' => $day_details['eighteenPercentIgstAmt'],
            'cgst' => $day_details['eighteenPercentCgstAmt'],
            'sgst' => $day_details['eighteenPercentSgstAmt'],
          ];
          $grand_tot_qty += $day_details['eighteenPercentItemQty'];
          $grand_billable += $day_details['eighteenPercentBillable'];
          $grand_taxable += $day_details['eighteenPercentTaxable'];

          $grand_igst_value += $day_details['eighteenPercentIgstAmt'];
          $grand_sgst_value += $day_details['eighteenPercentSgstAmt'];
          $grand_cgst_value += $day_details['eighteenPercentCgstAmt'];        
        }
        if($day_details['twentyEightPercentItemQty'] > 0) {
          $gst_summary[28] = [
            'qty' => $day_details['twentyEightPercentItemQty'],
            'billable' => $day_details['twentyEightPercentBillable'],
            'taxable' => $day_details['twentyEightPercentTaxable'],
            'igst' => $day_details['twentyEightPercentIgstAmt'],
            'cgst' => $day_details['twentyEightPercentCgstAmt'],
            'sgst' => $day_details['twentyEightPercentSgstAmt'],
          ];
          $grand_tot_qty += $day_details['twentyEightPercentItemQty'];
          $grand_billable += $day_details['twentyEightPercentBillable'];
          $grand_taxable += $day_details['twentyEightPercentTaxable'];

          $grand_igst_value += $day_details['twentyEightPercentIgstAmt'];
          $grand_cgst_value += $day_details['twentyEightPercentCgstAmt'];        
          $grand_sgst_value += $day_details['twentyEightPercentSgstAmt'];
        }

        // dump($gst_summary);
        // exit;

        foreach($gst_summary as $key => $gst_summary_details) {

          if($gst_summary_details['igst'] > 0) {
            $igst_amount = number_format($gst_summary_details['igst'],2,'.','');
            $igst_percent = number_format($key,2);
            $cgst_amount = $sgst_amount = '';
          } else {
            $cgst_amount = number_format($gst_summary_details['cgst'],2,'.','');
            $sgst_amount = number_format($gst_summary_details['sgst'],2,'.','');
            $cgst_percent = $sgst_percent = number_format($key/2, 2);
            $igst_percent = '';
            $igst_amount = '';
          }

          $codewise_taxable += $gst_summary_details['taxable'];
          $codewise_gst += ($gst_summary_details['igst'] + $gst_summary_details['cgst'] + $gst_summary_details['sgst']);

          /* It implies that we reached end of day. */
          if(isset($day_details['cashPayments'])) {
            $total_codewise_day_sales = ($codewise_taxable+$codewise_gst)-$return_amount;
            $day_round_off = ($net_day_sales-$total_codewise_day_sales);

            // dump($date.'====>'.$net_day_sales.'===>'.$total_codewise_day_sales);

            if($cash_payments > 0) {
              $cash_payments -= $day_round_off;
            } elseif($card_payments > 0) {
              $card_payments -= $day_round_off;
            } elseif($credit_sales > 0) {
              $credit_sales -= $day_round_off;
            }
            $codewise_taxable = $codewise_gst = 0;

            $tot_cash_payments += $cash_payments;
            $tot_card_payments += $card_payments;
            $tot_credit_sales += $credit_sales;
            $tot_cnote_payments += $cnote_payments;
            $tot_return_amount += $return_amount;
            $tot_day_round_off += $day_round_off;
            $tot_net_sales += $net_day_sales;
          }

          $pdf->Ln();
          $pdf->Cell($item_widths[0],6,$date,'LRTB',0,'L');
          $pdf->Cell($item_widths[1],6,number_format($gst_summary_details['qty'],2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[2],6,$uom_name,'RTB',0,'L');
          $pdf->Cell($item_widths[3],6,$hsn_sac_short_name,'RTB',0,'L');
          $pdf->Cell($item_widths[4],6,$hsn_sac_code,'RTB',0,'C');
          $pdf->Cell($item_widths[5],6,number_format($gst_summary_details['billable'],2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[6],6,number_format($gst_summary_details['taxable'],2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[7],6,$igst_percent,'RTB',0,'R');
          $pdf->Cell($item_widths[8],6,$igst_amount,'RTB',0,'R');
          $pdf->Cell($item_widths[9],6,$cgst_percent,'RTB',0,'R');
          $pdf->Cell($item_widths[10],6,$cgst_amount,'RTB',0,'R');
          $pdf->Cell($item_widths[11],6,$sgst_percent,'RTB',0,'R');
          $pdf->Cell($item_widths[12],6,$sgst_amount,'RTB',0,'R');
          $pdf->Cell($item_widths[13],6,number_format($key,2),'RTB',0,'R');
          $pdf->Cell($item_widths[14],6,number_format($gst_summary_details['igst']+$gst_summary_details['cgst']+$gst_summary_details['sgst'], 2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[15],6,number_format($cash_payments,2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[16],6,number_format($card_payments,2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[17],6,number_format($credit_sales,2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[18],6,number_format($cnote_payments,2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[19],6,number_format($return_amount,2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[20],6,number_format($day_round_off,2,'.',''),'RTB',0,'R');
          $pdf->Cell($item_widths[21],6,number_format($net_day_sales,2,'.',''),'RTB',0,'R');
        }
      }
      $pdf->Ln();
      $pdf->SetFont('Arial','B',6);
      $pdf->Cell($item_widths[0],6,'','LRTB',0,'L');
      $pdf->Cell($item_widths[1],6,number_format($grand_tot_qty,2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[2],6,'','RTB',0,'L');
      $pdf->Cell($item_widths[3],6,'','RTB',0,'L');           
      $pdf->Cell($item_widths[4],6,'','RTB',0,'L');           
      $pdf->Cell($item_widths[5],6,number_format($grand_billable,2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[6],6,number_format($grand_taxable,2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[7],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[8],6,$grand_igst_value>0 ? number_format($grand_igst_value, 2, '.', '') : '' ,'RTB',0,'R');
      $pdf->Cell($item_widths[9],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[10],6,$grand_cgst_value>0 ? number_format($grand_cgst_value, 2, '.', '') : '','RTB',0,'R');
      $pdf->Cell($item_widths[11],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[12],6,$grand_sgst_value>0 ? number_format($grand_sgst_value, 2, '.', '') : '','RTB',0,'R');
      $pdf->Cell($item_widths[13],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[14],6,number_format($grand_igst_value+$grand_cgst_value+$grand_sgst_value, 2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[15],6,number_format($tot_cash_payments,2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[16],6,number_format($tot_card_payments,2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[17],6,number_format($tot_credit_sales,2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[18],6,number_format($tot_cnote_payments,2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[19],6,number_format($tot_return_amount,2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[20],6,number_format($tot_day_round_off,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[21],6,number_format($tot_net_sales,2,'.',''),'RTB',0,'R');
      $pdf->SetFont('Arial','B',6);

      $pdf->Output();      
    }

    // controller variables.
    $controller_vars = array(
      'page_title' => 'Sales by HSN/SAC Codewise',
      'icon_name' => 'fa fa-inr',
    );    

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
      'months' => Utilities::get_calender_months(), 
      'years' => Utilities::get_calender_years(1),
      'def_month' => date("m"),
      'def_year' => date("Y"),
    );

    // render template
    return [$this->template->render_view('sales-by-hsn-code', $template_vars), $controller_vars];    
  }

  public function salesDispatchRegister(Request $request) {
    $default_location = $_SESSION['lc'];
    $client_locations = Utilities::get_client_locations();
    $agents_a = [];
    
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

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_form_data_dispatch_register($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
      } else {
        $error_message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error: '.json_encode($validation['form_errors']);
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-dispatch-register');        
      }

      //hit api
      $sales_api_response = $this->sales_model->get_sales_dispatch_register($form_data);
      if($sales_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-dispatch-register');
      } else {
        $total_records = $sales_api_response['dispatches']['dispatches'];
        $total_pages = $sales_api_response['dispatches']['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $form_data['pageNo'] = $i;
            $sales_api_response = $this->sales_model->get_sales_dispatch_register($form_data);
            if($sales_api_response['status']) {
              $total_records = array_merge($total_records,$sales_api_response['dispatches']['dispatches']);
            }
          }
        }

        // dump($sales_api_response);
        // dump($total_records);
        // exit;

        if( isset($form_data['locationCode']) && $form_data['locationCode'] !== '' && 
            is_array($client_locations) && count($client_locations)>0
          ) {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = 'All Stores/Locations';
        }
        if($form_data['agentCode'] !== '' && isset($agents_a[$form_data['agentCode']])) {
          $agent_name = $agents_a[$form_data['agentCode']];
        } else {
          $agent_name = '';
        }

        $heading1 = 'Sales Dispatches Register';
        $heading2 = '( from '.$form_data['fromDate'].' to '.$form_data['toDate'].' )';
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }
        if($agent_name !== '') {
          $heading2 .= ' Agent: '.$agent_name;
        }
        $csv_headings = [ [$heading1], [$heading2] ];

        $format = $form_data['format'];
        if($format === 'csv') {
          $total_records = $this->_format_dispatch_register_for_csv($total_records);
          Utilities::download_as_CSV_attachment('DispatchRegister', $csv_headings, $total_records);
          return;
        }

        // start PDF printing.
        $item_widths = array(10,47,25,25,18,20,25,21,23,25,40);
                          //  0, 1, 2, 3, 4, 5, 6, 7, 8, 9,10
        $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3] + $item_widths[4];
        $slno = 0;

        $pdf = PDF::getInstance();
        $pdf->AliasNbPages();
        $pdf->AddPage('L','A4');

        // Print Bill Information.
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,0,$heading1,'',1,'C');
        $pdf->SetFont('Arial','B',10);
        $pdf->Ln(5);
        $pdf->Cell(0,0,$heading2,'',1,'C');

        $pdf->SetFont('Arial','B',8);
        $pdf->Ln(5);
        $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
        $pdf->Cell($item_widths[1],6,'Customer name','RTB',0,'C');
        $pdf->Cell($item_widths[2],6,'GST Inv. No.','RTB',0,'C');
        $pdf->Cell($item_widths[3],6,'Internal Inv. No.','RTB',0,'C');
        $pdf->Cell($item_widths[4],6,'Invoice Date','RTB',0,'C');
        $pdf->Cell($item_widths[5],6,'Amount (Rs.)','RTB',0,'C');
        $pdf->Cell($item_widths[6],6,'City name','RTB',0,'C');  
        $pdf->Cell($item_widths[7],6,'State name','RTB',0,'C');
        $pdf->Cell($item_widths[8],6,'WayBill No.','RTB',0,'C');
        $pdf->Cell($item_widths[9],6,'LR No.','RTB',0,'C');
        $pdf->Cell($item_widths[10],6,'Carrier Name','RTB',0,'C');
        $pdf->SetFont('Arial','',8);

        $tot_amount = $slno = 0;
        foreach($total_records as $record_details) {
          $slno++;
          $tot_amount += $record_details['netpay'];

          $pdf->Ln();
          $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
          $pdf->Cell($item_widths[1],6,substr($record_details['customerName'],0,25),'RTB',0,'L');
          $pdf->Cell($item_widths[2],6,$record_details['gstInvoiceNo'],'RTB',0,'L');
          $pdf->Cell($item_widths[3],6,$record_details['qbInvoiceNo'],'RTB',0,'L');            
          $pdf->Cell($item_widths[4],6,date('d/m/Y', strtotime($record_details['invoiceDate'])),'RTB',0,'R');
          $pdf->Cell($item_widths[5],6,number_format($record_details['netpay'],2,'.',''),'RTB',0,'R');
          $pdf->Cell($item_widths[6],6,ucwords(strtolower(substr($record_details['cityName'],0,14))),'RTB',0,'L');
          $pdf->Cell($item_widths[7],6,Utilities::get_location_state_name($record_details['stateID']),'RTB',0,'L');
          $pdf->Cell($item_widths[8],6,$record_details['wayBillNo'],'RTB',0,'L');  
          $pdf->Cell($item_widths[9],6,substr($record_details['lrNo'],0,16),'RTB',0,'L');  
          $pdf->Cell($item_widths[10],6,ucwords(strtolower( substr($record_details['transporterName'],0,26) )),'RTB',0,'L');  
        }

        $pdf->Ln();
        $pdf->SetFont('Arial','B',9);    
        $pdf->Cell($totals_width,6,'Total Dispatches','LB',0,'R');
        $pdf->Cell($item_widths[5],6,number_format($tot_amount,2,'.',''),'B',0,'R');   
        $pdf->Cell($item_widths[6]+$item_widths[7]+$item_widths[8]+$item_widths[9]+$item_widths[10],6,'','RB',0,'R');   

        $pdf->Output();
      }
    }


    // controller variables.
    $controller_vars = array(
      'page_title' => 'Sales Dispatch Register',
      'icon_name' => 'fa fa-truck',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
      'agents' => ['' => 'All Agents'] + $agents_a,
    );

    // render template
    return [$this->template->render_view('sales-dispatch-register', $template_vars), $controller_vars];
  }

  public function salesUpiPaymentsRegister(Request $request) {

    $wallets = Constants::$WALLETS;
    $client_locations = Utilities::get_client_locations();
    $default_location = $_SESSION['lc'];
    $per_page = 300;

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_form_data_sales_upi_payments_register($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
      } else {
        $error_message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error: '.json_encode($validation['form_errors']);
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/day-sales');        
      }

      $form_data['perPage'] = $per_page;

      // hit api
      $sales_api_response = $this->sales_model->get_sales_upi_payments_register($form_data);
      if($sales_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-upi-register');
      } else {
        $total_records = $sales_api_response['walletPayments']['sales'];
        $total_pages = $sales_api_response['walletPayments']['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $form_data['pageNo'] = $i;
            $sales_api_response = $this->sales_model->get_sales_upi_payments_register($form_data);
            if($sales_api_response['status']) {
              $total_records = array_merge($total_records,$sales_api_response['walletPayments']['sales']);
            }
          }
        }

        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }

        $heading1 = 'Sales by UPI/EMI-Cards';
        $heading2 = 'from '.$form_data['fromDate'].' to '.$form_data['toDate'];
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }
        $csv_headings = [ [$heading1], [$heading2] ];
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        $total_records = $this->_format_sales_upi_register_csv($total_records);
        Utilities::download_as_CSV_attachment('SalesUPIRegister', $csv_headings, $total_records);
        return;
      }

      // dump($total_records);
      // exit;

      // PDF Printing
      $item_widths = array(10,47,25,25,18,20,25,21,23,25,40);
                        //  0, 1, 2, 3, 4, 5, 6, 7, 8, 9,10
      $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3] + $item_widths[4];
      $slno = 0;

      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('P','A4');
      $pdf->SetAutoPageBreak(false);

      // Print Bill Information.
      $this->_add_page_heading_for_sales_upi_payments_register($pdf, $item_widths, $heading1, $heading2);

      $tot_amount = $slno = 0;
      $row_cntr = 0;
      foreach($total_records as $record_details) {
        $slno++;
        $tot_amount += $record_details['netPay'];
        $row_cntr++;

        $customer_name = $record_details['customerName'] !== '' ? $record_details['customerName'] : $record_details['cnameTemp'];
        $mobile_no = $record_details['customerMobileNo'];
        $bill_no = $record_details['billNo'];
        $bill_date = date("d/m/Y", strtotime($record_details['billDate']));
        $amount = number_format($record_details['netPay'], 2, '.', '');
        $wallet_name = isset($wallets[$record_details['walletID']]) ? $wallets[$record_details['walletID']] : '-';
        $wallet_ref_no = $record_details['walletRefNo'];

        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,substr($customer_name,0,25),'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$mobile_no,'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,$bill_no,'RTB',0,'L');            
        $pdf->Cell($item_widths[4],6,$bill_date,'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,$amount,'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,$wallet_name,'RTB',0,'L');
        $pdf->Cell($item_widths[7],6,$wallet_ref_no,'RTB',0,'L');
        if($row_cntr === 39) {
          $pdf->AddPage('P','A4');
          $this->_add_page_heading_for_sales_upi_payments_register($pdf, $item_widths, $heading1, $heading2);
          $row_cntr = 0;
        }
      }

      $pdf->Ln();
      $pdf->SetFont('Arial','B',9);    
      $pdf->Cell($item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3]+$item_widths[4],6,'Totals','LBR',0,'R');
      $pdf->Cell($item_widths[5],6,number_format($tot_amount,2,'.',''),'B',0,'R');
      $pdf->Cell($item_widths[6]+$item_widths[7],6,'','RTB',0,'L');

      $pdf->Output();
    }    

    // controller variables.
    $controller_vars = array(
      'page_title' => 'Sales UPI/EMI-Card Payments Register',
      'icon_name' => 'fa fa-money',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
      'wallets' => ['' => 'All Wallets/EMI-Cards'] + $wallets,
    );

    // render template
    return [$this->template->render_view('sales-upi-payments-register', $template_vars), $controller_vars];
  }

  // sales by mis
  public function salesMisByFilter(Request $request) {

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_sales_mis_by_filter($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
      } else {
        $error_message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error: '.json_encode($validation['form_errors']);
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-mis');        
      }

      // hit api
      $sales_api_response = $this->sales_model->get_sales_by_filter($form_data);
      if($sales_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-mis');
      } else {
        // dump($sales_api_response);
        // exit;
        $report_type = $form_data['reportType'];
        if($report_type === 'executive') {
          $heading1 = 'Sales by Executive';
          $heading2 = 'from '.$form_data['fromDate'].' to '.$form_data['toDate'];
          $csv_headings = [ [$heading1], [$heading2] ];
          // csv format
          $format = $form_data['format'];
          if($format === 'csv') {
            $total_records = $this->_format_sales_by_executive($sales_api_response['sales']);
            Utilities::download_as_CSV_attachment('SalesByExecutive', $csv_headings, $total_records);
            return;
          }

          // PDF Printing
          $item_widths = array(10,80,30,30);
          $totals_width = $item_widths[0] + $item_widths[1];
          $slno = 0;

          $pdf = PDF::getInstance();
          $pdf->AliasNbPages();
          $pdf->AddPage('P','A4');
          $pdf->SetAutoPageBreak(false);

          // Page heading.
          $this->_add_page_heading_for_sales_by_executive($pdf, $item_widths, $heading1, $heading2);

          $tot_taxable_amount = $tot_bill_value = $slno = 0;
          $row_cntr = 0;
          foreach($sales_api_response['sales'] as $record_details) {
            $slno++;
            $tot_taxable_amount += $record_details['taxableAmount'];
            $tot_bill_value += $record_details['billValue'];

            $row_cntr++;

            $executive_name = $record_details['buName'] !== '' ? $record_details['buName'] : '';
            $taxable_amount = number_format($record_details['taxableAmount'], 2, '.', '');
            $bill_value =  number_format($record_details['billValue'], 2, '.', '');

            $pdf->Ln();
            $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
            $pdf->Cell($item_widths[1],6,substr($executive_name,0,40),'RTB',0,'L');
            $pdf->Cell($item_widths[2],6,$taxable_amount,'RTB',0,'R');
            $pdf->Cell($item_widths[3],6,$bill_value,'RTB',0,'R');            
            if($row_cntr === 39) {
              $pdf->AddPage('P','A4');
              $this->_add_page_heading_for_sales_by_executive($pdf, $item_widths, $heading1, $heading2);
              $row_cntr = 0;
            }
          }

          $pdf->Ln();
          $pdf->SetFont('Arial','B',9);
          $pdf->Cell($totals_width,6,'Totals','LBR',0,'R');
          $pdf->Cell($item_widths[2],6,number_format($tot_taxable_amount,2,'.',''),'BR',0,'R');
          $pdf->Cell($item_widths[3],6,number_format($tot_bill_value,2,'.',''),'RB',0,'R');

          $pdf->Output();
        }
        if($report_type === 'city') {
          $heading1 = 'Sales by City';
          $heading2 = 'from '.$form_data['fromDate'].' to '.$form_data['toDate'];
          $csv_headings = [ [$heading1], [$heading2] ];
          // csv format
          $format = $form_data['format'];
          if($format === 'csv') {
            $total_records = $this->_format_sales_by_city($sales_api_response['sales']);
            Utilities::download_as_CSV_attachment('SalesByCity', $csv_headings, $total_records);
            return;
          }

          // PDF Printing
          $item_widths = array(10, 80, 40, 30,30);
          $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2];
          $slno = 0;

          $pdf = PDF::getInstance();
          $pdf->AliasNbPages();
          $pdf->AddPage('P','A4');
          $pdf->SetAutoPageBreak(false);

          // Page heading.
          $this->_add_page_heading_for_sales_by_city($pdf, $item_widths, $heading1, $heading2);

          $tot_taxable_amount = $tot_bill_value = $slno = 0;
          $row_cntr = 0;
          $state_ids = Constants::$LOCATION_STATES;
          foreach($sales_api_response['sales'] as $record_details) {
            $slno++;
            $tot_taxable_amount += $record_details['taxableAmount'];
            $tot_bill_value += $record_details['billValue'];

            $row_cntr++;

            $city_name = $record_details['cityName'];
            $state_name = (int)$record_details['stateID'] > 0 ? $state_ids[$record_details['stateID']] : '';
            $taxable_amount = number_format($record_details['taxableAmount'], 2, '.', '');
            $bill_value =  number_format($record_details['billValue'], 2, '.', '');

            $pdf->Ln();
            $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
            $pdf->Cell($item_widths[1],6,$city_name,'RTB',0,'L');
            $pdf->Cell($item_widths[2],6,$state_name,'RTB',0,'L');
            $pdf->Cell($item_widths[3],6,$taxable_amount,'RTB',0,'R');            
            $pdf->Cell($item_widths[4],6,$bill_value,'RTB',0,'R');            
            if($row_cntr === 39) {
              $pdf->AddPage('P','A4');
              $this->_add_page_heading_for_sales_by_city($pdf, $item_widths, $heading1, $heading2);
              $row_cntr = 0;
            }
          }

          $pdf->Ln();
          $pdf->SetFont('Arial','B',9);
          $pdf->Cell($totals_width,6,'Totals','LBR',0,'R');
          $pdf->Cell($item_widths[3],6,number_format($tot_taxable_amount,2,'.',''),'BR',0,'R');
          $pdf->Cell($item_widths[4],6,number_format($tot_bill_value,2,'.',''),'RB',0,'R');

          $pdf->Output();
        }
        if($report_type === 'state') {
          $heading1 = 'Sales by State/Region';
          $heading2 = 'from '.$form_data['fromDate'].' to '.$form_data['toDate'];
          $csv_headings = [ [$heading1], [$heading2] ];
          // csv format
          $format = $form_data['format'];
          if($format === 'csv') {
            $total_records = $this->_format_sales_by_state($sales_api_response['sales']);
            Utilities::download_as_CSV_attachment('SalesByStateOrRegion', $csv_headings, $total_records);
            return;
          }

          // PDF Printing
          $item_widths = array(10, 80, 30,30);
          $totals_width = $item_widths[0] + $item_widths[1];
          $slno = 0;

          $pdf = PDF::getInstance();
          $pdf->AliasNbPages();
          $pdf->AddPage('P','A4');
          $pdf->SetAutoPageBreak(false);

          // Page heading.
          $this->_add_page_heading_for_sales_by_state($pdf, $item_widths, $heading1, $heading2);

          $tot_taxable_amount = $tot_bill_value = $slno = 0;
          $row_cntr = 0;
          $state_ids = Constants::$LOCATION_STATES;
          foreach($sales_api_response['sales'] as $record_details) {
            $slno++;
            $tot_taxable_amount += $record_details['taxableAmount'];
            $tot_bill_value += $record_details['billValue'];

            $row_cntr++;

            $state_name = (int)$record_details['stateID'] > 0 ? $state_ids[$record_details['stateID']] : '';
            $taxable_amount = number_format($record_details['taxableAmount'], 2, '.', '');
            $bill_value =  number_format($record_details['billValue'], 2, '.', '');

            $pdf->Ln();
            $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
            $pdf->Cell($item_widths[1],6,$state_name,'RTB',0,'L');
            $pdf->Cell($item_widths[2],6,$taxable_amount,'RTB',0,'R');            
            $pdf->Cell($item_widths[3],6,$bill_value,'RTB',0,'R');            
            if($row_cntr === 39) {
              $pdf->AddPage('P','A4');
              $this->_add_page_heading_for_sales_by_city($pdf, $item_widths, $heading1, $heading2);
              $row_cntr = 0;
            }
          }

          $pdf->Ln();
          $pdf->SetFont('Arial','B',9);
          $pdf->Cell($totals_width,6,'Totals','LBR',0,'R');
          $pdf->Cell($item_widths[2],6,number_format($tot_taxable_amount,2,'.',''),'BR',0,'R');
          $pdf->Cell($item_widths[3],6,number_format($tot_bill_value,2,'.',''),'RB',0,'R');

          $pdf->Output();
        }
        if($report_type === 'agent') {
          $heading1 = 'Sales by Agent';
          $heading2 = 'from '.$form_data['fromDate'].' to '.$form_data['toDate'];
          $csv_headings = [ [$heading1], [$heading2] ];
          // csv format
          $format = $form_data['format'];
          if($format === 'csv') {
            $total_records = $this->_format_sales_by_agent($sales_api_response['sales']);
            Utilities::download_as_CSV_attachment('SalesByAgent', $csv_headings, $total_records);
            return;
          }

          // PDF Printing
          $item_widths = array(10, 80, 30,30);
          $totals_width = $item_widths[0] + $item_widths[1];
          $slno = 0;

          $pdf = PDF::getInstance();
          $pdf->AliasNbPages();
          $pdf->AddPage('P','A4');
          $pdf->SetAutoPageBreak(false);

          // Page heading.
          $this->_add_page_heading_for_sales_by_agent($pdf, $item_widths, $heading1, $heading2);

          $tot_taxable_amount = $tot_bill_value = $slno = 0;
          $row_cntr = 0;
          $state_ids = Constants::$LOCATION_STATES;
          foreach($sales_api_response['sales'] as $record_details) {
            $slno++;
            $tot_taxable_amount += $record_details['taxableAmount'];
            $tot_bill_value += $record_details['billValue'];

            $row_cntr++;

            $agent_name = $record_details['buName'] !== '' ?  $record_details['buName'] : '';
            $taxable_amount = number_format($record_details['taxableAmount'], 2, '.', '');
            $bill_value =  number_format($record_details['billValue'], 2, '.', '');

            $pdf->Ln();
            $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
            $pdf->Cell($item_widths[1],6,$agent_name,'RTB',0,'L');
            $pdf->Cell($item_widths[2],6,$taxable_amount,'RTB',0,'R');            
            $pdf->Cell($item_widths[3],6,$bill_value,'RTB',0,'R');            
            if($row_cntr === 39) {
              $pdf->AddPage('P','A4');
              $this->_add_page_heading_for_sales_by_agent($pdf, $item_widths, $heading1, $heading2);
              $row_cntr = 0;
            }
          }

          $pdf->Ln();
          $pdf->SetFont('Arial','B',9);
          $pdf->Cell($totals_width,6,'Totals','LBR',0,'R');
          $pdf->Cell($item_widths[2],6,number_format($tot_taxable_amount,2,'.',''),'BR',0,'R');
          $pdf->Cell($item_widths[3],6,number_format($tot_bill_value,2,'.',''),'RB',0,'R');

          $pdf->Output();
        }
        if($report_type === 'brand') {

          # ---------- get location codes from api -----------------------
          $client_locations = Utilities::get_client_locations(true);
          $location_with_ids_a = [];
          foreach($client_locations as $location_key => $location_name) {
            $location_a = explode('`', $location_key);
            $location_with_ids_a[$location_a[1]] = $location_name;
          }

          $heading1 = 'Sales by Brand';
          $heading2 = 'from '.$form_data['fromDate'].' to '.$form_data['toDate'];
          $csv_headings = [ [$heading1], [$heading2] ];
          // csv format
          $format = $form_data['format'];
          if($format === 'csv') {
            $total_records = $this->_format_sales_by_brand($sales_api_response['sales'], $location_with_ids_a);
            Utilities::download_as_CSV_attachment('SalesByBrand', $csv_headings, $total_records);
            return;
          }

          // PDF Printing
          $item_widths = array(10, 60, 60, 30, 30);
          $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2];
          $slno = 0;

          $pdf = PDF::getInstance();
          $pdf->AliasNbPages();
          $pdf->AddPage('P','A4');
          $pdf->SetAutoPageBreak(false);

          // Page heading.
          $this->_add_page_heading_for_sales_by_brand($pdf, $item_widths, $heading1, $heading2);

          $tot_qty = $tot_amount = $slno = 0;
          $row_cntr = 0;
          foreach($sales_api_response['sales'] as $record_details) {
            $slno++;
            $tot_qty    +=  $record_details['itemQty'];
            $tot_amount +=  $record_details['itemValue'];

            $row_cntr++;

            $brand_name = $record_details['mfgName'] !== '' ?  $record_details['mfgName'] : '';
            $location_id = $record_details['locationID'];
            $brand_qty = number_format($record_details['itemQty'], 2, '.', '');
            $brand_value = number_format($record_details['itemValue'], 2, '.', '');

            $pdf->Ln();
            $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
            $pdf->Cell($item_widths[1],6,$brand_name,'RTB',0,'L');
            $pdf->Cell($item_widths[2],6,$location_with_ids_a[$location_id],'RTB',0,'L');            
            $pdf->Cell($item_widths[3],6,$brand_qty,'RTB',0,'R');            
            $pdf->Cell($item_widths[4],6,$brand_value,'RTB',0,'R');            
            if($row_cntr === 39) {
              $pdf->AddPage('P','A4');
              $this->_add_page_heading_for_sales_by_brand($pdf, $item_widths, $heading1, $heading2);
              $row_cntr = 0;
            }
          }

          $pdf->Ln();
          $pdf->SetFont('Arial','B',9);
          $pdf->Cell($totals_width,6,'Totals','LBR',0,'R');
          $pdf->Cell($item_widths[3],6,number_format($tot_qty,2,'.',''),'BR',0,'R');
          $pdf->Cell($item_widths[4],6,number_format($tot_amount,2,'.',''),'RB',0,'R');

          $pdf->Output();
        }
      }
    }

    // controller variables.
    $controller_vars = array(
      'page_title' => 'MIS Reports - Sales',
      'icon_name' => 'fa fa-money',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
      'report_types_a' => ['executive' => 'By Executive', 'city' => 'By City', 'state' => 'By State', 'agent' => 'By Agent', 'brand' => 'By Brand'],
    );

    // render template
    return [$this->template->render_view('sales-by-mis', $template_vars), $controller_vars];
  }

  private function _add_page_heading_for_sales_upi_payments_register(&$pdf=null, $item_widths=[], $heading1='', $heading2='') {
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');
    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');
    $pdf->SetFont('Arial','B',8);
    $pdf->Ln(3);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Customer name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Mobile No.','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Invoice No.','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Invoice Date','RTB',0,'C');
    $pdf->Cell($item_widths[5],6,'Amount (Rs.)','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'UPI/EMICard','RTB',0,'C');
    $pdf->Cell($item_widths[7],6,'Ref. No.','RTB',0,'C');
    $pdf->SetFont('Arial','',8);
  }

  private function _add_page_heading_for_sales_by_executive(&$pdf=null, $item_widths=[], $heading1='', $heading2='') {
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');
    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');
    $pdf->SetFont('Arial','B',8);
    $pdf->Ln(3);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Excecutive name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Taxable amount (Rs.)','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Invoice value (Rs.)','RTB',0,'C');
    $pdf->SetFont('Arial','',8);
  }

  private function _add_page_heading_for_sales_by_city(&$pdf=null, $item_widths=[], $heading1='', $heading2='') {
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');
    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');
    $pdf->SetFont('Arial','B',8);
    $pdf->Ln(3);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'City name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'State name','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Taxable amount (Rs.)','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Invoice value (Rs.)','RTB',0,'C');
    $pdf->SetFont('Arial','',8);
  }

  private function _add_page_heading_for_sales_by_state(&$pdf=null, $item_widths=[], $heading1='', $heading2='') {
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');
    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');
    $pdf->SetFont('Arial','B',8);
    $pdf->Ln(3);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'State/Region name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Taxable amount (Rs.)','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Invoice value (Rs.)','RTB',0,'C');
    $pdf->SetFont('Arial','',8);
  }

  private function _add_page_heading_for_sales_by_agent(&$pdf=null, $item_widths=[], $heading1='', $heading2='') {
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');
    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');
    $pdf->SetFont('Arial','B',8);
    $pdf->Ln(3);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Agent name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Taxable amount (Rs.)','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Invoice value (Rs.)','RTB',0,'C');
    $pdf->SetFont('Arial','',8);
  }

  public function _add_page_heading_for_sales_by_brand(&$pdf=null, $item_widths=[], $heading1='', $heading2='') {
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');
    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');
    $pdf->SetFont('Arial','B',8);
    $pdf->Ln(3);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Brand Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Store / Location Name','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Value (in Rs.)','RTB',0,'C');
    $pdf->SetFont('Arial','',8);    
  } 

  private function _get_sales_executives() {
    if($_SESSION['__utype'] !== 3) {
      $sexe_response = $this->bu_model->get_business_users(['userType' => 92]);
    } else {
      $sexe_response = $this->bu_model->get_business_users(['userType' => 92, 'locationCode' => $default_location]);      
    }
    if($sexe_response['status']) {
      foreach($sexe_response['users'] as $user_details) {
        $sa_executives[$user_details['userCode']] = $user_details['userName'];
      }
    } else {
      $sa_executives = [''=>'All Sales Executives'];
    }
    return $sa_executives;    
  }

  private function _validate_form_data($form_data = []) {
    $cleaned_params = $form_errors = [];
    // if($form_data['locationCode'] !== '') {
    //   $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    // } else {
    //   $form_errors['StoreName'] = 'Invalid Store Name.';
    // }
    if($form_data['fromDate'] !== '') {
      $cleaned_params['fromDate'] = Utilities::clean_string($form_data['fromDate']);
    } else {
      $form_errors['FromDate'] = 'Invalid From Date';
    }
    if($form_data['toDate'] !== '') {
      $cleaned_params['toDate'] = Utilities::clean_string($form_data['toDate']);
    } else {
      $form_errors['ToDate'] = 'Invalid To Date';
    }

    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);
    $cleaned_params['saExecutiveCode'] = Utilities::clean_string($form_data['saExecutiveCode']);

    if(count($form_errors) > 0) {
      return ['status' => false, 'form_errors' => $form_errors];
    } else {
      return ['status' => true, 'cleaned_params' => $cleaned_params];
    }
  }

  private function _validate_sales_mis_by_filter($form_data = []) {
    $cleaned_params = $form_errors = [];
    if($form_data['reportType'] !== '') {
      $cleaned_params['reportType'] = Utilities::clean_string($form_data['reportType']);
    } else {
      $form_errors['reportType'] = 'Invalid report type.';
    }
    if($form_data['fromDate'] !== '') {
      $cleaned_params['fromDate'] = Utilities::clean_string($form_data['fromDate']);
    } else {
      $form_errors['FromDate'] = 'Invalid From Date.';
    }
    if($form_data['toDate'] !== '') {
      $cleaned_params['toDate'] = Utilities::clean_string($form_data['toDate']);
    } else {
      $form_errors['ToDate'] = 'Invalid To Date.';
    }
    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);

    if(count($form_errors) > 0) {
      return ['status' => false, 'form_errors' => $form_errors];
    } else {
      return ['status' => true, 'cleaned_params' => $cleaned_params];
    }
  }

  private function _validate_form_data_billwise_itemwise($form_data = []) {
    $cleaned_params = $form_errors = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['StoreName'] = 'Invalid Store Name.';
    }
    if($form_data['fromDate'] !== '') {
      $cleaned_params['fromDate'] = Utilities::clean_string($form_data['fromDate']);
    } else {
      $form_errors['FromDate'] = 'Invalid From Date.';
    }
    if($form_data['toDate'] !== '') {
      $cleaned_params['toDate'] = Utilities::clean_string($form_data['toDate']);
    } else {
      $form_errors['ToDate'] = 'Invalid To Date.';
    }

    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);

    if(count($form_errors) > 0) {
      return ['status' => false, 'form_errors' => $form_errors];
    } else {
      return ['status' => true, 'cleaned_params' => $cleaned_params];
    }    
  }

  private function _validate_form_data_sales_upi_payments_register($form_data=[]) {
    $cleaned_params = $form_errors = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['StoreName'] = 'Invalid Store Name.';
    }
    if($form_data['fromDate'] !== '') {
      $cleaned_params['fromDate'] = Utilities::clean_string($form_data['fromDate']);
    } else {
      $form_errors['FromDate'] = 'Invalid From Date.';
    }
    if($form_data['toDate'] !== '') {
      $cleaned_params['toDate'] = Utilities::clean_string($form_data['toDate']);
    } else {
      $form_errors['ToDate'] = 'Invalid To Date.';
    }

    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);
    $cleaned_params['walletID'] = Utilities::clean_string($form_data['walletID']);

    if(count($form_errors) > 0) {
      return ['status' => false, 'form_errors' => $form_errors];
    } else {
      return ['status' => true, 'cleaned_params' => $cleaned_params];
    } 
  }

  private function _validate_form_data_day_sales($form_data = []) {
    $cleaned_params = $form_errors = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['StoreName'] = 'Invalid Store Name.';
    }
    if($form_data['saleDate'] !== '') {
      $cleaned_params['saleDate'] = Utilities::clean_string($form_data['saleDate']);
    } else {
      $form_errors['SaleDate'] = 'Sale Date is required.';
    }
    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);

    if(count($form_errors) > 0) {
      return ['status' => false, 'form_errors' => $form_errors];
    } else {
      return ['status' => true, 'cleaned_params' => $cleaned_params];
    }
  }

  private function _validate_form_data_sales_summary_bymon($form_data = []) {
    $cleaned_params = $form_errors = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['StoreName'] = 'Invalid Store Name.';
    }
    if($form_data['fromDate'] !== '') {
      $cleaned_params['fromDate'] = Utilities::clean_string($form_data['fromDate']);
    } else {
      $cleaned_params['fromDate'] = '01-'.date("m-Y");
    }
    if($form_data['toDate'] !== '') {
      $cleaned_params['toDate'] = Utilities::clean_string($form_data['toDate']);
    } else {
      $cleaned_params['toDate'] = date("d-m-Y");
    }    
    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);

    if(count($form_errors) > 0) {
      return ['status' => false, 'form_errors' => $form_errors];
    } else {
      return ['status' => true, 'cleaned_params' => $cleaned_params];
    }
  }

  private function _validate_form_data_itemwise_sr($form_data = []) {
    $cleaned_params = $form_errors = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['StoreName'] = 'Invalid Store Name.';
    }
    if($form_data['fromDate'] !== '') {
      $cleaned_params['fromDate'] = Utilities::clean_string($form_data['fromDate']);
    } else {
      $form_errors['FromDate'] = 'Invalid From Date.';
    }
    if($form_data['toDate'] !== '') {
      $cleaned_params['toDate'] = Utilities::clean_string($form_data['toDate']);
    } else {
      $form_errors['ToDate'] = 'Invalid To Date.';
    }

    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);
    $cleaned_params['sortBy'] = Utilities::clean_string($form_data['sortBy']);

    if(count($form_errors) > 0) {
      return ['status' => false, 'form_errors' => $form_errors];
    } else {
      return ['status' => true, 'cleaned_params' => $cleaned_params];
    }
  }

  private function _validate_form_data_dispatch_register($form_data = []) {
    $cleaned_params = $form_errors = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    // } else {
    //   $form_errors['StoreName'] = 'Invalid Store Name.';
    }
    if($form_data['fromDate'] !== '') {
      $cleaned_params['fromDate'] = Utilities::clean_string($form_data['fromDate']);
    } else {
      $cleaned_params['fromDate'] = '01-'.date("m-Y");
    }
    if($form_data['toDate'] !== '') {
      $cleaned_params['toDate'] = Utilities::clean_string($form_data['toDate']);
    } else {
      $cleaned_params['toDate'] = date("d-m-Y");
    }
    $cleaned_params['customerName'] = Utilities::clean_string($form_data['customerName']);
    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);
    $cleaned_params['agentCode'] = Utilities::clean_string($form_data['agentCode']);
    $cleaned_params['perPage'] = 300;

    if(count($form_errors) > 0) {
      return ['status' => false, 'form_errors' => $form_errors];
    } else {
      return ['status' => true, 'cleaned_params' => $cleaned_params];
    }    
  }

  private function _format_sales_upi_register_csv($total_records=[]) {
    $tot_amount = 0;
    $slno = 0;
    $wallets = Constants::$WALLETS;
    foreach($total_records as $key => $record_details) {
      $slno++;
      $tot_amount += $record_details['netPay'];
      $customer_name = $record_details['customerName'] !== '' ? $record_details['customerName'] : $record_details['cnameTemp'];
      $mobile_no = $record_details['customerMobileNo'];
      $bill_no = $record_details['billNo'];
      $bill_date = date("d/m/Y", strtotime($record_details['billDate']));
      $amount = number_format($record_details['netPay'], 2, '.', '');
      $wallet_name = isset($wallets[$record_details['walletID']]) ? $wallets[$record_details['walletID']] : '-';
      $wallet_ref_no = $record_details['walletRefNo'];

      $cleaned_params[$key] = [
        'Sno.' => $slno,
        'Customer Name' => $customer_name,
        'Mobile No.' => $mobile_no,
        'Invoice No.' => $bill_no,
        'Invoice Date' => $bill_date, 
        'Amount' => $amount,
        'UPI/EMICard' => $wallet_name,
        'Ref.No.' => $wallet_ref_no,
      ];
    }
    $cleaned_params[count($cleaned_params)] = [
      'SNo.' => '' ,
      'Customer Name' => 'T O T A L S',
      'Mobile No.' => '',
      'Invoice No.' => '',
      'Invoice Date' => '', 
      'Amount' => number_format($tot_amount,2,'.',''),
      'UPI/EMI Card' => '',
      'Ref No.' => '',
    ];
    
    return $cleaned_params; 
  } 

  private function _format_sales_register_for_csv($total_records = []) {
    $cleaned_params = [];
    $tot_gross_amount = $tot_discount = $tot_taxable = $tot_gst = $tot_round_off = $tot_net_pay = 0;
    $slno = 0;
    foreach($total_records as $key => $record_details) {
      $slno++;
      $payment_method = Constants::$PAYMENT_METHODS_RC_SHORT[$record_details['paymentMethod']];
      $bill_info = $record_details['billNo'].' / '.date("d-m-y", strtotime($record_details['invoiceDate']));
      $tran_info = date("d-M-Y h:ia", strtotime($record_details['createdOn']));
      if($record_details['customerName'] !== '') {
        $customer_name = $record_details['customerName'];
      } elseif($record_details['customerName'] !== '') {
        $customer_name = $record_details['tmpCustName'];          
      } else {
        $customer_name = '';
      }
      
      $gross_amount = $record_details['billAmount'];
      $discount = $record_details['discountAmount'];
      $taxable = $record_details['totalAmount'];
      $gst = $record_details['taxAmount'];
      $round_off = $record_details['roundOff'];
      $net_pay = $record_details['netPay'];

      $tot_gross_amount += $gross_amount;
      $tot_discount += $discount;
      $tot_taxable += $taxable;
      $tot_gst += $gst;
      $tot_round_off += $round_off;
      $tot_net_pay += $net_pay;

      $cleaned_params[$key] = [
        'SNo.' => $slno,
        'Payment Mode' => $payment_method,
        'Bill No. & Date' => $bill_info,
        'Gross Amount (Rs.)' => number_format($gross_amount, 2, '.', ''),
        'Discount (Rs.)' => number_format($discount, 2, '.', ''),
        'Billed (Rs.)' => number_format($net_pay, 2, '.', ''),
        'Taxable (Rs.)' => number_format($taxable, 2, '.', ''),
        'GST (Rs.)' => number_format($gst, 2, '.', ''),
        'Round Off (Rs.)' => number_format($round_off, 2, '.', ''),
        'CustomerName' => $customer_name,
      ];
    }
    $cleaned_params[count($cleaned_params)] = [
      'SNo.' => '',
      'Payment Mode' => 'T O T A L S',
      'Bill No. & Date' => '',
      'Gross Amount (Rs.)' => number_format($tot_gross_amount, 2, '.', ''),
      'Discount (Rs.)' => number_format($tot_discount, 2, '.', ''),
      'Billed (Rs.)' => number_format($tot_net_pay, 2, '.', ''),
      'Taxable (Rs.)' => number_format($tot_taxable, 2, '.', ''),
      'GST (Rs.)' => number_format($tot_gst, 2, '.', ''),
      'Round Off (Rs.)' => number_format($tot_round_off, 2, '.', ''),
      'CustomerName' => '',
    ];

    return $cleaned_params;
  }

  private function _format_itemwise_sales_register_for_csv($total_records = []) {
    $tot_sold_qty = $tot_amount = $tot_discount = $tot_net_pay = 0;
    $slno = 0;
    foreach($total_records as $key => $record_details) {
      $slno++;
      $net_pay = $record_details['saleValue'] - $record_details['discountAmount'];
      $tot_sold_qty += $record_details['soldQty'];
      $tot_amount += $record_details['saleValue'];
      $tot_discount += $record_details['discountAmount'];
      $tot_net_pay += $net_pay;
      $cleaned_params[$key] = [
        'SNo.' => $slno ,
        'Item Name' => $record_details['itemName'],
        'Brand' => $record_details['brandName'],
        'Category' => $record_details['categoryName'],
        'Item Rate' => number_format($record_details['saleRate'],2,'.',''), 
        'Sold Qty.' => number_format($record_details['soldQty'],2,'.','') ,
        'Total Amt.' => number_format($record_details['saleValue'],2,'.',''),
        'Total Disc.' => number_format($record_details['discountAmount'],2,'.','') ,
        'Net Value' => number_format($net_pay,2,'.',''),
      ];
    }
    $cleaned_params[count($cleaned_params)] = [
      'SNo.' => '' ,
      'Item Name' => 'T O T A L S',
      'HSN/SAC' => '',
      'Category' => '',
      'Item Rate' => '', 
      'Sold Qty.' => number_format($tot_sold_qty,2,'.','') ,
      'Total Amt.' => number_format($tot_amount,2,'.',''),
      'Total Disc.' => number_format($tot_discount,2,'.','') ,
      'Net Value' => number_format($tot_net_pay,2,'.',''),
    ];
    return $cleaned_params;
  }

  private function _format_daywise_sales_summary_for_csv($month_summary = []) {

    $tot_cash_sales = $tot_split_sales = $tot_card_sales = $tot_credit_sales = $tot_sales = 0;
    $tot_discounts = $tot_discount_bills = $tot_returns = 0;
    $tot_cash_payments = $tot_card_payments = $tot_cnote_payments = 0;

    foreach($month_summary as $key => $day_details) {
      $date = date("d-m-Y", strtotime($day_details['tranDate']));
      $week = date("l", strtotime($day_details['tranDate']));
      $day_sales = $day_details['cashSales'] + $day_details['splitSales'] + $day_details['cardSales'] + $day_details['creditSales'];

      $tot_cash_sales += $day_details['cashSales'];
      $tot_card_sales += $day_details['cardSales'];
      $tot_split_sales += $day_details['splitSales'];
      $tot_credit_sales += $day_details['creditSales'];
      $tot_returns += $day_details['returnAmount'];

      $tot_cash_payments += $day_details['cashPayments'];
      $tot_card_payments += $day_details['cardPayments'];
      $tot_cnote_payments += $day_details['cnotePayments'];

      $tot_discounts += $day_details['discountGiven'];
      $tot_discount_bills += $day_details['totalDiscountBills'];

      $cash_sales = $day_details['cashSales'] > 0 ? number_format($day_details['cashSales'],2,'.','') : '';
      $card_sales = $day_details['cardSales'] > 0 ? number_format($day_details['cardSales'],2,'.','') : '';
      $split_sales = $day_details['splitSales'] > 0 ? number_format($day_details['splitSales'],2,'.','') : '';
      $credit_sales = $day_details['creditSales'] > 0 ? number_format($day_details['creditSales'],2,'.','') : '';
      $sales_return = $day_details['returnAmount'] > 0 ?  number_format($day_details['returnAmount'],2,'.','') : '';
      $net_sales = ($day_sales-$day_details['returnAmount']) > 0 ? number_format($day_sales-$day_details['returnAmount'],2,'.','') : '';

      $cash_payments = $day_details['cashPayments'] > 0 ? number_format($day_details['cashPayments'],2,'.','') : '' ;
      $card_payments = $day_details['cardPayments'] > 0 ? number_format($day_details['cardPayments'],2,'.','') : '' ;
      $cnote_payments = $day_details['cnotePayments'] > 0 || $day_details['cnotePayments'] < 0  ? number_format($day_details['cnotePayments'],2,'.','') : '' ;

      $total_sales = number_format($day_details['cashSales']+$day_details['cardSales']+$day_details['splitSales']+$day_details['creditSales'],2,'.','');
      $discount_string = number_format($day_details['discountGiven'],2,'.','').' / '.$day_details['totalDiscountBills'];

      $cleaned_params[$key] = [
        'Date' => $date,
        'Cash Sales' => $cash_sales,
        'Card Sales' => $card_sales,
        'Split Sales' => $split_sales,
        'Credit Sales' => $credit_sales,
        'Gross Sales' => $total_sales,
        'Sales Return' => $sales_return,
        'Net Sales ##' => $net_sales,
        'Paid By Cash' => $cash_payments,
        'Paid By Card' => $card_payments,
        'Credit Notes' => $cnote_payments,
        'Discount / Bills **' => $discount_string,
      ];
    }

    $tot_sales = $tot_cash_sales + $tot_credit_sales + $tot_split_sales + $tot_card_sales;
    $tot_net_sales = $tot_sales - $tot_returns;

    $cleaned_params[count($cleaned_params)] = [
      'Date' => 'T O T A L S',
      'Cash Sales' => $tot_cash_sales,
      'Card Sales' => $tot_card_sales,
      'Split Sales' => $tot_split_sales,
      'Credit Sales' => $tot_credit_sales,
      'Gross Sales' => $tot_sales,
      'Sales Return' => $tot_returns,
      'Net Sales ##' => $tot_net_sales,
      'Paid By Cash' => $tot_cash_payments,
      'Paid By Card' => $tot_card_payments,
      'Credit Notes' => $tot_cnote_payments,
      'Discount / Bills **' => '',
    ];

    return $cleaned_params;
  }

  private function _format_sales_by_tax_rate_report_for_csv($sales_summary = []) {
    $grand_tot_qty = $grand_billable = $grand_taxable = $grand_igst_value = 0;
    $grand_cgst_value = $grand_sgst_value = 0;
    $cleaned_params = [];

    foreach($sales_summary as $key => $day_details) {
      $date = date("d-m-Y", strtotime($day_details['tranDate']));
      $gst_summary = [];
      if($day_details['fivePercentItemQty'] > 0) {
        $gst_summary[5] = [
          'qty' => $day_details['fivePercentItemQty'],
          'billable' => $day_details['fivePercentBillable'],
          'taxable' => $day_details['fivePercentTaxable'],
          'igst' => $day_details['fivePercentIgstAmt'],
          'cgst' => $day_details['fivePercentCgstAmt'],
          'sgst' => $day_details['fivePercentSgstAmt'],
        ];
        $grand_tot_qty += $day_details['fivePercentItemQty'];
        $grand_billable += $day_details['fivePercentBillable'];
        $grand_taxable += $day_details['fivePercentTaxable'];

        $grand_igst_value += $day_details['fivePercentIgstAmt'];
        $grand_cgst_value += $day_details['fivePercentCgstAmt'];
        $grand_sgst_value += $day_details['fivePercentSgstAmt'];
      }
      if($day_details['twelvePercentItemQty'] > 0) {
        $gst_summary[12] = [
          'qty' => $day_details['twelvePercentItemQty'],
          'billable' => $day_details['twelvePercentBillable'],
          'taxable' => $day_details['twelvePercentTaxable'],
          'igst' => $day_details['twelvePercentIgstAmt'],
          'cgst' => $day_details['twelvePercentCgstAmt'],
          'sgst' => $day_details['twelvePercentSgstAmt'],
        ];
        $grand_tot_qty += $day_details['twelvePercentItemQty'];
        $grand_billable += $day_details['twelvePercentBillable'];
        $grand_taxable += $day_details['twelvePercentTaxable'];
        
        $grand_igst_value += $day_details['twelvePercentIgstAmt'];
        $grand_cgst_value += $day_details['twelvePercentCgstAmt'];        
        $grand_sgst_value += $day_details['twelvePercentSgstAmt'];
      }
      if($day_details['eighteenPercentItemQty'] > 0) {
        $gst_summary[18] = [
          'qty' => $day_details['eighteenPercentItemQty'],
          'billable' => $day_details['eighteenPercentBillable'],
          'taxable' => $day_details['eighteenPercentTaxable'],
          'igst' => $day_details['eighteenPercentIgstAmt'],
          'cgst' => $day_details['eighteenPercentCgstAmt'],
          'sgst' => $day_details['eighteenPercentSgstAmt'],
        ];
        $grand_tot_qty += $day_details['eighteenPercentItemQty'];
        $grand_billable += $day_details['eighteenPercentBillable'];
        $grand_taxable += $day_details['eighteenPercentTaxable'];

        $grand_igst_value += $day_details['eighteenPercentIgstAmt'];
        $grand_sgst_value += $day_details['eighteenPercentSgstAmt'];
        $grand_cgst_value += $day_details['eighteenPercentCgstAmt'];        
      }
      if($day_details['twentyEightPercentItemQty'] > 0) {
        $gst_summary[28] = [
          'qty' => $day_details['twentyEightPercentItemQty'],
          'billable' => $day_details['twentyEightPercentBillable'],
          'taxable' => $day_details['twentyEightPercentTaxable'],
          'igst' => $day_details['twentyEightPercentIgstAmt'],
          'cgst' => $day_details['twentyEightPercentCgstAmt'],
          'sgst' => $day_details['twentyEightPercentSgstAmt'],
        ];
        $grand_tot_qty += $day_details['twentyEightPercentItemQty'];
        $grand_billable += $day_details['twentyEightPercentBillable'];
        $grand_taxable += $day_details['twentyEightPercentTaxable'];

        $grand_igst_value += $day_details['twentyEightPercentIgstAmt'];
        $grand_cgst_value += $day_details['twentyEightPercentCgstAmt'];        
        $grand_sgst_value += $day_details['twentyEightPercentSgstAmt'];
      }
      foreach($gst_summary as $key => $gst_summary_details) {
        if($gst_summary_details['igst'] > 0) {
          $igst_amount = number_format($gst_summary_details['igst'],2,'.','');
          $igst_percent = number_format($key,2);
          $cgst_amount = $sgst_amount = '';
        } else {
          $cgst_amount = number_format($gst_summary_details['cgst'],2,'.','');
          $sgst_amount = number_format($gst_summary_details['sgst'],2,'.','');
          $cgst_percent = $sgst_percent = number_format($key/2, 2);
          $igst_percent = '';
          $igst_amount = '';
        }
        $cleaned_params[] = [
          'Date' => $date,
          'Units Sold' => number_format($gst_summary_details['qty'],2,'.',''),
          'Billed (Rs.)' => number_format($gst_summary_details['billable'], 2, '.', ''),
          'Taxable (Rs.)' => number_format($gst_summary_details['taxable'], 2, '.', ''),
          'IGST%' => $igst_percent > 0 ? number_format($igst_percent, 2, '.', '') : '',
          'IGST Value (Rs.)' => $igst_amount > 0 ? number_format($igst_amount, 2, '.', '') : '',
          'CGST%' => $cgst_percent > 0 ? number_format($cgst_percent, 2, '.', '') : '',
          'CGST Value (Rs.)' => $cgst_amount > 0 ? number_format($cgst_amount, 2, '.', '') : '',
          'SGST%' => $sgst_percent >0 ? number_format($sgst_percent, 2, '.', '') : '',
          'SGST Value (Rs.)' => $sgst_amount > 0 ? number_format($sgst_amount, 2, '.', '') : '',
          'GST%' => number_format($key, 2, '.', ''),
          'GST Value (Rs.)' => number_format($gst_summary_details['igst']+$gst_summary_details['cgst']+$gst_summary_details['sgst'], 2, '.', ''),
        ];
      }
    }

    $cleaned_params[] = [
      'Date' => 'T O T A L S',
      'Units Sold' => number_format($grand_tot_qty,2,'.',''),
      'Billed (Rs.)' => number_format($grand_billable, 2, '.', ''),
      'Taxable (Rs.)' => number_format($grand_taxable, 2, '.', ''),
      'IGST%' => '',
      'IGST Value (Rs.)' => number_format($grand_igst_value, 2, '.', ''),
      'CGST%' => '',
      'CGST Value (Rs.)' => number_format($grand_cgst_value, 2, '.', ''),
      'SGST%' => '',
      'SGST Value (Rs.)' => number_format($grand_sgst_value, 2, '.', ''),
      'GST%' => '',
      'GST Value (Rs.)' => number_format($grand_igst_value+$grand_cgst_value+$grand_sgst_value, 2, '.', ''),
    ];

    return $cleaned_params;
  }

  private function _format_sales_by_hsn_code_for_csv($sales_summary = []) {
    $cleaned_params = [];

    $codewise_taxable = $codewise_gst = 0;
    $tot_cash_payments = $tot_card_payments = $tot_credit_sales = $tot_cnote_payments = 0;
    $tot_return_amount = $tot_day_round_off = $tot_net_sales = 0;

    $grand_tot_qty = $grand_billable = $grand_taxable = $grand_igst_value = 0;
    $grand_cgst_value = $grand_sgst_value = 0;

    foreach($sales_summary as $day_details) {
      $date = date("d-m-Y", strtotime($day_details['tranDate']));
      $hsn_sac_code = $day_details['hsnSacCode'];
      $hsn_sac_short_name = $day_details['hsnsacDescShort'];
      $uom_name = $day_details['uomName'];      

      $cash_payments = isset($day_details['cashPayments']) ? $day_details['cashPayments'] : 0;
      $card_payments = isset($day_details['cardPayments']) ? $day_details['cardPayments'] : 0;
      $credit_sales = isset($day_details['creditSales']) ? $day_details['creditSales'] : 0;
      $cnote_payments = isset($day_details['cnotePayments']) ? $day_details['cnotePayments'] : 0;
      $return_amount = isset($day_details['returnAmount']) ? $day_details['returnAmount'] : 0;
      $day_round_off = 0;
      $net_day_sales = ($cash_payments+$card_payments+$credit_sales+$cnote_payments) - $return_amount;

      $gst_summary = [];
      if($day_details['fivePercentItemQty'] > 0) {
        $gst_summary[5] = [
          'qty' => $day_details['fivePercentItemQty'],
          'billable' => $day_details['fivePercentBillable'],
          'taxable' => $day_details['fivePercentTaxable'],
          'igst' => $day_details['fivePercentIgstAmt'],
          'cgst' => $day_details['fivePercentCgstAmt'],
          'sgst' => $day_details['fivePercentSgstAmt'],
        ];
        $grand_tot_qty += $day_details['fivePercentItemQty'];
        $grand_billable += $day_details['fivePercentBillable'];
        $grand_taxable += $day_details['fivePercentTaxable'];

        $grand_igst_value += $day_details['fivePercentIgstAmt'];
        $grand_cgst_value += $day_details['fivePercentCgstAmt'];
        $grand_sgst_value += $day_details['fivePercentSgstAmt'];
      }
      if($day_details['twelvePercentItemQty'] > 0) {
        $gst_summary[12] = [
          'qty' => $day_details['twelvePercentItemQty'],
          'billable' => $day_details['twelvePercentBillable'],
          'taxable' => $day_details['twelvePercentTaxable'],
          'igst' => $day_details['twelvePercentIgstAmt'],
          'cgst' => $day_details['twelvePercentCgstAmt'],
          'sgst' => $day_details['twelvePercentSgstAmt'],
        ];
        $grand_tot_qty += $day_details['twelvePercentItemQty'];
        $grand_billable += $day_details['twelvePercentBillable'];
        $grand_taxable += $day_details['twelvePercentTaxable'];
        
        $grand_igst_value += $day_details['twelvePercentIgstAmt'];
        $grand_cgst_value += $day_details['twelvePercentCgstAmt'];        
        $grand_sgst_value += $day_details['twelvePercentSgstAmt'];
      }
      if($day_details['eighteenPercentItemQty'] > 0) {
        $gst_summary[18] = [
          'qty' => $day_details['eighteenPercentItemQty'],
          'billable' => $day_details['eighteenPercentBillable'],
          'taxable' => $day_details['eighteenPercentTaxable'],
          'igst' => $day_details['eighteenPercentIgstAmt'],
          'cgst' => $day_details['eighteenPercentCgstAmt'],
          'sgst' => $day_details['eighteenPercentSgstAmt'],
        ];
        $grand_tot_qty += $day_details['eighteenPercentItemQty'];
        $grand_billable += $day_details['eighteenPercentBillable'];
        $grand_taxable += $day_details['eighteenPercentTaxable'];

        $grand_igst_value += $day_details['eighteenPercentIgstAmt'];
        $grand_sgst_value += $day_details['eighteenPercentSgstAmt'];
        $grand_cgst_value += $day_details['eighteenPercentCgstAmt'];        
      }
      if($day_details['twentyEightPercentItemQty'] > 0) {
        $gst_summary[28] = [
          'qty' => $day_details['twentyEightPercentItemQty'],
          'billable' => $day_details['twentyEightPercentBillable'],
          'taxable' => $day_details['twentyEightPercentTaxable'],
          'igst' => $day_details['twentyEightPercentIgstAmt'],
          'cgst' => $day_details['twentyEightPercentCgstAmt'],
          'sgst' => $day_details['twentyEightPercentSgstAmt'],
        ];
        $grand_tot_qty += $day_details['twentyEightPercentItemQty'];
        $grand_billable += $day_details['twentyEightPercentBillable'];
        $grand_taxable += $day_details['twentyEightPercentTaxable'];

        $grand_igst_value += $day_details['twentyEightPercentIgstAmt'];
        $grand_cgst_value += $day_details['twentyEightPercentCgstAmt'];        
        $grand_sgst_value += $day_details['twentyEightPercentSgstAmt'];
      }

      // dump($gst_summary);
      // exit;

      foreach($gst_summary as $key => $gst_summary_details) {

        if($gst_summary_details['igst'] > 0) {
          $igst_amount = number_format($gst_summary_details['igst'],2,'.','');
          $igst_percent = number_format($key,2);
          $cgst_amount = $sgst_amount = '';
        } else {
          $cgst_amount = number_format($gst_summary_details['cgst'],2,'.','');
          $sgst_amount = number_format($gst_summary_details['sgst'],2,'.','');
          $cgst_percent = $sgst_percent = number_format($key/2, 2);
          $igst_percent = '';
          $igst_amount = '';
        }

        $codewise_taxable += $gst_summary_details['taxable'];
        $codewise_gst += ($gst_summary_details['igst'] + $gst_summary_details['cgst'] + $gst_summary_details['sgst']);

        /* It implies that we reached end of day. */
        if(isset($day_details['cashPayments'])) {
          $total_codewise_day_sales = ($codewise_taxable+$codewise_gst)-$return_amount;
          $day_round_off = ($net_day_sales-$total_codewise_day_sales);

          // dump($date.'====>'.$net_day_sales.'===>'.$total_codewise_day_sales);

          if($cash_payments > 0) {
            $cash_payments -= $day_round_off;
          } elseif($card_payments > 0) {
            $card_payments -= $day_round_off;
          } elseif($credit_sales > 0) {
            $credit_sales -= $day_round_off;
          }
          $codewise_taxable = $codewise_gst = 0;

          $tot_cash_payments += $cash_payments;
          $tot_card_payments += $card_payments;
          $tot_credit_sales += $credit_sales;
          $tot_cnote_payments += $cnote_payments;
          $tot_return_amount += $return_amount;
          $tot_day_round_off += $day_round_off;
          $tot_net_sales += $net_day_sales;
        }

        $cleaned_params[] = [
          'Date' => $date,
          'Qty.' => number_format($gst_summary_details['qty'],2, '.', ''),
          'UOM' => $uom_name,
          'Item / Category Name' => $hsn_sac_short_name,
          'HSN/SAC Code' => $hsn_sac_code,
          'Billed Amount' => number_format($gst_summary_details['billable'],2, '.', ''),
          'Taxable Amount' => number_format($gst_summary_details['taxable'],2, '.', '') ,
          'IGST%' => $igst_percent,
          'IGST Value' => $igst_amount,
          'CGST%' => $cgst_percent,
          'CGST Value' => $cgst_amount,
          'SGST%' => $sgst_percent,
          'SGST Value' => $sgst_amount,
          'GST%' => number_format($key,2),
          'GST Value' => number_format($gst_summary_details['igst']+$gst_summary_details['cgst']+$gst_summary_details['sgst'], 2, '.', ''),
          'Cash' => number_format($cash_payments,2, '.', '') ,
          'Card' => number_format($card_payments,2, '.', ''),
          'Credit' => number_format($credit_sales,2, '.', ''),
          'Cnote' => number_format($cnote_payments,2, '.', ''),
          'Returns' => number_format($return_amount,2, '.', ''),
          'Rounding off' => number_format($day_round_off,2,'.',''),
          'Net Sales' => number_format($net_day_sales,2,'.',''),
        ];
      }
    }

    $cleaned_params[count($cleaned_params)] = [
      'Date' => 'REPORT T O T A L S',
      'Qty.' => number_format($grand_tot_qty,2, '.', ''),
      'UOM' => '',
      'Item / Category Name' => '',
      'HSN/SAC Code' => '',
      'Billed Amount' => number_format($grand_billable,2, '.', ''),
      'Taxable Amount' => number_format($grand_taxable,2, '.', '') ,
      'IGST%' => '',
      'IGST Value' => $grand_igst_value>0 ? number_format($grand_igst_value, 2, '.', '') : '',
      'CGST%' => '',
      'CGST Value' => $grand_cgst_value>0 ? number_format($grand_cgst_value, 2, '.', '') : '',
      'SGST%' => '',
      'SGST Value' => $grand_sgst_value>0 ? number_format($grand_sgst_value, 2, '.', '') : '',
      'GST%' => '',
      'GST Value' => number_format($grand_igst_value+$grand_cgst_value+$grand_sgst_value, 2, '.', ''),
      'Cash' => number_format($tot_cash_payments,2, '.', '') ,
      'Card' => number_format($tot_card_payments,2, '.', ''),
      'Credit' => number_format($tot_credit_sales,2, '.', ''),
      'Cnote' => number_format($tot_cnote_payments,2, '.', ''),
      'Returns' => number_format($tot_return_amount,2, '.', ''),
      'Rounding off' => number_format($tot_day_round_off,2,'.',''),
      'Net Sales' => number_format($tot_net_sales,2,'.',''),
    ];

    return $cleaned_params; 
  }

  private function _format_billwise_itemwise_sr_for_csv($total_records = []) {
    $cleaned_params = [];

    $tot_sold_qty = $tot_amount = $tot_discount = $tot_net_pay = 0;
    $slno = $bill_qty = $tot_bill_tax = 0;
    $old_bill_no = $new_bill_no = $total_records[0]['invoiceNo'];
    foreach($total_records as $key => $record_details) {
      $slno++;
      $new_bill_no = $record_details['invoiceNo'];
      if($old_bill_no !== $new_bill_no) {
        $bill_total = $total_records[$key-1]['billAmount'];
        $bill_discount = $total_records[$key-1]['billDiscount'];
        $netpay =  $total_records[$key-1]['netpay'];
        $bill_tax = $total_records[$key-1]['taxAmount'];
        if($tax_calc_option === 'i') {
          $bill_tax = '';
        } else {
          $bill_tax = number_format($bill_tax, 2, '.', '');
        }        

        $cleaned_params[] = [
          'Sl. No.' => '',
          'Bill No.' => '',
          'Bill Date' => '',
          'Item Name' => 'BILL TOTALS',
          'Qty.' => number_format($bill_qty,2,'.',''),
          'CASE No.' => '',
          'Batch No.' => '',
          'Item SKU' => '',
          'Item Rate' => '',
          'Gross Amt.' => number_format($bill_total,2,'.',''),
          'Discount' => number_format($bill_discount,2,'.',''),
          'Tax' => $bill_tax,
          'Net Amount' => number_format($netpay,2,'.',''),
          'Customer Name' => '',
          'Remarks / Notes' => '',
        ];
        $cleaned_params[] = [
          'Sl. No.' => '',
          'Bill No.' => '',
          'Bill Date' => '',
          'Item Name' => '',
          'Qty.' => '',
          'CASE No.' => '',
          'Batch No.' => '',
          'Item SKU' => '',
          'Item Rate' => '',
          'Gross Amt.' => '',
          'Discount' => '',
          'Tax' => '',
          'Net Amount' => '',
          'Customer Name' => '',
          'Remarks / Notes' => '',
        ];        

        $tot_sold_qty += $bill_qty;
        $tot_amount += $bill_total;
        $tot_discount += $bill_discount;
        $tot_net_pay += $netpay;
        $tot_bill_tax += $bill_tax;

        $old_bill_no = $new_bill_no;
        $bill_qty = $bill_total = $bill_discount = $netpay = $bill_tax = 0;
      }        
      $bill_qty += $record_details['soldQty'];
      $item_amount = round($record_details['soldQty']*$record_details['mrp'], 2);
      $item_value = $item_amount - $record_details['itemDiscount'];
      if(isset($record_details['remarksInvoice'])) {
        $remarks_invoice = $record_details['remarksInvoice'];
      } else {
        $remarks_invoice = '';
      }
      if($record_details['customerName'] !== '') {
        $customer_name = $record_details['customerName'];
      } elseif($record_details['tmpCustomerName'] !== '') {
        $customer_name = $record_details['tmpCustomerName'];
      } else {
        $customer_name = '';
      }

      $tax_calc_option = $total_records[$key]['taxCalcOption'];
      
      $cleaned_params[] = [
        'Sl. No.' => $slno,
        'Bill No.' => $record_details['invoiceNo'],
        'Bill Date' => date("d-m-Y", strtotime($record_details['invoiceDate'])),
        'Item Name' => $record_details['itemName'],
        'Qty.' => number_format($record_details['soldQty'],2,'.',''),
        'CASE No.' => $record_details['cno'],
        'Batch No.' => $record_details['bno'],
        'Item SKU' => $record_details['itemSku'],
        'Item Rate' => number_format($record_details['mrp'],2,'.',''),
        'Gross Amt.' => number_format($item_amount,2,'.',''),
        'Discount' => number_format($record_details['itemDiscount'],2,'.',''),
        'Tax' => '',
        'Net Amount' => number_format($item_value,2,'.',''),
        'Customer Name' => $customer_name, 
        'Remarks / Notes' => $remarks_invoice,
      ];
    }

    // dump($cleaned_params);
    // exit;

    $bill_total = $total_records[$key]['billAmount'];
    $bill_discount = $total_records[$key]['billDiscount'];
    $netpay =  $total_records[$key]['netpay'];
    $bill_tax = $total_records[$key]['taxAmount'];

    $tot_sold_qty += $bill_qty;
    $tot_amount += $bill_total;
    $tot_discount += $bill_discount;
    $tot_net_pay += $netpay;
    $tot_bill_tax += $bill_tax;

    if($tax_calc_option === 'i') {
      $bill_tax = '';
    } else {
      $bill_tax = number_format($bill_tax, 2, '.', '');
    }        

    $cleaned_params[] = [
      'Sl. No.' => '',
      'Bill No.' => '',
      'Bill Date' => '',
      'Item Name' => 'BILL TOTALS',
      'Qty.' => number_format($bill_qty,2,'.',''),
      'CASE No.' => '',
      'Item Rate' => '',
      'Gross Amt.' => number_format($bill_total,2,'.',''),
      'Discount' => number_format($bill_discount,2,'.',''),
      'Tax' => $bill_tax,
      'Net Amount' => number_format($netpay,2,'.',''),
      'Customer Name' => '',
      'Remarks / Notes' => '',
    ];
    $cleaned_params[] = [
      'Sl. No.' => '',
      'Bill No.' => '',
      'Bill Date' => '',
      'Item Name' => '',
      'Qty.' => '',
      'Item Rate' => '',
      'Gross Amt.' => '',
      'Discount' => '',
      'Tax' => '',
      'Net Amount' => '',
      'Cust.Name' => '',
      'Remarks / Notes' => '',
    ];
    $cleaned_params[] = [
      'Sl. No.' => '',
      'Bill No.' => '',
      'Bill Date' => '',
      'Item Name' => 'REPORT TOTALS',
      'Qty.' => number_format($tot_sold_qty,2,'.',''),
      'CASE No.' => '',
      'Item Rate' => '',
      'Gross Amt.' => number_format($tot_amount,2,'.',''),
      'Discount' => number_format($tot_discount,2,'.',''),
      'Tax' =>  number_format($tot_bill_tax,2,'.',''),
      'Net Amount' => number_format($tot_net_pay,2,'.',''),
      'Cust.Name' => '',
      'Remarks / Notes' => '',
    ];

    return $cleaned_params;
  }

  private function _format_dispatch_register_for_csv($total_records = []) {
    $tot_amount = 0;
    $slno = 0;
    foreach($total_records as $key => $record_details) {
      $slno++;
      $tot_amount += $record_details['netpay'];
      $cleaned_params[$key] = [
        'Sno.' => $slno,
        'Customer Name' => $record_details['customerName'],
        'GST Invoice No.' => $record_details['gstInvoiceNo'],
        'Internal Tracking No.' => $record_details['qbInvoiceNo'],
        'Order / Indent No.' => $record_details['indentNo'],
        'Invoice Date' => date('d/m/Y', strtotime($record_details['invoiceDate'])), 
        'Amount' => number_format($record_details['netpay'],2,'.',''),
        'City Name' => ucwords(strtolower($record_details['cityName'])),
        'State Name' => Utilities::get_location_state_name($record_details['stateID']),
        'Waybill No.' => $record_details['wayBillNo'],
        'LR No.' => $record_details['lrNo'],
        'Carrier Name' => $record_details['transporterName'],
      ];
    }
    $cleaned_params[count($cleaned_params)] = [
      'SNo.' => '' ,
      'Customer Name' => 'T O T A L S',
      'GST Invoice No.' => '',
      'Internal Tracking No.' => '',
      'Order / Indent No.' => '',
      'Invoice Date' => '', 
      'Amount' => number_format($tot_amount,2,'.',''),
      'City Name' => '',
      'State Name' => '',
      'Waybill No.' => '',
      'LR No.' => '',
      'Carrier Name' => '',
    ];
    
    return $cleaned_params;    
  }

  private function _format_sales_by_executive($total_records = []) {
    $tot_taxable_amount = $tot_bill_value = 0;
    $slno = 0;
    foreach($total_records as $key => $record_details) {
      $slno++;
      $tot_taxable_amount += $record_details['taxableAmount'];
      $tot_bill_value += $record_details['billValue'];
      $cleaned_params[$key] = [
        'Sno.' => $slno,
        'Executive Name' => $record_details['buName'],
        'Taxable Amount (in Rs.)' => $record_details['taxableAmount'],
        'Bill Value (in Rs.)' => $record_details['billValue'],
      ];
    }
    $cleaned_params[count($cleaned_params)] = [
      'SNo.' => '' ,
      'Executive Name' => 'T O T A L S',
      'Taxable Amount (in Rs.)' => number_format($tot_taxable_amount,2,'.',''),
      'Bill Value (in Rs.)' => number_format($tot_bill_value,2,'.',''),      
    ];
    
    return $cleaned_params;    
  }

  private function _format_sales_by_city($total_records = []) {
    $tot_taxable_amount = $tot_bill_value = 0;
    $slno = 0;
    $state_ids = Constants::$LOCATION_STATES;    
    foreach($total_records as $key => $record_details) {
      $slno++;
      $city_name = $record_details['cityName'];
      $state_name = (int)$record_details['stateID'] > 0 ? $state_ids[$record_details['stateID']] : '';
      $tot_taxable_amount += $record_details['taxableAmount'];
      $tot_bill_value += $record_details['billValue'];
      $cleaned_params[$key] = [
        'Sno.' => $slno,
        'City Name' => $city_name,
        'State Name' => $state_name,
        'Taxable Amount (in Rs.)' => $record_details['taxableAmount'],
        'Bill Value (in Rs.)' => $record_details['billValue'],
      ];
    }
    $cleaned_params[count($cleaned_params)] = [
      'SNo.' => '' ,
      'City Name' => '',
      'State Name' => 'T O T A L S',
      'Taxable Amount (in Rs.)' => number_format($tot_taxable_amount,2,'.',''),
      'Bill Value (in Rs.)' => number_format($tot_bill_value,2,'.',''),      
    ];
    
    return $cleaned_params;    
  }

  private function _format_sales_by_state($total_records = []) {
    $tot_taxable_amount = $tot_bill_value = 0;
    $slno = 0;
    $state_ids = Constants::$LOCATION_STATES;    
    foreach($total_records as $key => $record_details) {
      $slno++;
      $state_name = (int)$record_details['stateID'] > 0 ? $state_ids[$record_details['stateID']] : '';
      $tot_taxable_amount += $record_details['taxableAmount'];
      $tot_bill_value += $record_details['billValue'];
      $cleaned_params[$key] = [
        'Sno.' => $slno,
        'State Name' => $state_name,
        'Taxable Amount (in Rs.)' => $record_details['taxableAmount'],
        'Bill Value (in Rs.)' => $record_details['billValue'],
      ];
    }
    $cleaned_params[count($cleaned_params)] = [
      'SNo.' => '' ,
      'State Name' => 'T O T A L S',
      'Taxable Amount (in Rs.)' => number_format($tot_taxable_amount,2,'.',''),
      'Bill Value (in Rs.)' => number_format($tot_bill_value,2,'.',''),      
    ];
    
    return $cleaned_params;    
  }

  private function _format_sales_by_agent($total_records = []) {
    $tot_taxable_amount = $tot_bill_value = 0;
    $slno = 0;
    foreach($total_records as $key => $record_details) {
      $slno++;
      $agent_name = $record_details['buName'] !== '' ? $record_details['buName'] : '';
      $tot_taxable_amount += $record_details['taxableAmount'];
      $tot_bill_value += $record_details['billValue'];
      $cleaned_params[$key] = [
        'Sno.' => $slno,
        'Agent Name' => $agent_name,
        'Taxable Amount (in Rs.)' => $record_details['taxableAmount'],
        'Bill Value (in Rs.)' => $record_details['billValue'],
      ];
    }
    $cleaned_params[count($cleaned_params)] = [
      'SNo.' => '' ,
      'Agent Name' => 'T O T A L S',
      'Taxable Amount (in Rs.)' => number_format($tot_taxable_amount,2,'.',''),
      'Bill Value (in Rs.)' => number_format($tot_bill_value,2,'.',''),      
    ];
    
    return $cleaned_params;    
  }

  private function _format_sales_by_brand($total_records = [], $location_with_ids_a=[]) {
    $tot_qty = $tot_amount = 0;
    $slno = 0;

    foreach($total_records as $key => $record_details) {
      $slno++;
      $tot_qty    +=  $record_details['itemQty'];
      $tot_amount +=  $record_details['itemValue'];
      $brand_name = $record_details['mfgName'] !== '' ?  $record_details['mfgName'] : '';
      $location_id = $record_details['locationID'];
      $brand_qty = number_format($record_details['itemQty'], 2, '.', '');
      $brand_value = number_format($record_details['itemValue'], 2, '.', '');      
      $cleaned_params[$key] = [
        'Sno.' => $slno,
        'Brand Name' => $brand_name,
        'Store / Location Name' => $location_with_ids_a[$location_id],
        'Qty.' => $brand_qty,
        'Value (in Rs.)' => $brand_value,
      ];
    }

    $cleaned_params[count($cleaned_params)] = [
      'Sno.' => '',
      'Brand Name' => '',
      'Store / Location Name' => 'T O T A L S',
      'Qty.' => $tot_qty,
      'Value (in Rs.)' => $tot_amount,
    ];
    
    return $cleaned_params;    
  }  

  private function _add_b2b_invoice_header(&$pdf=null, $customer_info=[], $item_widths=[]) {

    $billing_address = $shipping_address = '';

    // dump($customer_info);
    // exit;

    $pdf->SetFont('Arial','B',11);
    $pdf->Ln(-3);
    $pdf->Cell(190,6,'Tax Invoice','',1,'C');

    // $file_download_path = __DIR__.'/../../../../bulkuploads';
    // $pdf->Image("http://bills.local/qrcode.png",150,0,0,0,'PNG');   
    
    $pdf->SetFont('Arial','B',10);
    $pdf->SetTextColor(255,255,255);
    $pdf->Cell(70,6,'GST Invoice No.','LBT',0,'C',true);
    $pdf->Cell(25,6,'Invoice Date','BT',0,'C',true);
    $pdf->Cell(70,6,'Internal Tracking Number','RBT',0,'C',true);
    $pdf->Cell(25,6,'Order No.','RBT',0,'C',true);
    $pdf->SetFillColor(0,0,0);
    $pdf->Ln();
    $pdf->SetTextColor(0,0,0);


    $pdf->SetFont('Arial','',10);
    $pdf->Cell(70,6,$customer_info['custom_invoice_no'],'LRB',0,'C');
    $pdf->Cell(25,6,$customer_info['bill_date'],'RB',0,'C');
    // $pdf->Cell(70,6,$customer_info['bill_no'].' [ '.$customer_info['invoice_code'].' ]','RB',0,'C');
    $pdf->Cell(70,6,$customer_info['bill_no'],'RB',0,'C');
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(25,6,$customer_info['indent_no'],'RB',1,'C');

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(95,6,'Billing Address','LR',0,'L');
    $pdf->Cell(95,6,'Shipping Address','R',0,'L');
    $pdf->Ln();
    $pdf->SetFont('Arial','',9);

    // billing address format.
    if($customer_info['billing']['address'] !== '') {
      $billing_address .= $customer_info['billing']['address'];
    }
    if($customer_info['billing']['pincode'] !== '') {
      $billing_address .= ','.$customer_info['billing']['city_name'].'-'.$customer_info['billing']['pincode'].'.';
    } else {
      $billing_address .= ','.$customer_info['billing']['city_name'];      
    }

    // shipping address format.
    if($customer_info['shipping']['address'] !== '') {
      $shipping_address .= $customer_info['shipping']['address'];
    }
    if($customer_info['shipping']['pincode'] !== '') {
      $shipping_address .= ','.$customer_info['shipping']['city_name'].'-'.$customer_info['shipping']['pincode'].'.';
    } else {
      $shipping_address .= ','.$customer_info['shipping']['city_name'];
    }

    // check strlen and add extra spaces if required.
    if(strlen($shipping_address) !== strlen($billing_address)) {
      $billing_address_length = strlen($billing_address);
      $shipping_address_length = strlen($shipping_address);
      if($billing_address_length < $shipping_address_length) {
        $diff_length = $shipping_address_length - $billing_address_length;
        $extra_spaces = str_repeat(' ', $diff_length);
        $billing_address .= $extra_spaces;
      } elseif($shipping_address_length < $billing_address_length) {
        $diff_length = $billing_address_length - $shipping_address_length;
        $extra_spaces = str_repeat(' ', $diff_length);
        $shipping_address .= $extra_spaces;
      }
    }

    // dump($billing_address, $shipping_address);
    // exit;

    $payment_method_name = Constants::$PAYMENT_METHODS_RC[$customer_info['payment_method']];

    $x = $pdf->getX();
    $y = $pdf->getY();
    $pdf->Multicell(95,4,$customer_info['billing']['customer_name'],'LR','L');
    $pdf->setXY($x+95, $y);
    $pdf->Multicell(95,4,$customer_info['shipping']['customer_name'],'LR','L');

    if($customer_info['billing']['state_id'] > 0) {
      $billing_state_name = $customer_info['billing']['state_name'].' [ '.$customer_info['billing']['state_id'].' ]';
    } else {
      $billing_state_name = $customer_info['billing']['state_name'].' [ ]';
    }

    if($customer_info['shipping']['state_id']>0) {
      $shipping_state_name = $customer_info['shipping']['state_name'].' [ '.$customer_info['shipping']['state_id'].' ]';
    } else {
      $shipping_state_name = $customer_info['shipping']['state_name'].' [ ]';
    }
    $x = $pdf->getX();
    $y = $pdf->getY();
    $pdf->Multicell(95,4,$billing_address,'L','L');
    $pdf->setXY($x+95, $y);
    $pdf->Multicell(95,4,$shipping_address,'LR','L');

    $pdf->Cell(95,6,'GST State: '.$billing_state_name,'LR',0,'L');
    $pdf->Cell(95,6,'GST State: '.$shipping_state_name,'R',0,'L');
    $pdf->Ln(4.5);

    $pdf->Cell(95,5,'GSTIN: '.$customer_info['billing']['gst_no'],'LRB',0,'L');
    $pdf->Cell(95,5,'GSTIN: '.$customer_info['shipping']['gst_no'],'RB',0,'L');
    $pdf->Ln();

    // section or location information
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(50,5,'Section','LB',0,'C');
    $pdf->Cell(60,5,'Excecutive','B',0,'C');
    $pdf->Cell(40,5,'Scheme','B',0,'C');
    $pdf->Cell(40,5,'Payment terms','BR',0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(50,5,$customer_info['location_name'],'LB',0,'C');
    $pdf->Cell(60,5,$customer_info['executive_name'],'B',0,'C');
    $pdf->Cell(40,5,$customer_info['promo_code'],'B',0,'C');
    $pdf->Cell(40,5,$payment_method_name,'BR',0,'C');
    $pdf->Ln();

    // transport information
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(80,5,'Transporter Name','LB',0,'C');
    $pdf->Cell(30,5,'L.R. No.','',0,'C');
    $pdf->Cell(20,5,'L.R. Date','',0,'C');
    $pdf->Cell(30,5,'Challan No.','',0,'C');
    $pdf->Cell(30,5,'eWay Bill No.','R',0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',8);

    $pdf->Cell(80,5,$customer_info['transport_details']['transporter_name'],'LB',0,'C');
    $pdf->Cell(30,5,$customer_info['transport_details']['lr_no'],'TB',0,'C');
    if(
        $customer_info['transport_details']['lr_date'] !== '0' && 
        strlen($customer_info['transport_details']['lr_date']) > 0 &&
        $customer_info['transport_details']['lr_date'] !== ''
      ) {
      $pdf->Cell(20,5,date("d-M-Y",strtotime($customer_info['transport_details']['lr_date'])),'TB',0,'C');
    } else {
      $pdf->Cell(20,5,'','TB',0,'C');
    }
    $pdf->Cell(30,5,$customer_info['transport_details']['challan_no'],'TB',0,'C');
    $pdf->Cell(30,5,$customer_info['transport_details']['way_bill_no'],'TRB',0,'C');
    $pdf->Ln();


    $pdf->SetFont('Arial','B',8);
    $pdf->Cell($item_widths[0],  6,'Sno.','LRB',0,'C');
    $pdf->Cell($item_widths[1],  6,'Item Name','RB',0,'C');
    $pdf->Cell($item_widths[2],  6,'HSN/SAC','RB',0,'C');
    $pdf->Cell($item_widths[4],  6,'UOM','RB',0,'C');
    $pdf->Cell($item_widths[3],  6,'Rate (Rs.)','RB',0,'C');        
    $pdf->Cell($item_widths[5],  6,'Qty.','RB',0,'C');
    $pdf->Cell($item_widths[6],  6,'Amount(Rs.)','RB',0,'C');
    $pdf->Cell($item_widths[7],  6,'Disc. (Rs.)','RB',0,'C');
    $pdf->Cell($item_widths[8],  6,'Taxable (Rs.)','RB',0,'C');
    $pdf->Ln();
  }

  private function _add_einvoice_header(&$pdf=null, $customer_info=[], $item_widths=[], $loc_address=[], $gst_details=[], $brand_name='') {

    $billing_address = $shipping_address = '';

    // dump($customer_info);
    // exit;
    // $file_download_path = __DIR__.'/../../../../bulkuploads';

    $address_string = $loc_address['address1'].', '.$loc_address['address2'].', '.$loc_address['address3'];
    
    if($customer_info['billing']['address'] !== '') {
      $billing_address .= $customer_info['billing']['address'];
    }
    if($customer_info['billing']['pincode'] !== '') {
      $billing_address .= ','.$customer_info['billing']['city_name'].'-'.$customer_info['billing']['pincode'].'.';
    } else {
      $billing_address .= ','.$customer_info['billing']['city_name'];      
    }

    if($customer_info['shipping']['address'] !== '') {
      $shipping_address .= $customer_info['shipping']['address'];
    }
    if($customer_info['shipping']['pincode'] !== '') {
      $shipping_address .= ','.$customer_info['shipping']['city_name'].'-'.$customer_info['shipping']['pincode'].'.';
    } else {
      $shipping_address .= ','.$customer_info['shipping']['city_name'];
    }

    if($gst_details['gst_doc_no'] !== '') {
      $document_no = $gst_details['gst_doc_no'];
    } elseif($customer_info['custom_invoice_no'] !== '') {
      $document_no = $customer_info['custom_invoice_no'];
    } else {
      $document_no = $customer_info['bill_no'];
    }

    $payment_method_name = Constants::$PAYMENT_METHODS_RC[$customer_info['payment_method']];

    // get einvoice details for QRCode generation.
    $einvoice_details = $this->einvoice->get_einvoice_details($loc_address['gst_no'], $document_no);
    if($einvoice_details['status']) {
      $qrdata = $einvoice_details['response']['signedQrCode'];
      $irn = $einvoice_details['response']['irnNo'];
    } else {
      die("<h2>Invalid eInvoice Details / eInvoice not found...</h2>");
    }

    $promo_code_discount = $customer_info['promo_code_discount'] > 0 ? 
                           number_format($customer_info['promo_code_discount'],0,'.','').'%' :
                           '';

    // supplier address
    $image_url = Utilities::generate_QRcode_for_einvoice($qrdata, $irn);
    // echo $image_url;
    // exit;
    $pdf->Image($image_url,0,0,0,0,'PNG');   

    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,5,'TAX INVOICE',0,1,'R');
    $pdf->SetFont('Arial','I',8);
    $pdf->Cell(0,3,'IRN: '.$gst_details['irn'],0,1,'R');
    $pdf->setX(60);
    $pdf->Cell(150,4,'Ack No - '.$gst_details['gst_ack_no'].' :: Ack Date - '.
                    date('d/M/Y h:ia', strtotime($gst_details['gst_ack_date'])),'B',1,'R');

    $pdf->SetFont('Arial','B',12);
    $pdf->Ln(2);
    $pdf->setX(60);
    $pdf->Cell(0,4,$loc_address['store_name'],0,1,'L');
    $pdf->SetFont('Arial','I',8);
    $pdf->setX(60);
    $pdf->Cell(0,4,'GSTN: '.$loc_address['gst_no'],0,1,'L');
    $pdf->SetFont('Arial','',9);
    $pdf->setX(60);
    $pdf->Cell(0,4,$address_string,0,1,'L');

    // client address
    $pdf->Ln(1.5);
    $pdf->SetFont('Arial','U',9);
    $pdf->setX(60);
    $pdf->Cell(0,4,'Buyer - Bill to',0,1,'L');
    $pdf->Ln(1);
    $pdf->SetFont('Arial','B',10);
    $pdf->setX(60);
    $pdf->Cell(150,4,$customer_info['billing']['customer_name'],0,1,'L');
    $pdf->SetFont('Arial','I',8);
    $pdf->setX(60);
    $pdf->Cell(0,4,'GSTN: '.$customer_info['billing']['gst_no'],0,1,'L');
    $pdf->setX(60);
    $pdf->Cell(150,3,$billing_address,0,1,'L');

    $pdf->Ln(1.5);
    $pdf->SetFont('Arial','U',9);
    $pdf->setX(60);
    $pdf->Cell(150,4,'Buyer - Ship to',0,1,'L');
    $pdf->Ln(0.9);
    $pdf->SetFont('Arial','B',10);
    $pdf->setX(60);
    $pdf->Cell(150,4,$customer_info['shipping']['customer_name'],0,1,'L');
    $pdf->SetFont('Arial','I',8);
    $pdf->setX(60);
    $pdf->Cell(0,4,'GSTN: '.$customer_info['shipping']['gst_no'],0,1,'L');
    $pdf->setX(60);
    $pdf->Cell(150,4,$shipping_address,0,1,'L');    

    $pdf->SetFont('Arial','I',9);    
    $pdf->Cell(25,6,'Document No.','LBTR',0,'R');
    $pdf->SetFont('Arial','B',11);    
    $pdf->Cell(40,6,$document_no,'BTR',0,'C');
    $pdf->SetFont('Arial','I',9);    
    $pdf->Cell(25,6,'Document Date','BTR',0,'R');
    $pdf->SetFont('Arial','B',11);    
    $pdf->Cell(25,6,$customer_info['bill_date'],'BTR',0,'C');
    $pdf->SetFont('Arial','I',9);    
    $pdf->Cell(10,6,'Brand','BTR',0,'R');
    $pdf->SetFont('Arial','B',10);    
    $pdf->Cell(37,6,$brand_name,'BTR',0,'C');
    $pdf->SetFont('Arial','I',9);    
    $pdf->Cell(20,6,'Discount(%)','BTR',0,'R');
    $pdf->SetFont('Arial','B',11);    
    $pdf->Cell(20,6,$promo_code_discount,'BTR',0,'C');
    $pdf->Ln(6);

    // section or location information
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(55,5,'Section / Division','LBT',0,'C');
    $pdf->Cell(63,5,'Excecutive Name','B',0,'C');
    $pdf->Cell(43,5,'Scheme','BT',0,'C');
    $pdf->Cell(41,5,'Payment Terms','BRT',0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(53,5,$customer_info['location_name'],'LB',0,'C');
    $pdf->Cell(63,5,$customer_info['executive_name'],'B',0,'C');
    $pdf->Cell(43,5,$customer_info['campaign_name'],'B',0,'C');
    $pdf->Cell(43,5,$payment_method_name,'BR',0,'C');
    $pdf->Ln();

    // order no and information
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(55,5,'Buyer Order No.','LB',0,'C');
    $pdf->Cell(63,5,'Agent Name','B',0,'C');
    $pdf->Cell(43,5,'eWayBill No.','B',0,'C');
    $pdf->Cell(41,5,'eWayBill Date','BR',0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(53,5,$customer_info['indent_no'],'LB',0,'C');
    $pdf->Cell(63,5,'','B',0,'C');
    $pdf->Cell(43,5,$customer_info['transport_details']['way_bill_no'],'B',0,'C');
    $pdf->Cell(43,5,$customer_info['transport_details']['way_bill_date'],'BR',0,'C');
    $pdf->Ln();    

    // transport information
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(100,5,'Dispatched Through','LBR',0,'C');
    $pdf->Cell(70,5,'Dispatch Doc. No.','R',0,'C');
    $pdf->Cell(32,5,'Dispatch Doc. Dt.','R',0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','',8);

    $pdf->Cell(100,5,$customer_info['transport_details']['transporter_name'],'LB',0,'C');
    $pdf->Cell(50,5,$customer_info['transport_details']['lr_no'],'TB',0,'C');
    if($customer_info['transport_details']['lr_date'] !== '0' &&
       $customer_info['transport_details']['lr_date'] !== '1970-01-01' && 
       $customer_info['transport_details']['lr_date'] !== '0000-00-00' && 
       strlen($customer_info['transport_details']['lr_date']) > 0) {
      $pdf->Cell(22,5,date("d-M-Y",strtotime($customer_info['transport_details']['lr_date'])),'TB',0,'C');
    } else {
      $pdf->Cell(22,5,'','TB',0,'C');
    }
    $pdf->Cell(30,5,$customer_info['transport_details']['way_bill_no'],'TRB',0,'C');
    $pdf->Ln();    

    $pdf->SetFont('Arial','B',8);
    $pdf->Cell($item_widths[0],  6,'Sno.','LRB',0,'C');
    $pdf->Cell($item_widths[1],  6,'Description','RB',0,'C');
    $pdf->Cell($item_widths[2],  6,'HSN/SAC','RB',0,'C');
    $pdf->Cell(             21,  6,'Case/Box No.','RB',0,'C');
    $pdf->Cell($item_widths[5],  6,'Qty.','RB',0,'C');
    $pdf->Cell($item_widths[3],  6,'Rate (Rs.)','RB',0,'C');        
    $pdf->Cell($item_widths[4],  6,'Unit','RB',0,'C');
    $pdf->Cell($item_widths[6],  6,'Amount (Rs.)','RB',0,'C');
    $pdf->Cell($item_widths[7],  6,'Discount (Rs.)','RB',0,'C');
    $pdf->Cell($item_widths[8],  6,'Taxable (Rs.)','RB',0,'C');
    $pdf->Ln();
  }  

}
