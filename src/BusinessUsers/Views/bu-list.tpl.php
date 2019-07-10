<?php
  use Atawa\Utilities;
  $query_params = [];  
  if(isset($search_params['userName']) && $search_params['userName'] !='') {
    $user_name = $search_params['userName'];
    $query_params[] = 'userName='.$user_name;
  } else {
    $user_name = '';
  }
  if(isset($search_params['userType']) && $search_params['userType'] !='') {
    $user_type = $search_params['userType'];
    $query_params[] = 'userType='.$user_type;
  } else {
    $user_type = '';
  }
  if(isset($search_params['stateID']) && $search_params['stateID'] !='') {
    $state_id = $search_params['stateID'];
    $query_params[] = 'stateID='.$state_id;
  } else {
    $state_id = 0;
  }
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !='') {
    $location_code = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$location_code;
  } else {
    $location_code = '';
  }  
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }

  $pagination_url = $page_url = '/bu/list';
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/bu/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Business User 
            </a>
          </div>
        </div>
        <div class="filters-block">
          <div id="filters-form">
            <form class="form-validate form-horizontal">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1">Filter by</div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($client_locations as $key=>$value): 
                          if($location_code === $key) {
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
                    <select class="form-control" name="userType" id="userType">
                      <?php 
                        foreach($bu_types as $key=>$value):
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
                    <select class="form-control" name="stateID" id="stateID">
                      <?php 
                        foreach($states as $key=>$value):
                          if((int)$state_id === (int)$key) {
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
                  <input placeholder="User name" type="text" name="userName" id="userName" class="form-control" value="<?php echo $user_name ?>">
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <?php include_once __DIR__."/../../../src/Layout/helpers/filter-buttons.helper.php" ?>
                </div>
              </div>
            </form>
          </div>
        </div>  
        <div class="table-responsive">
          <table class="table table-striped table-hover font12">
            <thead>
              <tr>
                <th width="5%"  class="text-center valign-middle">Sno.</th>
                <th width="25%" class="text-center valign-middle">User name</th>
                <th width="10%" class="text-center valign-middle">User type</th>
                <th width="10%" class="text-center valign-middle">Address</span></th>
                <th width="10%" class="text-center valign-middle">Mobile no</th>
                <th width="10%" class="text-center valign-middle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                if(is_array($users) && count($users)>0):
                  $cntr = $sl_no;
                  foreach($users as $user_details):
                    $address = '';
                    $user_name = $user_details['userName'];
                    $mobile_no = $user_details['mobileNo'];
                    $user_code = $user_details['userCode'];
                    $user_type = Utilities::get_business_user_types($user_details['userType'], false);
                    if($user_details['address'] !== '') {
                      $address = $user_details['address'];
                    }
                    if($user_details['cityName'] !== '') {
                      $address .= $address === '' ? $user_details['cityName'] : ', '.$user_details['cityName'];
                    }
                ?>
                    <tr class="text-right font11">
                      <td class="valign-middle"><?php echo $cntr ?></td>
                      <td class="text-left valign-middle"><?php echo $user_name ?></td>
                      <td class="text-left valign-middle"><?php echo $user_type ?></td>
                      <td class="text-left valign-middle"><?php echo trim($address) ?></td>
                      <td class="text-left text-bold valign-middle"><?php echo $mobile_no ?></td>
                      <td>
                        <div class="btn-actions-group valign-middle">
                          <?php if($user_code !== ''): ?>
                            <a class="btn btn-success" href="/bu/update/<?php echo $user_code ?>" title="Update user information">
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
            <?php else: ?>
                <tr>
                  <td colspan="6" align="center"><b>No users are available.</b></td>
                </tr>
            <?php endif; ?>
            </tbody>
          </table>
          <?php include_once __DIR__."/../../../src/Layout/helpers/pagination.helper.php" ?>
        </div>
      </div>
    </section>
  </div>
</div>