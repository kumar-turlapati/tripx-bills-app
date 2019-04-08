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

  // prints sales register.
  public function printSalesRegister(Request $request) {
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 300;
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
        $tot_round_off += $round_off;
        $tot_net_pay += $net_pay;
        $tot_gst += $gst;
        
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
    $page_no = 1; $per_page = 300;
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
    $page_no = 1; $per_page = 300;
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
      $item_widths = array(10,16,18,42,13,16,18,18,18,20,40,48);
                        //  0, 1, 2, 3, 4, 5, 6, 7, 8, 9,10,11
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
      $pdf->Cell($item_widths[9],6,'Net Amount','RTB',0,'C');
      $pdf->Cell($item_widths[10],6,'Customer Name','RTB',0,'C');      
      $pdf->Cell($item_widths[11],6,'Remarks','RTB',0,'C');      
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
          $pdf->Cell($item_widths[6],6,'','RTB',0,'R');
          $pdf->Cell($item_widths[8],6,number_format($bill_total, 2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[8],6,number_format($bill_discount, 2, '.', ''),'RTB',0,'R');
          $pdf->Cell($item_widths[9],6,number_format($netpay, 2, '.', ''),'RTB',0,'R');  
          $pdf->Cell($item_widths[10],6,'','RTB',0,'L');
          $pdf->Cell($item_widths[11],6,'','RTB',0,'L');
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

        if($record_details['remarksInvoice'] !== '') {
          $remarks_invoice = substr($record_details['remarksInvoice'],0,25);
        } else {
          $remarks_invoice = '';
        }

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
        $pdf->Cell($item_widths[9],6,number_format($item_value,2,'.',''),'RTB',0,'R');  
        $pdf->Cell($item_widths[10],6,substr($customer_name,0,20),'RTB',0,'L');
        $pdf->SetFont('Arial','',6);   
        $pdf->Cell($item_widths[11],6,$remarks_invoice,'RTB',0,'L');
        $pdf->SetFont('Arial','',8);        
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
      $pdf->Cell($item_widths[6],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[7],6,number_format($bill_total, 2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[8],6,number_format($bill_discount, 2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[9],6,number_format($netpay, 2, '.', ''),'RTB',0,'R');  
      $pdf->Cell($item_widths[10],6,'','RT',0,'L');
      $pdf->Cell($item_widths[11],6,'','RT',0,'L');

      $pdf->Ln();
      $pdf->Cell($item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3],6,'REPORT TOTALS','LRTB',0,'R');
      $pdf->Cell($item_widths[4],6,number_format($tot_sold_qty,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[6],6,'','RTB',0,'R');
      $pdf->Cell($item_widths[7],6,number_format($tot_amount, 2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[8],6,number_format($tot_discount, 2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[9],6,number_format($tot_net_pay, 2, '.', ''),'RTB',0,'R');  
      $pdf->Cell($item_widths[10],6,'','RTB',0,'L');
      $pdf->Cell($item_widths[11],6,'','RTB',0,'L');
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
      $pdf->Cell($item_widths[1],6,'Cash in hand **','RTB',0,'R');
      $pdf->Cell($item_widths[2],6,number_format($cash_in_hand,2,'.',''),'RTB',0,'R');

      $pdf->Ln();
      $pdf->SetFont('Arial','B',10);              
      $pdf->Cell($item_widths[0] + $item_widths[1] + $item_widths[2], 6, '** Cash Sale + Cash Paid in Split Sale');

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
      $item_widths = array(18,19,19,19,21,21,21,21,24,24,24,26);
      $totals_width = $item_widths[0]+$item_widths[1];
      $slno = 0;

      $discount_label  = '**Discount amount is shown for information purpose only. It was already included in Cash/Card/Cnote Sale';
      $net_sales_text  = '##Net Sales: (Cash Sales + Card Sales + Split Sales + Credit Sales) - Sales Return';
      $net_sales_text1 = '##Net Sales: (Paid By Cash + Paid By Card + Credit Notes + Credit Sales) - Sales Return';

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
      $pdf->Cell($item_widths[5],6,'Gross Sales','RT',0,'C');
      $pdf->Cell($item_widths[6],6,'Sales Return','RT',0,'C');
      $pdf->Cell($item_widths[7],6,'Net Sales ##','RT',0,'C');
      $pdf->Cell($item_widths[8],6,'Paid By Cash','RTB',0,'C');
      $pdf->Cell($item_widths[9],6,'Paid By Card','RTB',0,'C');
      $pdf->Cell($item_widths[10],6,'Credit Notes','RTB',0,'C');
      $pdf->Cell($item_widths[11],6,'Discount / Bills **','RTB',0,'C');
      $pdf->SetFont('Arial','',8);

      $tot_cash_sales = $tot_split_sales = $tot_card_sales = $tot_credit_sales = $tot_sales = 0;
      $tot_discounts = $tot_discount_bills = $tot_returns = 0;
      $tot_cash_payments = $tot_card_payments = $tot_cnote_payments = 0;

      foreach($month_summary as $day_details) {
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

        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$date,'LRTB',0,'L');
        $pdf->Cell($item_widths[1],6,$cash_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[2],6,$card_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[3],6,$split_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[4],6,$credit_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,$total_sales,'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,$sales_return,'RTB',0,'R');
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell($item_widths[7],6,$net_sales,'RTB',0,'R');
        $pdf->SetFont('Arial','',8);
        $pdf->Cell($item_widths[8],6,$cash_payments,'RTB',0,'R');
        $pdf->Cell($item_widths[9],6,$card_payments,'RTB',0,'R');
        $pdf->Cell($item_widths[10],6,$cnote_payments,'RTB',0,'R');
        $pdf->Cell($item_widths[11],6,$discount_string,'RTB',0,'R');
      }

      $tot_sales = $tot_cash_sales + $tot_credit_sales + $tot_split_sales + $tot_card_sales;
      $tot_net_sales = $tot_sales - $tot_returns;

      $pdf->SetFont('Arial','B',8);      
      $pdf->Ln();
      $pdf->Cell($item_widths[0],6,'TOTALS','LTB',0,'R');
      $pdf->Cell($item_widths[1],6,number_format($tot_cash_sales,2,'.',''),'LRTB',0,'R');
      $pdf->Cell($item_widths[2],6,number_format($tot_card_sales,2,'.',''),'RTB',0,'R');        
      $pdf->Cell($item_widths[3],6,number_format($tot_split_sales,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[4],6,number_format($tot_credit_sales,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,number_format($tot_sales,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[6],6,number_format($tot_returns,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[7],6,number_format($tot_net_sales,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[8],6,number_format($tot_cash_payments,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[9],6,number_format($tot_card_payments,2,'.',''),'RTB',0,'R');        
      $pdf->Cell($item_widths[10],6,number_format($tot_cnote_payments,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[11],6,'*****','RTB',1,'R');
      $pdf->SetFont('Arial','',8);
      $pdf->Ln(5);
      $pdf->Cell(array_sum($item_widths),6,$discount_label,'',0,'R');
      $pdf->Ln(4);
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
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['StoreName'] = 'Invalid Store Name.';
    }
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
      $tot_round_off += $round_off;
      $tot_net_pay += $net_pay;
      $tot_gst += $tot_gst;

      $cleaned_params[$key] = [
        'SNo.' => $slno,
        'Payment Mode' => $payment_method,
        'Bill No. & Date' => $bill_info,
        'Gross Amount (Rs.)' => number_format($gross_amount, 2, '.', ''),
        'Discount (Rs.)' => number_format($discount, 2, '.', ''),
        'Taxable (Rs.)' => number_format($net_pay, 2, '.', ''),
        'GST (Rs.)' => number_format($taxable, 2, '.', ''),
        'RndOff (Rs.)' => number_format($gst, 2, '.', ''),
        'NetPay (Rs.)' => number_format($round_off, 2, '.', ''),
        'CustomerName' => $customer_name,
      ];
    }
    $cleaned_params[count($cleaned_params)] = [
      'SNo.' => '',
      'Payment Mode' => 'T O T A L S',
      'Bill No. & Date' => '',
      'Gross Amount (Rs.)' => number_format($tot_gross_amount, 2, '.', ''),
      'Discount (Rs.)' => number_format($tot_discount, 2, '.', ''),
      'Taxable (Rs.)' => number_format($tot_net_pay, 2, '.', ''),
      'GST (Rs.)' => number_format($tot_taxable, 2, '.', ''),
      'RndOff (Rs.)' => number_format($tot_gst, 2, '.', ''),
      'NetPay (Rs.)' => number_format($tot_round_off, 2, '.', ''),
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
    $slno = $bill_qty = 0;
    $old_bill_no = $new_bill_no = $total_records[0]['invoiceNo'];
    foreach($total_records as $key => $record_details) {
      $slno++;
      $new_bill_no = $record_details['invoiceNo'];
      if($old_bill_no !== $new_bill_no) {

        $bill_total = $total_records[$key-1]['billAmount'];
        $bill_discount = $total_records[$key-1]['billDiscount'];
        $netpay =  $total_records[$key-1]['netpay'];

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
          'Item Rate' => '',
          'Gross Amt.' => '',
          'Discount' => '',
          'Net Amount' => '',
          'Customer Name' => '',
          'Remarks / Notes' => '',
        ];        

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

      $cleaned_params[] = [
        'Sl. No.' => $slno,
        'Bill No.' => $record_details['invoiceNo'],
        'Bill Date' => date("d-m-Y", strtotime($record_details['invoiceDate'])),
        'Item Name' => $record_details['itemName'],
        'Qty.' => number_format($record_details['soldQty'],2,'.',''),
        'CASE No.' => $record_details['cno'],
        'Item Rate' => number_format($record_details['mrp'],2,'.',''),
        'Gross Amt.' => number_format($item_amount,2,'.',''),
        'Discount' => number_format($record_details['itemDiscount'],2,'.',''),
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

    $tot_sold_qty += $bill_qty;
    $tot_amount += $bill_total;
    $tot_discount += $bill_discount;
    $tot_net_pay += $netpay;      

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
      'Net Amount' => number_format($tot_net_pay,2,'.',''),
      'Cust.Name' => '',
      'Remarks / Notes' => '',
    ];

    return $cleaned_params;
  }

}