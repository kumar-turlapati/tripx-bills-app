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

class InventoryReportsController {

  protected $views_path;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->inven_api = new Inventory;
    $this->products_api = new Products;
    $this->flash = new Flash;    
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

        $heading1 = 'Test Data - '.$location_name;
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