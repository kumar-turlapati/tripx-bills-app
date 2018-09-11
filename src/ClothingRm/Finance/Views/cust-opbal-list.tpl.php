<?php
  use Atawa\Utilities;
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/fin/cust-opbal/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> Create Opening Balance 
            </a> 
          </div>
        </div>
        <h2 class="hdg-reports text-center">List of Customers Opening Balances</h2>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th width="5%" class="text-center">Sno.</th>
                <th width="20%" class="text-center">Customer Name</th>
                <th width="10%" class="text-center">Bill No.</th>
                <th width="10%" class="text-center">Bill Date</th>
                <th width="10%" class="text-center">Credit Days</th>
                <th width="10%" class="text-center">Amount</th>
                <th width="10%" class="text-center">Status</span></th>
                <th width="10%" class="text-center">Opening date</th>
                <th width="15%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                if(count($customers)>0) {
                    $cntr = 1;
                    $total_balance = 0;
                    foreach($customers as $balance_details):
                      $cust_name = $balance_details['customerName'];
                      $amount = $balance_details['amount'];
                      $opbal_date = date("d-m-Y",strtotime($balance_details['openingDate']));
                      $opbal_code = $balance_details['openingCode'];
                      $bill_no = $balance_details['billNo'];
                      $bill_date = date("d-m-Y", strtotime($balance_details['billDate']));
                      $credit_days = $balance_details['creditDays'];
                      if($balance_details['action']==='c') {
                        $status = 'Credit';
                        $total_balance -= $amount;
                      } else {
                        $status = 'Debit';
                        $total_balance += $amount;
                      }
                  ?>
                    <tr class="text-right font12">
                      <td class="text-right valign-middle"><?php echo $cntr ?></td>
                      <td class="text-left valign-middle"><?php echo $cust_name ?></td>
                      <td class="text-left valign-middle"><?php echo $bill_no ?></td>
                      <td class="text-left valign-middle"><?php echo $bill_date ?></td>
                      <td class="text-left valign-middle"><?php echo $credit_days ?></td>
                      <td class="text-right valign-middle"><?php echo number_format($amount,2,'.','') ?></td>
                      <td class="text-center valign-middle"><?php echo $status ?></td>
                      <td class="text-right valign-middle"><?php echo $opbal_date ?></td>
                      <td>
                        <div class="btn-actions-group valign-middle">
                          <?php if($opbal_code !== ''): ?>
                            <a class="btn btn-primary" href="/fin/cust-opbal/update/<?php echo $opbal_code ?>" title="Edit Details">
                              <i class="fa fa-pencil"></i>
                            </a>
                            <a class="btn btn-danger removeCustOpbal" href="/fin/cust-opbal/remove/<?php echo $opbal_code ?>" title="Remove Opening Balance">
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
                  <tr>
                    <td colspan="5" class="text-right text-bold">Totals</td>
                    <td class="text-right text-bold"><?php echo number_format($total_balance,2,'.','') ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>                                        
                  </tr>
            <?php } else { ?>
                <tr>
                  <td colspan="9" style="color:red;font-weight:bold;text-align:center;">No records are available.</td>
                </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>