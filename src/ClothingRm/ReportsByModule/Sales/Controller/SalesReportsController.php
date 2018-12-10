<?php 

namespace ClothingRm\ReportsByModule\Sales\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;
use Atawa\PDF;

use ClothingRm\Sales\Model\Sales;
use BusinessUsers\Model\BusinessUsers;

class SalesReportsController {

  protected $views_path;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->sales_model = new Sales;
    $this->bu_model = new BusinessUsers;
    $this->flash = new Flash;    
  }

  public function printSalesRegister(Request $request) {
   
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 300;
    $total_records = [];

    $client_locations = Utilities::get_client_locations();
    $sa_executives = $this->_get_sales_executives();

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_form_data($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
      } else {
        $error_message = 'Invalid Form Data.';
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-register');        
      }

      // hit api
      $sales_api_response = $this->sales_model->get_sales($page_no, $per_page, $form_data);
      if($sales_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-register');
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

        $total_records = $this->_format_data_for_sales_register($total_records);
        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
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
        Utilities::download_as_CSV_attachment('SalesRegister', $csv_headings, $total_records);
        return;
      }

      // dump($total_records);
      // exit;

      // start PDF printing.
      $item_widths = array(10,25,33,18,18,18,15,18,35);
      $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2];
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
      $pdf->Cell($item_widths[1],6,'Payment Mode','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'Bill No. & Date','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'BillAmt','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'TaxAmout','RTB',0,'C');
      $pdf->Cell($item_widths[5],6,'TotAmt','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'RndOff','RTB',0,'C');  
      $pdf->Cell($item_widths[7],6,'NetPay','RTB',0,'C');
      $pdf->Cell($item_widths[8],6,'CustomerName','RTB',0,'C');
      $pdf->SetFont('Arial','',9);

      $tot_bill_amount = $tot_tax = $tot_total_amount = $tot_round_off = $tot_net_pay = 0;
      foreach($total_records as $record_details) {
          $slno++;
          $amount = $record_details['NetPay'];

          $tot_bill_amount += $record_details['BillAmt'];
          $tot_tax += $record_details['taxAmount'];
          $tot_total_amount += $record_details['TotAmt'];
          $tot_round_off += $record_details['RndOff'];
          $tot_net_pay += $amount;
          
          $pdf->Ln();
          $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
          $pdf->Cell($item_widths[1],6,$record_details['Payment Mode'],'RTB',0,'L');
          $pdf->Cell($item_widths[2],6,$record_details['Bill No. & Date'],'RTB',0,'L');
          $pdf->Cell($item_widths[3],6,$record_details['BillAmt'],'RTB',0,'R');            
          $pdf->Cell($item_widths[4],6,$record_details['taxAmount'],'RTB',0,'R');
          $pdf->Cell($item_widths[5],6,$record_details['TotAmt'],'RTB',0,'R');
          $pdf->Cell($item_widths[6],6,$record_details['RndOff'],'RTB',0,'R');
          $pdf->Cell($item_widths[7],6,$record_details['NetPay'],'RTB',0,'R');
          $pdf->Cell($item_widths[8],6,$record_details['CustomerName'],'RTB',0,'L');  
      }
    
      $pdf->Ln();
      $pdf->SetFont('Arial','B',10);    
      $pdf->Cell($totals_width,6,'Totals','LRTB',0,'R');
      $pdf->Cell($item_widths[3],6,number_format($tot_bill_amount,2),'LRTB',0,'R');
      $pdf->Cell($item_widths[4],6,number_format($tot_tax,2),'LRTB',0,'R');    
      $pdf->Cell($item_widths[5],6,number_format($tot_total_amount,2),'LRTB',0,'R');
      $pdf->Cell($item_widths[6],6,number_format($tot_round_off,2),'LRTB',0,'R');
      $pdf->Cell($item_widths[7],6,number_format($tot_net_pay,2),'LRTB',0,'R');
      $pdf->Cell($item_widths[8],6,'','LRTB',0,'R');

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
        $error_message = 'Invalid Form Data.';
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
        $credit_sales = $day_summary[0]['creditSales'];
        $cash_in_hand = $day_summary[0]['cashInHand'];
        $sales_return = $day_summary[0]['returnAmount'];
        $day_sales = $cash_sales + $card_sales + $split_sales + $credit_sales;
        $total_sales = $day_sales - $sales_return;
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        Utilities::download_as_CSV_attachment('DaySalesReport', $csv_headings, $day_summary);
        return;
      }

      // dump($total_records);
      // exit;

      // start PDF printing.

      $item_widths = array(10,45,35);
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
      $pdf->Cell($item_widths[1],6,'Credit Sale','RTB',0,'L');
      $pdf->Cell($item_widths[2],6,number_format($credit_sales,2,'.',''),'RTB',0,'R');

      $pdf->Ln();
      $pdf->SetFont('Arial','B');          
      $pdf->Cell($item_widths[0],6,'','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'(a) + (b) + (c) + (d)','RTB',0,'R');
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
      $pdf->SetFont('Arial','B');              
      $pdf->Cell($item_widths[0],6,'','LRTB',0,'C');                     
      $pdf->Cell($item_widths[1],6,'Cash in hand (a)-(e)','RTB',0,'R');
      $pdf->Cell($item_widths[2],6,number_format($cash_in_hand,2,'.',''),'RTB',0,'R');

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
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
    );

    // render template
    return [$this->template->render_view('day-sales-report', $template_vars), $controller_vars];    
  }

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
        $error_message = 'Invalid Form Data.';
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
        $month_name = date('F', mktime(0, 0, 0, $form_data['month'], 10));        
        $heading1 = 'Daywise Sales Summary';
        $heading2 = 'for the month of '.$month_name.', '.$form_data['year'];
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }
        $csv_headings = [ [$heading1], [$heading2] ];
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        Utilities::download_as_CSV_attachment('DaywiseSalesSummary', $csv_headings, $month_summary);
        return;
      }

      // start PDF printing.
      $item_widths = array(17,18,18,18,18,18,21,21,21,23);
      $totals_width = $item_widths[0]+$item_widths[1];
      $slno = 0;

      $discount_label = '**Discount amount is shown for information purpose only. It was already included in Cash/Card/Cnote Sale';

      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('P','A4');

      $pdf->SetFont('Arial','B',16);
      $pdf->Cell(0,0,$heading1,'',1,'C');
      $pdf->SetFont('Arial','B',11);
      $pdf->Ln(5);
      $pdf->Cell(0,0,$heading2,'',1,'C');

      $pdf->SetFont('Arial','B',8);
      $pdf->Ln(5);
      $pdf->Cell($item_widths[0],6,'Date','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'CashSales','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'CardSales','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'SplitSales','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'CreditSales','RTB',0,'C');      
      $pdf->Cell($item_widths[5],6,'TotalSales','RT',0,'C');  
      $pdf->Cell($item_widths[6],6,'CashPaymnts','RTB',0,'C');
      $pdf->Cell($item_widths[7],6,'CardPaymnts','RTB',0,'C');
      $pdf->Cell($item_widths[8],6,'CnotePaymnts','RTB',0,'C');
      $pdf->Cell($item_widths[9],6,'Discount**','RTB',0,'C');
      $pdf->SetFont('Arial','',8);

      $tot_cash_sales = $tot_split_sales = $tot_card_sales = $tot_credit_sales = $tot_sales = 0;
      $tot_discounts = $tot_discount_bills = 0;
      $tot_cash_payments = $tot_card_payments = $tot_cnote_payments = 0;

      foreach($month_summary as $day_details) {
        $date = date("d-m-Y", strtotime($day_details['tranDate']));
        $week = date("l", strtotime($day_details['tranDate']));
        $day_sales = $day_details['cashSales'] + $day_details['splitSales'] + $day_details['cardSales'] + $day_details['creditSales'];

        $tot_cash_sales += $day_details['cashSales'];
        $tot_card_sales += $day_details['cardSales'];
        $tot_split_sales += $day_details['splitSales'];
        $tot_credit_sales += $day_details['creditSales'];

        $tot_cash_payments += $day_details['cashPayments'];
        $tot_card_payments += $day_details['cardPayments'];
        $tot_cnote_payments += $day_details['cnotePayments'];

        $tot_discounts += $day_details['discountGiven'];
        $tot_discount_bills += $day_details['totalDiscountBills'];

        $cash_sales = $day_details['cashSales'] > 0 ? number_format($day_details['cashSales'],2,'.','') : '';
        $card_sales = $day_details['cardSales'] > 0 ? number_format($day_details['cardSales'],2,'.','') : '';
        $split_sales = $day_details['splitSales'] > 0 ? number_format($day_details['splitSales'],2,'.','') : '';
        $credit_sales = $day_details['creditSales'] > 0 ? number_format($day_details['creditSales'],2,'.','') : '';

        $cash_payments = $day_details['cashPayments'] > 0 ? number_format($day_details['cashPayments'],2,'.','') : '' ;
        $card_payments = $day_details['cardPayments'] > 0 ? number_format($day_details['cardPayments'],2,'.','') : '' ;
        $cnote_payments = $day_details['cnotePayments'] > 0 ? number_format($day_details['cnotePayments'],2,'.','') : '' ;

        $total_sales = number_format($day_details['cashSales'] + $day_details['cardSales'] + $day_details['splitSales'],2,'.','');
        $discount_string = number_format($day_details['discountGiven'],2,'.','').' ('.$day_details['totalDiscountBills'].')';

        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$date,'LRTB',0,'L');
        $pdf->Cell($item_widths[1],6,$cash_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[2],6,$card_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[3],6,$split_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[4],6,$credit_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,$total_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,$cash_payments,'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,$card_payments,'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,$cnote_payments,'RTB',0,'R');
        $pdf->Cell($item_widths[9],6,$discount_string,'RTB',0,'R');
      }

      $tot_sales = $tot_cash_sales + $tot_credit_sales + $tot_split_sales + $tot_card_sales;

      $pdf->SetFont('Arial','B',8);      
      $pdf->Ln();
      $pdf->Cell($item_widths[0],6,'TOTALS','LTB',0,'R');
      $pdf->Cell($item_widths[1],6,number_format($tot_cash_sales,2,'.',''),'LRTB',0,'R');
      $pdf->Cell($item_widths[2],6,number_format($tot_card_sales,2,'.',''),'RTB',0,'R');        
      $pdf->Cell($item_widths[3],6,number_format($tot_split_sales,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[4],6,number_format($tot_credit_sales,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,number_format($tot_sales,2,'.',''),'RTB',0,'R');        
      $pdf->Cell($item_widths[6],6,number_format($tot_cash_payments,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[7],6,number_format($tot_card_payments,2,'.',''),'RTB',0,'R');        
      $pdf->Cell($item_widths[8],6,number_format($tot_cnote_payments,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[9],6,'*****','RTB',1,'R');    
      $pdf->Cell(array_sum($item_widths),6,$discount_label,'',0,'R');
      
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
        $error_message = 'Invalid Form Data.';
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/itemwise-sales-register');        
      }

      // hit api
      $sales_api_response = $this->sales_model->get_sales_summary_bymon_tax_report($form_data);
      if($sales_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/itemwise-sales-register');
      } else {
        $sales_summary = $sales_api_response['summary'];
        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }        
        $month_name = date('F', mktime(0, 0, 0, $form_data['month'], 10));
        $heading1 = 'Sales by Tax Rate';
        $heading2 = 'for the month of '.$month_name.', '.$form_data['year'];
        if($location_name !== '') {
          $heading1 .= ' :: '.$location_name;
        }        
        $csv_headings = [ [$heading1], [$heading2] ];
      }

      // dump($sales_summary);
      // exit;

      $format = $form_data['format'];
      if($format === 'csv') {
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
          $grand_cgst_value += $day_details['fivePercentCgstAmt'];        
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
            $cgst_amount = $sgst_amount = '';
            $igst_percent = number_format($key,2);
          } else {
            $cgst_amount = number_format($gst_summary_details['cgst'],2,'.','');
            $sgst_amount = number_format($gst_summary_details['sgst'],2,'.','');
            $cgst_percent = $sgst_percent = number_format($key/2, 2);
            $igst_percent = '';
            $igst_amount = '';
          }

          $pdf->Ln();
          $pdf->Cell($item_widths[0],6,$date,'LRTB',0,'L');
          $pdf->Cell($item_widths[1],6,number_format($gst_summary_details['qty'],2),'RTB',0,'R');
          $pdf->Cell($item_widths[2],6,number_format($gst_summary_details['billable'],2),'RTB',0,'R');
          $pdf->Cell($item_widths[3],6,number_format($gst_summary_details['taxable'],2),'RTB',0,'R');
          $pdf->Cell($item_widths[4],6,$igst_percent,'RTB',0,'R');
          $pdf->Cell($item_widths[5],6,$igst_amount,'RTB',0,'R');
          $pdf->Cell($item_widths[6],6,$cgst_percent,'RTB',0,'R');
          $pdf->Cell($item_widths[7],6,$cgst_amount,'RTB',0,'R');
          $pdf->Cell($item_widths[8],6,$sgst_percent,'RTB',0,'R');
          $pdf->Cell($item_widths[9],6,$sgst_amount,'RTB',0,'R');
          $pdf->Cell($item_widths[10],6,number_format($key,2),'RTB',0,'R');
          $pdf->Cell($item_widths[11],6,number_format($gst_summary_details['cgst'] + $gst_summary_details['sgst'], 2, '.', ''),'RTB',0,'R');
        }
      }

      $pdf->Ln();
      $pdf->SetFont('Arial','B',11);
      $pdf->Cell($item_widths[0],6,'','LRTB',0,'L');
      $pdf->Cell($item_widths[1],6,number_format($grand_tot_qty,2),'RTB',0,'R');
      $pdf->Cell($item_widths[2],6,number_format($grand_billable,2),'RTB',0,'R');
      $pdf->Cell($item_widths[3],6,number_format($grand_taxable,2),'RTB',0,'R');
      $pdf->Cell($item_widths[4],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[5],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[6],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[7],6,number_format($grand_cgst_value, 2),'RTB',0,'R');
      $pdf->Cell($item_widths[8],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[9],6,number_format($grand_sgst_value, 2),'RTB',0,'R');
      $pdf->Cell($item_widths[10],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[11],6,number_format($grand_cgst_value + $grand_sgst_value, 2),'RTB',0,'R');
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

  public function itemwiseSalesRegister(Request $request) {
   
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 300;
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
        $error_message = 'Invalid Form Data.';
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-register');        
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

        // dump($total_records);
        // exit;

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
        $total_records = $this->_format_data_for_itemwise_sales_register($total_records);
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
      $pdf->Cell($item_widths[2],6,'HSN/SAC','RTB',0,'C');
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
          $pdf->Cell($item_widths[2],6,$record_details['hsnSacCode'],'RTB',0,'L');
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
    $cleaned_params = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $cleaned_params['locationCode'] = '';
    }
    if($form_data['fromDate'] !== '') {
      $cleaned_params['fromDate'] = Utilities::clean_string($form_data['fromDate']);
    } else {
      $cleaned_params['fromDate'] = '';
    }
    if($form_data['toDate'] !== '') {
      $cleaned_params['toDate'] = Utilities::clean_string($form_data['toDate']);
    } else {
      $cleaned_params['toDate'] = '';
    }

    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);
    $cleaned_params['saExecutiveCode'] = Utilities::clean_string($form_data['saExecutiveCode']);

    return ['status' => true, 'cleaned_params' => $cleaned_params];
  }

  private function _validate_form_data_day_sales($form_data = []) {
    $cleaned_params = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $cleaned_params['locationCode'] = '';
    }
    if($form_data['saleDate'] !== '') {
      $cleaned_params['saleDate'] = Utilities::clean_string($form_data['saleDate']);
    } else {
      $cleaned_params['saleDate'] = '';
    }
    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);

    return ['status' => true, 'cleaned_params' => $cleaned_params];
  }

  private function _validate_form_data_sales_summary_bymon($form_data = []) {
    $cleaned_params = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $cleaned_params['locationCode'] = '';
    }
    if($form_data['month'] !== '') {
      $cleaned_params['month'] = Utilities::clean_string($form_data['month']);
    } else {
      $cleaned_params['month'] = date("m");
    }
    if($form_data['year'] !== '') {
      $cleaned_params['year'] = Utilities::clean_string($form_data['year']);
    } else {
      $cleaned_params['year'] = date("Y");
    }    
    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);

    return ['status' => true, 'cleaned_params' => $cleaned_params];
  }

  private function _validate_form_data_itemwise_sr($form_data = []) {
    $cleaned_params = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $cleaned_params['locationCode'] = '';
    }
    if($form_data['fromDate'] !== '') {
      $cleaned_params['fromDate'] = Utilities::clean_string($form_data['fromDate']);
    } else {
      $cleaned_params['fromDate'] = '';
    }
    if($form_data['toDate'] !== '') {
      $cleaned_params['toDate'] = Utilities::clean_string($form_data['toDate']);
    } else {
      $cleaned_params['toDate'] = '';
    }

    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);
    $cleaned_params['sortBy'] = Utilities::clean_string($form_data['sortBy']);

    return ['status' => true, 'cleaned_params' => $cleaned_params];
  }  

  private function _format_data_for_sales_register($total_records = []) {
    $new_records = [];
    foreach($total_records as $key => $total_record) {
      $payment_method = Constants::$PAYMENT_METHODS_RC_SHORT[$total_record['paymentMethod']];
      $bill_info = $total_record['billNo'].' / '.date("d-m-y", strtotime($total_record['invoiceDate']));
      $tran_info = date("d-M-Y h:ia", strtotime($total_record['createdOn']));
      $customer_name = strtolower($total_record['customerName']);

      $new_records[$key]['Payment Mode'] = $payment_method;
      $new_records[$key]['Bill No. & Date'] = $bill_info;
      $new_records[$key]['BillAmt'] = number_format($total_record['totalAmount'],2,'.','');
      $new_records[$key]['taxAmount'] = number_format($total_record['taxAmount'],2,'.','');
      $new_records[$key]['TotAmt'] = number_format($total_record['billAmount'],2,'.','');
      $new_records[$key]['RndOff'] = number_format($total_record['roundOff'],2,'.','');
      $new_records[$key]['NetPay'] = number_format($total_record['netPay'],2,'.','');
      if($customer_name !== '') {
        $new_records[$key]['CustomerName'] = substr($customer_name,0,20);
      } else {
        $new_records[$key]['CustomerName'] = $total_record['tmpCustName'];
      }
    }
    return $new_records;
  }

  private function _format_data_for_itemwise_sales_register($total_records = []) {
    $new_records = [];
    $slno = 0;
    foreach($total_records as $key => $total_record) {
      $slno++;
      $new_records[$key]['Sl. No.'] = $slno;
      $new_records[$key]['Item Name'] = $total_record['itemName'];
      $new_records[$key]['HSN/SAC Code'] = $total_record['hsnSacCode'];
      $new_records[$key]['Category Name'] = $total_record['categoryName'];
      $new_records[$key]['Sold Qty.'] = $total_record['soldQty'];
      $new_records[$key]['Rate'] = $total_record['saleRate'];
      $new_records[$key]['Gross Amount'] = $total_record['saleValue'];
      $new_records[$key]['Discount'] = $total_record['discountAmount'];
    }
    return $new_records;
  }  
}