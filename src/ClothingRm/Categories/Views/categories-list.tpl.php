<?php 
  $query_params = '';  
  if(isset($search_params['lc']) && $search_params['lc'] !='') {
    $location_code = $search_params['lc'];
    $query_params[] = 'locationCode='.$location_code;
  } else {
    $location_code = '';
  } 
  if($query_params !== '') {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = '/categories/list';
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/category/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> Create Category 
            </a> 
          </div>
        </div>
        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>

        <div class="filters-block">
          <div id="filters-form">
           <form class="form-validate form-horizontal" method="POST" action="<?php echo $page_url ?>">
            <div class="form-group">
              <div class="col-sm-12 col-md-1 col-lg-1 text-right">
                <label class="control-label text-right"><b>Filter by</b></label>          
              </div>
              <div class="col-sm-12 col-md-2 col-lg-2">
                <div class="select-wrap">
                  <select class="form-control" name="locationCode" id="locationCode">
                    <?php 
                      foreach($client_locations as $location_key=>$value):
                        $location_key_a = explode('`', $location_key);
                        if($location_code === $location_key_a[0]) {
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
              <div class="col-sm-12 col-md-3 col-lg-3">
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
                <th width="5%" class="text-center">Sno</th>
                <th width="30%" class="text-center">Category name</th>
                <th width="20%" class="text-center">Store name</th>
                <th width="5%" class="text-center">Status</th>                
                <th width="10%" class="text-center">Products / Category</th>
                <th width="10%" class="text-center">Options</th>
              </tr>
            </thead>
            <tbody>
            <?php if(is_array($categories) && count($categories) > 0): ?>
                <?php 
                  $cntr = $sl_no;
                  $total_item_count = 0;
                  foreach($categories as $category_details):
                    $total_item_count += $category_details['totalItems'];
                    if($category_details['categoryName'] !== '') {
                      $category_name = $category_details['categoryName'];
                    } else {
                      $category_name = '';
                    }
                    if($category_details['categoryCode'] !== '') {
                      $category_code = $category_details['categoryCode'];
                    } else {
                      $category_code = '';
                    }
                    if($category_details['totalItems'] > 0) {
                      $total_items = $category_details['totalItems'];
                    } else {
                      $total_items = '';
                    }
                    if((int)$category_details['status'] === 0) {
                      $status = 'Inactive';
                    } elseif((int)$category_details['status'] === 1) {
                      $status = 'Active';
                    }
                    $location_code = isset($location_codes[$category_details['locationID']]) ? $location_codes[$category_details['locationID']] : '';
                    $location_name = isset($location_ids[$category_details['locationID']]) ? $location_ids[$category_details['locationID']] : '';
                ?>
                  <tr class="text-right font11">
                    <td class="text-right valign-middle"><?php echo $cntr ?></td>
                    <td class="text-left valign-middle"><?php echo $category_name ?></td>
                    <td class="text-left valign-middle"><?php echo $location_name ?></td>
                    <td class="text-left valign-middle"><?php echo $status ?></td>
                    <td class="text-right text-bold valign-middle"><?php echo $total_items ?></td>
                    <td class="valign-middle">
                      <div class="btn-actions-group text-right">
                        <?php if($category_code !== ''): ?>
                          <a class="btn btn-primary" href="/category/update/<?php echo $category_code ?>?lc=<?php echo $location_code ?>" title="Edit this category">
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
                <tr>
                  <td colspan="4" class="text-bold text-right font14">Total Products</td>
                  <td class="text-bold text-right font14"><?php echo number_format($total_item_count) ?></td>
                  <td>&nbsp;</td>
                </tr>
            <?php else: ?>
                <tr><td colspan="6" align="center" class="font14">No categories are available.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>