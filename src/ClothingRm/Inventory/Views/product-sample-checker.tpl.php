<?php
?>

<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <form class="form-validate form-horizontal" method="POST" autocomplete="off">
          <div class="panel" style="margin-bottom:0px;">
            <div class= "panel-body">
              <div class="form-group">
                <div class="col-sm-12 col-md-12 col-lg-12">
                  <label class="control-label">Scan the barcode</label>
                  <input
                    type="text"
                    id="samplesBarcode"
                    class="samplesBarcode"
                    style="font-size:16px;font-weight:bold;border:1px dashed #225992;padding-left:5px;font-weight:bold;width:70%"
                    maxlength="13"
                  />
                </div>
              </div>
            </div>
          </div>
          <div class="table-responsive" id="sampleShower" style="display: none;">
            <table class="table table-striped table-hover font12">
              <thead>
                <tr>
                  <th width="35%" class="text-center">Item name</th>                  
                  <th width="15%" class="text-center">Category</th>                  
                  <th width="25%" class="text-center">Available Qty.</th>
                  <th width="25%" class="text-center">Brand Name</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td id="itemName" align="center" style="font-size:20px;color:#225992;vertical-align:middle">&nbsp;</td>
                  <td id="categoryName" align="center" style="font-size:20px;color:#225992;vertical-align:middle">&nbsp;</td>
                  <td id="avaQty" align="center" style="font-size:50px;vertical-align:middle;color:#5CDB95">&nbsp;</td>
                  <td id="brandName" align="center" style="font-size:20px;vertical-align:middle;color:#FC4445">&nbsp;</td>
                </tr>
                <tr>
                  <th width="35%" class="text-center">Exmill (in Rs.)</th>                  
                  <th width="15%" class="text-center">Wholesale (in Rs.)</th>                  
                  <th width="25%" class="text-center">MRP (in Rs.)</th>
                  <th width="25%" class="text-center">Online (in Rs.)</th>
                </tr>
                <tr>
                  <td id="exMill" align="center" style="font-size:20px;color:#225992;vertical-align:middle;font-weight: bold;">&nbsp;</td>
                  <td id="wholesale" align="center" style="font-size:20px;vertical-align:middle;color:#5CDB95;font-weight: bold;">&nbsp;</td>
                  <td id="mrp" align="center" style="font-size:20px;vertical-align:middle;color:#FC4445;font-weight: bold;">&nbsp;</td>
                  <td id="online" align="center" style="font-size:20px;vertical-align:middle;color:#FC4445;font-weight: bold;">&nbsp;</td>
                </tr>                
              </tbody>
            </table>
          </div>         
        </form>
      </div>
    </section>
  </div>
</div>