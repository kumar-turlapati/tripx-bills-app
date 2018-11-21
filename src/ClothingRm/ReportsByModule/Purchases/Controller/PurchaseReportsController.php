<?php 

namespace ClothingRm\ReportsByModule\Purchases\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;
use Atawa\PDF;

use ClothingRm\Inward\Model\Inward;
use ClothingRm\Suppliers\Model\Supplier;

class PurchaseReportsController {

  protected $views_path;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->inward_api = new Inward;
    $this->flash = new Flash;
    $this->supplier_model = new Supplier;    
  }

  public function poRegister(Request $request) {
   
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 100;
    $total_records = $suppliers = $suppliers_a = [];

    $client_locations = Utilities::get_client_locations();
    $suppliers = $this->supplier_model->get_suppliers(0,0,[]);
    if($suppliers['status']) {
      $suppliers_a += $suppliers['suppliers'];
    }    

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_po_register_data($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
      } else {
        $error_message = 'Invalid Form Data.';
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/po-register');        
      }

      // hit api
      $po_response = $this->inward_api->get_purchases($page_no, $per_page, $form_data);
      if($po_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/po-register');
      } else {
        $total_records = $po_response['purchases'];
        $total_pages = $po_response['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $page_no = $i;
            $po_response = $this->inward_api->get_purchases($page_no, $per_page, $form_data);
            if($po_response['status']) {
              $total_records = array_merge($total_records, $po_response['purchases']);
            }
          }
        }

        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }

        $heading1 = 'Purchase Register';
        $heading2 = '( from '.$form_data['fromDate'].' to '.$form_data['toDate'].' )';
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }
        $csv_headings = [ [$heading1], [$heading2] ];
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        $total_records = $this->_format_po_register_for_csv($total_records);
        Utilities::download_as_CSV_attachment('PurchaseRegister', $csv_headings, $total_records);
        return;
      }

      // dump($total_records, $form_data);
      // exit;
      
      // start PDF printing.
      $item_widths = array(10,17,17,45,28,15,17,11,12,22,18,19,10,22,16,10,20);
                     ###### 0, 1, 2, 3, 4, 5, 6, 7, 8, 9,10,11,12,13,14,15,16
      $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3] +
                      $item_widths[4] + $item_widths[5] + $item_widths[6] + $item_widths[7] +
                      $item_widths[8];
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
      $pdf->Cell($item_widths[1],6,'PO No.','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'PO Date','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'Supplier Name','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'Invoice No.','RTB',0,'C');      
      $pdf->Cell($item_widths[5],6,'GRN No.','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'GRN Date','RTB',0,'C');
      $pdf->Cell($item_widths[7],6,'PMode','RTB',0,'C');  
      $pdf->Cell($item_widths[8],6,'Cr.Days','RTB',0,'C');
      $pdf->Cell($item_widths[9],6,'Gross (Rs.)','RTB',0,'C');
      $pdf->Cell($item_widths[10],6,'Disc.(Rs.)','RTB',0,'C');      
      $pdf->Cell($item_widths[11],6,'GST (Rs.)','RTB',0,'C');       
      $pdf->Cell($item_widths[12],6,'R.Off','RTB',0,'C');
      $pdf->Cell($item_widths[13],6,'Net Pay (Rs.)','RTB',0,'C');
      $pdf->Cell($item_widths[14],6,'Tot.Qty.','RTB',0,'C');      
      $pdf->SetFont('Arial','',8);

      $tot_gross = $tot_discount = $tot_tax = $tot_round_off = $tot_net_pay = $tot_qty = 0;
      foreach($total_records as $record_details) {
        $slno++;

        $tot_gross += $record_details['billAmount'];
        $tot_discount += $record_details['discountAmount'];
        $tot_tax += $record_details['taxAmount'];
        $tot_round_off += $record_details['roundOff'];
        $tot_net_pay += $record_details['netPay'];
        $tot_qty += $record_details['totalQty'];

        if((int)$record_details['paymentMethod'] === 1) {
          $payment_method = 'Credit';
          $credit_days = $record_details['creditDays'];
        } elseif((int)$record_details['paymentMethod'] === 0) {
          $payment_method = 'Cash';
          $credit_days = '';
        }
        
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$record_details['poNo'],'RTB',0,'R');
        $pdf->Cell($item_widths[2],6,date('d-m-Y', strtotime($record_details['purchaseDate'])),'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,substr($record_details['supplierName'],0,30),'RTB',0,'L');
        $pdf->Cell($item_widths[4],6,substr($record_details['billNo'],0,17),'RTB',0,'L');        
        $pdf->Cell($item_widths[5],6,$record_details['grnNo'],'RTB',0,'L');
        $pdf->Cell($item_widths[6],6,date('d-m-Y', strtotime($record_details['grnDate'])),'RTB',0,'L');
        $pdf->Cell($item_widths[7],6,$payment_method,'RTB',0,'L');
        $pdf->Cell($item_widths[8],6,$credit_days,'RTB',0,'C');
        $pdf->Cell($item_widths[9],6,number_format($record_details['billAmount'], 2, '.', ''),'RTB',0,'R');
        $pdf->Cell($item_widths[10],6,number_format($record_details['discountAmount'], 2, '.', ''),'RTB',0,'R');
        $pdf->Cell($item_widths[11],6,number_format($record_details['taxAmount'], 2, '.', ''),'RTB',0,'R');
        $pdf->Cell($item_widths[12],6,number_format($record_details['roundOff'], 2, '.', ''),'RTB',0,'R');
        $pdf->Cell($item_widths[13],6,number_format($record_details['netPay'], 2, '.', ''),'RTB',0,'R');
        $pdf->Cell($item_widths[14],6,$record_details['totalQty'],'RTB',0,'R');
      }
    
      $pdf->Ln();
      $pdf->SetFont('Arial','B',9);    
      $pdf->Cell($totals_width,6,'Register Totals','LB',0,'R');
      $pdf->Cell($item_widths[9],6,number_format($tot_gross, 2, '.', ''),'LB',0,'R');
      $pdf->Cell($item_widths[10],6,number_format($tot_discount, 2, '.', ''),'LB',0,'R');    
      $pdf->Cell($item_widths[11],6,number_format($tot_tax, 2, '.', ''),'LB',0,'R');
      $pdf->Cell($item_widths[12],6,number_format($tot_round_off, 2, '.', ''),'LB',0,'R');
      $pdf->Cell($item_widths[13],6,number_format($tot_net_pay, 2, '.', ''),'LB',0,'R');
      $pdf->Cell($item_widths[14],6,number_format($tot_qty, 2, '.', ''),'LRTB',0,'R');

      $pdf->Output();
    }

    $controller_vars = array(
      'page_title' => 'Purchase Register',
      'icon_name' => 'fa fa-print',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'suppliers' => array('' => 'All Suppliers') + $suppliers_a,
      'default_location' => $default_location,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
    );

    // render template
    return [$this->template->render_view('po-register', $template_vars), $controller_vars];
  }

  public function itemwisePoRegister(Request $request) {
   
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 300;
    $total_records = $suppliers = $suppliers_a = [];

    $client_locations = Utilities::get_client_locations();
    $suppliers = $this->supplier_model->get_suppliers(0,0,[]);
    if($suppliers['status']) {
      $suppliers_a += $suppliers['suppliers'];
    }    

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_po_register_itemwise_data($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
      } else {
        $error_message = 'Invalid Form Data.';
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/po-register-itemwise');
      }

      $form_data['pageNo'] = $page_no;
      $form_data['perPage'] = $per_page;

      // hit api
      $po_response = $this->inward_api->get_purchases_itemwise($form_data);
      if($po_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/po-register-itemwise');
      } else {
        $total_records = $po_response['response']['results'];
        $total_pages = $po_response['response']['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $page_no = $i;
            $form_data['pageNo'] = $page_no;
            $po_response = $this->inward_api->get_purchases_itemwise($form_data);
            if($po_response['status']) {
              $total_records = array_merge($total_records, $po_response['response']['results']);
            }
          }
        }

        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }

        $heading1 = 'Itemwise Purchase Register';
        $heading2 = '( from '.$form_data['fromDate'].' to '.$form_data['toDate'].' )';
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }
        $csv_headings = [ [$heading1], [$heading2] ];
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        $total_records = $this->_format_po_register_itemwise_for_csv($total_records);
        Utilities::download_as_CSV_attachment('PurchaseRegisterItemwise', $csv_headings, $total_records);
        return;
      }

      // dump($total_records);
      // exit;
      
      // start PDF printing.
      $item_widths = array(10,45,15,20,23,15,17,36,12,18,12,18,18,18);
                     ###### 0, 1, 2, 3, 4, 5, 6, 7, 8, 9,10,11,12,13,14,15,16
      $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3] +
                      $item_widths[4] + $item_widths[5] + $item_widths[6] + $item_widths[7] +
                      $item_widths[9] + $item_widths[10];
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

      $pdf->SetFont('Arial','B',7);
      $pdf->Ln(5);
      $pdf->Cell($item_widths[0],6,'SNo.','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'ItemName','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'HSN/SAC','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'BrandName','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'Lot No.','RTB',0,'C');      
      $pdf->Cell($item_widths[5],6,'PO No.','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'PO Date','RTB',0,'C');
      $pdf->Cell($item_widths[7],6,'SupplierName','RTB',0,'C');  
      $pdf->Cell($item_widths[9],6,'ItemRate (Rs.)','RTB',0,'C');
      $pdf->Cell($item_widths[10],6,'Tax (%)','RTB',0,'C');       
      $pdf->Cell($item_widths[8],6,'TotQty.','RTB',0,'C');
      $pdf->Cell($item_widths[11],6,'Gross (Rs.)','RTB',0,'C');
      $pdf->Cell($item_widths[12],6,'TaxAmt (Rs.)','RTB',0,'C');
      $pdf->Cell($item_widths[13],6,'Net (Rs.)','RTB',0,'C');      
      $pdf->SetFont('Arial','',7);

      $tot_gross = $tot_tax = $tot_net_pay = $tot_qty = 0;
      foreach($total_records as $record_details) {
        $slno++;

        $tot_gross += $record_details['taxableAmount'];
        $tot_tax += $record_details['taxValue'];
        $tot_net_pay += $record_details['netPay'];
        $tot_qty += $record_details['totalQty'];
        
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,substr($record_details['itemName'],0,25),'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$record_details['hsnSacCode'],'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,substr($record_details['brandName'],0,12),'RTB',0,'L');
        $pdf->Cell($item_widths[4],6,substr($record_details['lotNo'],0,13),'RTB',0,'L');        
        $pdf->Cell($item_widths[5],6,$record_details['poNo'],'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,date('d-m-Y', strtotime($record_details['poDate'])),'RTB',0,'L');
        $pdf->Cell($item_widths[7],6,substr($record_details['supplierName'],0,23),'RTB',0,'L');
        $pdf->Cell($item_widths[9],6,number_format($record_details['itemRate'], 2, '.', ''),'RTB',0,'R');
        $pdf->Cell($item_widths[10],6,number_format($record_details['taxPercent'], 2, '.', ''),'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,number_format($record_details['totalQty'], 2, '.', ''),'RTB',0,'R');
        $pdf->Cell($item_widths[11],6,number_format($record_details['taxableAmount'], 2, '.', ''),'RTB',0,'R');
        $pdf->Cell($item_widths[12],6,number_format($record_details['taxValue'], 2, '.', ''),'RTB',0,'R');
        $pdf->Cell($item_widths[13],6,number_format($record_details['netPay'], 2, '.', ''),'RTB',0,'R');
      }
      $pdf->Ln();
      $pdf->SetFont('Arial','B',8);    
      $pdf->Cell($totals_width,6,'Register Totals','LB',0,'R');
      $pdf->Cell($item_widths[8],6,number_format($tot_qty, 2, '.', ''),'LB',0,'R');
      $pdf->Cell($item_widths[11],6,number_format($tot_gross, 2, '.', ''),'LB',0,'R');
      $pdf->Cell($item_widths[12],6,number_format($tot_tax, 2, '.', ''),'LB',0,'R');
      $pdf->Cell($item_widths[13],6,number_format($tot_net_pay, 2, '.', ''),'LBR',0,'R');
      $pdf->Output();
    }

    $controller_vars = array(
      'page_title' => 'Purchase Register - Itemwise',
      'icon_name' => 'fa fa-print',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'suppliers' => array('' => 'All Suppliers') + $suppliers_a,
      'default_location' => $default_location,
      'format_options' => ['pdf'=>'PDF', 'csv' => 'CSV'],
    );

    // render template
    return [$this->template->render_view('po-register-itemwise', $template_vars), $controller_vars];
  }  

  private function _validate_po_register_data($form_data = []) {
    $cleaned_params = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $cleaned_params['locationCode'] = '';
    }

    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);
    $cleaned_params['fromDate'] = Utilities::clean_string($form_data['fromDate']);
    $cleaned_params['toDate'] = Utilities::clean_string($form_data['toDate']);
    $cleaned_params['supplierCode'] = Utilities::clean_string($form_data['supplierCode']);

    return ['status' => true, 'cleaned_params' => $cleaned_params];
  }

  private function _validate_po_register_itemwise_data($form_data = []) {
    $cleaned_params = $form_errors = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['locationCode'] = 'Invalid store name.';
    }
    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);
    $cleaned_params['fromDate'] = Utilities::clean_string($form_data['fromDate']);
    $cleaned_params['toDate'] = Utilities::clean_string($form_data['toDate']);
    $cleaned_params['supplierCode'] = Utilities::clean_string($form_data['supplierCode']);
    $cleaned_params['itemName'] = Utilities::clean_string($form_data['itemName']);
    $cleaned_params['brandName'] = Utilities::clean_string($form_data['brandName']);

    if(count($form_errors)>0) {
      return ['status' => false, 'errors' => $form_errors];
    } else {
      return ['status' => true, 'cleaned_params' => $cleaned_params];
    }
  }  

  private function _format_po_register_for_csv($total_records = []) {
    $cleaned_params = [];
    $cntr = 0;
    foreach($total_records as $key => $record_details) {
      $cntr++;
      if((int)$record_details['paymentMethod'] === 1) {
        $payment_method = 'Credit';
        $credit_days = $record_details['creditDays'];
      } elseif((int)$record_details['paymentMethod'] === 0) {
        $payment_method = 'Cash';
        $credit_days = 0;
      }      
      $cleaned_params[$key] = [
        'Sl. No.' => $cntr,
        'PO No.' => $record_details['poNo'],
        'PO Date' => date("d-m-Y", strtotime($record_details['purchaseDate']) ),
        'Supplier Name' => $record_details['supplierName'],
        'Invoice No.' => $record_details['billNo'],
        'GRN No.' => $record_details['grnNo'],
        'GRN Date' => date("d-m-Y", strtotime($record_details['grnDate'])),
        'Payment Method' => $payment_method,
        'Credit Days' => $credit_days,
        'Gross Amount' => $record_details['billAmount'],        
        'Discount' => $record_details['discountAmount'],        
        'GST' => $record_details['taxAmount'],        
        'Round Off' => $record_details['roundOff'],        
        'Net Pay' => $record_details['netPay'],        
        'Total Qty.' => $record_details['totalQty'],        
      ];
    }
    return $cleaned_params;
  }

  private function _format_po_register_itemwise_for_csv($total_records = []) {
    $cleaned_params = [];
    $cntr = 0;
    foreach($total_records as $key => $record_details) {
      $cntr++;
      $cleaned_params[$key] = [
        'Sl. No.' => $cntr,
        'Item Name' => $record_details['itemName'],
        'HSN / SAC' => $record_details['hsnSacCode'],
        'Brand Name' => $record_details['brandName'],
        'Lot No.' => $record_details['lotNo'],
        'PO No.' => $record_details['poNo'],
        'PO Date' => date('d-m-Y', strtotime($record_details['poDate'])),
        'Supplier Name' => $record_details['supplierName'],
        'Item Rate (in Rs.)' => $record_details['itemRate'],
        'Tax (in %)' => $record_details['taxPercent'],        
        'Total Qty.' => $record_details['totalQty'],        
        'Gross (in Rs.)' => $record_details['taxableAmount'],        
        'Tax Amount (in Rs.)' => $record_details['taxValue'],        
        'Net (Rs.)' => $record_details['netPay'],        
      ];
    }
    return $cleaned_params;
  }  
}