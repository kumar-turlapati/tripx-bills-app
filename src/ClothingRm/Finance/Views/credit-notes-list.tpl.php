<?php

  use Atawa\Utilities;
  use Atawa\Constants;
  
  $current_date = date("d-m-Y");
  $user_type = (int)$_SESSION['utype'];

  // dump($search_params);

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

  $page_url = '/fin/credit-notes';
?>

<div class="row">
  <div class="col-lg-12">
    <!-- Panel starts -->
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
            <a href="/fin/credit-note/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Credit Note 
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
          <?php if(count($cnotes)>0): ?>
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font12">
                <th width="5%" class="text-center">Sno.</th>
                <th width="10%" class="text-center">Invoice date</th>                
                <th width="10%" class="text-center">Invoice no.</th>
                <th width="10%" class="text-center">Credit Note no.</th>
                <th width="10%" class="text-center">Credit Note date</th>
                <th width="10%" class="text-center">Credit Note value</span></th>
                <th width="10%" class="text-center">Consumed Invoice No.</th>
                <th width="10%" class="text-center">Balance value</span></th>                
                <th width="15%" class="text-center">Store name</th>
                <th width="15%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $cntr = $sl_no;
                $total = 0;
                $balance_total = 0;
                foreach($cnotes as $voucher_details):
                  $voucher_no = $voucher_details['cnNo'];
                  $voucher_code = $voucher_details['cnCode'];
                  $voucher_date = date('d-M-Y', strtotime($voucher_details['cnDate']));
                  $location_id = $voucher_details['locationID'];
                  $location_name = isset($location_ids[$location_id]) ?  $location_ids[$location_id] : 'Invalid';
                  $voucher_amount = $voucher_details['cnValue'];
                  $bill_no = $voucher_details['billNo'] >0 ? $voucher_details['billNo'] : '-';
                  $invoice_date = !is_null($voucher_details['invoiceDate']) ? date('d-M-Y', strtotime($voucher_details['invoiceDate'])) : '-';
                  $balance_value = $voucher_details['balanceValue'];
                  $voucher_type = $voucher_details['cnType'];
                  $invoice_code = $voucher_details['invoiceCode'];
                  $consumed_invoice_no = $voucher_details['consumedInvoiceNo'];
                  $consumed_invoice_code =  $voucher_details['consumedInvoiceCode'];

                  $total += $voucher_amount;
                  $balance_total += $balance_value;
              ?>
                <tr class="font11">
                  <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                  <td align="center" class="valign-middle"><?php echo $invoice_date ?></td>
                  <td align="right" class="valign-middle">
                    <i class="fa fa-external-link" aria-hidden="true"></i>&nbsp;
                    <a href="/sales/view-invoice/<?php echo $invoice_code ?>" target="_blank" class="hyperlink">
                      <?php echo $bill_no ?>
                    </a>
                  </td> 
                  <td align="right" class="valign-middle"><?php echo $voucher_no ?></td>
                  <td class="valign-middle"><?php echo $voucher_date ?></td>
                  <td align="right" class="valign-middle"><?php echo number_format($voucher_amount,2,'.','') ?></td>
                  <td align="right" class="valign-middle">
                    <?php if((int)$consumed_invoice_no > 0): ?>
                      <i class="fa fa-external-link" aria-hidden="true"></i>&nbsp;
                      <a href="/sales/view-invoice/<?php echo $consumed_invoice_code ?>" target="_blank" class="hyperlink">
                        <?php echo $consumed_invoice_no ?>
                      </a>
                    <?php endif; ?>
                  </td>
                  <td align="right" class="valign-middle"><?php echo number_format($balance_value,2,'.','') ?></td>                  
                  <td class="valign-middle"><?php echo $location_name ?></td>
                  <td class="valign-middle">
                    <div class="btn-actions-group" align="right">
                      <?php if($user_type === 3 || $user_type === 9): ?>
                        <?php /*
                        <a class="btn btn-primary" href="/fin/credit-note/update/<?php echo $voucher_no ?>" title="Edit Voucher">
                          <i class="fa fa-pencil"></i>
                        </a> */ ?>
                        <a class="btn btn-danger delCNote" href="/fin/credit-note/delete/<?php echo $voucher_no ?>" title="Delete Voucher">
                          <i class="fa fa-times "></i>
                        </a>
                      <?php endif; ?>

                      <?php if( ($user_type>3 || $user_type<9) && $voucher_type === 'lo'): ?>
                        <?php /*
                        <a class="btn btn-primary" href="/fin/credit-note/update/<?php echo $voucher_no ?>" title="Edit Voucher">
                          <i class="fa fa-pencil"></i>
                        </a>*/ ?>
                        <a class="btn btn-danger delCNote" href="/fin/credit-note/delete/<?php echo $voucher_no ?>" title="Delete Voucher">
                          <i class="fa fa-times "></i>
                        </a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
            <?php
              $cntr++;
              endforeach; 
            ?>
              <tr class="text-bold">
                <td colspan="5" align="right">TOTALS</td>
                <td align="right"><?php echo number_format($total, 2, '.', '') ?></td>
                <td>&nbsp;</td>
                <td align="right"><?php echo number_format($balance_total, 2, '.', '') ?></td>
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
    <!-- Panel ends -->
  </div>
</div>

<?php /*
<!--a class="btn btn-danger delDoctor" href="javascrip:void(0)" title="Remove Doctor" sid="<?php echo $doctor_code ?>">
  <i class="fa fa-times"></i>
</a-->*/
