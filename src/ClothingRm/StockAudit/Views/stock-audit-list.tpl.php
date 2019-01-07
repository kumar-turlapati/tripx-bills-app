<?php
  use Atawa\Utilities;

  $pagination_url = $page_url = '/stock-audit/register';
  $query_params = '';  
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = $default_location;
  }  
  if($query_params != '') {
    $query_params = '?'.implode('&', $query_params);
  }
  $audit_status_a = [1 => 'OPEN', 2 => 'LOCKED', 3 => 'APPROVED', 4 => 'DELETED'];
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message(); ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
              <a href="/stock-audit/create" class="btn btn-default">
                <i class="fa fa-file-text-o"></i> New Stock Audit
              </a>
          </div>
        </div>
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
                <th width="5%" class="text-center">Sno.</th>
                <th width="15%" class="text-center">Audit Type</th>
                <th width="15%" class="text-center">CB Date</th>               
                <th width="15%" class="text-center">Start Date</th>
                <th width="10%" class="text-center">Status</th>
                <th width="30%" class="text-center">Options</th>
              </tr>
            </thead>
            <tbody>
              <?php if(count($audits)>0): ?>
                <?php
                  $cntr = $sl_no;
                  foreach($audits as $audit_details):
                    $audit_type = $audit_details['auditType'] === 'ext' ? 'External' : 'Internal';
                    $cb_date = date("d-m-Y", strtotime($audit_details['cbDate']));
                    $start_date = date("d-m-Y", strtotime($audit_details['auditStartDate']));
                    $status = isset($audit_status_a[$audit_details['status']]) ?  $audit_status_a[$audit_details['status']] : 'INVALID';
                    $audit_code = $audit_details['auditCode'];
                ?>
                  <tr>
                    <td style="text-align:right;vertical-align:middle;"><?php echo $cntr ?></td>
                    <td style="text-align:center;vertical-align:middle;"><?php echo $audit_type ?></td>
                    <td style="text-align:center;vertical-align:middle;"><?php echo $cb_date ?></td>
                    <td style="text-align:center;vertical-align:middle;"><?php echo $start_date ?></td>
                    <td style="text-align:right;vertical-align:middle;"><?php echo $status ?></td>
                    <td style="vertical-align:middle;text-align:center;">
                      <div class="btn-actions-group">
                        <?php if((int)$audit_details['status'] === 1 || Utilities::is_admin()): ?>
                          <?php /*
                          <a class="btn btn-warning" href="/stock-audit/update/<?php echo $audit_code ?>" title="Edit Audit Details">
                            <i class="fa fa-pencil"></i>
                          </a> */ ?>
                          <a class="btn btn-success" href="/stock-audit/items/<?php echo $audit_code ?>" title="Manage Items">
                            <i class="fa fa-cubes"></i>
                          </a>
                        <?php endif; ?>
                        <?php if(Utilities::is_admin()): ?>
                          <?php /*
                          <a class="btn btn-danger" href="/stock-audit/remove/<?php echo $audit_code ?>" title="Delete Audit">
                            <i class="fa fa-times"></i>
                          </a> */ ?>
                        <?php endif; ?>
                        <a class="btn btn-primary" target="_blank" href="/stock-audit/print/<?php echo $audit_code ?>" title="Print Audit Report">
                          <i class="fa fa-print"></i>
                        </a>                        
                      </div>
                    </td>
                  </tr>
                <?php
                  $cntr++;
                  endforeach;
                ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" align="center" class="red">No records are available. Change the above Filters and try again.</td>
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