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
            <a href="/campaigns/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Campaign 
            </a> 
          </div>
        </div>
        <h2 class="hdg-reports text-center">List of Campaigns Created</h2>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th width="5%" class="text-center">Sno.</th>
                <th width="50%">Campaign name</th>
                <th width="10%">Start date</th>
                <th width="10%">End date</span></th>
                <th width="10%">Status</span></th>                
                <th width="10%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php if(count($campaigns)>0) { ?>
              <?php 
                $cntr = 1;
                foreach($campaigns as $campaign_details):
                  $campaign_code = $campaign_details['campaignCode'];
                  $campaign_name = $campaign_details['campaignName'];
                  $start_date = date("d-m-Y",strtotime($campaign_details['startDate']));
                  $end_date = date("d-m-Y",strtotime($campaign_details['endDate']));
                  if((int)$campaign_details['status'] === 0) {
                    $status = 'Inactive';
                  } else {
                    $status = 'Active';
                  }
              ?>
                <tr class="text-right font12">
                  <td class="text-right valign-middle"><?php echo $cntr ?></td>
                  <td class="text-left  valign-middle"><?php echo $campaign_name ?></td>
                  <td class="text-right valign-middle"><?php echo $start_date ?></td>
                  <td class="text-right valign-middle"><?php echo $end_date ?></td>
                  <td class="text-right valign-middle"><?php echo $status ?></td>                  
                  <td class="valign-middle">
                    <div class="btn-actions-group">
                      <?php if($campaign_code !== ''): ?>
                        <a class="btn btn-primary" href="/campaigns/update/<?php echo $campaign_code ?>" title="Edit Campaign Details">
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
                <td colspan="6" align="center"><b>No campaigns are available.</b></td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>