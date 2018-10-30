<?php

namespace ClothingRm\Reports\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\PDF;

class ReportsInventoryController {
  private $inven_api;

  public function __construct() {
    $this->views_path = __DIR__.'/../Views/';
    $this->inven_api = new \ClothingRm\Inventory\Model\Inventory;
  }

  // Print Stock Report
  public function stockReport(Request $request) {

    $item_widths = array(10,60,28,15,20,20,20,17);

    $totals_width = $item_widths[0] + $item_widths[1] + $item_widths[2] + $item_widths[3];
    
    $totals_width1 = $item_widths[5] + $item_widths[6];
    
    $slno = $tot_amount = $tot_qty = 0; 
    $total_items = [];
    $client_locations = Utilities::get_client_locations();

    $location_code = !is_null($request->get('locationCode')) ? $request->get('locationCode') : '';
    if($location_code === '') {
      die('<h3>Please choose a Store name to get the stock report</h3>');
    } elseif(isset($client_locations[$location_code])) {
      $location_name = $client_locations[$location_code];
    } else {
      $location_name = 'Invalid';
    }

    $params = [
      'locationCode' => $location_code,
    ];

    $inven_api_response = $this->inven_api->get_stock_report($params);
    
    // dump($inven_api_response);
    // exit;

    if($inven_api_response['status']===false) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $total_items = $inven_api_response['results']['results'];
      $total_pages = $inven_api_response['results']['total_pages'];
      if($total_pages>1) {
        for($i=2;$i<=$total_pages;$i++) {
          $params['pageNo'] = $i;
          $params['perPage'] = 100;
          $inven_api_response = $this->inven_api->get_stock_report($params);
          if($inven_api_response['status']) {
            $total_items = array_merge($total_items,$inven_api_response['results']['results']);
          }
        }
      }
      $heading1 = 'Stock Report - '.$location_name;
      $heading2 = 'As on '.date('jS F, Y');
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
    $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Lot No.','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'GST(%)','RTB',0,'C');        
    $pdf->Cell($item_widths[4],6,'Closing Qty.','RTB',0,'C');        
    $pdf->Cell($item_widths[5],6,'Rate/Unit','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'Amount','RTB',0,'C');
    $pdf->Cell($item_widths[7],6,'M.R.P','RTB',0,'C');    
    
    $pdf->SetFont('Arial','',9);

    foreach($total_items as $item_details) {
      $slno++;

      $item_name = $item_details['itemName'];
      $lot_no = $item_details['lotNo'];
      $closing_qty = $item_details['closingQty'];
      $closing_rate = $item_details['purchaseRate'];
      $tax_percent = $item_details['taxPercent'];
      $mrp = $item_details['mrp'] + 0;
      $upp = $item_details['upp'] + 0;

      # calculate landing cost
      // $amount_gross = round($closing_qty*$closing_rate,2);
      // $tax_amount = 0;
/*      if($amount_gross>0 && $tax_percent>0) {
        $tax_amount = round( ($amount_gross*$tax_percent)/100, 2);
      } else {
        $tax_amount = 0;
      }*/
      // $landing_cost = $amount_gross + $tax_amount;
      // $purchase_rate = round($landing_cost/$closing_qty, 2);
      $amount = round($closing_qty * $closing_rate, 2);

      // dump($landing_cost, $purchase_rate, $amount);
      // exit;

      $tot_amount += $amount;
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
    }

    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell($totals_width,6,'T O T A L S','LRTB',0,'R');
    $pdf->Cell($item_widths[4],6,number_format($tot_qty,2,'.',''),'LRTB',0,'R');
    $pdf->SetFont('Arial','B',11);    
    $pdf->Cell($item_widths[5]+$item_widths[5],6,number_format($tot_amount,2,'.',''),'LRTB',0,'R');    

    $pdf->Output(); 
  }  

  public function grnRegister(Request $request) {

        $from_date = $request->get('fromDate');
        $to_date = $request->get('toDate');

        $item_widths = array(12,20,25,100,50,50,20);
        $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3]
                        +$item_widths[4]+$item_widths[5];
        $slno=0;       

        # inititate GRN Model
        $grn_api = new \ClothingRm\Grn\Model\Grn;

        $search_params = array(
            'fromDate' => $from_date,
            'toDate' => $to_date
        );

        $grn_response = $grn_api->get_grns(1,100,$search_params);
        if($grn_response['status']===false) {
            die("<h1>No data is available. Change Report Filters and Try again</h1>");
        } else {
            $grns = $grn_response['grns'];
            $heading1 = 'GRN Register';
            $heading2 = '( from '.$from_date.' to '.$to_date.' )';
        }

        // dump($grns);
        // exit;

        # start PDF printing.
        $pdf = PDF::getInstance();
        $pdf->AliasNbPages();
        $pdf->AddPage('L','A4');

        # Print Bill Information.
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,0,$heading1,'',1,'C');
        $pdf->SetFont('Arial','B',10);
        $pdf->Ln(5);
        $pdf->Cell(0,0,$heading2,'',1,'C');

        $pdf->SetFont('Arial','B',9);
        $pdf->Ln(5);
        $pdf->Cell($item_widths[0],6,'Sl.No.','LRTB',0,'C');
        $pdf->Cell($item_widths[1],6,'GRN No.','RTB',0,'C');
        $pdf->Cell($item_widths[2],6,'GRN Date','RTB',0,'C');
        $pdf->Cell($item_widths[3],6,'Supplier','RTB',0,'C');        
        $pdf->Cell($item_widths[4],6,'PONo. & Date','RTB',0,'C');
        $pdf->Cell($item_widths[5],6,'Bill No.','RTB',0,'C');
        $pdf->Cell($item_widths[6],6,'Bill Amount','RTB',0,'C');
        $pdf->SetFont('Arial','',10);

        $tot_net_pay = 0;
        foreach($grns as $grn_details) {
            $slno++;
            $tot_net_pay += $grn_details['netPay'];

            $po_info = $grn_details['poNo'].' / '.date("d-M-Y", strtotime($grn_details['purchaseDate']));
            // $po_info = $grn_details['poNo'];
            $grn_no = $grn_details['grnNo'];
            $grn_date = date("d-M-Y",strtotime($grn_details['grnDate']));
            $supplier_name = $grn_details['supplierName'];
            $bill_no = $grn_details['billNo'];
            
            $pdf->Ln();
            $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
            $pdf->Cell($item_widths[1],6,$grn_no,'RTB',0,'L');
            $pdf->Cell($item_widths[2],6,$grn_date,'RTB',0,'L');
            $pdf->Cell($item_widths[3],6,$supplier_name,'RTB',0,'L');            
            $pdf->Cell($item_widths[4],6,$po_info,'RTB',0,'L');
            $pdf->Cell($item_widths[5],6,$bill_no,'RTB',0,'R');
            $pdf->Cell($item_widths[6],6,number_format($grn_details['netPay'],2),'RTB',0,'R');
        }

        $pdf->Ln();
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell($totals_width,6,'Totals','LRTB',0,'R');
        $pdf->Cell($item_widths[6],6,number_format($tot_net_pay,2),'LRTB',0,'R');

        $pdf->Output();             
  }

  // Print Expiry Report
  public function expiryReport(Request $request) {

        $month = $request->get('month');
        $year = $request->get('year');
        if(!Utilities::validateMonth($month)) {
            die("<h1>Invalid month</h1>");            
        }
        if(!Utilities::validateYear($year)) {
            die("<h1>Invalid year</h1>");            
        }        

        $item_widths = array(10,70,7,25,12,14,15,17,20);
        $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3]
                        +$item_widths[4]+$item_widths[5]+$item_widths[6];
        $totals_width1 = $item_widths[7]+$item_widths[8];
        $slno = $tot_amount = 0;       

        # inititate GRN Model
        $inven_api = new \ClothingRm\Inventory\Model\Inventory;
        $total_items = array();

        $params = array(
            'month' => $month,
            'year' => $year
        );

        $inven_api_response = $inven_api->get_expiry_report($params);
        if($inven_api_response['status']===false) {
            die("<h1>No data is available. Change Report Filters and Try again</h1>");
        } else {
            $total_items = $inven_api_response['items'];
            $total_pages = $inven_api_response['total_pages'];
            if($total_pages>1) {
                for($i=2;$i<=$total_pages;$i++) {
                    $inven_api_response = $inven_api->get_expiry_report($params,$i);
                    if($inven_api_response['status'] === true) {
                        $total_items = array_merge($total_items,$inven_api_response['items']);
                    }
                }
            }
            $heading1 = 'Medicine Expiry Report';
            $heading2 = 'As on '.Utilities::get_calender_month_names($month).', '.$year;
        }
        // exit;

        # start PDF printing.
        $pdf = PDF::getInstance();
        $pdf->AliasNbPages();
        $pdf->AddPage('P','A4');
        $pdf->setTitle($heading1.' - '.$month.'_'.$year);

        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,0,$heading1,'',1,'C');

        $pdf->SetFont('Arial','B',10);
        $pdf->Ln(5);
        $pdf->Cell(0,0,$heading2,'',1,'C');

        $pdf->SetFont('Arial','B',9);
        $pdf->Ln(5);
        $pdf->Cell($item_widths[0],6,'SNo.','LRTB',0,'C');
        $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'C');
        $pdf->Cell($item_widths[2],6,'U/P','RTB',0,'C');
        $pdf->Cell($item_widths[3],6,'Batch No.','RTB',0,'C');
        $pdf->Cell($item_widths[4],6,'Tax(%)','RTB',0,'C');        
        $pdf->Cell($item_widths[5],6,'ExpDate','RTB',0,'C');
        $pdf->Cell($item_widths[6],6,'ClosQty.','RTB',0,'C');        
        $pdf->Cell($item_widths[7],6,'Rate/Unit','RTB',0,'C');
        $pdf->Cell($item_widths[8],6,'Amount','RTB',0,'C');
        
        $pdf->SetFont('Arial','',9);

        foreach($total_items as $item_details) {
            $slno++;

            $item_name = $item_details['itemName'];
            $upp = $item_details['upp'];
            $batch_no = $item_details['batchNo'];

            $opqty = $item_details['opQty'];
            $oprate = $item_details['opPurRate'];
            $oprateiv = $item_details['opPurIvat'];
            $opvat = $item_details['opVatPercent'];
            $opexpdate = $item_details['opExpDate'];

            $pqty = $item_details['purchaseQty'];
            $prate = $item_details['puPurRate'];
            $pvat = $item_details['puVatPercent'];
            $pexpdate = $item_details['puExpDate'];

            $sqty = $item_details['soldQty'];
            $srqty = $item_details['salesReturnQty'];

            // if($batch_no=='16AB14') {
            //     dump($item_details);
            //     echo $opqty, '---', $pqty, '---', $sqty;
            //     echo 'closing qty is...'.$closqty;
            //     exit;
            // }            

            if((int)$pqty===0) {
                if((int)$oprateiv===1) {
                    $clos_rate = $oprate;
                } else {
                    $pamount = $oprate*$opqty;                
                    $taxamount = $pamount*$opvat/100;
                    $pamount += $taxamount;
                    $clos_rate = round($pamount/$opqty,2);                            
                }
                $tax_percent = $opvat;
                $exp_date = $opexpdate;
            } else {
                $pamount = $prate*$pqty;
                $taxamount = ($pamount*$pvat)/100;
                $pamount += $taxamount;
                
                $clos_rate = round($pamount/($upp*$pqty),2);
                $tax_percent = $pvat;
                $exp_date = $pexpdate;
            }

            $closqty = (($opqty+$pqty)-$sqty)+$srqty;
            $amount = round($closqty*$clos_rate,2);
            $tot_amount += $amount;
            
            $pdf->Ln();
            $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
            $pdf->Cell($item_widths[1],6,$item_name,'RTB',0,'L');
            $pdf->Cell($item_widths[2],6,$upp,'RTB',0,'R');
            $pdf->Cell($item_widths[3],6,$batch_no,'RTB',0,'L');            
            $pdf->Cell($item_widths[4],6,$tax_percent,'RTB',0,'R');
            
            $pdf->SetFont('Arial','B',9);
            $pdf->Cell($item_widths[5],6,$exp_date,'RTB',0,'R');
            $pdf->SetFont('Arial','',9);

            $pdf->Cell($item_widths[6],6,$closqty,'RTB',0,'R');
            $pdf->Cell($item_widths[7],6,number_format($clos_rate,2),'RTB',0,'R');
            $pdf->Cell($item_widths[8],6,number_format($amount,2),'RTB',0,'R');        
        }

        $pdf->Ln();
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell($totals_width,6,'TOTAL EXPIRY VALUE','LRTB',0,'R');
        $pdf->Cell($totals_width1,6,'Rs.'.number_format($tot_amount,2),'LRTB',0,'C');        

        $pdf->Output(); 
  }  

  public function adjEntries(Request $request) {
    $fromDate = $request->get('fromDate');
    $toDate = $request->get('toDate');
    $adjType = $request->get('optionType');

    $item_widths = array(10,80,10,25,20,20,25);
    $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3]+
                    $item_widths[4]+$item_widths[5];
    $slno=0;
    $summary = array();

    # inititate Inventory Model
    $inven_api = new \ClothingRm\Inventory\Model\Inventory;

    $search_params = array(
      'fromDate' => $fromDate,
      'toDate' => $toDate,
      'adjType' => $adjType,
      'perPage' => 100,
    );

    $inven_response = $inven_api->get_stock_adj_report($search_params);
    if($inven_response['status']===false) {
        die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
        $total_items = $inven_response['results']['results'];
        $total_pages = $inven_response['results']['total_pages'];
        if($total_pages>1) {
            for($i=2;$i<=$total_pages;$i++) {
                $search_params['pageNo'] = $i;
                $inven_response = $inven_api->get_stock_adj_report($search_params);
                // dump($inven_response);
                if($inven_response['status'] === true) {
                    $total_items = array_merge($total_items,$inven_response['results']['results']);
                }
            }
        }
        $heading1 = 'Stock Adjustment Report';
        $heading2 = 'From '.date('d-M-Y', strtotime($fromDate)).' To '.date('d-M-Y', strtotime($toDate));
    }

    // dump($total_items);
    // exit;

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');
    $pdf->setTitle($heading1.' - '.date('jS F, Y', time()));

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');

    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');

    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'SNo.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'U/P','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Batch No.','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Adj. Qty.','RTB',0,'C');        
    $pdf->Cell($item_widths[5],6,'Rate/Unit','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'Amount (Rs.)','RTB',0,'C');
    
    $pdf->SetFont('Arial','',9);
    $tot_amount = 0;
    foreach($total_items as $item_details) {
        $slno++;

        $item_name = $item_details['itemName'];
        $upp = $item_details['upp'];
        $batch_no = $item_details['batchNo'];

        $oprate = $item_details['opPurRate'];
        $oprateiv = $item_details['opPurIvat'];
        $opvat = $item_details['opVatPercent'];

        $prate = $item_details['puPurRate'];
        $pvat = $item_details['puVatPercent'];

        $adjqty = $item_details['adjQty'];

        if($prate==0) {
          if((int)$oprateiv===1) {
            $clos_rate = $oprate;
          } else {
            $pamount = $oprate*$adjqty;                
            $taxamount = $pamount*$opvat/100;
            $pamount += $taxamount;
            $clos_rate = round($pamount/$adjqty,2);                            
          }
        } else {
            $pamount = $prate*$adjqty;
            $taxamount = ($pamount*$pvat)/100;
            $pamount += $taxamount;
            
            $clos_rate = round($pamount/($upp*$adjqty),2);
            $tax_percent = $pvat;
        }

        $amount = round($adjqty*$clos_rate,2);
        $tot_amount += $amount;
        
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$item_name,'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$upp,'RTB',0,'R');
        $pdf->Cell($item_widths[3],6,$batch_no,'RTB',0,'L');            
        $pdf->Cell($item_widths[4],6,$adjqty,'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,number_format($clos_rate,2),'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,number_format($amount,2),'RTB',0,'R');        
    }

    $pdf->Ln();
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell($totals_width,6,'TOTAL ADJUSTMENT VALUE','LRTB',0,'R');
    $pdf->Cell($item_widths[6],6,number_format($tot_amount,2),'LRTB',0,'R');        

    $pdf->Output();
  }

  public function materialMovement(Request $request) {

    $fromDate = $request->get('fromDate');
    $toDate = $request->get('toDate');
    $movType = $request->get('optionType');
    $count = $request->get('count');

    # inititate Inventory Model
    $inven_api = new \Inventory\Model\Inventory;
    $search_params = array(
      'fromDate' => $fromDate,
      'toDate' => $toDate,
      'movType' => $movType,
      'perPage' => 100,
      'count' => ($movType==='fast'?$count:1),
    );

    $total_items = array();

    $inven_response = $inven_api->get_material_movement($search_params);
    // dump($inven_response);
    if($inven_response['status']===false) {
        die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
        $total_items = $inven_response['results']['results'];
        $total_pages = $inven_response['results']['total_pages'];
        if($total_pages>1) {
            for($i=2;$i<=$total_pages;$i++) {
                $search_params['pageNo'] = $i;
                $inven_response = $inven_api->get_material_movement($search_params);
                // dump($inven_response);
                if($inven_response['status'] === true) {
                    $total_items = array_merge($total_items,$inven_response['results']['results']);
                }
            }
        }
        $heading1 = ucwords($movType).' Material Movement Register';
        $heading2 = 'Between '.date('d-M-Y', strtotime($fromDate)).' To '.date('d-M-Y', strtotime($toDate));
    }

    // dump($total_items);
    // exit;

    $item_widths = array(10,70,25,25,15,24,23);
    $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3];
    $totals_width1 = $item_widths[6];    

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');
    $pdf->setTitle('MaterialMovement'.' - '.date('jS F, Y'));

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');

    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');

    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'SNo.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Category','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'MfgName','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'SoldQty.','RTB',0,'C');        
    $pdf->Cell($item_widths[5],6,'Rate/unit (Rs.)','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'Amount (Rs.)','RTB',1,'C');
    
    $pdf->SetFont('Arial','',9);
    $slno=$tot_amount=$tot_qty=0;
    foreach($total_items as $item_details) {
        $slno++;
        $amount = $item_details['soldQty']*$item_details['itemRate'];
        $tot_amount += $amount;
        $tot_qty += $item_details['soldQty'];
        
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$item_details['itemName'],'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$item_details['categoryName'],'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,$item_details['mfgName'],'RTB',0,'L');
        $pdf->Cell($item_widths[4],6,number_format($item_details['soldQty'],2),'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,number_format($item_details['itemRate'],2),'RTB',0,'R');  
        $pdf->Cell($item_widths[6],6,number_format($amount,2),'RTB',1,'R');
    }

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell($totals_width,6,'Totals','LRTB',0,'R');
    $pdf->Cell($item_widths[4],6,$tot_qty,'RTB',0,'R');
    $pdf->Cell($item_widths[5],6,'','RTB',0,'R');    
    $pdf->Cell($item_widths[6],6,number_format($tot_amount,2),'RTB',0,'R');  

    $pdf->Output();

  }

  public function printItemthrLevel(Request $request) {

    # inititate Inventory Model
    $inven_api = new \ClothingRm\Inventory\Model\Inventory;
    $search_params = array(
      'perPage' => 100,
    );

    $total_items = array();

    $inven_response = $inven_api->get_item_thrlevel($search_params);
    // dump($inven_response);
    // exit;
    if($inven_response['status']===false) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $total_items = $inven_response['response']['results'];
      $total_pages = $inven_response['response']['total_pages'];
      if($total_pages>1) {
        for($i=2;$i<=$total_pages;$i++) {
          $search_params['pageNo'] = $i;
          $inven_response = $inven_api->get_item_thrlevel($search_params);
          if($inven_response['status'] === true) {
            $total_items = array_merge($total_items,$inven_response['response']['results']);
          }
        }
      }
      $heading1 = 'Items reached threshold qty.';
      $heading2 = 'as on '.date('d-M-Y h:ia');
    }

    // dump($total_items);
    // exit;
    
    $item_widths = array(10,70,20,16,15,60,23);
    $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3];

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');
    $pdf->setTitle('itemthrqty'.' - '.date('jS F, Y'));

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');

    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');

    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'SNo.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'ItemName','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Units/Pack','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'ThrQty.','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'AvaQty.','RTB',0,'C');        
    $pdf->Cell($item_widths[5],6,'SupplierName','RTB',1,'C');
    
    $pdf->SetFont('Arial','',9);
    $slno=0;
    $supplier_name = '';
    foreach($total_items as $item_details) {
        $slno++;
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$item_details['itemName'],'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$item_details['unitsPerPack'],'RTB',0,'R');
        $pdf->Cell($item_widths[3],6,number_format($item_details['thrQty'],0),'RTB',0,'R');
        $pdf->SetFont('Arial','B',10);        
        $pdf->Cell($item_widths[4],6,number_format($item_details['avaQty'],0),'RTB',0,'R');
        $pdf->SetFont('Arial','',9);        
        $pdf->Cell($item_widths[5],6,$supplier_name,'RTB',1,'L');
    } 
    $pdf->Output();
  }

  /** prints item master with latest landing cost **/
  public function itemMaster() {

    # inititate Inventory Model
    $inven_api = new \ClothingRm\Inventory\Model\Inventory;    
    
    $search_params = array(
      'perPage' => 300,
    );

    $total_items = array();
    $inven_response = $inven_api->item_master_with_pp($search_params);
    if($inven_response['status']===false) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $total_items = $inven_response['response']['items'];
      $total_pages = $inven_response['response']['total_pages'];
      if($total_pages>1) {
        for($i=2;$i<=$total_pages;$i++) {
          $search_params['pageNo'] = $i;
          $inven_response = $inven_api->item_master_with_pp($search_params);
          if($inven_response['status'] === true) {
            $total_items = array_merge($total_items,$inven_response['response']['items']);
          }
        }
      }
      $heading1 = 'Inventory Master';
      $heading1_sub = 'as on '.date("dS F, Y");
    }
 
    $item_widths = array(10,35,95,17,17,17);
    $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3];

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');
    $pdf->setTitle('itemmaster'.' - '.date('dS F, Y'));

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');

    $pdf->SetFont('Arial','B',13);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading1_sub,'',1,'C');

    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Item Code','LRTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Item Name','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Category','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Units/Pack','RTB',0,'C');
    $pdf->Cell($item_widths[5],6,'Item Rate','RTB',0,'C');
    $pdf->SetFont('Arial','',9);
    $pdf->Ln();

    $slno=0;
    foreach($total_items as $item_details) {
      $slno++;
      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,$item_details['itemCode'],'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,$item_details['itemName'],'RTB',0,'L');
      $pdf->Cell($item_widths[3],6,$item_details['categoryCode'],'RTB',0,'L');
      $pdf->Cell($item_widths[4],6,$item_details['upp'],'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,number_format($item_details['itemRate'],2),'RTB',1,'R');
    } 
    $pdf->Output();
  }

  /** Inventory Profitability report **/
  public function inventoryProfitability(Request $request) {

    $fromDate = $request->get('fromDate');
    $toDate = $request->get('toDate');
    $mode = $request->get('optionType');

    # inititate Inventory Model
    $inven_api = new \ClothingRm\Inventory\Model\Inventory;
    $search_params = array(
      'pageNo' => 1,
      'perPage' => 300,
      'fromDate' => $fromDate,
      'toDate' => $toDate,
      'mode' => $mode,
    );

    $total_items = array();
    $inven_response = $inven_api->inventory_profitability($search_params);
    // dump($inven_response);
    // exit;
    if($inven_response['status']===false) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $total_items = $inven_response['results']['items'];
      $total_pages = $inven_response['results']['total_pages'];
      if($total_pages>1) {
        for($i=2;$i<=$total_pages;$i++) {
          $search_params['pageNo'] = $i;
          $inven_response = $inven_api->inventory_profitability($search_params);
          if($inven_response['status'] === true) {
            $total_items = array_merge($total_items,$inven_response['results']['items']);
          }
        }
      }
      $heading1 = $mode !== ''?
                  'Inventory Profitability - '.Utilities::get_sale_mode_name($mode).' Sale':
                  'Inventory Profitability - All sale modes';
      $heading1_sub = '( from '.$fromDate.' to '.$toDate.' )';
    }

    $item_widths = array(10,61,15,17,17,17,17,19,17);
    $totals_width = $item_widths[0]+$item_widths[1];

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');
    $pdf->setTitle('inventoryprofitability'.' - '.date('dS F, Y'));

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');

    $pdf->SetFont('Arial','B',13);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading1_sub,'',1,'C');

    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Item Name','LRTB',0,'C');
    $pdf->Cell($item_widths[2],6,'QtySold','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'SalePrice','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'SaleValue','RTB',0,'C');
    $pdf->Cell($item_widths[5],6,'Pur.Price','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'Pur.Value','RTB',0,'C');
    $pdf->Cell($item_widths[7],6,'GrossProfit','RTB',0,'C');
    $pdf->Cell($item_widths[8],6,'Proft (%)','RTB',0,'C');    
    $pdf->SetFont('Arial','',9);

    $slno = 0;
    $tot_sold_qty = $tot_sold_value = $tot_pur_value = $tot_gross_profit = 0; 
    foreach($total_items as $item_details) {
      $slno++;

      $gross_profit = $item_details['soldValue']-$item_details['purchaseValue'];
      $gross_profit_percent = 100-round(($item_details['purchaseValue']/$item_details['soldValue'])*100,2);
      $tot_sold_qty += $item_details['soldQty'];
      $tot_sold_value += $item_details['soldValue'];
      $tot_pur_value += $item_details['purchaseValue'];

      $pdf->Ln();
      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,substr($item_details['itemName'],0,32),'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,$item_details['soldQty'],'RTB',0,'R');
      $pdf->Cell($item_widths[3],6,$item_details['sellingPrice'],'RTB',0,'R');
      $pdf->Cell($item_widths[4],6,$item_details['soldValue'],'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,$item_details['finalPurchaseRate'],'RTB',0,'R');
      $pdf->Cell($item_widths[6],6,$item_details['purchaseValue'],'RTB',0,'R');
      $pdf->Cell($item_widths[7],6,number_format($gross_profit,2),'RTB',0,'R');
      $pdf->Cell($item_widths[8],6,number_format($gross_profit_percent,2),'RTB',0,'R');
    }
    
    $pdf->Ln(12);
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,'Profitability Summary','',1,'C');
    $pdf->Ln(4);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(100,6,'Description','LRTB',0,'C');
    $pdf->Cell(40,6,'Value','RTB',1,'C');
    $pdf->SetFont('Arial','',14);

    $tot_gross_profit = 100-( round( round($tot_pur_value/$tot_sold_value,2) * 100, 2) );

    $pdf->Cell(100,6,'Total Sale Value (in Rs.)','LRTB',0,'R');
    $pdf->Cell(40,6,number_format($tot_sold_value,2),'RTB',1,'R');

    $pdf->Cell(100,6,'Total Purchase Value (in Rs.)','LRTB',0,'R');
    $pdf->Cell(40,6,number_format($tot_pur_value,2),'RTB',1,'R');

    $pdf->Cell(100,6,'Gross Profit (in Rs.)','LRTB',0,'R');
    $pdf->Cell(40,6,number_format($tot_sold_value-$tot_pur_value,2),'RTB',1,'R');

    $pdf->Cell(100,6,'Gross Profit (in %)','LRTB',0,'R');
    $pdf->Cell(40,6,number_format($tot_gross_profit,2).'%','RTB',1,'R');     

    $pdf->Output();

  } # end of Inventory Profitability

} # end of class