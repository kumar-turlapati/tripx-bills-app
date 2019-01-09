<?php
  use Atawa\Utilities;
  $query_params = '';
  if(isset($search_params['custName']) && $search_params['custName'] !='') {
    $custName = $search_params['custName'];
    $query_params[] = 'custName='.$custName;
  } else {
    $custName = '';
  }
  if(isset($search_params['stateCode']) && $search_params['stateCode'] !='') {
    $stateCode = $search_params['stateCode'];
    $query_params[] = 'stateCode='.$stateCode;
  } else {
    $stateCode = '';
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
  $pagination_url = $page_url = '/customers/list';
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/customers/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Customer 
            </a> 
          </div>
        </div>
        <div class="filters-block">
          <div id="filters-form">
            <form class="form-validate form-horizontal" method="POST" action="<?php echo $page_url ?>" autocomplete="off">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1" style="padding-top:5px;"><b>Filter by</b></div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Customer Name" type="text" name="custName" id="custName" class="form-control" value="<?php echo $custName ?>">
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="stateCode" id="stateCode">
                      <?php 
                        foreach($states_a as $key=>$value):
                          if((int)$stateCode === (int)$key) {
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
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
              </div>
            </form>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font12">
            <thead>
              <tr>
                <th width="5%"  class="text-center">Sno.</th>
                <th width="25%" class="text-center">Customer name</th>
                <th width="15%" class="text-center">Store name</th>
                <th width="5%" class="text-center">Customer type</th>
                <th width="35%" class="text-center">Address</span></th>
                <th width="10%" class="text-center">Mobile no</th>
                <th width="10%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                if(is_array($customers) && count($customers)>0):
                  $cntr = $sl_no;
                  foreach($customers as $customer_details):
                    $address = '';
                    $customer_name = $customer_details['customerName'];
                    $mobile_no = $customer_details['mobileNo'];
                    $customer_code = $customer_details['customerCode'];
                    if($customer_details['customerType'] === 'b') {
                      $customer_type = 'B2B';
                    } else {
                      $customer_type = 'B2C';
                    }
                    if($customer_details['address'] !== '') {
                      $address = $customer_details['address'];
                    }
                    if($customer_details['cityName'] !== '') {
                      $address .= $address === '' ? $customer_details['cityName'] : ', '.$customer_details['cityName'];
                    }
                    $store_name = isset($location_ids[$customer_details['locationID']]) ? $location_ids[$customer_details['locationID']] : '';
                ?>
                    <tr class="text-right font11">
                      <td class="valign-middle"><?php echo $cntr ?></td>
                      <td class="text-left valign-middle"><?php echo $customer_name ?></td>
                      <td class="text-left valign-middle"><?php echo $store_name ?></td>
                      <td class="text-left valign-middle"><?php echo $customer_type ?></td>
                      <td class="text-left valign-middle"><?php echo trim($address) ?></td>
                      <td class="text-left text-bold valign-middle"><?php echo $mobile_no ?></td>
                      <td class="valign-middle">
                        <div class="btn-actions-group">
                          <?php if($customer_code !== ''): ?>
                            <a class="btn btn-success" href="/customers/update/<?php echo $customer_code ?>" title="Update Customer information">
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
                  <td colspan="6" align="center"><b>No data available.</b></td>
                </tr>
            <?php endif; ?>
            </tbody>
          </table>
          <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
        </div>
      </div>
    </section>
  </div>
</div>