<?php

  use Atawa\Utilities;
  use Atawa\Constants;
  
  $current_date = date("d-m-Y");
  $user_type = (int)$_SESSION['utype'];

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
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = '';
  }

  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }

  $page_url = '/fin/debit-notes';
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/inward-entry/list" class="btn btn-default">
              <i class="fa fa-book"></i> Purchase Register
            </a>&nbsp;
            <a href="/purchase-return/register" class="btn btn-default">
              <i class="fa fa-book"></i> Purchase Return Register
            </a>&nbsp;
            <a href="/fin/debit-note/create" class="btn btn-default">
              <i class="fa fa-share"></i> Create debit note
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
          <?php if(count($dnotes)>0): ?>
             <table class="table table-striped table-hover">
              <thead>
                <tr class="font11">
                  <th width="5%" class="text-center">Sno.</th>
                  <th width="10%" class="text-center">Purchase Date</th>
                  <th width="10%" class="text-center">Bill No.</th>                
                  <th width="10%" class="text-center">Bill Value (Rs.)</th>
                  <th width="10%" class="text-center">Debit Note No.</th>
                  <th width="10%" class="text-center">Debit Note Date</th>
                  <th width="10%" class="text-center">Amount (Rs.)</span></th>
                  <th width="10%" class="text-center">Debit Type</span></th>                
                  <th width="15%" class="text-center">Store Name</th>
                  <th width="15%" class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  $cntr = $sl_no;
                  $total = 0;
                  $tot_bill_value = 0;
                  foreach($dnotes as $voucher_details):
                    $voucher_no = $voucher_details['dnNo'];
                    $voucher_code = $voucher_details['dnCode'];
                    $voucher_date = date('d-M-Y', strtotime($voucher_details['dnDate']));
                    $location_id = $voucher_details['locationID'];
                    $location_name = isset($location_ids[$location_id]) ?  $location_ids[$location_id] : 'Invalid';
                    $voucher_amount = $voucher_details['dnValue'];
                    $bill_no = $voucher_details['billNo'] !== '' ? $voucher_details['billNo'] : '-';
                    $bill_value = $voucher_details['billValue'] > 0 ? $voucher_details['billValue'] : 0;
                    $purchase_date = !is_null($voucher_details['purchaseDate']) ? date('d-M-Y', strtotime($voucher_details['purchaseDate'])) : '-';
                    if($voucher_details['dnType'] === 'w' || $voucher_details['dnType'] === 'wo') {
                      $voucher_type = $voucher_details['dnType'] === 'w' ? 'With items' : 'W/o Items';
                    } elseif($voucher_details['dnType'] === 'ma') {
                      $voucher_type = 'Pur.Return';
                    } else {
                      $voucher_type = 'Auto';
                    }
                    $purchase_code = $voucher_details['purchaseCode'];
                    $grn_code = '';
                    $return_code = '';                    
                    $total += $voucher_amount;
                    $tot_bill_value += $bill_value;
                ?>
                  <tr class="font11">
                    <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                    <td align="center" class="valign-middle">
                      <a href="/inward-entry/view/<?php echo $purchase_code ?>" class="hyperlink" title="View inward entry" target="_blank">
                        <?php echo $purchase_date ?>
                      </a>
                    </td>
                    <td align="left" class="valign-middle">
                      <?php echo $bill_no ?>
                    </td>                
                    <td align="right" class="valign-middle">
                      <?php echo $bill_value > 0 ? number_format($bill_value, 2, '.', '') : '-' ?>
                    </td>
                    <td align="right" class="valign-middle">
                      <?php if($return_code !== ''): ?>
                      <a href="/purchase-return/view/<?php echo $return_code ?>" class="hyperlink" title="View Debit Note" target="_blank">
                        <?php echo $voucher_no ?>
                      </a>
                      <?php else: ?>
                        <?php echo $voucher_no ?>
                      <?php endif; ?>
                    </td>
                    <td align="right" class="valign-middle"><?php echo $voucher_date ?></td>
                    <td align="right" class="valign-middle"><?php echo number_format($voucher_amount,2, '.', '') ?></td>
                    <td align="center" class="valign-middle"><?php echo $voucher_type ?></td>                  
                    <td class="valign-middle"><?php echo $location_name ?></td>
                    <td>
                      <div class="btn-actions-group" align="center">
                        <a class="btn btn-primary" href="/fin/debit-note/view/<?php echo $voucher_code ?>" title="View debit note">
                          <i class="fa fa-eye"></i>
                        </a>                        
                        <?php if($user_type === 3 || $user_type === 9 || $user_type === 7): ?>
                          <a class="btn btn-danger delDNote" href="/fin/debit-note/delete/<?php echo $voucher_code ?>" title="Delete Voucher">
                            <i class="fa fa-times"></i>
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
                  <td colspan="3" align="right">TOTALS</td>
                  <td align="right"><?php echo number_format($tot_bill_value, 2, '.', '') ?></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td align="right"><?php echo number_format($total, 2, '.', '') ?></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>                  
                </tr>
              </tbody>
            </table>
          <?php else: ?>
            <div style="text-align:center;margin-top:10px;font-weight:bold;color:red;font-size:14px;border:1px dotted;">There are no debit notes available in the given criteria. Please change search filters!</div>
          <?php endif; ?>
          <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
        </div>
      </div>
    </section>
  </div>
</div>