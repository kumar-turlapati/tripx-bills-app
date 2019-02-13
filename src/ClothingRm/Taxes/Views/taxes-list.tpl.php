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
            <a href="/taxes/add" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Tax Rate 
            </a> 
          </div>
        </div>
        <?php /*<h2 class="hdg-reports text-center">Available Tax Rates</h2> */ ?>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="font14">
              <tr>
                <th width="5%" class="text-center">Sno.</th>
                <th width="50%" class="text-center">Tax Name</th>
                <th width="10%" class="text-center">Tax Percent</th>
                <th width="10%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php if(count($taxes)>0) { ?>
              <?php 
                $cntr = 1;
                foreach($taxes as $tax_details):
                  $tax_code = $tax_details['taxCode'];
                  $tax_percent = $tax_details['taxPercent'];
                  $tax_name = $tax_details['taxLabel'];
                  if((int)$tax_details['isCompound']===1) {
                    $is_compound = 'Yes';
                  } else {
                    $is_compound = 'No';
                  }
              ?>
                  <tr class="text-right font11">
                    <td class="text-right valign-middle"><?php echo $cntr ?></td>
                    <td class="text-left valign-middle"><?php echo $tax_name ?></td>
                    <td class="text-right valign-middle"><?php echo $tax_percent.' %' ?></td>
                    <td class="valign-middle">
                      <div class="btn-actions-group">
                        <?php if($tax_code !== ''): ?>
                          <a class="btn btn-primary" href="/taxes/update/<?php echo $tax_code ?>" title="Edit Taxes">
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
                <td colspan="4" align="center"><b>Tax rates are not yet configured...</b></td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>