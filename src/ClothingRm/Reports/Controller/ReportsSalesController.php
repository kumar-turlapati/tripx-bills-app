<?php 

namespace ClothingRm\Reports\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\PDF;

use ClothingRm\Sales\Model\Sales;

class ReportsSalesController
{

  protected $views_path;

  public function __construct() {
    $this->views_path = __DIR__.'/../Views/';
  }

  public function salesRegister(Request $request) {
    $from_date = $request->get('fromDate');
    $to_date = $request->get('toDate');
    $location_code = !is_null($request->get('locationCode')) ? $request->get('locationCode') : '';

    $item_widths = array(10,25,33,18,18,18,15,18,35);
    $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2];
    $slno=0;

    # inititate Sales Model
    $sales_api = new \ClothingRm\Sales\Model\Sales;
    $search_params = array(
      'fromDate' => $from_date,
      'toDate' => $to_date,
      'locationCode' => $location_code,
    );

    $sales_response = $sales_api->get_sales(1,300,$search_params);
    if($sales_response['status']===false) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $sales_transactions = $sales_response['sales'];
      $locations = Utilities::get_client_locations(false, true);
      if(is_array($locations) && count($locations)>0 && $location_code !== '') {
        $location_name = $locations[$location_code];
      } else {
        $location_name = '';
      }
      $heading1 = 'Daywise Sales Register';
      $heading2 = '( from '.$from_date.' to '.$to_date.' )';
      if($location_name !== '') {
        $heading1 .= ' - '.$location_name;
      }
    }

    // dump($sales_transactions);
    // exit;

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
    $pdf->Cell($item_widths[0],6,'SNo.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Payment Mode','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Bill No. & Date','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'BillAmt','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Discount','RTB',0,'C');
    $pdf->Cell($item_widths[5],6,'TotAmt','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'RndOff','RTB',0,'C');  
    $pdf->Cell($item_widths[7],6,'NetPay','RTB',0,'C');
    $pdf->Cell($item_widths[8],6,'CustomerName','RTB',0,'C');        
    $pdf->SetFont('Arial','',9);

    $tot_bill_amount = $tot_discount = $tot_total_amount = $tot_round_off = $tot_net_pay = 0;
    foreach($sales_transactions as $item_details) {
        $slno++;
        $amount = $item_details['netPay'];

        $payment_method = Constants::$PAYMENT_METHODS_RC_SHORT[$item_details['paymentMethod']];
        $bill_info = $item_details['billNo'].' / '.date("d-m-y", strtotime($item_details['invoiceDate']));
        $tran_info = date("d-M-Y h:ia", strtotime($item_details['createdOn']));
        $customer_name = strtolower($item_details['customerName']);

        $tot_bill_amount += $item_details['billAmount'];
        $tot_discount += $item_details['discountAmount'];
        $tot_total_amount += $item_details['totalAmount'];
        $tot_round_off += $item_details['roundOff'];
        $tot_net_pay += $item_details['netPay'];
        
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$payment_method,'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$bill_info,'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,number_format($item_details['billAmount'],2),'RTB',0,'R');            
        $pdf->Cell($item_widths[4],6,number_format($item_details['discountAmount'],2),'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,number_format($item_details['totalAmount'],2),'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,number_format($item_details['roundOff'],2),'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,number_format($item_details['netPay'],2),'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,substr($customer_name,0,20),'RTB',0,'L');  
    }
    
    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);    
    $pdf->Cell($totals_width,6,'Totals','LRTB',0,'R');
    $pdf->Cell($item_widths[3],6,number_format($tot_bill_amount,2),'LRTB',0,'R');
    $pdf->Cell($item_widths[4],6,number_format($tot_discount,2),'LRTB',0,'R');    
    $pdf->Cell($item_widths[5],6,number_format($tot_total_amount,2),'LRTB',0,'R');
    $pdf->Cell($item_widths[6],6,number_format($tot_round_off,2),'LRTB',0,'R');
    $pdf->Cell($item_widths[7],6,number_format($tot_net_pay,2),'LRTB',0,'R');
    $pdf->Cell($item_widths[8],6,'','LRTB',0,'R');

    $pdf->Output();
  }

  public function printSalesSummaryByMonth(Request $request) {
    $month = $request->get('month');
    $year = $request->get('year');

    $item_widths = array(20,21,21,21,21,21,21,21,23);
    $totals_width = $item_widths[0]+$item_widths[1];
    $slno = 0;       

    # inititate Sales Model
    $sales_api = new \ClothingRm\Sales\Model\Sales;
    $search_params = array(
      'month' => $month,
      'year' => $year
    );

    $sales_response = $sales_api->get_sales_summary_bymon($search_params);
    if($sales_response['status']===false) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $sales_summary = $sales_response['summary']['daywiseSales'];
      $month_name = date('F', mktime(0, 0, 0, $month, 10));
      $heading1 = 'Daywise Sales Summary';
      $heading2 = 'for the month of '.$month_name.', '.$year;
    }

    $discount_label = '**Discount amount is shown for information purpose only. It was already included in Cash/Card/Cnote Sale';

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');
    $pdf->SetFont('Arial','B',11);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');

    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'Date','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'CashSales','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'CardSales','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'SplitSales','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'TotalSales','RT',0,'C');  
    $pdf->Cell($item_widths[5],6,'CashInHand','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'CardAmount','RTB',0,'C');
    $pdf->Cell($item_widths[7],6,'CnotesValue','RTB',0,'C');
    $pdf->Cell($item_widths[8],6,'Discount**','RTB',0,'C');
    $pdf->SetFont('Arial','',9);

    $tot_cash_sales = $tot_split_sales = $tot_card_sales = $tot_discounts = 0;
    $tot_cash_in_hand = $tot_card_swipes = $tot_cnotes_used = 0;
    foreach($sales_summary as $day_details) {
      $date = date("d-m-Y", strtotime($day_details['tranDate']));
      $week = date("l", strtotime($day_details['tranDate']));
      $day_sales = $day_details['cashSales']+$day_details['splitSales']+$day_details['cardSales'];
      $discount_bills = $day_details['totalDiscountBills'];

      $tot_cash_sales += $day_details['cashSales'];
      $tot_card_sales += $day_details['cardSales'];
      $tot_split_sales += $day_details['splitSales'];
      $tot_discounts += $day_details['discountGiven'];
      $tot_cash_in_hand += $day_details['cashInHand'];
      $tot_card_swipes += $day_details['cardSwipes'];
      $tot_split_sales += $day_details['cnoteUsed'];

      $cash_sales = $day_details['cashSales'] > 0 ? number_format($day_details['cashSales'],2,'.','') : '';
      $card_sales = $day_details['cardSales'] > 0 ? number_format($day_details['cardSales'],2,'.','') : '';
      $split_sales = $day_details['splitSales'] > 0 ? number_format($day_details['splitSales'],2,'.','') : '';
      $cash_in_hand = $day_details['cashInHand'] > 0 ? number_format($day_details['cashInHand'],2,'.','') : '' ;
      $card_swipes = $day_details['cardSwipes'] > 0 ? number_format($day_details['cardSwipes'],2,'.','') : '' ;
      $cnotes_used = $day_details['cnoteUsed'] > 0 ? number_format($day_details['cnoteUsed'],2,'.','') : '' ;

      $total_sales = number_format($day_details['cashSales'] + $day_details['cardSales'] + $day_details['splitSales'],2,'.','');

      $discount_string = number_format($day_details['discountGiven'],2).' ('.$discount_bills.')';

      $pdf->Ln();
      $pdf->Cell($item_widths[0],6,$date,'LRTB',0,'L');
      $pdf->SetFont('Arial','',10);
      $pdf->Cell($item_widths[1],6,$cash_sales,'RTB',0,'R');
      $pdf->Cell($item_widths[2],6,$card_sales,'RTB',0,'R');
      $pdf->Cell($item_widths[3],6,$split_sales,'RTB',0,'R');
      $pdf->Cell($item_widths[4],6,$total_sales,'RTB',0,'R');
      $pdf->SetFont('Arial','B',9);
      $pdf->Cell($item_widths[5],6,$cash_in_hand,'RTB',0,'R');
      $pdf->Cell($item_widths[6],6,$card_swipes,'RTB',0,'R');
      $pdf->Cell($item_widths[7],6,$cnotes_used,'RTB',0,'R');
      $pdf->SetFont('Arial','',10);      
      $pdf->Cell($item_widths[8],6,$discount_string,'RTB',0,'R');
    }

    $tot_sales = $tot_cash_sales + $tot_split_sales + $tot_card_sales;

    $pdf->Ln();
    $pdf->SetFont('Arial','B',11);    
    $pdf->Cell($item_widths[0],6,'TOTALS','LRTB',0,'R');
    $pdf->Cell($item_widths[1],6,number_format($tot_cash_sales,2,'.',''),'LRTB',0,'R');
    $pdf->Cell($item_widths[2],6,number_format($tot_card_sales,2,'.',''),'RTB',0,'R');        
    $pdf->Cell($item_widths[3],6,number_format($tot_split_sales,2,'.',''),'RTB',0,'R');        
    $pdf->Cell($item_widths[4],6,number_format($tot_sales,2),'RTB',0,'R');        
    $pdf->Cell($item_widths[5],6,number_format($tot_cash_in_hand,2),'RTB',0,'R');
    $pdf->Cell($item_widths[6],6,number_format($tot_card_swipes,2),'RTB',0,'R');        
    $pdf->Cell($item_widths[7],6,number_format($tot_cnotes_used,2),'RTB',0,'R');
    $pdf->Cell($item_widths[8],6,'*****','RTB',1,'R');    
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(array_sum($item_widths),6,$discount_label,'',0,'R');
    $pdf->Output();
  }

  public function printDaySalesSummary(Request $request) {

        $date = $request->get('date');

        $item_widths = array(10,45,35);
        $totals_width = $item_widths[0]+$item_widths[1];
        $slno=0;       

        # inititate Sales Model
        $sales_api = new \ClothingRm\Sales\Model\Sales;

        $search_params = array(
            'saleDate' => $date,
        );

        $sales_response = $sales_api->get_sales_summary_byday($search_params);
        // dump($sales_response);
        // exit;
        if($sales_response['status']===false) {
            die("<h1>No data is available. Change Report Filters and Try again</h1>");
        } else {
            $sales_summary = $sales_response['summary'];
            $cash_sales = $sales_summary[0]['cashSales'];
            $card_sales = $sales_summary[0]['cardSales'];
            $split_sales = $sales_summary[0]['splitSales'];
            $cash_in_hand = $sales_summary[0]['cashInHand'];
            $day_sales = $cash_sales+$card_sales+$split_sales;

            $heading1 = 'Day Sales Summary';
            $heading2 = date('jS F, Y', strtotime($date));
        }

        # start PDF printing.
        $pdf = PDF::getInstance();
        $pdf->AliasNbPages();
        $pdf->AddPage('P','A4');

        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,0,$heading1,'',1,'C');
        $pdf->SetFont('Arial','B',11);
        $pdf->Ln(5);
        $pdf->Cell(0,0,$heading2,'',1,'C');
        
        $pdf->SetFont('Arial','',13);

        $pdf->Ln(5);
        $pdf->Cell($item_widths[0],6,'a)','LRTB',0,'C');
        $pdf->Cell($item_widths[1],6,'Cash Sale','RTB',0,'L');
        $pdf->Cell($item_widths[2],6,number_format($cash_sales,2),'RTB',0,'R');

        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,'b)','LRTB',0,'C');
        $pdf->Cell($item_widths[1],6,'Card Sale','RTB',0,'L');
        $pdf->Cell($item_widths[2],6,number_format($card_sales,2),'RTB',0,'R');

        $pdf->Ln();                
        $pdf->Cell($item_widths[0],6,'c)','LRTB',0,'C');
        $pdf->Cell($item_widths[1],6,'Split Sale','RTB',0,'L');
        $pdf->Cell($item_widths[2],6,number_format($split_sales,2),'RTB',0,'R');

        $pdf->Ln();
        $pdf->SetFont('Arial','B');          
        $pdf->Cell($item_widths[0],6,'','LRTB',0,'C');
        $pdf->Cell($item_widths[1],6,'(a)+(b)+(c)','RTB',0,'R');
        $pdf->Cell($item_widths[2],6,number_format($day_sales,2),'RTB',0,'R');

        $pdf->Ln();
        $pdf->SetFont('Arial','B');              
        $pdf->Cell($item_widths[0],6,'','LRTB',0,'C');                     
        $pdf->Cell($item_widths[1],6,'Cash in hand','RTB',0,'R');
        $pdf->Cell($item_widths[2],6,number_format($cash_in_hand,2),'RTB',0,'R');        

        $pdf->Output();
  }

  public function patientBillSummary(Request $request) {

    $refNo = $request->get('refNo');
    $regType = $request->get('regType');

    $item_widths = array(10,45,35);
    $totals_width = $item_widths[0]+$item_widths[1];
    $slno=0;
    $summary = array();

    # inititate Sales Model
    $sales_api = new \ClothingRm\Sales\Model\Sales;

    $search_params = array(
        'refNo' => $refNo,
        'regType' => $regType
    );

    $sales_response = $sales_api->get_patient_sales_summary($search_params);
    if($sales_response['status']===false) {
        die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
        $patient_details = $sales_response['summary']['patientDetails'];
        $bills =  $sales_response['summary']['bills'];
    }

    // dump($patient_details);
    // dump($bills);
    // exit;

    if((int)$patient_details['regType']===2) {
        $ip_ref_label = 'I.P.No.';
    } elseif((int)$patient_details['regType']===3) {
        $ip_ref_label = 'O.P.No.';
    } else {
        $ip_ref_label = 'Ref. No.';
    }    

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');

    # Print Bill Information.
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,'IP/OP/GEN Medicine Sales - Billwise','',1,'C');
    
    $header_widths = array(70,30,40,50);
    $bill_header = array(15,45,15,45,30,60);

    $item_widths = array(12,98,35,15,15,15);
    $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3]+$item_widths[4];
    $terms1_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3];
    $terms2_width = $item_widths[4]+$item_widths[5];

    $summary_widths = array(15,40,50,35);
    $patient_name = substr(strtoupper(strtolower($patient_details['patientName'])),0,25);

    # Patient Details
    $pdf->SetFont('Arial','B',11);
    $pdf->Ln(4);
    $pdf->Cell($header_widths[0],6,'Name','LRTB',0,'C');
    $pdf->Cell($header_widths[1],6,$ip_ref_label,'RTB',0,'C');
    $pdf->Cell($header_widths[2],6,'Age & Gender','RTB',0,'C');
    $pdf->Cell($header_widths[3],6,'Referred By','RTB',1,'C');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell($header_widths[0],6,$patient_name,'LRTB',0,'C');
    $pdf->Cell($header_widths[1],6,$patient_details['refNumber'],'RTB',0,'C');
    $pdf->Cell($header_widths[2],6,
        $patient_details['age'].' '.$patient_details['ageCategory'].' - '.
        ($patient_details['gender']=='m'?'Male':'Female'),
        'LRTB',0,'C'
    );
    $pdf->Cell($header_widths[3],6,'','RTB',1,'C');

    foreach($bills as $key=>$bill_details) {
        $bill_no = $bill_details['tranDetails']['billNo'];
        $bill_date = date("d-M-Y h:ia",strtotime($bill_details['tranDetails']['createdTime']));
        $bill_amount = $bill_details['tranDetails']['billAmount'];
        $bill_discount = $bill_details['tranDetails']['discountAmount'];
        $total_amount = $bill_details['tranDetails']['totalAmount'];
        $total_amt_r = $bill_details['tranDetails']['roundOff'];
        $net_pay = $bill_details['tranDetails']['netPay'];        

        $no_of_items = (isset($bill_details['tranItemsCount'])?$bill_details['tranItemsCount']:0);

        if($no_of_items>0) {
            $pdf->Ln();
            $pdf->SetFont('Arial','',10);
            $pdf->Cell($bill_header[0],6,'Bill No.:','',0,'L');
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell($bill_header[1],6,$bill_no,'',0,'L');

            $pdf->SetFont('Arial','',10);  
            $pdf->Cell($bill_header[2],6,'Bill Date:','',0,'L');
            $pdf->SetFont('Arial','B',11);        
            $pdf->Cell($bill_header[3],6,$bill_date,'',0,'L');

            $pdf->SetFont('Arial','',10);         
            $pdf->Cell($bill_header[4],6,'No. of Items:','',0,'L');
            $pdf->SetFont('Arial','B',11);        
            $pdf->Cell($bill_header[5],6,$no_of_items,'',1,'L');

            if(isset($bill_details['tranItems']) && count($bill_details['tranItems'])>0) {
                $pdf->SetFont('Arial','',10);        
                $pdf->Cell($item_widths[0],6,'Sl.No.','LRTB',0,'C');
                $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'C');
                $pdf->Cell($item_widths[2],6,'Batch No.&Expiry','RTB',0,'C');
                $pdf->Cell($item_widths[3],6,'Qty.','RTB',0,'C');
                $pdf->Cell($item_widths[4],6,'Rate','RTB',0,'C');  
                $pdf->Cell($item_widths[5],6,'Amount','RTB',1,'C');

                $pdf->SetFont('Arial','',9);
                $slno=0;
                foreach($bill_details['tranItems'] as $item_details) {
                    $slno++;
                    $amount = $item_details['itemQty']*$item_details['itemRate'];
                    $batch_no_a = explode('_', $item_details['batchNo']);
                    if( count($batch_no_a)>0 && is_array($batch_no_a) && isset($batch_no_a[1]) ) {
                      $batch_no = $batch_no_a[1];
                    } else {
                      $batch_no = $item_details['batchNo'];
                    }
                    
                    $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
                    $pdf->Cell($item_widths[1],6,$item_details['itemName'],'RTB',0,'L');
                    $pdf->Cell($item_widths[2],6,$batch_no.', '.$item_details['expDate'],'RTB',0,'L');
                    $pdf->Cell($item_widths[3],6,number_format($item_details['itemQty'],2),'RTB',0,'R');
                    $pdf->Cell($item_widths[4],6,number_format($item_details['itemRate'],2),'RTB',0,'R');  
                    $pdf->Cell($item_widths[5],6,number_format($amount,2),'RTB',1,'R');
                }

                $pdf->SetFont('Arial','B',9);    
                $pdf->Cell($totals_width,6,'Gross Total','LRB',0,'R');
                $pdf->Cell($item_widths[5],6,$bill_amount,'LRB',1,'R');

                # print only when there is discount.
                if($bill_discount>0) {
                    $pdf->Cell($totals_width,6,'(-) Discount','LRTB',0,'R');
                    $pdf->Cell($item_widths[5],6,$bill_discount,'LRTB',1,'R');
                    $pdf->Cell($totals_width,6,'Total Amount','LRTB',0,'R');
                    $pdf->Cell($item_widths[5],6,$total_amount,'LRTB',1,'R');
                }

                $pdf->Cell($totals_width,6,'(+/-) Round Off','LRTB',0,'R');
                $pdf->Cell($item_widths[5],6,$total_amt_r,'RTB',1,'R');
                $pdf->Cell($totals_width,6,'Net Pay','LRTB',0,'R');
                $pdf->Cell($item_widths[5],6,$net_pay,'RTB',1,'R');

                $summary[] = array(
                    'bill_no' => $bill_no,
                    'bill_date' => $bill_date,
                    'amount' => $net_pay
                );
            }
        }
    }

    // dump($summary);
    // exit;

    # Print all Bills Summary.
    $pdf->Ln(10);
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,'Bills Summary','',1,'C');
    $pdf->Ln(4);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell($summary_widths[0],6,'Sl.No.','LRTB',0,'C');
    $pdf->Cell($summary_widths[1],6,'Bill No.','RTB',0,'C');
    $pdf->Cell($summary_widths[2],6,'Bill Date & Time','RTB',0,'C');
    $pdf->Cell($summary_widths[3],6,'Amount (in Rs.)','RTB',1,'C');
    $slno=$total_amount = 0;
    $pdf->SetFont('Arial','',12);
    foreach($summary as $summary_details) {
        $total_amount += $summary_details['amount'];
        $slno++;
        $pdf->Cell($summary_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($summary_widths[1],6,$summary_details['bill_no'],'RTB',0,'R');
        $pdf->Cell($summary_widths[2],6,$summary_details['bill_date'],'RTB',0,'R');
        $pdf->Cell($summary_widths[3],6,$summary_details['amount'],'RTB',1,'R');        
    }
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell($summary_widths[0]+$summary_widths[1]+$summary_widths[2],6,'Total Amount','LRTB',0,'R');
    $pdf->Cell($summary_widths[3],6,number_format($total_amount,2),'RTB',0,'R');  

    $pdf->Output();
  }

  public function itemWiseSalesReport(Request $request) {
    $date = $request->get('date');
    $locationCode = $request->get('locationCode');
    if(!Utilities::validateDate($date)) {
        die("<h1>Invalid date</h1>");            
    }

    $item_widths = array(10,70,25,25,15,24,23);
    $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3];
    $totals_width1 = $item_widths[6];
    $slno = $tot_amount = 0;

    # inititate Sales Model
    $sales_api = new \ClothingRm\Sales\Model\Sales;
    $total_items = array();

    $params = array(
      'date' => $date,
      'locationCode' => $locationCode,
    );

    $sales_api_response = $sales_api->get_itemwise_sales_report($params);
    // dump($sales_api_response);
    // exit;

    if($sales_api_response['status']===false) {
        die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
        $total_items = $sales_api_response['summary']['results'];
        $total_pages = $sales_api_response['summary']['total_pages'];
        if($total_pages>1) {
            for($i=2;$i<=$total_pages;$i++) {
                $params['pageNo'] = $i;
                $params['perPage'] = 300;
                $sales_api_response = $sales_api->get_stock_report($params);
                if($sales_api_response['status'] === true) {
                    $total_items = array_merge($total_items,$sales_api_response['summary']['results']);
                }
            }
        }
        $locations = Utilities::get_client_locations(false, true);
        if(is_array($locations) && count($locations)>0 && $locationCode !== '') {
            $location_name = ' - '.$locations[$locationCode];
        } else {
            $location_name = ' - All Stores';
        }        
        $heading1 = 'Itemwise Sales Report'.$location_name;
        $heading2 = 'As on '.date('jS F, Y', strtotime($date));
    }  

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');
    $pdf->setTitle('ItemwiseSales'.' - '.date('jS F, Y', strtotime($date)));

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
    $pdf->Cell($item_widths[3],6,'HSN/SAC Code','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'SoldQty.','RTB',0,'C');        
    $pdf->Cell($item_widths[5],6,'Rate/Unit (Rs.)','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'Amount (Rs.)','RTB',1,'C');
    
    $pdf->SetFont('Arial','',9);
    $slno=$tot_amount=$tot_qty=0;
    foreach($total_items as $item_details) {
      $slno++;
      // $amount = $item_details['soldQty']*$item_details['saleRate'];
      $amount = $item_details['saleValue'];
      $tot_amount += $amount;
      $tot_qty += $item_details['soldQty'];
        
      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,$item_details['itemName'],'RTB',0,'L');
      $pdf->Cell($item_widths[3],6,$item_details['categoryName'],'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,$item_details['hsnSacCode'],'RTB',0,'L');
      $pdf->Cell($item_widths[4],6,number_format($item_details['soldQty'],2),'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,number_format($item_details['saleRate'],2),'RTB',0,'R');  
      $pdf->Cell($item_widths[6],6,number_format($amount,2),'RTB',1,'R');
    }

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell($totals_width,6,'Totals','LRTB',0,'R');
    $pdf->Cell($item_widths[4],6,$tot_qty,'RTB',0,'R');
    $pdf->Cell($item_widths[5],6,'','RTB',0,'R');    
    $pdf->Cell($item_widths[6],6,number_format($tot_amount,2),'RTB',0,'R');  

    $pdf->Output();
  }

  public function itemWiseSalesReportByMode(Request $request) {
    $from_date = $request->get('fromDate');
    $to_date = $request->get('toDate');
    $mode = $request->get('optionType');

    if(!Utilities::validateDate($from_date) || !Utilities::validateDate($to_date)) {
      die("<h1>Invalid date</h1>");            
    }
                        # 0, 1, 2, 3, 4, 5, 6, 7, 8, 9,10,11,12,13,14,15
    $item_widths = array(10,50,20,16,18,16,16,16,16,16,16,16,16,20,18,20);
    $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[14];
    $totals_width1 = $item_widths[6];
    $slno = $tot_amount = 0;  

    # inititate Sales Model
    $sales_api = new \ClothingRm\Sales\Model\Sales;
    $total_items = array();

    $params = array(
      'fromDate' => $from_date,
      'toDate' => $to_date,
    );

    if($mode !== '') {
      $mode_name = Utilities::get_sale_mode_name($mode);
      $params['saleMode'] = $mode;
    } else {
      $mode_name = 'All Modes';
    }

    $sales_api_response = $sales_api->get_itemwise_sales_report_bymode($params);

    if($sales_api_response['status']===false) {
        die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
        $total_items = $sales_api_response['summary']['results'];
        $total_pages = $sales_api_response['summary']['total_pages'];
        if($total_pages>1) {
            for($i=2;$i<=$total_pages;$i++) {
                $params['pageNo'] = $i;
                $params['perPage'] = 200;
                $sales_api_response = $sales_api->get_itemwise_sales_report_bymode($params);
                if($sales_api_response['status'] === true) {
                  $total_items = array_merge($total_items,$sales_api_response['summary']['results']);
                }
            }
        }
        $heading1 = 'Itemwise Sales Report By Sale Mode';
        $heading2 = 'from '.$from_date.' to '.$to_date;
    }  

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('L','A4');
    $pdf->setTitle('ItemwiseSalesByMode'.' - '.date('jS F, Y'));

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');

    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');

    $pdf->SetFont('Arial','B',8);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'L');
    $pdf->Cell($item_widths[14],6,'Rate/Unit','RTB',0,'C');
    // $pdf->Cell($item_widths[2],6,'Category','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'NOR.Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'NOR.Value','RTB',0,'C');
    $pdf->Cell($item_widths[5],6,'PKG.Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'PKG.Value','RTB',0,'C');
    $pdf->Cell($item_widths[7],6,'SEL.Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[8],6,'SEL.Value','RTB',0,'C');
    $pdf->Cell($item_widths[9],6,'ASRI.Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[10],6,'ASRI.Value','RTB',0,'C');
    $pdf->Cell($item_widths[11],6,'INS.Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[12],6,'INS.Value','RTB',0,'C');
    $pdf->Cell($item_widths[13],6,'TOT.Qty.','RTB',0,'C');        
    $pdf->Cell($item_widths[15],6,'Amount (Rs.)','RTB',1,'C');
    
    $pdf->SetFont('Arial','',8);
    $slno=$tot_amount=$tot_qty=0;
    $tot_normal_value = $tot_pkg_value = $tot_self_value = $tot_asri_value = $tot_ins_value = 0;
    $tot_normal_qty = $tot_pkg_qty = $tot_self_qty = $tot_asri_qty = $tot_ins_qty = 0;
    $total_sold_qty = $total_sold_amount = 0;
    foreach($total_items as $item_details) {
      $slno++;
      $item_name = $item_details['itemName'];
      $cat_name = $item_details['categoryName'];
      $item_rate = $item_details['itemRate'];
      $normal_sold_qty = $item_details['normalSaleQty'];
      $normal_sold_value = $item_details['normalSaleValue'];
      $pkg_sold_qty = $item_details['packageSaleQty'];
      $pkg_sold_value = $item_details['packageSaleValue'];
      $self_sold_qty = $item_details['selfSaleQty'];
      $self_sold_value = $item_details['selfSaleValue'];
      $asri_sold_qty = $item_details['asriSaleQty'];
      $asri_sold_value = $item_details['asriSaleValue'];
      $ins_sold_qty = $item_details['insuranceSaleQty'];
      $ins_sold_value = $item_details['insuranceSaleValue'];
      $tot_sold_qty = $item_details['totalQty'];
      $tot_sold_value = $item_details['totalSaleValue'];

      $tot_normal_qty += $normal_sold_qty;
      $tot_pkg_qty += $pkg_sold_qty;
      $tot_self_qty += $self_sold_qty;
      $tot_asri_qty += $asri_sold_qty;
      $tot_ins_qty += $ins_sold_qty;
      $total_sold_qty += $tot_sold_qty;

      $tot_normal_value += $normal_sold_value;
      $tot_pkg_value += $pkg_sold_value;
      $tot_self_value += $self_sold_value;
      $tot_asri_value += $asri_sold_value;
      $tot_ins_value += $ins_sold_value;
      $total_sold_amount += $tot_sold_value;

      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,substr($item_name,0,23),'RTB',0,'L');
      $pdf->Cell($item_widths[14],6,$item_rate,'RTB',0,'R');
      $pdf->Cell($item_widths[3],6,$normal_sold_qty>0?$normal_sold_qty:'','RTB',0,'R');
      $pdf->Cell($item_widths[4],6,$normal_sold_value>0?$normal_sold_value:'','RTB',0,'R');
      $pdf->Cell($item_widths[5],6,$pkg_sold_qty>0?$pkg_sold_qty:'','RTB',0,'R');
      $pdf->Cell($item_widths[6],6,$pkg_sold_value>0?$pkg_sold_value:'','RTB',0,'R');
      $pdf->Cell($item_widths[7],6,$self_sold_qty>0?$self_sold_qty:'','RTB',0,'R');
      $pdf->Cell($item_widths[8],6,$self_sold_value>0?$self_sold_value:'','RTB',0,'R');
      $pdf->Cell($item_widths[9],6,$asri_sold_qty>0?$asri_sold_qty:'','RTB',0,'R');
      $pdf->Cell($item_widths[10],6,$asri_sold_value>0?$asri_sold_value:'','RTB',0,'R');
      $pdf->Cell($item_widths[11],6,$ins_sold_qty>0?$ins_sold_qty:'','RTB',0,'R');
      $pdf->Cell($item_widths[12],6,$ins_sold_value>0?$ins_sold_value:'','RTB',0,'R');
      $pdf->Cell($item_widths[13],6,$tot_sold_qty,'RTB',0,'R');
      $pdf->Cell($item_widths[15],6,$tot_sold_value,'RTB',1,'R');
    }

    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($totals_width,6,'Totals','LRTB',0,'R');
    $pdf->Cell($item_widths[3],6,$tot_normal_qty>0?$tot_normal_qty:'','RTB',0,'R');
    $pdf->Cell($item_widths[4],6,$tot_normal_value>0?$tot_normal_value:'','RTB',0,'R');
    $pdf->Cell($item_widths[5],6,$tot_pkg_qty>0?$tot_pkg_qty:'','RTB',0,'R');
    $pdf->Cell($item_widths[6],6,$tot_pkg_value>0?$tot_pkg_value:'','RTB',0,'R');
    $pdf->Cell($item_widths[7],6,$tot_self_qty>0?$tot_self_qty:'','RTB',0,'R');
    $pdf->Cell($item_widths[8],6,$tot_self_value>0?$tot_self_value:'','RTB',0,'R');
    $pdf->Cell($item_widths[9],6,$tot_asri_qty>0?$tot_asri_qty:'','RTB',0,'R');
    $pdf->Cell($item_widths[10],6,$tot_asri_value>0?$tot_asri_value:'','RTB',0,'R');
    $pdf->Cell($item_widths[11],6,$tot_ins_qty>0?$tot_ins_qty:'','RTB',0,'R');
    $pdf->Cell($item_widths[12],6,$tot_ins_value>0?$tot_ins_value:'','RTB',0,'R');
    $pdf->Cell($item_widths[13],6,$total_sold_qty,'RTB',0,'R');    
    $pdf->Cell($item_widths[15],6,$total_sold_amount,'RTB',1,'R');

    $pdf->Output();
  }  

  public function salesByMode(Request $request) {

    $fromDate = $request->get('fromDate');
    $toDate = $request->get('toDate');
    $saleMode = $request->get('optionType');

    $item_widths = array(10,40,25,15,25,45,30);
    $totals_width = $item_widths[0]+$item_widths[1];
    $slno=0;
    $summary = array();

    # inititate Sales Model
    $sales_api = new \Sales\Model\Sales;

    $search_params = array(
      'fromDate' => $fromDate,
      'toDate' => $toDate,
      'saleMode' => $saleMode,
    );

    $sales_response = $sales_api->get_credit_sales_report($search_params);
    // dump($sales_response);
    // exit;

    if($sales_response['status']===false) {
        die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
        $total_sales = $sales_response['response']['sales'];
        $total_pages = $sales_response['response']['total_pages'];
        if($total_pages>1) {
            for($i=2;$i<=$total_pages;$i++) {
                $search_params['pageNo'] = $i;
                $search_params['perPage'] = 300;
                $sales_response = $sales_api->get_credit_sales_report($search_params);
                if($sales_response['status'] === true) {
                    $total_sales = array_merge($total_sales,$sales_response['response']['sales']);
                }
            }
        }
        $heading1 = 'Credit Sales Report';
        $heading2 = 'From '.date('d-M-Y', strtotime($fromDate)).' To '.date('d-M-Y', strtotime($toDate));
    }

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');
    $pdf->setTitle('Credit_Sales_Report'.' - '.date('d-m-Y',time()));

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');

    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');

    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'SNo.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Bill No. & Date','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Amount','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Mode','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Ref.No','RTB',0,'C');        
    $pdf->Cell($item_widths[5],6,'Patient Name','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'Age / Gender','RTB',1,'C');    
    
    $pdf->SetFont('Arial','',9);
    $slno=$tot_amount=0;
    foreach($total_sales as $sale_details) {
        $slno++;
        $bill_no = $sale_details['billNo'];
        $bill_date = date('d-M-Y',strtotime($sale_details['invoiceDate']));
        $amount = $sale_details['netPay'];
        if($sale_details['saleMode']==0) {
            $mode = 'GEN';
        } elseif($sale_details['saleMode']==1) {
            $mode = 'PKG';
        } elseif($sale_details['saleMode']==2) {
            $mode = 'INT/SE';
        } elseif($sale_details['saleMode']==3) {
            $mode = 'ASRI';            
        } elseif($sale_details['saleMode']==4) {
            $mode = 'INS';
        } else {
            $mode = 'INVALID';
        }
        $ref_no = $sale_details['patientRefNumber'];
        $patient_name = $sale_details['patientName'];
        $patient_age = $sale_details['patientAge'].' '.$sale_details['patientAgeCategory'];
        if($sale_details['patientGender']==='f') {
          $gender = ' / Female';
        } elseif($sale_details['patientGender']==='m') {
          $gender = ' / Male';
        } elseif($sale_details['patientGender']==='o') {
          $gender = ' / Other';
        } else {
          $gender = '';
        }

        $tot_amount += $amount;
        
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$bill_no.' / '.$bill_date,'RTB',0,'R');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell($item_widths[2],6,number_format($amount,2),'RTB',0,'R');
        $pdf->SetFont('Arial','',9);      
        $pdf->Cell($item_widths[3],6,$mode,'RTB',0,'R');
        $pdf->Cell($item_widths[4],6,$ref_no,'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,$patient_name,'RTB',0,'L');
        $pdf->Cell($item_widths[6],6,($patient_age>0?$patient_age.$gender:''),'RTB',1,'L');        
    }

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell($totals_width,6,'Totals','LRTB',0,'R');
    $pdf->Cell($item_widths[2],6,number_format($tot_amount,2),'RTB',0,'R');
    $pdf->Cell($item_widths[3],6,'','RTB',0,'R');    
    $pdf->Cell($item_widths[4],6,'','RTB',0,'R');    
    $pdf->Cell($item_widths[5],6,'','RTB',0,'R');
    $pdf->Cell($item_widths[6],6,'','RTB',0,'R');

    $pdf->Output();    
  }

  /** Month-over-month comparison **/
  public function momComparison(Request $request) {

    # inititate Sales Model
    $sales_api = new \ClothingRm\Sales\Model\Sales;
    $month1_response = $month2_response = $result_array = [];
    $month1_days = $month2_days = 0;
    $tot_month1_sale = $tot_month2_sale = $tot_up_sale = $tot_down_sale = 0;
    $tot_gross_profit = 0;

    $month1 = abs(!is_null($request->get('month1'))?$request->get('month1'):(int)date('m')-1);
    $year1 = abs(!is_null($request->get('year1'))?$request->get('year1'):(int)date('Y'));
    $month2 = abs(!is_null($request->get('month2'))?$request->get('month2'):(int)date('m'));
    $year2 = abs(!is_null($request->get('year2'))?$request->get('year2'):(int)date('Y'));
    $sales_mode = abs(!is_null($request->get('optionType'))?$request->get('optionType'):'');

    if($sales_mode<1 || $sales_mode>4) {
      $sales_mode = 0;
    }

    # get request variables.
    $search_params_array = [
      ['month'=>$month1, 'year' => $year1],
      ['month'=>$month2, 'year' => $year2],
    ];

    // dump($month1, $year1, $month2, $year2, $saleMode);
    // exit;

    # get month names
    $month1_year_short = $search_params_array[0]['year'];
    $month2_year_short = $search_params_array[1]['year'];

    if($search_params_array[0]['month']>=1 && $search_params_array[0]['month']<=12) {
      $month1_name = Utilities::get_calender_month_names($search_params_array[0]['month']).', '.$month1_year_short;
    } else {
      $month1_name = 'Invalid'.', '.$month1_year_short;
    }
    if($search_params_array[1]['month']>=1 && $search_params_array[1]['month']<=12) {
      $month2_name = Utilities::get_calender_month_names($search_params_array[1]['month']).', '.$month2_year_short;
    } else {
      $month2_name = 'Invalid'.', '.$month2_year_short;
    }    

    # hit api and get results.
    foreach($search_params_array as $key=>$search_params) {
      $sales_response = $sales_api->get_sales_summary_bymon($search_params);
      if($sales_response['status']===false) {
        $response = [];
      }
      $month_index = $key+1;
      ${'month'.$month_index.'_response'} = $sales_response['summary'];
    }

    $month1_days = count($month1_response);
    $month2_days = count($month2_response);

    # check we got values in at least one month.
    if($month1_days===0 && $month2_days===0) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    }

    // dump($month1_response, $month2_response);
    // exit;

    # loop through data
    for($i=0;$i<$month1_days;$i++) {
      $sale_date = $month1_response[$i]['tranDate'];
      if((int)$sales_mode===0) {
        $m1_sale_value = $month1_response[$i]['cashSales']+$month1_response[$i]['creditSales']+$month1_response[$i]['cardSales'];
        if(isset($month2_response[$i])) {
          $m2_sale_value = $month2_response[$i]['cashSales']+$month2_response[$i]['creditSales']+$month2_response[$i]['cardSales'];
        } else {
          $m2_sale_value = 0;
        }
      } elseif((int)$sales_mode===1) {
        $m1_sale_value = $month1_response[$i]['packageSales'];
        if(isset($month2_response[$i])) {
          $m2_sale_value = $month2_response[$i]['packageSales'];
        } else {
          $m2_sale_value = 0;
        }
      } elseif((int)$sales_mode===2) {
        $m1_sale_value = $month1_response[$i]['selfSales'];
        if(isset($month2_response[$i])) {
          $m2_sale_value = $month2_response[$i]['selfSales'];
        } else {
          $m2_sale_value = 0;
        }        
      } elseif((int)$sales_mode===3) {
        $m1_sale_value = $month1_response[$i]['asriSales'];
        if(isset($month2_response[$i])) {
          $m2_sale_value = $month2_response[$i]['asriSales'];
        } else {
          $m2_sale_value = 0;
        }        
      } elseif((int)$sales_mode===4) {
        $m1_sale_value = $month1_response[$i]['insuranceSales'];
        if(isset($month2_response[$i])) {
          $m2_sale_value = $month2_response[$i]['insuranceSales'];
        } else {
          $m2_sale_value = 0;
        }        
      }

      $diff_sale = $m2_sale_value-$m1_sale_value;
      $tot_month1_sale += $m1_sale_value;
      $tot_month2_sale += $m2_sale_value;

      if($diff_sale>0) {
        $up = $diff_sale;
        $down = 0;
        $tot_up_sale += $up;
      } else {
        $down = $diff_sale*-1;
        $tot_down_sale += $down;
        $up = 0;
      }

      $gross_profit = $up-$down;
      $tot_gross_profit += $gross_profit;

      $result_array[$sale_date] = array(
        'month1_sales' => $m1_sale_value,
        'month2_sales' => $m2_sale_value,
        'up' => $up,
        'down' => $down,
        'noc' => $m2_sale_value === 0 ? true : false,
        'gross_profit' => $gross_profit,
      );
      $m1_sale_value = 0;
      $m2_sale_value = 0;
    }

    $heading1 = 'Month-over-Month Sales Comparison Report';
    $heading2 = $month1_name.' (vs) '.$month2_name.' - '.Constants::$SALE_MODES[$sales_mode].' Sales';

    # start PDF printing.
    $item_widths = array(10,25,37,37,25,25,32);
    $totals_width = $item_widths[0]+$item_widths[1];
    $line_width = array_sum($item_widths);
    $slno=0;    

    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');
    $pdf->setTitle('Month-over-month-comparison-report'.' - '.date('d-m-Y',time()));

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');

    $pdf->SetFont('Arial','B',12);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');

    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'Sno','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Day of Month','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,$month1_name.' (Rs.) *','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,$month2_name.' (Rs.) *','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Up** (Rs.)','RTB',0,'C');
    $pdf->Cell($item_widths[5],6,'Down** (Rs.)','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'Profit / Loss (Rs.)','RTB',1,'C');    
    $pdf->SetFont('Arial','',10);

    foreach($result_array as $sale_date => $sale_details) {
      $slno++;
      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,'Day - '.date("d", strtotime($sale_date)),'RTB',0,'C');
      $pdf->Cell($item_widths[2],6,number_format($sale_details['month1_sales'],2),'RTB',0,'R');
      $pdf->Cell($item_widths[3],6,number_format($sale_details['month2_sales'],2),'RTB',0,'R');
      $pdf->Cell($item_widths[4],6,$sale_details['up']>0?number_format($sale_details['up'],2):'','RTB',0,'R');
      $pdf->Cell($item_widths[5],6,$sale_details['down']>0?number_format($sale_details['down'],2):'','RTB',0,'R');
      if($sale_details['gross_profit']<0) {
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell($item_widths[6],6,number_format($sale_details['gross_profit'],2),'RTB',1,'R');
        $pdf->SetFont('Arial','',10);
      } else {
        $pdf->Cell($item_widths[6],6,number_format($sale_details['gross_profit'],2),'RTB',1,'R');
      }
    }

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell($totals_width,6,'Total Sales','LRTB',0,'R');
    $pdf->Cell($item_widths[2],6,number_format($tot_month1_sale,2),'RTB',0,'R');
    $pdf->Cell($item_widths[3],6,number_format($tot_month2_sale,2),'RTB',0,'R');
    $pdf->Cell($item_widths[4],6,number_format($tot_up_sale,2),'RTB',0,'R');
    $pdf->Cell($item_widths[5],6,number_format($tot_down_sale,2),'RTB',0,'R');
    $pdf->Cell($item_widths[6],6,number_format($tot_gross_profit,2),'RTB',1,'R');
    
    $pdf->Ln(5);
    $pdf->SetFont('Arial','',8);
    $pdf->Cell($line_width,6,'* Net Sales on the day after discount.','',0,'L');
    $pdf->Ln(3);    
    $pdf->Cell($line_width,6,'** Up: Increase in Sales on the same day of previous month, Down: Decrease in Sales on the same day of previous month.','',1,'L');

    $pdf->Output();
  }
}