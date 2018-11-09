<?php 

namespace ClothingRm\Reports\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\PDF;

class ReportsSalesControllerTax {
  
  public function salesAbsMonthTaxRate(Request $request) {
    $month = $request->get('month');
    $year = $request->get('year');
    $location_code = $request->get('locationCode');

    $item_widths = array(22,20,33,33,15,27,15,27,15,27,15,27);
    $totals_width = $item_widths[0]+$item_widths[1];
    $slno=0;
    $gst_summary = [];

    $grand_tot_qty = $grand_billable = $grand_taxable = $grand_igst_value = 0;
    $grand_cgst_value = $grand_sgst_value = 0;    

    # inititate Sales Model
    $sales_api = new \ClothingRm\Sales\Model\Sales;

    $search_params = array(
      'month' => $month,
      'year' => $year,
      'locationCode' => $location_code,
    );

    $sales_response = $sales_api->get_sales_summary_bymon_tax_report($search_params);
    if(!$sales_response['status']) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $sales_summary = $sales_response['summary'];
      $month_name = date('F', mktime(0, 0, 0, $month, 10));
      $heading1 = 'Daywise Sales Summary - By Tax Rate';
      $heading2 = 
      $heading3 = 'for the month of '.$month_name.', '.$year;
    }

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('L','A4');

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');
    $pdf->SetFont('Arial','B',11);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');

    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'Date','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Units Sold','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Total Amount','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Taxable','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'IGST%','RTB',0,'C');  
    $pdf->Cell($item_widths[5],6,'IGST Value','RTB',0,'C'); 
    $pdf->Cell($item_widths[6],6,'CGST%','RTB',0,'C');  
    $pdf->Cell($item_widths[7],6,'CGST Value','RTB',0,'C'); 
    $pdf->Cell($item_widths[8],6,'SGST%','RTB',0,'C');  
    $pdf->Cell($item_widths[9],6,'SGST Value','RTB',0,'C');
    $pdf->Cell($item_widths[10],6,'GST%','RTB',0,'C');  
    $pdf->Cell($item_widths[11],6,'GST Value','RTB',0,'C');
    $pdf->SetFont('Arial','',10);

    // dump($sales_summary);
    // exit;

    foreach($sales_summary as $day_details) {
      $date = date("d-m-Y", strtotime($day_details['tranDate']));
      if($day_details['fivePercentItemQty'] > 0) {
        $gst_summary[5] = [
          'qty' => $day_details['fivePercentItemQty'],
          'billable' => $day_details['fivePercentBillable'],
          'taxable' => $day_details['fivePercentTaxable'],
          'igst' => $day_details['fivePercentIgstAmt'],
          'cgst' => $day_details['fivePercentCgstAmt'],
          'sgst' => $day_details['fivePercentSgstAmt'],
        ];
        $grand_tot_qty += $day_details['fivePercentItemQty'];
        $grand_billable += $day_details['fivePercentBillable'];
        $grand_taxable += $day_details['fivePercentTaxable'];
        $grand_igst_value += $day_details['fivePercentIgstAmt'];
        $grand_cgst_value += $day_details['fivePercentCgstAmt'];
        $grand_sgst_value += $day_details['fivePercentSgstAmt'];
      }
      if($day_details['twelvePercentItemQty'] > 0) {
        $gst_summary[12] = [
          'qty' => $day_details['twelvePercentItemQty'],
          'billable' => $day_details['twelvePercentBillable'],
          'taxable' => $day_details['twelvePercentTaxable'],
          'igst' => $day_details['twelvePercentIgstAmt'],
          'cgst' => $day_details['twelvePercentCgstAmt'],
          'sgst' => $day_details['twelvePercentSgstAmt'],
        ];
        $grand_tot_qty += $day_details['twelvePercentItemQty'];
        $grand_billable += $day_details['twelvePercentBillable'];
        $grand_taxable += $day_details['twelvePercentTaxable'];
        $grand_igst_value += $day_details['twelvePercentIgstAmt'];
        $grand_cgst_value += $day_details['fivePercentCgstAmt'];        
        $grand_sgst_value += $day_details['twelvePercentSgstAmt'];
      }
      if($day_details['eighteenPercentItemQty'] > 0) {
        $gst_summary[18] = [
          'qty' => $day_details['eighteenPercentItemQty'],
          'billable' => $day_details['eighteenPercentBillable'],
          'taxable' => $day_details['eighteenPercentTaxable'],
          'igst' => $day_details['eighteenPercentIgstAmt'],
          'cgst' => $day_details['eighteenPercentCgstAmt'],
          'sgst' => $day_details['eighteenPercentSgstAmt'],
        ];
        $grand_tot_qty += $day_details['eighteenPercentItemQty'];
        $grand_billable += $day_details['eighteenPercentBillable'];
        $grand_taxable += $day_details['eighteenPercentTaxable'];
        $grand_igst_value += $day_details['eighteenPercentIgstAmt'];
        $grand_sgst_value += $day_details['eighteenPercentSgstAmt'];
        $grand_cgst_value += $day_details['eighteenPercentCgstAmt'];        
      }
      if($day_details['twentyEightPercentItemQty'] > 0) {
        $gst_summary[28] = [
          'qty' => $day_details['twelvePercentItemQty'],
          'billable' => $day_details['twentyEightPercentBillable'],
          'taxable' => $day_details['twentyEightPercentTaxable'],
          'igst' => $day_details['twentyEightPercentIgstAmt'],
          'cgst' => $day_details['twentyEightPercentCgstAmt'],
          'sgst' => $day_details['twentyEightPercentSgstAmt'],
        ];
        $grand_tot_qty += $day_details['twelvePercentItemQty'];
        $grand_billable += $day_details['twentyEightPercentBillable'];
        $grand_taxable += $day_details['twentyEightPercentTaxable'];
        $grand_igst_value += $day_details['twentyEightPercentIgstAmt'];
        $grand_cgst_value += $day_details['twentyEightPercentCgstAmt'];        
        $grand_sgst_value += $day_details['twentyEightPercentSgstAmt'];
      }

      foreach($gst_summary as $key => $gst_summary_details) {
        $split_percent = $key / 2;

        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$date,'LRTB',0,'L');
        $pdf->Cell($item_widths[1],6,number_format($gst_summary_details['qty'],2),'RTB',0,'R');
        $pdf->Cell($item_widths[2],6,number_format($gst_summary_details['billable'],2),'RTB',0,'R');
        $pdf->Cell($item_widths[3],6,number_format($gst_summary_details['taxable'],2),'RTB',0,'R');
        $pdf->Cell($item_widths[4],6,0,'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,0.00,'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,number_format($split_percent, 2),'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,number_format($gst_summary_details['cgst'], 2),'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,number_format($split_percent, 2),'RTB',0,'R');
        $pdf->Cell($item_widths[9],6,number_format($gst_summary_details['sgst'], 2),'RTB',0,'R');
        $pdf->Cell($item_widths[10],6,number_format($key,2),'RTB',0,'R');
        $pdf->Cell($item_widths[11],6,number_format($gst_summary_details['cgst'] + $gst_summary_details['sgst'], 2),'RTB',0,'R');
      }
    }

    $pdf->Ln();
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell($item_widths[0],6,'','LRTB',0,'L');
    $pdf->Cell($item_widths[1],6,number_format($grand_tot_qty,2),'RTB',0,'R');
    $pdf->Cell($item_widths[2],6,number_format($grand_billable,2),'RTB',0,'R');
    $pdf->Cell($item_widths[3],6,number_format($grand_taxable,2),'RTB',0,'R');
    $pdf->Cell($item_widths[4],6,'','RTB',0,'R');
    $pdf->Cell($item_widths[5],6,'','RTB',0,'R');
    $pdf->Cell($item_widths[6],6,'','RTB',0,'R');
    $pdf->Cell($item_widths[7],6,number_format($grand_cgst_value, 2),'RTB',0,'R');
    $pdf->Cell($item_widths[8],6,'','RTB',0,'R');
    $pdf->Cell($item_widths[9],6,number_format($grand_sgst_value, 2),'RTB',0,'R');
    $pdf->Cell($item_widths[10],6,'','RTB',0,'R');
    $pdf->Cell($item_widths[11],6,number_format($grand_cgst_value + $grand_sgst_value, 2),'RTB',0,'R');
    $pdf->SetFont('Arial','B',9);
    
    $pdf->Output();
  }
}