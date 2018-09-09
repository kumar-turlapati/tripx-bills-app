<?php
  use Atawa\Utilities;
  $query_params = '';  
  if(isset($search_params['custName']) && $search_params['custName'] !='') {
    $custName = $search_params['custName'];
    $query_params[] = 'custName='.$customerName;
  } else {
    $custName = '';
  }
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
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
        <h2 class="hdg-reports text-center">Customers List</h2>
        <div class="table-responsive">
          <table class="table table-striped table-hover font12">
            <thead>
              <tr>
                <th width="5%"  class="text-center">Sno.</th>
                <th width="25%" class="text-center">Customer name</th>
                <th width="10%" class="text-center">Customer type</th>
                <th width="10%" class="text-center">Address</span></th>
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
                      $customer_type = 'Business';
                    } else {
                      $customer_type = 'Retail Customer';
                    }
                    if($customer_details['address'] !== '') {
                      $address = $customer_details['address'];
                    }
                    if($customer_details['cityName'] !== '') {
                      $address .= $address === '' ? $customer_details['cityName'] : ', '.$customer_details['cityName'];
                    }
                ?>
                    <tr class="text-right font12">
                      <td><?php echo $cntr ?></td>
                      <td class="text-left"><?php echo $customer_name ?></td>
                      <td class="text-left"><?php echo $customer_type ?></td>
                      <td class="text-left"><?php echo trim($address) ?></td>
                      <td class="text-left text-bold"><?php echo $mobile_no ?></td>
                      <td>
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