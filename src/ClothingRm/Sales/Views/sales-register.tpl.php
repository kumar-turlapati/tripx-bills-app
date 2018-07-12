<?php
  use Atawa\Utilities;
  use Atawa\Constants;

  $current_date = date("d-m-Y");
  $pagination_url = '/sales/list';

  $query_params = '';
  if(isset($search_params['fromDate']) && $search_params['fromDate'] !='') {
    $fromDate = $search_params['fromDate'];
    $query_params[] = 'fromDate='.$fromDate;
  } else {
    $fromDate = $current_date;
  }
  if(isset($search_params['toDate']) && $search_params['toDate'] !='' ) {
    $toDate = $search_params['toDate'];
    $query_params[] = 'toDate='.$toDate;
  } else {
    $toDate = $current_date;
  }
  if(isset($search_params['paymentMethod']) && $search_params['paymentMethod'] !='' ) {
    $paymentMethod = $search_params['paymentMethod'];
    $query_params[] = 'paymentMethod='.$search_params['paymentMethod'];
  } else {
    $paymentMethod = 99;
  }
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
  } else {
    $locationCode = '';
  }  
  if(isset($search_params['saExecutiveCode']) && $search_params['saExecutiveCode'] !== '' ) {
    $saExecutiveCode = $search_params['saExecutiveCode'];
  } else {
    $saExecutiveCode = '';
  }  
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = '/sales/list';
?>

<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12">
    
    <!-- Panel starts -->
    <section class="panelBox">
      <h2 class="hdg-reports text-center">Daywise Sales List</h2>
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/sales/entry" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Sale 
            </a> 
          </div>
        </div>
		
		  <div class="filters-block">
  			<div id="filters-form">
  			  <!-- Form starts -->
  			  <form class="form-validate form-horizontal" method="GET" action="/sales/list">
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
    				  <div class="col-sm-12 col-md-2 col-lg-2">
      					<div class="select-wrap">
      						<select class="form-control" name="paymentMethod" id="paymentMethod">
      						  <?php 
                      foreach($payment_methods as $key=>$value):
                        if((int)$paymentMethod === (int)$key) {
                          $selected = 'selected="selected"';
                        } else {
                          $selected = '';
                        }  
                    ?>
      							 <option value="<?php echo $key ?>" <?php echo $selected ?>>
                        <?php echo $value ?>
                      </option>
      						  <?php endforeach; ?>
      						</select>
      					 </div>
    				  </div>
              <div class="col-sm-12 col-md-2 col-lg-2">
                <div class="select-wrap">
                  <select class="form-control" name="locationCode" id="locationCode">
                    <?php 
                      foreach($client_locations as $key=>$value):
                        if($locationCode === $key) {
                          $selected = 'selected="selected"';
                        } else {
                          $selected = '';
                        }  
                    ?>
                     <option value="<?php echo $key ?>" <?php echo $selected ?>>
                        <?php echo $value ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                 </div>
              </div>
              <div class="col-sm-12 col-md-2 col-lg-2">
                <div class="select-wrap">
                  <select class="form-control" name="saExecutiveCode" id="saExecutiveCode">
                    <?php 
                      foreach($sa_executives as $key=>$value):
                        if($saExecutiveCode === $key) {
                          $selected = 'selected="selected"';
                        } else {
                          $selected = '';
                        }  
                    ?>
                     <option value="<?php echo $key ?>" <?php echo $selected ?>>
                        <?php echo $value ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                 </div>
              </div>
    				</div>
            <div class="form-group text-center">
              <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
            </div>
  			  </form>
  			  <!-- Form ends -->
  			</div>
      </div>
      <div class="table-responsive">
        <table class="table table-striped table-hover font12">
          <thead>
            <tr>
              <th width="3%"  class="text-center">Sno.</th>
              <th width="15%" class="text-center">Customer<br />Name</th>
              <?php /*<th width="7%" class="text-center">Mobile<br />Number</th>*/ ?>
              <th width="8%" class="text-center">Payment<br />Method</th>                
              <th width="14%" class="text-center">Bill No. &amp; Date</th>
              <th width="7%" class="text-center">Bill Amount<br />(in Rs.)</th>
              <th width="7%" class="text-center">Discount<br />(in Rs.)</th>
              <th width="7%" class="text-center">Taxable<br />(in Rs.)</th>
              <th width="7%" class="text-center">GST<br />(in Rs.)</th>                
              <th width="5%" class="text-center">R.off<br />(in Rs.)</th>
              <th width="7%" class="text-center">Net Pay<br />(in Rs.)</th>
              <th width="18%" class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $cntr = $sl_no;
              $tot_bill_amount = $tot_disc_amount = $tot_amount = $tot_tax_amount = 0;
              $tot_round_off = $tot_net_pay = 0;
              foreach($sales as $sales_details):
                // dump($sales_details);
                $sales_code = $sales_details['invoiceCode'];
                $invoice_date = date("d-M-Y", strtotime($sales_details['invoiceDate']));
                $mobile_number = $sales_details['mobileNo'];
                if($sales_details['customerName'] !== null) {
                  $customer_name = $sales_details['customerName'];
                } else {
                  $customer_name = '';
                }
                if($sales_details['paymentMethod'] !== '') {
                  $payment_method = Constants::$PAYMENT_METHODS_RC_SHORT[$sales_details['paymentMethod']];
                } else {
                  $payment_method = 'Invalid';
                }
                
                $tot_bill_amount += $sales_details['billAmount'];
                $tot_disc_amount += $sales_details['discountAmount'];
                $tot_amount += $sales_details['totalAmount'];
                $tot_tax_amount += $sales_details['taxAmount'];
                $tot_round_off += $sales_details['roundOff'];
                $tot_net_pay += $sales_details['netPay'];
            ?>
                <tr class="text-uppercase text-right font11">
                  <td class="valign-middle"><?php echo $cntr ?></td>
                  <td class="text-left med-name valign-middle"><?php echo $customer_name ?></td>
                  <?php /*<td class="text-left med-name valign-middle"><?php echo $mobile_number ?></td>*/ ?>
                  <td class="text-left med-name valign-middle">
                    <span style="font-weight:bold;"><?php echo $payment_method ?></span>
                  </td>
                  <td class="valign-middle"><?php echo $sales_details['billNo'].' / '.$invoice_date ?></td>
                  <td class="text-right valign-middle"><?php echo number_format($sales_details['billAmount'],2) ?></td>
                  <td class="text-right valign-middle"><?php echo number_format($sales_details['discountAmount'],2) ?></td>
                  <td class="text-right valign-middle"><?php echo number_format($sales_details['totalAmount'],2) ?></td>                    
                  <td class="text-right valign-middle"><?php echo number_format($sales_details['taxAmount'],2) ?></td>
                  <td class="text-right valign-middle"><?php echo number_format($sales_details['roundOff'],2) ?></td>
                  <td class="text-right valign-middle"><?php echo number_format($sales_details['netPay'],2) ?></td>    
                  <td>
                    <div class="btn-actions-group">
                      <?php if($sales_code !== ''): ?>
                        <a class="btn btn-danger" href="javascript: printSalesBillSmall(<?php echo $sales_details['billNo'] ?>)" title="Print Sale Bill - Small format">
                          <i class="fa fa-files-o"></i>
                        </a>
                        <a class="btn btn-primary" href="/sales-return/entry/<?php echo $sales_code ?>" title="Sales Return">
                          <i class="fa fa-undo"></i>
                        </a>                          
                        <?php /*
                        <a class="btn btn-primary" href="/sales/update/<?php echo $sales_code ?>" title="Edit Sales Transaction">
                          <i class="fa fa-pencil"></i>
                        </a>*/ ?>
                        <?php /*
                        <a class="btn btn-primary" href="javascript: printSalesBill(<?php echo $sales_details['billNo'] ?>)" title="Print Sales Bill - Normal format">
                          <i class="fa fa-print"></i>
                        </a>               
                        <a class="btn btn-primary" href="/sales/view/<?php echo $sales_code ?>" title="View Sales Transaction">
                          <i class="fa fa-eye"></i>
                        </a>
                        */?>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
          <?php
            $cntr++;
            endforeach; 
          ?>
          <tr class="text-uppercase">
            <td colspan="4" align="right">PAGE TOTALS</td>
            <td class="text-bold text-right"><?php echo number_format($tot_bill_amount,2) ?></td>
            <td class="text-bold text-right"><?php echo number_format($tot_disc_amount,2) ?></td>
            <td class="text-bold text-right"><?php echo number_format($tot_amount,2) ?></td>
            <td class="text-bold text-right"><?php echo number_format($tot_tax_amount,2) ?></td>              
            <td class="text-bold text-right"><?php echo number_format($tot_round_off,2) ?></td>
            <td class="text-bold text-right"><?php echo number_format($tot_net_pay,2) ?></td>
            <td>&nbsp;</td>
          </tr>
          <tr class="text-uppercase font14">
            <td colspan="4" align="right">DAY SALES</td>
            <td class="text-bold text-right"><?php echo isset($query_totals['billAmount']) ? number_format($query_totals['billAmount'],2) : "0.00" ?></td>
            <td class="text-bold text-right"><?php echo isset($query_totals['discountAmount']) ? number_format($query_totals['discountAmount'],2) : "0.00" ?></td>
            <td class="text-bold text-right"><?php echo isset($query_totals['totalAmount']) ? number_format($query_totals['totalAmount'],2) : "0.00"  ?></td>
            <td class="text-bold text-right"><?php echo isset($query_totals['taxAmount']) ? number_format($query_totals['taxAmount'],2) : "0.00" ?></td>
            <td class="text-bold text-right"><?php echo isset($query_totals['roundOff']) ? number_format($query_totals['roundOff'],2) : "0.00" ?></td>
            <td class="text-bold text-right"><?php echo isset($query_totals['netPay']) ? number_format($query_totals['netPay'],2) : "0.00" ?></td>
            <td>&nbsp;</td>      
          </tr>
        </tbody>
        </table>
        <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
      </div>
      </div>
    </section>
  </div>
</div>