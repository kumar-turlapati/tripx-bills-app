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
            <a href="/taxes/add" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Tax Rate 
            </a> 
          </div>
        </div>
        <!-- Right links ends --> 
        
        <h2 class="hdg-reports text-center">Available Tax Rates</h2>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th width="5%" class="text-center">Sno.</th>
                <th width="50%">Tax name</th>
                <th width="10%">Tax percent</th>
                <th width="10%">Is compound?</span></th>
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
                  <tr class="text-right font12">
                    <td align="center"><?php echo $cntr ?></td>
                    <td class="text-left"><?php echo $tax_name ?></td>
                    <td class="text-right"><?php echo $tax_percent.' %' ?></td>
                    <td class="text-bold"><?php echo $is_compound ?></td>
                    <td>
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
                <td colspan="5" align="center"><b>Tax rates are not yet configured...</b></td>
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