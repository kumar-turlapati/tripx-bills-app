<?php
  use Atawa\Utilities;

  $transport_mode = isset($form_data['transportMode']) ? $form_data['transportMode'] : '';
  $distance = isset($form_data['distance']) ? $form_data['distance'] : '';
  $transporter_id = isset($form_data['transporterId']) ? $form_data['transporterId'] : '';
  $transporter_name = isset($form_data['transporterName']) ? $form_data['transporterName'] : '';

  $vehicle_type = isset($form_data['vehicleType']) ? $form_data['vehicleType'] : '';
  $vehicle_no = isset($form_data['vehicleNo']) ? $form_data['vehicleNo'] : '';
  $transport_doc_no = isset($form_data['transportDocNo']) ? $form_data['transportDocNo'] : '';
  $transport_doc_date = isset($form_data['transportDocDate']) && $form_data['transportDocDate'] !== '' ? date("d-m-Y", strtotime($form_data['transportDocDate'])) : date("d-m-Y");

  if((int)$transport_mode === 1) {
    $road_transport_class = '';
    $other_transport_class = 'style="display:none"';
  } elseif((int)$transport_mode>=2 && (int)$transport_mode <=4 ) {
    $road_transport_class = 'style="display:none"';
    $other_transport_class = '';
  } else {
    $road_transport_class = $other_transport_class = 'style="display: none;"';
  }

  // dump($invoice_details);
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/sales/list" class="btn btn-default">
              <i class="fa fa-book"></i> Sales Register
            </a>&nbsp;
            <a href="/einvoices/list" class="btn btn-default">
              <i class="fa fa-book"></i> eInvoices Register
            </a>            
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="eWayBillByIrn">
          <?php if(count($invoice_details) > 0): ?>
            <div class="table-responsive">
              <table class="table table-striped table-hover font12" style="margin-bottom: 0px;">
                <thead>
                  <tr>
                    <th width="15%"  class="text-center">Invoice no.</th>                  
                    <th width="12%" class="text-center">Invoice date</th>
                    <th width="10%" class="text-center">Invoice value<br />( in Rs. )</th>
                    <th width="33%"  class="text-center">Customer name</th>
                    <th width="10%" class="text-center">GST no.</th>
                    <th width="20%" class="text-center">Location / Store name</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="font11" align="center">
                    <td style="font-size:16px; color: #D83A56; font-weight: bold;">
                      <a href="/sales/view-invoice/<?php echo $invoice_details['invoiceCode'] ?>">
                        <?php echo $invoice_details['billNo'] ?>
                        &nbsp;<i class="fa fa-window-restore" style="text-decoration: none;" aria-hidden="true"></i>
                      </a>
                    </td>
                    <td style="font-size:16px; color: #D83A56; font-weight: bold;"><?php echo date("d-m-Y", strtotime($invoice_details['invoiceDate'])) ?></td>
                    <td style="font-size:16px; color: #D83A56; font-weight: bold;"><?php echo number_format($invoice_details['netPay'], 2,'.','') ?></td>
                    <td style="font-size:16px; color: #D83A56; font-weight: bold;"><?php echo $invoice_details['customerName'] ?></td>
                    <td style="font-size:16px; color: #D83A56; font-weight: bold;"><?php echo $invoice_details['customerGstNo'] ?></td>
                    <td style="font-size:16px; color: #D83A56; font-weight: bold;"><?php echo $invoice_details['locationName'] ?></td>
                  </tr>
                  <tr style="background: rgba(52, 73, 94, 0.90); color: #ecf0f1; font-weight: bold; text-align: center;">
                    <td>Our Pincode</td>
                    <td>Buyer Pincode</td>
                    <td colspan="4">IRN</td>
                  </tr>
                  <tr>
                    <td style="font-weight: bold; text-align: center; color: #D83A56; font-size: 16px;"><?php echo $invoice_details['locPincode'] ?></td>
                    <td style="font-weight: bold; text-align: center; color: #D83A56; font-size: 16px;"><?php echo $invoice_details['pincode'] ?></td>
                    <td style="font-weight: bold; text-align: center; color: #D83A56; font-size: 16px;" colspan="4" align="center"><?php echo $irn ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
          <br />
          <div class="panel" style="margin-bottom:10px;">
            <div class="panel-body">
              <div class="form-group">
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label labelStyle">Transporter Id*</label>
                  <input 
                    type="text" 
                    class="form-control noEnterKey" 
                    name="transporterId" 
                    id="transporterId" 
                    value="<?php echo $transporter_id ?>"
                    maxlength="15"
                  />
                  <?php if(isset($form_errors['transporterId'])): ?>
                    <span class="error"><?php echo $form_errors['transporterId'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label labelStyle">Distance*&nbsp;
                  <a 
                    href="https://einvoice1.gst.gov.in/Others/GetPinCodeDistance" 
                    target="_blank" 
                    style="font-size:14px; color: green; font-weight: bold; padding-left: 10px;">
                      Distance finder&nbsp;<i class="fa fa-window-restore" style="text-decoration: none;" aria-hidden="true"></i>
                  </a>
                  </label>
                  <input 
                    type="text" 
                    class="form-control noEnterKey" 
                    name="distance" 
                    id="distance" 
                    value="<?php echo $distance ?>"
                    maxlength="10"
                  />
                  <?php if(isset($form_errors['distance'])): ?>
                    <span class="error"><?php echo $form_errors['distance'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Mode of Transport</label>
                  <div class="select-wrap">
                    <select class="form-control" name="transportMode" id="transportMode">
                      <?php 
                        foreach($transport_modes as $key=>$value):
                          if((int)$transport_mode === (int)$key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <?php if(isset($form_errors['transportMode'])): ?>
                    <span class="error"><?php echo $form_errors['transportMode'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label labelStyle">Transporter Name</label>
                  <input 
                    type="text" 
                    class="form-control noEnterKey" 
                    name="transporterName" 
                    id="transporterName" 
                    value="<?php echo $transporter_name ?>"
                    maxlength="100"
                  />
                  <?php if(isset($form_errors['transporterName'])): ?>
                    <span class="error"><?php echo $form_errors['transporterName'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="form-group">
                <div id="transportByRoad" <?php echo $road_transport_class ?>>
                  <div class="col-sm-12 col-md-3 col-lg-3">
                    <label class="control-label labelStyle">Vehicle Type</label>
                    <div class="select-wrap">
                      <select class="form-control" name="vehicleType" id="vehicleType">
                        <?php 
                          foreach($vehicle_types as $key=>$value):
                            if($vehicle_type === $key) {
                              $selected = 'selected="selected"';
                            } else {
                              $selected = '';
                            }
                        ?>
                          <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <?php if(isset($form_errors['vehicleType'])): ?>
                      <span class="error"><?php echo $form_errors['vehicleType'] ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="col-sm-12 col-md-3 col-lg-3">
                    <label class="control-label labelStyle">Vehicle No.</label>
                    <input 
                      type="text" 
                      class="form-control noEnterKey" 
                      name="vehicleNo" 
                      id="vehicleNo" 
                      value="<?php echo $vehicle_no ?>"
                      maxlength="10"
                    />
                    <?php if(isset($form_errors['vehicleNo'])): ?>
                      <span class="error"><?php echo $form_errors['vehicleNo'] ?></span>
                    <?php endif; ?>
                  </div>
                </div>
                <div id="transportByOtherModes" <?php echo $other_transport_class ?>>
                  <div class="col-sm-12 col-md-3 col-lg-3">
                    <label class="control-label labelStyle">Transport Document No.</label>
                    <input 
                      type="text" 
                      class="form-control noEnterKey" 
                      name="transportDocNumber" 
                      id="transportDocNumber" 
                      value="<?php echo '' ?>"
                      maxlength="10"
                    />
                    <?php if(isset($form_errors['transportDocNumber'])): ?>
                      <span class="error"><?php echo $form_errors['transportDocNumber'] ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="col-sm-12 col-md-3 col-lg-3">
                    <label class="control-label labelStyle">Transport Document Date</label>
                    <div class="col-lg-12" style="padding-left:0px;">
                      <div class="input-append date" data-date="<?php echo $transport_doc_date ?>" data-date-format="dd-mm-yyyy">
                        <input class="span2" value="<?php echo $transport_doc_date ?>" size="16" type="text" readonly name="transportDocDate" id="transportDocDate" />
                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                      </div>
                      <?php if(isset($form_errors['transportDocDate'])): ?>
                        <span class="error"><?php echo $form_errors['transportDocDate'] ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <ol style="font-size:12px; padding-left: 15px; padding-bottom: 10px; padding-top: 10px;">
            <li>E-way Bill is not generated for document types of Debit Note and Credit Note and Services.</li>
            <li>E Way Bill can be generated provided at least HSN of one item belongs to goods.</li>
            <li>If only Transporter Id is provided, then only Part-A is generated.</li>
            <li>If mode of transportation is "Road", then the Vehicle number and vehicle type should be passed. If mode of transportation is Ship, Air, Rail, then the transport document number and date should be passed.</li>
            <li>The Vehicle no. should match with specified format and exist in Vahan database.</li>
            <li>E-Waybill will not be generated if the Supplier or Recipient GSTIN is blocked due to non-filing of Returns.</li>
          </ol>
          <div class="text-center">
            <button class="btn btn-success" id="cancelIrn">
              <i class="fa fa-save"></i> Generate eWayBill
            </button>
            <button class="btn btn-danger cancelButton" id="cancelIrnCancel">
              <i class="fa fa-times"></i> Reset
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>