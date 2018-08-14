<?php 

namespace ClothingRm\Reports\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\PDF;

use ClothingRm\Openings\Model\Openings;

class ReportsOpBalController {

  protected $indent_model;

  public function __construct() {
    $this->opbal_model = new Openings;   
  }

  public function opBalReport(Request $request) {
    
    $filter_params = $total_items =  $csv_headings = [];
    $location_code = $heading3 = '';
    $tot_qty = $tot_amount = 0;

    $format = !is_null($request->get('format')) && $request->get('format') !== '' ? Utilities::clean_string($request->get('format')) : 'pdf';
    
    $slno = $tot_qty = 0; 

    $filter_params['pageNo'] = 1;
    $filter_params['perPage'] = 500;
    if(!is_null($request->get('fromDate')) && $request->get('fromDate') !== '') {
      $from_date = Utilities::clean_string($request->get('fromDate'));
      $filter_params['fromDate'] = $from_date;
    } else {
      $from_date = date('d-m-Y');
    }
    if(!is_null($request->get('toDate')) && $request->get('toDate') !== '') {
      $to_date = Utilities::clean_string($request->get('toDate'));
      $filter_params['toDate'] = $to_date;
    } else {
      $to_date = date('d-m-Y');
    }
    if(!is_null($request->get('locationCode')) && $request->get('locationCode') !== '') {
      $location_code = Utilities::clean_string($request->get('locationCode'));
      $filter_params['locationCode'] = $location_code;
    }

    $opbal_details = $this->opbal_model->opbal_list($filter_params);
    if($opbal_details['status']===false) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $total_items = $opbal_details['openings'];
      $total_pages = $opbal_details['total_pages'];
      if($total_pages>1) {
        for($i=2;$i<=$total_pages;$i++) {
          $filter_params['pageNo'] = $i;
          $opbal_details = $this->opbal_model->opbal_list($filter_params);
          if($opbal_details['status']) {
            $total_items = array_merge($total_items,$opbal_details['openings']);
          }
        }
      }
      # ---------- get location codes from api -----------------------
      $client_locations = Utilities::get_client_locations(true);
      foreach($client_locations as $location_key => $location_value) {
        $location_key_a = explode('`', $location_key);
        $location_ids[$location_key_a[1]] = $location_value;
        $location_names[$location_key_a[0]] = $location_value;
      }
      #-----------------------------------------------------------------
      $heading1 = 'Inventory Opening Balance Report';
      $heading2 = 'From '.date('jS F, Y', strtotime($from_date)).' To '.date('jS F, Y', strtotime($to_date));
      $csv_headings = [ [$heading1], [$heading2] ];
      if($location_code !== '') {
        $heading3  = 'Store Name: '.$location_names[$location_code];
      }
      if($heading3 !== '') {
        $csv_headings[] = [$heading3];
      }      
    }

    // echo '<pre>';
    // print_r($total_items);
    // echo '</pre>';
    // exit;

    // if format is csv dump csv file for download. otherwise go with pdf
    if($format === 'csv') {
      Utilities::download_as_CSV_attachment('OpeningBalancesReport', $csv_headings, $total_items);
      return;
    }    

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');
    $pdf->setTitle($heading1.' - '.date('jS F, Y'));

    $pdf->SetFont('Arial','B',13);
    $pdf->Cell(0,0,$heading1,'',1,'C');

    $pdf->SetFont('Arial','B',8);
    $pdf->Ln(4);
    $pdf->Cell(0,0,$heading2,'',1,'C');
    
    if($heading3 !== '') {
      $pdf->Ln(4);
      $pdf->SetFont('Arial','B',9);
      $pdf->Cell(0,0,$heading3,'',1,'C');
    }    
    
    $item_widths = array(10,55,25,23,15,15,12,20,15);
    $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2];

    $pdf->SetFont('Arial','B',8);
    $pdf->Ln(4);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Lot No.','RTB',0,'C');    
    $pdf->Cell($item_widths[3],6,'Category','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'OpenQty.','RTB',0,'C');
    $pdf->Cell($item_widths[5],6,'Pur.Rate','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'Tax%','RTB',0,'C');
    $pdf->Cell($item_widths[7],6,'Amount','RTB',0,'C');
    $pdf->Cell($item_widths[8],6,'Rate','RTB',0,'C');
    $pdf->SetFont('Arial','',8);
    $pdf->Ln();    

    foreach($total_items as $item_details) {
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

      $item_amount = round($purchase_rate*$opening_qty,2);
      $tax_amount = 0;
/*      if($tax_percent>0) {
        $tax_amount = round(($item_amount*$tax_percent)/100,2);
      } else {
        $tax_amount = 0;
      }*/

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

  public function indentAgentwiseBooked(Request $request) {
    
    $filter_params = $total_items = [];
    $csv_headings = [];
    
    $item_widths = array(10,100,35,35);
    $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2];

    $format = !is_null($request->get('format')) && $request->get('format') !== '' ? Utilities::clean_string($request->get('format')) : 'pdf';
    
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
      $heading1 = 'Wholesalerwise / Agentwise Indent Bookings';
      $heading2 = 'From '.date('jS F, Y', strtotime($from_date)).' To '.date('jS F, Y', strtotime($to_date));
      $csv_headings = [ [$heading1], [$heading2] ];
    }

    // if format is csv dump csv file for download. otherwise go with pdf
    if($format === 'csv') {
      Utilities::download_as_CSV_attachment('IndentAgentwiseBooked', $csv_headings, $total_items);
      return;
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
    $pdf->Cell($item_widths[1],6,'Wholesaler / Agent Name','RTB',0,'C');
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

    $format = !is_null($request->get('format')) && $request->get('format') !== '' ? Utilities::clean_string($request->get('format')) : 'pdf';
    
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
      $csv_headings = [ [$heading1], [$heading2] ];      
    }

    // echo '<pre>';
    // print_r($total_items);
    // echo '</pre>';
    // exit;

    // if format is csv dump csv file for download. otherwise go with pdf
    if($format === 'csv') {
      Utilities::download_as_CSV_attachment('IndentStatewiseBooked', $csv_headings, $total_items);
      return;
    }    

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

  public function indentRegister(Request $request) {
    $filter_params = $total_indents = $agents_a = $campaigns_a = [];
    
    $item_widths = array(10,33,47,47,16,17,21);
    $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3] + $item_widths[4];

    $format = !is_null($request->get('format')) && $request->get('format') !== '' ? Utilities::clean_string($request->get('format')) : 'pdf';    
    
    $slno = $tot_qty = $tot_amount = 0;
    $heading3 = $campaign_code = $agent_code = '';
    
    $filter_params['perPage'] = 100;
    $filter_params['pageNo'] = 1;
    if(!is_null($request->get('campaignCode')) && $request->get('campaignCode') !== '') {
      $campaign_code = Utilities::clean_string($request->get('campaignCode'));
      $filter_params['campaignCode'] = $campaign_code;
    }
    if(!is_null($request->get('agentCode')) && $request->get('agentCode') !== '') {
      $agent_code =  Utilities::clean_string($request->get('agentCode'));
      $filter_params['agentCode'] = $agent_code;
    }
    if(!is_null($request->get('fromDate')) && $request->get('fromDate') !== '') {
      $from_date =Utilities::clean_string($request->get('fromDate'));
      $filter_params['fromDate'] = $from_date;
    } else {
      $from_date = date('d-m-Y');
    }
    if(!is_null($request->get('toDate')) && $request->get('toDate') !== '') {
      $to_date = Utilities::clean_string($request->get('toDate'));
      $filter_params['toDate'] = $to_date;
    } else {
      $to_date = date('d-m-Y');
    }
    if(!is_null($request->get('showRate')) && $request->get('showRate') !== '') {
      $show_rate = Utilities::clean_string($request->get('showRate'));
    } else {
      $show_rate = 'no';
    }

    # ---------- get business users -------------------------------------------
    $agents_response = $this->bu_model->get_business_users(['userType' => 90]);
    if($agents_response['status']) {
      foreach($agents_response['users'] as $user_details) {
        $agents_a[$user_details['userCode']] = $user_details['userName'];
      }
    }
    $campaigns_response = $this->camp_model->get_live_campaigns();
    if($campaigns_response['status']) {
      $campaign_keys = array_column($campaigns_response['campaigns'], 'campaignCode');
      $campaign_names = array_column($campaigns_response['campaigns'], 'campaignName');
      $campaigns_a = array_combine($campaign_keys, $campaign_names);
    }
    #---------------------------------------------------------------------------

    $indents_response = $this->indent_model->get_all_indents($filter_params);
    if($indents_response['status']===false) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $total_indents = $indents_response['response']['indents'];
      $total_pages = $indents_response['response']['total_pages'];
      if($total_pages>1) {
        for($i=2;$i<=$total_pages;$i++) {
          $filter_params['pageNo'] = $i;
          $indents_response = $this->indent_model->get_all_indents($filter_params);
          if($indents_response['status']) {
            $total_indents = array_merge($total_indents,$indents_response['response']['indents']);
          }
        }
      }
      $heading1 = 'Indent Register';
      $heading2 = 'From '.date('jS F, Y', strtotime($from_date)).' To '.date('jS F, Y', strtotime($to_date));
      $csv_headings = [ [$heading1], [$heading2] ];      
      if(isset($campaigns_a[$campaign_code]) && $campaign_code !== '') {
        $heading3  = 'Campaign Name: '.$campaigns_a[$campaign_code];
      } else {
        $heading3 =  ''; 
      }
      if($agent_code !== '' && isset($agents_a[$agent_code])) {
        $heading3 .= ', Wholesaler / Agent Name: '.$agents_a[$agent_code];
      }
      if($heading3 !== '') {
        $csv_headings[] = [$heading3];
      }      
    }

    // if format is csv dump csv file for download. otherwise go with pdf
    if($format === 'csv') {
      Utilities::download_as_CSV_attachment('IndentRegister', $csv_headings, $total_indents);
      return;
    }    

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
      $pdf->SetFont('Arial','B',9);
      $pdf->Ln(5);
      $pdf->Cell(0,0,$heading3,'',1,'C');
    }
    
    $pdf->SetFont('Arial','B',8);
    $pdf->Ln(3);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Indent No. & Date','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Customer Name','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Wholesaler / Agent Name','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Total Items','RTB',0,'C');
    $pdf->Cell($item_widths[5],6,'Total Qty.','RTB',0,'C');
    if($show_rate === 'yes') {
      $pdf->Cell($item_widths[6],6,'Indent Value','RTB',0,'C');    
    }
    $pdf->SetFont('Arial','',8);
    foreach($total_indents as $indent_details) {
      $slno++;

      $indent_no_date = $indent_details['indentNo'].' / '.date("d-m-Y", strtotime($indent_details['indentDate']));
      $customer_name = substr($indent_details['customerName'],0,27);
      $agent_name = substr($indent_details['agentName'],0,24);
      $indent_qty = $indent_details['indentQty'];
      $indent_value = $indent_details['netpay'];
      $indent_items = $indent_details['totalItems'];

      $tot_qty += $indent_qty;
      $tot_amount += $indent_value;

      $pdf->Ln();
      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,$indent_no_date,'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,$customer_name,'RTB',0,'L');
      $pdf->Cell($item_widths[3],6,$agent_name,'RTB',0,'L');
      $pdf->Cell($item_widths[4],6,number_format($indent_items,2,'.',''),'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,number_format($indent_qty,2,'.',''),'RTB',0,'R');
      if($show_rate === 'yes') {
        $pdf->Cell($item_widths[6],6,number_format($indent_value,2,'.',''),'RTB',0,'R');
      }
    }

    $pdf->Ln();
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($totals_width,6,'T O T A L S','LRTB',0,'R');
    $pdf->Cell($item_widths[5],6,number_format($tot_qty,2,'.',''),'LRTB',0,'R');
    if($show_rate === 'yes') {
      $pdf->Cell($item_widths[6],6,number_format($tot_amount,2,'.',''),'LRTB',0,'R');    
    }
    $pdf->SetFont('Arial','B',9);

    $pdf->Output();
  }

  public function indentDispatchSummary(Request $request) {

    $filter_params = $campaigns_a = [];
    
    $item_widths = array(10,33,47,47,16,17,21);
    $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3] + $item_widths[4];

    $slno = $tot_qty = $tot_amount = 0;
    $heading3 = $campaign_code = '';
    
    $filter_params['perPage'] = 1000;
    $filter_params['pageNo'] = 1;
    if(!is_null($request->get('campaignCode')) && $request->get('campaignCode') !== '') {
      $campaign_code = Utilities::clean_string($request->get('campaignCode'));
      $filter_params['campaignCode'] = $campaign_code;
    }
    #---------------------------------------------------------------------------
    $campaigns_response = $this->camp_model->list_campaigns();
    if($campaigns_response['status']) {
      $campaign_keys = array_column($campaigns_response['campaigns']['campaigns'], 'campaignCode');
      $campaign_names = array_column($campaigns_response['campaigns']['campaigns'], 'campaignName');
      $campaigns_a = array_combine($campaign_keys, $campaign_names);
    }
    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_names[$location_key_a[0]] = $location_value;
    }
    #---------------------------------------------------------------------------

    $indents_response = $this->indent_model->get_indent_dispatch_register($filter_params);
    if($indents_response['status']===false) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $total_items = $indents_response['response']['results'];
      $total_pages = $indents_response['response']['total_pages'];
      if($total_pages>1) {
        for($i=2;$i<=$total_pages;$i++) {
          $filter_params['pageNo'] = $i;
          $indents_response = $this->indent_model->get_indent_dispatch_register($filter_params);
          if($indents_response['status']) {
            $total_items = array_merge($total_items,$indents_response['response']['results']);
          }
        }
      }
      $heading1 = 'Dispatch Summary';
      if(isset($campaigns_a[$campaign_code]) && $campaign_code !== '') {
        $heading2  = 'Campaign Name: '.$campaigns_a[$campaign_code];
      } else {
        $heading2 =  ''; 
      }
    }

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->setTitle($heading1.' - '.date('jS F, Y'));

    $old_item_name = $new_item_name  = $total_items[0]['itemName'];
    $this->_add_page_heading_for_dispatch_reg($pdf, $heading1, $heading2, $item_widths, $new_item_name);
    $sl_no = $item_cntr = $tot_item_qty = 0;
    foreach($total_items as $item_details) {
      $slno++;
      $new_item_name = $item_details['itemName'];
      if($old_item_name !== $new_item_name) {
        $old_item_name = $new_item_name = $item_details['itemName'];        
        $item_cntr++;
        $this->_add_item_total_for_dispatch_reg($pdf, $tot_item_qty, $totals_width, $item_widths);
        $this->_add_page_heading_for_dispatch_reg($pdf, $heading1, $heading2, $item_widths, $new_item_name);
        // if($item_cntr >= 3) {
        //   break;
        // }
        $old_item_name = $new_item_name;
        $tot_item_qty = 0;
      }      
      $indent_no_date = $item_details['indentNo'].' / '.date("d-m-Y", strtotime($item_details['indentDate']));
      $customer_name = substr($item_details['customerName'],0,27);
      $agent_name = isset($item_details['agentName']) ? substr($item_details['agentName'],0,24) : '';
      $order_qty = $item_details['orderQty'];
      $indent_value = $item_details['indentValue'];
      $location_id = $item_details['locationID'];
      $remarks = $item_details['remarks'];

      $tot_item_qty += $order_qty;

      $pdf->SetFont('Arial','',7);
      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,$indent_no_date,'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,$customer_name,'RTB',0,'L');
      $pdf->Cell($item_widths[3],6,$agent_name,'RTB',0,'L');
      $pdf->Cell($item_widths[4],6,$order_qty,'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,$location_ids[$location_id],'RTB',0,'L');
      $pdf->Cell($item_widths[6],6,$indent_value,'RTB',0,'R');
      $pdf->Ln();
      if($remarks !== '') {
        $pdf->SetFont('Arial','',6);
        $pdf->MultiCell(array_sum($item_widths),4,'REMARKS - '.$item_details['indentNo'].': '.$remarks,'LTRB','C');
      }
    }
    $this->_add_item_total_for_dispatch_reg($pdf, $tot_item_qty, $totals_width, $item_widths);
    $pdf->Output();
  }

  public function _add_item_total_for_dispatch_reg(&$pdf, $tot_item_qty=0, $totals_width=0, $item_widths=[]) {
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($totals_width,6,number_format($tot_item_qty,2,'.',''),'LRTB',0,'R');
    $pdf->Cell($item_widths[5],6,'','RTB',0,'R');
    $pdf->Cell($item_widths[6],6,'','RTB',0,'R');
  }

  public function _add_page_heading_for_dispatch_reg(&$pdf, $heading1='', $heading2='', $item_widths=[], $item_name='') {
    $pdf->AddPage('P','A4');
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');

    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');

    $pdf->setTextColor(245,11,26);
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(4);
    $pdf->Cell(0,0,'Item Name: [ '.$item_name.' ]','',1,'C');
    $pdf->setTextColor(0,0,0);    

    $pdf->SetFont('Arial','B',8);
    $pdf->Ln(3);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Indent No./Date','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Customer Name','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Wholesaler/Agent Name','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Order Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[5],6,'Store Name','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'Indent Value','RTB',0,'C');    
    $pdf->Ln();
  }
}

/*
    // $pdf->SetFont('Arial','',9);
    // $pdf->Cell(170,6,$remarks,'RT',0,'L');
    // $pdf->Ln();
*/