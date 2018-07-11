<?php
  use Atawa\Utilities;  
  if(isset($submitted_data['itemName']) && $submitted_data['itemName'] !='') {
    $medName = $submitted_data['itemName'];
  } else {
    $medName = '';
  } 
?>

<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12">
    
    <!-- Panel starts -->
    <section class="panel">
      <div class="panel-body">

        <?php echo Utilities::print_flash_message() ?>

        <div class="filters-block">
          <div id="filters-form">
            <!-- Form starts -->
            <form class="form-validate form-horizontal" method="POST">
              <div class="form-group">             
                <div class="col-sm-12 col-md-6 col-lg-6">
                  <input 
                    type="text" 
                    name="itemName" 
                    id="itemName" 
                    class="form-control inameAc" 
                    placeholder="Type product name..."
                  />
                </div>
                <div class="form-group text-right">
                  <div class="col-sm-12 col-md-3 col-lg-3"> 
                    <button class="btn btn-success">
                      <i class="fa fa-search"></i> Track Item
                    </button>
                    <button type="reset" class="btn btn-warning" onclick="javascript:resetFilter('/inventory/track-item')">
                      <i class="fa fa-refresh"></i> Reset 
                    </button>
                  </div>
                </div>
              </div>           
            </form>
            <!-- Form ends -->
          </div>
        </div>

        <?php if(count($total_trans)>0): ?>
          <div class="table-responsive">
            <?php 
              // echo '<pre>';
              // var_dump($total_trans);
              // echo '</pre>';
              // exit;
            ?>
            <div class="table-responsive">
              <table class="table table-bordered table-hover font12" id="itemTrack">
                <thead>
                  <tr>
                    <th width="5%" class="text-center">Sno.</th>
                    <th width="9%" class="text-center">Transaction<br />Date</th>
                    <?php /*<th width="8%" class="text-center">Batch<br />No.</th> */ ?>
                    <th width="7%" class="text-center">Opening<br />Qty.</th>
                    <th width="7%" class="text-center">Purchased<br />Qty.</th>
                    <th width="7%" class="text-center">Sold<br />Qty.</th>
                    <th width="8%" class="text-center">Sales Return<br />Qty.</th>
                    <th width="7%" class="text-center">Adjustment<br />Qty.</th>
                    <th width="7%" class="text-center">Closing<br />Qty.</th>                    
                    <th width="10%" class="text-center">Transaction<br />Reference</th>                    
                    <th width="10%" class="text-center">Item Rate<br />(in Rs.)</th>
                    <th width="10%" class="text-center">Amount<br />(in Rs.)</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $slno=0;
                    foreach($total_trans as $batchwise_details):
                      $clos_qty=$total_amount=0;
                      $all_op_qty = $all_sa_qty = $all_sr_qty = $all_pu_qty = $all_aj_qty = $others = 0;
                      $op_slno = $sa_slno = $sr_slno = $pu_slno = $aj_slno = 0;
                      foreach($batchwise_details as $tran_details):
                        $tran_date = date("d-M-Y", strtotime($tran_details['tranDate']));
                        $slno++;
                        switch($tran_details['tranType']) {
                          case 'OP':
                            $op_qty = $tran_details['finalQty'];
                            $sa_qty = $sr_qty = $pu_qty = $aj_qty = 0;
                            $clos_qty = $clos_qty + $op_qty;
                            $all_op_qty = $all_op_qty+$op_qty;
                            $op_slno++;
                            break;
                          case 'SA':
                            $sa_qty = $tran_details['finalQty'];
                            $all_sa_qty = $all_sa_qty+$sa_qty;
                            $op_qty = $sr_qty = $pu_qty = $aj_qty = 0;
                            $clos_qty = $clos_qty-$sa_qty;
                            $sa_slno++;
                            break;
                          case 'SR':
                            $sr_slno++;
                            $sr_qty = $tran_details['finalQty'];
                            $all_sr_qty = $all_sr_qty+$sr_qty;
                            $op_qty = $sa_qty = $pu_qty = $aj_qty = 0;
                            $clos_qty = $clos_qty + $sr_qty;
                            break;
                          case 'PU':
                            $pu_slno++;
                            $pu_qty = $tran_details['finalQty'];
                            $all_pu_qty = $all_pu_qty+$pu_qty;
                            $op_qty = $sr_qty = $sa_qty = $aj_qty = 0;
                            $clos_qty = $clos_qty + $pu_qty;
                            break;
                          case 'AJ':
                            $aj_slno++;
                            $aj_qty = $tran_details['finalQty'];
                            $all_aj_qty = $all_aj_qty+$aj_qty;
                            $op_qty = $sr_qty = $pu_qty = $sa_qty = 0;
                            $clos_qty = $clos_qty + $aj_qty;
                            break;
                        }

                        $item_rate = $tran_details['finalRate'];
                        // $batch_no = $tran_details['batchNo'];
                        $ref_no = $tran_details['refNumber'];
                        $amount = round($item_rate*$tran_details['finalQty'],2);
                        $total_amount += $amount;
                    ?>
                      <tr class="text-right font12">
                        <td><?php echo $slno ?></td>
                        <td class="text-left"><?php echo $tran_date ?></td>
                        <?php /*<td class="text-right"><?php echo $batch_no ?></td> */?>
                        <td class="text-right"><?php echo ($op_qty<>''?$op_qty:'') ?></td>
                        <td class="text-right"><?php echo ($pu_qty<>''?$pu_qty:'') ?></td>
                        <td class="text-right"><?php echo ($sa_qty<>''?$sa_qty:'') ?></td>
                        <td class="text-right"><?php echo ($sr_qty<>''?$sr_qty:'') ?></td>
                        <td class="text-right"><?php echo ($aj_qty<>''?$aj_qty:'') ?></td>
                        <td class="text-right text-bold green"><?php echo $clos_qty ?></td>                      
                        <td class="text-right"><?php echo $ref_no ?></td>
                        <td class="text-right"><?php echo number_format($item_rate,2) ?></td>
                        <td class="text-right"><?php echo number_format($amount,2) ?></td>
                      </tr>
                    <?php endforeach; ?>
                      <tr>
                        <td colspan="12" class="tdseperator"></td>
                      </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

          </div>
        <?php endif;?>
      </div>
    </section>
    <!-- Panel ends -->
  </div>
</div>

<?php /*
                    // dump($all_op_qty,$all_sa_qty, $all_pu_qty, $all_sr_qty, $all_aj_qty);
                    // dump($op_slno,$sa_slno,$pu_slno,$sr_slno,$aj_slno, $others);
                    // exit; */?>
