<?php 
  $pagination_url = $page_url = '/stock-audit/items/'.$audit_code;
  $query_params = [];  
  if(isset($search_params['psName']) && $search_params['psName'] !='') {
    $item_name = $search_params['psName'];
    $query_params[] = 'psName='.$item_name;
  } else {
    $item_name = '';
  }
  if(isset($search_params['brandName']) && $search_params['brandName'] !== '' ) {
    $brand_name = $search_params['brandName'];
    $query_params[] = 'brandName='.$brand_name;
  } else {
    $brand_name = '';
  }
  if(isset($search_params['category']) && $search_params['category'] !== '' ) {
    $category = $search_params['category'];
    $query_params[] = 'category='.$category;
  } else {
    $category = '';
  }  
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = $default_location;
  }
  if(isset($search_params['fetchPattern']) && $search_params['fetchPattern'] !== '' ) {
    $fetch_pattern = $search_params['fetchPattern'];
    $query_params[] = 'fetchPattern='.$fetch_pattern;
  } else {
    $fetch_pattern = '';
  }  
  if($query_params != '') {
    $query_params = '?'.implode('&', $query_params);
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message(); ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
              <a href="/stock-audit/create" class="btn btn-default">
                <i class="fa fa-plus"></i> New Stock Audit
              </a>&nbsp;&nbsp;
              <a href="/stock-audit/register" class="btn btn-default">
                <i class="fa fa-book"></i> Stock Audit Register
              </a>              
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" action="<?php echo $page_url ?>" id="stockAuditItems">
          <div class="filters-block">
            <div id="filters-form">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1 text-right">
                  <label class="control-label text-right"><b>Filter by</b></label>          
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Product name" type="text" name="psName" id="psName" class="form-control inameAc" value="<?php echo $item_name ?>">
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Brand name" type="text" name="brandName" id="brandName" class="form-control brandAc" value="<?php echo $brand_name ?>">
                </div>                
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="category" id="category">
                      <?php 
                        foreach($categories as $key => $value):
                          if($category === $key) {
                            $selected = " selected = selected";
                          } else {
                            $selected = '';
                          }
                      ?>
                         <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="fetchPattern" id="fetchPattern">
                      <?php
                        foreach($fetch_pattern_a as $key => $value):
                          if($key === $fetch_pattern) {
                            $selected = 'selected = selected';
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
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
                </div>
              </div>
            </div>
          </div>
          <div class="text-center" style="margin:5px 0 5px 0;">
            <?php if($audit_status === 1): ?>
              <button class="btn btn-danger" name="op" value="saLockSubmit" id="saLockSubmit">
                <i class="fa fa-lock"></i> Lock &amp; Submit
              </button>
            <?php endif; ?>
            <?php if((int)$_SESSION['utype'] === 3 && $audit_status === 2): ?>
              <button class="btn btn-primary" name="op" value="saPhyQty" id="saPhyQty">
                <i class="fa fa-recycle"></i> Post System Qty.
              </button>
            <?php endif; ?>
              <button class="btn btn-success" name="op" value="printAuditReport" id="printAuditReport">
                <i class="fa fa-print"></i> Print Audit Report
              </button>
          </div>          
          <div class="table-responsive">
            <table class="table table-striped table-hover" id="itemnames">
              <thead>
                <tr class="font12">
                  <th width="5%" class="text-center">Sno.</th>
                  <th width="9%" class="text-center">Item Code</th>                  
                  <th width="25%" class="text-left">Product / Service Name</th>
                  <th width="13%" class="text-center">Brand Name</th>               
                  <th width="13%" class="text-center">Category</th>               
                  <th width="10%" class="text-center">Physical Qty.</span></th>                
                  <th width="10%" class="text-center"><?php echo (int)$_SESSION['utype'] === 3 ? 'System Qty.': '' ?></span></th>
                  <th width="10%" class="text-center"><?php echo (int)$_SESSION['utype'] === 3 ? 'Diff.' : '' ?></span></th>
                  <th width="10%" class="text-center">HSN / SAC<span class="brk">Code</span></th>
                  <th width="10%" class="text-center">Rack Number</th> 
                </tr>
              </thead>
              <tbody>
                <?php if( count($items)>0 ): ?>
                  <?php
                    $cntr = $sl_no;
                    $page_tot_phy_qty = $page_tot_sys_qty = $page_tot_dif_qty = 0;
                    foreach($items as $item_details):
                      $item_name = $item_details['itemName'];
                      $brand_name = $item_details['brandName'];
                      $category = $item_details['categoryName'];
                      $physical_qty = $item_details['physicalQty'];
                      $system_qty = $item_details['systemQty'];
                      $hsn_sac_code = $item_details['hsnSacCode'];
                      $rack_number = $item_details['rackNo'];
                      $sku = $item_details['itemSku'];
                      $item_code = $item_details['itemCode'];
                      $diff = $physical_qty - $system_qty;
                      
                      $page_tot_phy_qty += $physical_qty;
                      $page_tot_sys_qty += $system_qty;
                      $page_tot_dif_qty += $diff;
                  ?>
                    <tr class="font11">
                      <td class="text-right"><?php echo $cntr ?></td>
                      <td class="text-left"><?php echo $item_code ?></td>                      
                      <td class="text-left"><?php echo $item_name ?></td>
                      <td class="text-left"><?php echo $brand_name ?></td>
                      <td class="text-left"><?php echo $category ?></td>
                      <td class="text-right" style="font-size:14px;font-weight:bold;color:#225992" title="Click here to add Physical Qty."><?php echo $physical_qty > 0 ? number_format($physical_qty, 2, '.', '') : '' ?></td>
                      <?php if((int)$_SESSION['utype'] === 3): ?>
                        <td class="text-right" style="font-size:14px;font-weight:bold;color:#ce175a"><?php echo $system_qty !=='' ? number_format($system_qty, 2, '.', '') : '' ?></td>
                        <td class="text-right" style="font-size:14px;font-weight:bold;color:red">
                          <?php echo $diff !=='' ? number_format($diff, 2, '.', '') : '' ?>
                        </td>
                      <?php else: ?>
                        <td>&nbsp;</td><td>&nbsp;</td>
                      <?php endif; ?>
                      <td class="text-right"><?php echo $hsn_sac_code ?></td>
                      <td class="text-left"><?php echo $rack_number ?></td>
                    </tr>
                  <?php
                    $cntr++;
                    endforeach;
                    $tot_phy_qty = $audit_totals['totPhyQty'];
                    $tot_sys_qty = $audit_totals['totSysQty'];
                    $tot_dif_qty = $tot_phy_qty - $tot_sys_qty;
                  ?>
                  <tr>
                    <td colspan="5" style="text-align:right;font-weight:bold;">PAGE TOTALS</td>
                    <td style="text-align:right;font-weight:bold;"><?php echo number_format($page_tot_phy_qty,2,'.','') ?></td>
                    <?php if((int)$_SESSION['utype'] === 3): ?>
                      <td style="text-align:right;font-weight:bold;"><?php echo number_format($page_tot_sys_qty,2,'.','') ?></td>
                      <td style="text-align:right;font-weight:bold;"><?php echo number_format($page_tot_dif_qty,2,'.','') ?></td>
                    <?php else: ?>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    <?php endif; ?>
                  </tr>

                  <tr>
                    <td colspan="5" style="text-align:right;font-weight:bold;">AUDIT TOTALS</td>
                    <td style="text-align:right;font-weight:bold;"><?php echo number_format($tot_phy_qty,2,'.','') ?></td>
                    <?php if((int)$_SESSION['utype'] === 3): ?>
                      <td style="text-align:right;font-weight:bold;"><?php echo number_format($tot_sys_qty,2,'.','') ?></td>
                      <td style="text-align:right;font-weight:bold;"><?php echo number_format($tot_dif_qty,2,'.','') ?></td>
                    <?php else: ?>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    <?php endif; ?>
                  </tr>
                <?php else: ?>
                  <td colspan="9" align="center" class="red">No records are available. Change the above Filters and try again.</td>
                <?php endif; ?>
              </tbody>
            </table>
            <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
            <input type="hidden" id="aC" name="aC" value="<?php echo $audit_code ?>" />
            <input type="hidden" id="locationCode" name="locationCode" value="<?php echo $audit_location_code ?>" />
          </div>
        </form>
      </div>
    </section>
  </div>
</div>