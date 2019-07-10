<?php

  use Atawa\Utilities;
  use Atawa\Constants;
  
  // dump($search_params);
  // dump($users);
  // dump($devices);
  // exit;

  $query_params = [];
  if(isset($search_params['uuid']) && $search_params['uuid'] !='') {
    $sel_uuid = $search_params['uuid'];
    $query_params[] = 'uuid='.$sel_uuid;
  } else {
    $sel_uuid = '';
  }
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;    
  } else {
    $locationCode = '';
  }  
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = '/devices/list';
?>

<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/device/add" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> Add New Device
            </a> 
          </div>
        </div>
  		  <div class="filters-block">
    		  <div id="filters-form">
            <form class="form-validate form-horizontal" method="POST">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1">Filter by</div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="uuid" id="uuid">
                      <?php
                        foreach($users as $uuid => $user_name):
                          if($sel_uuid === $uuid) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                       <option value="<?php echo $uuid ?>" <?php echo $selected ?>>
                          <?php echo $user_name ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($client_locations as $location_key=>$value):
                          $location_key_a = explode('`', $location_key);
                          if($locationCode === $location_key_a[0]) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }  
                      ?>
                       <option value="<?php echo $location_key_a[0] ?>" <?php echo $selected ?>>
                          <?php echo $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>                
                <?php include_once __DIR__."/../../../src/Layout/helpers/filter-buttons.helper.php" ?>
            </div>
           </form>        
			    </div>
        </div>
        <div class="table-responsive">
          <?php if( count($devices)>0 ): ?>
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font12">
                <th width="5%" class="text-center valign-middle">Sno.</th>
                <th width="15%" class="text-center valign-middle">Device Name</th>                
                <th width="15%" class="text-center valign-middle">User Name</th>                
                <th width="15%" class="text-center valign-middle">Store Name</th>                
                <th width="10%" class="text-center valign-middle">Created On</th>
                <th width="10%" class="text-center valign-middle">Updated On</th>
                <th width="15%" class="text-center valign-middle">Status</th>
                <th width="15%" class="text-center valign-middle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $cntr = $sl_no;
                foreach($devices as $device_details):
                  $device_name = $device_details['deviceName'];
                  $device_code = $device_details['deviceCode'];
                  $created_date = !is_null($device_details['createdDate']) ? date('d-M-Y', strtotime($device_details['createdDate'])) : '-';
                  $updated_date = !is_null($device_details['updatedDate']) && $device_details['updatedDate'] !== '0000-00-00 00:00:00' ? date('d-M-Y', strtotime($device_details['updatedDate'])) : '-';
                  $status = (int)$device_details['status'] === 1 ? 'Active' : 'Inactive';
                  $user_name = isset($users[$device_details['uuid']]) ? $users[$device_details['uuid']] : '';
                  $store_name = isset($location_ids[$device_details['locationID']]) ? $location_ids[$device_details['locationID']] : '';
              ?>
                <tr class="font11">
                  <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                  <td align="left"  class="valign-middle"><?php echo $device_name ?></td>
                  <td align="left"  class="valign-middle"><?php echo $user_name ?></td>
                  <td align="left"  class="valign-middle"><?php echo $store_name ?></td>
                  <td align="left"  class="valign-middle"><?php echo $created_date ?></td>                
                  <td align="left"  class="valign-middle"><?php echo $updated_date ?></td>
                  <td align="left"  class="valign-middle"><?php echo $status ?></td>
                  <td align="right" class="valign-middle">
                    <div class="btn-actions-group" align="right">
                      <a class="btn btn-primary" href="/device/update/<?php echo $device_code ?>" title="Edit Device">
                        <i class="fa fa-pencil"></i>
                      </a>
                      <a class="btn btn-danger delDevice" href="/device/delete/<?php echo $device_code ?>" title="Delete Device">
                        <i class="fa fa-times "></i>
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
          <?php endif; ?>    
          <?php include_once __DIR__."/../../../src/Layout/helpers/pagination.helper.php" ?>
        </div>
      </div>
    </section>
  </div>
</div>