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
            <a href="/users/create" class="btn btn-default">
              <i class="fa fa-user"></i> Add New User 
            </a> 
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font12">
            <thead>
              <tr>
                <th width="5%"  class="text-center valign-middle">Sno.</th>
                <th width="20%" class="text-center valign-middle">User Name</th>
                <th width="11%" class="text-center valign-middle">Store Name</th>                
                <th width="7%"  class="text-center valign-middle">Mobile No.</th>
                <th width="7%" class="text-center valign-middle">User Type</span></th>
                <th width="7%"  class="text-center valign-middle">IP Address</span></th>
                <th width="12%" class="text-center valign-middle">Last Access Time</span></th>              
                <th width="13%" class="text-center valign-middle">Device</span></th>
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
                      $store_name = isset($location_ids[$user_details['locationID']]) ? $location_ids[$user_details['locationID']] : 'Invalid';
                      $ip_address = $user_details['ipAddress'];
                      $last_accessed = date("d-m-Y H:ia", strtotime($user_details['lastAccessed']));
                  ?>
                    <tr class="text-right font11">
                      <td class="text-right valign-middle"><?php echo $cntr ?></td>
                      <td class="text-left valign-middle"><?php echo $user_name ?></td>
                      <td class="text-left valign-middle"><?php echo $store_name ?></td>                      
                      <td class="text-bold valign-middle"><?php echo $phone ?></td>
                      <td class="text-left valign-middle"><?php echo $user_type ?></td>
                      <td class="text-left valign-middle"><?php echo $ip_address ?></td>
                      <td class="text-left valign-middle"><?php echo $last_accessed ?></td>
                      <td class="text-center valign-middle">
                        <?php if($user_details['requestSource'] === 'computer'): ?>
                          <i class="fa fa-desktop" aria-hidden="true"></i>&nbsp;&nbsp;Desktop
                        <?php else: ?>
                          <i class="fa fa-tablet" aria-hidden="true"></i>&nbsp;&nbsp;Mobile/Tablet
                        <?php endif; ?>
                      </td>
                      <td class="text-left valign-middle"><?php echo $status ?></td>
                      <td>
                        <div class="btn-actions-group valign-middle">
                          <?php if($uuid !== ''): ?>
                            <a class="btn btn-primary" href="/users/update/<?php echo $uuid ?>" title="Edit user">
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
                  <td colspan="11">No users are available online.</td>
                </tr>
            <?php } ?>
            </tbody>
          </table>

        </div>
      </div>
    </section>
  </div>
</div>