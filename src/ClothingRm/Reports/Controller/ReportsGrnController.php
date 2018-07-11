<?php 

namespace ClothingRm\Reports\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\PDF;

use ClothingRm\Grn\Model\Grn;

class ReportsGrnController
{

  protected $views_path;

  public function __construct() {
  }

  public function printGrn(Request $request) {

    # inititate Sales Model
    $grn_api_call = new Grn;

    $grn_code = Utilities::clean_string($request->get('grnCode'));
    $grn_response = $grn_api_call->get_grn_details($grn_code);
    if($grn_response['status']===true) {
        $grn_details = $grn_response['grnDetails'];
    } else {
      die($this->_get_print_error_message());
    }    

    $print_date_time = date("d-M-Y h:ia");
    $slno = 0;

    // dump($grn_details);
    // exit;

    $grn_date       =    date('d-M-Y',strtotime($grn_details['grnDate']));
    $grn_no         =    $grn_details['grnNo'];
    $pay_method     =    Constants::$PAYMENT_METHODS_PURCHASE[$grn_details['paymentMethod']];
    $credit_days    =    $grn_details['creditDays'];
    $supplier_name  =    $grn_details['supplierName'];
    $po_info        =    $grn_details['poNo'].' / '.date('d-M-Y',strtotime($grn_details['purchaseDate']));
    $bill_no        =    $grn_details['billNo'];
    $total_items    =    (isset($grn_details['itemDetails']) && count($grn_details['itemDetails'])>0 ? count($grn_details['itemDetails']) : 'Invalid' );
    $bill_due_date  =    date('d-M-Y',strtotime($grn_details['paymentDueDate']));
    $remarks        =    $grn_details['remarks'];
    $grn_tax_amount =    $grn_details['taxAmount'];
    $grn_value      =    $grn_details['netPay'];

    $items_total = $grn_details['billAmount'];
    $discount_amount = $grn_details['discountAmount'];
    $total_tax_amount = $grn_details['taxAmount'];

    if( isset($grn_details['discountPercent']) ) {
      $discount_percent = $grn_details['discountPercent'];
    } else {
      $discount_percent = 0;
    }
    if(isset($grn_details['otherTaxes'])) {
      $other_taxes = $grn_details['otherTaxes'];
    } else {
      $other_taxes = 0;
    }
    if(isset($grn_details['adjustment'])) {
      $adjustment = $grn_details['adjustment'];
    } else {
      $adjustment = 0;
    }
    if(isset($grn_details['shippingCharges'])) {
      $shipping_charges = $grn_details['shippingCharges'];
    } else {
      $shipping_charges = 0;
    }
    if(isset($grn_details['remarks'])) {
      $remarks = $grn_details['remarks'];
    } else {
      $remarks = '';
    }    

    $items_tot_after_discount = $items_total-$discount_amount;
    $grand_total = $items_tot_after_discount+$total_tax_amount+
                   $other_taxes+$shipping_charges+($adjustment);

    $net_pay = $grn_details['netPay'];
    $round_off = $grn_details['roundOff'];

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('L','A4');

    # Print Bill Information.
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
 
    # second row
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($header_widths[0],6,'GRN No. & Date','LRTB',0,'C');
    $pdf->Cell($header_widths[1],6,'ItemsTotal (Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[2],6,'Discount (Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[3],6,'BilledAmount (Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[4],6,'Taxes - GST (Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[5],6,'OtherTaxes (Rs.)','RTB',1,'C');
    
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($header_widths[0],6,$grn_no.'/ '.$grn_date,'LRB',0,'C');
    $pdf->SetFont('Arial','',9);
    $pdf->Cell($header_widths[1],6,$items_total,'RB',0,'C');
    $pdf->Cell($header_widths[2],6,$discount_amount,'RB',0,'C');
    $pdf->Cell($header_widths[3],6,$items_tot_after_discount,'RB',0,'C');
    $pdf->Cell($header_widths[4],6,$total_tax_amount,'RB',0,'C');
    $pdf->Cell($header_widths[5],6,$other_taxes,'RB',1,'C');

    # third row
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($header_widths[0],6,'GRN Value (Rs.)','LRTB',0,'C');
    $pdf->Cell($header_widths[1],6,'Shipping/Freight (Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[2],6,'Adjustments(Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[3],6,'GrandTotal','RTB',0,'C');
    $pdf->Cell($header_widths[4],6,'RoundOff(Rs.)','RTB',0,'C');
    $pdf->Cell($header_widths[5],6,'TotalGRNItems','RTB',1,'C');

    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($header_widths[0],6,number_format($grn_value,2),'LRB',0,'C');
    $pdf->SetFont('Arial','',9);
    $pdf->Cell($header_widths[1],6,number_format($shipping_charges,2),'RB',0,'C');
    $pdf->Cell($header_widths[2],6,number_format($adjustment,2),'RB',0,'C');
    $pdf->Cell($header_widths[3],6,number_format($grand_total,2),'RB',0,'C');
    $pdf->Cell($header_widths[4],6,number_format($round_off,2),'RB',0,'C');
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($header_widths[5],6,$total_items,'RB',1,'C');
    $pdf->SetFont('Arial','',9);

    # fourth row(s)
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($item_widths[0],6,'Sno.','LRB',0,'C');
    $pdf->Cell($item_widths[1],6,'ItemName','RB',0,'C');
    $pdf->Cell($item_widths[2],6,'BatchNo.& ExpiryDate','RB',0,'C');
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
        $item_name = $item_details['itemName'];
        $acc_qty = $item_details['itemQty'];
        $item_qty = $item_details['itemQty']-$item_details['freeQty'];
        $item_rate = $item_details['itemRate'];
        $vat_percent = $item_details['vatPercent'];
        $item_amount = round($item_qty*$item_rate,2);
        $batch_no = $item_details['batchNo'];
        $exp_date = ($item_details['expdateMonth']<10?$item_details['expdateMonth']:'0'.$item_details['expdateMonth']).'/'.$item_details['expdateYear'];
        if($vat_percent>0) {
          $vat_amount = round(($item_amount*$vat_percent)/100,2);
        } else {
          $vat_amount = 0;
        }
        $total_amount = $item_amount+$vat_amount;
        $total_value += $total_amount;
        
        $pdf->Ln();
        $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
        $pdf->Cell($item_widths[1],6,substr($item_details['itemName'],0,28),'RTB',0,'L');
        $pdf->Cell($item_widths[2],6,$batch_no.', '.$exp_date,'RTB',0,'L');
        $pdf->Cell($item_widths[3],6,number_format($acc_qty,2),'RTB',0,'R');  
        $pdf->Cell($item_widths[4],6,number_format($item_qty,2),'RTB',0,'R');
        $pdf->Cell($item_widths[5],6,number_format($item_rate,2),'RTB',0,'R');
        $pdf->Cell($item_widths[6],6,$vat_percent,'RTB',0,'R');
        $pdf->Cell($item_widths[7],6,number_format($item_amount,2),'RTB',0,'R');
        $pdf->Cell($item_widths[8],6,number_format($vat_amount,2),'RTB',0,'R');
        $pdf->Cell($item_widths[9],6,number_format($total_amount,2),'RTB',0,'R');        
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

  /**
   * returns error message for the reports.
  **/
  private function _get_print_error_message() {
    return "<h1>Invalid Request</h1>";
  }  
}