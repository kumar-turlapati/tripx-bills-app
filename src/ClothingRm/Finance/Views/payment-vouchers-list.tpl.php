<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  
  $current_date = date("d-m-Y");

  // dump($search_params);

  $query_params = [];
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
  if(isset($search_params['bankCode']) && $search_params['bankCode'] !== '' ) {
    $bankCode = $search_params['bankCode'];
    $query_params[] = 'bankCode='.$bankCode;
  } else {
    $bankCode = '';
  }

  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }

  $page_url = '/fin/payment-vouchers';
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <h2 class="hdg-reports text-center">Payment Vouchers List</h2>
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/fin/payment-voucher/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Payment Voucher 
            </a> 
          </div>
        </div>
  		  <div class="filters-block">
    		  <div id="filters-form">
            <form class="form-validate form-horizontal" method="POST">
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
                    <select class="form-control" name="partyCode" id="partyCode">
                      <?php 
                        foreach($parties as $key=>$value): 
                          if($party_code === $key) {
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
                  <div class="select-wrap">                  
                    <select class="form-control" name="bankCode" id="bankCode">
                      <?php 
                        foreach($bank_names as $key=>$value): 
                          if($bankCode === $key) {
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
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
            </div>
           </form>        
			    </div>
        </div>
        <div class="table-responsive">
          <?php if(count($vouchers)>0): ?>
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font12">
                <th width="5%" class="text-center">Sno</th>
                <th width="8%" class="text-center">Voucher No.</th>
                <th width="8%" class="text-center">Voucher Date</th>
                <th width="8%" class="text-center">Amount</span></th>
                <th width="8%" class="text-center">Payment mode</span></th>                
                <th width="20%" class="text-center">Party name</th>
                <th width="8%" class="text-center">Bill No.</th>
                <th width="20%" class="text-center">Bank name</th>
                <th width="8%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $cntr = $sl_no;
                $total = 0;
                foreach($vouchers as $voucher_details):
                  $voucher_no = $voucher_details['voucherNo'];
                  $voucher_date = date('d-M-Y', strtotime($voucher_details['voucherDate']));
                  $amount = $voucher_details['amount'];
                  if($voucher_details['paymentMode']==='c') {
                    $payment_mode = 'Cash';
                  } else {
                    $payment_mode = 'Bank';
                  }
                  $party_name = $voucher_details['partyName'];
                  $bank_name = $voucher_details['bankName'];
                  $bill_no = $voucher_details['billNo'];
                  $total += $amount;
              ?>
                <tr class="font12">
                  <td align="right"><?php echo $cntr ?></td>
                  <td align="right"><?php echo $voucher_no ?></td>
                  <td><?php echo $voucher_date ?></td>
                  <td align="right"><?php echo number_format($amount,2) ?></td>
                  <td align="center"><?php echo $payment_mode ?></td>                
                  <td><?php echo $party_name ?></td>
                  <td><?php echo $bill_no ?></td>
                  <td><?php echo $bank_name ?></td>
                  <td>
                  <?php if($voucher_no>0): ?>
                    <div class="btn-actions-group" align="right">                    
                      <a class="btn btn-primary" href="/fin/payment-voucher/update/<?php echo $voucher_no ?>" title="Edit Voucher">
                        <i class="fa fa-pencil"></i>
                      </a>&nbsp;
                      <a class="btn btn-danger delPcVoucher" href="/fin/payment-voucher/delete/<?php echo $voucher_no ?>" title="Delete Voucher">
                        <i class="fa fa-times"></i>
                      </a>
                    </div>
                  <?php endif; ?>
                  </td>
                </tr>
            <?php
              $cntr++;
              endforeach; 
            ?>
              <tr class="text-bold">
                <td colspan="3" align="right">TOTALS</td>
                <td align="right"><?php echo number_format($total, 2) ?></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
            </tbody>
          </table>
          <?php endif; ?>    
          <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
        </div>
      </div>
    </section>
  </div>
</div>