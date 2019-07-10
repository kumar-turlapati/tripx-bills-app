<?php
  use Atawa\Utilities;

  $current_date = date("d-m-Y");
  $query_params = [];  

  if(isset($search_params['adjDateFrom']) && $search_params['adjDateFrom'] !='') {
    $adjDateFrom = $search_params['adjDateFrom'];
    $query_params[] = 'adjDateFrom='.$adjDateFrom;
  } else {
    $adjDateFrom = $current_date;
  }
  if(isset($search_params['adjDateTo']) && $search_params['adjDateTo'] !='') {
    $adjDateTo = $search_params['adjDateTo'];
    $query_params[] = 'adjDateTo='.$adjDateTo;
  } else {
    $adjDateTo = $current_date;
  }
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !='') {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = '';
  }

  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  } else {
    $query_params = '';
  }
  $pagination_url = $page_url = '/inventory/stock-adjustments-list';
?>

<div class="row">
  <div class="col-lg-12">
    
    <section class="panelBox">
      <h2 class="hdg-reports text-center">Stock adjustment register</h2>      
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/inventory/stock-adjustment" class="btn btn-default"><i class="fa fa-book"></i> New Stock Adjustment</a>
          </div>
        </div>
        <div class="filters-block">
          <div id="filters-form">
            <form class="form-validate form-horizontal" method="GET" action="<?php echo $pagination_url ?>">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1">Filter by</div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" size="16" type="text" readonly name="adjDateFrom" id="adjDateFrom" value="<?php echo $adjDateFrom ?>" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" size="16" type="text" readonly name="adjDateTo" id="adjDateTo" value="<?php echo $adjDateTo ?>" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                </div>           
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($client_locations as $key=>$value):
                          if($locationCode === $key) {
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
                <div class="form-group text-left">
                  <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
                </div>
              </div>
            </form>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font12">
            <thead>
              <tr>
                <th width="5%" class="text-center valign-middle">Sno.</th>
                <th width="25%" class="text-left valign-middle">Item name</th>
                <th width="8%" class="text-center valign-middle">Adjusted qty.</th>
                <th width="30%" class="text-center valign-middle">Reason</th>
                <th width="10%" class="text-center valign-middle">Adj. date</th>
                <th width="8%" class="text-center valign-middle">Lot no.</th>
                <th width="10%" class="text-center valign-middle">Options</th>                
              </tr>
            </thead>
            <tbody>
              <?php if(count($items)>0): ?>
                <?php
                  $cntr = $sl_no;
                  foreach($items as $item_details):
                    $item_name = $item_details['itemName'];
                    $item_code = $item_details['itemCode'];
                    $lot_no = $item_details['lotNo'];
                    $adj_qty = $item_details['adjQty'];
                    $reason_code = $item_details['reasonCode'];
                    $reason_a = explode('_',$adj_reasons[$reason_code]);
                    $adj_date = date("d-M-Y",strtotime($item_details['adjDate']));
                ?>
                    <tr class="text-right">
                      <td class="valign-middle"><?php echo $cntr ?></td>
                      <td class="text-left valign-middle"><?php echo $item_name ?></td>
                      <td class="text-right valign-middle"><?php echo number_format($adj_qty,2) ?></td>
                      <td class="text-right font11 valign-middle"><i><?php echo $reason_a[0] ?></i></td>
                      <td class="text-right valign-middle"><?php echo $adj_date ?></td>
                      <td class="text-bold valign-middle"><?php echo $lot_no ?></td>
                      <td>
                        <div class="btn-actions-group">                      
                          <a class="btn btn-danger delAdjEntry" href="/inventory/stock-adjustment/delete/<?php echo $item_details['adjCode'].trim($query_params) ?>" title="Remove Adjustment Entry" id="<?php echo $item_details['adjCode'] ?>">
                            <i class="fa fa-times"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
              <?php
                $cntr++;
                endforeach; 
              ?>
            <?php else: ?>
              <tr class="text-center">
                <td colspan="7" style="font-weight:bold;font-size:16px;">No entries available. Change filters and try again.</td>
              </tr>
            <?php endif; ?>
            </tbody>
          </table>
          <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
        </div>
      </div>
    </section>
    <!-- Panel ends -->
  </div>
</div>