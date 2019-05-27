<?php
  use Atawa\Utilities;  
  $item_name = isset($form_data['itemName']) && $form_data['itemName'] !== '' ? $form_data['itemName'] : '';
  $location_code = isset($form_data['locationCode']) ? $form_data['locationCode'] : $default_location;

  $query_params = '';
  if($location_code !== '') {
    $query_params[] = 'locationCode='.$location_code;
  }  
  if($item_name !== '' ) {
    $query_params[] = 'itemName='.$item_name;
  }
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = $pagination_url = '/inventory/track-item';
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message(); ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
              <a href="/products/create" class="btn btn-default">
                <i class="fa fa-file-text-o"></i> New Product / Service
              </a>
          </div>
        </div>
        <div class="filters-block">
          <div id="filters-form">
            <form class="form-validate form-horizontal" method="GET" action="<?php echo $page_url ?>">
              <div class="form-group">
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($client_locations as $key=>$value):
                          if($location_code === $key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <?php if(isset($form_errors['locationCode'])): ?>
                    <span class="error"><?php echo $form_errors['locationCode'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <input placeholder="Item name" type="text" name="itemName" id="itemName" class="form-control inameAc" value="<?php echo $item_name ?>">
                  <?php if(isset($form_errors['itemName'])): ?>
                    <span class="error"><?php echo $form_errors['itemName'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <div class="container-fluid">
                    <button class="btn btn-success">
                      <i class="fa fa-file-text"></i> Get Track
                    </button>
                    <button type="reset" class="btn btn-warning" onclick="javascript:resetFilter(<?php echo (isset($page_url) && $page_url != '' ? "'".$page_url."'" : '#') ?>)">
                      <i class="fa fa-refresh"></i> Reset
                    </button>
                  </div>
                </div>
              </div>
            </form>        
          </div>
        </div>
        <?php if(count($track_a) > 0): ?>
          <div class="table-responsive">
            <table class="table table-bordered table-hover font12" id="itemTrack">
              <thead>
                <tr>
                  <th width="5%" class="text-center">Sno.</th>
                  <th width="9%" class="text-center">Transaction<br />Date</th>
                  <th width="8%" class="text-center">Lot No.</th>
                  <th width="6%" class="text-center">CASE/<br />BOX No.</th>
                  <th width="7%" class="text-center">Opening<br />Qty.</th>
                  <th width="7%" class="text-center">Purchased<br />Qty.</th>
                  <th width="8%" class="text-center">Sales Return<br />Qty.</th>
                  <th width="7%" class="text-center">Sold<br />Qty.</th>
                  <th width="7%" class="text-center">Purchase Return<br />Qty.</th>
                  <th width="7%" class="text-center">Adjustment<br />Qty.</th>
                  <th width="7%" class="text-center">Transfer<br />Qty.</th>
                  <th width="7%" class="text-center">Closing<br />Qty.</th>                    
                  <th width="10%" class="text-center">Transaction<br />Reference</th>                    
                  <th width="10%" class="text-center">Item Rate<br />(in Rs.)</th>
                  <th width="10%" class="text-center">Amount<br />(in Rs.)</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $slno_page = $sl_no;
                  $clos_qty = $total_amount = 0;
                  $all_op_qty = $all_pur_qty = $all_sr_qty = $all_sa_qty = $all_pr_qty = $all_adj_qty = $all_st_qty = 0;
                
                  if( isset($_SESSION['trackItemClosing']) && $current_page > 1):
                    $balance_forwarded_qty = $_SESSION['trackItemClosing'];
                    unset($_SESSION['trackItemClosing']);
                    $op_qty = $balance_forwarded_qty;
                    $clos_qty += $op_qty;
                    $all_op_qty += $op_qty;
                ?>
                  <tr class="text-right font11">
                    <td colspan="3">Balance C/F</td>
                    <td class="text-right"><?php echo number_format($balance_forwarded_qty, 2, '.', '') ?></td>
                    <td class="text-right">&nbsp;</td>
                    <td class="text-right">&nbsp;</td>
                    <td class="text-right">&nbsp;</td>
                    <td class="text-right">&nbsp;</td>
                    <td class="text-right">&nbsp;</td>
                    <td class="text-right">&nbsp;</td>
                    <td class="text-right">&nbsp;</td>
                    <td class="text-right text-bold green font14"></td>                      
                    <td class="text-right">&nbsp;</td>
                    <td class="text-right">&nbsp;</td>
                    <td class="text-right">&nbsp;</td>
                  </tr>
                <?php endif; ?>

                <?php
                  foreach($track_a as $tran_details):
                    $redirect_url = '';
                    $tran_date = date("d-M-Y", strtotime($tran_details['tranDate']));
                    $pur_qty = $sr_qty = $sa_qty = $pr_qty = $adj_qty = $st_qty = $op_qty = 0;
                    switch($tran_details['tranType']) {
                      case 'OP':
                        $op_qty = $tran_details['itemQty'];

                        $clos_qty += $op_qty;
                        $all_op_qty += $op_qty;
                        $redirect_url = '/opbal/update/'.$tran_details['autoCode'];
                        break;
                      case 'PU':
                        $pur_qty = $tran_details['itemQty'];

                        $all_pur_qty += $pur_qty;
                        $clos_qty += $pur_qty;
                        $redirect_url = '/inward-entry/view/'.$tran_details['autoCode'];
                        break;                          
                      case 'SR':
                        $sr_qty = $tran_details['itemQty'];

                        $all_sr_qty += $sr_qty;
                        $clos_qty += $sr_qty;
                        $redirect_url = '';
                        break;                          
                      case 'SA':
                        $sa_qty = $tran_details['itemQty'];

                        $all_sa_qty += $sa_qty;
                        $clos_qty -= $sa_qty;
                        $redirect_url = '/sales/view-invoice/'.$tran_details['autoCode'];
                        break;
                      case 'PR':
                        $pr_qty = $tran_details['itemQty'];
                        $redirect_url = '/purchase-return/view/'.$tran_details['autoCode'];

                        $all_pr_qty += $pr_qty;
                        $clos_qty -= $pr_qty;
                        break;                          
                      case 'AJ':
                        $adj_qty = $tran_details['itemQty'];

                        $all_adj_qty += $adj_qty;
                        $clos_qty += $adj_qty;
                        break;
                      case 'TR':
                        $st_qty = $tran_details['itemQty'];
                        $redirect_url = '/stock-transfer/out/'.$tran_details['autoCode'];
                        
                        $all_st_qty += $st_qty;
                        $clos_qty += $st_qty;            
                        break;
                    }

                    $item_rate = $tran_details['itemRate'];
                    $lot_no = $tran_details['lotNo'];
                    $cno = $tran_details['cno'];
                    $ref_no = $tran_details['refNumber'];
                    $amount = round($item_rate*$tran_details['itemQty'],2);
                    $total_amount += $amount;
                  ?>
                    <tr class="text-right font11">
                      <td><?php echo $slno_page ?></td>
                      <td class="text-left"><?php echo $tran_date ?></td>
                      <td class="text-left"><?php echo $lot_no ?></td>                      
                      <td class="text-right"><?php echo $cno ?></td>                      
                      <td class="text-right"><?php echo $op_qty > 0 ? number_format($op_qty, 2, '.', '') : '' ?></td>
                      <td class="text-right"><?php echo $pur_qty > 0 ? number_format($pur_qty, 2, '.', '') : '' ?></td>
                      <td class="text-right"><?php echo $sr_qty > 0 ? number_format($sr_qty, 2, '.', '') : '' ?></td>
                      <td class="text-right"><?php echo $sa_qty > 0 ? number_format($sa_qty, 2, '.', '') : '' ?></td>
                      <td class="text-right"><?php echo $pr_qty > 0 ? number_format($pr_qty, 2, '.', '') : '' ?></td>
                      <td class="text-right"><?php echo is_numeric($adj_qty) && $adj_qty !== 0 ? number_format($adj_qty, 2, '.', '') : '' ?></td>
                      <td class="text-right"><?php echo is_numeric($st_qty) ? number_format($st_qty, 2, '.', '') : '' ?></td>
                      <td class="text-right text-bold green font14"><?php echo number_format($clos_qty, 2, '.', '') ?></td>                      
                      <td class="text-right">
                        <?php if($redirect_url !== ''): ?>
                          <a href="<?php echo $redirect_url ?>" style="color:#225992; font-weight:bold; font-size:12px;" title="View Voucher" target="_blank"><?php echo $ref_no ?> <i class="fa fa-external-link" aria-hidden="true"></i> </a>
                        <?php else: ?>
                          <?php echo $ref_no ?>
                        <?php endif; ?>
                      </td>
                      <td class="text-right"><?php echo number_format($item_rate,2,'.','') ?></td>
                      <td class="text-right"><?php echo number_format($amount,2,'.','') ?></td>
                    </tr>
                <?php 
                  $slno_page++;
                  endforeach;
                  $_SESSION['trackItemClosing'] = $clos_qty;
                ?>
                  <tr class="text-right font14">
                    <td colspan="4" class="text-bold">T O T A L S</td>
                    <td class="text-right text-bold"><?php echo $all_op_qty > 0 ? number_format($all_op_qty, 2, '.', '') : '' ?></td>
                    <td class="text-right text-bold"><?php echo $all_pur_qty > 0 ? number_format($all_pur_qty, 2, '.', '') : '' ?></td>
                    <td class="text-right text-bold"><?php echo $all_sr_qty > 0 ? number_format($all_sr_qty, 2, '.', '') : '' ?></td>
                    <td class="text-right text-bold"><?php echo $all_sa_qty > 0 ? number_format($all_sa_qty, 2, '.', '') : '' ?></td>
                    <td class="text-right text-bold"><?php echo $all_pr_qty > 0 ? number_format($all_pr_qty, 2, '.', '') : '' ?></td>
                    <td class="text-right text-bold"><?php echo is_numeric($all_adj_qty) ? number_format($all_adj_qty, 2, '.', '') : '' ?></td>
                    <td class="text-right text-bold"><?php echo is_numeric($all_st_qty) ? number_format($all_st_qty, 2, '.', '') : '' ?></td>
                  </tr>
              </tbody>
            </table>
            <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>