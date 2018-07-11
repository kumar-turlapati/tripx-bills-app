<?php 

namespace ClothingRm\Reports\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\PDF;

class ReportsAnalysisController
{

  public function __construct() {
    $this->views_path = __DIR__.'/../Views/';
    $this->inven_api = new \Inventory\Model\Inventory;
  }

  public function ioAnalysis(Request $request) {

    if(is_null($request->get('month'))) {
      $month = (int)date('m');
    } else {
      $month = (int)Utilities::clean_string($request->get('month'));
    }
    if(is_null($request->get('year'))) {
      $year = (int)date('Y');
    } else {
      $year = (int)Utilities::clean_string($request->get('year'));
    }

    // $month = 5;

    $search_params = array(
      'perPage' => 200,
      'month' => $month,
      'year' => $year,
    );

    $total_items = array();
    $inven_response = $this->inven_api->io_analysis($search_params);

    if($inven_response['status']===false) {
      die("<h1>No data is available. Change Report Filters and Try again</h1>");
    } else {
      $total_items = $inven_response['response']['records'];
      $total_pages = $inven_response['response']['total_pages'];
      if($total_pages>1) {
        for($i=2;$i<=$total_pages;$i++) {
          $search_params['pageNo'] = $i;
          $inven_response = $this->inven_api->io_analysis($search_params);
          if($inven_response['status'] === true) {
            $total_items = array_merge($total_items,$inven_response['response']['records']);
          }
        }
      }

      $month_name = date('F', mktime(0, 0, 0, $month, 10));
      $heading1 = 'Inward - Outward Report';
      $heading2 = '( Based on Item landing cost )';
      $heading1_sub = 'for the month of '.$month_name.', '.$year;
    }

    // dump($total_items);
    // dump(count($total_items));
    // exit;
    
    $item_widths = array(10,50,10,15,12,20,12,20,14,20,12,20,12,20,14,18);
    $totals_width = $item_widths[0]+$item_widths[1]+$item_widths[2]+$item_widths[3];

    # start PDF printing.
    $pdf = PDF::getInstance();
    $pdf->AliasNbPages();
    $pdf->AddPage('L','A4');
    $pdf->setTitle('IO Analysis'.' - '.date('jS F, Y'));

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,0,$heading1,'',1,'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',13);
    $pdf->Cell(0,0,$heading1_sub,'',1,'C');    

    $pdf->SetFont('Arial','B',11);
    $pdf->Ln(5);
    $pdf->Cell(0,0,$heading2,'',1,'C');

    $pdf->SetFont('Arial','B',9);
    $pdf->Ln(5);
    $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
    $pdf->Cell($item_widths[1],6,'ItemName','RTB',0,'C');
    $pdf->Cell($item_widths[2],6,'U/P','RTB',0,'C');
    $pdf->Cell($item_widths[3],6,'ItemRate','RTB',0,'C');
    $pdf->Cell($item_widths[4],6,'OpQty','RTB',0,'C');
    $pdf->Cell($item_widths[5],6,'OpValue','RTB',0,'C');
    $pdf->Cell($item_widths[6],6,'PurQty','RTB',0,'C');
    $pdf->Cell($item_widths[7],6,'PurValue','RTB',0,'C');
    $pdf->Cell($item_widths[8],6,'SRetQty','RTB',0,'C');
    $pdf->Cell($item_widths[9],6,'SRetValue','RTB',0,'C');
    $pdf->Cell($item_widths[10],6,'SaQty','RTB',0,'C');
    $pdf->Cell($item_widths[11],6,'SaValue','RTB',0,'C');
    $pdf->Cell($item_widths[12],6,'AdjQty','RTB',0,'C');
    $pdf->Cell($item_widths[13],6,'AdjValue','RTB',0,'C');
    $pdf->Cell($item_widths[14],6,'ClosQty','RTB',0,'C');
    $pdf->Cell($item_widths[15],6,'ClosValue','RTB',1,'C');
    $pdf->SetFont('Arial','',9);
    
    $tot_opening_value = $tot_purchased_value = $tot_sold_value = $tot_sr_value = 
    $tot_adj_value = $tot_clos_value = $slno = 0;

    $tot_op_qty = $tot_pu_qty = $tot_sr_qty = $tot_sa_qty = $tot_adj_qty = $tot_clos_qty = 0; 
    foreach($total_items as $item_details) {

      $opening_value = $purchased_value = $sold_value = 
      $sr_value = $adj_value = 0;

      $opening_qty = $sold_qty = $purchased_qty = $sr_qty =
      $adj_qty = $item_rate = 0;

      $item_rate = $item_details['itemRate'];

      $opening_qty = $item_details['openingQty'];
      $sold_qty = $item_details['soldQty'];
      $purchased_qty = $item_details['purchasedQty'];
      $sr_qty = $item_details['salesReturnQty'];
      $adj_qty = $item_details['adjQty'];
      $clos_qty = $item_details['closingQty'];

      $opening_value = $opening_qty*$item_rate;
      $purchased_value = $purchased_qty*$item_rate;
      $sold_value = $sold_qty*$item_rate;
      $sr_value = $sr_qty*$item_rate;
      $adj_value = $adj_qty*$item_rate;
      $clos_value = $clos_qty*$item_rate;

      $tot_op_qty += $opening_qty;
      $tot_sa_qty += $sold_qty;
      $tot_pu_qty += $purchased_qty;
      $tot_sr_qty += $sr_qty;
      $tot_clos_qty += $clos_qty;
      $tot_adj_qty += $adj_qty;

      $tot_opening_value += $opening_value;
      $tot_purchased_value += $purchased_value;
      $tot_sold_value += $sold_value;
      $tot_sr_value += $sr_value;
      $tot_clos_value += $clos_value;
      $tot_adj_value += $adj_value;

      $slno++;

      $opening_qty = ($opening_qty!=0)?number_format($opening_qty):'';
      $purchased_qty = ($purchased_qty!=0)?number_format($purchased_qty):'';
      $sr_qty = ($sr_qty!=0)?number_format($sr_qty):'';
      $sold_qty = ($sold_qty!=0)?number_format($sold_qty):'';
      $clos_qty = ($clos_qty!=0)?number_format($clos_qty):'';
      $adj_qty = ($adj_qty!=0)?number_format($adj_qty):'';

      $opening_value = ($opening_value!=0)?number_format($opening_value,2):'';
      $purchased_value = ($purchased_value!=0)?number_format($purchased_value,2):'';
      $sr_value = ($sr_value!=0)?number_format($sr_value,2):'';
      $sold_value = ($sold_value!=0)?number_format($sold_value,2):'';
      $clos_value = ($clos_value!=0)?number_format($clos_value,2):'';
      $adj_value = ($adj_value!=0)?number_format($adj_value,2):'';

      $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
      $pdf->Cell($item_widths[1],6,substr($item_details['itemName'],0,25),'RTB',0,'L');
      $pdf->Cell($item_widths[2],6,$item_details['upp'],'RTB',0,'R');
      $pdf->Cell($item_widths[3],6,$item_rate,'RTB',0,'R');
      $pdf->Cell($item_widths[4],6,$opening_qty,'RTB',0,'R');
      $pdf->Cell($item_widths[5],6,$opening_value,'RTB',0,'R');
      $pdf->Cell($item_widths[6],6,$purchased_qty,'RTB',0,'R');
      $pdf->Cell($item_widths[7],6,$purchased_value,'RTB',0,'R');
      $pdf->Cell($item_widths[8],6,$sr_qty,'RTB',0,'R');
      $pdf->Cell($item_widths[9],6,$sr_value,'RTB',0,'R');
      $pdf->Cell($item_widths[10],6,$sold_qty,'RTB',0,'R');
      $pdf->Cell($item_widths[11],6,$sold_value,'RTB',0,'R');
      $pdf->Cell($item_widths[12],6,$adj_qty,'RTB',0,'R');
      $pdf->Cell($item_widths[13],6,$adj_value,'RTB',0,'R');
      $pdf->Cell($item_widths[14],6,$clos_qty,'RTB',0,'R');
      $pdf->Cell($item_widths[15],6,$clos_value,'RTB',1,'R');
    }

    $tot_op_qty = ($tot_op_qty!=0)?number_format($tot_op_qty):'';
    $tot_pu_qty = ($tot_pu_qty!=0)?number_format($tot_pu_qty):'';
    $tot_sr_qty = ($tot_sr_qty!=0)?number_format($tot_sr_qty):'';
    $tot_sa_qty = ($tot_sa_qty!=0)?number_format($tot_sa_qty):'';
    $tot_clos_qty = ($tot_clos_qty!=0)?number_format($tot_clos_qty):'';
    $tot_adj_qty = ($tot_adj_qty!=0)?number_format($tot_adj_qty):'';

    $tot_opening_value = ($tot_opening_value!=0)?number_format($tot_opening_value,2):'';
    $tot_purchased_value = ($tot_purchased_value!=0)?number_format($tot_purchased_value,2):'';
    $tot_sr_value = ($tot_sr_value!=0)?number_format($tot_sr_value,2):'';
    $tot_sold_value = ($tot_sold_value!=0)?number_format($tot_sold_value,2):'';
    $tot_clos_value = ($tot_clos_value!=0)?number_format($tot_clos_value,2):'';
    $tot_adj_value = ($tot_adj_value!=0)?number_format($tot_adj_value,2):'';

    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($totals_width,6,'TOTALS','LRTB',0,'R');
    $pdf->Cell($item_widths[4],6,$tot_op_qty,'RTB',0,'R');
    $pdf->Cell($item_widths[5],6,$tot_opening_value,'RTB',0,'R');
    $pdf->Cell($item_widths[6],6,$tot_pu_qty,'RTB',0,'R');
    $pdf->Cell($item_widths[7],6,$tot_purchased_value,'RTB',0,'R');
    $pdf->Cell($item_widths[8],6,$tot_sr_qty,'RTB',0,'R');
    $pdf->Cell($item_widths[9],6,$tot_sr_value,'RTB',0,'R');
    $pdf->Cell($item_widths[10],6,$tot_sa_qty,'RTB',0,'R');
    $pdf->Cell($item_widths[11],6,$tot_sold_value,'RTB',0,'R');
    $pdf->Cell($item_widths[12],6,$tot_adj_qty,'RTB',0,'R');
    $pdf->Cell($item_widths[13],6,$tot_adj_value,'RTB',0,'R');
    $pdf->Cell($item_widths[14],6,$tot_clos_qty,'RTB',0,'R');
    $pdf->Cell($item_widths[15],6,$tot_clos_value,'RTB',1,'R');
    $pdf->SetFont('Arial','',9);

    $pdf->Output();
  }

}