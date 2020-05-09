<?php
  use Atawa\Utilities;
  use Atawa\CrmUtilities;

  $page_url = $pagination_url = '/appointments/list';

  $appointment_status_id = isset($filter_params['appointmentStatusId']) ? $filter_params['appointmentStatusId'] : '';
  $appointment_type_id = isset($filter_params['appointmentTypeId']) ? $filter_params['appointmentTypeId'] : '';
  $appointment_purpose_id = isset($filter_params['appointmentPurposeId']) ? $filter_params['appointmentPurposeId'] : '';
  $order_by = isset($filter_params['orderBy']) ? $filter_params['orderBy'] : '';
  $appointment_time = isset($filter_params['appointmentTime']) ? $filter_params['appointmentTime'] : 'future';
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
            <a href="/appointment/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Appointment 
            </a>            
          </div>
        </div>
        <div class="filters-block">
          <div id="filters-form">
            <!-- Form starts -->
            <form class="form-validate form-horizontal" method="POST">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1" style="font-weight: bold; color:#000; font-size: 12px; padding-top: 10px; text-align: right;">Filter by</div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <?php echo CrmUtilities::render_dropdown('appointmentStatusId', $appointment_status_a, $appointment_status_id) ?>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <?php echo CrmUtilities::render_dropdown('appointmentTypeId', $appointment_types_a, $appointment_type_id) ?>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <?php echo CrmUtilities::render_dropdown('appointmentPurposeId', $appointment_purpose_a, $appointment_purpose_id) ?>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <?php echo CrmUtilities::render_dropdown('orderBy', $appointments_orderby, $order_by) ?>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <?php echo CrmUtilities::render_dropdown('appointmentTime', $appointment_times, $appointment_time) ?>
                   </div>
                </div><br /><br />
                <div align="center">               
                  <?php include_once __DIR__."/../../../src/Layout/helpers/filter-buttons.helper.php" ?>
                </div>
            </div>
           </form>
          <!-- Form ends -->
          </div>
        </div>
        <div class="table-responsive">
          <?php if(count($appointments)>0): ?>
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font12">
                <th width="4%" class="text-center">Sno</th>
                <th width="25%" class="text-center">Appointment title</th>
                <th width="12%" class="text-center">Start date & time</th>
                <th width="12%" class="text-center">End date & time</th>
                <th width="10%" class="text-center">Appointment type</th>
                <th width="10%" class="text-center">Appointment purpose</th>                
                <th width="10%" class="text-center">Appointment status</th>
                <th width="10%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $cntr = $sl_no;
                $total = 0;
                foreach($appointments as $appointment_details):
                  $appointment_code = $appointment_details['appointmentCode'];
                  $appointment_title = $appointment_details['appointmentTitle'];
                  $start_date = date("d-m-Y h:ia", strtotime($appointment_details['appointmentStartDate']));
                  $end_date = date("d-m-Y h:ia", strtotime($appointment_details['appointmentEndDate']));
                  $appointment_type = CrmUtilities::get_appointment_types($appointment_details['appointmentTypeId'], false);
                  $appointment_purpose = CrmUtilities::get_appointment_purpose($appointment_details['appointmentPurposeId'], false);
                  $appointment_status = CrmUtilities::get_appointment_status($appointment_details['appointmentStatusId'], false);
              ?>
                <tr class="font12">
                  <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                  <td class="valign-middle"><?php echo $appointment_title ?></td>
                  <td class="valign-middle"><?php echo $start_date ?></td>
                  <td class="valign-middle"><?php echo $end_date ?></td>
                  <td class="valign-middle"><?php echo $appointment_type ?></td>
                  <td class="valign-middle"><?php echo $appointment_purpose ?></td>
                  <td class="valign-middle"><?php echo $appointment_status ?></td>
                  <td class="valign-middle">
                    <div class="btn-actions-group" align="right">                    
                      <a class="btn btn-primary" href="/appointment/update/<?php echo $appointment_code ?>" title="Update Appointment">
                        <i class="fa fa-pencil"></i>
                      </a>
                      <a class="btn btn-danger appointmentDelete" href="/appointment/remove/<?php echo $appointment_code ?>" id="<?php echo $appointment_code ?>" title="Remove Appointment">
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