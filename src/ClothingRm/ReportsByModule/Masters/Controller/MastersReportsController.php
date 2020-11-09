<?php 

namespace ClothingRm\ReportsByModule\Masters\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;
use Atawa\PDF;

use ClothingRm\Products\Model\Products;
use ClothingRm\Inventory\Model\Inventory;
use ClothingRm\Customers\Model\Customers;

class MastersReportsController {

  protected $views_path;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->products_api = new Products;
    $this->inven_api = new Inventory;
    $this->customer_api = new Customers;    
    $this->flash = new Flash;
  }

  public function itemMasterReport(Request $request) {
   
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 1000;
    $total_records = $categories_a = [];

    $client_locations = Utilities::get_client_locations();
    $categories_a = $this->products_api->get_product_categories();

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_item_master_data($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
        $form_data['pageNo'] = $page_no;
        $form_data['perPage'] = $per_page;
      } else {
        $error_message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error: '.json_encode($validation['form_errors']);
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/item-master');        
      }

      // hit api
      $inven_api_response = $this->inven_api->item_master_with_pp($form_data);
      if($inven_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/item-master');
      } else {
        $total_records = $inven_api_response['response']['items'];
        $total_pages = $inven_api_response['response']['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $form_data['pageNo'] = $i;
            $inven_api_response = $this->inven_api->item_master_with_pp($form_data);
            if($inven_api_response['status']) {
              $total_records = array_merge($total_records, $inven_api_response['response']['items']);
            }
          }
        }

        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }

        $heading1 = 'Item Master - '.$location_name;
        $heading2 = 'As on '.date('jS F, Y');
        $heading3 = '';
        if($form_data['brandName'] !== '') {
          $heading3 .= 'Brand Name: '.$form_data['brandName'];
        }
        if($form_data['categoryCode'] !== '') {
          $category_name = $categories_a[$form_data['categoryCode']];
          if($heading3 !== '') {
            $heading3 .= ', ';
          }
          $heading3 .= 'Category Name: '.$category_name;
        }
        $csv_headings = [ [$heading1], [$heading2], [$heading3] ];
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        $total_records = $this->_format_data_for_item_master($total_records);
        Utilities::download_as_CSV_attachment('ItemMasterAsonDate', $csv_headings, $total_records);
        return;
      }
      
      // start PDF printing.
      $item_widths = array(10,33,57,25,25,25,15);
      $slno = $tot_amount = $tot_qty = $tot_amount_mrp = 0;

      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('P','A4');
      $pdf->setTitle($heading1.' - '.date('jS F, Y'));

      $pdf->SetFont('Arial','B',16);
      $pdf->Cell(0,5,$heading1,'',1,'C');
      $pdf->SetFont('Arial','B',10);
      $pdf->Cell(0,5,$heading2,'',1,'C');
      if($heading3 !== '') {
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(0,5,$heading3,'B',1,'C');
      }

      $pdf->SetFont('Arial','B',9);
      $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Item Code','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'Item Name','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'Category Name','RTB',0,'C');        
      $pdf->Cell($item_widths[4],6,'Brand Name','RTB',0,'C');        
      $pdf->Cell($item_widths[5],6,'Landing Cost','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'TaxRate','RTB',0,'C');      
      
      $pdf->SetFont('Arial','',9);
      
      $slno = 0;
      foreach($total_records as $item_details) {
        $slno++;

        $item_code = $item_details['itemCode'];
        $item_name = substr($item_details['itemName'],0,33);
        $category_name = substr($item_details['categoryName'],0,12);
        $brand_name = substr($item_details['mfgName'],0,12);
        $item_rate = $item_details['itemRate'];
        $tax_percent = $item_details['taxPercent'];
        
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$item_code,'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$item_name,'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,$category_name,'RTB',0,'L');            
        $pdf->Cell($item_widths[4],6,$brand_name,'RTB',0,'L');
        $pdf->Cell($item_widths[5],6,number_format($item_rate,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,number_format($tax_percent,2,'.',''),'RTB',0,'R');      
      }
      $pdf->Output();
    }

    $controller_vars = array(
      'page_title' => 'Print Item Master',
      'icon_name' => 'fa fa-print',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'categories' => array(''=>'All Categories') + $categories_a,
      'default_location' => $default_location,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
    );

    // render template
    return [$this->template->render_view('item-master', $template_vars), $controller_vars];
  }

  public function itemMasterWithBarcodes(Request $request) {
   
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 1000;
    $total_records = $categories_a = [];

    $client_locations = Utilities::get_client_locations();
    $categories_a = $this->products_api->get_product_categories();

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_item_master_data($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
        $form_data['pageNo'] = $page_no;
        $form_data['perPage'] = $per_page;
      } else {
        $error_message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error: '.json_encode($validation['form_errors']);
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/item-master-with-barcodes');        
      }

      // hit api
      $inven_api_response = $this->inven_api->item_master_with_barcodes($form_data);
      if($inven_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/item-master-with-barcodes');
      } else {
        $total_records = $inven_api_response['response']['items'];
        $total_pages = $inven_api_response['response']['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $form_data['pageNo'] = $i;
            $inven_api_response = $this->inven_api->item_master_with_barcodes($form_data);
            if($inven_api_response['status']) {
              $total_records = array_merge($total_records, $inven_api_response['response']['items']);
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

        $heading1 = 'Barcode Register with Closing Balances - '.$location_name;
        $heading2 = 'As on '.date('jS F, Y');
        $heading3 = '';
        if($form_data['brandName'] !== '') {
          $heading3 .= 'Brand Name: '.$form_data['brandName'];
        }
        if($form_data['categoryCode'] !== '') {
          $category_name = $categories_a[$form_data['categoryCode']];
          if($heading3 !== '') {
            $heading3 .= ', ';
          }
          $heading3 .= 'Category Name: '.$category_name;
        }
        $csv_headings = [ [$heading1], [$heading2], [$heading3] ];
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        $total_records = $this->_format_data_for_item_master_with_barcodes($total_records);
        Utilities::download_as_CSV_attachment('BarcodeRegister', $csv_headings, $total_records);
        return;
      }
      
      // start PDF printing.
      $item_widths = array(10,24,40,25,23,23,18,14,14);
      $slno = $tot_amount = $tot_qty = $tot_amount_mrp = 0;

      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('P','A4');
      $pdf->setTitle($heading1.' - '.date('jS F, Y'));

      $pdf->SetFont('Arial','B',14);
      $pdf->Cell(0,5,$heading1,'',1,'C');
      $pdf->SetFont('Arial','B',9);
      $pdf->Cell(0,5,$heading2,'',1,'C');
      if($heading3 !== '') {
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(0,5,$heading3,'B',1,'C');
      }

      $pdf->SetFont('Arial','B',8);
      $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Barcode','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'Item Name','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'Lot No.','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'Category','RTB',0,'C');        
      $pdf->Cell($item_widths[5],6,'Brand','RTB',0,'C');        
      $pdf->Cell($item_widths[6],6,'Purch.Rate','RTB',0,'C');
      $pdf->Cell($item_widths[7],6,'Clos.Qty.','RTB',0,'C');      
      $pdf->Cell($item_widths[8],6,'MRP','RTB',0,'C');      
      $pdf->SetFont('Arial','',8);
      
      $slno = 0;
      $total_purch_value = $tot_closing_qty = 0;
      foreach($total_records as $item_details) {
        $slno++;

        $barcode = $item_details['barcode'];
        $item_name = substr($item_details['itemName'],0,22);
        $lot_no = $item_details['lotNo'];
        $category_name = substr($item_details['categoryName'],0,10);
        $brand_name = substr($item_details['mfgName'],0,10);
        $purchase_rate = $item_details['purchaseRate'];
        $closing_qty = $item_details['closingQty'];
        $mrp = $item_details['mrp'];

        $tot_closing_qty += $closing_qty;
        $total_purch_value += ($purchase_rate * $closing_qty);
        
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$barcode,'LRTB',0,'R');
        $pdf->Cell($item_widths[2],6,$item_name,'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,$lot_no,'RTB',0,'L');
        $pdf->Cell($item_widths[4],6,$category_name,'RTB',0,'L');            
        $pdf->Cell($item_widths[5],6,$brand_name,'RTB',0,'L');
        $pdf->Cell($item_widths[6],6,number_format($purchase_rate,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,number_format($closing_qty,2,'.',''),'RTB',0,'R');      
        $pdf->Cell($item_widths[8],6,number_format($mrp,2,'.',''),'RTB',0,'R');      
      }
      $pdf->SetFont('Arial','B',8);
      $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] +
                      $item_widths[3] + $item_widths[4] + $item_widths[5];
      $pdf->Ln();
      $pdf->Cell($totals_width,6,'T O T A L S','LRB',0,'R');
      $pdf->Cell($item_widths[6],6,number_format($total_purch_value,2,'.',''),'RB',0,'R');      
      $pdf->Cell($item_widths[7],6,number_format($tot_closing_qty,2,'.',''),'RB',0,'R');      
      $pdf->Output();
    }

    $controller_vars = array(
      'page_title' => 'Print Barcode Register with Available Qtys.',
      'icon_name' => 'fa fa-print',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'categories' => array(''=>'All Categories') + $categories_a,
      'default_location' => $default_location,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
    );

    // render template
    return [$this->template->render_view('item-master-with-barcodes', $template_vars), $controller_vars];
  }  

  public function customerMasterReport(Request $request) {
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 1000;
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
        $error_message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error: '.json_encode($validation['form_errors']);
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
      'page_title' => 'Print Customer Master',
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

  private function _validate_item_master_data($form_data = []) {
    $cleaned_params = $form_errors = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['StoreName'] = 'Invalid Store Name.';
    }
    if($form_data['categoryCode'] !== '') {
      $cleaned_params['categoryCode'] = Utilities::clean_string($form_data['categoryCode']);
    } else {
      $cleaned_params['categoryCode'] = '';
    }
    if($form_data['brandName'] !== '') {
      $cleaned_params['brandName'] = Utilities::clean_string($form_data['brandName']);
    } else {
      $cleaned_params['brandName'] = '';
    }
    if($form_data['barcodeOption']) {
      $cleaned_params['zeroBarcodes'] = true;
    }

    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);    

    if(count($form_errors) > 0) {
      return ['status' => false, 'form_errors' => $form_errors];
    } else {
      return ['status' => true, 'cleaned_params' => $cleaned_params];
    }
  }

  private function _validate_customer_master_data($form_data = []) {
    $cleaned_params = $form_errors = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['StoreName'] = 'Invalid Store Name.';
    }
    if($form_data['customerType'] !== '') {
      $cleaned_params['customerType'] = Utilities::clean_string($form_data['customerType']);
    } else {
      $cleaned_params['customerType'] = 'b2c';
    }
    
    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);

    if(count($form_errors) > 0) {
      return ['status' => false, 'form_errors' => $form_errors];
    } else {
      return ['status' => true, 'cleaned_params' => $cleaned_params];
    }
  }  
  
  private function _format_data_for_item_master($total_records = []) {
    $cleaned_params = [];
    foreach($total_records as $key => $record_details) {
      $cleaned_params[$key] = [
        'Item Code' => $record_details['itemCode'],
        'Item Name' => $record_details['itemName'],
        'Category Name' => $record_details['categoryName'],
        'Brand Name' => $record_details['mfgName'],
        'Recent Landing Cost' => $record_details['itemRate'],
        'Tax Percent' => $record_details['taxPercent'],
      ];
    }
    return $cleaned_params;
  }

  private function _format_data_for_item_master_with_barcodes($total_records = []) {
    $cleaned_params = [];

    $slno = $total_purch_value = $tot_closing_qty = 0;
    foreach($total_records as $key => $item_details) {
      $slno++;

      $barcode = $item_details['barcode'];
      $item_name = $item_details['itemName'];
      $lot_no = $item_details['lotNo'];
      $category_name = $item_details['categoryName'];
      $brand_name = $item_details['mfgName'];
      $purchase_rate = $item_details['purchaseRate'];
      $closing_qty = $item_details['closingQty'];
      $mrp = $item_details['mrp'];

      $cleaned_params[$key] = [
        'Sl.No.' => $slno,
        'Barcode' => $barcode,
        'Item Name' => $item_name,
        'Lot No.' => $lot_no,
        'Category' => $category_name,
        'Brand' => $brand_name,
        'Purchase Rate' => number_format($purchase_rate, 2, '.', ''),
        'Closing Qty.' => number_format($closing_qty, 2, '.', ''),
        'M.R.P' => number_format($mrp, 2, '.', ''),
      ];

      $tot_closing_qty += $closing_qty;
      $total_purch_value += ($purchase_rate * $closing_qty);
    }

    $cleaned_params[count($cleaned_params)] = [
      'Sl.No.' => 'T O T A L S',
      'Barcode' => '',
      'Item Name' => '',
      'Lot No.' => '',
      'Category' => '',
      'Brand' => '',
      'Purchase Rate' => number_format($total_purch_value, 2, '.', ''),
      'Closing Qty.' => number_format($tot_closing_qty, 2, '.', ''),
      'M.R.P' => '',
    ];

    return $cleaned_params;
  }

  private function _format_data_for_customer_master($total_records = []) {
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

  private function _add_state_name($total_records=[], $states_a=[]) {
    foreach($total_records as $key => $record_details) {
      $state_name = isset($states_a[$record_details['stateID']]) ? $states_a[$record_details['stateID']] : '';
      $total_records[$key]['stateName'] = $state_name;
    }
    return $total_records;
  }
}