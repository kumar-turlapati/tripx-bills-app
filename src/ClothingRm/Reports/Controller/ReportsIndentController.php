<?php 

namespace ClothingRm\Reports\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\PDF;

use ClothingRm\SalesIndent\Model\SalesIndent;

class ReportsIndentController {

  protected $indent_model;

  public function __construct() {
    $this->indent_model = new SalesIndent;
  }

  public function printIndent(Request $request) {

    $indent_no = $request->get('indentNo');
    $slno = 0;

    $indent_info_widths = [30,20,70,70];
    $customer_info_widths = [95,95];
    $item_widths = [10,95,35,25,25];
    $final_tot_width = [23,23,23,25,20,30,23,23];

    $indent_details = $this->indent_model->get_indent_details($indent_no);
    if(!is_array($indent_details) || count($indent_details)<0) {
      die('Invalid Indent No.');
    } else {
      $indent_tran_details = $indent_details['response']['indentDetails']['tranDetails'];
      $indent_item_details = $indent_details['response']['indentDetails']['itemDetails'];
    }

    // dump($indent_tran_details);
    // dump($indent_item_details);
    // exit;

    $placed_by = isset($indent_tran_details['customerName']) && $indent_tran_details['customerName'] !== '' ? $indent_tran_details['customerName'] : '';
    $referred_by = isset($indent_tran_details['agentName']) && $indent_tran_details['agentName'] !== '' ? $indent_tran_details['agentName'] : '';
    $mobile_no = isset($indent_tran_details['primaryMobileNo']) && $indent_tran_details['primaryMobileNo'] !== '' ? $indent_tran_details['primaryMobileNo'] : '';
    $campaign_name = $indent_tran_details['campaignName'];
    $print_date_time = date("d/m/Y H:ia");
    $operator_name = $_SESSION['uname'];

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');

    $pdf->SetFont('Arial','B',16);
    $pdf->Ln(2);    
    $pdf->Cell(0,0,'SALES INDENT','',1,'C');
    $pdf->SetFont('Arial','B',11);
    $pdf->Ln(5);

    # second row
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln();
    $pdf->Cell($indent_info_widths[0],6,'Indent No.','LRTB',0,'C');
    $pdf->Cell($indent_info_widths[1],6,'Indent Date','RTB',0,'C');
    $pdf->Cell($indent_info_widths[2],6,'Retailer Name','RTB',0,'C');
    $pdf->Cell($indent_info_widths[3],6,'Mobile No.','RTB',0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','B',13);
    $pdf->Cell($indent_info_widths[0],6,$indent_tran_details['indentNo'],'LRTB',0,'C');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell($indent_info_widths[1],6,date('d/m/Y', strtotime($indent_tran_details['indentDate'])),'RTB',0,'C');
    $pdf->Cell($indent_info_widths[2],6,$placed_by,'RTB',0,'C');
    $pdf->Cell($indent_info_widths[3],6,$mobile_no,'RTB',0,'C');

    # third row
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln();
    $pdf->Cell(95,6,'Wholesaler Name','LRTB',0,'C');
    $pdf->Cell(95,6,'Campaign','RTB',0,'C');
    $pdf->Ln();

    $pdf->SetFont('Arial','',9);
    $pdf->Cell(95,6,$referred_by,'LRTB',0,'C');
    $pdf->Cell(95,6,$campaign_name,'RTB',0,'C');

    # item details
    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Product Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'HSN/SAC Code','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Rate/Unit','RTB',0,'C');
    $pdf->SetFont('Arial','',9);
    $pdf->Ln();

    $tot_bill_value = $tot_items_qty = 0;
    foreach($indent_item_details as $item_details) {
      $slno++;
      $amount = round($item_details['itemQty']*$item_details['itemRate'], 2);

      $tot_bill_value += $amount;
      $tot_items_qty += $item_details['itemQty'];

      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,substr($item_details['itemName'],0,20),'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,'','RTB',0,'L');
      $pdf->Cell($item_widths[3],6,$item_details['itemQty'],'RTB',0,'R');
      $pdf->Cell($item_widths[4],6,$item_details['itemRate'],'RTB',0,'R');
      $pdf->Ln();
    }

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(140,6,'TOTALS','LRTB',0,'R');
    $pdf->Cell(25,6,number_format($tot_items_qty,2,'.',''),'LRTB',0,'R');
    $pdf->Cell(25,6,'','LRTB',0,'R');    
    $pdf->SetFont('Arial','B',10);
    $pdf->Ln();

    $pdf->Cell(60,10,'Buyer Signature','LRTB',0,'R');
    $pdf->Cell(130,10,'','LRTB',0,'R');

    $pdf->Ln();    
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(60,10,'Print date & time: '.$print_date_time,'LRTB',0,'L');
    $pdf->Cell(60,10,'Prepared by: '.$operator_name,'RTB',0,'L');
    $pdf->Cell(70,10,'Authorized Signature: ','RTB',0,'L');

    $pdf->Output();
  }

  public function printIndentWoRate(Request $request) {
    $indent_no = $request->get('indentNo');
    $slno = 0;

    $indent_info_widths = [30,20,70,70];
    $customer_info_widths = [95,95];
    $item_widths = [10,70,30,18,62];
    $final_tot_width = [23,23,23,25,20,30,23,23];

    $indent_details = $this->indent_model->get_indent_details($indent_no);
    if(!is_array($indent_details) || count($indent_details)<0) {
      die('Invalid Indent No.');
    } else {
      $indent_tran_details = $indent_details['response']['indentDetails']['tranDetails'];
      $indent_item_details = $indent_details['response']['indentDetails']['itemDetails'];
    }

    $placed_by = isset($indent_tran_details['customerName']) && $indent_tran_details['customerName'] !== '' ? $indent_tran_details['customerName'] : '';
    $referred_by = isset($indent_tran_details['agentName']) && $indent_tran_details['agentName'] !== '' ? $indent_tran_details['agentName'] : '';
    $mobile_no = isset($indent_tran_details['primaryMobileNo']) && $indent_tran_details['primaryMobileNo'] !== '' ? $indent_tran_details['primaryMobileNo'] : '';
    $print_date_time = date("d/m/Y H:ia");
    $operator_name = $_SESSION['uname'];

    // dump($indent_details);
    // exit;

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');

    $pdf->SetFont('Arial','B',16);
    $pdf->Ln(2);    
    $pdf->Cell(0,0,'SALES INDENT','',1,'C');
    $pdf->SetFont('Arial','B',11);
    $pdf->Ln(5);

    # second row
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln();
    $pdf->Cell($indent_info_widths[0],6,'Indent No.','LRTB',0,'C');
    $pdf->Cell($indent_info_widths[1],6,'Indent Date','RTB',0,'C');
    $pdf->Cell($indent_info_widths[2],6,'Retailer Name','RTB',0,'C');
    $pdf->Cell($indent_info_widths[3],6,'Mobile No.','RTB',0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell($indent_info_widths[0],6,$indent_tran_details['indentNo'],'LRTB',0,'C');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell($indent_info_widths[1],6,date('d/m/Y', strtotime($indent_tran_details['indentDate'])),'RTB',0,'C');
    $pdf->Cell($indent_info_widths[2],6,$placed_by,'RTB',0,'C');
    $pdf->Cell($indent_info_widths[3],6,$mobile_no,'RTB',0,'C');

    # third row
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln();
    $pdf->Cell(95,6,'Wholesaler Name','LRTB',0,'C');
    $pdf->Cell(95,6,'Campaign','RTB',0,'C');
    $pdf->Ln();

    $pdf->Cell(95,6,$referred_by,'LRTB',0,'C');
    $pdf->Cell(95,6,'GOA CONFERENCE','RTB',0,'C');

    # item details
    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Product Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'HSN/SAC Code','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Comments/Notes','RTB',0,'C');
    $pdf->SetFont('Arial','',9);
    $pdf->Ln();

    $tot_bill_value = $tot_items_qty = 0;
    foreach($indent_item_details as $item_details) {
      $slno++;
      $amount = round($item_details['itemQty']*$item_details['itemRate'], 2);

      $tot_bill_value += $amount;
      $tot_items_qty += $item_details['itemQty'];

      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,substr($item_details['itemName'],0,20),'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,'','RTB',0,'L');
      $pdf->Cell($item_widths[3],6,$item_details['itemQty'],'RTB',0,'R');
      $pdf->Cell($item_widths[4],6,'','RTB',0,'R');
      $pdf->Ln();
    }

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(110,6,'TOTALS','LR',0,'R');
    $pdf->Cell(18,6,number_format($tot_items_qty,2,'.',''),'R',0,'R');
    $pdf->Cell(62,6,'','R',0,'R');
    $pdf->SetFont('Arial','B',10);
    $pdf->Ln();

    $pdf->Cell(60,10,'Buyer Signature','LRTB',0,'R');
    $pdf->Cell(130,10,'','LRTB',0,'R');

    $pdf->Ln();
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(60,10,'Print date & time: '.$print_date_time,'LRTB',0,'L');
    $pdf->Cell(60,10,'Prepared by: '.$operator_name,'RTB',0,'L');
    $pdf->Cell(70,10,'Authorized Signature: ','RTB',0,'L');

    $pdf->Output();    
  }

  public function indentItemAvailability(Request $request) {
    
    $filter_params = $total_items = [];
    
    $item_widths = array(10,85,35,35,25);
    $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3];
    
    $slno = $tot_qty = 0; 
    
    $filter_params['perPage'] = 100;
    $filter_params['pageNo'] = 1;
    if(!is_null($request->get('locationCode')) && $request->get('locationCode') !== '') {
      $filter_params['locationCode'] = Utilities::clean_string($request->get('locationCode'));
    }
    if(!is_null($request->get('nearbyQty')) && $request->get('nearbyQty') !== '') {
      $filter_params['nearbyQty'] = Utilities::clean_string($request->get('nearbyQty'));
    }

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_names[$location_key_a[0]] = $location_value;
    }

    $indent_item_details = $this->indent_model->get_indent_item_avail($filter_params);
    if($indent_item_details['status']===false) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $total_items = $indent_item_details['response']['results'];
      $total_pages = $indent_item_details['response']['total_pages'];
      if($total_pages>1) {
        for($i=2;$i<=$total_pages;$i++) {
          $filter_params['pageNo'] = $i;
          $indent_item_details = $this->indent_model->get_indent_item_avail($filter_params);
          if($indent_item_details['status']) {
            $total_items = array_merge($total_items,$indent_item_details['response']['results']);
          }
        }
      }
      $heading1 = 'Item Availability Report For Indents';
      $heading2 = 'As on '.date('jS F, Y');
      if(isset($filter_params['nearbyQty'])) {
        $heading3  = 'Threshold Qty <= '.$filter_params['nearbyQty'];
      } else {
        $heading3 =  ''; 
      }
      if(isset($filter_params['locationCode'])) {
        $heading3 .= ', Store Name - '.$location_names[$filter_params['locationCode']];
      }
    }

    // echo '<pre>';
    // print_r($total_items);
    // echo '</pre>';
    // exit;

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');
    $pdf->setTitle($heading1.' - '.date('jS F, Y'));

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');

    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');
    
    if($heading3 !== '') {
      $pdf->SetFont('Arial','BU',10);
      $pdf->Ln(5);
      $pdf->Cell(0,0,'[ FILTERS: '.$heading3.' ]','',1,'C');
    }

    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Category Name','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Store Name','RTB',0,'C');    
    $pdf->Cell($item_widths[4],6,'Available Qty.','RTB',0,'C');        
    $pdf->SetFont('Arial','',9);

    foreach($total_items as $item_details) {
      $slno++;

      $item_name = $item_details['itemName'];
      $category_name = $item_details['categoryName'];
      $store_name = $location_ids[$item_details['locationID']];
      $closing_qty = $item_details['closingQty'];

      $tot_qty += $closing_qty;
      
      $pdf->Ln();
      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,$item_name,'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,$category_name,'RTB',0,'L');
      $pdf->Cell($item_widths[3],6,$store_name,'RTB',0,'L');            
      $pdf->Cell($item_widths[4],6,number_format($closing_qty,2,'.',''),'RTB',0,'R');
    }

    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell($totals_width,6,'T O T A L S','LRTB',0,'R');
    $pdf->Cell($item_widths[4],6,number_format($tot_qty,2,'.',''),'LRTB',0,'R');
    $pdf->SetFont('Arial','B',11);    

    $pdf->Output();
  }

  public function indentItemwiseBooked(Request $request) {
    
    $filter_params = $total_items = [];
    
    $item_widths = array(10,100,35,35);
    $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2];
    
    $slno = $tot_qty = 0; 
    
    $filter_params['perPage'] = 100;
    $filter_params['pageNo'] = 1;
    if(!is_null($request->get('campaignCode')) && $request->get('campaignCode') !== '') {
      $filter_params['campaignCode'] = Utilities::clean_string($request->get('campaignCode'));
    }
    if(!is_null($request->get('fromDate')) && $request->get('fromDate') !== '') {
      $filter_params['fromDate'] = Utilities::clean_string($request->get('fromDate'));
      $from_date = $request->get('fromDate');
    } else {
      $from_date = date('d-m-Y');
    }
    if(!is_null($request->get('toDate')) && $request->get('toDate') !== '') {
      $filter_params['toDate'] = Utilities::clean_string($request->get('toDate'));
      $to_date = $request->get('toDate');
    } else {
      $to_date = date('d-m-Y');
    }

    $indent_item_details = $this->indent_model->get_indents_itemwise($filter_params);
    if($indent_item_details['status']===false) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $total_items = $indent_item_details['response']['results'];
      $total_pages = $indent_item_details['response']['total_pages'];
      if($total_pages>1) {
        for($i=2;$i<=$total_pages;$i++) {
          $filter_params['pageNo'] = $i;
          $indent_item_details = $this->indent_model->get_indents_itemwise($filter_params);
          if($indent_item_details['status']) {
            $total_items = array_merge($total_items,$indent_item_details['response']['results']);
          }
        }
      }
      $heading1 = 'Itemwise Indent Bookings';
      $heading2 = 'From '.date('jS F, Y', strtotime($from_date)).' To '.date('jS F, Y', strtotime($to_date));
    }

    // echo '<pre>';
    // print_r($total_items);
    // echo '</pre>';
    // exit;

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');
    $pdf->setTitle($heading1.' - '.date('jS F, Y'));

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');

    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Category Name','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Indent Qty.','RTB',0,'C');        
    $pdf->SetFont('Arial','',9);

    foreach($total_items as $item_details) {
      $slno++;

      $item_name = $item_details['itemName'];
      $closing_qty = $item_details['indentQty'];
      $category_name = $item_details['categoryName'];

      $tot_qty += $closing_qty;
      
      $pdf->Ln();
      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,$item_name,'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,$category_name,'RTB',0,'L');
      $pdf->Cell($item_widths[3],6,number_format($closing_qty,2,'.',''),'RTB',0,'R');
    }

    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell($totals_width,6,'T O T A L S','LRTB',0,'R');
    $pdf->Cell($item_widths[3],6,number_format($tot_qty,2,'.',''),'LRTB',0,'R');
    $pdf->SetFont('Arial','B',11);    

    $pdf->Output();
  }

  public function indentAgentwiseBooked(Request $request) {
    
    $filter_params = $total_items = [];
    
    $item_widths = array(10,100,35,35);
    $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2];
    
    $slno = $tot_qty = 0; 
    
    $filter_params['perPage'] = 100;
    $filter_params['pageNo'] = 1;
    if(!is_null($request->get('campaignCode')) && $request->get('campaignCode') !== '') {
      $filter_params['campaignCode'] = Utilities::clean_string($request->get('campaignCode'));
    }
    if(!is_null($request->get('fromDate')) && $request->get('fromDate') !== '') {
      $filter_params['fromDate'] = Utilities::clean_string($request->get('fromDate'));
      $from_date = $request->get('fromDate');
    } else {
      $from_date = date('d-m-Y');
    }
    if(!is_null($request->get('toDate')) && $request->get('toDate') !== '') {
      $filter_params['toDate'] = Utilities::clean_string($request->get('toDate'));
      $to_date = $request->get('toDate');
    } else {
      $to_date = date('d-m-Y');
    }

    $indent_item_details = $this->indent_model->get_indents_agentwise($filter_params);
    if($indent_item_details['status']===false) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $total_items = $indent_item_details['response']['results'];
      $total_pages = $indent_item_details['response']['total_pages'];
      if($total_pages>1) {
        for($i=2;$i<=$total_pages;$i++) {
          $filter_params['pageNo'] = $i;
          $indent_item_details = $this->indent_model->get_indents_itemwise($filter_params);
          if($indent_item_details['status']) {
            $total_items = array_merge($total_items,$indent_item_details['response']['results']);
          }
        }
      }
      $heading1 = 'Agentwise Indent Bookings';
      $heading2 = 'From '.date('jS F, Y', strtotime($from_date)).' To '.date('jS F, Y', strtotime($to_date));
    }

    // echo '<pre>';
    // print_r($total_items);
    // echo '</pre>';
    // exit;

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');
    $pdf->setTitle($heading1.' - '.date('jS F, Y'));

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');

    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Agent Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'State Name','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Indent Qty.','RTB',0,'C');        
    $pdf->SetFont('Arial','',9);

    foreach($total_items as $item_details) {
      $slno++;

      $agent_name = $item_details['agentName'];
      $closing_qty = $item_details['indentQty'];
      $state_name = $item_details['stateName'];

      $tot_qty += $closing_qty;
      
      $pdf->Ln();
      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,$agent_name,'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,$state_name,'RTB',0,'L');
      $pdf->Cell($item_widths[3],6,number_format($closing_qty,2,'.',''),'RTB',0,'R');
    }

    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell($totals_width,6,'T O T A L S','LRTB',0,'R');
    $pdf->Cell($item_widths[3],6,number_format($tot_qty,2,'.',''),'LRTB',0,'R');
    $pdf->SetFont('Arial','B',11);

    $pdf->Output();
  }

  public function indentStatewiseBooked(Request $request) {
    
    $filter_params = $total_items = [];
    
    $item_widths = array(10,100,35);
    $totals_width = $item_widths[0] + $item_widths[1];
    
    $slno = $tot_qty = 0; 
    
    $filter_params['perPage'] = 100;
    $filter_params['pageNo'] = 1;
    if(!is_null($request->get('campaignCode')) && $request->get('campaignCode') !== '') {
      $filter_params['campaignCode'] = Utilities::clean_string($request->get('campaignCode'));
    }
    if(!is_null($request->get('fromDate')) && $request->get('fromDate') !== '') {
      $filter_params['fromDate'] = Utilities::clean_string($request->get('fromDate'));
      $from_date = $request->get('fromDate');
    } else {
      $from_date = date('d-m-Y');
    }
    if(!is_null($request->get('toDate')) && $request->get('toDate') !== '') {
      $filter_params['toDate'] = Utilities::clean_string($request->get('toDate'));
      $to_date = $request->get('toDate');
    } else {
      $to_date = date('d-m-Y');
    }

    $indent_item_details = $this->indent_model->get_indents_statewise($filter_params);
    if($indent_item_details['status']===false) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $total_items = $indent_item_details['response']['results'];
      $total_pages = $indent_item_details['response']['total_pages'];
      if($total_pages>1) {
        for($i=2;$i<=$total_pages;$i++) {
          $filter_params['pageNo'] = $i;
          $indent_item_details = $this->indent_model->get_indents_statewise($filter_params);
          if($indent_item_details['status']) {
            $total_items = array_merge($total_items,$indent_item_details['response']['results']);
          }
        }
      }
      $heading1 = 'Statewise Indent Bookings';
      $heading2 = 'From '.date('jS F, Y', strtotime($from_date)).' To '.date('jS F, Y', strtotime($to_date));
    }

    // echo '<pre>';
    // print_r($total_items);
    // echo '</pre>';
    // exit;

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');
    $pdf->setTitle($heading1.' - '.date('jS F, Y'));

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');

    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'State Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Indent Qty.','RTB',0,'C');        
    $pdf->SetFont('Arial','',9);

    foreach($total_items as $item_details) {
      $slno++;

      $closing_qty = $item_details['indentQty'];
      $state_name = $item_details['stateName'];

      $tot_qty += $closing_qty;
      
      $pdf->Ln();
      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,$state_name,'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,number_format($closing_qty,2,'.',''),'RTB',0,'R');
    }

    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell($totals_width,6,'T O T A L S','LRTB',0,'R');
    $pdf->Cell($item_widths[2],6,number_format($tot_qty,2,'.',''),'LRTB',0,'R');
    $pdf->SetFont('Arial','B',11);

    $pdf->Output();
  }


}