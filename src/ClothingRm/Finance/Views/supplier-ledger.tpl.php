<?php
  use Atawa\Utilities;
  $page_url = '/fin/supplier-ledger';
?>
<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12">
    
    <!-- Panel starts -->
    <section class="panelBox">
      <div class="panelBody">

        <?php echo Utilities::print_flash_message() ?>

        <!-- Right links starts -->
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/fin/supp-outstanding-ason" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> Payables ason Date
            </a>
            <a href="/suppliers/list" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> Supplier's List
            </a>            
          </div>
        </div>
        <!-- Right links ends -->
        <div class="filters-block">
          <div id="filters-form">
            <!-- Form starts -->
            <form class="form-validate form-horizontal" method="POST" action="<?php echo $page_url ?>">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1 text-right">
                  <label class="control-label text-right"><b>Filter by</b></label>          
                </div>                
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="suppCode" id="suppCode">
                      <?php 
                        foreach($suppliers as $key=>$value): 
                          if($sel_supp_id === $key) {
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
                <div class="col-sm-12 col-md-3 col-lg-3">                         
                  <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
                </div>
              </div>
            </form>    
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font12">
            <thead>
              <tr>
                <th width="5%" class="text-center">Sno.</th>
                <th width="8%" class="text-center">Voucher<br />no.</th>
                <th width="8%" class="text-center">Voucher<br />date</th>
                <th width="8%" class="text-center">Transaction<br />type</th>
                <th width="30%" class="text-center">Narration</th>
                <th width="8%" class="text-center">Debits<br />( in Rs. )</th>
                <th width="8%" class="text-center">Credits<br />( in Rs. )</th>
                <th width="10%" class="text-center">Balance<br />( in Rs. )</th>
              </tr>
            </thead>
            <tbody>
              <?php
                if(count($records)>0) {
                    $cntr = 1;
                    $totals = $total_debits = $total_credits = 0;
                    foreach($records as $record_details):
                      $tran_date = date("d-m-Y",strtotime($record_details['tranDate']));
                      $tran_no = $record_details['tranNo'];
                      $tran_code = $record_details['tranCode'];
                      $bill_no = $record_details['billNo'];
                      $tran_type = $record_details['tranType'];
                      if((int)$record_details['action']===1 || $record_details['action']==='c') {
                        $credits = $record_details['tranAmount'];
                        $debits = 0;
                        $totals += $credits;
                        $total_credits += $credits;
                      } else {
                        $debits = $record_details['tranAmount'];
                        $totals -= $debits;
                        $credits = 0;
                        $total_debits += $debits;
                      }
                      $narration = '';
                  ?>
                    <tr class="text-right font12">
                      <td align="center"><?php echo $cntr ?></td>
                      <td class="text-right"><?php echo $tran_no>0?$tran_no:'' ?></td>
                      <td class="text-right"><?php echo $tran_date ?></td>
                      <td class="text-left"><?php echo strtolower($tran_type) ?></td>
                      <td class="text-left"><?php echo $narration ?></td>
                      <td class="text-right"><?php echo $debits>0?number_format($debits,2):'' ?></td>
                      <td class="text-right"><?php echo $credits>0?number_format($credits,2) :'' ?></td>
                      <td class="text-right"><?php echo $totals>0?number_format($totals,2):'' ?></td>
                    </tr>
                <?php
                  $cntr++;
                  endforeach;
                  $balance = $total_debits - $total_credits;
                  if($balance<0) {
                    $credit_balance = $balance*-1;
                    $debit_balance = 0;
                  } else {
                    $debit_balance = $balance;
                    $credit_balance = 0;
                  }
                ?>
                    <tr>
                      <td colspan="5" class="text-right">TOTALS</td>
                      <td class="text-right"><?php echo number_format($total_debits, 2) ?></td>
                      <td class="text-right"><?php echo number_format($total_credits, 2) ?></td>
                      <td class="text-right">&nbsp;</td>
                    </tr>
                    <tr class="text-bold">
                      <td colspan="5" class="text-right">BALANCE</td>
                      <td class="text-right"><?php echo $debit_balance>0?number_format($debit_balance, 2):'' ?></td>
                      <td class="text-right"><?php echo $credit_balance>0?number_format($credit_balance, 2):'' ?></td>
                      <td class="text-right">&nbsp;</td>
                    </tr>
            <?php } else { ?>
                <tr>
                  <td colspan="8" style="text-align:center;">No Transactions are available.</td>
                </tr>
            <?php } ?>
            </tbody>
          </table>

        </div>
      </div>
    </section>
    <!-- Panel ends -->
  </div>
</div>