<?php 

namespace ClothingRm\ReportsByModule\Inventory\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;
use Atawa\PDF;

use ClothingRm\Inventory\Model\Inventory;
use ClothingRm\Products\Model\Products;
use ClothingRm\Openings\Model\Openings;

class InventoryReportsController {

  protected $views_path;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->inven_api = new Inventory;
    $this->products_api = new Products;
    $this->flash = new Flash;
    $this->opbal_model = new Openings;    
  }

  public function stockReport(Request $request) {
   
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 300;
    $total_records = $categories_a = [];

    $client_locations = Utilities::get_client_locations();
    $categories_a = $this->products_api->get_product_categories();

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_stock_report_data($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
        $form_data['pageNo'] = $page_no;
        $form_data['perPage'] = $per_page;
      } else {
        $error_message = 'Invalid Form Data.';
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-register');        
      }

      // hit api
      $inven_api_response = $this->inven_api->get_stock_report($form_data);
      if($inven_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/stock-report');
      } else {
        $total_records = $inven_api_response['results']['results'];
        $total_pages = $inven_api_response['results']['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $form_data['pageNo'] = $i;
            $inven_api_response = $this->inven_api->get_stock_report($form_data);
            if($inven_api_response['status']) {
              $total_records = array_merge($total_records, $inven_api_response['results']['results']);
            }
          }
        }

        // $total_records = $this->_format_data_for_sales_register($total_records);
        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }

        $heading1 = 'Stock Report - '.$location_name;
        $heading2 = 'As on '.date('jS F, Y');
        $heading3 = '';
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }
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
        $total_records = $this->_format_stock_report_for_csv($total_records);
        Utilities::download_as_CSV_attachment('StockReport', $csv_headings, $total_records);
        return;
      }
      
      // start PDF printing.
      $item_widths = array(10,48,28,13,18,20,20,17,17);
      $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3];
      $totals_width1 = $item_widths[5] + $item_widths[6];
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
      $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'Lot No.','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'GST(%)','RTB',0,'C');        
      $pdf->Cell($item_widths[4],6,'Clos.Qty.','RTB',0,'C');        
      $pdf->Cell($item_widths[5],6,'Rate/Unit','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'Amount','RTB',0,'C');
      $pdf->Cell($item_widths[7],6,'M.R.P','RTB',0,'C');
      $pdf->Cell($item_widths[8],6,'Amount','RTB',0,'C');    
      
      $pdf->SetFont('Arial','',9);

      foreach($total_records as $item_details) {
        $slno++;

        $item_name = $item_details['itemName'];
        $lot_no = $item_details['lotNo'];
        $closing_qty = $item_details['closingQty'];
        $closing_rate = $item_details['purchaseRate'];
        $tax_percent = $item_details['taxPercent'];
        $mrp = $item_details['mrp'] + 0;
        $upp = $item_details['upp'] + 0;

        $amount = round($closing_qty * $closing_rate, 2);
        $mrp_amount = round($closing_qty * $mrp, 2);

        $tot_amount += $amount;
        $tot_amount_mrp += $mrp_amount;      
        $tot_qty += $closing_qty;
        
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$item_name,'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$lot_no,'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,number_format($tax_percent,2,'.',''),'RTB',0,'R');            
        $pdf->Cell($item_widths[4],6,number_format($closing_qty,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,number_format($closing_rate,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,number_format($amount,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,number_format($mrp,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,number_format($mrp_amount,2,'.',''),'RTB',0,'R');      
      }

      $pdf->Ln();
      $pdf->SetFont('Arial','B',10);
      $pdf->Cell($totals_width,6,'T O T A L S','LTB',0,'R');
      $pdf->Cell($item_widths[4],6,number_format($tot_qty,2,'.',''),'LRTB',0,'R');
      $pdf->Cell($item_widths[5]+$item_widths[5],6,number_format($tot_amount,2,'.',''),'RTB',0,'R');    
      $pdf->Cell($item_widths[7]+$item_widths[8],6,number_format($tot_amount_mrp,2,'.',''),'RTB',0,'R');      
      $pdf->Output();
    }

    $controller_vars = array(
      'page_title' => 'Print Stock Report',
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
    return [$this->template->render_view('stock-report', $template_vars), $controller_vars];
  }

  public function openingBalances(Request $request) {

    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 300;
    $total_records = $categories_a = [];

    $client_locations = Utilities::get_client_locations();
    $categories_a = $this->products_api->get_product_categories();

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_opbal_data($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
        $form_data['pageNo'] = $page_no;
        $form_data['perPage'] = $per_page;
      } else {
        $error_message = 'Invalid Form Data.';
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/sales-register');        
      }

      // hit api
      $inven_api_response = $this->opbal_model->opbal_list($form_data);
      if($inven_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/opbal');
      } else {
        $total_records = $inven_api_response['openings'];
        $total_pages = $inven_api_response['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $form_data['pageNo'] = $i;
            $inven_api_response = $this->opbal_model->opbal_list($form_data);
            if($inven_api_response['status']) {
              $total_records = array_merge($total_records, $inven_api_response['openings']);
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

        $heading1 = 'Inventory Opening Balance Report';
        $heading2 = 'From '.date('jS F, Y', strtotime($form_data['fromDate'])).' To '.date('jS F, Y', strtotime($form_data['toDate']));
        $heading3 = '';
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }
        if($form_data['category'] !== '') {
          $category_name = $categories_a[$form_data['category']];
          if($heading3 !== '') {
            $heading3 .= ', ';
          }
          $heading3 .= 'Category Name: '.$category_name;
        }        
        $csv_headings = [ [$heading1], [$heading2], [$heading3] ];
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        $total_records = $this->_format_stock_report_for_csv($total_records);
        Utilities::download_as_CSV_attachment('OpeningBalances', $csv_headings, $total_records);
        return;
      }

      # start PDF printing.
      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('P','A4');
      $pdf->setTitle($heading1.' - '.date('jS F, Y'));

      $pdf->SetFont('Arial','B',13);
      $pdf->Cell(0,0,$heading1,'',1,'C');

      $pdf->SetFont('Arial','B',10);
      $pdf->Ln(5);
      $pdf->Cell(0,0,$heading2,'',1,'C');

      if($heading3 !== '') {
        $pdf->SetFont('Arial','B',10);        
        $pdf->Cell(0,10,$heading3,'B',1,'C');
      } else {
        $pdf->Ln(4);
      }     
      
      $item_widths = array(10,55,25,23,15,15,12,20,15);
      $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2];

      $pdf->SetFont('Arial','B',8);
      $pdf->Cell($item_widths[0],6,'Sno.','LTRB',0,'C');
      $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'Lot No.','RTB',0,'C');    
      $pdf->Cell($item_widths[3],6,'Category','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'OpenQty.','RTB',0,'C');
      $pdf->Cell($item_widths[5],6,'Pur.Rate','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'Tax%','RTB',0,'C');
      $pdf->Cell($item_widths[7],6,'Amount','RTB',0,'C');
      $pdf->Cell($item_widths[8],6,'MRP','RTB',0,'C');
      $pdf->SetFont('Arial','',8);
      $pdf->Ln();    
      
      $slno = 0;
      $tot_amount = $tot_qty = 0;
      foreach($total_records as $item_details) {
        $slno++;

        $item_name = $item_details['itemName'];
        $category_name = $item_details['categoryName'];
        $location_id = $item_details['locationID'];
        $opening_qty = $item_details['openingQty'];
        $lot_no = $item_details['lotNo'];
        $purchase_rate = $item_details['purchaseRate'];
        $tax_percent = $item_details['taxPercent'];
        $barcode = $item_details['barcode'];
        $opening_date = date("d-m-Y", strtotime($item_details['createdDateTime']));
        $opening_rate = $item_details['openingRate'];
        if($purchase_rate > 0 && $opening_qty > 0) {
          $item_amount = round($purchase_rate*$opening_qty,2);
        } else {
          $item_amount = 0;
        }
        if($item_amount>0 && $tax_percent > 0) {
          $tax_amount = round( ($item_amount*$tax_percent)/100, 2);
        } else {
          $tax_amount = 0;
        }

        $item_total = $item_amount + $tax_amount;
        $tot_amount += $item_total;
        $tot_qty += $opening_qty;

        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$item_name,'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$lot_no,'RTB',0,'L');    
        $pdf->Cell($item_widths[3],6,$category_name,'RTB',0,'L');
        $pdf->Cell($item_widths[4],6,$opening_qty,'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,number_format($purchase_rate,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,$tax_percent,'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,number_format($item_total,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,number_format($opening_rate,2,'.',''),'RTB',0,'R');
        $pdf->Ln();
      }
      $pdf->SetFont('Arial','B',10);
      $pdf->Cell($totals_width,6,'T O T A L S','LRTB',0,'R');
      $pdf->Cell($item_widths[3]+$item_widths[4],6,number_format($tot_qty,2,'.',''),'TB',0,'R');
      $pdf->Cell($item_widths[5]+$item_widths[6]+$item_widths[7],6,number_format($tot_amount,2,'.',''),'LRTB',0,'R');    
      $pdf->SetFont('Arial','B',11);

      $pdf->Output();
    }

    $controller_vars = array(
      'page_title' => 'Print Opening Balances',
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
    return [$this->template->render_view('opbal-report', $template_vars), $controller_vars];
  }

  public function inventoryProfitability(Request $request) {

    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 300;
    $total_records = $categories_a = [];

    $client_locations = Utilities::get_client_locations();
    $categories_a = $this->products_api->get_product_categories();

    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_inventory_profitability($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
        $form_data['pageNo'] = $page_no;
        $form_data['perPage'] = $per_page;
      } else {
        $error_message = 'Invalid Form Data.';
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/inventory-profitability');        
      }

      // hit api
      $inven_api_response = $this->inven_api->inventory_profitability($form_data);
      if($inven_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/inventory-profitability');
      } else {
        $total_records = $inven_api_response['results']['items'];
        $total_pages = $inven_api_response['results']['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $form_data['pageNo'] = $i;
            $inven_api_response = $this->inven_api->inventory_profitability($form_data);
            if($inven_api_response['status']) {
              $total_records = array_merge($total_records, $inven_api_response['results']['items']);
            }
          }
        }

        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }
        $heading3 = '';
        $heading1 = 'Inventory Profitability Report';
        $heading2 = 'From '.date('jS F, Y', strtotime($form_data['fromDate'])).' To '.date('jS F, Y', strtotime($form_data['toDate']));
        if($form_data['brandName'] !== '') {
          $heading3 .= 'Brand Name: '.$form_data['brandName'];
        }        
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }
        if($form_data['category'] !== '') {
          $category_name = $categories_a[$form_data['category']];
          if($heading3 !== '') {
            $heading3 .= ', ';
          }
          $heading3 .= 'Category Name: '.$category_name;
        }        
        $csv_headings = [ [$heading1], [$heading2], [$heading3] ];
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        $total_records = $this->_format_stock_report_for_csv($total_records);
        Utilities::download_as_CSV_attachment('InventoryProfitability', $csv_headings, $total_records);
        return;
      }
      
      // start PDF printing.
      
      $slno = 0;
      $tot_sold_qty = $tot_sold_value = $tot_pur_value = $tot_gross_profit = 0;
      $item_widths = array(8,45,15,14,16,16,16,19,17,25);
      $totals_width = $item_widths[0]+$item_widths[1];

      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('P','A4');
      $pdf->setTitle('inventoryprofitability'.' - '.date('dS F, Y'));

      $pdf->SetFont('Arial','B',16);
      $pdf->Cell(0,0,$heading1,'',1,'C');

      $pdf->SetFont('Arial','B',13);
      $pdf->Ln(6);
      $pdf->Cell(0,0,$heading2,'',1,'C');

      if($heading3 !== '') {
        $pdf->SetFont('Arial','B',10);
        $pdf->Ln(5);
        $pdf->Cell(0,0,$heading3,'',1,'C');
      }

      $pdf->SetFont('Arial','B',8);
      $pdf->Ln(5);
      $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'C');
      $pdf->Cell($item_widths[9],6,'Lot No.','RTB',0,'C');      
      $pdf->Cell($item_widths[2],6,'Qty.Sold','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'SalePrice','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'SaleValue','RTB',0,'C');
      $pdf->Cell($item_widths[5],6,'Pur.Price','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'Pur.Value','RTB',0,'C');
      $pdf->Cell($item_widths[7],6,'GrossProfit','RTB',0,'C');
      $pdf->Cell($item_widths[8],6,'Profit (%)','RTB',0,'C');    
      $pdf->SetFont('Arial','',8);

      // dump($total_records);
      // exit;

      foreach($total_records as $item_details) {
        $slno++;

        $gross_profit = $item_details['soldValue']-$item_details['purchaseValue'];
        $tot_sold_qty += $item_details['soldQty'];
        $tot_sold_value += $item_details['soldValue'];
        $tot_pur_value += $item_details['purchaseValue'];

        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,substr($item_details['itemName'],0,32),'RTB',0,'L');
        $pdf->Cell($item_widths[9],6,$item_details['lotNo'],'RTB',0,'L');        
        $pdf->Cell($item_widths[2],6,$item_details['soldQty'],'RTB',0,'R');
        $pdf->Cell($item_widths[3],6,$item_details['sellingPrice'],'RTB',0,'R');
        $pdf->Cell($item_widths[4],6,$item_details['soldValue'],'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,$item_details['finalPurchaseRate'],'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,$item_details['purchaseValue'],'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,number_format($gross_profit,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,number_format($item_details['profitPercentage'],2,'.',''),'RTB',0,'R');
      }
      
      $pdf->Ln(12);
      $pdf->SetFont('Arial','B',16);
      $pdf->Cell(0,0,'Profitability Summary','',1,'C');
      $pdf->Ln(4);
      $pdf->SetFont('Arial','B',12);
      $pdf->Cell(100,6,'Description','LRT',0,'C');
      $pdf->Cell(40,6,'Value','RT',1,'C');
      $pdf->SetFont('Arial','',14);

      $tot_gross_profit = 100-( round( round($tot_pur_value/$tot_sold_value,2) * 100, 2) );

      $pdf->Cell(100,6,'Total Sold Qty.','LRTB',0,'R');
      $pdf->Cell(40,6,number_format($tot_sold_qty,2,'.',''),'RTB',1,'R');

      $pdf->Cell(100,6,'Total Sale Value (in Rs.)','LRTB',0,'R');
      $pdf->Cell(40,6,number_format($tot_sold_value,2,'.',''),'RTB',1,'R');

      $pdf->Cell(100,6,'Total Purchase Value (in Rs.)','LRTB',0,'R');
      $pdf->Cell(40,6,number_format($tot_pur_value,2,'.',''),'RTB',1,'R');

      $pdf->Cell(100,6,'Profit (in Rs.)','LRTB',0,'R');
      $pdf->Cell(40,6,number_format($tot_sold_value-$tot_pur_value,2,'.',''),'RTB',1,'R');

      $pdf->Cell(100,6,'Profit (in %)','LRTB',0,'R');
      $pdf->Cell(40,6,number_format($tot_gross_profit,2,'.','').'%','RTB',1,'R');     

      $pdf->Output();
    }

    $controller_vars = array(
      'page_title' => 'Print Inventory Profitability',
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
    return [$this->template->render_view('inventory-profitability', $template_vars), $controller_vars];    
  }

  public function materialMovement(Request $request) {

    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 30;
    $total_records = $categories_a = [];

    $client_locations = Utilities::get_client_locations();
    $mov_types = ['fast' => 'Fast moving','slow'=>'Slow moving'];    
    
    if(count($request->request->all()) > 0) {
      // validate form data.
      $form_data = $request->request->all();
      $validation = $this->_validate_material_movement($form_data);
      if($validation['status']) {
        $form_data = $validation['cleaned_params'];
        $form_data['pageNo'] = $page_no;
        $form_data['perPage'] = $per_page;
      } else {
        $error_message = 'Invalid Form Data.';
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/material-movement');        
      }

      // hit api
      $inven_api_response = $this->inven_api->get_material_movement($form_data);
      if($inven_api_response['status'] === false) {
        $error_message = Constants::$REPORTS_ERROR_MESSAGE;
        $this->flash->set_flash_message($error_message, 1);
        Utilities::redirect('/reports/material-movement');
      } else {
        $total_records = $inven_api_response['results']['results'];
        $total_pages = $inven_api_response['results']['total_pages'];
        if($total_pages>1) {
          for($i=2;$i<=$total_pages;$i++) {
            $form_data['pageNo'] = $i;
            $inven_api_response = $this->inven_api->get_material_movement($form_data);
            if($inven_api_response['status']) {
              $total_records = array_merge($total_records, $inven_api_response['results']['results']);
            }
          }
        }

        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }
        $heading1 = ucwords($form_data['movType']).' Material Movement Register';
        $heading2 = 'Between '.date('jS F, Y', strtotime($form_data['fromDate'])).' - '.date('jS F, Y', strtotime($form_data['toDate']));
        if($form_data['count'] > 0) {
          $operator_type = $form_data['movType'] === 'fast' ? '>=' : '<=';
          $heading2 .= ' :: [ Sold Qty '.$operator_type.' '.$form_data['count'].' ]';
        }
        if($location_name !== '') {
          $heading1 .= ' - '.$location_name;
        }
        $csv_headings = [ [$heading1], [$heading2] ];
      }

      $format = $form_data['format'];
      if($format === 'csv') {
        $total_records = $this->_format_data_for_material_movement($total_records);
        Utilities::download_as_CSV_attachment('MaterialMovement', $csv_headings, $total_records);
        return;
      }

      $item_widths = array(10,70,25,25,15,24,23);
      $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3];
      $totals_width1 = $item_widths[6];    

      // start PDF printing.
      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->AddPage('P','A4');
      $pdf->setTitle('MaterialMovement'.' - '.date('jS F, Y'));

      $pdf->SetFont('Arial','B',16);
      $pdf->Cell(0,0,$heading1,'',1,'C');

      $pdf->SetFont('Arial','B',10);
      $pdf->Ln(5);
      $pdf->Cell(0,0,$heading2,'',1,'C');

      $pdf->SetFont('Arial','B',8);
      $pdf->Ln(5);
      $pdf->Cell($item_widths[0],6,'SNo.','LRTB',0,'C');
      $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'C');
      $pdf->Cell($item_widths[2],6,'Category','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'Brand Name','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'Sold Qty.','RTB',0,'C');        
      $pdf->Cell($item_widths[5],6,'Rate / Unit (Rs.)','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'Amount (Rs.)','RTB',1,'C');
      
      $pdf->SetFont('Arial','',8);
      $slno=$tot_amount=$tot_qty=0;
      foreach($total_records as $item_details) {
        $slno++;
        $amount = $item_details['soldQty']*$item_details['itemRate'];
        $tot_amount += $amount;
        $tot_qty += $item_details['soldQty'];
        
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,substr($item_details['itemName'],0,30),'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,substr($item_details['categoryName'],0,15),'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,substr($item_details['mfgName'],0,12),'RTB',0,'L');
        $pdf->Cell($item_widths[4],6,number_format($item_details['soldQty'],2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,number_format($item_details['itemRate'],2,'.',''),'RTB',0,'R');  
        $pdf->Cell($item_widths[6],6,number_format($amount,2,'.',''),'RTB',1,'R');
      }

      $pdf->SetFont('Arial','B',9);
      $pdf->Cell($totals_width,6,'Totals','LRTB',0,'R');
      $pdf->Cell($item_widths[4],6,number_format($tot_qty,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,'','RTB',0,'R');    
      $pdf->Cell($item_widths[6],6,number_format($tot_amount,2,'.',''),'RTB',0,'R');  

      $pdf->Output();      
    }

    $controller_vars = array(
      'page_title' => 'Print Fast / Slow Movement Items',
      'icon_name' => 'fa fa-print',
    );

    // prepare form variables.
    $template_vars = array(
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'mov_types' => $mov_types,
      'default_location' => $default_location,
      'format_options' => ['pdf'=>'PDF Format', 'csv' => 'CSV Format'],
    );

    // render template
    return [$this->template->render_view('material-movement', $template_vars), $controller_vars];    
  }

  private function _validate_stock_report_data($form_data = []) {
    $cleaned_params = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $cleaned_params['locationCode'] = '';
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

    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);

    return ['status' => true, 'cleaned_params' => $cleaned_params];
  }

  private function _validate_opbal_data($form_data = []) {
    $cleaned_params = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $cleaned_params['locationCode'] = '';
    }
    if($form_data['category'] !== '') {
      $cleaned_params['category'] = Utilities::clean_string($form_data['category']);
    } else {
      $cleaned_params['category'] = '';
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

    return ['status' => true, 'cleaned_params' => $cleaned_params];
  }  

  private function _validate_inventory_profitability($form_data = []) {
    $cleaned_params = [];
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $cleaned_params['locationCode'] = '';
    }
    if($form_data['category'] !== '') {
      $cleaned_params['category'] = Utilities::clean_string($form_data['category']);
    } else {
      $cleaned_params['category'] = '';
    }
    if($form_data['brandName'] !== '') {
      $cleaned_params['brandName'] = Utilities::clean_string($form_data['brandName']);
    } else {
      $cleaned_params['brandName'] = '';
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

    return ['status' => true, 'cleaned_params' => $cleaned_params];
  }

  private function _validate_material_movement($form_data = []) {
    $cleaned_params = [];
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
    if($form_data['locationCode'] !== '') {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $cleaned_params['locationCode'] = '';
    }
    if($form_data['count'] !== '') {
      $cleaned_params['count'] = Utilities::clean_string($form_data['count']);
    } else {
      $cleaned_params['count'] = '';
    }
    if($form_data['movType'] !== '') {
      $cleaned_params['movType'] = Utilities::clean_string($form_data['movType']);
    } else {
      $cleaned_params['movType'] = '';
    }    
    $cleaned_params['format'] =  Utilities::clean_string($form_data['format']);

    return ['status' => true, 'cleaned_params' => $cleaned_params];
  }  

  private function _format_stock_report_for_csv($total_records = []) {
    $cleaned_params = [];
    foreach($total_records as $key => $record_details) {
      $cleaned_params[$key] = [
        'Item Name' => $record_details['itemName'],
        'Category Name' => $record_details['categoryName'],
        'Lot No.' => $record_details['lotNo'],
        'Tax Percent' => $record_details['taxPercent'],
        'Closing Qty' => $record_details['closingQty'],
        'Purchase Rate' => $record_details['purchaseRate'],
        'M.R.P' => $record_details['mrp'],
        'Brand Name' => $record_details['mfgName'],
      ];
    }
    return $cleaned_params;
  }
}