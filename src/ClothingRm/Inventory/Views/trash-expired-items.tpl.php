<?php
  use Atawa\Utilities;

  if(isset($template_vars) && is_array($template_vars)) {
    extract($template_vars); 
  }

  foreach($params as $key=>$value) {
    $query_params[] = "$key=$value";
  }

  $query_params = '?'.implode('&',$query_params);
  $pagination_url = '/inventory/trash-expired-items';   
?>

<!-- Basic form starts -->
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
        <?php elseif($page_success !== ''): ?>
          <div class="alert alert-success" role="alert">
            <strong>Success!</strong> <?php echo $page_success ?> 
          </div>
        <?php endif; ?>

        <!-- Filter form -->
        <div class="panel">
          <div class="panel-body">
          <div id="filters-form">
            <!-- Form starts -->
            <form class="form-validate form-horizontal" method="POST" id="expiryForm">
              <div class="form-group">
                <div class="col-sm-12 col-md-2 col-lg-1">Expired by</div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap m-bot15">
                    <select class="form-control" name="month" id="month">
                      <?php 
                        foreach($months as $key=>$value):
                          if($key==$def_month) {
                            $selected = "selected";
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
                  <div class="select-wrap m-bot15">
                    <select class="form-control" name="year" id="year">
                      <?php 
                        foreach($years as $key=>$value):
                          if($key==$def_year) {
                            $selected = "selected";
                          } else {
                            $selected = '';
                          }                          
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                    <button class="btn btn-success"><i class="fa fa-times"></i> Trash Below Items</button>
                    <button type="reset" class="btn btn-warning" onclick="javascript:resetFilter('/inventory/trash-expired-items')"><i class="fa fa-refresh"></i> Reset </button>
                </div>
              </div>
            </form>        
            <!-- Form ends -->
          </div>
          </div>
        </div><!--end of Filter form -->

        <?php if(count($items)>0): ?>

          <h2 class="hdg-reports text-center">List of Expired / Expiring Items as on <?php echo $month_name.', '.$def_year ?></h2>
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th width="5%" class="text-center">Sl.No.</th>
                  <th width="40%" class="text-left">Item Name</th>
                  <th width="5%" class="text-center">Units/<br />Pack</th>
                  <th width="5%" class="text-center">Batch No.</span></th>
                  <th width="5%" class="text-center">Expiry<br />Date</th>
                  <th width="5%" class="text-center">Available<br />Qty.</th>
                  <th width="10%" class="text-center">Item Rate<br />(in Rs.)</th>
                  <th width="10%" class="text-center">Amount<br />(in Rs.)</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  $cntr = $sl_no;
                  $tot_amount = 0;
                  foreach($items as $item_details):
                    $item_code = $item_details['itemCode'];
                    $item_name = $item_details['itemName'];
                    $upp = $item_details['upp'];
                    $batch_no = $item_details['batchNo'];

                    $opqty = $item_details['opQty'];
                    $oprate = $item_details['opPurRate'];
                    $oprateiv = $item_details['opPurIvat'];
                    $opvat = $item_details['opVatPercent'];
                    $opexpdate = $item_details['opExpDate'];

                    $pqty = $item_details['purchaseQty'];
                    $prate = $item_details['puPurRate'];
                    $pvat = $item_details['puVatPercent'];
                    $pexpdate = $item_details['puExpDate'];

                    $sqty = $item_details['soldQty'];
                    $srqty = $item_details['salesReturnQty'];
                    $adjqty = $item_details['adjQty'];                           

                    if((int)$pqty===0) {
                        if((int)$oprateiv===1) {
                            $clos_rate = $oprate;
                        } else {
                            $pamount = $oprate*$opqty;                
                            $taxamount = $pamount*$opvat/100;
                            $pamount += $taxamount;
                            $clos_rate = round($pamount/$opqty,2);                            
                        }
                        $tax_percent = $opvat;
                        $exp_date = $opexpdate;
                    } else {
                        $pamount = $prate*$pqty;
                        $taxamount = ($pamount*$pvat)/100;
                        $pamount += $taxamount;
                        
                        $clos_rate = round($pamount/($upp*$pqty),2);
                        $tax_percent = $pvat;
                        $exp_date = $pexpdate;
                    }

                    $closqty = ( ($opqty+($pqty*$upp) )-$sqty )+$srqty+($adjqty);
                    $amount = round($closqty*$clos_rate,2);
                    $tot_amount += $amount;                  
                ?>
                    <tr class="text-right font12">
                      <td><?php echo $cntr ?></td>
                      <td class="text-left"><?php echo $item_name ?></td>
                      <td class="text-right"><?php echo $upp ?></td>
                      <td class="text-bold"><?php echo $batch_no ?></td>
                      <td class="text-right"><?php echo $exp_date ?></td>
                      <td class="text-right"><?php echo $closqty ?></td>
                      <td class="text-right"><?php echo number_format($clos_rate,2) ?></td>
                      <td class="text-right"><?php echo number_format($amount,2) ?></td>
                    </tr>
              <?php
                $cntr++;
                endforeach; 
              ?>
              <tr>
                <td colspan="7" class="text-right text-bold">PAGE TOTALS</td>
                <td class="text-right text-bold"><?php echo number_format($tot_amount,2) ?></td>
              </tr>
              </tbody>
            </table>
            <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
        </div>

      <?php endif; ?>

    </section>
    <!-- Panel ends -->
  </div>
</div>