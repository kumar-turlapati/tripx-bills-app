<?php
  use Atawa\Utilities;
  use Atawa\CrmUtilities;

  $page_url = $pagination_url = '/leads/list';
?>
<div class="row">
  <div class="col-lg-12">
    <!-- Panel starts -->
    <section class="panelBox">
      <div class="panelBody">

        <?php echo Utilities::print_flash_message() ?>

        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>

        <!-- Right links starts -->
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/lead/import" class="btn btn-default">
              <i class="fa fa-download"></i> Import Leads 
            </a>&nbsp;&nbsp;&nbsp;
            <a href="/lead/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Lead 
            </a>            
          </div>
        </div>
        <div class="filters-block">
          <div id="filters-form">
            <!-- Form starts -->
            <form class="form-validate form-horizontal" method="POST">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1">Filter by</div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <?php echo CrmUtilities::render_dropdown('leadStatusId', $lead_status_a, 0) ?>
                   </div>
                </div>
                <?php include_once __DIR__."/../../../src/Layout/helpers/filter-buttons.helper.php" ?>
            </div>
           </form>        
          <!-- Form ends -->
          </div>
        </div>
        <div class="table-responsive">
          <?php if(count($leads)>0): ?>
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font12">
                <th width="4%" class="text-center">Sno</th>
                <th width="30%" class="text-center">Buisness name / name</th>
                <th width="10%" class="text-center">Mobile</th>
                <th width="10%" class="text-center">Email</th>                
                <th width="10%" class="text-center">Lead source</th>
                <th width="10%" class="text-center">Status</th>
                <th width="10%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $cntr = $sl_no;
                $total = 0;
                foreach($leads as $lead_details):
                  $lead_code = $lead_details['leadCode'];
                  $email = $lead_details['email'];
                  $mobile = $lead_details['mobile'];
                  $status = CrmUtilities::get_lead_status($lead_details['leadStatusId'], false);
                  if($lead_details['businessName'] !== '') {
                    $name = $lead_details['businessName'];
                  } else {
                    $name = $lead_details['firstName'].' '.$lead_details['lastName'];
                  }
                  $lead_source = CrmUtilities::get_lead_source($lead_details['leadSourceId'], false);
              ?>
                <tr class="font12">
                  <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                  <td class="valign-middle"><?php echo $name ?></td>
                  <td class="valign-middle"><?php echo $mobile ?></td>
                  <td class="valign-middle"><?php echo $email ?></td>
                  <td class="valign-middle"><?php echo $lead_source ?></td>
                  <td class="valign-middle"><?php echo $status ?></td>
                  <td class="valign-middle">
                    <div class="btn-actions-group" align="right">                    
                      <a class="btn btn-primary" href="/lead/update/<?php echo $lead_code ?>" title="Update Lead">
                        <i class="fa fa-pencil"></i>
                      </a>
                      <a class="btn btn-danger leadDelete" href="javascript:void(0)" id="<?php echo $lead_code ?>" title="Remove Lead">
                        <i class="fa fa-times"></i>
                      </a>                      
                    </div>
                  </td>
                </tr>
            <?php
              $cntr++;
              endforeach; 
            ?>
            </tbody>
          </table>
          <form>
            <input type="hidden" value="<?php echo $current_page ?>" id="currentPage" name="currentPage" />
          </form>
          <?php endif; ?>    
          <?php include_once __DIR__."/../../../src/Layout/helpers/pagination.helper.php" ?>
        </div>
      </div>
    </section>
    <!-- Panel ends -->
  </div>
</div>