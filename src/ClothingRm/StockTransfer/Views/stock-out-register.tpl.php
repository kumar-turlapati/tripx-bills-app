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
  if(isset($search_params['fromLocationCode']) && $search_params['fromLocationCode'] !== '' ) {
    $fromLocationCode = $search_params['fromLocationCode'];
    $query_params[] = 'fromLocationCode='.$fromLocationCode;
  } else {
    $fromLocationCode = '';
  }
  if(isset($search_params['toLocationCode']) && $search_params['toLocationCode'] !== '' ) {
    $toLocationCode = $search_params['toLocationCode'];
    $query_params[] = 'toLocationCode='.$fromLocationCode;
  } else {
    $toLocationCode = '';
  }  
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = '/stock-transfer/register';
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
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/stock-transfer/out" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Stock Transfer 
            </a> 
          </div>
        </div>
  		  <div class="filters-block">
    		  <div id="filters-form">
            <!-- Form starts -->
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
                    <select class="form-control" name="fromLocationCode" id="fromLocationCode">
                      <?php 
                        foreach($from_locations as $location_key=>$value):
                          $location_key_a = explode('`', $location_key);
                          if($fromLocationCode === $location_key_a[0]) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }  
                      ?>
                       <option value="<?php echo $location_key_a[0] ?>" <?php echo $selected ?>>
                          <?php echo $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="toLocationCode" id="toLocationCode">
                      <?php 
                        foreach($to_locations as $location_key=>$value):
                          $location_key_a = explode('`', $location_key);
                          if($toLocationCode === $location_key_a[0]) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }  
                      ?>
                       <option value="<?php echo $location_key_a[0] ?>" <?php echo $selected ?>>
                          <?php echo $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>         
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
            </div>
           </form>        
          <!-- Form ends -->
			    </div>
        </div>
        <div class="table-responsive">
          <?php if(count($transactions)>0): ?>
           <table class="table table-striped table-hover font11">
            <thead>
              <tr class="font12">
                <th width="5%" class="text-center valign-middle">Sno</th>
                <th width="8%" class="text-center valign-middle">Voucher no.</th>
                <th width="8%" class="text-center valign-middle">Voucher date</th>
                <th width="18%" class="text-center valign-middle">From store</span></th>                
                <th width="18%" class="text-center valign-middle">To store</th>
                <th width="8%" class="text-center valign-middle">Transfer code</th>
                <th width="8%" class="text-center valign-middle">Transfer value</span></th>
                <th width="8%" class="text-center valign-middle">Transfer qty.</span></th>                
                <th width="12%" class="text-center valign-middle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $cntr = $sl_no;
                $total_amount = $total_qty = 0;
                foreach($transactions as $tran_details):
                  $voucher_no = $tran_details['transferNo'];
                  $voucher_date = date('d-M-Y', strtotime($tran_details['transferDate']));
                  $transfer_code = $tran_details['transferCode'];
                  $transfer_value = $tran_details['netpay'];
                  $transfer_qty = $tran_details['totalQty'];
                  $from_location_id = $tran_details['fromLocationID'];
                  $to_location_id = $tran_details['toLocationID'];
                  $from_location_name = isset($location_ids[$from_location_id]) ?  $location_ids[$from_location_id] : 'Invalid';                  
                  $to_location_name = isset($location_ids[$to_location_id]) ?  $location_ids[$to_location_id] : 'Invalid';                  

                  $total_amount += $transfer_value;
                  $total_qty += $transfer_qty;
              ?>
                <tr class="font11">
                  <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                  <td align="right" class="valign-middle"><?php echo $voucher_no ?></td>
                  <td class="valign-middle"><?php echo $voucher_date ?></td>
                  <td class="valign-middle"><?php echo $from_location_name ?></td>
                  <td class="valign-middle"><?php echo $to_location_name ?></td>
                  <td class="valign-middle"><?php echo $transfer_code ?></td>
                  <td align="right" class="valign-middle"><?php echo number_format($transfer_value,2) ?></td>
                  <td align="right" class="valign-middle"><?php echo number_format($transfer_qty, 2) ?></td>                
                  <td class="valign-middle">
                  <?php if($voucher_no>0): ?>
                    <div class="btn-actions-group" align="right">
                      <a class="btn btn-warning" href="/stock-transfer/validate/<?php echo $transfer_code ?>" title="Validate Stock Transfer">
                        <i class="fa fa-check"></i>
                      </a>&nbsp;&nbsp;
                      <a class="btn btn-primary" href="/stock-transfer/out/<?php echo $transfer_code ?>" title="View this voucher">
                        <i class="fa fa-eye"></i>
                      </a>
                      <?php if(Utilities::is_admin()): ?>
                        <a class="btn btn-danger delStransfer" href="/stock-transfer/delete/<?php echo $transfer_code ?>" title="Delete this voucher">
                          <i class="fa fa-times"></i>
                        </a>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                  </td>
                </tr>
            <?php
              $cntr++;
              endforeach; 
            ?>
              <tr class="text-bold">
                <td colspan="6" align="right" class="valign-middle">TOTALS</td>
                <td align="right" class="valign-middle"><?php echo number_format($total_amount, 2, '.', '') ?></td>
                <td align="right" class="valign-middle"><?php echo number_format($total_qty, 2, '.', '') ?></td>
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