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
}