<?php
  use Atawa\Utilities;

  $query_params = [];
  if(isset($search_params['userType']) && $search_params['userType'] !='') {
    $user_type = $search_params['userType'];
    $query_params[] = 'userType='.$user_type;
  } else {
    $user_type = '';
  }
  if(isset($search_params['mobileNo']) && $search_params['mobileNo'] !='' ) {
    $mobile_no = $search_params['mobileNo'];
    $query_params[] = 'mobileNo='.$mobile_no;
  } else {
    $mobile_no = '';
  }
  if(isset($search_params['status']) && $search_params['status'] !='' ) {
    $status = $search_params['status'];
    $query_params[] = 'status='.$status;
  } else {
    $status = '-1';
  }  
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = $pagination_url = '/users/list';
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/users/create" class="btn btn-default">
              <i class="fa fa-user"></i> Create New Platform User 
            </a>
          </div>
        </div>
        <div class="filters-block">
          <div id="filters-form">
            <form 
              class="form-validate form-horizontal" 
              method="POST" 
              action="<?php echo $page_url ?>" 
              autocomplete="off"
            >
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1" style="font-weight: bold; font-size: 14px; margin-top: 9px; text-align: right;">Filter by</div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="userType" id="userType">
                      <?php 
                        foreach($user_types as $key=>$value):
                          if((int)$user_type === (int)$key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }  
                      ?>
                       <option value="<?php echo $key ?>" <?php echo $selected ?>>
                          <?php echo $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="status" id="status">
                      <?php 
                        foreach($user_status as $key=>$value):
                          if((int)$status === (int)$key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }  
                      ?>
                       <option value="<?php echo $key ?>" <?php echo $selected ?>>
                          <?php echo $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>                
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input type="text" class="form-control noEnterKey" name="mobileNo" id="mobileNo" placeholder="Mobile No." value="<?php echo $mobile_no ?>" />
                </div>                
                <?php include_once __DIR__."/../../Layout/helpers/filter-buttons.helper.php" ?>
              </div>
            </form>
          </div>
        </div>  
        <div class="table-responsive">
          <table class="table table-striped table-hover font11">
            <thead>
              <tr>
                <th width="5%" class="text-center valign-middle">Sno.</th>
                <th width="30%" class="text-center valign-middle">User name</th>
                <th width="25%" class="text-center valign-middle">Login ID</th>
                <th width="10%" class="text-center valign-middle">Phone no</th>
                <th width="10%" class="text-center valign-middle">User type</span></th>
                <th width="10%" class="text-center valign-middle">Status</th>
                <th width="10%" class="text-center valign-middle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                if(count($users)>0) {
                    $cntr = 1;
                    foreach($users as $user_details):
                      $user_name = $user_details['userName'];
                      $email = $user_details['email'];
                      $phone = $user_details['userPhone'];
                      $uuid = $user_details['uuid'];
                      $user_type = Utilities::get_user_types($user_details['userType']);
                      if((int)$user_details['status']===0) {
                        $status = 'Disabled';
                      } elseif((int)$user_details['status']===1) {
                        $status = 'Active';
                      } elseif((int)$user_details['status']===0) {
                        $status = 'Inactive';
                      } else {
                        $status = '<span class="red">Blocked</span>';
                      }
                  ?>

                  <?php //if( (int)$user_details['userType'] !== 3 ): ?>
                    <tr class="text-right font11">
                      <td class="text-right valign-middle"><?php echo $cntr ?></td>
                      <td class="text-left valign-middle"><?php echo $user_name ?></td>
                      <td class="text-left valign-middle"><?php echo $email ?></td>
                      <td class="text-bold valign-middle"><?php echo $phone ?></td>
                      <td class="text-left valign-middle"><?php echo $user_type ?></td>
                      <td class="text-left valign-middle"><?php echo $status ?></td>
                      <td>
                        <div class="btn-actions-group">
                          <?php if($uuid !== ''): ?>
                            <a class="btn btn-primary" href="/users/update/<?php echo $uuid ?>" title="Edit user">
                              <i class="fa fa-pencil"></i>
                            </a>
                            <a class="btn btn-danger delUser" href="/users/delete/<?php echo $uuid ?>" title="Delete user">
                              <i class="fa fa-times"></i>
                            </a>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php //endif; ?>

                <?php
                  $cntr++;
                  endforeach; 
                ?>
            <?php } else { ?>
                <tr>
                  <td colspan="7" style="font-size:16px;text-align:center;color:red;">No users are available / Unauthorized access.</td>
                </tr>
            <?php } ?>
            </tbody>
          </table>

        </div>
      </div>
    </section>
  </div>
</div>