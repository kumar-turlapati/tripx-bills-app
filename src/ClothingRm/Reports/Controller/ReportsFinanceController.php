<?php 

namespace ClothingRm\Reports\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\PDF;

class ReportsFinanceController
{

  public function supplierPaymentsDue(Request $request) {
    $month = $request->get('month');
    $year = $request->get('year');

    $item_widths = array(10,77,36,23,23,23,20);
    $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2]
                    +$item_widths[3];
    $slno=0;       

    # inititate Supplier Model
    $supp_api = new \ClothingRm\Suppliers\Model\Supplier;

    $search_params = array(
      'month' => $month,
      'year' => $year
    );

    $supp_response = $supp_api->get_supplier_payments_due($search_params);
    if($supp_response['status']===false) {
        die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
        // dump($supp_response);
        // exit;
        $month_name = date('F', mktime(0, 0, 0, $month, 10));
        $heading1 = "Supplier's Payment Due Report";
        $heading2 = 'for the month of '.$month_name.', '.$year;
    }

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');

    # Print Bill Information.
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');
    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');

    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'Sl.No.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Supplier Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'GRN No. / Date','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Bill No.','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Bill Amount','RTB',0,'C');
    $pdf->Cell($item_widths[5],6,'Due Date','RTB',0,'C');        
    $pdf->SetFont('Arial','',9);

    $tot_amount = $sl_no = 0;
    foreach($supp_response['suppliers'] as $supp_due_details) {
        $sl_no++;

        $supp_due_details['poDate'] = date("Y-m-d");

        $po_date = date("d-m-Y", strtotime($supp_due_details['poDate']));
        $po_no = $supp_due_details['poNo'];
        $grn_date = date("d-m-Y", strtotime($supp_due_details['grnDate']));
        $grn_no = $supp_due_details['grnNo'];
        $bill_no = $supp_due_details['billNo'];
        $bill_amount = $supp_due_details['billAmount'];
        $supplier_name = $supp_due_details['supplierName'];
        $due_date = date("d-m-Y", strtotime($supp_due_details['paymentDueDate']));        

        $tot_amount += $bill_amount;
                                              
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$sl_no,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,substr($supplier_name,0,40),'RTB',0,'L');
        // $pdf->Cell($item_widths[2],6,$po_no.' / '.$po_date,'RTB',0,'R');
        $pdf->Cell($item_widths[2],6,$grn_no.' / '.$grn_date,'RTB',0,'R');            
        $pdf->Cell($item_widths[3],6,$bill_no,'RTB',0,'R');
        $pdf->Cell($item_widths[4],6,number_format($bill_amount,2),'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,$due_date,'RTB',0,'R');
    }

    $pdf->Ln();
    $pdf->SetFont('Arial','B',11);    
    $pdf->Cell($totals_width,6,'TOTAL DUE','LRTB',0,'R');
    $pdf->Cell($item_widths[4],6,number_format($tot_amount,2),'RTB',0,'R');
    $pdf->Cell($item_widths[5],6,'','RTB',0,'R');

    $pdf->Output();
  }


  public function supplierOutstanding(Request $request) {
    $supp_code = !is_null($request->get('suppCode'))?$request->get('suppCode'):'';

    $item_widths = array(10,80,50,21,20,30,22,22,22);
    $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+
                    $item_widths[3]+$item_widths[4]+$item_widths[5];
    $slno=0;

    # inititate Supplier Model
    $supp_api = new \ClothingRm\Finance\Model\SuppOpbal;

    $search_params = array(
      'supplierCode' => $supp_code,
    );

    $supp_response = $supp_api->get_supp_billwise_outstanding($search_params);
    if($supp_response['status']===false) {
        die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
        // dump($supp_response);
        // exit;
        $current_date = date('dS F, Y - h:ia');
        $heading1 = "Supplier's Outstanding Report";
        $heading2 = 'as on '.$current_date;
    }

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
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Supplier Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'PO No. / Date','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Credit Period','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'GRN No.','RTB',0,'C');
    $pdf->Cell($item_widths[5],6,'Bill No.','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'Bill Amount','RTB',0,'C');
    $pdf->Cell($item_widths[7],6,'Amount Paid','RTB',0,'C');
    $pdf->Cell($item_widths[8],6,'Balance','RTB',0,'C');    
    $pdf->SetFont('Arial','',9);

    $tot_bill_amount = $tot_amount_paid = $tot_balance = $sl_no = 0;
    foreach($supp_response['balances'] as $supp_due_details) {
        $sl_no++;

        $supplier_name = $supp_due_details['supplierName'];
        $po_no = $supp_due_details['poNo'];
        $po_date = date("d-m-Y", strtotime($supp_due_details['purchaseDate']));
        $credit_period = $supp_due_details['creditDays'].' days';
        $grn_no = $supp_due_details['grnNo'];
        $bill_no = $supp_due_details['billNo'];
        $bill_amount = $supp_due_details['billAmount'];
        $amount_paid = $supp_due_details['amountPaid'];
        $balance = $supp_due_details['amountDue'];        

        $tot_bill_amount += $bill_amount;
        $tot_amount_paid += $amount_paid;
        $tot_balance += $balance;
                                              
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$sl_no,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,substr($supplier_name,0,40),'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$po_no.' / '.$po_date,'RTB',0,'R');
        $pdf->Cell($item_widths[3],6,$credit_period,'RTB',0,'R');            
        $pdf->Cell($item_widths[4],6,$grn_no,'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,$bill_no,'RTB',0,'R');        
        $pdf->Cell($item_widths[6],6,number_format($bill_amount,2),'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,number_format($amount_paid,2),'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,number_format($balance,2),'RTB',0,'R');        
    }

    $pdf->Ln();
    $pdf->SetFont('Arial','B',11);    
    $pdf->Cell($totals_width,6,'TOTALS','LRTB',0,'R');
    $pdf->Cell($item_widths[6],6,number_format($tot_bill_amount,2),'RTB',0,'R');
    $pdf->Cell($item_widths[7],6,number_format($tot_amount_paid,2),'RTB',0,'R');
    $pdf->Cell($item_widths[8],6,number_format($tot_balance,2),'RTB',1,'R');    

    $pdf->Output();
  }

  /** Payables - monthwise **/
  public function payablesMonthwise(Request $request) {

    $month = $request->get('month');
    $year = $request->get('year');

    $item_widths = array(8,75,26,26,30,26);
    $totals_width = $item_widths[0]+$item_widths[1];
    $tot_openings = $tot_debits = $tot_credits = $tot_closing = $sl_no = 0;

    # initiate Supplier Model
    $supp_api = new \ClothingRm\Finance\Model\SuppOpbal;

    $search_params = array(
      'month' => $month,
      'year' => $year
    );

    $supp_response = $supp_api->payables_monthwise($search_params);
    if($supp_response['status']===false) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $month_name = date('F', mktime(0, 0, 0, $month, 10));
      $heading1 = "Payables - Monthwise";
      $heading2 = 'for the month of '.$month_name.', '.$year;
    }

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');

    # Print Bill Information.
    $pdf->SetFont('Arial','B',18);
    $pdf->Cell(0,0,$heading1,'',1,'C');
    $pdf->SetFont('Arial','B',12);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');

    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'SNo.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Supplier Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Opening','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Credits (GRN)','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Debits (Payments)','RTB',0,'C');
    $pdf->Cell($item_widths[5],6,'Closing','RTB',0,'C');        
    $pdf->SetFont('Arial','',9);

    foreach($supp_response['data'] as $payables) {
      $sl_no++;
      $supplier_name = $payables['supplierName'];
      $opening = $payables['openingBalance'];
      $credits = $payables['billsReceived'];
      $debits = $payables['amountPaid'];
      $closing = $payables['closingBalance'];

      $tot_openings += $opening;
      $tot_debits += $debits;
      $tot_credits += $credits;
      $tot_closing += $closing;
                                              
      $pdf->Ln();
      $pdf->Cell($item_widths[0],6,$sl_no,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,substr($supplier_name,0,40),'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,number_format($opening,2),'RTB',0,'R');
      $pdf->Cell($item_widths[3],6,number_format($credits,2),'RTB',0,'R');        
      $pdf->Cell($item_widths[4],6,number_format($debits,2),'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,number_format($closing,2),'RTB',0,'R');
    }

    $pdf->Ln();
    $pdf->SetFont('Arial','B',11);    
    $pdf->Cell($totals_width,6,'TOTALS','LRTB',0,'R');
    $pdf->Cell($item_widths[2],6,number_format($tot_openings,2),'RTB',0,'R');
    $pdf->Cell($item_widths[3],6,number_format($tot_credits,2),'RTB',0,'R');    
    $pdf->Cell($item_widths[4],6,number_format($tot_debits,2),'RTB',0,'R');
    $pdf->Cell($item_widths[5],6,number_format($tot_closing,2),'RTB',1,'R');    

    $pdf->Output();
  }

}