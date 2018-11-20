<?php 

namespace ClothingRm\ReportsByModule\Finance\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;
use Atawa\PDF;

use ClothingRm\Finance\Model\Finance;
use ClothingRm\Suppliers\Model\Supplier;

class FinReportsController {

  protected $views_path;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->fin_api = new Finance;
    $this->flash = new Flash;
    $this->supplier_model = new Supplier;    
  }

  // payables section
  public function payablesAction(Request $request) {
   
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 30;
    $total_records = $suppliers_a = [];
    $aging_logic = false;

    $client_locations = Utilities::get_client_locations();
    $suppliers = $this->supplier_model->get_suppliers(0,0,[]);
    if($suppliers['status']) {
      $suppliers_a += $suppliers['suppliers'];
    }    

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_payables_data($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
        $form_data['pageNo'] = $page_no;
        $form_data['perPage'] = $per_page;
      } else {
        $error_message = 'Invalid Filters. Please try again.';
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/payables');
      }

      // hit api
      $payables_response = $this->fin_api->get_payables($form_data);
      if($payables_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/item-master');
      } else {
        $total_records = $payables_response['response']['records'];
        $total_pages = $payables_response['response']['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $form_data['pageNo'] = $i;
            $payables_response = $this->fin_api->get_payables($form_data);
            if($payables_response['status']) {
              $total_records = array_merge($total_records, $payables_response['response']['records']);
            }
          }
        }

        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }

        $heading1 = 'Payables - '.$location_name;
        $heading2 = 'As on '.date('jS F, Y');
        $csv_headings = [ [$heading1], [$heading2]];
      }

      if($form_data['aging1'] > 0 && $form_data['aging2'] > 0 && $form_data['aging3'] > 0 && $form_data['aging4'] > 0 ) {
        $aging1 = '0 - '.$form_data['aging1'];
        $aging2 = '>'.$form_data['aging1'].' & <='.$form_data['aging2'];
        $aging3 = '>'.$form_data['aging2'].' & <='.$form_data['aging3'];
        $aging4 = '>='.$form_data['aging4'];
        $aging_logic = true;     
      } else {
        $aging1 = '';
        $aging2 = '';
        $aging3 = '';
        $aging4 = '';
      }

      // format data for aging logic.
      $total_records = $this->_format_data_for_payables_aging_logic($total_records, $aging_logic, $form_data['aging1'], $form_data['aging2'], $form_data['aging3'], $form_data['aging4']);
      $format = $form_data['format'];
      if($format === 'csv') {
        $total_records = $this->_format_data_for_payables($total_records, $aging_logic, $form_data['aging1'], $form_data['aging2'], $form_data['aging3'], $form_data['aging4']);
        Utilities::download_as_CSV_attachment('Payables', $csv_headings, $total_records);
        return;
      }
      
      // start PDF printing.
      $item_widths = array(8, 40, 30, 26, 17, 10, 21, 20, 21, 22, 22, 22, 22);
                      //    0,  1,  2,  3,  4,  5,  6,  7,  8,  9, 10, 11, 12

      $slno = $tot_aging1 = $tot_aging2 = $tot_aging3 = $tot_aging4 = 0;
      $total_bill_amount = $total_debits = $total_balance = 0;

      $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3] +
                      $item_widths[4] + $item_widths[5];
      $aging_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3] +
                     $item_widths[4] + $item_widths[5] + $item_widths[6] + $item_widths[7] +
                     $item_widths[8];

      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('L','A4');
      $pdf->setTitle($heading1.' - '.date('jS F, Y'));

      $pdf->SetFont('Arial','B',16);
      $pdf->Cell(0,5,$heading1,'',1,'C');
      $pdf->SetFont('Arial','B',10);
      $pdf->Cell(0,5,$heading2,'',1,'C');

      $pdf->Cell($aging_width,6,'','',0,'C');
      $pdf->Cell($item_widths[9]+$item_widths[10]+$item_widths[11]+$item_widths[12],6,'Days Bucket','LRTB',1,'C');

      $pdf->SetFont('Arial','B',8);
      $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'SupplierName','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'Address','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'Bill No.','RTB',0,'C');      
      $pdf->Cell($item_widths[4],6,'Bill Date','RTB',0,'C');      
      $pdf->Cell($item_widths[5],6,'CDays','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'BillAmt','RTB',0,'C');
      $pdf->Cell($item_widths[7],6,'Debits','RTB',0,'C');
      $pdf->Cell($item_widths[8],6,'Balance','RTB',0,'C');
      $pdf->Cell($item_widths[9],6,$aging1,'RTB',0,'C');      
      $pdf->Cell($item_widths[10],6,$aging2,'RTB',0,'C');      
      $pdf->Cell($item_widths[11],6,$aging3,'RTB',0,'C');      
      $pdf->Cell($item_widths[12],6,$aging4,'RTB',0,'C');      
      $pdf->SetFont('Arial','',8);

      // dump($total_records);
      // exit;
      $slno = 0;
      foreach($total_records as $record_details) {
        
        $total_bill_amount += $record_details['billAmount'];
        $total_debits += $record_details['debitAmount'] + $record_details['paidAmount'];
        $total_balance += $record_details['balAmount'];

        $slno++;
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,substr($record_details['supplierName'],0,22),'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,'','RTB',0,'L');
        $pdf->Cell($item_widths[3],6,substr($record_details['billNo'],0,17),'RTB',0,'L');
        $pdf->Cell($item_widths[4],6,date("d-m-Y", strtotime($record_details['billDate'])),'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,$record_details['creditDays'],'RTB',0,'R');      
        $pdf->Cell($item_widths[6],6,$record_details['billAmount'],'RTB',0,'R');      
        $pdf->Cell($item_widths[7],6,$record_details['debitAmount'],'RTB',0,'R');      
        $pdf->Cell($item_widths[8],6,$record_details['balAmount'],'RTB',0,'R');
        $pdf->Cell($item_widths[9],6,$record_details['aging1'] > 0 ? $record_details['aging1'] : '','RTB',0,'R');
        $pdf->Cell($item_widths[10],6,$record_details['aging2'] > 0 ? $record_details['aging2'] : '','RTB',0,'R');
        $pdf->Cell($item_widths[11],6,$record_details['aging3'] > 0 ? $record_details['aging3'] : '','RTB',0,'R');
        $pdf->Cell($item_widths[12],6,$record_details['aging4'] > 0 ? $record_details['aging4'] : '','RTB',0,'R');

        $tot_aging1 += $record_details['aging1'];
        $tot_aging2 += $record_details['aging2'];
        $tot_aging3 += $record_details['aging3'];
        $tot_aging4 += $record_details['aging4'];
      }

      $pdf->Ln();
      $pdf->SetFont('Arial','B',9);
      $pdf->Cell($totals_width,6,'T O T A L S','LRTB',0,'R');
      $pdf->Cell($item_widths[6],6,number_format($total_bill_amount, 2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[7],6,number_format($total_debits, 2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[8],6,number_format($total_balance, 2, '.', ''),'RTB',0,'R');
      $pdf->Cell($item_widths[9],6,$tot_aging1 > 0 ? number_format($tot_aging1, 2, '.', '') : '','RTB',0,'R');
      $pdf->Cell($item_widths[10],6,$tot_aging2 > 0 ? number_format($tot_aging2, 2, '.', '') : '','RTB',0,'R');
      $pdf->Cell($item_widths[11],6,$tot_aging3 > 0 ? number_format($tot_aging3, 2, '.', '') : '','RTB',0,'R');
      $pdf->Cell($item_widths[12],6,$tot_aging4 > 0 ? number_format($tot_aging4, 2, '.', '') : '','RTB',0,'R');

      $pdf->Output();
    }

    $controller_vars = array(
      'page_title' => 'Print Payables',
      'icon_name' => 'fa fa-print',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
      'suppliers' => array('' => 'All Suppliers') + $suppliers_a,      
    );

    // render template
    return [$this->template->render_view('payables', $template_vars), $controller_vars];
  }

  private function _format_data_for_payables_aging_logic($total_records=[], $aging_logic=false, $aging1=0, $aging2=0, $aging3=0, $aging4=0) {
    foreach($total_records as $record_key => $record_details) {
      if($aging_logic) {
        $current_date = date_create(date("Y-m-d"));
        $due_date = date_create($record_details['dueDate']);
        $diff_obj = date_diff($current_date,$due_date);
        $diff_days = $diff_obj->format("%a");
        $total_records[$record_key]['diff_days'] = $diff_days;
        if($diff_days >= 0 && $diff_days <= $aging1) {
          $total_records[$record_key]['aging1'] = $record_details['balAmount'];
          $total_records[$record_key]['aging2'] = 0;        
          $total_records[$record_key]['aging3'] = 0;        
          $total_records[$record_key]['aging4'] = 0;
        } elseif($diff_days > $aging1 && $diff_days <= $aging2) {
          $total_records[$record_key]['aging1'] = 0;        
          $total_records[$record_key]['aging2'] = $record_details['balAmount'];
          $total_records[$record_key]['aging3'] = 0;        
          $total_records[$record_key]['aging4'] = 0;        
        } elseif($diff_days > $aging1 && $diff_days <= $aging2) {
          $total_records[$record_key]['aging1'] = 0;        
          $total_records[$record_key]['aging2'] = 0;
          $total_records[$record_key]['aging3'] = $record_details['balAmount'];
          $total_records[$record_key]['aging4'] = 0;        
        } else {
          $total_records[$record_key]['aging1'] = 0;        
          $total_records[$record_key]['aging2'] = 0;        
          $total_records[$record_key]['aging3'] = 0;
          $total_records[$record_key]['aging4'] = $record_details['balAmount'];
        }
      } else {
        $total_records[$record_key]['aging1'] = 0;
        $total_records[$record_key]['aging2'] = 0;        
        $total_records[$record_key]['aging3'] = 0;        
        $total_records[$record_key]['aging4'] = 0;        
      }
    }

    return $total_records;
  }

  private function _validate_payables_data($form_data = []) {

    $cleaned_params = $form_errors = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $cleaned_params['locationCode'] = '';
    }
    if($form_data['supplierCode'] !== '') {
      $cleaned_params['supplierCode'] = Utilities::clean_string($form_data['supplierCode']);
    } else {
      $cleaned_params['supplierCode'] = '';
    }

    if($form_data['aging1'] !== '') {
      if(is_numeric($form_data['aging1']) && is_int((int)$form_data['aging1'])) {
        $cleaned_params['aging1'] = Utilities::clean_string($form_data['aging1']);
      } else {
        $form_errors['aging1'] = 'Invalid';
      }
    } else {
      $cleaned_params['aging1'] = 0;
    }
    if($form_data['aging2'] !== '') {    
      if(is_numeric($form_data['aging2']) && is_int((int)$form_data['aging2'])) {
        $cleaned_params['aging2'] = Utilities::clean_string($form_data['aging2']);
      } else {
        $form_errors['aging2'] = 'Invalid';
      }
    } else {
      $cleaned_params['aging2'] = 0;
    }
    if($form_data['aging3'] !== '') {
      if(is_numeric($form_data['aging3']) && is_int((int)$form_data['aging3'])) {
        $cleaned_params['aging3'] = Utilities::clean_string($form_data['aging3']);
      } else {
        $form_errors['aging3'] = 'Invalid';
      }
    } else {
      $cleaned_params['aging3'] = 0;
    }
    if($form_data['aging4'] !== '') {    
      if(is_numeric($form_data['aging4']) && is_int((int)$form_data['aging4'])) {
        $cleaned_params['aging4'] = Utilities::clean_string($form_data['aging4']);
      } else {
        $form_errors['aging4'] = 'Invalid';
      }
    } else {
      $cleaned_params['aging4'] = 0;
    }
    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);
    if(count($form_errors)>0) {
      return ['status' => false, 'form_errors' => $form_errors];
    } else {
      return ['status' => true, 'cleaned_params' => $cleaned_params];
    }
  }
  
  private function _format_data_for_payables($total_records = [], $aging_logic=false, $aging1=0, $aging2=0, $aging3=0, $aging4=0) {
    $cleaned_params = [];
    $slno = 0;
    if($aging1 > 0 && $aging2 > 0 && $aging3 > 0 && $aging4 > 0 ) {
      $aging1_string = '0 - '.$aging1;
      $aging2_string = '>'.$aging1.' & <='.$aging2;
      $aging3_string = '>'.$aging2.' & <='.$aging3;
      $aging4_string = '>='.$aging4;
      $aging_logic = true;     
    } else {
      $aging1_string = '';
      $aging2_string = '';
      $aging3_string = '';
      $aging4_string = '';
    }    
    foreach($total_records as $key => $record_details) {
      $slno++;
      $cleaned_params[$key] = [
        'Sl. No.' => $slno,
        'Supplier Name' => $record_details['supplierName'],
        'Address' => $record_details['cityName'],
        'Bill No.' => $record_details['billNo'],
        'Bill Date' => date("d-m-Y", strtotime($record_details['billDate'])),
        'Credit Days' => $record_details['creditDays'],
        'Bill Amount' => $record_details['billAmount'],
        'Debits' => $record_details['debitAmount'] + $record_details['paidAmount'],
        'Balance' => $record_details['balAmount'],
        $aging1_string => $record_details['aging1'],
        $aging2_string => $record_details['aging2'],
        $aging3_string => $record_details['aging3'],
        $aging4_string => $record_details['aging4'],
      ];
    }

    return $cleaned_params;
  }

  // receivables section
  public function receivablesAction(Request $request) {
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 10;
    $total_records = $categories_a = [];
    $customer_types = ['' => 'All Customers'] + Constants::$CUSTOMER_TYPES; 

    $states_a = Constants::$LOCATION_STATES;
    asort($states_a);
    $countries_a = Constants::$LOCATION_COUNTRIES;    

    $client_locations = Utilities::get_client_locations();

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_customer_master_data($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
        $form_data['pageNo'] = $page_no;
        $form_data['perPage'] = $per_page;
      } else {
        $error_message = 'Invalid Form Data.';
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/customer-master');        
      }

      // hit api
      $customers_api_response = $this->customer_api->get_customers($form_data);
      if($customers_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/customer-master');
      } else {
        $total_records = $customers_api_response['customers'];
        $total_pages = $customers_api_response['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $form_data['pageNo'] = $i;
            $customers_api_response = $this->customer_api->get_customers($form_data);
            if($customers_api_response['status']) {
              $total_records = array_merge($total_records, $customers_api_response['customers']);
            }
          }
        }

        $total_records = $this->_add_state_name($total_records, $states_a);
        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }

        $heading1 = 'Customer Master';
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }        
        $heading2 = 'As on '.date('jS F, Y');
        $csv_headings = [ [$heading1], [$heading2] ];
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        $total_records = $this->_format_data_for_customer_master($total_records);
        Utilities::download_as_CSV_attachment('CustomerMasterAsonDate', $csv_headings, $total_records);
        return;
      }
      
      // start PDF printing.
      $item_widths = array(8,10,40,35,22,20,13,18,28);
      $slno = 0;

      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('P','A4');
      $pdf->setTitle($heading1.' - '.date('jS F, Y'));

      $pdf->SetFont('Arial','B',14);
      $pdf->Cell(0,5,$heading1,'',1,'C');
      $pdf->SetFont('Arial','B',10);
      $pdf->Cell(0,5,$heading2,'',1,'C');

      $pdf->SetFont('Arial','B',8);
      $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Ctype','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'Customer Name','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'Address','RTB',0,'C');        
      $pdf->Cell($item_widths[4],6,'City Name','RTB',0,'C');        
      $pdf->Cell($item_widths[5],6,'State Name','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'Pincode','RTB',0,'C');
      $pdf->Cell($item_widths[7],6,'Mobile No.','RTB',0,'C');
      $pdf->Cell($item_widths[8],6,'GST No.','RTB',0,'C');
      $pdf->SetFont('Arial','',8);
      
      $slno = 0;
      foreach($total_records as $record_details) {
        $slno++;
        $customer_type = $record_details['customerType'] === 'b' ? 'B2B' : 'B2C';
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$customer_type,'RTB',0,'R');
        $pdf->Cell($item_widths[2],6,substr($record_details['customerName'], 0, 25),'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,substr($record_details['address'], 0, 24),'RTB',0,'L');
        $pdf->Cell($item_widths[4],6,substr($record_details['cityName'], 0, 10),'RTB',0,'L');
        $pdf->Cell($item_widths[5],6,substr($record_details['stateName'],0,10),'RTB',0,'L');
        $pdf->Cell($item_widths[6],6,$record_details['pincode'],'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,$record_details['mobileNo'],'RTB',0,'R');      
        $pdf->Cell($item_widths[8],6,$record_details['gstNo'],'RTB',0,'R');      
      }

      $pdf->Output();
    }

    $controller_vars = array(
      'page_title' => 'Print Receivables',
      'icon_name' => 'fa fa-print',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
      'customer_types' => $customer_types,
      'states' => [0=>'Choose'] + $states_a,
      'countries' => [0=>'Choose'] + $countries_a,
    );

    // render template
    return [$this->template->render_view('customer-master', $template_vars), $controller_vars];
  }  

  private function _validate_receivables_data($form_data = []) {
    $cleaned_params = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $cleaned_params['locationCode'] = '';
    }
    if($form_data['customerType'] !== '') {
      $cleaned_params['customerType'] = Utilities::clean_string($form_data['customerType']);
    } else {
      $cleaned_params['customerType'] = 'b2c';
    }
    
    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);

    return ['status' => true, 'cleaned_params' => $cleaned_params];
  }   

  private function _format_data_for_receivables($total_records = []) {
    $cleaned_params = [];
    $slno = 0;
    foreach($total_records as $key => $record_details) {
      $slno++;
      $customer_type = $record_details['customerType'] === 'b' ? 'B2B' : 'B2C';
      $cleaned_params[$key] = [
        'Sl. No.' => $slno,
        'Customer Type' => $customer_type,
        'Customer Name' => $record_details['customerName'],
        'Address' => $record_details['address'],
        'City Name' => $record_details['cityName'],
        'State Name' => $record_details['stateName'],
        'Pincode' => $record_details['pincode'],
        'Mobile No.' => $record_details['mobileNo'],
        'GST No.' => $record_details['gstNo'],
      ];
    }
    return $cleaned_params;    
  }
}