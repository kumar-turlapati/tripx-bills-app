<?php
  use Atawa\Utilities;
  use Atawa\Constants;

  $current_date = date("d-m-Y");
  $pagination_url = '/sales/list';
  $query_params = [];

  if(isset($search_params['fromDate']) && $search_params['fromDate'] !='') {
    $fromDate = $search_params['fromDate'];
    $query_params[] = 'fromDate='.$fromDate;
  } else {
    $fromDate = date("Y-m-01");
  }
  if(isset($search_params['toDate']) && $search_params['toDate'] !='' ) {
    $toDate = $search_params['toDate'];
    $query_params[] = 'toDate='.$toDate;
  } else {
    $toDate = $current_date;
  }
  if(isset($search_params['paymentMethod']) && $search_params['paymentMethod'] !='' ) {
    $paymentMethod = $search_params['paymentMethod'];
    $query_params[] = 'paymentMethod='.$search_params['paymentMethod'];
  } else {
    $paymentMethod = 99;
  }
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = '';
  }  
  if(isset($search_params['saExecutiveCode']) && $search_params['saExecutiveCode'] !== '' ) {
    $saExecutiveCode = $search_params['saExecutiveCode'];
    $query_params[] = 'saExecutiveCode='.$saExecutiveCode;
  } else {
    $saExecutiveCode = '';
  }
  if(isset($search_params['walletID']) && $search_params['walletID'] !== '' ) {
    $wallet_id = $search_params['walletID'];
    $query_params[] = 'walletID='.$wallet_id;
  } else {
    $wallet_id = '';
  }
  if(isset($search_params['custName']) && $search_params['custName'] !== '' ) {
    $customer_name = $search_params['custName'];
    $query_params[] = 'custName='.$customer_name;
  } else {
    $customer_name = '';
  }
  if(isset($search_params['billNo']) && $search_params['billNo'] !== '' ) {
    $bill_no = $search_params['billNo'];
    $query_params[] = 'billNo='.$bill_no;
  } else {
    $bill_no = '';
  }
  if(isset($search_params['documentNo']) && $search_params['documentNo'] !== '' ) {
    $document_no = $search_params['documentNo'];
    $query_params[] = 'documentNo='.$document_no;
  } else {
    $document_no = '';
  }
  if(isset($search_params['brandName']) && $search_params['brandName'] !== '' ) {
    $brand_name = $search_params['brandName'];
    $query_params[] = 'brandName='.$brand_name;
  } else {
    $brand_name = '';
  }

  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = '/sales/list';

  // dump($search_params);
  // dump($sales);
  // exit;
  // dump($fromDate, $toDate);
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/einvoices/list" class="btn btn-default"><i class="fa fa-book"></i> eInvoices Register</a>
            <a href="/sales/entry-with-barcode" class="btn btn-default"><i class="fa fa-inr"></i> Sales Entry - Barcode</a>
            <a href="/sales/entry" class="btn btn-default"><i class="fa fa-inr"></i> Sales Entry - Manual</a>
            <a href="/sales-entry/combos" class="btn btn-default"><i class="fa fa-shopping-basket" aria-hidden="true"></i> Sales Entry - Combo</a>
          </div>
        </div>
  		  <div class="filters-block">
    			<div id="filters-form">
    			  <form class="form-validate form-horizontal" method="GET" action="/sales/list">
      				<div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1">Filter by</div>
      				  <div class="col-sm-12 col-md-2 col-lg-2">
      						<div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
      						  <input class="span2" size="16" type="text" readonly name="fromDate" id="fromDate" value="<?php echo $fromDate ?>" />
      						  <span class="add-on"><i class="fa fa-calendar"></i></span>
      					  </div>
      				  </div>
      				  <div class="col-sm-12 col-md-2 col-lg-2">
      						<div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
      						  <input class="span2" size="16" type="text" readonly name="toDate" id="toDate" value="<?php echo $toDate ?>" />
      						  <span class="add-on"><i class="fa fa-calendar"></i></span>
      						</div>
      				  </div>
      				  <div class="col-sm-12 col-md-2 col-lg-2">
        					<div class="select-wrap">
        						<select class="form-control" name="paymentMethod" id="paymentMethod">
        						  <?php 
                        foreach($payment_methods as $key=>$value):
                          if((int)$paymentMethod === (int)$key) {
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
                  <div class="select-wrap">
                    <select class="form-control" name="saExecutiveCode" id="saExecutiveCode">
                      <?php 
                        foreach($sa_executives as $key=>$value):
                          if($saExecutiveCode === $key) {
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
      				</div>
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1">&nbsp;</div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="walletID" id="walletID">
                      <?php 
                        foreach($wallets as $key => $value):
                          if((int)$wallet_id == (int)$key) {
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
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <input 
                    placeholder="Customer name" 
                    type="text" 
                    name="custName" 
                    id="custName" 
                    class="form-control cnameAc" 
                    value="<?php echo $customer_name ?>"
                  />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input 
                    placeholder="Bill no." 
                    type="text" 
                    name="billNo" 
                    id="billNo" 
                    class="form-control" 
                    value="<?php echo $bill_no ?>"
                  />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2 m-bot15">
                  <input 
                    placeholder="GST Document no." 
                    type="text" 
                    name="documentNo" 
                    id="documentNo" 
                    class="form-control" 
                    value="<?php echo $document_no ?>"
                  />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2 m-bot15">
                  <input 
                    placeholder="Brand name" 
                    type="text" 
                    name="brandName" 
                    id="brandName" 
                    class="form-control" 
                    value="<?php echo $brand_name ?>"
                  />
                </div>                
                <div align="center" style="clear: both;">         
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
                <th width="3%"  class="text-center">Sno.</th>
                <th width="15%" class="text-center">Customer<br />Name</th>
                <th width="7%" class="text-center">Payment<br />Method</th>                
                <th width="9%" class="text-center">Invoice No. &amp; Date</th>
                <th width="7%" class="text-center">Invoice Value<br />(in Rs.)</th>
                <th width="10%" class="text-center">GST Document<br />No.</th>
                <th width="12%" class="text-center">Brand Name</th>
                <th width="28%" class="text-center">Actions</th>

                <?php /*<th width="7%" class="text-center">Mobile<br />Number</th>
                <th width="7%" class="text-center">Bill Amount<br />(in Rs.)</th>
                <th width="6%" class="text-center">Discount<br />(in Rs.)</th>
                <th width="7%" class="text-center">Taxable<br />(in Rs.)</th>
                <th width="7%" class="text-center">GST<br />(in Rs.)</th> */?>
              </tr>
            </thead>
            <tbody>
              <?php 
                $cntr = $sl_no;
                $tot_bill_amount = $tot_disc_amount = $tot_amount = $tot_tax_amount = 0;
                $tot_round_off = $tot_net_pay = 0;
                foreach($sales as $sales_details):
                  // dump($sales_details);
                  // exit;
                  $sales_code = $sales_details['invoiceCode'];
                  $invoice_date = date("d-m-Y", strtotime($sales_details['invoiceDate']));
                  $mobile_number = $sales_details['mobileNo'];
                  $is_combo_bill = $sales_details['isComboBill'];
                  $location_code = isset($location_codes[$sales_details['locationID']]) ? $location_codes[$sales_details['locationID']] : '';
                  if($sales_details['customerName'] !== '') {
                    $customer_name = $sales_details['customerName'];
                    $customer_name_short = strlen($customer_name) > 20 ? substr($customer_name,0,20).'..' : $customer_name;
                  } elseif($sales_details['tmpCustName'] !== '') {
                    $customer_name = $sales_details['tmpCustName'];
                    $customer_name_short = strlen($customer_name) > 20 ? substr($customer_name,0,20).'..' : $customer_name;
                  } else {
                    $customer_name = $customer_name_short = '';
                  }
                  if($sales_details['paymentMethod'] !== '') {
                    $payment_method = Constants::$PAYMENT_METHODS_RC_SHORT[$sales_details['paymentMethod']];
                  } else {
                    $payment_method = 'Invalid';
                  }
                  $customer_code = $sales_details['customerCode'];
                  $bill_amount = $sales_details['billAmount'];
                  $discount_amount = $sales_details['discountAmount'];
                  $taxable_amount = $bill_amount - $discount_amount;
                  $tot_bill_amount += $bill_amount;
                  $tot_disc_amount += $discount_amount;
                  $tot_amount += $taxable_amount;
                  $tot_tax_amount += $sales_details['taxAmount'];
                  $tot_round_off += $sales_details['roundOff'];
                  $tot_net_pay += $sales_details['netPay'];
                  $tax_calc_option = $sales_details['taxCalcOption'] === 'e' ? 'E' : 'I';
                  $gatepass_no = (int)$sales_details['gatePassNo'] > 0 ? $sales_details['gatePassNo'] : '';
                  $gatepass_date = $sales_details['gatePassDateTime'] !== '0000-00-00 00:00:00' ? date("d-m-Y h:ia", strtotime($sales_details['gatePassDateTime'])) : '';
                  $gatepass_status = (int)$sales_details['gatePassStatus'] === 1 ? 'Generated' : 'Pending'; 
                  $transporter_name = $sales_details['transporterName'];
                  $gst_no = $sales_details['gstNo'];

                  $gst_doc_no = $sales_details['gstDocNo'] !== '' ? $sales_details['gstDocNo'] : $sales_details['customBillNo'];
                  $brand_name = $sales_details['brandName'];
                  $gst_irn = $sales_details['gstIrn'];
              ?>
                  <tr class="text-uppercase text-right font11">
                    <td class="valign-middle"><?php echo $cntr ?></td>
                    <td class="text-left med-name valign-middle" title="<?php echo $customer_name;echo $mobile_number !== '' && !is_null($mobile_number)  ? ' / '.$mobile_number : '' ?>">
                      <?php if($customer_code !== ''): ?>
                        <a href="/customers/update/<?php echo $customer_code ?>" target="_blank" class="blue"><?php echo $customer_name_short ?></a>
                      <?php else: ?>
                        <?php echo $customer_name_short ?>
                      <?php endif; ?>
                    </td>
                    <td class="text-left med-name valign-middle">
                      <span style="font-weight:bold;"><?php echo $payment_method ?></span>
                    </td>
                    <td class="valign-middle">
                      <a href="/sales/view-invoice/<?php echo $sales_code ?>" title="View Invoice" style="font-size:10px;color:#225992;font-weight:bold;" target="_blank">
                        <?php echo $sales_details['billNo'].' / '.$invoice_date ?>
                      </a>
                    </td>
                    <td class="text-right valign-middle" style="font-size: 13px; font-weight: bold; color: green;"><?php echo number_format($sales_details['netPay'],2,'.','') ?></td>
                    <td class="text-right valign-middle"><?php echo $gst_doc_no ?></td>    
                    <td class="text-left valign-middle"><?php echo $brand_name ?></td>    
                    <?php 
                      /*
                        <td class="text-left med-name valign-middle"><?php echo $mobile_number ?></td>
                        <td class="text-right valign-middle"><?php echo number_format($discount_amount,2,'.','') ?></td>
                        <td class="text-right valign-middle"><?php echo number_format($taxable_amount,2,'.','') ?></td>                    
                        <td class="text-right valign-middle"><?php echo number_format($sales_details['taxAmount'],2,'.','').' ['. $tax_calc_option.']' ?></td>
                        <td class="text-right valign-middle"><?php echo number_format($sales_details['roundOff'],2,'.','') ?></td> 
                      */ 
                    ?>
                    <td>
                      <div class="btn-actions-group">
                        <?php if($sales_code !== ''): ?>
                          <?php if($tax_calc_option === 'I'): ?>
                            <?php if($is_combo_bill): ?>
                              <a class="btn btn-success" href="javascript: printSalesBillCombo('<?php echo $sales_code ?>')" title="Print Combo Bill on Laser/InkJet Printer - A4 Size">
                                <i class="fa fa-print" aria-hidden="true"></i>
                              </a>
                            <?php else: ?>
                              <a class="btn btn-success" href="javascript: printSalesBillSmall('<?php echo $sales_code ?>')" title="Print Invoice on Thermal Printer - Paper Roll">
                                <i class="fa fa-map-o" aria-hidden="true"></i>
                              </a>
                              <a class="btn btn-success" href="javascript: printSalesBill('<?php echo $sales_code ?>')" title="Print Invoice on Laser/InkJet Printer - A4 Size">
                                <i class="fa fa-print" aria-hidden="true"></i>
                              </a>
                            <?php endif; ?>
                          <?php endif; ?>

                          <?php if($tax_calc_option === 'E'): ?>

                            <?php if($gst_no !== ''): ?>
                              <a class="btn btn-warning" href="/sales/generate-einvoice/<?php echo $sales_code ?>?fromDate=<?php echo $fromDate ?>&toDate=<?php echo $toDate ?>&locationCode=<?php echo $locationCode ?>" title="Generate eInvoice from a single click">
                                <i class="fa fa-internet-explorer" aria-hidden="true"></i>
                              </a>
                            <?php endif; ?>

                            <a class="btn btn-primary" target="_blank" href="/whatsapp/shipping-update?ic=<?php echo $sales_code ?>" title="Push Whatsapp shipping update">
                              <i class="fa fa-whatsapp" aria-hidden="true"></i>
                            </a>

                            <?php if($gst_irn !== ''): ?>
                              <a class="btn btn-success" target="_blank" href="/sales-invoice-irn/<?php echo $sales_code ?>" title="Print B2B Invoice">
                                <i class="fa fa-bold" aria-hidden="true"></i>
                              </a>
                            <?php else: ?>
                              <a class="btn btn-success" target="_blank" href="/sales-invoice-b2b/<?php echo $sales_code ?>" title="Print B2B Invoice">
                                <i class="fa fa-bold" aria-hidden="true"></i>
                              </a>
                            <?php endif; ?>
                            <a 
                              class="btn <?php echo strlen($transporter_name) > 0 ? 'btn-success' : 'btn-danger' ?>" 
                              target="_blank" 
                              href="/sales/shipping-info/<?php echo $sales_code ?>" 
                              title="Update Shipping Information"
                            >
                              <i class="fa fa-truck" aria-hidden="true"></i>
                            </a>
                          <?php endif; ?>
                          
                          <a class="btn btn-info" target="_blank" href="/sales-return/entry/<?php echo $sales_code ?>" title="Sales Return">
                            <i class="fa fa-undo" aria-hidden="true"></i>
                          </a>

                          <?php //if(Utilities::is_admin()): ?>
                            <?php /*
                            <?php if($gatepass_no !== ''): ?>
                              <a class="btn btn-danger delGatepass" href="/gate-pass/remove/<?php echo $sales_code ?>" title="Delete Gatepass">
                                <i class="fa fa-times" aria-hidden="true"></i>
                              </a>
                            <?php endif; ?> */ ?>
                            <a class="btn btn-warning" target="_blank" href="/sales/update-with-barcode/<?php echo $sales_code ?>" title="Edit Invoice Using Barcode">
                              <i class="fa fa-pencil" aria-hidden="true"></i>
                            </a>
                            <a class="btn btn-info" target="_blank" href="/sales/update/<?php echo $sales_code ?>" title="Edit Invoice Without Barcode">
                              <i class="fa fa-pencil" aria-hidden="true"></i>
                            </a>
                          <?php //endif; ?>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
            <?php
              $cntr++;
              endforeach; 
            ?>
            <tr class="text-uppercase font11">
              <td colspan="4" align="right">PAGE TOTALS</td>
              <?php 
                /*              
                <td class="text-bold text-right"><?php echo number_format($tot_bill_amount,2,'.','') ?></td>
                <td class="text-bold text-right"><?php echo number_format($tot_disc_amount,2,'.','') ?></td>
                <td class="text-bold text-right"><?php echo number_format($tot_amount,2,'.','') ?></td>
                <td class="text-bold text-right"><?php echo number_format($tot_tax_amount,2,'.','') ?></td>              
                <td class="text-bold text-right"><?php echo number_format($tot_round_off,2,'.','') ?></td> 
                */ 
              ?>
              <td class="text-bold text-right" style="font-size: 13px; font-weight: bold; color: green;"><?php echo number_format($tot_net_pay,2,'.','') ?></td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr class="text-uppercase font11">
              <td colspan="4" align="right">TOTAL SALES (does not include Brand filter)</td>
              <?php 
                /*              
                <td class="text-bold text-right"><?php echo isset($query_totals['billAmount']) ? number_format($query_totals['billAmount'],2,'.','') : "0.00" ?></td>
                <td class="text-bold text-right"><?php echo isset($query_totals['discountAmount']) ? number_format($query_totals['discountAmount'],2,'.','') : "0.00" ?></td>
                <td class="text-bold text-right"><?php echo isset($query_totals['totalAmount']) ? number_format($query_totals['totalAmount'],2,'.','') : "0.00"  ?></td>
                <td class="text-bold text-right"><?php echo isset($query_totals['taxAmount']) ? number_format($query_totals['taxAmount'],2,'.','') : "0.00" ?></td>
                <td class="text-bold text-right"><?php echo isset($query_totals['roundOff']) ? number_format($query_totals['roundOff'],2,'.','') : "0.00" ?></td> 
                */ 
              ?>
              <td class="text-bold text-right" style="font-size: 13px; font-weight: bold; color: green;"><?php echo isset($query_totals['netPay']) ? number_format($query_totals['netPay'],2,'.','') : "0.00" ?></td>
              <td>&nbsp;</td>      
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
          </tbody>
          </table>
          <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
        </div>
      </div>
    </section>
  </div>
</div>