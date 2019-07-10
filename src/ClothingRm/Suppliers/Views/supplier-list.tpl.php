<?php
  use Atawa\Utilities;
  $query_params = [];  
  if(isset($search_params['suppName']) && $search_params['suppName'] !='') {
    $suppName = $search_params['suppName'];
    $query_params[] = 'suppName='.$suppName;
  } else {
    $suppName = '';
  }
  if(isset($search_params['category']) && $search_params['category'] !='' ) {
    $category = $search_params['category'];
    $query_params[] = 'category='.$category;
  } else {
    $category = '';
  }
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = '/suppliers/list';
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
            <a href="/suppliers/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Supplier 
            </a> 
          </div>
        </div>
        <div class="filters-block">
          <div id="filters-form">
            <form class="form-validate form-horizontal" method="POST" action="/suppliers/list" autocomplete="off">
              <div class="form-group">
                <div class="col-sm-12 col-md-2 col-lg-2"><b>Filter by</b></div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                   <input placeholder="Supplier Name" type="text" name="suppName" id="suppName" class="form-control" value="<?php echo $suppName ?>">
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
                <th width="5%"  class="text-center valign-middle">Sno.</th>
                <th width="25%" class="text-center valign-middle">Supplier name</th>
                <th width="10%" class="text-center valign-middle">GST No.</span></th>
                <th width="15%" class="text-center valign-middle">Contact person</th>
                <th width="15%" class="text-center valign-middle">Phone(s)</th>
                <th width="15%" class="text-center valign-middle">Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php if(is_array($suppliers) && count($suppliers)>0): ?>
              <?php
                $cntr = $sl_no;
                foreach($suppliers as $supplier_details):
                  $supplier_code = $supplier_details['supplierCode'];
                  $reg_no = $supplier_details['dlNo'];
                  $gst_no = $supplier_details['tinNo'];
              ?>
                  <tr class="text-uppercase text-right font11">
                    <td class="text-center valign-middle"><?php echo $cntr ?></td>
                    <td class="text-left med-name valign-middle"><?php echo $supplier_details['supplierName'] ?></td>
                    <td class="text-bold text-left valign-middle"><?php echo $gst_no ?></td>
                    <td class="text-left valign-middle"><?php echo $supplier_details['contactPersonName'] ?></td>
                    <td class="text-left valign-middle"><?php echo $supplier_details['mobileNo'] ?></td>
                    <td>
                      <div class="btn-actions-group">
                        <?php if($supplier_code !== ''): ?>
                          <a class="btn btn-primary" href="/suppliers/update/<?php echo $supplier_code ?>" title="Edit Supplier">
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
                <td colspan="6" align="center"><b>No data is available.</b></td>
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