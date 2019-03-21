<?php
  use Atawa\Utilities;
  $page_url = $pagination_url = '/stock-adj-reasons/list';  
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/stock-adj-reason/add" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Adjustment Reason
            </a> 
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="font14">
              <tr>
                <th width="5%" class="text-center">Sno.</th>
                <th width="50%" class="text-center">Description</th>
                <th width="10%" class="text-center">Status</th>
                <th width="5%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php if(count($adj_reasons)>0) { ?>
              <?php 
                $cntr = 1;
                foreach($adj_reasons as $reason_details):
                  $reason_desc = $reason_details['adjReason'];
                  $unique_code = $reason_details['adjReasonCode'];
                  if((int)$reason_details['status'] === 1) {
                    $status = 'Active';
                  } else {
                    $status = 'Inactive';
                  }
              ?>
                <tr class="text-right font11">
                  <td class="text-right valign-middle"><?php echo $cntr ?></td>
                  <td class="text-left valign-middle"><?php echo $reason_desc ?></td>
                  <td class="text-center valign-middle"><?php echo $status ?></td>
                  <td class="valign-middle" align="center">
                    <div class="btn-actions-group">
                      <?php if($unique_code !== ''): ?>
                        <a class="btn btn-primary" href="/stock-adj-reason/update/<?php echo $unique_code ?>" title="Edit Adjustment Reason">
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
                <td colspan="4" align="center"><b>No records are available.</b></td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>