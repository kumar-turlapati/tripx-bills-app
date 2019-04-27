<?php 

namespace ClothingRm\Reports\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\PDF;
use Atawa\Flash;

use ClothingRm\Sales\Model\Sales;
use Campaigns\Model\Campaigns;
use BusinessUsers\Model\BusinessUsers;

class ReportsController
{

  protected $views_path;

  public function __construct() {
   $this->views_path = __DIR__.'/../Views/';
   $this->camp_model = new Campaigns;
   $this->bu_model = new BusinessUsers;  
   $this->flash = new Flash;
  }

  public function printSalesBillSmall(Request $request) {

    # inititate Sales Model
    $sales = new \ClothingRm\Sales\Model\Sales;
    $user_model = new \User\Model\User;

    $billNo = Utilities::clean_string($request->get('billNo'));
    $slno = 0;

    # get user details
    if(isset($_SESSION['uname']) && $_SESSION['uname'] !== '') {
      $operator_name = substr($_SESSION['uname'],0,12);
    } else {
      $operator_name = '';
    }

    $params['billNo'] = $billNo;
    $print_date_time = date("d-M-Y h:ia");

    $sales_response = $sales->get_sales_details($billNo,false);
    $status = $sales_response['status'];
    if($status) {
      $sale_details = $sales_response['saleDetails'];
      $sale_item_details = $sale_details['itemDetails'];
      unset($sale_details['itemDetails']);
    } else {
      die($this->_get_print_error_message());
    }

    $template_vars = array(
      'sale_details' => $sale_details,
      'sale_item_details' => $sale_item_details,
    );
    $controller_vars = array(

    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('print-sale-bill-small', $template_vars), $controller_vars);
  }

  public function printSalesBill(Request $request) {

    // inititate Sales Model
    $sales = new \ClothingRm\Sales\Model\Sales;
    $user_model = new \User\Model\User;

  	$billNo = Utilities::clean_string($request->get('billNo'));
    $slno = 0;

    // get user details
    if(isset($_SESSION['uname']) && $_SESSION['uname'] !== '') {
      $operator_name = substr($_SESSION['uname'],0,12);
    } else {
      $operator_name = '';
    }

    $params['billNo'] = $billNo;
    $print_date_time = date("d-M-Y h:ia");

    $sales_response = $sales->get_sales_details($billNo,false);
    // dump($sales_response);
    // exit;
    $status = $sales_response['status'];
    if($status) {
      $sale_details = $sales_response['saleDetails'];
      $sale_item_details = $sale_details['itemDetails'];
      unset($sale_details['itemDetails']);
    } else {
      die($this->_get_print_error_message());
    }
    $bill_no = $sale_details['billNo'];
    $bill_date = date('d-M-Y',strtotime($sale_details['invoiceDate']));
    $bill_time = date('h:ia',strtotime($sale_details['createdTime']));
    $payment_method = Constants::$PAYMENT_METHODS_RC[$sale_details['paymentMethod']];
    $payment_method_num = (int)$sale_details['paymentMethod'];
    $tmp_cust_name = $sale_details['tmpCustName'];
    $customer_name  =  $sale_details['customerName'] !== '' ? substr(strtoupper($sale_details['customerName']),0,30) : '';
    $card_no = $sale_details['cardNo'] > 0 ? '* ****'.$sale_details['cardNo'] : '';
    $auth_code = $sale_details['authCode'] > 0 ? $sale_details['authCode'] : '****';

    $cn_no =  $sale_details['cnNo'];
    $referral_no = $sale_details['refCardNo'];
    $promo_code =  $sale_details['promoCode'];
    if($customer_name === '') {
      $customer_name = $tmp_cust_name;
    }

    // dump($sale_details, $_SESSION);
    // exit;

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

    $loc_address = [
      'address1' => $business_add1,
      'address2' => $business_add2,
      'address3' => $business_add3,
      'phones' => $phones,
      'store_name' => $business_name,
      'gst_no' => $gst_no,
    ];

    // $gst_no = '';

    // start PDF printing.
    $pdf = PDF::getInstance(true, $loc_address);
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');

    // Print Bill Information.
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0, $gst_no !== '' ? 'Tax Invoice' : 'Bill of Sale','',1,'C');
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(4);

    // Initialize widths
    $invoice_info_widths = [40,30,30,60,30];
    $customer_info_widths = [95,95];
    $item_widths = [10,60,22,22,16,20,20,20];
    $final_tot_width = [26,26,26,25,27,20];

    // second row
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln();
    $pdf->Cell($invoice_info_widths[0],6,'Invoice No.','LRTB',0,'C');
    $pdf->Cell($invoice_info_widths[1],6,'Invoice Date','RTB',0,'C');
    $pdf->Cell($invoice_info_widths[2],6,'Payment Mode','RTB',0,'C');
    $pdf->Cell($invoice_info_widths[3],6,'Customer Name','RTB',0,'C');    
    $pdf->Cell($invoice_info_widths[4],6,'Promo Code','RTB',1,'C');
    
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell($invoice_info_widths[0],6,$bill_no,'LRB',0,'C');
    $pdf->SetFont('Arial','',9);
    $pdf->Cell($invoice_info_widths[1],6,$bill_date,'RB',0,'C');
    $pdf->Cell($invoice_info_widths[2],6,$payment_method,'RB',0,'C');
    $pdf->Cell($invoice_info_widths[3],6,$customer_name,'RB',0,'C');
    $pdf->Cell($invoice_info_widths[4],6,$promo_code,'RB',1,'C');
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($item_widths[0],6,'Sno.','LR',0,'C');
    $pdf->Cell($item_widths[1],6,'Product Name','R',0,'C');
    $pdf->Cell($item_widths[2],6,'HSN/SAC','R',0,'C');
    $pdf->Cell($item_widths[3],6,'Item Rate','R',0,'C');
    $pdf->Cell($item_widths[4],6,'Qty.','R',0,'C');  
    $pdf->Cell($item_widths[5],6,'Amount','R',0,'C');
    $pdf->Cell($item_widths[6],6,'Discount','R',0,'C');
    $pdf->Cell($item_widths[7],6, $gst_no !== '' ? 'Taxable' : 'Gross Amt.','R',1,'C');
    $pdf->SetFont('Arial','',9);

    $tot_bill_value = $tot_discount = $tot_taxable = $tot_items_qty = 0;
    $taxable_values = $tax_amounts = $taxable_gst_value = [];

    foreach($sale_item_details as $item_details) {
      $slno++;
      $tax_percent = $item_details['taxPercent'];

      $amount = round($item_details['itemQty']*$item_details['mrp'], 2);
      $discount = $item_details['discountAmount'];

      $base_price = $item_details['itemQty'] * $item_details['itemRate'];
      $taxable = round($amount - $discount, 2);
      $tax_value = ($taxable * $tax_percent / 100);

      $tax_amount = $item_details['cgstAmount'] + $item_details['sgstAmount'];

      $cgst_percent = $sgst_percent = round($tax_percent/2,2);
      $cgst_value = $sgst_value = round($tax_value/2,2);

      $tot_bill_value += $amount;
      $tot_discount += $discount;
      $tot_taxable += $taxable;
      $tot_items_qty += $item_details['itemQty'];

      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,substr($item_details['itemName'],0,20),'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,$item_details['hsnSacCode'],'RTB',0,'L');
      $pdf->Cell($item_widths[3],6,$item_details['mrp'],'RTB',0,'R');
      $pdf->Cell($item_widths[4],6,$item_details['itemQty'],'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,number_format($amount,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[6],6,number_format($item_details['discountAmount'],2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[7],6,number_format($taxable,2,'.',''),'RTB',1,'R');

      if(isset($taxable_values[$tax_percent])) {
        $taxable = $taxable_values[$tax_percent] + ($base_price);
        $gst_value = $taxable_gst_value[$tax_percent] + $tax_amount;

        $taxable_values[$tax_percent] = $taxable;
        $taxable_gst_value[$tax_percent] = $gst_value;
      } else {
        $taxable_values[$tax_percent] = ($base_price);
        $taxable_gst_value[$tax_percent] = $tax_amount;
      }
    }

    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($item_widths[6]+$item_widths[7],6,'','L',0,'C');
    $pdf->Cell($final_tot_width[0],6,'Amount (Rs.)','LR',0,'C');
    if($gst_no !== '') {
      $pdf->Cell($final_tot_width[1],6,'Discount (Rs.)','R',0,'C');
      $pdf->Cell($final_tot_width[2],6,'Taxable (Rs.)','R',0,'C');  
    } else {
      $pdf->Cell($final_tot_width[1]+$final_tot_width[1],6,'Discount (Rs.)','R',0,'C');
    }

    $pdf->Cell($final_tot_width[3],6,'Round Off','R',0,'C');
    $pdf->Cell($final_tot_width[5],6,'Total Qty.','R',0,'C');
    $pdf->Cell($final_tot_width[4],6,'Net Pay (Rs.)','R',1,'C');
    $pdf->SetFont('Arial','',9);

    $pdf->Cell($item_widths[6]+$item_widths[7],6,'','L',0,'C');
    $pdf->Cell($final_tot_width[0],6,number_format($tot_bill_value,2,'.',''),'LRTB',0,'R');

    if($gst_no !== '') {
      $pdf->Cell($final_tot_width[1],6,number_format($tot_discount,2,'.',''),'RTB',0,'R');
      $pdf->Cell($final_tot_width[2],6,number_format($tot_taxable,2,'.',''),'RTB',0,'R');
    } else {
      $pdf->Cell($final_tot_width[2] + $final_tot_width[1],6,number_format($tot_taxable,2,'.',''),'RTB',0,'R');
    }

    $pdf->Cell($final_tot_width[3],6,number_format($sale_details['roundOff'],2,'.',''),'RTB',0,'R');
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($final_tot_width[5],6,number_format($tot_items_qty,2),'RTB',0,'R');
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell($final_tot_width[4],6,number_format($sale_details['netPay'],2,'.',''),'RTB',1,'R');
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(190,6,'[ In words: '.Utilities::get_indian_currency($sale_details['netPay']).' ]','LRB',1,'R');

    if($gst_no !== '') {
      $taxes = array_keys($taxable_values);

      $pdf->SetFont('Arial','B',10);
      $pdf->Cell(95,6,'GST Summary','LRB',0,'C');
      $pdf->Cell(95,6,'Payment Details','LRB',1,'C');

      $pdf->SetFont('Arial','B',9);
      $pdf->Cell(15,6,'GST','LB',0,'C');    
      $pdf->Cell(21,6,'Taxable (Rs.)','LB',0,'C');    
      $pdf->Cell(19,6,'IGST (Rs.)','LB',0,'C');    
      $pdf->Cell(20,6,'CGST (Rs.)','LB',0,'C');
      $pdf->Cell(20,6,'SGST (Rs.)','LRB',0,'C');

      $pdf->Cell(23,6,'Paid through','LB',0,'C');    
      $pdf->Cell(47,6,'Details','B',0,'C');    
      $pdf->Cell(25,6,'Amount (Rs.)','RB',1,'C');

      $pdf->SetFont('Arial','',8);      

      // Row - 1

      // for 5%
      if(isset($taxable_values['5.00'])) {
        $five_percent_taxable = $taxable_values['5.00'];
        $cgst_amount = $sgst_amount = round($taxable_gst_value['5.00']/2,2);
        $igst_amount = 0;
      } else {
        $five_percent_taxable = $igst_amount = $sgst_amount = $cgst_amount = 0;
      }
      $pdf->Cell(15,6,'5.00 %','LB',0,'R');    
      $pdf->Cell(21,6,$five_percent_taxable > 0 ? number_format($five_percent_taxable, 2, '.', '') : '','LB',0,'R');
      $pdf->Cell(19,6,$igst_amount > 0 ? number_format($igst_amount, 2, '.', '') : '','LB',0,'R');    
      $pdf->Cell(20,6,$cgst_amount > 0 ? number_format($cgst_amount, 2, '.', '') : '','LB',0,'R');
      $pdf->Cell(20,6,$sgst_amount > 0 ? number_format($sgst_amount, 2, '.', '') : '','LRB',0,'R');      

      $pdf->Cell(23,6,'By Cash','',0,'R');
      if($payment_method_num === 0) {
        $cash_amount = number_format($sale_details['netPay'],2,'.','');
      } elseif($payment_method_num === 2 && $sale_details['netPayCash']>0) {
        $cash_amount = number_format($sale_details['netPayCash'],2,'.','');
      } else {
        $cash_amount = '';
      }
      $pdf->Cell(47,6,'','',0,'C');
      $pdf->Cell(25,6,$cash_amount,'R',1,'C');         

      // for 12%
      if(isset($taxable_values['12.00'])) {
        $twelve_percent_taxable = $taxable_values['12.00'];
        $cgst_amount = $sgst_amount = round($taxable_gst_value['12.00']/2,2);
        $igst_amount = 0;
      } else {
        $twelve_percent_taxable = $cgst_amount = $igst_amount = $sgst_amount = 0;
      }
      $pdf->Cell(15,6,'12.00 %','LB',0,'R');    
      $pdf->Cell(21,6, $twelve_percent_taxable > 0 ? number_format($twelve_percent_taxable, 2, '.', '') : '','LB',0,'R');
      $pdf->Cell(19,6,$igst_amount > 0 ? number_format($igst_amount, 2, '.', '') : '','LB',0,'R');    
      $pdf->Cell(20,6,$cgst_amount > 0 ? number_format($cgst_amount, 2, '.', '') : '','LB',0,'R');
      $pdf->Cell(20,6,$sgst_amount > 0 ? number_format($sgst_amount, 2, '.', '') : '','LRB',0,'R');      

      $pdf->Cell(23,6,'By Card','',0,'R'); 
      if($payment_method_num === 1) {
        $card_amount = number_format($sale_details['netPay'],2,'.','');
      } elseif($payment_method_num === 2 && $sale_details['netPayCard']>0) {
        $card_amount = number_format($sale_details['netPayCard'],2,'.','');
      } else {
        $card_amount = '';
      }
      $pdf->Cell(47,6,$card_amount !== '' ? $card_no.', Appr.Code: '.$auth_code : '','',0,'C');    
      $pdf->Cell(25,6,$card_amount,'R',1,'C');

      // for 18%
      if(isset($taxable_values['18.00'])) {
        $eighteen_percent_taxable = isset($taxable_values['18.00']) ?  $taxable_values['18.00'] : '';
        $cgst_amount = $sgst_amount = round($taxable_gst_value['18.00']/2,2);
        $igst_amount = 0;
      } else {
        $eighteen_percent_taxable = $igst_amount = $cgst_amount = $sgst_amount = 0;
      }

      $pdf->Cell(15,6,'18.00 %','LB',0,'R');    
      $pdf->Cell(21,6, $eighteen_percent_taxable > 0 ? number_format($eighteen_percent_taxable, 2, '.', '') : '','LB',0,'R');
      $pdf->Cell(19,6,$igst_amount > 0 ? number_format($igst_amount, 2, '.', '') : '','LB',0,'R');    
      $pdf->Cell(20,6,$cgst_amount > 0 ? number_format($cgst_amount, 2, '.', '') : '','LB',0,'R');
      $pdf->Cell(20,6,$sgst_amount > 0 ? number_format($sgst_amount, 2, '.', '') : '','LRB',0,'R');      

      $pdf->Cell(23,6,'By CrditVoc','',0,'R');
      if($payment_method_num === 2 && $sale_details['netPayCn']>0) {
        $cv_amount = number_format($sale_details['netPayCn'],2,'.','');
      } else {
        $cv_amount = '';
      }      
      $pdf->Cell(47,6,$cv_amount !== '' ? 'CNN:'.$cn_no : '','',0,'C');
      $pdf->Cell(25,6,$cv_amount,'R',1,'C');         

      // for 28%
      if(isset($taxable_values['28.00'])) {
        $twenty_eight_percent_taxable = $taxable_values['28.00'];
        $cgst_amount = $sgst_amount = round($taxable_gst_value['28.00']/2,2);
        $igst_amount = 0;
      } else {
        $twenty_eight_percent_taxable = $igst_amount = $cgst_amount = $sgst_amount = 0;
      }

      $pdf->Cell(15,6,'28.00 %','LB',0,'R');    
      $pdf->Cell(21,6, $twenty_eight_percent_taxable > 0 ? number_format($twenty_eight_percent_taxable, 2, '.', '') : '','LB',0,'R');
      $pdf->Cell(19,6,$igst_amount > 0 ? number_format($igst_amount, 2, '.', '') : '','LB',0,'R');    
      $pdf->Cell(20,6,$cgst_amount > 0 ? number_format($cgst_amount, 2, '.', '') : '','LB',0,'R');
      $pdf->Cell(20,6,$sgst_amount > 0 ? number_format($sgst_amount, 2, '.', '') : '','LRB',0,'R');      

      $pdf->Cell(23,6,'By Credit','B',0,'R');    
      $pdf->Cell(47,6,'','B',0,'C');    
      $pdf->Cell(25,6,'','RB',1,'C');
    } else {
      
      $pdf->SetFont('Arial','B',10);
      $pdf->Cell(190,6,'Payment Details','LRB',1,'C');

      $pdf->SetFont('Arial','B',9);
      $pdf->Cell(23,6,'Paid through','LB',0,'C');    
      $pdf->Cell(47,6,'Details','B',0,'C');    
      $pdf->Cell(25,6,'Amount (Rs.)','B',0,'C');
      $pdf->Cell(95,6,'','RB',1,'C');
      $pdf->SetFont('Arial','',8);      

      $pdf->Cell(23,6,'By Cash','L',0,'R');
      if($payment_method_num === 0) {
        $cash_amount = number_format($sale_details['netPay'],2,'.','');
      } elseif($payment_method_num === 2 && $sale_details['netPayCash']>0) {
        $cash_amount = number_format($sale_details['netPayCash'],2,'.','');
      } else {
        $cash_amount = '';
      }
      $pdf->Cell(47,6,'','',0,'C');
      $pdf->Cell(25,6,$cash_amount,'',0,'C');
      $pdf->Cell(95,6,'','R',1,'C');

      $pdf->Cell(23,6,'By Card','L',0,'R'); 
      if($payment_method_num === 1) {
        $card_amount = number_format($sale_details['netPay'],2,'.','');
      } elseif($payment_method_num === 2 && $sale_details['netPayCard']>0) {
        $card_amount = number_format($sale_details['netPayCard'],2,'.','');
      } else {
        $card_amount = '';
      }
      $pdf->Cell(47,6,$card_amount !== '' ? $card_no.', Appr.Code: '.$auth_code : '','',0,'C');    
      $pdf->Cell(25,6,$card_amount,'',0,'C');
      $pdf->Cell(95,6,'','R',1,'C');

      $pdf->Cell(23,6,'By CrditVoc','L',0,'R');
      if($payment_method_num === 2 && $sale_details['netPayCn']>0) {
        $cv_amount = number_format($sale_details['netPayCn'],2,'.','');
      } else {
        $cv_amount = '';
      }      
      $pdf->Cell(47,6,$cv_amount !== '' ? 'CNN:'.$cn_no : '','',0,'C');
      $pdf->Cell(25,6,$cv_amount,'',0,'C');         
      $pdf->Cell(95,6,'','R',1,'C');

      $pdf->Cell(23,6,'By Credit','L',0,'R');    
      $pdf->Cell(47,6,'','',0,'C');    
      $pdf->Cell(25,6,'','',0,'C');
      $pdf->Cell(95,6,'','R',1,'C');
    }

    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(190,6,'Terms & Conditions','LRBT',1,'C');
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(190,6,'1) NO EXCHANGE. NO RETURN.','LRB',0,'L');

    $pdf->Output();
  }

  // report options for each report.
  public function reportOptions(Request $request) {
    $report_name = Utilities::clean_string($request->get('reportName'));
    if(count($request->request->all())>0) {
      $request_params = $request->request->all();
      $report_url = $request_params['reportHook'];
      unset($request_params['reportHook']);

      $query_params = http_build_query($request_params);
      $redirect_url = $report_url.'?'.$query_params;
      Utilities::redirect($redirect_url);
    } else {
      switch($report_name) {
        case 'sales-register':
          $template_name = 'template-11';
          $locations_a = ['' => 'All Stores'] + Utilities::get_client_locations();                
          $template_vars = array(
            'title' => 'Sales Register',
            'formAction' => '/report-options/sales-register',
            'reportHook' => '/sales-register',
            'location_codes' => $locations_a,
          );
          $controller_vars = array(
            'page_title' => 'Sales Register',
            'icon_name' => 'fa fa-inr',
          );
          break;
        case 'sales-return-register':
          $template_name = 'template-1';
          $locations_a = ['' => 'All Stores'] + Utilities::get_client_locations();                
          $template_vars = array(
            'title' => 'Sales Return Register',
            'formAction' => '/report-options/sales-return-register',
            'reportHook' => '/sales-return-register',
            'location_codes' => $locations_a,
          );
          $controller_vars = array(
            'page_title' => 'Sales Return Register',
            'icon_name' => 'fa fa-inr',
          );
          break;
        case 'grn-register':
          $template_name = 'template-1';
          $template_vars = array(
            'title' => 'GRN Register',
            'formAction' => '/report-options/grn-register',
            'reportHook' => '/grn-register',
          );
          $controller_vars = array(
            'page_title' => 'GRN Register',
            'icon_name' => 'fa fa-laptop',
          );
          break;
        case 'sales-summary-by-month':
          $template_name = 'template-2';
          $template_vars = array(
            'title' => 'Sales Summary - Monthly',
            'formAction' => '/report-options/sales-summary-by-month',
            'reportHook' => '/sales-summary-by-month',
          );
          $controller_vars = array(
            'page_title' => 'Sales Summary - Daywise',
            'icon_name' => 'fa fa-inr',
          );
          break;
        case 'day-sales-report':
          $template_name = 'template-3';
          $template_vars = array(
            'title' => 'Sales Summary - Day',
            'formAction' => '/report-options/day-sales-report',
            'reportHook' => '/day-sales-report',
          );
          $controller_vars = array(
            'page_title' => 'Sales Summary - Day',
            'icon_name' => 'fa fa-inr',
          );            
          break;
        case 'sales-summary-patient':
          $template_name = 'template-4';
          $template_vars = array(
            'title' => 'Sales Summary - By Patient',
            'formAction' => '/report-options/sales-summary-patient',
            'reportHook' => '/sales-summary-patient',
            'patient_types' => array(''=>'Choose')+Constants::$PATIENT_TYPES,
          );
          $controller_vars = array(
            'page_title' => 'Patient Sales Summary',
            'icon_name' => 'fa fa-inr',
          );            
          break;
        case 'stock-report':
          $template_name = 'template-12';
          $template_vars = array(
            'title' => 'Stock Report',
            'formAction' => '/report-options/stock-report',
            'reportHook' => '/stock-report',
            'dropDownlabel' => 'Show',
            'location_codes' => ['' => 'Store name'] + Utilities::get_client_locations(),
          );
          $controller_vars = array(
            'page_title' => 'Stock Report',
            'icon_name' => 'fa fa-laptop',
          );            
          break;
        case 'stock-report-new':
          $template_name = 'template-5';
          $filter_types = array(
            ''=>'Choose', 'all' => 'All', 'neg' => 'Negative'
          );
          $template_vars = array(
            'title' => 'Stock Report',
            'formAction' => '/report-options/stock-report-new',
            'reportHook' => '/stock-report-new',
            'dropDownlabel' => 'Show',
            'filter_types' => $filter_types,
          );
          $controller_vars = array(
            'page_title' => 'Stock Report',
            'icon_name' => 'fa fa-laptop',
          );            
          break;                
        case 'expiry-report':
          $template_name = 'template-2';
          $template_vars = array(
            'title' => 'Stock Report',
            'formAction' => '/report-options/expiry-report',
            'reportHook' => '/expiry-report',
          );
          $controller_vars = array(
            'page_title' => 'Medicine Expiry Report',
            'icon_name' => 'fa fa-times',
          );
          break;                
        case 'itemwise-sales-report':
          $template_name = 'template-3';
          $locations_a = ['' => 'All Stores'] + Utilities::get_client_locations();
          $template_vars = array(
            'title' => 'Itemwise Sales Report',
            'formAction' => '/report-options/itemwise-sales-report',
            'reportHook' => '/itemwise-sales-report',
            'location_codes' => $locations_a,
          );
          $controller_vars = array(
            'page_title' => 'Itemwise Sales Report',
            'icon_name' => 'fa fa-inr',
          );            
          break;
        case 'itemwise-sales-report-bymode':
          $filter_types = Constants::$SALE_MODES;
          $template_name = 'template-6';
          $template_vars = array(
            'title' => 'Itemwise Sales Report By Sale Mode',
            'formAction' => '/report-options/itemwise-sales-report-bymode',
            'reportHook' => '/itemwise-sales-report-bymode',
            'dropDownlabel' => 'Mode of Sale',
            'filter_types' => $filter_types,                 
          );
          $controller_vars = array(
            'page_title' => 'Itemwise Sales Report By Sale Mode',
            'icon_name' => 'fa fa-inr',
          );
          break;                
        case 'sales-by-mode':
          $template_name = 'template-6';
          $filter_types = array(
            'all' => 'All','pkg'=>'Package','int'=>'Internal/Self',
          );                
          $template_vars = array(
            'title' => 'Credit Sales Report',
            'formAction' => '/report-options/sales-by-mode',
            'reportHook' => '/sales-by-mode',
            'dropDownlabel' => 'Mode of Sale',
            'filter_types' => $filter_types,                    
          );
          $controller_vars = array(
            'page_title' => 'Credit Sales Report',
            'icon_name' => 'fa fa-inr',
          );            
          break;
        case 'supplier-payments-due':
          $template_name = 'template-2';
          $template_vars = array(
            'title' => "Supplier's Payment Due",
            'formAction' => '/report-options/supplier-payments-due',
            'reportHook' => '/supplier-payments-due',
          );
          $controller_vars = array(
            'page_title' => "Supplier's Payment Due -  Monthwise",
            'icon_name' => 'fa fa-group',
          );
          break;
        case 'itemwise-sales-returns':
          $template_name = 'template-1';
          $locations_a = ['' => 'All Stores'] + Utilities::get_client_locations();                
          $template_vars = array(
            'title' => 'Itemwise Sales Return Register',
            'formAction' => '/report-options/itemwise-sales-returns',
            'reportHook' => '/itemwise-sales-returns',
            'location_codes' => $locations_a,
          );
          $controller_vars = array(
            'page_title' => 'Itemwise Sales Return Register',
            'icon_name' => 'fa fa-repeat',
          );
          break;
        case 'material-movement':
          $template_name = 'template-7';
          $filter_types = array(
            'fast' => 'Fast moving','slow'=>'Slow moving',
          );                
          $template_vars = array(
            'title' => 'Material Movement Register',
            'formAction' => '/report-options/material-movement',
            'reportHook' => '/material-movement',
            'dropDownlabel' => 'Movement Criteria',
            'filter_types' => $filter_types,
          );
          $controller_vars = array(
            'page_title' => 'Material Movement Register',
            'icon_name' => 'fa fa-arrows',
          );
          break;
        case 'io-analysis':
          $template_name = 'template-2';
          $template_vars = array(
            'title' => 'Inward - Outward Analysis',
            'formAction' => '/report-options/io-analysis',
            'reportHook' => '/io-analysis',
          );
          $controller_vars = array(
            'page_title' => 'Inward - Outward Analysis',
            'icon_name' => 'fa fa-inr',
          );
          break;
        case 'payables-monthwise':
          $template_name = 'template-2';
          $template_vars = array(
            'title' => 'Payables - Monthwise',
            'formAction' => '/report-options/payables-monthwise',
            'reportHook' => '/payables-monthwise',
          );
          $controller_vars = array(
            'page_title' => 'Payables - Monthwise',
            'icon_name' => 'fa fa-inr',
          );
          break;
        case 'inventory-profitability':
          $template_name = 'template-6';
          $filter_types = Constants::$SALE_MODES;  
          $template_vars = array(
            'title' => 'Inventory Profitability',
            'formAction' => '/report-options/inventory-profitability',
            'reportHook' => '/inventory-profitability',
            'filter_types' => $filter_types,
            'dropDownlabel' => 'Sale mode',
          );
          $controller_vars = array(
            'page_title' => 'Inventory Profitability',
            'icon_name' => 'fa fa-level-up',
          );
          break;
        case 'mom-comparison':
          $template_name = 'template-8';
          $filter_types = Constants::$SALE_MODES;  
          $template_vars = array(
            'title' => 'Month over Month Sales Comparison',
            'formAction' => '/report-options/mom-comparison',
            'reportHook' => '/mom-comparison',
            'filter_types' => $filter_types,
            'dropDownlabel' => 'Sale mode',
          );
          $controller_vars = array(
            'page_title' => 'Month over Month Sales Comparison',
            'icon_name' => 'fa fa-bolt',
          );
          break;
        case 'sales-summary-tax-rate':
          $template_name = 'template-10';
          $locations_a = ['' => 'All Stores'] + Utilities::get_client_locations();
          $template_vars = array(
            'title' => 'Sales Summary - Monthwise - By Tax Rate',
            'formAction' => '/report-options/sales-summary-by-month',
            'reportHook' => '/sales-abs-month/taxrate',
            'location_codes' => $locations_a,
          );
          $controller_vars = array(
            'page_title' => 'Sales Summary - Monthwise - By Tax Rate',
            'icon_name' => 'fa fa-inr',
          );
          break;
        case 'indent-item-avail':
          $template_name = 'template-indent-1';
          $locations_a = ['' => 'All Stores'] + Utilities::get_client_locations();
          $template_vars = array(
            'title' => 'Item Availability Report For Indents',
            'formAction' => '/report-options/indent-item-avail',
            'reportHook' => '/indent-item-avail',
            'location_codes' => $locations_a,
          );
          $controller_vars = array(
            'page_title' => 'Item Availability Report For Indents',
            'icon_name' => 'fa fa-database',
          );
          break;
        case 'indent-itemwise':
          $template_name = 'template-indent-2';
          $agents_a = [];
          $agents_response = $this->bu_model->get_business_users(['userType' => 90]);
          if($agents_response['status']) {
            foreach($agents_response['users'] as $user_details) {
              $agents_a[$user_details['userCode']] = $user_details['userName'];
            }
          }
          $template_vars = array(
            'title' => 'Itemwise Indents Booked',
            'formAction' => '/report-options/indent-itemwise',
            'reportHook' => '/indent-itemwise',
            'campaigns' => ['' => 'All Campaigns'] + $this->_get_indent_campaigns(),
            'show_fromto_dates' => true,
            'agents' => ['' => 'Referred by'] + $agents_a
          );
          $controller_vars = array(
            'page_title' => 'Itemwise Indents Booked',
            'icon_name' => 'fa fa-cubes',
          );
          break;
        case 'indent-agentwise':
          $template_name = 'template-indent-2';
          $template_vars = array(
            'title' => 'Wholesalerwise / Agentwise Indents Booked',
            'formAction' => '/report-options/indent-itemwise',
            'reportHook' => '/indent-agentwise',
            'campaigns' => ['' => 'All Campaigns'] + $this->_get_indent_campaigns(),
            'show_fromto_dates' => true,
          );
          $controller_vars = array(
            'page_title' => 'Wholesalerwise / Agentwise Indents Booked',
            'icon_name' => 'fa fa-user-circle-o',
          );
          break;
        case 'indent-statewise':
          $template_name = 'template-indent-2';
          $template_vars = array(
            'title' => 'Statewise Indents Booked',
            'formAction' => '/report-options/indent-statewise',
            'reportHook' => '/indent-statewise',
            'campaigns' => ['' => 'All Campaigns']  + $this->_get_indent_campaigns(),
            'show_fromto_dates' => true,
          );
          $controller_vars = array(
            'page_title' => 'Statewise Indents Booked',
            'icon_name' => 'fa fa-compass',
          );
          break;
        case 'indent-register':
          $template_name = 'template-indent-2';
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
          $template_vars = array(
            'title' => 'Indent Register',
            'formAction' => '/report-options/indent-register',
            'reportHook' => '/indent-register',
            'campaigns' => ['' => 'All Campaigns']  + $this->_get_indent_campaigns(),
            'agents' => ['' => 'Referred by']  + $agents_a,
            'rateOptions' => [''=>'Rate Option', 'no' => 'Without Rate', 'yes'=>'With Rate'],
            'show_fromto_dates' => true,
          );
          $controller_vars = array(
            'page_title' => 'Indent Register',
            'icon_name' => 'fa fa-book',
          );
          break;
        case 'print-indents-agentwise':
          $template_name = 'template-indent-2';
          $agents_a = [];
          $agents_response = $this->bu_model->get_business_users(['userType' => 90]);
          if($agents_response['status']) {
            foreach($agents_response['users'] as $user_details) {
              $agents_a[$user_details['userCode']] = $user_details['userName'];
            }
          }
          $template_vars = array(
            'title' => 'Indents List By Agentwise',
            'formAction' => '/report-options/print-indents-agentwise',
            'reportHook' => '/print-indents-agentwise',
            'campaigns' => ['' => 'All Campaigns'] + $this->_get_indent_campaigns(),
            'agents' => ['' => 'Referred by'] + $agents_a,
            'show_fromto_dates' => false,
            'show_format' => false,
          );
          $controller_vars = array(
            'page_title' => 'All Indents List - By Agentwise',
            'icon_name' => 'fa fa-cubes',
          );
          break;
        case 'indent-dispatch-summary':
          $template_name = 'template-indent-2';
          $template_vars = array(
            'title' => 'Dispatch Summary - Indents Itemwise',
            'formAction' => '/report-options/print-indents-agentwise',
            'reportHook' => '/indent-dispatch-summary',
            'campaigns' => ['' => 'All Campaigns'] + $this->_get_indent_campaigns(),
            'agents' => [],
            'show_fromto_dates' => false,
            'show_format' => false,
          );
          $controller_vars = array(
            'page_title' => 'Dispatch Summary - Indents Itemwise',
            'icon_name' => 'fa fa-truck',
          );
          break;
        case 'opening-balances':
          $template_name = 'common-1';
          $locations_a = ['' => 'All Stores'] + Utilities::get_client_locations();            
          $template_vars = array(
            'title' => 'Opening Balances Report',
            'formAction' => '/report-options/opening-balances',
            'reportHook' => '/opening-balances',
            'location_codes' => $locations_a,            
          );
          $controller_vars = array(
            'page_title' => 'Opening Balances Report',
            'icon_name' => 'fa fa-folder-open',
          );
          break;          
        default:
          $this->flash->set_flash_message('Invalid Report!! Try again.', 1);
          Utilities::redirect('/dashboard');
      }
      $template = new Template($this->views_path);
      return array($template->render_view($template_name, $template_vars), $controller_vars);
    }
  }

  // returns error message for the reports.
  private function _get_print_error_message() {
    return "<h1>Invalid Request</h1>";
  }

  private function _get_indent_campaigns() {
    $campaigns_response = $this->camp_model->list_campaigns();
    if($campaigns_response['status']) {
      $campaign_keys = array_column($campaigns_response['campaigns']['campaigns'], 'campaignCode');
      $campaign_names = array_column($campaigns_response['campaigns']['campaigns'], 'campaignName');
      $campaigns_a = array_combine($campaign_keys, $campaign_names);
    } else {
      $campaigns_a = [];
    }
    return $campaigns_a;
  }
}