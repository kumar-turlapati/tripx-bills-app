<?php
  use Atawa\Utilities;
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
            <a href="/fin/supp-opbal/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> Create Opening Balance 
            </a> 
          </div>
        </div>
        <!-- Right links ends --> 
        
        <h2 class="hdg-reports text-center">List of Supplier's Opening Balances</h2>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th width="5%" class="text-center">Sno.</th>
                <th width="50%" class="text-center">Supplier Name</th>
                <th width="10%" class="text-center">Amount</th>
                <th width="10%" class="text-center">Status</span></th>
                <th width="10%">Opening date</th>
                <th width="15%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                if(count($balances)>0) {
                    $cntr = 1;
                    $total_balance = 0;
                    foreach($balances as $balance_details):
                      $supp_name = $balance_details['supplierName'];
                      $amount = $balance_details['amount'];
                      $opbal_date = date("d-m-Y",strtotime($balance_details['openDate']));
                      $opbal_code = $balance_details['suppOpeningCode'];
                      if($balance_details['action']==='c') {
                        $status = 'Credit';
                        $total_balance += $amount;
                      } else {
                        $status = 'Debit';
                        $total_balance -= $amount;
                      }
                  ?>
                    <tr class="text-right font12">
                      <td class="text-right"><?php echo $cntr ?></td>
                      <td class="text-left"><?php echo $supp_name ?></td>
                      <td class="text-right"><?php echo number_format($amount,2) ?></td>
                      <td class="text-center"><?php echo $status ?></td>
                      <td class="text-right"><?php echo $opbal_date ?></td>
                      <td>
                        <div class="btn-actions-group">
                          <?php if($opbal_code !== ''): ?>
                            <a class="btn btn-primary" href="/fin/supp-opbal/update/<?php echo $opbal_code ?>" title="Edit Details">
                              <i class="fa fa-pencil"></i>
                            </a>
                            <a class="btn btn-danger" href="#" title="Remove Opening Balance">
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
                    <td colspan="2" class="text-right text-bold">Totals</td>
                    <td class="text-right text-bold"><?php echo number_format($total_balance,2) ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>                                        
                  </tr>
            <?php } else { ?>
                <tr>
                  <td colspan="6">No opening balances are available.</td>
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