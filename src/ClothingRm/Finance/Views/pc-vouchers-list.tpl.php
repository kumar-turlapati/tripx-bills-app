<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  
  $current_date = date("d-m-Y");

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
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = '';
  }
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = $pagination_url = '/fin/pc-vouchers';
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
            <a href="/fin/pc-voucher/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Petty Cash Voucher 
            </a>
            <a href="/fin/petty-cash-book" class="btn btn-default">
              <i class="fa fa-inr"></i> Petty Cash Book
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
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($client_locations as $location_key=>$value):
                          $location_key_a = explode('`', $location_key);
                          if($locationCode === $location_key_a[0]) {
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
			    </div>
        </div>
        
        <div class="table-responsive">
          <?php if(count($vouchers)>0): ?>
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font12">
                <th width="5%" class="text-center valign-middle">Sno</th>
                <th width="6%" class="text-center valign-middle">Voc.no.</th>
                <th width="8%" class="text-center valign-middle">Voc.date</th>
                <th width="8%" class="text-center valign-middle">Amount</span></th>
                <th width="8%" class="text-center valign-middle">Tran.type</span></th>                
                <th width="15%" class="text-center valign-middle">Ref.no.</th>
                <th width="8%" class="text-center valign-middle">Ref.date</th>
                <th width="25%" class="text-center valign-middle">Narration</th>
                <th width="8%" class="text-center valign-middle">Actions</th>
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
                  $action = ucwords($voucher_details['action']);
                  $ref_no = $voucher_details['refNo'];
                  if($voucher_details['refDate'] !== '0000-00-00') {
                    $ref_date = date('d-M-Y', strtotime($voucher_details['refDate']));
                  } else {
                    $ref_date = '';
                  }
                  $narration = $voucher_details['narration'];
                  $location_code = $location_codes[$voucher_details['locationID']];
                  $total += $amount;
              ?>
                <tr class="font12">
                  <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                  <td align="right" class="valign-middle"><?php echo $voucher_no ?></td>
                  <td class="valign-middle"><?php echo $voucher_date ?></td>
                  <td align="right" class="valign-middle"><?php echo number_format($amount,2) ?></td>
                  <td align="left" class="valign-middle"><?php echo $action ?></td>                
                  <td class="valign-middle"><?php echo $ref_no ?></td>
                  <td class="valign-middle"><?php echo $ref_date ?></td>
                  <td class="valign-middle"><?php echo $narration ?></td>
                  <td class="valign-middle">
                  <?php if($voucher_no>0): ?>
                    <div class="btn-actions-group" align="right">                    
                      <a class="btn btn-primary" href="/fin/pc-voucher/update/<?php echo $voucher_no ?>?l=<?php echo $location_code ?>" title="Edit Voucher">
                        <i class="fa fa-pencil"></i>
                      </a>&nbsp;
                      <a class="btn btn-danger delPcVoucher" href="/fin/pc-voucher/delete/<?php echo $voucher_no ?>?l=<?php echo $location_code ?>" title="Remove Voucher" id="<?php echo $voucher_no ?>">
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