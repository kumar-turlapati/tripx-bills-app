<?php
  use Atawa\Utilities;
  use Atawa\Constants;

  $query_params_a = []; $query_params = '';

  if(isset($search_params['searchBy']) && $search_params['searchBy'] !='') {
    $searchBy = $search_params['searchBy'];
    $query_params_a[] = 'searchBy='.$searchBy;
  } else {
    $searchBy = '';
  }
  if(isset($search_params['searchValue']) && $search_params['searchValue'] !='') {
    $searchValue = $search_params['searchValue'];
    $query_params_a[] = 'searchValue='.$searchValue;    
  } else {
    $searchValue = '';
  }
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !='') {
    $locationCode = $search_params['locationCode'];
    $query_params_a[] = 'locationCode='.$locationCode;    
  } else {
    $locationCode = $default_location;
  }  

  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params_a);
  }  
?>

<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>
        <div class="filters-block">
          <div id="filters-form">
            <form class="form-validate form-horizontal" method="POST" autocomplete="off">
              <div class="form-group">
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label">Search by</label>
                  <div class="select-wrap m-bot15">
                    <select class="form-control" name="searchBy" id="searchBy">
                      <?php 
                        foreach($search_by_a as $key=>$value): 
                          if($key===$searchBy) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                      <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label">Search value</label>
                  <input type="text" name="searchValue" id="searchValue" class="form-control" value="<?php echo $searchValue ?>">
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label">Store name</label>                  
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
                <div class="col-sm-12 col-md-3 col-lg-3">
                    <label class="control-label">&nbsp;</label>
                    <button class="btn btn-success"><i class="fa fa-file-text"></i> Search</button>
                    <button type="reset" class="btn btn-warning" onclick="javascript:resetFilter('/sales/search-bills')"><i class="fa fa-refresh"></i> Reset </button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <?php if( count($bills)>0 ) { ?>
          <div class="table-responsive">
            <table class="table table-striped table-hover font12">
              <thead>
                <tr>
                  <th width="3%"  class="text-center">Sno.</th>
                  <th width="15%" class="text-center">Customer<br />Name</th>
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
                  foreach($bills as $sales_details):
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
              </tbody>
            </table>
          </div>
        <?php } ?>
      </div>
    </section>
  </div>
</div>