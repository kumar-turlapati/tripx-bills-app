<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message(); ?>
        <div class="global-links actionButtons clearfix"> 
          <div class="pull-right text-right">
            <a href="/sales/list" class="btn btn-default"><i class="fa fa-book"></i> Gatepass Register</a>&nbsp;&nbsp;
          </div>
        </div>        
        <form id="gatePassForm" method="POST" autocomplete="off">
          <div class="table-responsive">
            <table class="table table-hover font12" style="overflow: hidden; border-top:none;border-left:none;border-right:none;margin-bottom: 0px;">
              <thead>
                <tr>
                  <td style="vertical-align:middle;border-bottom: none;font-size:18px;font-weight:bold;border-right:none;border-left:none;border-top:none;text-align:right;width:10%;" id="scanText">QwikBills Invoice No.</td>
                  <td style="vertical-align:middle;border-bottom: none;border-right:none;border-left:none;border-top:none;width:10%;">
                    <input
                      type="text"
                      id="invoiceNo"
                      name="invoiceNo"
                      style="font-size:16px;font-weight:bold;border:1px dashed #225992;padding-left:5px;font-weight:bold;width:150px;"
                      maxlength="15"
                    />
                  </td>
                  <td style="vertical-align:middle;border-bottom: none;font-size:15px;font-weight:bold;border-right:none;border-left:none;border-top:none;text-align:right;width:8%;">Store/Location Name</td>
                  <td style="vertical-align:middle;border-right:none;border-bottom: none;border-left:none;border-top:none;width:15%;text-align:left;">
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($client_locations as $key=>$value): 
                          if($location_code === $key) {
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
                  </td>
                </tr>
              </thead>
            </table>
          </div>
          <div style="margin-top: 20px;text-align: center;">
            <button class="btn btn-primary" name="op" value="GetInvoiceNo">
              <i class="fa fa-share" aria-hidden="true"></i> Scan Products
            </button>&nbsp;
            <button class="btn btn-danger" onclick="window.location.href='/get-invoice-no'">
              <i class="fa fa-times"></i> Cancel
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>