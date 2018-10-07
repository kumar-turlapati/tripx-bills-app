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
            <a href="/finy/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Financial Year 
            </a>&nbsp;&nbsp;
            <a href="/finy-slnos/list" class="btn btn-default">
              <i class="fa fa-sort-numeric-asc"></i> FY Serial Numbers List
            </a>&nbsp;&nbsp;
            <a href="/finy/set-active" class="btn btn-default">
              <i class="fa fa-check"></i> Set Active FY
            </a>            
          </div>
        </div>
        <h2 class="hdg-reports text-center">Financial Years</h2>
        <div class="table-responsive">
          <table class="table table-striped table-hover font12">
            <thead>
              <tr>
                <th width="5%" class="text-center">Sno.</th>
                <th width="30%">Year name</th>
                <th width="10%">Year short name</th>
                <th width="10%">Start date</th>
                <th width="10%">End date</th>
                <th width="10%">Status</th>                
                <th width="10%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php if( count($finys) > 0 ) { ?>
              <?php 
                $cntr = 1;
                foreach($finys as $finy_details):
                  $finy_code = $finy_details['finyCode'];
                  $finy_name = $finy_details['finyName'];
                  $finy_short_name = $finy_details['finyShortName'];
                  $start_date = date("d-m-Y",strtotime($finy_details['startDate']));
                  $end_date = date("d-m-Y",strtotime($finy_details['endDate']));
                  if($finy_details['status']) {
                    $status = 'Active';
                  } else {
                    $status = 'Inactive';
                  }
              ?>
                  <tr class="text-right font12">
                    <td align="text-right" style="vertical-align:middle;"><?php echo $cntr ?></td>
                    <td class="text-left valign-middle"><?php echo $finy_name ?></td>
                    <td class="text-center valign-middle"><?php echo $finy_short_name ?></td>
                    <td class="text-left valign-middle"><?php echo $start_date ?></td>
                    <td class="text-left valign-middle"><?php echo $end_date ?></td>
                    <td class="text-left valign-middle"><?php echo $status ?></td>
                    <td>
                      <div class="btn-actions-group">
                        <?php if($finy_code !== ''): ?>
                          <a class="btn btn-primary" href="/finy/update/<?php echo $finy_code ?>" title="Edit Financial Year Details">
                            <i class="fa fa-pencil"></i>
                          </a>&nbsp;&nbsp;
                          <a class="btn btn-danger" href="/finy/set-active" title="Set Active Financial year">
                            <i class="fa fa-check"></i>
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
                <td colspan="7" align="center"><b>No financial years are available.</b></td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>