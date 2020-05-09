<?php
  use Atawa\Utilities;
  use Atawa\CrmUtilities;

  #process form data
  $task_owner_id = isset($form_data['taskOwnerId']) && $form_data['taskOwnerId'] !== '' ? $form_data['taskOwnerId'] : $_SESSION['uid'];
  $task_title = isset($form_data['taskTitle']) && $form_data['taskTitle'] !== '' ? $form_data['taskTitle'] : '';
  $task_desc = isset($form_data['taskDescription']) && $form_data['taskDescription'] !== '' ? $form_data['taskDescription'] : '';
  $task_type = isset($form_data['taskTypeId']) && $form_data['taskTypeId'] !== '' ? $form_data['taskTypeId'] : '';
  $task_response = isset($form_data['taskResponseId']) && $form_data['taskResponseId'] !== '' ? $form_data['taskResponseId'] : '';
  $task_status = isset($form_data['taskStatusId']) && $form_data['taskStatusId'] !== '' ? $form_data['taskStatusId'] : '';
  $task_start_date = isset($form_data['taskStartDate']) && $form_data['taskStartDate'] !== '' ? $form_data['taskStartDate'] : date("d-m-Y");
  $task_start_time = isset($form_data['taskStartTime']) && $form_data['taskStartTime'] !== '' ? $form_data['taskStartTime'] : date("Hi");
  $task_end_date = isset($form_data['taskEndDate']) && $form_data['taskEndDate'] !== '' ? $form_data['taskEndDate'] : date("d-m-Y");
  $task_end_time = isset($form_data['taskEndTime']) && $form_data['taskEndTime'] !== '' ? $form_data['taskEndTime'] : date("Hi");
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/tasks/list" class="btn btn-default">
              <i class="fa fa-tasks"></i> Tasks List
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="Off">
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Task owner*</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('taskOwnerId', $users, $task_owner_id) ?>
              </div>
              <?php if(isset($form_errors['taskOwnerId'])): ?>
                <span class="error"><?php echo $form_errors['taskOwnerId'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-2 col-lg-2">
              <label class="control-label labelStyle">Task start date*</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $task_start_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $task_start_date ?>" size="16" type="text" readonly name="taskStartDate" id="taskStartDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($form_errors['taskStartDate'])): ?>
                    <span class="error"><?php echo $form_errors['taskStartDate'] ?></span>
                  <?php endif; ?>                  
                </div>
              </div>
            </div>
            <div class="col-sm-12 col-md-2 col-lg-2">
              <label class="control-label labelStyle">Task start time*</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('taskStartTime', $time_array_a, $task_start_time) ?>
              </div>
              <?php if(isset($form_errors['taskStartTime'])): ?>
                <span class="error"><?php echo $form_errors['taskStartTime'] ?></span>
              <?php endif; ?>               
            </div>
            <div class="col-sm-12 col-md-2 col-lg-2">
              <label class="control-label labelStyle">Task end date*</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $task_end_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $task_end_date ?>" size="16" type="text" readonly name="taskEndDate" id="taskEndDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($form_errors['taskEndDate'])): ?>
                    <span class="error"><?php echo $form_errors['taskEndDate'] ?></span>
                  <?php endif; ?>                  
                </div>
              </div>
            </div>
            <div class="col-sm-12 col-md-2 col-lg-2">
              <label class="control-label labelStyle">Task end time*</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('taskEndTime', $time_array_a, $task_end_time) ?>
              </div>
              <?php if(isset($form_errors['taskEndTime'])): ?>
                <span class="error"><?php echo $form_errors['taskEndTime'] ?></span>
              <?php endif; ?>
            </div>                       
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-12 col-lg-12">
              <label class="control-label labelStyle">Task title*</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="taskTitle" 
                id="taskTitle"
                value="<?php echo $task_title ?>"
              />
              <?php if(isset($form_errors['taskTitle'])): ?>
                <span class="error"><?php echo $form_errors['taskTitle'] ?></span>
              <?php endif; ?>
            </div>            
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-12 col-lg-12">
              <label class="control-label labelStyle">Task description</label>
              <textarea
                class="form-control noEnterKey"
                rows="3"
                cols="100"
                id="taskDescription"
                name="taskDescription"
              ><?php echo $task_desc ?></textarea>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Task type*</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('taskTypeId', $task_types_a, $task_type) ?>
              </div>
              <?php if(isset($form_errors['taskTypeId'])): ?>
                <span class="error"><?php echo $form_errors['taskTypeId'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Task response*</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('taskResponseId', $task_response_a, $task_response) ?>
              </div>
              <?php if(isset($form_errors['taskResponseId'])): ?>
                <span class="error"><?php echo $form_errors['taskResponseId'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle">Task status*</label>
              <div class="select-wrap">
                <?php echo CrmUtilities::render_dropdown('taskStatusId', $task_status_a, $task_status) ?>
              </div>
              <?php if(isset($form_errors['taskStatusId'])): ?>
                <span class="error"><?php echo $form_errors['taskStatusId'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="text-center" style="margin-top: 20px;">
            <button class="btn btn-primary cancelOp">
              <i class="fa fa-save"></i> Save
            </button>&nbsp;&nbsp;
            <button class="btn btn-danger cancelButton" id="taskSave">
              <i class="fa fa-times"></i> Cancel
            </button>            
          </div>
        </form>
      </div>
    </section>
  </div>
</div>