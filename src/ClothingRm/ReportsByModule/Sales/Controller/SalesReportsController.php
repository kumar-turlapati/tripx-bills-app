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
      'sa_executives' => $sa_executives,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
    );

    // render template
    return [$this->template->render_view('print-sales-register', $template_vars), $controller_vars];
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

    $cleaned_params['format'] =  $form_data['format'];

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
      $new_records[$key]['CustomerName'] = substr($customer_name,0,20);
    }
    return $new_records;
  }
}