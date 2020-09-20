<?php 
  use Atawa\Config\Config;
  $catalog_domain = Config::get_catalog_domain();

  $pagination_url = $page_url = '/ecom/categories/list';
  $query_params = [];
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message(); ?>
        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/ecom/app-content/create" class="btn btn-default">
              <i class="fa fa-plus"></i> New Content
            </a>
          </div>
        </div>
        <div class="table-responsive">
          <?php if(count($content) > 0): ?>
            <table class="table table-striped table-hover">
              <thead>
                <tr class="font12">
                  <th width="5%" class="text-center">Sno.</th>
                  <th width="10%" class="text-center">Content title</th>
                  <th width="10%" class="text-center">Section</th>
                  <th width="25%" class="text-center">Catalog name</th>
                  <th width="25%" class="text-center">Item name</th>
                  <th width="5%" class="text-center">Status</th>
                  <th width="10%" class="text-center">Options</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  $cntr = 1;
                  foreach($content as $content_details):
                    $content_title = $content_details['contentTitle'];
                    $catalog_name = $content_details['catalogName'];
                    $item_name = $content_details['itemName'];
                    $status = (int)$content_details['status'] === 0 ? 'Inactive' : 'Active';
                    $update_url = '/ecom/app-content/update/'.$content_details['contentID'];
                    if($content_details['contentCategory'] === 'main-banner') {
                      $section_name = 'Main Banner';
                    } elseif($content_details['contentCategory'] === 'hot-sellers') {
                      $section_name = 'Hot Sellers';
                    } elseif($content_details['contentCategory'] === 'top-brands') {
                      $section_name = 'Top Brands';
                    } else {
                      $section_name = '';
                    }
                ?>
                  <tr class="text-uppercase text-right font11">
                    <td class="text-right valign-middle"><?php echo $cntr ?></td>
                    <td class="text-left valign-middle"><?php echo $content_title ?></td>
                    <td class="text-center valign-middle"><?php echo $section_name ?></td>
                    <td class="text-left valign-middle"><?php echo $catalog_name ?></td>
                    <td class="text-left valign-middle"><?php echo $item_name ?></td>
                    <td class="text-right valign-middle"><?php echo $status ?></td>
                    <td class="text-right valign-middle">
                      <div class="btn-actions-group">
                        <a class="btn btn-info" href="<?php echo $update_url ?>" title="Edit content">
                          <i class="fa fa-pencil" aria-hidden="true"></i>
                        </a>
                        <a class="btn btn-danger delEcomContent" href="/ecom/app-content/delete/<?php echo $content_details['contentID'] ?>" title="Delete Content">
                          <i class="fa fa-times" aria-hidden="true"></i>
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
          <?php else: ?>
            <div style="text-align:center;margin-top:10px;font-weight:bold;color:red;font-size:14px;">No data available !</div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </div>
</div>