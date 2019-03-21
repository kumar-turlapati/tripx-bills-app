<?php
  use Atawa\Utilities;
  $page_url = $pagination_url = '/stock-adj-reasons/list';  
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/sales-category/add" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Sales Category
            </a> 
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="font14">
              <tr>
                <th width="5%" class="text-center">Sno.</th>
                <th width="50%" class="text-center">Category Name</th>
                <th width="10%" class="text-center">Status</th>
                <th width="5%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php if(count($sales_categories)>0) { ?>
              <?php 
                $cntr = 1;
                foreach($sales_categories as $category_details):
                  $category_name = $category_details['salesCategoryName'];
                  $unique_code = $category_details['salesCategoryCode'];
                  if((int)$category_details['status'] === 1) {
                    $status = 'Active';
                  } else {
                    $status = 'Inactive';
                  }
              ?>
                <tr class="text-right font11">
                  <td class="text-right valign-middle"><?php echo $cntr ?></td>
                  <td class="text-left valign-middle"><?php echo $category_name ?></td>
                  <td class="text-center valign-middle"><?php echo $status ?></td>
                  <td class="valign-middle" align="center">
                    <div class="btn-actions-group">
                      <?php if($unique_code !== ''): ?>
                        <a class="btn btn-primary" href="/sales-category/update/<?php echo $unique_code ?>" title="Edit Sales Category">
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
                <td colspan="4" align="center"><b>No records are available.</b></td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>