<?php
  use Atawa\Utilities;
  $current_date = date("d-m-Y");
  $pagination_url = $page_url = '/users/app';

  $status_a = array('-1'=>'All Users')+Utilities::get_user_status();
  unset($status_a[2]);

  $query_params = [];
  if(isset($search_params['fromDate']) && $search_params['fromDate'] !='') {
    $fromDate = $search_params['fromDate'];
    $query_params[] = 'fromDate='.$fromDate;
  } else {
    $fromDate = '';
  }
  if(isset($search_params['toDate']) && $search_params['toDate'] !='' ) {
    $toDate = $search_params['toDate'];
    $query_params[] = 'toDate='.$toDate;
  } else {
    $toDate = '';
  }
  if(isset($search_params['customerName']) && $search_params['customerName'] !== '' ) {
    $customer_name = $search_params['customerName'];
    $query_params[] = 'customerName='.$customer_name;
  } else {
    $customer_name = '';
  }
  if(isset($search_params['mobileNo']) && $search_params['mobileNo'] !== '' ) {
    $mobile_no = $search_params['mobileNo'];
    $query_params[] = 'mobileNo='.$mobile_no;
  } else {
    $mobile_no = '';
  }
  if(isset($search_params['status']) && $search_params['status'] !== '' ) {
    $status = $search_params['status'];
    $query_params[] = 'status='.$status;
  } else {
    $status = '-1';
  }  
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }

  //dump($status, $status_a, $status == "");
?>
<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12">
    
    <!-- Panel starts -->
    <section class="panelBox">
      <div class="panelBody">

        <?php echo Utilities::print_flash_message() ?>

        <!-- Right links starts -->
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/app-user/create" class="btn btn-default">
              <i class="fa fa-mobile"></i> New App User 
            </a> 
          </div>
        </div>
        <!-- Right links ends -->

        <div class="filters-block">
          <div id="filters-form">
           <form class="form-validate form-horizontal" method="POST" action="<?php echo $page_url ?>" autocomplete="off">
            <div class="form-group">
              <div class="col-sm-12 col-md-1 col-lg-1 text-right">
                <label class="control-label text-right"><b>Filter by</b></label>          
              </div>
              <div class="col-sm-12 col-md-2 col-lg-2">
                <input 
                  placeholder="Mobile number" 
                  type="text" 
                  name="mobileNo" 
                  id="mobileNo" 
                  class="form-control" 
                  value="<?php echo $mobile_no ?>"
                  maxlength="10"
                />
              </div>              
              <div class="col-sm-12 col-md-2 col-lg-2">
                <input 
                  placeholder="Customer name" 
                  type="text" 
                  name="customerName" 
                  id="customerName" 
                  class="form-control cnameAc" 
                  value="<?php echo $customer_name ?>"
                >
              </div>
              <div class="col-sm-12 col-md-2 col-lg-2">
                <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                  <input class="span2" size="16" type="text" readonly name="fromDate" id="fromDate" value="<?php echo $fromDate ?>" placeholder="Start date" />
                  <span class="add-on"><i class="fa fa-calendar"></i></span>
                </div>
              </div>
              <div class="col-sm-12 col-md-2 col-lg-2">
                <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                  <input class="span2" size="16" type="text" readonly name="toDate" id="toDate" value="<?php echo $toDate ?>" placeholder="End date" />
                  <span class="add-on"><i class="fa fa-calendar"></i></span>
                </div>
              </div>
              <div class="col-sm-12 col-md-2 col-lg-2">
                <select class="form-control" name="status" id="status">
                  <?php 
                    foreach($status_a as $key => $value): 
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
            <div class="form-group" style="text-align: center; padding-top: 10px;">
              <?php include_once __DIR__."/../../Layout/helpers/filter-buttons.helper.php" ?>
            </div>
            </form>
          </div>
        </div>        
        
        <div class="table-responsive">
          <table class="table table-striped table-hover font12">
            <thead>
              <tr>
                <th width="5%" class="text-center valign-middle">Sno.</th>
                <th width="10%" class="text-center valign-middle">Mobile number</th>
                <th width="40%" class="text-center valign-middle">Customer name</th>
                <th width="20%" class="text-center valign-middle">Created on</th>
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
                      $created_on = date("d-m-Y h:ia", strtotime($user_details['createdTime']));
                  ?>

                  <?php //if( (int)$user_details['userType'] !== 3 ): ?>
                    <tr class="text-right font11">
                      <td class="text-right valign-middle"><?php echo $cntr ?></td>
                      <td class="text-right valign-middle"><?php echo $phone ?></td>
                      <td class="text-left valign-middle"><?php echo $user_name ?></td>
                      <td class="text-right valign-middle"><?php echo $created_on ?></td>
                      <td class="text-right valign-middle"><?php echo $status ?></td>
                      <td>
                        <div class="btn-actions-group">
                          <?php if($uuid !== ''): ?>
                            <a class="btn btn-primary" href="/users/update-app/<?php echo $uuid ?>" title="Edit user">
                              <i class="fa fa-pencil"></i>
                            </a>
                            <?php /*
                            <a class="btn btn-danger delUser" href="javascript:void(0)" title="Remove user" uid="<?php echo $uuid ?>">
                              <i class="fa fa-times"></i>
                            </a> */ ?>
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
                  <td colspan="6" style="font-size:14px;text-align:center;color:red;font-weight:bold;">No data available!</td>
                </tr>
            <?php } ?>
            </tbody>
          </table>

        </div>
      </div>
    </section>
    <!-- Panel ends -->
  </div>
</div>