<?php 

namespace ClothingRm\Reports\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\PDF;

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

    $sales_response = $sales->get_sales_details($billNo,true);
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

    $sales_response = $sales->get_sales_details($billNo,true);
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
    $bill_time = date('h:ia',strtotime($sale_details['createdOn']));
    $pay_method = Constants::$PAYMENT_METHODS_RC[$sale_details['paymentMethod']];
    $terms_text = 'Note: [1] Please get your medicines checked by Doctor before use. [2] Production of Original bill is mandatory for return of items. [3] Item returns/replacement will not be entertained after 48 hours. [4] Total amount is inclusive of applicable taxes.';

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');

    # Print Bill Information.
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,'Tax Invoice','',1,'C');
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(4);

    # Initialize widths
    $invoice_info_widths = [40,35,45,70];
    $customer_info_widths = [95,95];
    $item_widths = [10,38,16,14,14,14,14,14,14,14,14,14];
    $final_tot_width = [23,23,23,25,20,30,23,23];

    # second row
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln();
    $pdf->Cell($invoice_info_widths[0],6,'Invoice No.','LRTB',0,'C');
    $pdf->Cell($invoice_info_widths[1],6,'Invoice Date','RTB',0,'C');
    $pdf->Cell($invoice_info_widths[2],6,'Paid Through','RTB',0,'C');
    $pdf->Cell($invoice_info_widths[3],6,'Promo Offer(s)','RTB',1,'C');
    
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($invoice_info_widths[0],6,$bill_no,'LRTB',0,'C');
    $pdf->SetFont('Arial','',9);
    $pdf->Cell($invoice_info_widths[1],6,$bill_date,'RTB',0,'C');
    $pdf->Cell($invoice_info_widths[2],6,$pay_method,'RTB',0,'C');
    $pdf->Cell($invoice_info_widths[3],6,'','RTB',1,'C');
    
    $pdf->SetFont('Arial','B',7);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'ProductName','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'HSNCode','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Item Rate','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Item Qty.','RTB',0,'C');  
    $pdf->Cell($item_widths[5],6,'Gross Val.','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'Discount','RTB',0,'C');
    $pdf->Cell($item_widths[7],6,'Taxable','RTB',0,'C');
    $pdf->Cell($item_widths[8],6,'CGST','RTB',0,'C');
    $pdf->Cell($item_widths[9],6,'CGST Val.','RTB',0,'C');
    $pdf->Cell($item_widths[10],6,'SGST','RTB',0,'C');
    $pdf->Cell($item_widths[11],6,'SGST Val.','RTB',1,'C');    
    $pdf->SetFont('Arial','',7);

    $tot_bill_value = $tot_discount = $tot_cgst_value = 0;
    $tot_sgst_value = $tot_taxable = $tot_items_qty = 0;
    foreach($sale_item_details as $item_details) {
      $slno++;
      $amount = round($item_details['itemQty']*$item_details['itemRate'], 2);
      $discount = $item_details['discount'];
      $taxable = round($amount - $discount, 2);
      
      $tax_percent = $item_details['taxPercent'];
      $tax_value = ($taxable * $tax_percent / 100);

      $cgst_percent = $sgst_percent = round($tax_percent/2,2);
      $cgst_value = $sgst_value = round($tax_value/2,2);

      $tot_bill_value += $amount;
      $tot_discount += $discount;
      $tot_taxable += $taxable;
      $tot_sgst_value += $sgst_value;
      $tot_cgst_value += $cgst_value;
      $tot_items_qty += $item_details['itemQty'];

      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,substr($item_details['itemName'],0,20),'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,'','RTB',0,'L');
      $pdf->Cell($item_widths[3],6,$item_details['itemRate'],'RTB',0,'R');
      $pdf->Cell($item_widths[4],6,$item_details['itemQty'],'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,number_format($amount,2),'RTB',0,'R');
      $pdf->Cell($item_widths[6],6,number_format($item_details['discount'],2),'RTB',0,'R');
      $pdf->Cell($item_widths[6],6,number_format($taxable,2),'RTB',0,'R');      
      $pdf->Cell($item_widths[7],6,$cgst_percent.' %','RTB',0,'R');
      $pdf->Cell($item_widths[8],6,number_format($cgst_value,2),'RTB',0,'R');
      $pdf->Cell($item_widths[9],6,$sgst_percent.' %','RTB',0,'R');
      $pdf->Cell($item_widths[10],6,number_format($sgst_value,2),'RTB',1,'R');
    }

    $pdf->Cell($item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3],6,'LINE TOTALS','LRTB',0,'R');
    $pdf->Cell($item_widths[4],6,$tot_items_qty,'RTB',0,'R');
    $pdf->Cell($item_widths[5],6,number_format($tot_bill_value,2),'RTB',0,'R');
    $pdf->Cell($item_widths[6],6,number_format($tot_discount,2),'RTB',0,'R');
    $pdf->Cell($item_widths[6],6,number_format($tot_taxable,2),'RTB',0,'R');  
    $pdf->Cell($item_widths[7],6,'','RTB',0,'R');
    $pdf->Cell($item_widths[8],6,number_format($tot_cgst_value,2),'RTB',0,'R');
    $pdf->Cell($item_widths[9],6,'','RTB',0,'R');
    $pdf->Cell($item_widths[10],6,number_format($tot_sgst_value,2),'RTB',1,'R');
      
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(190,6,'INVOICE SUMMARY (figures in Rs.)','LRTB',1,'C');
    $pdf->SetFont('Arial','B',9);

    $pdf->Cell($final_tot_width[0],6,'Bill Amount','LRTB',0,'C');
    $pdf->Cell($final_tot_width[1],6,'Discount','RTB',0,'C');
    $pdf->Cell($final_tot_width[2],6,'Taxable','RTB',0,'C');  
    $pdf->Cell($final_tot_width[3],6,'CGST & SGST','RTB',0,'C');
    $pdf->Cell($final_tot_width[4],6,'Round Off','RTB',0,'C');
    $pdf->Cell($final_tot_width[5],6,'Invoice Total','RTB',0,'C');
    $pdf->Cell($final_tot_width[6],6,'Paid by Cash','RTB',0,'C'); 
    $pdf->Cell($final_tot_width[7],6,'Paid by Card','RTB',1,'C');

    $pdf->Cell($final_tot_width[0],6,number_format($sale_details['billAmount'],2),'LRTB',0,'R');
    $pdf->Cell($final_tot_width[1],6,number_format($sale_details['discountAmount'],2),'RTB',0,'R');
    $pdf->Cell($final_tot_width[2],6,number_format($sale_details['totalAmount'],2),'RTB',0,'R');  
    $pdf->Cell($final_tot_width[3],6,number_format($sale_details['taxAmount'],2),'RTB',0,'R');
    $pdf->Cell($final_tot_width[4],6,number_format($sale_details['roundOff'],2),'RTB',0,'R');
    $pdf->SetFont('Arial','B',14);      
    $pdf->Cell($final_tot_width[5],6,number_format($sale_details['netPay'],2),'RTB',0,'R');
    $pdf->SetFont('Arial','B',9);      
    $pdf->Cell($final_tot_width[6],6,$sale_details['netPayCash'] > 0 ? number_format($sale_details['netPayCash'],2) : '','RTB',0,'R');      
    $pdf->Cell($final_tot_width[7],6,$sale_details['netPayCard'] > 0 ? number_format($sale_details['netPayCard'],2) : '','RTB',1,'R');
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
    $campaigns_response = $this->camp_model->get_live_campaigns();
    if($campaigns_response['status']) {
      $campaign_keys = array_column($campaigns_response['campaigns'], 'campaignCode');
      $campaign_names = array_column($campaigns_response['campaigns'], 'campaignName');
      $campaigns_a = array_combine($campaign_keys, $campaign_names);
    } else {
      $campaigns_a = [];
    }
    return $campaigns_a;
  }
}