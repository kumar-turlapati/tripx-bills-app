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
use ClothingRm\Inward\Model\Inward;
use ClothingRm\Grn\Model\GrnNew;
use ClothingRm\StockAudit\Model\StockAudit;

class InventoryReportsController {

  protected $views_path;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->inven_api = new Inventory;
    $this->products_api = new Products;
    $this->flash = new Flash;
    $this->opbal_model = new Openings;
    $this->inward_model = new Inward;
    $this->grn_model = new GrnNew;
    $this->audit_model = new StockAudit;    
  }

  public function stockReport(Request $request) {
   
    $default_location = $_SESSION['lc'];
    $page_no = 1; $per_page = 300;
    $total_records = $categories_a = [];
    $group_by_a = ['item' => 'Itemwise', 'lot' => 'Lotwise'];
    $neg_a = ['all' => 'All items', 'neg' => 'Negative values'];

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

        // dump($total_records, $form_data);
        // exit;

        // $total_records = $this->_format_data_for_sales_register($total_records);
        if(is_array($client_locations) && count($client_locations)>0 && $form_data['locationCode'] !== '') {
          $location_name = $client_locations[$form_data['locationCode']];
        } else {
          $location_name = '';
        }

        if($form_data['groupBy'] === 'lot') {
          $report_string = 'Lotwise';
        } else {
          $report_string = 'Itemwise';
        }

        $heading1 = $report_string.' Stock Report - '.$location_name;
        $heading2 = '( from '.date("d-M-Y", strtotime($form_data['fromDate'])).' to '. date("d-M-Y", strtotime($form_data['toDate'])) .' )';
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
        $total_records = $this->_format_stock_report_for_csv($total_records);
        Utilities::download_as_CSV_attachment('StockReport', $csv_headings, $total_records);
        return;
      }
      // start PDF printing.
      $item_widths = array(10,38,23,20,25,12,14,14,14,16,12,12,12,13,12,18,12);
      //                    0, 1, 2, 3, 4, 5, 6, 7, 8, 9,10,11,12,13,14,15,16
      $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3] + $item_widths[4]  + $item_widths[5];
      $slno = 0; $tot_amount = 0;
      $tot_op_qty = $tot_pu_qty = $tot_sr_qty = $tot_aj_qty = $tot_st_qty = $tot_sa_qty = $tot_pr_qty = $tot_cl_qty = 0;

      $pdf = PDF::getInstance();
      $pdf->AliasNbPages();
      $pdf->SetAutoPageBreak(false);
      $pdf->AddPage('L','A4');
      $pdf->setTitle($heading1.' - '.date('jS F, Y'));

      $pdf->SetFont('Arial','B',16);
      $pdf->Cell(0,5,$heading1,'',1,'C');
      $pdf->SetFont('Arial','B',12);
      $pdf->Cell(0,5,$heading2,'',1,'C');
      if($heading3 !== '') {
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(0,5,$heading3,'B',1,'C');
      }

      $this->_add_page_heading_for_stock_report($pdf, $item_widths);
      $first_page = true;
      $row_cntr = 0;
      foreach($total_records as $item_details) {
        $slno++;

        $item_name = substr($item_details['itemName'], 0, 20);
        $category_name = substr($item_details['categoryName'], 0, 15);
        $brand_name = substr( strtolower($item_details['brandName']), 0, 14);
        $lot_no = $item_details['lotNo'];

        $row_cntr++;

        $opening_qty = $item_details['openingQty'];
        $purchased_qty = $item_details['purchasedQty'];
        $sales_return_qty = $item_details['salesReturnQty'];
        $adjusted_qty = $item_details['adjustedQty'];
        $transfer_qty = $item_details['transferQty'];
        $purchase_return_qty = $item_details['purchaseReturnQty'];
        $sold_qty = $item_details['soldQty'];
        $closing_qty = $item_details['closingQty'];
        $mrp = $item_details['mrp'];
        $purchase_rate = $item_details['purchaseRate'];
        $tax_percent = $item_details['taxPercent'];

        $amount = round($closing_qty * $purchase_rate, 2);
        
        $tot_op_qty += $opening_qty;
        $tot_pu_qty += $purchased_qty;
        $tot_sr_qty += $sales_return_qty;
        $tot_aj_qty += $adjusted_qty;
        $tot_st_qty += $transfer_qty;
        $tot_sa_qty += $sold_qty;
        $tot_pr_qty += $purchase_return_qty;
        $tot_cl_qty += $closing_qty;
        $tot_amount += $amount;

        if($form_data['groupBy'] === 'item') {
          $lot_no = '';
        }
        
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$item_name,'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$category_name,'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,$brand_name,'RTB',0,'L');
        $pdf->Cell($item_widths[4],6,$lot_no,'RTB',0,'L');
        $pdf->Cell($item_widths[5],6,$tax_percent,'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,number_format($opening_qty,2,'.',''),'RTB',0,'R');            
        $pdf->Cell($item_widths[7],6,number_format($purchased_qty,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,number_format($sales_return_qty,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[9],6,number_format($adjusted_qty,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[10],6,number_format($transfer_qty,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[11],6,number_format($sold_qty,2,'.',''),'RTB',0,'R');      
        $pdf->Cell($item_widths[12],6,number_format($purchase_return_qty,2,'.',''),'RTB',0,'R');      
        $pdf->Cell($item_widths[13],6,number_format($closing_qty,2,'.',''),'RTB',0,'R');      
        $pdf->Cell($item_widths[14],6,number_format($purchase_rate,2,'.',''),'RTB',0,'R');      
        $pdf->Cell($item_widths[15],6,number_format($amount,2,'.',''),'RTB',0,'R');      
        $pdf->Cell($item_widths[16],6,number_format($mrp,2,'.',''),'RTB',0,'R');
        if($first_page && $row_cntr === 23) {
          $pdf->AddPage('L','A4');
          $this->_add_page_heading_for_stock_report($pdf, $item_widths);
          $first_page = false; $row_cntr = 0;
        } elseif ($row_cntr === 26) {
          $pdf->AddPage('L','A4');
          $this->_add_page_heading_for_stock_report($pdf, $item_widths);
          $row_cntr = 0;
        }
      }

      $pdf->Ln();
      $pdf->SetFont('Arial','B',8);
      $pdf->Cell($totals_width,6,'REPORT TOTALS','LRTB',0,'R');
      $pdf->Cell($item_widths[6],6,number_format($tot_op_qty,2,'.',''),'RTB',0,'R');            
      $pdf->Cell($item_widths[7],6,number_format($tot_pu_qty,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[8],6,number_format($tot_sr_qty,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[9],6,number_format($tot_aj_qty,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[10],6,number_format($tot_st_qty,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[11],6,number_format($tot_sa_qty,2,'.',''),'RTB',0,'R');      
      $pdf->Cell($item_widths[12],6,number_format($tot_pr_qty,2,'.',''),'RTB',0,'R');      
      $pdf->Cell($item_widths[13],6,number_format($tot_cl_qty,2,'.',''),'RTB',0,'R');      
      $pdf->Cell($item_widths[14],6,'','RTB',0,'R');      
      $pdf->Cell($item_widths[15],6,number_format($tot_amount,2,'.',''),'RTB',0,'R');      
      $pdf->Cell($item_widths[16],6,'','RTB',0,'R');
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
      'group_by_a' => $group_by_a,
      'neg_a' => $neg_a,
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
      $pdf->Cell($item_widths[2],6,'BrandName','RTB',0,'C');    
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
        $brand_name = substr($item_details['mfgName'], 0, 13);
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
        $pdf->Cell($item_widths[2],6,$brand_name,'RTB',0,'L');    
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
      $pdf->Cell($item_widths[9],6,'BrandName','RTB',0,'C');      
      $pdf->Cell($item_widths[2],6,'Qty.Sold','RTB',0,'C');
      $pdf->Cell($item_widths[3],6,'SalePrice','RTB',0,'C');
      $pdf->Cell($item_widths[4],6,'SaleValue','RTB',0,'C');
      $pdf->Cell($item_widths[5],6,'Pur.Price','RTB',0,'C');
      $pdf->Cell($item_widths[6],6,'Pur.Value','RTB',0,'C');
      $pdf->Cell($item_widths[7],6,'Profit','RTB',0,'C');
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
        $pdf->Cell($item_widths[9],6,substr($item_details['mfgName'],0,13),'RTB',0,'L');        
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

      // $tot_gross_profit = 100-( round( round($tot_pur_value/$tot_sold_value,2) * 100, 2) );
      $profit_amount = round($tot_sold_value - $tot_pur_value, 2);
      $tot_gross_profit = round(($profit_amount/$tot_pur_value)*100, 2);

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

  public function printGRN(Request $request) {
    $grn_code = Utilities::clean_string($request->get('grnCode'));
    $grn_response = $this->grn_model->get_grn_details($grn_code);
    if($grn_response['status']) {
      $grn_details = $grn_response['grnDetails'];
    } else {
      $this->set_flash_message('Invalid GRN Code.');
      Utilities::redirect('/grn/list');
    }

    // dump($grn_details);
    // exit;

    $print_date_time = date("d-M-Y h:ia");
    $slno = $total_qty = 0;

    $grn_date       =    date('d-M-Y',strtotime($grn_details['grnDate']));
    $grn_no         =    $grn_details['grnNo'];
    $pay_method     =    Constants::$PAYMENT_METHODS_PURCHASE[$grn_details['paymentMethod']];
    $credit_days    =    $grn_details['creditDays'];
    $supplier_name  =    $grn_details['supplierName'];
    $po_info        =    $grn_details['poNo'].' / '.date('d-M-Y',strtotime($grn_details['purchaseDate']));
    $bill_no        =    $grn_details['billNo'];
    $bill_due_date  =    date('d-M-Y',strtotime($grn_details['paymentDueDate']));
    $remarks        =    $grn_details['remarks'];
    $grn_tax_amount =    $grn_details['taxAmount'];
    $grn_value      =    $grn_details['netPay'];
    $total_items    =    (isset($grn_details['itemDetails']) && count($grn_details['itemDetails'])>0 ? count($grn_details['itemDetails']) : 'Invalid' );

    if( isset($grn_details['itemDetails']) && count($grn_details['itemDetails']) > 0 ) {
      foreach($grn_details['itemDetails'] as $grn_qty_details) {
        $total_qty += ($grn_qty_details['itemQty']*$grn_qty_details['packedQty']);
      }
    }

    $items_total = $grn_details['billAmount'];
    $discount_amount = $grn_details['discountAmount'];
    $total_tax_amount = $grn_details['taxAmount'];

    $packing_charges = isset($grn_details['packingCharges']) && $grn_details['packingCharges'] > 0 ? $grn_details['packingCharges'] : 0;
    $shipping_charges = isset($grn_details['shippingCharges']) && $grn_details['shippingCharges'] > 0 ? $grn_details['shippingCharges'] : 0;
    $insurance_charges = isset($grn_details['insuranceCharges']) && $grn_details['insuranceCharges'] > 0 ? $grn_details['insuranceCharges'] : 0;
    $other_charges = isset($grn_details['otherCharges']) && $grn_details['otherCharges'] > 0 ? $grn_details['otherCharges'] : 0;
    $remarks = isset($grn_details['remarks']) ? $grn_details['remarks'] : '';

    $items_tot_after_discount = $items_total-$discount_amount;
    $grand_total = $items_tot_after_discount + $total_tax_amount + $packing_charges + $shipping_charges + $insurance_charges + $other_charges;

    $net_pay = $grn_details['netPay'];
    $round_off = $grn_details['roundOff'];

    // start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('L','A4');

    // Print Bill Information.
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,'Godown Receipt Note (GRN)','',1,'C');
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(4);

    # first row
    $header_widths = array(100,35,30,30,48,35);
    $item_widths = array(12,74,43,23,22,20,15,23,23,23);
    $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3]+
                    $item_widths[4]+$item_widths[5]+$item_widths[6]+$item_widths[7]+
                    $item_widths[8];

    $pdf->Cell($header_widths[0],6,'Supplier name','LRTB',0,'C');
    $pdf->Cell($header_widths[1],6,'Supplier bill no.','RTB',0,'C');
    $pdf->Cell($header_widths[2],6,'Payment method','RTB',0,'C');
    $pdf->Cell($header_widths[3],6,'Credit days','RTB',0,'C');
    $pdf->Cell($header_widths[4],6,'Payment due date','RTB',0,'C');
    $pdf->Cell($header_widths[5],6,'PO. No & Date','RTB',1,'C');
    $pdf->SetFont('Arial','',9);
    $pdf->Cell($header_widths[0],6,$supplier_name,'LRTB',0,'C');
    $pdf->Cell($header_widths[1],6,$bill_no,'LRTB',0,'C');
    $pdf->Cell($header_widths[2],6,$pay_method,'RTB',0,'C');
    $pdf->Cell($header_widths[3],6,$credit_days,'RTB',0,'C');
    $pdf->Cell($header_widths[4],6,$bill_due_date,'RTB',0,'C');
    $pdf->Cell($header_widths[5],6,$po_info,'RTB',1,'C');    
 
    // second row
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($header_widths[0],6,'GRN No. & Date','LRTB',0,'C');
    $pdf->Cell($header_widths[1],6,'ItemsTotal (Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[2],6,'Discount (Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[3],6,'GrossAmount (Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[4],6,'GST (Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[5],6,'PackingCharges (Rs.)','RTB',1,'C');
    
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($header_widths[0],6,$grn_no.'/ '.$grn_date,'LRB',0,'C');
    $pdf->SetFont('Arial','',9);
    $pdf->Cell($header_widths[1],6,number_format($items_total,2,'.',''),'RB',0,'C');
    $pdf->Cell($header_widths[2],6,number_format($discount_amount,2,'.',''),'RB',0,'C');
    $pdf->Cell($header_widths[3],6,number_format($items_tot_after_discount,2,'.',''),'RB',0,'C');
    $pdf->Cell($header_widths[4],6,number_format($total_tax_amount,2,'.',''),'RB',0,'C');
    $pdf->Cell($header_widths[5],6,number_format($packing_charges,2,'.',''),'RB',1,'C');

    // third row
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($header_widths[0],6,'Net Pay (Rs.)','LRTB',0,'C');
    $pdf->Cell($header_widths[1],6,'Shipping/Freight (Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[2],6,'Insurance(Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[3],6,'OtherCharges','RTB',0,'C');
    $pdf->Cell($header_widths[4],6,'RoundOff(Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[5],6,'Total Qty.','RTB',1,'C');

    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($header_widths[0],6,number_format($grn_value,2,'.',''),'LRB',0,'C');
    $pdf->SetFont('Arial','',9);
    $pdf->Cell($header_widths[1],6,number_format($shipping_charges,2,'.',''),'RB',0,'C');
    $pdf->Cell($header_widths[2],6,number_format($insurance_charges,2,'.',''),'RB',0,'C');
    $pdf->Cell($header_widths[3],6,number_format($other_charges,2,'.',''),'RB',0,'C');
    $pdf->Cell($header_widths[4],6,number_format($round_off,2,'.',''),'RB',0,'C');
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($header_widths[5],6,$total_items.' | '.$total_qty,'RB',1,'C');
    $pdf->SetFont('Arial','',9);

    // fourth row(s)
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($item_widths[0],6,'Sno.','LRB',0,'C');
    $pdf->Cell($item_widths[1],6,'ItemName','RB',0,'C');
    $pdf->Cell($item_widths[2],6,'Lot No.','RB',0,'C');
    $pdf->Cell($item_widths[3],6,'Accp. Qty.','RB',0,'C');
    $pdf->Cell($item_widths[4],6,'Billed Qty.','RB',0,'C');    
    $pdf->Cell($item_widths[5],6,'Item Rate','RB',0,'C');
    $pdf->Cell($item_widths[6],6,'GST(%)','RB',0,'C');
    $pdf->Cell($item_widths[7],6,'Amount','RB',0,'C');
    $pdf->Cell($item_widths[8],6,'GST Amount','RB',0,'C');
    $pdf->Cell($item_widths[9],6,'Total Amount','RB',0,'C');    
    $pdf->SetFont('Arial','',9);
    $total_value = 0;
    foreach($grn_details['itemDetails'] as $item_details) {
        $slno++;
        $item_name = substr($item_details['itemName'],0,35);
        $packed_qty = $item_details['packedQty'];
        $acc_qty = $item_details['itemQty']*$packed_qty;
        $item_qty = ($item_details['itemQty']-$item_details['freeQty']) * $packed_qty;
        $item_rate = $item_details['itemRate'];
        $tax_percent = $item_details['taxPercent'];
        $item_amount = round($item_qty*$item_rate,2);
        $lot_no = $item_details['lotNo'];
        if($tax_percent>0) {
          $tax_amount = round(($item_amount*$tax_percent)/100,2);
        } else {
          $tax_amount = 0;
        }
        $total_amount = $item_amount+$tax_amount;
        $total_value += $total_amount;
        
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,substr($item_details['itemName'],0,28),'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$lot_no,'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,number_format($acc_qty,2,'.',''),'RTB',0,'R');  
        $pdf->Cell($item_widths[4],6,number_format($item_qty,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,number_format($item_rate,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,number_format($tax_percent,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,number_format($item_amount,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,number_format($tax_amount,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[9],6,number_format($total_amount,2,'.',''),'RTB',0,'R');        
    }

    // $pdf->Ln();
    // $pdf->SetFont('Arial','B',11);    
    // $pdf->Cell($totals_width,6,'TOTAL VALUE','LRTB',0,'R');
    // $pdf->Cell($item_widths[9],6,number_format(,2),'LRTB',0,'R');
    $pdf->Ln();
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(60,10,'Prepared by:','LRB',0,'L');
    $pdf->Cell(60,10,'Verified by:','RB',0,'L');
    $pdf->Cell(60,10,'Approved by:','RB',0,'L');
    $pdf->Cell(98,10,'Remarks: '.$remarks,'RB',0,'L');

    $pdf->Output();
  }

  public function printPO(Request $request) {
    $purchase_code = Utilities::clean_string($request->get('purchaseCode'));
    $purchase_response = $this->inward_model->get_purchase_details($purchase_code);
    if($purchase_response['status']) {
      $purchase_details = $purchase_response['purchaseDetails'];
    } else {
      $this->set_flash_message('Invalid PO Code.');
      Utilities::redirect('/inward-entry/list');
    }

    dump($purchase_details);
    exit;

    $print_date_time = date("d-M-Y h:ia");
    $slno = $total_qty = 0;

    $grn_date       =    date('d-M-Y',strtotime($grn_details['grnDate']));
    $grn_no         =    $grn_details['grnNo'];
    $pay_method     =    Constants::$PAYMENT_METHODS_PURCHASE[$grn_details['paymentMethod']];
    $credit_days    =    $grn_details['creditDays'];
    $supplier_name  =    $grn_details['supplierName'];
    $po_info        =    $grn_details['poNo'].' / '.date('d-M-Y',strtotime($grn_details['purchaseDate']));
    $bill_no        =    $grn_details['billNo'];
    $bill_due_date  =    date('d-M-Y',strtotime($grn_details['paymentDueDate']));
    $remarks        =    $grn_details['remarks'];
    $grn_tax_amount =    $grn_details['taxAmount'];
    $grn_value      =    $grn_details['netPay'];
    $total_items    =    (isset($grn_details['itemDetails']) && count($grn_details['itemDetails'])>0 ? count($grn_details['itemDetails']) : 'Invalid' );

    if( isset($grn_details['itemDetails']) && count($grn_details['itemDetails']) > 0 ) {
      foreach($grn_details['itemDetails'] as $grn_qty_details) {
        $total_qty += ($grn_qty_details['itemQty']*$grn_qty_details['packedQty']);
      }
    }

    $items_total = $grn_details['billAmount'];
    $discount_amount = $grn_details['discountAmount'];
    $total_tax_amount = $grn_details['taxAmount'];

    $packing_charges = isset($grn_details['packingCharges']) && $grn_details['packingCharges'] > 0 ? $grn_details['packingCharges'] : 0;
    $shipping_charges = isset($grn_details['shippingCharges']) && $grn_details['shippingCharges'] > 0 ? $grn_details['shippingCharges'] : 0;
    $insurance_charges = isset($grn_details['insuranceCharges']) && $grn_details['insuranceCharges'] > 0 ? $grn_details['insuranceCharges'] : 0;
    $other_charges = isset($grn_details['otherCharges']) && $grn_details['otherCharges'] > 0 ? $grn_details['otherCharges'] : 0;
    $remarks = isset($grn_details['remarks']) ? $grn_details['remarks'] : '';

    $items_tot_after_discount = $items_total-$discount_amount;
    $grand_total = $items_tot_after_discount + $total_tax_amount + $packing_charges + $shipping_charges + $insurance_charges + $other_charges;

    $net_pay = $grn_details['netPay'];
    $round_off = $grn_details['roundOff'];

    // start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('L','A4');

    // Print Bill Information.
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,'Purchase Order','',1,'C');
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(4);

    # first row
    $header_widths = array(100,35,30,30,48,35);
    $item_widths = array(12,74,43,23,22,20,15,23,23,23);
    $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3]+
                    $item_widths[4]+$item_widths[5]+$item_widths[6]+$item_widths[7]+
                    $item_widths[8];

    $pdf->Cell($header_widths[0],6,'Supplier name','LRTB',0,'C');
    $pdf->Cell($header_widths[1],6,'Supplier bill no.','RTB',0,'C');
    $pdf->Cell($header_widths[2],6,'Payment method','RTB',0,'C');
    $pdf->Cell($header_widths[3],6,'Credit days','RTB',0,'C');
    $pdf->Cell($header_widths[4],6,'Payment due date','RTB',0,'C');
    $pdf->Cell($header_widths[5],6,'PO. No & Date','RTB',1,'C');
    $pdf->SetFont('Arial','',9);
    $pdf->Cell($header_widths[0],6,$supplier_name,'LRTB',0,'C');
    $pdf->Cell($header_widths[1],6,$bill_no,'LRTB',0,'C');
    $pdf->Cell($header_widths[2],6,$pay_method,'RTB',0,'C');
    $pdf->Cell($header_widths[3],6,$credit_days,'RTB',0,'C');
    $pdf->Cell($header_widths[4],6,$bill_due_date,'RTB',0,'C');
    $pdf->Cell($header_widths[5],6,$po_info,'RTB',1,'C');    
 
    // second row
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($header_widths[0],6,'GRN No. & Date','LRTB',0,'C');
    $pdf->Cell($header_widths[1],6,'ItemsTotal (Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[2],6,'Discount (Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[3],6,'GrossAmount (Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[4],6,'GST (Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[5],6,'PackingCharges (Rs.)','RTB',1,'C');
    
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($header_widths[0],6,$grn_no.'/ '.$grn_date,'LRB',0,'C');
    $pdf->SetFont('Arial','',9);
    $pdf->Cell($header_widths[1],6,number_format($items_total,2,'.',''),'RB',0,'C');
    $pdf->Cell($header_widths[2],6,number_format($discount_amount,2,'.',''),'RB',0,'C');
    $pdf->Cell($header_widths[3],6,number_format($items_tot_after_discount,2,'.',''),'RB',0,'C');
    $pdf->Cell($header_widths[4],6,number_format($total_tax_amount,2,'.',''),'RB',0,'C');
    $pdf->Cell($header_widths[5],6,number_format($packing_charges,2,'.',''),'RB',1,'C');

    // third row
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($header_widths[0],6,'Net Pay (Rs.)','LRTB',0,'C');
    $pdf->Cell($header_widths[1],6,'Shipping/Freight (Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[2],6,'Insurance(Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[3],6,'OtherCharges','RTB',0,'C');
    $pdf->Cell($header_widths[4],6,'RoundOff(Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[5],6,'Total Qty.','RTB',1,'C');

    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($header_widths[0],6,number_format($grn_value,2,'.',''),'LRB',0,'C');
    $pdf->SetFont('Arial','',9);
    $pdf->Cell($header_widths[1],6,number_format($shipping_charges,2,'.',''),'RB',0,'C');
    $pdf->Cell($header_widths[2],6,number_format($insurance_charges,2,'.',''),'RB',0,'C');
    $pdf->Cell($header_widths[3],6,number_format($other_charges,2,'.',''),'RB',0,'C');
    $pdf->Cell($header_widths[4],6,number_format($round_off,2,'.',''),'RB',0,'C');
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($header_widths[5],6,$total_items.' | '.$total_qty,'RB',1,'C');
    $pdf->SetFont('Arial','',9);

    // fourth row(s)
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($item_widths[0],6,'Sno.','LRB',0,'C');
    $pdf->Cell($item_widths[1],6,'ItemName','RB',0,'C');
    $pdf->Cell($item_widths[2],6,'Lot No.','RB',0,'C');
    $pdf->Cell($item_widths[3],6,'Accp. Qty.','RB',0,'C');
    $pdf->Cell($item_widths[4],6,'Billed Qty.','RB',0,'C');    
    $pdf->Cell($item_widths[5],6,'Item Rate','RB',0,'C');
    $pdf->Cell($item_widths[6],6,'GST(%)','RB',0,'C');
    $pdf->Cell($item_widths[7],6,'Amount','RB',0,'C');
    $pdf->Cell($item_widths[8],6,'GST Amount','RB',0,'C');
    $pdf->Cell($item_widths[9],6,'Total Amount','RB',0,'C');    
    $pdf->SetFont('Arial','',9);
    $total_value = 0;
    foreach($grn_details['itemDetails'] as $item_details) {
        $slno++;
        $item_name = substr($item_details['itemName'],0,35);
        $packed_qty = $item_details['packedQty'];
        $acc_qty = $item_details['itemQty']*$packed_qty;
        $item_qty = ($item_details['itemQty']-$item_details['freeQty']) * $packed_qty;
        $item_rate = $item_details['itemRate'];
        $tax_percent = $item_details['taxPercent'];
        $item_amount = round($item_qty*$item_rate,2);
        $lot_no = $item_details['lotNo'];
        if($tax_percent>0) {
          $tax_amount = round(($item_amount*$tax_percent)/100,2);
        } else {
          $tax_amount = 0;
        }
        $total_amount = $item_amount+$tax_amount;
        $total_value += $total_amount;
        
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,substr($item_details['itemName'],0,28),'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$lot_no,'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,number_format($acc_qty,2,'.',''),'RTB',0,'R');  
        $pdf->Cell($item_widths[4],6,number_format($item_qty,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,number_format($item_rate,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,number_format($tax_percent,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,number_format($item_amount,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,number_format($tax_amount,2,'.',''),'RTB',0,'R');
        $pdf->Cell($item_widths[9],6,number_format($total_amount,2,'.',''),'RTB',0,'R');        
    }

    // $pdf->Ln();
    // $pdf->SetFont('Arial','B',11);    
    // $pdf->Cell($totals_width,6,'TOTAL VALUE','LRTB',0,'R');
    // $pdf->Cell($item_widths[9],6,number_format(,2),'LRTB',0,'R');
    $pdf->Ln();
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(60,10,'Prepared by:','LRB',0,'L');
    $pdf->Cell(60,10,'Verified by:','RB',0,'L');
    $pdf->Cell(60,10,'Approved by:','RB',0,'L');
    $pdf->Cell(98,10,'Remarks: '.$remarks,'RB',0,'L');

    $pdf->Output();
  }

  public function printStockAuditReport(Request $request) {
    $page_no = 1; $per_page = 300;
    $client_locations = $location_ids = $location_codes = [];
    $items_a = [];

    $register_url = '/stock-audit/register';

    // get location codes from api
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    // get audit details
    $audit_code = $request->get('auditCode');
    $audit_details = $this->audit_model->get_audit_details($audit_code);
    if(is_array($audit_details) && count($audit_details)>0) {
      $audit_location_id = $audit_details['locationID'];
      $audit_status = (int)$audit_details['status'];
      // check for filter variables.
      if(isset($location_codes[$audit_location_id])) {
        $audit_location_code = $location_codes[$audit_location_id];
        $audit_location_name = $location_ids[$audit_location_id];
        $audit_type = $audit_details['auditType'] === 'int' ? 'Internal' : 'External';
        $start_date = $audit_details['auditStartDate'];
        $cb_date = $audit_details['cbDate'];
      } else {
        $this->flash->set_flash_message('Invalid audit location', 1);
        Utilities::redirect($register_url);
      }       
    } else {
      $this->flash->set_flash_message('Invalid Audit Location', 1);
      Utilities::redirect($register_url);
    }
    
    // prepare form data variables.
    $form_data = [
      'pageNo' => 1,
      'perPage' => 300,
      'locationCode' => $audit_location_code,
    ];

    // hit api
    $audit_response = $this->audit_model->get_audit_items($form_data, $audit_code);
    if($audit_response['status'] === false) {
      $this->flash->set_flash_message('No data available to Download', 1);
      Utilities::redirect('/stock-audit/register');
    } else {
      $total_records = $audit_response['response']['items'];
      $total_pages = $audit_response['response']['total_pages'];
      if($total_pages>1) {
        for($i=2;$i<=$total_pages;$i++) {
          $form_data['pageNo'] = $i;
          $audit_response = $this->audit_model->get_audit_items($form_data, $audit_code);
          if($audit_response['status']) {
            $total_records = array_merge($total_records, $audit_response['response']['items']);
          }
        }
      }
    }

    // dump($total_records);
    // exit;

    // start printing PDF.
    $heading1 = $audit_type.' Stock Audit Report - '.$audit_location_name;
    $heading2 = 'Started on '.date('jS M, Y', strtotime($start_date)).' | CB Date: '.date('jS M, Y', strtotime($cb_date));
    $heading2 .= ' [ Phy.Qty. compares with Sys.Qty. ]';

    // start PDF printing.
    $item_widths = array(10,65,28,28,15,15,15,15);
    $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + 
                  $item_widths[3];
    $slno = $tot_amount = $tot_qty = $tot_amount_mrp = 0;

    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');
    $pdf->setTitle($heading1.' - '.date('jS F, Y'));

    $this->_add_page_heading_for_audit_report($pdf, $item_widths, $heading1, $heading2);
    
    $slno = $row_cntr = 0;
    $tot_phy_qty = $tot_sys_qty = $tot_diff_qty = 0;
    foreach($total_records as $item_details) {
      $slno++;

      $item_name = substr($item_details['itemName'],0,33);
      $category_name = substr($item_details['categoryName'],0,12);
      $brand_name = substr($item_details['brandName'],0,12);
      $phy_qty = $item_details['physicalQty'];
      $sys_qty = $item_details['systemQty'];
      $diff_qty = $phy_qty-$sys_qty;
      if($phy_qty>0 && $sys_qty>0) {
        $accu = round($phy_qty/$sys_qty*100,2);
        if($accu > 100) {
          $accu = '********';
        } else {
          $accu .= '%';
        }
      } else {
        $accu = '';
      }

      $tot_phy_qty += $phy_qty;
      $tot_sys_qty += $sys_qty;
      $tot_diff_qty += $diff_qty;

      $pdf->Ln();
      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,$item_name,'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,$category_name,'RTB',0,'L');
      $pdf->Cell($item_widths[3],6,$brand_name,'RTB',0,'L');            
      $pdf->Cell($item_widths[4],6,$phy_qty !== '' ? number_format($phy_qty,2,'.','') : '','RTB',0,'R');
      if((int)$_SESSION['utype'] === 3) {
        $pdf->Cell($item_widths[5],6,$sys_qty !== '' ? number_format($sys_qty,2,'.','') : '','RTB',0,'R');
        $pdf->Cell($item_widths[6],6,$diff_qty !== '' ? number_format($diff_qty,2,'.','') : '','RTB',0,'R');
      } else {
        $pdf->Cell($item_widths[5],6,'','RTB',0,'R');
        $pdf->Cell($item_widths[6],6,'','RTB',0,'R');
      }

      $pdf->Cell($item_widths[7],6,$accu,'RTB',0,'R');

      if($row_cntr === 37) {
        $pdf->AddPage('P','A4');
        $this->_add_page_heading_for_audit_report($pdf, $item_widths, $heading1, $heading2);
        $first_page = false; 
        $row_cntr = 0;
      }
      $row_cntr++;
    }

    $pdf->Ln();
    $pdf->Cell($totals_width,6,'TOTALS','LRTB',0,'R');
    $pdf->Cell($item_widths[4],6,$tot_phy_qty>0 ? number_format($tot_phy_qty,2,'.','') : '','LRTB',0,'R');
    $pdf->Cell($item_widths[5],6,$tot_phy_qty>0 ? number_format($tot_sys_qty,2,'.','') : '','RTB',0,'R');

    $pdf->Ln(40);
    $pdf->Cell(63,6,'PREPARED BY','B',0,'C');
    $pdf->Cell(63,6,'VERIFIED BY','B',0,'C');
    $pdf->Cell(63,6,'APPROVED BY','B',0,'C');

    $pdf->Ln();
    $pdf->Cell(189,6,'This report is printed on '.date("d-M-Y @ H:i:s").' hrs.','',0,'R');

    $pdf->Output();
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
    $cleaned_params['fromDate'] = Utilities::clean_string($form_data['fromDate']);
    $cleaned_params['toDate'] = Utilities::clean_string($form_data['toDate']);
    $cleaned_params['groupBy'] = Utilities::clean_string($form_data['groupBy']);
    $cleaned_params['balanceType'] = Utilities::clean_string($form_data['balanceType']);

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

  private function _add_page_heading_for_stock_report(&$pdf=null, $item_widths=[]) {
    $pdf->SetFont('Arial','B',8);
    $pdf->Cell($item_widths[0],  6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],  6,'Item Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],  6,'Category Name','RTB',0,'C');
    $pdf->Cell($item_widths[3],  6,'Brand Name','RTB',0,'C');
    $pdf->Cell($item_widths[4],  6,'Lot No.','RTB',0,'C');
    $pdf->Cell($item_widths[5],  6,'GST(%)','RTB',0,'C');        
    $pdf->Cell($item_widths[6],  6,'OP Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[7],  6,'PU Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[8],  6,'SR Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[9],  6,'AJ Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[10], 6,'ST Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[11], 6,'SA Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[12], 6,'PR Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[13], 6,'CL Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[14], 6,'Rate','RTB',0,'C');
    $pdf->Cell($item_widths[15], 6,'Amount','RTB',0,'C');
    $pdf->Cell($item_widths[16], 6,'M.R.P','RTB',0,'C');
    $pdf->SetFont('Arial','',8);
  }

  private function _add_page_heading_for_audit_report(&$pdf=null, $item_widths=[], $heading1='', $heading2='') {
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,5,$heading1,'',1,'C');
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(0,5,$heading2,'',1,'C');

    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Category Name','RTB',0,'C');        
    $pdf->Cell($item_widths[3],6,'Brand Name','RTB',0,'C');        
    $pdf->Cell($item_widths[4],6,'Phy.Qty','RTB',0,'C');
    $pdf->Cell($item_widths[5],6,'Sys.Qty','RTB',0,'C');      
    $pdf->Cell($item_widths[6],6,'Diff.','RTB',0,'C');    
    $pdf->Cell($item_widths[7],6,'Accu.%','RTB',0,'C');
    $pdf->SetFont('Arial','',9);
  }

}