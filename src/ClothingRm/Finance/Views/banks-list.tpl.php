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
            <a href="/fin/bank/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> Add New Bank 
            </a> 
          </div>
        </div>
        <h2 class="hdg-reports text-center">List of all Banks</h2>
        <div class="table-responsive">
          <table class="table table-striped table-hover font12">
            <thead>
              <tr>
                <th width="5%" class="text-center valign-middle">Sno.</th>
                <th width="30%" class="valign-middle">Bank Name</th>
                <th width="25%" class="valign-middle">Account Name</th>
                <th width="10%" class="valign-middle">Account No.</span></th>
                <th width="10%" class="valign-middle">IFSC Code</th>
                <th width="10%" class="valign-middle">Phone</th>
                <th width="10%" class="text-center valign-middle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                if(count($banks)>0) {
                    $cntr = 1;
                    foreach($banks as $bank_details):
                      $bank_name = $bank_details['bankName'];
                      $account_name = $bank_details['accountName'];
                      $ifsc_code = $bank_details['ifscCode'];
                      $account_no = $bank_details['accountNo'];
                      $phone = $bank_details['phone'];
                      $bank_code = $bank_details['bankCode'];
                  ?>
                    <tr class="text-right font12">
                      <td align="center" class="valign-middle"><?php echo $cntr ?></td>
                      <td class="text-left valign-middle"><?php echo $bank_name ?></td>
                      <td class="text-left valign-middle"><?php echo $account_name ?></td>
                      <td class="text-bold valign-middle"><?php echo $account_no ?></td>
                      <td class="text-left valign-middle"><?php echo $ifsc_code ?></td>
                      <td class="text-left valign-middle"><?php echo $phone ?></td>
                      <td>
                        <div class="btn-actions-group valign-middle">
                          <?php if($bank_code !== ''): ?>
                            <a class="btn btn-primary" href="/fin/bank/update/<?php echo $bank_code ?>" title="Edit Bank Details">
                              <i class="fa fa-pencil"></i>
                            </a>                          
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                <?php
                  $cntr++;
                  endforeach; 
                ?>
            <?php } else { ?>
                <tr>
                  <td colspan="7" style="text-align:center;">No Banks are available.</td>
                </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>