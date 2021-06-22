<?php
  use Atawa\Utilities;
  use Atawa\Constants;

  $current_date = date("d-m-Y");
  $pagination_url = '/einvoices/list';

  $query_params = [];
  if(isset($search_params['fromDate']) && $search_params['fromDate'] !='') {
    $fromDate = $search_params['fromDate'];
    $query_params[] = 'fromDate='.$fromDate;
  } else {
    $fromDate = date("Y-m-01");
  }
  if(isset($search_params['toDate']) && $search_params['toDate'] !='' ) {
    $toDate = $search_params['toDate'];
    $query_params[] = 'toDate='.$toDate;
  } else {
    $toDate = $current_date;
  }
  if(isset($search_params['gstNo']) && $search_params['gstNo'] !== '' ) {
    $gst_no = $search_params['gstNo'];
  } else {
    $gst_no = '';
  }  

  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = '/einvoices/list';

  // dump($search_params);
  // dump($sales);
  // exit;
  // dump($fromDate, $toDate);
  // dump($_SESSION);
?>

<div class="row">
  <div class="col-lg-12">
    
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/sales/list" class="btn btn-default"><i class="fa fa-book"></i> Sales Register</a>&nbsp;&nbsp;
            <a href="/sales/entry-with-barcode" class="btn btn-default"><i class="fa fa-inr"></i> Sales Entry - Barcode</a>
            <a href="/sales/entry" class="btn btn-default"><i class="fa fa-inr"></i> Sales Entry - Manual</a>
            <a href="/sales-entry/combos" class="btn btn-default"><i class="fa fa-shopping-basket" aria-hidden="true"></i> New Combo Sale</a>
          </div>
        </div>
		
		    <div class="filters-block">
    			<div id="filters-form">
    			  <form class="form-validate form-horizontal" method="GET" action="<?php echo $page_url ?>">
      				<div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1">Filter by</div>
      				  <div class="col-sm-12 col-md-2 col-lg-2">
      						<div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
      						  <input class="span2" size="16" type="text" readonly name="fromDate" id="fromDate" value="<?php echo $fromDate ?>" />
      						  <span class="add-on"><i class="fa fa-calendar"></i></span>
      					  </div>
      				  </div>
      				  <div class="col-sm-12 col-md-2 col-lg-2">
      						<div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
      						  <input class="span2" size="16" type="text" readonly name="toDate" id="toDate" value="<?php echo $toDate ?>" />
      						  <span class="add-on"><i class="fa fa-calendar"></i></span>
      						</div>
      				  </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <input 
                    placeholder="Gstin" 
                    type="text" 
                    name="gstNo" 
                    id="gstNo" 
                    class="form-control" 
                    value="<?php echo $gst_no ?>"
                  />
                </div>
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
      				</div>
    			  </form>
    			</div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-hover font12">
            <thead>
              <tr class="font11">
                <th width="5%"  class="text-center">Sno.</th>
                <th width="10%" class="text-center">Doc. No.</th>
                <th width="10%" class="text-center">Customer Gstin</th>
                <th width="8%" class="text-center">Invoice Amount<br />(in Rs.)</th>
                <th width="10%" class="text-center">Ack. No.</th>
                <th width="12%" class="text-center">Ack. Date &amp; Time</th>
                <th width="10%" class="text-center">eWayBill No.</th>
                <th width="11%" class="text-center">eWayBill Date</th>
                <th width="7%" class="text-center">Status</th>
                <th width="25%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                foreach($einvoices as $invoice_details):
                  $sl_no++;
                  $doc_no = $invoice_details['docNo'];
                  $gst_no = $invoice_details['gstNo'];
                  $customer_gst_no = $invoice_details['customerGstNo'];
                  $irn = $invoice_details['irnNo'];
                  $ack_no = $invoice_details['ackNo'];
                  $ack_date = date("d-m-Y H:ia", strtotime(str_replace(['T', '.000Z'], ['', ''], $invoice_details['ackDt'])));
                  $invoice_amount = $invoice_details['invoiceValue'];
                  $eway_bill_no = $invoice_details['ewbNo'];
                  $eway_bill_date = !is_null($invoice_details['ewbDate']) ? date("d-m-Y H:ia", strtotime(str_replace(['T', '.000Z'], ['', ''], $invoice_details['ewbDate']))) : '';
                  $status = $invoice_details['status'];
                  $qb_location_id = $invoice_details['qblocationId'];
                  $qb_invoice_code = !is_null($invoice_details['qbinvoiceCode']) ? $invoice_details['qbinvoiceCode'] : 'unknown';
              ?>
               <tr class="font11">
                 <td class="valign-middle" align="right"><?php echo $sl_no ?></td>
                 <td class="valign-middle"><?php echo $doc_no ?></td>
                 <td class="valign-middle"><?php echo $customer_gst_no ?></td>
                 <td class="valign-middle" align="right" style="font-weight: bold;"><?php echo number_format($invoice_amount, 2, '.', 0) ?></td>
                 <td class="valign-middle" align="right"><?php echo $ack_no ?></td>
                 <td class="valign-middle" align="right"><?php echo $ack_date ?></td>
                 <td class="valign-middle" align="right"><?php echo $eway_bill_no ?></td>
                 <td class="valign-middle" align="right"><?php echo $eway_bill_date ?></td>
                 <td class="valign-middle" align="center"><?php echo $status === 'ACT' ? 'Active' : '<span style="color: red; font-weight: bold;">Cancelled</span>' ?></td>
                 <td class="valign-middle" align="left">
                  <div class="btn-actions-group" style="padding-left:10px;">
                    <a class="btn btn-success" href="javascript: printSalesBillCombo('<?php //echo $sales_code ?>')" title="Print eInvoice">
                      <i class="fa fa-print" aria-hidden="true"></i>
                    </a>
                    <?php if($status === 'ACT'): ?>
                      <a class="btn btn-danger" href="/einvoice/cancel-irn/<?php echo $irn ?>/<?php echo $qb_invoice_code ?>" title="Cancel IRN">
                        <i class="fa fa-times" aria-hidden="true"></i>
                      </a>
                    <?php endif; ?>
                    <?php if(strlen($eway_bill_no) === 0 && $status === 'ACT'): ?>
                      <a class="btn btn-primary" href="/einvoice/generate-eway-bill/<?php echo $irn ?>/<?php echo $qb_invoice_code ?>" title="Generate eWayBill">
                        <i class="fa fa-bus" aria-hidden="true"></i>
                      </a>
                    <?php endif; ?>
                    <a class="btn btn-warning" href="/einvoice/view/<?php echo $qb_invoice_code ?>?docNo=<?php echo $doc_no ?>" title="View eInvoice">
                      <i class="fa fa-eye" aria-hidden="true"></i>
                    </a>                    
                  </div>
                 </td>
               </tr> 
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
        </div>

      </div>
    </section>
  </div>
</div>