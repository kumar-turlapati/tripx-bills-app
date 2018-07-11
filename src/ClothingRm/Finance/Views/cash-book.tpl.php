<?php
  use Atawa\Utilities;
  $page_url = '/fin/cash-book';  
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

        <!--Filters block -->
        <div class="filters-block">
          <div id="filters-form">
            <!-- Form starts -->
            <form class="form-validate form-horizontal" method="POST" action="<?php echo $page_url ?>">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1 text-right">
                  <label class="control-label text-right"><b>Filter by</b></label>          
                </div>                
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
                <div class="col-sm-12 col-md-3 col-lg-3">                         
                  <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
                </div>
              </div>
            </form>    
          </div>
        </div>

        <!--Data to display -->
        <div class="table-responsive">
          <table class="table table-striped table-hover font12">
            <thead>
              <tr>
                <th width="5%" class="text-center">Sno.</th>
                <th width="8%" class="text-center">Voucher No.</th>
                <th width="10%" class="text-center">Voucher Date</th>
                <th width="47%" class="text-center">Description</th>
                <th width="10%" class="text-center">Receipts<br />( Debits )</th>
                <th width="10%" class="text-center">Payments<br />( Credits )</th>
                <th width="10%" class="text-center">Balance</th>
              </tr>
            </thead>
            <tbody>
              <?php
                if(count($records)>0) {
                    $cntr = 1;
                    $totals = $total_debits = $total_credits = 0;
                    foreach($records as $record_details):
                      $party_code = $record_details['partyCode'];
                      $voc_date = date("d-m-Y",strtotime($record_details['voucherDate']));
                      $voc_no = $record_details['voucherNo'];
                      $tran_type = $record_details['voucherType'];
                      if((int)$record_details['action']===1 || $record_details['action']==='c') {
                        $credits = $record_details['amount'];
                        $debits = 0;
                        $totals += $credits;
                        $total_credits += $credits;
                      } else {
                        $debits = $record_details['amount'];
                        $totals -= $debits;
                        $credits = 0;
                        $total_debits += $debits;
                      }

                      if($record_details['billNo'] !== '') {
                        $description = $record_details['billNo'];
                      } else {
                        $description = '';
                      }

                      if($record_details['partyName'] !== '' && $description !== '') {
                        $description .= ', '.$record_details['partyName'];
                      } else {
                        $description = $record_details['partyName'];
                      }
                  ?>
                    <tr class="text-right font12">
                      <td class="text-right"><?php echo $cntr ?></td>
                      <td class="text-left"><?php echo $voc_no>0?$voc_no:'' ?></td>
                      <td class="text-center"><?php echo $voc_date ?></td>
                      <td class="text-left"><?php echo $description ?></td>
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
                      <td colspan="4" class="text-right">TOTALS</td>
                      <td class="text-right"><?php echo number_format($total_debits, 2) ?></td>
                      <td class="text-right"><?php echo number_format($total_credits, 2) ?></td>
                      <td class="text-right">&nbsp;</td>
                    </tr>
                    <tr class="text-bold">
                      <td colspan="4" class="text-right">BALANCE</td>
                      <td class="text-right"><?php echo $debit_balance>0?number_format($debit_balance, 2):'' ?></td>
                      <td class="text-right"><?php echo $credit_balance>0?number_format($credit_balance, 2):'' ?></td>
                      <td class="text-right">&nbsp;</td>
                    </tr>
            <?php } else { ?>
                <tr>
                  <td colspan="7">No Transactions are available.</td>
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