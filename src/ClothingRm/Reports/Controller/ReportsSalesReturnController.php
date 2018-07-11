<?php 

namespace ClothingRm\Reports\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\PDF;

use ClothingRm\SalesReturns\Model\SalesReturns;

class ReportsSalesReturnController
{

  protected $views_path;

  public function __construct() {
  }

  public function printSalesReturnBill(Request $request) {

    # inititate Sales Model
    $sales = new SalesReturns;
    $returnCode = $request->get('returnCode');
    $slno = 0;

    $print_date_time = date("d-M-Y h:ia");
    $operator_name = isset($_SESSION['uname']) ? $_SESSION['uname'] : '';

    $sales_return_response = $sales->get_sales_return_details($returnCode);
    $status = $sales_return_response['status'];
    if($status) {
      $return_details = $sales_return_response['returnDetails'];
      $return_item_details = $return_details['itemDetails'];
      unset($return_details['itemDetails']);
    } else {
      die($this->_get_print_error_message());
    }

    // dump($return_details);
    // dump($return_item_details);
    // exit;

    $return_date                    =    date('d-M-Y',strtotime($return_details['returnDate']));
    $return_time                    =    date('h:ia',strtotime($return_details['createdTime']));
    $mrn_no                         =    $return_details['mrnNo'];
    $bill_no                        =    $return_details['billNo'];
    $bill_date                      =    date('d-M-Y',strtotime($return_details['invoiceDate']));

    $total_return_amount            =    $return_details['totalReturnAmount'];
    $total_return_amount_round      =    $return_details['totalReturnAmountRound'];
    $return_amount                  =    $return_details['returnAmount'];

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','A4');

    # Print Bill Information.
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,'Sales Return Bill','',1,'C');
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(4);

    # first row
    $header_widths = array(70,40,40,40);
    $item_widths = array(12,98,35,15,15,15);
    $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3]+$item_widths[4];
    $terms1_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3];
    $terms2_width = $item_widths[4]+$item_widths[5];

    $pdf->Cell($header_widths[0],6,'MRN (Material Return Note) No.','LRTB',0,'C');
    $pdf->Cell($header_widths[1],6,'Return Date & Time','RTB',0,'C');
    $pdf->Cell($header_widths[2],6,'Sale Bill No.','RTB',0,'C');
    $pdf->Cell($header_widths[3],6,'Sale Bill Date','RTB',1,'C');

    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($header_widths[0],6,$mrn_no,'LRTB',0,'C');

    $pdf->SetFont('Arial','',8);
    $pdf->Cell($header_widths[1],6,$return_date.', '.$return_time,'LRTB',0,'C');
    $pdf->Cell($header_widths[2],6,$bill_no,'RTB',0,'C');
    $pdf->Cell($header_widths[3],6,$bill_date,'RTB',0,'C');

    # second row
    $pdf->SetFont('Arial','B',9);
    $pdf->Ln();
    $pdf->Cell($item_widths[0],6,'Sl.No.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'Item Name','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'Lot No.','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'Qty.','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'Rate','RTB',0,'C');  
    $pdf->Cell($item_widths[5],6,'Amount','RTB',0,'C');
    $pdf->SetFont('Arial','',8);

    foreach($return_item_details as $item_details) {
        $slno++;
        $amount = $item_details['itemQty']*$item_details['itemRate'];
        
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$item_details['itemName'],'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$item_details['lotNo'],'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,number_format($item_details['itemQty'],2),'RTB',0,'R');
        $pdf->Cell($item_widths[4],6,number_format($item_details['itemRate'],2),'RTB',0,'R');  
        $pdf->Cell($item_widths[5],6,number_format($amount,2),'RTB',0,'R');
    }

    $pdf->Ln();
    $pdf->SetFont('Arial','B',9);    
    $pdf->Cell($totals_width,6,'Gross Total','LRTB',0,'R');
    $pdf->Cell($item_widths[5],6,$total_return_amount,'LRTB',0,'R');
    $pdf->Ln();
    
    $pdf->Cell($totals_width,6,'(+/-) Round Off','LRTB',0,'R');
    $pdf->Cell($item_widths[5],6,$total_return_amount_round,'RTB',0,'R');
    $pdf->Ln();
    $pdf->Cell($totals_width,6,'Net Pay','LRTB',0,'R');
    $pdf->Cell($item_widths[5],6,$return_amount,'RTB',0,'R');
    $pdf->Ln();
    
    $pdf->SetFont('Arial','',9);
    // $pdf->MultiCell($terms1_width+$terms2_width,4,$terms_text,'LRTB','L');

    $pdf->Cell(67,10,'Print Date & Time: '.$print_date_time,'LRTB',0,'L');
    $pdf->Cell(47,10,'Operator Name: '.$operator_name,'RTB',0,'L');
    $pdf->Cell(76,10,'Authorized Signature: ','RTB',0,'L');
    $pdf->Output();
  }

  public function salesReturnRegister(Request $request) 
  {

        $from_date = $request->get('fromDate');
        $to_date = $request->get('toDate');
        $location_code = $request->get('locationCode');

        $item_widths = array(12,38,24,20,25,25,20,25);
        $totals_width = $item_widths[0]+$item_widths[1];
        $slno=0;
        $total_items = array();

        # inititate Sales Model
        $sales_return_api = new SalesReturns;

        $search_params = array(
          'fromDate' => $from_date,
          'toDate' => $to_date,
          'locationCode' => $location_code,
        );

        $sales_return_response = $sales_return_api->get_all_sales_returns($search_params);
        if($sales_return_response['status']===false) {
            die("<h1>No data is available. Change Report Filters and Try again</h1>");
        } else {
            $total_items = $sales_return_response['sales_returns'];
            $total_pages = $sales_return_response['total_pages'];
            if($total_pages>1) {
                for($i=2;$i<=$total_pages;$i++) {
                    $sales_return_response = $sales_return_api->get_all_sales_returns($i,100,$search_params);
                    if($sales_return_response['status'] === true) {
                        $total_items = array_merge($total_items,$sales_return_response['sales_returns']);
                    }
                }
            }            
            $heading1 = 'Daywise Sales Return Register';
            $heading2 = '( from '.$from_date.' to '.$to_date.' )';
        }

        // dump($sales_return_transactions);
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
        $pdf->Cell($item_widths[0],6,'Sl.No.','LRTB',0,'C');
        $pdf->Cell($item_widths[1],6,'Bill No.&Date','RTB',0,'C');
        $pdf->Cell($item_widths[2],6,'Bill Amount','RTB',0,'C');
        $pdf->Cell($item_widths[3],6,'MRN No.','RTB',0,'C');
        $pdf->Cell($item_widths[4],6,'Return Date','RTB',0,'C');        
        $pdf->Cell($item_widths[5],6,'Gross Amount','RTB',0,'C');
        $pdf->Cell($item_widths[6],6,'Round Off','RTB',0,'C');
        $pdf->Cell($item_widths[7],6,'Return Value','RTB',0,'C');  
        $pdf->SetFont('Arial','',9);

        $tot_return_gross = $tot_round_off = $tot_return_amount = $tot_bill_amount = 0;
        foreach($total_items as $item_details) {
            $slno++;
            $bill_info = $item_details['billNo'].' / '.date("d-M-Y", strtotime($item_details['invoiceDate']));

            $tot_return_gross += $item_details['totalReturnAmount'];
            $tot_round_off += $item_details['totalReturnAmountRound'];
            $tot_return_amount += $item_details['returnAmount'];
            $tot_bill_amount += $item_details['netPay'];
            $mrn_no = trim($item_details['mrnNo']);
            $return_date = date('d-M-Y', strtotime($item_details['returnDate']));
            
            $pdf->Ln();
            $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
            $pdf->Cell($item_widths[1],6,$bill_info,'RTB',0,'L');
            $pdf->Cell($item_widths[2],6,number_format($item_details['netPay'],2),'RTB',0,'R');
            
            $pdf->SetFont('Arial','B',9);
            $pdf->Cell($item_widths[3],6,$mrn_no,'RTB',0,'R');
            $pdf->SetFont('Arial','',9);

            $pdf->Cell($item_widths[4],6,$return_date,'RTB',0,'R');
            $pdf->Cell($item_widths[5],6,number_format($item_details['totalReturnAmount'],2),'RTB',0,'R');
            $pdf->Cell($item_widths[6],6,number_format($item_details['totalReturnAmountRound'],2),'RTB',0,'R');            
            $pdf->Cell($item_widths[7],6,number_format($item_details['returnAmount'],2),'RTB',0,'R');            
        }

        $pdf->Ln();
        $pdf->SetFont('Arial','B',11);    
        $pdf->Cell($totals_width,6,'Totals','LRTB',0,'R');
        $pdf->Cell($item_widths[2],6,number_format($tot_bill_amount,2),'LRTB',0,'R');
        $pdf->Cell($item_widths[3],6,'','LRTB',0,'R');
        $pdf->Cell($item_widths[4],6,'','LRTB',0,'R');        
        $pdf->Cell($item_widths[5],6,number_format($tot_return_gross,2),'LRTB',0,'R');                
        $pdf->Cell($item_widths[6],6,number_format($tot_round_off,2),'LRTB',0,'R');                
        $pdf->Cell($item_widths[7],6,number_format($tot_return_amount,2),'LRTB',0,'R');

        $pdf->Output();             
  }

  public function itemwiseSalesReturns(Request $request) {
    $from_date = $request->get('fromDate');
    $to_date = $request->get('toDate');
    $location_code = $request->get('locationCode');

    $item_widths = array(8,36,25,21,38,24,16,12,18);
    $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[3]+$item_widths[4]+
                    $item_widths[5]+$item_widths[6]+$item_widths[7]+$item_widths[2];
    $slno=0;       

    # inititate Sales Model
    $sales_return_api = new SalesReturns;

    $params = array(
      'fromDate' => $from_date,
      'toDate' => $to_date,
      'perPage' => 100,
      'pageNo' => 1,
      'locationCode' => $location_code
    );

    $sr_response = $sales_return_api->get_itemwise_sales_returns($params);
    if($sr_response['status']===false) {
        die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
        $total_items = $sr_response['returns'];
        $total_pages = $sr_response['total_pages'];
        if($total_pages>1) {
            for($i=2;$i<=$total_pages;$i++) {
                $params['pageNo'] = $i;
                $sr_response = $sales_return_api->get_itemwise_sales_returns($params);
                if($sr_response['status'] === true) {
                    $total_items = array_merge($total_items,$sr_response['returns']);
                }
            }
        }

        $heading1 = 'Itemwise Sales Returns';
        $heading2 = '( from '.$from_date.' to '.$to_date.' )';

        $locations = Utilities::get_client_locations(false, true);
        if(is_array($locations) && count($locations)>0 && $location_code !== '') {
            $location_name = $locations[$location_code];
        } else {
            $location_name = '';
        }
        if($location_name !== '') {
            $heading1 .= ' - '.$location_name;
        }
    }

    // dump($total_items);
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
    $pdf->Cell($item_widths[0],6,'SNo','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'BillNo.&Date','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'CustomerName','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'ReturnDate','RTB',0,'C');   
    $pdf->Cell($item_widths[4],6,'ItemName','RTB',0,'C');
    $pdf->Cell($item_widths[5],6,'LotNo.','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'Rate','RTB',0,'C');
    $pdf->Cell($item_widths[7],6,'RQty.','RTB',0,'C');
    $pdf->Cell($item_widths[8],6,'Amount','RTB',0,'C');  
    $pdf->SetFont('Arial','',9);

    $total_return_amount = 0;
    foreach($total_items as $item_details) {
        $slno++;
        $bill_info = $item_details['billNo'].' / '.date("d-M-Y", strtotime($item_details['billDate']));
        $customer_name = $item_details['customerName'] !== '' ? substr($item_details['customerName'], 0, 10) : '';
        $item_name = substr($item_details['itemName'],0,15);
        $lot_no = $item_details['lotNo'];
        $rate = $item_details['itemRate'];
        $qty = $item_details['itemQty'];
        $amount = $qty*$rate;
        $total_return_amount += $amount;

        // $tot_return_gross += $item_details['totalReturnAmount'];
        // $tot_round_off += $item_details['totalReturnAmountRound'];
        // $tot_return_amount += $item_details['returnAmount'];
        // $tot_bill_amount += $item_details['netPay'];
        // $mrn_no = trim($item_details['mrnNo']);
        $return_date = date('d-M-Y', strtotime($item_details['returnDate']));
        
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,$bill_info,'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$customer_name,'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,$return_date,'RTB',0,'L');
        $pdf->Cell($item_widths[4],6,$item_name,'RTB',0,'L');
        $pdf->Cell($item_widths[5],6,$lot_no,'RTB',0,'L');
        $pdf->Cell($item_widths[6],6,number_format($rate,2),'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,number_format($qty,2),'RTB',0,'R');        
        $pdf->Cell($item_widths[8],6,number_format($amount,2),'RTB',0,'R');            
    }

    $pdf->Ln();
    $pdf->SetFont('Arial','B',9);    
    $pdf->Cell($totals_width,6,'TOTAL RETURN VALUE','LRTB',0,'R');
    $pdf->Cell($item_widths[8],6,number_format($total_return_amount,2),'LRTB',0,'R');

    $pdf->Output();    
  }

  /**
   * returns error message for the reports.
  **/
  private function _get_print_error_message() {
    return "<h1>Invalid Request</h1>";
  }  


}