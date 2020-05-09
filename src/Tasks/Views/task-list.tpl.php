<?php
  use Atawa\Utilities;
  use Atawa\CrmUtilities;

  $page_url = $pagination_url = '/tasks/list';

  $task_status_id = isset($filter_params['taskStatusId']) ? $filter_params['taskStatusId'] : '';
  $task_type_id = isset($filter_params['taskTypeId']) ? $filter_params['taskTypeId'] : '';
  $task_response_id = isset($filter_params['taskResponseId']) ? $filter_params['taskResponseId'] : '';
  $task_time = isset($filter_params['taskTime']) ? $filter_params['taskTime'] : 'future';
  $order_by = isset($filter_params['orderBy']) ? $filter_params['orderBy'] : '';
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
            <a href="/task/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Task 
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
                    <?php echo CrmUtilities::render_dropdown('taskStatusId', $task_status_a, $task_status_id) ?>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <?php echo CrmUtilities::render_dropdown('taskTypeId', $task_types_a, $task_type_id) ?>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <?php echo CrmUtilities::render_dropdown('taskResponseId', $task_response_a, $task_response_id) ?>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <?php echo CrmUtilities::render_dropdown('taskTime', $task_times, $task_time) ?>
                   </div>
                </div><br /><br />
                <div align="center">               
                  <?php include_once __DIR__."/../../../src/Layout/helpers/filter-buttons.helper.php" ?>
                </div>            </div>
           </form>        
          <!-- Form ends -->
          </div>
        </div>
        <div class="table-responsive">
          <?php if(count($tasks)>0): ?>
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font12">
                <th width="4%" class="text-center">Sno</th>
                <th width="25%" class="text-center">Task title</th>
                <th width="12%" class="text-center">Start date & time</th>
                <th width="12%" class="text-center">End date & time</th>
                <th width="10%" class="text-center">Task type</th>
                <th width="10%" class="text-center">Task response</th>                
                <th width="10%" class="text-center">Task status</th>
                <th width="10%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $cntr = $sl_no;
                $total = 0;
                foreach($tasks as $task_details):
                  $task_code = $task_details['taskCode'];
                  $task_title = $task_details['taskTitle'];
                  $start_date = date("d-m-Y h:ia", strtotime($task_details['taskStartDate']));
                  $end_date = date("d-m-Y h:ia", strtotime($task_details['taskEndDate']));
                  $task_type = CrmUtilities::get_task_types($task_details['taskTypeId'], false);
                  $task_response = CrmUtilities::get_task_response($task_details['taskResponseId'], false);
                  $task_status = CrmUtilities::get_task_status($task_details['taskStatusId'], false);
              ?>
                <tr class="font12">
                  <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                  <td class="valign-middle"><?php echo $task_title ?></td>
                  <td class="valign-middle"><?php echo $start_date ?></td>
                  <td class="valign-middle"><?php echo $end_date ?></td>
                  <td class="valign-middle"><?php echo $task_type ?></td>
                  <td class="valign-middle"><?php echo $task_response ?></td>
                  <td class="valign-middle"><?php echo $task_status ?></td>
                  <td class="valign-middle">
                    <div class="btn-actions-group" align="right">                    
                      <a class="btn btn-primary" href="/task/update/<?php echo $task_code ?>" title="Update Task">
                        <i class="fa fa-pencil"></i>
                      </a>
                      <a class="btn btn-danger taskDelete" href="/task/remove/<?php echo $task_code ?>" id="<?php echo $task_code ?>" title="Remove Task">
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