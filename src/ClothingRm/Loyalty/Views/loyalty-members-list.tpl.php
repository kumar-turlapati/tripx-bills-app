<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  
  $query_params = [];
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = '';
  }
  if(isset($search_params['memberName']) && $search_params['memberName'] !== '' ) {
    $member_name = $search_params['memberName'];
    $query_params[] = 'memberName='.$member_name;
  } else {
    $member_name = '';
  }
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = '/loyalty-members/list';
?>

<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <h2 class="hdg-reports text-center">Loyalty Members List</h2>
      <div class="panelBody">

        <?php echo Utilities::print_flash_message() ?>

        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>

        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/loyalty-member/add" class="btn btn-default">
              <i class="fa fa-diamond"></i> New Loyalty Member 
            </a> 
          </div>
        </div>
  		  <div class="filters-block">
    		  <div id="filters-form">
            <form class="form-validate form-horizontal" method="POST" autocomplete="off" action="<?php echo $page_url ?>">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1">Filter by</div>
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
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Member name" type="text" name="memberName" id="memberName" class="form-control" value="<?php echo $member_name ?>">
                </div>
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
            </div>
           </form>
			    </div>
        </div>
        <div class="table-responsive">
          <?php if(count($members)>0): ?>
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font12">
                <th width="5%"  class="text-center valign-middle">Sno</th>
                <th width="30%" class="text-center valign-middle">Member name</th>
                <th width="8%"  class="text-center valign-middle">Mobile no.</th>
                <th width="8%"  class="text-center valign-middle">Card no.</span></th>
                <th width="8%"  class="text-center valign-middle">Created on</span></th>
                <th width="20%" class="text-center valign-middle">Store name</span></th>                
                <th width="10%" class="text-center valign-middle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $cntr = $sl_no;
                foreach($members as $member_details):
                  $member_name = $member_details['memberName'];
                  $member_code = $member_details['memberCode'];
                  $member_mobile = $member_details['memberMobile'];
                  $card_no = $member_details['cardNo'];
                  $created_on = date('d-M-Y', strtotime($member_details['createdDate']));
                  $location_code = $location_codes[$member_details['locationID']];
                  $store_name = $location_ids[$member_details['locationID']];
              ?>
                <tr class="font12">
                  <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                  <td class="valign-middle">
                    <a href="/loyalty-member/ledger/<?php echo $member_code ?>" title="Ledger details" class="hyperlink"><?php echo $member_name ?></a>
                  </td>
                  <td align="right" class="valign-middle"><?php echo $member_mobile ?></td>
                  <td align="right" class="valign-middle"><?php echo $card_no ?></td>
                  <td align="left" class="valign-middle"><?php echo $created_on ?></td>                
                  <td class="valign-middle"><?php echo $store_name ?></td>
                  <td class="valign-middle">
                  <?php if($member_code !== ''): ?>
                    <div class="btn-actions-group" align="right">                    
                      <a class="btn btn-primary" href="/loyalty-member/update/<?php echo $member_code ?>" title="Edit member details">
                        <i class="fa fa-pencil"></i>
                      </a>&nbsp;
                    </div>
                  <?php endif; ?>
                  </td>
                </tr>
              <?php
                $cntr++;
                endforeach; 
              ?>
            </tbody>
          </table>
          <?php endif; ?>
          <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
        </div>
      </div>
    </section>
  </div>
</div>