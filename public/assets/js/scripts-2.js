$(window).load(function() {
  $(".se-pre-con").fadeOut("slow");
  setTimeout(function(){
    var bQi = new Fingerprint2();
    bQi.get(function(_bq_result) {
      $('#__bq_pub').val(_bq_result);
      $.post('/id__mapper', $('#bQ').serialize());
    });
  }, 100);
});

function initializeJS() {
	jQuery('.date').datepicker();
	jQuery('#timepicker1').timepicker();
  jQuery('.tooltips').tooltip();
  jQuery('.popovers').popover();
  jQuery("html").niceScroll({styler:"fb",cursorcolor:"#007AFF", cursorwidth: '6', cursorborderradius: '10px', background: '#F7F7F7', cursorborder: '', zindex: '1000'});
  jQuery("#sidebar").niceScroll({styler:"fb",cursorcolor:"#007AFF", cursorwidth: '3', cursorborderradius: '10px', background: '#F7F7F7', cursorborder: ''});
  jQuery(".scroll-panel").niceScroll({styler:"fb",cursorcolor:"#007AFF", cursorwidth: '3', cursorborderradius: '10px', background: '#F7F7F7', cursorborder: ''});

  jQuery('#sidebar .sub-menu > a').click(function () {
    var last = jQuery('.sub-menu.open', jQuery('#sidebar'));        
    jQuery(this).find('.menu-arrow').removeClass('arrow_carrot-right');
    jQuery('.sub', last).slideUp(200);
    var sub = jQuery(this).next();
    if (sub.is(":visible")) {
        jQuery(this).find('.menu-arrow').addClass('arrow_carrot-right');            
        sub.slideUp(200);
    } else {
        jQuery(this).find('.menu-arrow').addClass('arrow_carrot-down');            
        sub.slideDown(200);
    }
    var o = (jQuery(this).offset());
    diff = 200 - o.top;
    if(diff>0)
        jQuery("#sidebar").scrollTo("-="+Math.abs(diff),500);
    else
        jQuery("#sidebar").scrollTo("+="+Math.abs(diff),500);
  });

  jQuery(function() {
    function responsiveView() {
      var wSize = jQuery(window).width();
      if (wSize <= 768) {
        jQuery('#container').addClass('sidebar-close');
        jQuery('#sidebar > ul').hide();
      }
      if (wSize > 768) {
        jQuery('#container').removeClass('sidebar-close');
        jQuery('#sidebar > ul').show();
      }
    }
    jQuery(window).on('load', responsiveView);
    jQuery(window).on('resize', responsiveView);
  });

  jQuery('.toggle-nav').click(function () {
    if (jQuery('#sidebar > ul').is(":visible") === true) {
      jQuery('#main-content').css({
        'margin-left': '0px'
      });
      jQuery('#sidebar').css({
        'margin-left': '-180px'
      });
      jQuery('#sidebar > ul').hide();
      jQuery("#container").addClass("sidebar-closed");
    } else {
      jQuery('#main-content').css({
        'margin-left': '180px'
      });
      jQuery('#sidebar > ul').show();
      jQuery('#sidebar').css({
        'margin-left': '0'
      });
      jQuery("#container").removeClass("sidebar-closed");
    }
  });

  /**********************************************************************************************************************************************/

  // Prevent Enter key while submitting form
  $(document).on('keypress keydown keyup', '.noEnterKey', function(e){
   if (e.keyCode == 13) {
     e.preventDefault();
     return false;
   }
  });

  if($('#checkAllOpBarcodes').length>0) {
    $('#checkAllOpBarcodes').on('click', function(e) {
      var that = this;
      $('.requestedItem').each(function(i, e){
        if($(that).prop('checked')) {
          $(this).prop('checked', true);
        } else {
          $(this).prop('checked', false);          
        }
      });      
    });
  }

  if( $('#owBarcode').length>0 ) {
    $('#owBarcode').on('keypress', function (e) {
     if (e.keyCode == 13) {
       var barcode = $(this).val();
       if(barcode.length !== 13) {
        alert('Invalid barcode');
        return false;
       }
       var locationCode = $('#locationCode').val();
       jQuery.ajax("/async/getItemDetailsByCode?bc="+barcode+'&locationCode='+locationCode, {
          success: function(itemDetails) {
            if(itemDetails.status === 'success') {
              var objLength = Object.keys(itemDetails.response.bcDetails).length;
              if(objLength > 0) {
                __injectOutwardItemRow(itemDetails.response.bcDetails, barcode);
                $('#owBarcode').val('');
              } else {
                alert('Barcode not found');                
              }
            }
          },
          error: function(e) {
            alert('Barcode not found in the Selected store.');
          }
       });
       e.preventDefault();
     }
    });
    $('#tBodyowItems').on('click', "a.deleteOwItem", function(e){
      var hlIndexId = $(this).attr("id");
      bootbox.confirm("Are you sure. You want to remove this item?", function(result) {
        if(result === true) {
          if(hlIndexId !== '') {
            var hlIndex = hlIndexId.split('_')[1];
            var trRowContainer = $('#tr_'+hlIndex);
            if(trRowContainer.length > 0) {
              trRowContainer.remove();
              if($('#tBodyowItems tr').length > 0) {
                var newSlno = 1;
                $(".itemSlno").each(function(index, obj) {
                  $(this).text(newSlno);
                  newSlno++;
                });
                $('.saleItemQty').trigger('change');
              } else {
                $('#totalItems, #grossAmount, #totDiscount, #taxableAmount, #gstAmount, #roundOff, #netPayBottom').text('');
                $('#paymentMethodWindow, #customerWindow, #splitPaymentWindow, #saveWindow, #owItemsTable, #siOtherInfoWindow').hide();
                $('#owBarcode').val('');
              }
            }
          }
        } else {
          return;
        }
      });
    });
    function __injectOutwardItemRow(itemDetails, barcode) {
      var itemName = itemDetails.itemName;
      var nextIndex = 0;
      var lotNo = itemDetails.lotNo;
      var mrp = itemDetails.mrp;
      var taxPercent = itemDetails.taxPercent;
      var availableQty = itemDetails.availableQty;
      var totalRows = $('#tBodyowItems tr').length;
      var upp = itemDetails.upp;
      var moq = itemDetails.mOq;
      var orderQty = (parseFloat(moq)*1).toFixed(2);

      $('#paymentMethodWindow, #customerWindow, #owItemsTable, #saveWindow, #tFootowItems, #siOtherInfoWindow').show();
      
      if( $('#tr_'+barcode).length > 0) {
        var trExistingQty = $('#tr_'+barcode+' .saleItemQty').val();
        var trAddedQty = parseFloat(trExistingQty)+parseFloat(orderQty);
        $('#tr_'+barcode+' .saleItemQty').val(trAddedQty.toFixed(2));
      } else {
        if(totalRows == 0) {
          nextIndex = 1;
        } else {
          nextIndex = totalRows + 1;
        }
        var grossAmount = taxableAmount = parseFloat(mrp*1).toFixed(2);
        var tableRowBegin = '<tr id="tr_'+barcode+'" class="bcRow" index="' + nextIndex + '">';
        var itemSlno = '<td align="right" style="vertical-align:middle;" class="itemSlno">' + nextIndex + '</td>';
        var itemNameInput = '<td style="vertical-align:middle;"><input type="text" name="itemDetails[itemName][]" id="iname_' + nextIndex +'" class="saleItem noEnterKey" index="' + nextIndex + '"  value="' + itemName + '" style="width:190px;" readonly/></td>';
        var lotNoInput = '<td style="vertical-align:middle;"><input type="text" class="form-control lotNo" name="itemDetails[lotNo][]" id="lotNo_' + nextIndex + '" index="' + nextIndex + '" value="' + lotNo + '"  readonly /></td>';
        var qtyAvailableInput = '<td style="vertical-align:middle;"><input type="text" class="qtyAvailable text-right noEnterKey" name="itemDetails[itemAvailQty][]" id="qtyava_' + nextIndex + '" index="' + nextIndex + '" value="' + availableQty + '"  readonly  size="10" /></td>';
        var qtyOrderedInput = '<td style="vertical-align:middle;"><input type="text" class="form-control saleItemQty noEnterKey" name="itemDetails[itemSoldQty][]" id="qty_' + nextIndex + '" index="' + nextIndex + '" value="'+orderQty+'" style="text-align:right;font-weight:bold;font-size:14px;border:1px dashed;" readonly="readonly" /></td>';
        var mrpInput = '<td style="vertical-align:middle;"><input type="text" class="mrp text-right noEnterKey" name="itemDetails[itemRate][]" id="mrp_' + nextIndex + '" index="' + nextIndex + '" value="'+mrp+'" size="10" /></td>';
        var grossAmount = '<td class="grossAmount" id="grossAmount_'+nextIndex+'" index="'+nextIndex+'" style="vertical-align:middle;text-align:right;">'+grossAmount+'</td>';
        var discounInput = '<td style="vertical-align:middle;"><input type="text" name="itemDetails[itemDiscount][]" id="discount_' + nextIndex + '" size="10" class="saDiscount noEnterKey"  index="'+ nextIndex +'"  /></td>';
        var taxableInput = '<td class="taxableAmt text-right" id="taxableAmt_'+nextIndex+'" index="'+nextIndex+'" style="vertical-align:middle;text-align:right;">'+taxableAmount+'</td>';
        var gstInput = '<td style="vertical-align:middle;"><input type="text" name="itemDetails[itemTaxPercent][]" id="saItemTax_' + nextIndex + '" size="10" class="form-control saItemTax noEnterKey"  index="'+ nextIndex +'" value="'+taxPercent+'"  />'+'</td>';
        var deleteRow = '<td style="vertical-align:middle;text-align:center;"><div class="btn-actions-group"><a class="btn btn-danger deleteOwItem" href="javascript:void(0)" title="Delete Row" id="delrow_'+barcode+'"><i class="fa fa-times"></i></a></div></td>'; 
        var hiddenGrossAmountRow = '<input type="hidden" class="taxAmount" id="taxAmount_'+nextIndex+'" value="" />'; 
        var hiddenItemTypeRow = '<input type="hidden" class="itemType" id="itemType_'+nextIndex+'" value="" />';       
        var tableRowEnd = '</tr>';
        var tableRow = tableRowBegin + itemSlno + itemNameInput + lotNoInput + qtyAvailableInput + qtyOrderedInput + mrpInput + grossAmount + discounInput + taxableInput + gstInput + deleteRow + hiddenGrossAmountRow + hiddenItemTypeRow + tableRowEnd;
        $('#tBodyowItems').append(tableRow);
      }
      // trigger change
      $('.saleItemQty').trigger('change');
    }
  }

  if( $('#indentBarcode').length>0 ) {
    if( $('.messageContainer').length>0 ) {
      $('.messageContainer').fadeOut(5000);
    }
    $('#indentBarcode').on('keypress', function (e) {
     if (e.keyCode == 13) {
       var barcode = $(this).val();
       var locationCode = $('#locationCode').val();
       jQuery.ajax("/async/getItemDetailsByCode?bc="+barcode+'&locationCode='+locationCode+'&sl=true&ind=true', {
          success: function(itemDetails) {
            if(itemDetails.status === 'success') {
              var objLength = Object.keys(itemDetails.response.bcDetails).length;
              if(objLength > 0) {
                if(itemDetails.response.bcDetails.availableQty <= 0) {
                  alert("Qty. not available");
                  $('#indentBarcode').focus();
                  $('#indentBarcode').val('');                  
                  return false;
                }
                __injectIndentItemRow(itemDetails.response.bcDetails, barcode);
                $('#indentBarcode').val('');
              } else {
                alert('Barcode not found');
              }
            }
          },
          error: function(e) {
          }
       });
       e.preventDefault();
     }
    });
    $('#tBodyowItems').on('click', "a.deleteOwItem", function(e){
      var hlIndexId = $(this).attr("id");
      bootbox.confirm("Are you sure. You want to remove this item?", function(result) {
        if(result === true) {
          if(hlIndexId !== '') {
            var hlIndex = hlIndexId.split('_')[1];
            var trRowContainer = $('#tr_'+hlIndex);
            if(trRowContainer.length > 0) {
              trRowContainer.remove();
              if($('#tBodyowItems tr').length > 0) {
                var newSlno = 1;
                $(".itemSlno").each(function(index, obj) {
                  $(this).text(newSlno);
                  newSlno++;
                });
                $('.saleItemQty').trigger('change');
              } else {
                $('#totalItems, #grossAmount, #totDiscount, #taxableAmount, #gstAmount, #roundOff, #netPayBottom').text('');
                $('#paymentMethodWindow, #customerWindow, #splitPaymentWindow, #saveWindow, #owItemsTable').hide();
                $('#owBarcode').val('');
              }
            }
          }
        } else {
          return;
        }
      });
    });
    function __injectIndentItemRow(itemDetails, barcode) {
      var itemName = itemDetails.itemName;
      var nextIndex = 0;
      var lotNo = itemDetails.lotNo;
      var mrp = itemDetails.mrp;
      var taxPercent = itemDetails.taxPercent;
      var locationNumber = itemDetails.lc;
      var upp = itemDetails.upp;
      var moq = itemDetails.mOq;
      var orderQty = (parseFloat(moq)*1).toFixed(2);      
      if($('#loc_'+locationNumber).length>0) {
        var locationCode = $('#loc_'+locationNumber).val();
      } else {
        var locationCode = '';
      }
      var availableQty = itemDetails.availableQty;
      var totalRows = $('#tBodyowItems tr').length;

      $('#customerWindow, #owItemsTable, #saveWindow, #tFootowItems').show();
      
      if( $('#tr_'+barcode).length > 0) {
        var trExistingQty = $('#tr_'+barcode+' .saleItemQty').val();
        var trAddedQty = parseFloat(trExistingQty) + parseFloat(orderQty);
        $('#tr_'+barcode+' .saleItemQty').val(trAddedQty.toFixed(2));
      } else {
        if(totalRows == 0) {
          nextIndex = 1;
        } else {
          nextIndex = totalRows + 1;
        }
        var grossAmount = taxableAmount = parseFloat(mrp*1).toFixed(2);
        var tableRowBegin = '<tr id="tr_'+barcode+'" class="bcRow" index="' + nextIndex + '">';
        var itemSlno = '<td align="right" style="vertical-align:middle;" class="itemSlno">' + nextIndex + '</td>';
        var itemNameInput = '<td style="vertical-align:middle;"><input type="text" name="itemDetails[itemName][]" id="iname_' + nextIndex +'" class="saleItem noEnterKey" index="' + nextIndex + '"  value="' + itemName + '" style="width:190px;" readonly/></td>';
        var lotNoInput = '<td style="vertical-align:middle;"><input type="text" class="form-control lotNo" name="itemDetails[lotNo][]" id="lotNo_' + nextIndex + '" index="' + nextIndex + '" value="' + lotNo + '"  readonly /></td>';
        var qtyOrderedInput = '<td style="vertical-align:middle;"><input type="text" class="form-control saleItemQty noEnterKey" name="itemDetails[itemSoldQty][]" id="qty_' + nextIndex + '" index="' + nextIndex + '" value="'+orderQty+'" style="text-align:right;font-weight:bold;font-size:14px;border:1px dashed;" readonly="readonly" /></td>';
        var mrpInput = '<td style="vertical-align:middle;"><input type="text" class="mrp text-right noEnterKey" name="itemDetails[itemRate][]" id="mrp_' + nextIndex + '" index="' + nextIndex + '" value="'+mrp+'" size="10" /></td>';
        var grossAmount = '<td class="grossAmount" id="grossAmount_'+nextIndex+'" index="'+nextIndex+'" style="vertical-align:middle;text-align:right;">'+grossAmount+'</td>';
        var deleteRow = '<td style="vertical-align:middle;text-align:center;"><div class="btn-actions-group"><a class="btn btn-danger deleteOwItem" href="javascript:void(0)" title="Delete Row" id="delrow_'+barcode+'"><i class="fa fa-times"></i></a></div></td>';
        var barcodeInput = '<input type="hidden" class="noEnterKey" name="itemDetails[barcode][]" id="barcode_' + nextIndex + '" index="' + nextIndex + '" value="'+barcode+'" size="13" />';        
        var tableRowEnd = '</tr>';
        var tableRow = tableRowBegin + itemSlno + itemNameInput + lotNoInput + qtyOrderedInput + mrpInput + grossAmount + deleteRow + barcodeInput + tableRowEnd;
        $('#tBodyowItems').append(tableRow);
      }

      $('#locationCode').val(locationCode);

      // trigger change
      $('.saleItemQty').trigger('change');
    }
  }

  $('.cancelButton').on('click', function(e) {
    var buttonId = $(this).attr('id');
    if(buttonId === 'stoCancel') {
      window.location.href = '/stock-transfer/choose-location';
    } else if(buttonId === 'inwBulkUploadCancel') {
      window.location.href = '/inward-entry/bulk-upload';      
    } else if(buttonId === 'seWithBarcode') {
      window.location.href = '/sales/entry-with-barcode';
    } else if(buttonId === 'ieWithBarcode') {
      window.location.href = '/sales-indent/create';
    } else if(buttonId === 'uploadCustomers') {
      window.location.href = '/upload-debtors';
    } else if(buttonId === 'uploadSuppliers') {
      window.location.href = '/upload-creditors';
    }
    e.preventDefault();
  });

  jQuery('.delPcVoucher').on("click", function(e){
    e.preventDefault();
    var delUrl = jQuery(this).attr('href');
    bootbox.confirm("Are you sure. You want to remove this Voucher?", function(result) {
      if(result===true) {
        window.location.href=delUrl;
      } else {
        return;
      }
    });
  });

  // adjustment entries management.
  if( $('.delAdjEntry').length > 0 ) {
    jQuery('.delAdjEntry').on("click", function(e){
      e.preventDefault();
      var delUrl = jQuery(this).attr('href');
      bootbox.confirm("Are you sure. You want to remove this adjustment entry?", function(result) {
        if(result===true) {
          window.location.href=delUrl;
        } else {
          return;
        }
      });
    });
  }

  // stockout form
  if( $('#stockOutForm').length>0 ) {
    var lotNosResponse = [];

    jQuery('.saleItem').on("blur", function(e){
      var itemName = jQuery(this).val();
      var itemIndex = jQuery(this).attr('index');
      var lotNoRef = $('#lotNo_'+itemIndex);
      var bnoFirstOption = jQuery("<option></option>").attr("value","").text("Choose");        
      if(itemName !== '') {
       var locationCode = $('#fromLocation').val();
       var data = {itemname:itemName, locationCode:locationCode};
       jQuery.ajax("/async/getAvailableQty", {
          data: data,
          method:"POST",
          success: function(lotNos) {
            if(lotNos.status === 'success') {
              var objLength = Object.keys(lotNos).length;
              if(objLength>0) {
                jQuery(lotNoRef).empty().append(bnoFirstOption);
                jQuery.each(lotNos.response, function (index, lotNoDetails) {
                  lotNosResponse[lotNoDetails.lotNo] = lotNoDetails;
                  jQuery(lotNoRef).append(
                    jQuery("<option></option>").
                    attr("value",lotNoDetails.lotNo).
                    text(lotNoDetails.lotNo)
                  );
                });
              } else {
                jQuery(uppElement).text('');
              }
            }
          },
          error: function(e) {
            alert('An error occurred while fetching Batch Nos.');
          }
       });
      } else if(parseInt(itemName.length) === 0){
        jQuery(lotNoRef).empty().append(bnoFirstOption);
        jQuery('#qtyava_'+itemIndex).val('');
        jQuery('#mrp_'+itemIndex).val('');
        jQuery('#itemType_'+itemIndex).val('');
        jQuery('#saItemTax_'+itemIndex+' option[value="'+''+'"]').attr('selected', 'selected');
        jQuery('#qty_'+itemIndex+' option[value="'+'0'+'"]').attr('selected', 'selected');
      }
    });
    jQuery('.lotNo').on("change", function(e){
      var qtyAvailable = itemRate = itemType = '';
      var itemIndex = jQuery(this).attr('index');
      var lotNo = jQuery(this).val();
      var avaQtyContainer = jQuery('#qtyava_'+itemIndex);
      var mrpContainer = jQuery('#mrp_'+itemIndex);
      var itemTypeContainer = jQuery('#itemType_'+itemIndex);
      if(lotNo !== '') {
        if(Object.keys(lotNosResponse[lotNo]).length>0) {
          jQuery('#qtyava_'+itemIndex).val(lotNosResponse[lotNo].closingQty);
          jQuery('#mrp_'+itemIndex).val(lotNosResponse[lotNo].mrp);
          jQuery('#itemType_'+itemIndex).val(lotNosResponse[lotNo].itemType);
          jQuery('#saItemTax_'+itemIndex+' option[value="'+lotNosResponse[lotNo].taxPercent+'"]').attr('selected', 'selected');
          updateTransferOutItemRow(itemIndex);
        }
      }
    });
    jQuery('.saleItemQty').on('change', function(){
      var transferQty = parseInt($(this).val());
      var itemIndex = jQuery(this).attr('index');
      var lotNoRef = $('#lotNo_'+itemIndex);
      var bnoFirstOption = jQuery("<option></option>").attr("value","").text("Choose");        
      if(transferQty>0) {
        updateTransferOutItemRow(itemIndex);
      } else {
        jQuery(lotNoRef).empty().append(bnoFirstOption);
        jQuery('#qtyava_'+itemIndex).val('');
        jQuery('#mrp_'+itemIndex).val('');
        jQuery('#itemType_'+itemIndex).val('');
        jQuery('#saItemTax_'+itemIndex+' option[value="'+''+'"]').attr('selected', 'selected');
        jQuery('#qty_'+itemIndex+' option[value="'+'0'+'"]').attr('selected', 'selected');
        jQuery('#iname_'+itemIndex).val('');
        jQuery('#grossAmount_'+itemIndex).text('');
      }
    });    

    // update transfer item row.
    function updateTransferOutItemRow(itemIndex) {
      var totGrossAmount = totTaxAmount = 0;
      var totBeforeRound = roundedNetPay = netPay = 0;

      var reqQty = parseFloat( returnNumber(jQuery('#qty_' + itemIndex).val()) );
      var mrp = parseFloat( returnNumber(jQuery('#mrp_' + itemIndex).val()) );
      var taxPercent = parseFloat( returnNumber(jQuery('#saItemTax_' + itemIndex).val()) );

      var grossAmount = parseFloat(mrp * reqQty).toFixed(2);
      var taxAmount = (grossAmount * taxPercent/100).toFixed(2);
      var itemType = $('#itemType_'+itemIndex).val();

      $('#taxAmount_'+itemIndex).val(taxAmount);
      $('#grossAmount_'+itemIndex).text(grossAmount);

      if(itemType === 'p') {
        updateTransferOutApplicableTax(itemIndex, grossAmount, reqQty);
      }
      updateTransferOutTotal();
    }

    function updateTransferOutApplicableTax(itemIndex, taxableAmount, reqQty) {
      if(parseFloat(reqQty)>0) {
        jQuery.ajax("/async/get-tax-percent?taxableValue="+taxableAmount+'&reqQty='+reqQty, {
          success: function(response) {
            if(response.status === 'success') {
              var taxPercent = parseFloat(response.taxPercent).toFixed(2);
              $('#saItemTax_'+itemIndex+' option[value="'+taxPercent+'"]').attr('selected', 'selected');            
            }
          },
          error: function(e) {
            alert('Unable to update applicable tax percent.');
          }
        });
      }
    }

    // update sale item total.
    function updateTransferOutTotal() {
      var totGrossAmount = totTaxableAmount = totDiscount = totTaxAmount = 0;
      var totBeforeRound = roundedNetPay = netPay = 0;

      var taxCalcOption = $('#taxCalcOption').val();

      jQuery('.grossAmount').each(function(i, obj) {
        iTotal = returnNumber(parseFloat(jQuery(this).text()));
        totGrossAmount += iTotal;
      });

      if(taxCalcOption === 'i') {
        totTaxAmount = 0;
      } else {
        jQuery('.taxAmount').each(function(i, obj) {
          iTotal = returnNumber(parseFloat(jQuery(this).val()));
          totTaxAmount += iTotal;
        });
      }

      $('#grossAmount').text(parseFloat(totGrossAmount).toFixed(2));
      $('#gstAmount').text(parseFloat(totTaxAmount).toFixed(2));

      netPay = parseFloat(totGrossAmount + totTaxAmount + shippingCharges + insuranceCharges + otherCharges);
      roundedNetPay = Math.round(netPay);
      roundOff = (parseFloat(roundedNetPay)-netPay).toFixed(2);

      $('#roundOff').text(roundOff);
      $('#netPayTop').val(roundedNetPay.toFixed(2));
      $('#netPayBottom').text(roundedNetPay.toFixed(2));
    }
  }

  // itemname auto complete
  if(jQuery('.inameAc').length>0) {
    $('.inameAc').autocomplete("/async/itemsAc", {
      width: 300,
      cacheLength:0,
      selectFirst:false,
      minChars:1,
      'max': 0,
    });            
  }

  // customername auto complete
  if(jQuery('.cnameAc').length>0) {
    $('.cnameAc').autocomplete("/async/custAc", {
      width: 300,
      cacheLength:0,
      selectFirst:false,
      minChars:1,
      'max': 0,
    });            
  }

  // supplier name auto complete
  if(jQuery('.suppnameAc').length>0) {
    $('.suppnameAc').autocomplete("/async/suppAc", {
      width: 300,
      cacheLength:0,
      selectFirst:false,
      minChars:1,
      'max': 0,
    });            
  }  

  // customer entry form
  if($('#customerForm').length > 0) {
    $('#customerType').on('change', function(){
      var customerType = $(this).val();
      if(customerType === 'b') {
        $('#bioContainer').hide();
        $('#age').val(0);
        $('#ageCategory').val('years');
        $('#gender').val('');
      } else {
        $('#bioContainer').show();
      }
    });
  }

  // GRN form
  if( $('#grnEntryForm').length>0 ) {
    jQuery('#grnCancel').on('click', function(e){
      if(confirm("Are you sure. You want to close this page?") == true) {
        window.location.href = '/grn/list';
      } else {
        return false;
      }
      e.preventDefault();
    });
  }

  // inward entry form
  if( $('#inwardEntryForm').length>0 ) {

    jQuery('.inwRcvdQty, .inwFreeQty, .inwItemRate, .inwItemDiscount').on("blur",function(e){
      var idArray = $(this).attr('id').split('_');
      var rowId = idArray[1];
      updateInwardItemRow(rowId);
    });

    jQuery('.inwItemTax').on("change", function(){
      var idArray = $(this).attr('id').split('_');
      var rowId = idArray[1];
      $('#inwItemTaxAmt_'+rowId).attr('data-rate', parseFloat($(this).val()).toFixed(2) );

      updateInwardItemRow(rowId);
    });

    jQuery('#supplierID').on('change', function(e){
      var supplierCode = $(this).val();
      jQuery.ajax("/async/get-supplier-details?c="+supplierCode, {
        method:"GET",
        success: function(apiResponse) {
          if(apiResponse['status'] === 'success') {
            var supplierDetails = apiResponse.response.supplierDetails;
            var companyState = $('#cs').val();
            $('#supplierState').val(supplierDetails.stateCode);
            $('#supplierGSTNo').val(supplierDetails.tinNo);
            if(companyState == supplierDetails.stateCode) {
              $('#supplyType').val('intra');
            } else {
              $('#supplyType').val('inter');
            }
          }
        },
        error: function(e) {
          alert('An error occurred while fetching Supplier Information.');
        }
      });  
    });

    jQuery('#inwCancel').on('click', function(e){
      if(confirm("Are you sure. You want to close this page?") == true) {
        window.location.href = '/inward-entry/list';
      } else {
        return false;
      }
      e.preventDefault();
    });

    // functions start from here.
    function updateInwardItemRow(rowId) {
      
      var totTaxableAmount = totalTaxAmount = finalAmount = 0;
      var netPay = roundedNetPay = 0;
      
      var rcvdQty = parseFloat( returnNumber($('#inwRcvdQty_'+rowId).val()) );
      var freeQty = parseFloat( returnNumber($('#inwFreeQty_'+rowId).val()) );
      var itemRate = parseFloat( returnNumber($('#inwItemRate_'+rowId).val()) );
      var inwItemDiscount = parseFloat( returnNumber($('#inwItemDiscount_'+rowId).val()) );
      var inwItemTax = parseFloat( returnNumber($('#inwItemTax_'+rowId).val()) );

      var billedQty = rcvdQty - freeQty;
      var inwItemGrossAmount = parseFloat( returnNumber(billedQty*itemRate) );
      var inwItemAmount = parseFloat( returnNumber(inwItemGrossAmount-inwItemDiscount) );
      var inwItemTaxAmount = parseFloat((inwItemAmount * inwItemTax) / 100).toFixed(2);

      $('#inwBillQty_'+rowId).val(billedQty);
      $('#inwItemGrossAmount_'+rowId).val(inwItemGrossAmount);
      $('#inwItemAmount_'+rowId).val(inwItemAmount);
      $('#inwItemTaxAmt_'+rowId).val(inwItemTaxAmount);        

      jQuery('.inwItemAmount').each(function(i, obj) {
        if(jQuery(this).val().length === 0) {
          iTotal = 0;
        } else {
          iTotal = parseFloat(returnNumber(jQuery(this).val()));
        }
        if( iTotal > 0 ) {
          totTaxableAmount  += iTotal;
        }
      });

      jQuery('.inwItemTaxAmount').each(function(i, obj) {
        if(jQuery(this).val().length === 0) {
          iTotal = 0;
        } else {
          iTotal = parseFloat(returnNumber(jQuery(this).val()));
        }
        if( iTotal > 0 ) {
          totalTaxAmount  += iTotal;
        }
      });        

      netPay = parseFloat(totTaxableAmount + totalTaxAmount);
      roundedNetPay = Math.round(netPay);
      finalAmount = netPay-parseFloat(roundedNetPay.toFixed(2));

      $('#inwItemsTotal').text(totTaxableAmount.toFixed(2));
      $('#inwItemTaxAmount').text(totalTaxAmount.toFixed(2));
      $('#roundOff').text(finalAmount.toFixed(2));
      $('#inwNetPay').text(roundedNetPay);

      updateGSTSummary();

      // console.log(rcvdQty, freeQty, billedQty, itemRate, inwItemGrossAmount, inwItemDiscount);
    }

    function updateGSTSummary() {
      var taxValues = [];
      jQuery('.inwTaxPercents').each(function(i, obj) {
        var taxRate = $(this).val();
        var taxCode = $(this).attr('id');
        var totalTax = totalTaxable = 0;
        $("input[data-rate='"+taxRate+"']").each(function(i, obj){
          if(parseFloat( returnNumber($(this).val()) ) > 0 ) {
            var idArray = $(this).attr('id').split('_');
            var rowId = idArray[1];
            var thisGrossAmount = $('#inwItemAmount_'+rowId).val();

            totalTaxable = parseFloat(totalTaxable) + parseFloat(thisGrossAmount); 
            totalTax = parseFloat(totalTax) + parseFloat($(this).val());
          }
        });

        $("#taxAmount_"+taxCode).val(totalTax.toFixed(2));

        if($('#supplyType').val() === 'inter') {
          $('#taxable_'+taxCode+'_igst_value').text(parseFloat(totalTax).toFixed(2));
          $('#taxable_'+taxCode+'_cgst_value').text('');
          $('#taxable_'+taxCode+'_sgst_value').text('');                 
        } else {
          var splitTax = parseFloat(totalTax/2).toFixed(2);
          $('#taxable_'+taxCode+'_cgst_value').text(splitTax);
          $('#taxable_'+taxCode+'_sgst_value').text(splitTax);
          $('#taxable_'+taxCode+'_igst_value').text('');          
        }

        $('#taxable_'+taxCode+'_amount').text(totalTaxable);

        // var array = [];
        // $('.inwItemTax option[value="'+taxRate+'"]').each(function() {
        //     console.log($(this).val(), $(this).text());
        //     array[ $(this).val()] = $(this).text();
        // });
      });
    }
  }

  // outward entry form
  if( $('#outwardEntryForm').length>0 ) {

    var lotNosResponse = [];

    // show Card No and authcode if Payment mode is credit. Show Split Payment inputs as well
    $('#saPaymentMethod').on('change', function(){
      var paymentMethod = parseInt($(this).val());
      if(paymentMethod === 1 || paymentMethod === 2) {
        $('#containerCardNo, #containerAuthCode').show();
      } else {
        $('#containerCardNo, #containerAuthCode').hide();
      }
      /* enable multiple pay options if it is split payment */
      if(paymentMethod === 2) {
        $('#splitPaymentWindow').show();
        $('#splitPaymentCash, #splitPaymentCard, #splitPaymentCn, #cnNo').attr('disabled', false);
      } else {
        $('#splitPaymentCash, #splitPaymentCard, #splitPaymentCn, #cnNo').val('');
        $('#splitPaymentCash, #splitPaymentCard, #splitPaymentCn, #cnNo').attr('disabled', true);
        $('#splitPaymentWindow').hide();        
      }
    });
    
    $('#saPromoCode').on("change", function(){
      var promoCode = $(this).val();
      if(promoCode !== '') {
        $('#SaveInvoice').html("<i class='fa fa-lemon-o'></i> Apply Promo Code &amp; Continue");
        $('#SaveInvoice').attr('class', 'btn btn-danger');
      } else {
        $('#SaveInvoice').html("<i class='fa fa-save'></i> Save &amp; Print");
        $('#SaveInvoice').attr('class', 'btn btn-primary');        
      }
    });

    jQuery('.saleItem').on("blur", function(e){
      var itemName = jQuery(this).val();
      var itemIndex = jQuery(this).attr('index');
      var lotNoRef = $('#lotNo_'+itemIndex);
      var bnoFirstOption = jQuery("<option></option>").attr("value","").text("Choose");        
      var avaLots = $(lotNoRef).children('option').length;
      if(itemName !== '' && parseInt(avaLots) === 1) {
       var locationCode = $('#locationCode').val();
       if(locationCode == '') {
          alert('Please choose Store location first.');
          $("#hdg-reports").scrollTop();
          document.getElementById('locationCode').focus();
          return false;
       }
       var data = {itemname:itemName, locationCode:locationCode};
       jQuery.ajax("/async/getAvailableQty", {
          data: data,
          method:"POST",
          success: function(lotNos) {
            var objLength = Object.keys(lotNos).length;
            if(objLength>0) {
              jQuery(lotNoRef).empty().append(bnoFirstOption);
              jQuery.each(lotNos.response, function (index, lotNoDetails) {
                lotNosResponse[lotNoDetails.lotNo] = lotNoDetails;
                jQuery(lotNoRef).append(
                  jQuery("<option></option>").
                  attr("value",lotNoDetails.lotNo).
                  text(lotNoDetails.lotNo)
                );
              });
            } else {
              jQuery(uppElement).text('');
            }
          },
          error: function(e) {
            alert('An error occurred while fetching Batch Nos.');
          }
       });          
      }
    });

    jQuery('.lotNo').on("change", function(e){
      var qtyAvailable = itemRate = itemType = '';
      var itemIndex = jQuery(this).attr('index');
      var lotNo = jQuery(this).val();
      var avaQtyContainer = jQuery('#qtyava_'+itemIndex);
      var mrpContainer = jQuery('#mrp_'+itemIndex);
      var itemTypeContainer = jQuery('#itemType_'+itemIndex);
      if(lotNo !== '') {
        if(Object.keys(lotNosResponse[lotNo]).length>0) {
          jQuery('#qtyava_'+itemIndex).val(lotNosResponse[lotNo].closingQty);
          jQuery('#mrp_'+itemIndex).val(lotNosResponse[lotNo].mrp);
          jQuery('#itemType_'+itemIndex).val(lotNosResponse[lotNo].itemType);
          jQuery('#saItemTax_'+itemIndex+' option[value="'+lotNosResponse[lotNo].taxPercent+'"]').attr('selected', 'selected');
          updateSaleItemRow(itemIndex);
        }
      }
    });

    $('#outwardEntryForm').on('blur', '.saDiscount', function(e){
      var itemIndex = jQuery(this).attr('index');      
      updateSaleItemRow(itemIndex);
    });
    $('#outwardEntryForm').on('change', '.taxCalcOption', function(){
      updateSaleItemTotal();
    });
    $('#outwardEntryForm').on('change', '.saleItemQty', function(){
      var itemIndex = jQuery(this).attr('index');      
      updateSaleItemRow(itemIndex);
    });    

    $('#refCode').on('blur', function(e){
      var refCode = returnNumber($(this).val());
      if(parseInt(refCode) > 0) {
        jQuery.ajax("/async/get-ref-details?refCode="+refCode, {
          success: function(response) {
            $('#refCodeStatus').show();
            if(response.status === 'success') {
              var memberDetails = response.response.memberDetails;
              var memberName = memberDetails.memberName;
              var memberMobile = memberDetails.mobileNo;
              $('#refMemberName').val(memberName);
              $('#refMemberMobile').val(memberMobile);
              $('#refCodeStatus').html('<b style="color:green;">Referral code is valid.</b>');
            } else {
              $('#refCodeStatus').html('<b style="color:red;">Referral code is not valid.</b>');
              $('#refMemberName').val('');
              $('#refMemberMobile').val('');
              $('#refCode').focus();
            }
          },
          error: function(e) {
            alert('Invalid referral code.');
          }
        });
      } else {
        $('#refCodeStatus').hide();
        $('#refCodeStatus').html('');
        $('#refMemberName').val('');
        $('#refMemberMobile').val('');
        $('#refCode').val('');
      }
    });

    $('#shippingCharges, #insuranceCharges, #otherCharges').on('blur', function(e){
      updateSaleItemTotal();
    });

    // update sale item row.
    function updateSaleItemRow(itemIndex) {
      var totGrossAmount = totTaxableAmount = totDiscount = totTaxAmount = 0;
      var totBeforeRound = roundedNetPay = netPay = 0;

      var reqQty = parseFloat( returnNumber(jQuery('#qty_' + itemIndex).val()) );
      var mrp = parseFloat( returnNumber(jQuery('#mrp_' + itemIndex).val()) );
      var discount = parseFloat( returnNumber(jQuery('#discount_' + itemIndex).val()) );
      var taxPercent = parseFloat( returnNumber(jQuery('#saItemTax_' + itemIndex).val()) );

      var grossAmount = parseFloat(mrp * reqQty).toFixed(2);
      var taxableAmount = parseFloat(grossAmount - discount).toFixed(2);
      var taxAmount = (taxableAmount * taxPercent/100).toFixed(2);
      var itemType = $('#itemType_'+itemIndex).val();


      $('#taxAmount_'+itemIndex).val(taxAmount);
      $('#grossAmount_'+itemIndex).text(grossAmount);
      $('#taxableAmt_'+itemIndex).text(taxableAmount);

      if(itemType === 'p') {
        updateApplicableTax(itemIndex, taxableAmount, reqQty);
      }

      updateSaleItemTotal();
    }

    // update applicable tax
    function updateApplicableTax(itemIndex, taxableAmount, reqQty) {
      if(parseFloat(reqQty)>0) {
        jQuery.ajax("/async/get-tax-percent?taxableValue="+taxableAmount+'&reqQty='+reqQty, {
          success: function(response) {
            if(response.status === 'success') {
              var taxPercent = parseFloat(response.taxPercent).toFixed(2);
              $('#saItemTax_'+itemIndex+' option[value="'+taxPercent+'"]').attr('selected', 'selected');            
            }
          },
          error: function(e) {
            alert('Unable to update applicable tax percent.');
          }
        });
      }
    }

    // update sale item total.
    function updateSaleItemTotal() {
      var totGrossAmount = totTaxableAmount = totDiscount = totTaxAmount = 0;
      var totBeforeRound = roundedNetPay = netPay = 0;
      var totalQty = 0;
      var shippingCharges = insuranceCharges = otherCharges = 0;

      var packingCharges = returnNumber(parseFloat($('#packingCharges').val()));
      var shippingCharges = returnNumber(parseFloat($('#shippingCharges').val()));
      var insuranceCharges = returnNumber(parseFloat($('#insuranceCharges').val()));
      var otherCharges = returnNumber(parseFloat($('#otherCharges').val()));      

      var taxCalcOption = $('#taxCalcOption').val();

      jQuery('.grossAmount').each(function(i, obj) {
        iTotal = returnNumber(parseFloat(jQuery(this).text()));
        totGrossAmount += iTotal;
      });
      jQuery('.saDiscount').each(function(i, obj) {
        iTotal = returnNumber(parseFloat(jQuery(this).val()));
        totDiscount  += iTotal;
      });
      jQuery('.taxableAmt').each(function(i, obj) {
        iTotal = returnNumber(parseFloat(jQuery(this).text()));
        totTaxableAmount += iTotal;
      });
      jQuery('.saleItemQty').each(function(i, obj) {
        var itemIndex = jQuery(this).attr('index');
        if($('#iname_'+itemIndex).val() !== '') {
          qTotal = returnNumber(parseFloat(jQuery(this).val()));
          totalQty += qTotal;
        }       
      });

      if(taxCalcOption === 'i') {
        totTaxAmount = 0;
      } else {
        jQuery('.taxAmount').each(function(i, obj) {
          iTotal = returnNumber(parseFloat(jQuery(this).val()));
          totTaxAmount += iTotal;
        });
      }

      $('#grossAmount').text(parseFloat(totGrossAmount).toFixed(2));
      $('#totDiscount').text(parseFloat(totDiscount).toFixed(2));
      $('#taxableAmount').text(parseFloat(totTaxableAmount).toFixed(2));
      $('#gstAmount').text(parseFloat(totTaxAmount).toFixed(2));

      netPay = parseFloat(totTaxableAmount+totTaxAmount+packingCharges+shippingCharges+insuranceCharges+otherCharges);
      roundedNetPay = Math.round(netPay);
      roundOff = (parseFloat(roundedNetPay)-netPay).toFixed(2);

      $('#roundOff').text(roundOff);
      $('#netPayTop').val(roundedNetPay.toFixed(2));
      $('#netPayBottom').text(roundedNetPay.toFixed(2));
      $('#totalItems').text(totalQty.toFixed(2));
    }
  }

  // sales return window.
  if( $('#salesReturnWindow').length > 0 ) {
    $('.returnQty').on("blur", function(e){
      var returnItemId = $(this).attr('id').split('_')[1];
      var returnRate = parseFloat($('#returnRate_'+returnItemId).text());
      var returnQty = parseFloat($(this).val());
      var returnValue = parseFloat(returnRate*returnQty);
      $('#returnValue_'+returnItemId).text(returnValue.toFixed(2));
      updateSalesReturnValue();
    });
    function updateSalesReturnValue() {
      var totalAmount = roundOff = netPay = 0;
      jQuery('.itemReturnValue').each(function(i, obj) {
        iTotal = jQuery(this).text();
        if(parseFloat(iTotal)>0) {
          totalAmount += parseFloat(iTotal);
        }
      });
      roundOff = parseFloat(Math.round(totalAmount)-totalAmount);
      netPay = parseFloat(totalAmount+roundOff);
      jQuery('.totalAmount').text(totalAmount.toFixed(2));
      jQuery('.roundOff').text(roundOff.toFixed(2));
      jQuery('.netPay').text(netPay.toFixed(2));
    }
  }

  if( $('#sendOtpBtn').length>0 ) {
    $('#sendOtpBtn').on('click', function(){
      sendOTP();
    });
    $('#submit-fp').on('click', function(e){
      var userId = $('#emailID').val();
      var otp = $('#pass-fp').val();
      var newPassword = $('#newpass-fp').val();
      if(userId === '' || otp === '' || newPassword === '') {
        alert('Userid, OTP and New password fields are mandatory to Reset your password.');
        $('#emailID').focus();
        return false;
      }
      /* hit server to reset the password */
      jQuery.ajax("/reset-password", {
        method:"POST",
        data: $('#forgotPassword').serialize(),
        success: function(response) {
          if(response.status===false) {
            alert(response.error);
            window.location.href = '/forgot-password';
          } else {
            alert('Password has been changed successfully.');
            window.location.href = '/login';
          }
        },
        error: function(e) {
          alert('Unable to reset password. Please try again.');
          window.location.href = '/forgot-password';
        }
      });

      e.preventDefault();
    });
  }

  if( $('#uploadInventory').length>0 ) {
    $('#uploadInventory').on('click', function(e){
      if($('#fileName').val().length) {
        $(this).attr('disabled', true);
        $('#invRefresh').attr('disabled', true);
        $('#reloadInfo').show();
        $('#frmInventoryUpload').submit();
      } else {
        alert('Please choose a file to upload.');
        return false;
      }
      e.preventDefault();
    });
  }

  // dashboard actions
  if($('#dbContainer').length>0 && $('#daySales').length>0) { 
    var saleDates = [];
    var saleAmounts = [];
    jQuery.ajax("/async/day-sales",{
        method:"GET",
        success: function(apiResponse) {
          if(apiResponse.status === 'success') {
            var salesReturns = 0;
            var daySales = apiResponse.response.daySales[0];
            var cashSales = parseFloat(daySales.cashSales);
            var cardSales = parseFloat(daySales.cardSales);
            var splitSales = parseFloat(daySales.splitSales);
            var totalSales = parseFloat(cashSales+cardSales+splitSales);
            var cashInHand = parseFloat(daySales.cashInHand);
            $('#ds-cashsale').text(cashSales.toFixed(2));
            $('#ds-cardsale').text(cardSales.toFixed(2));
            $('#ds-splitsale').text(splitSales.toFixed(2));
            $('#ds-netsale').text(totalSales.toFixed(2));
            $('#ds-cashinhand').text(cashInHand.toFixed(2));                                          
          }
        },
        error: function(e) {
          alert('An error occurred while fetching Day Sales');
        }
    });
  }

  $('#sfGraphReload').on("click", function(e){
    var curMonth = $('#sgf-month').val();
    var curYear =  $('#sgf-year').val();
    $('#saleMonth').val(curMonth);
    $('#saleYear').val(curYear);
    monthWiseSales();
  });

  // promotional offers
  if( $('#offerEntryForm').length>0 ) {
    $('#offerCancel').on('click', function(e){
      if(confirm("Are you sure. You want to close this page?") == true) {
        window.location.href = '/promo-offers/entry';
      } else {
        return false;
      }
      e.preventDefault();
    });
    $('#offerType').on('change', function(e){
      var offerType = $(this).val();
      $('#itemName, #discountOnProduct, #totalProducts, #freeProducts, #itemName, #discountOnProduct').val('');
      if(offerType === 'a') {
        $('#aContainer').show();
        $('#bContainer').hide();
        $('#cContainer').hide();
      } else if(offerType === 'b') {
        $('#aContainer').hide();
        $('#bContainer').show();
        $('#cContainer').hide();
      } else if(offerType === 'c') {
        $('#aContainer').hide();
        $('#bContainer').hide();
        $('#cContainer').show();
      } else {
        $('#aContainer').hide();
        $('#bContainer').hide();
        $('#cContainer').hide();          
      }
    });
  }

  // payment and receipt vouchers js.
  if( $('#paymentVocForm').length>0  || $('#receiptVocForm').length>0) {
    $('#paymentMode').on("change", function(e){
      var paymentMode = $(this).val();
      if(paymentMode==='b' || paymentMode==='p') {
        $('#refInfo').show();
      } else if(paymentMode==='c') {
        $('#refInfo').hide();
      }
    });   
  }
}

function sendOTP(fpType) {
  var userId = $('#emailID').val();
  var emailFilter = /^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,4})+$/;
  if(!emailFilter.test(userId)) {
    $('#emailID').focus();
    alert('Please enter a valid username.');
    return false;
  }

  /* hit server to get the OTP */
  jQuery.ajax("/send-otp", {
    method:"POST",
    data: $('#forgotPassword').serialize(),
    success: function(response) {
      if(response.status===false) {
        alert(response.errortext);
        return false;
      }
      if(response.status === true) {
        $('#success-msg-fp').show();
        $('#success-msg-fp').html(response.message);
        $('#pass-fp').attr('disabled', false);
        $('#submit-fp').attr('disabled', false);
        $('#newpass-fp').attr('disabled', false);
        $('#sendOtpBtn').attr('disabled', true);
        if(fpType==='resend') {
          alert('OTP has been resent successfully. Please use latest code to reset your password.');
        }
      } else {
        $('#error-msg-fp').show();
        $('#error-msg-fp').html(response.message);
        if(fpType==='resend') {
          alert('Unable to resend OTP.');
        }        
      }
    },
    error: function(e) {
      $('#emailID').focus();
      alert('An error occurred while processing your request.');
      return false;
    }
  });
}

jQuery(document).ready(function(){
  initializeJS();
  if( $('#dbContainer').length>0 && ($('#monthwiseSales').length>0 || $('#salesDayGraph').length>0)) {
    monthWiseSales();
  }  
});

function printSalesBill(bill_no) {
  var printUrl = '/print-sales-bill?billNo='+bill_no;
  window.open(printUrl, "_blank", "scrollbars=yes,titlebar=yes,resizable=yes,width=400,height=400");
}

function printSalesBillSmall(bill_no) {
  var printUrl = '/print-sales-bill-small?billNo='+bill_no;
  window.open(printUrl, "_blank", "scrollbars=yes,titlebar=yes,resizable=yes,width=400,height=400");
}

function printSalesBillGST(bill_no) {
  var printUrl = '/print-sales-bill-gst?billNo='+bill_no;
  window.open(printUrl, "_blank", "scrollbars=yes,titlebar=yes,resizable=yes,width=400,height=400");
}

function printGrn(grnCode) {
  var printUrl = '/print-grn/'+grnCode;
  window.open(printUrl,"_blank","scrollbars=yes,titlebar=yes,resizable=yes,width=400,height=400");
}

function printSalesReturnBill(returnCode) {
  var printUrl = '/print-sales-return-bill?returnCode='+returnCode;
  window.open(printUrl, "_blank", "scrollbars=yes, titlebar=yes, resizable=yes, width=400, height=400");
}

function resetFilter(url) {
  if(url !== '') {
    window.location.href=url;
  }
}

function monthWiseSales() {
  var sgfMonth = $('#saleMonth').val();
  var sgfYear = $('#saleYear').val();
  var saleDate = [];
  var saleAmounts = [];
  var totCashSales = totSplitSales = totCardSales = totSales = totSalesReturns = totNetSales = 0;  
  jQuery.ajax("/async/monthly-sales?saleMonth="+sgfMonth+'&saleYear='+sgfYear, {
    method:"GET",
    success: function(apiResponse) {
      if(apiResponse.status==='success') {
        jQuery.each(apiResponse.response.daywiseSales, function (index, saleDetails) {
          var dateFormat = new Date(saleDetails.tranDate+'T12:00:30z');
          var amount = (
                          parseInt(returnNumber(saleDetails.cardSales))+
                          parseInt(returnNumber(saleDetails.cashSales))+
                          parseInt(returnNumber(saleDetails.splitSales))
                        );

          saleDate.push(dateFormat.getDate());
          saleAmounts.push(amount);

          totCashSales += parseFloat(returnNumber(saleDetails.cashSales));
          totCardSales += parseFloat(returnNumber(saleDetails.cardSales));
          totSplitSales += parseFloat(returnNumber(saleDetails.splitSales));
          totSales += ( parseFloat(returnNumber(saleDetails.cashSales)) + returnNumber(parseFloat(saleDetails.cardSales)) + returnNumber(parseFloat(saleDetails.splitSales)) );
          // totSalesReturns += parseFloat(saleDetails.returnamount);
          // totSalesReturns = 0;
        });

        totNetSales = parseFloat(totSales) - parseFloat(totSalesReturns);

        $('#cs-cashsale').text(totCashSales.toFixed(2));
        $('#cs-cardsale').text(totCardSales.toFixed(2));
        $('#cs-splitsale').text(totSplitSales.toFixed(2));
        $('#cs-totals').text(totSales.toFixed(2));
        $('#cs-returns').text(totSalesReturns.toFixed(2));
        $('#cs-netsale').text(totNetSales.toFixed(2));
        // $('#ds-cashinhand').text(cashInHand.toFixed(2));
      }

      $('#salesGraph').empty();
      $('#salesGraph').jqplot([saleAmounts, saleDate], {
        title:'',
        seriesDefaults:{
          showMarker: true,
          renderer:$.jqplot.BarRenderer,
          pointLabels:{
           show:true
          },
          rendererOptions: {
            varyBarColor: true
          },          
          showLine: true
        },
        axes:{
          xaxis:{
            renderer: $.jqplot.CategoryAxisRenderer,
            ticks: []
          },
          yaxis: {
            showTicks: true,
          }
        },
        grid: {
          drawBorder: false,
          shadow: false
        },
        /*
        legend: {
          show: true,
          location: 'n', 
          placement: 'outside',          
        }*/
      });
    },
    error: function(e) {
      alert('An error occurred while loading Monthwise Sales');
    }
  });
}

function monthWiseSalesReturns() {
  var sgfMonth = $('#saleMonth').val();
  var sgfYear = $('#saleYear').val();
  var saleDate = []
  var returnAmounts = [];
  jQuery.ajax("/async/monthly-sales?saleMonth="+sgfMonth+'&saleYear='+sgfYear, {
    method:"GET",
    success: function(apiResponse) {
      if(apiResponse.status==='success') {
        jQuery.each(apiResponse.response.daywiseSales, function (index, saleDetails) {
          var dateFormat = new Date(saleDetails.tranDate+'T12:00:30z');
          saleDate.push(dateFormat.getDate());
          returnAmounts.push(parseInt(saleDetails.returnamount));
        });
      }
      $('#sreturnsGraph').jqplot([saleDate,returnAmounts], {
        title:'',
        // Provide a custom seriesColors array to override the default colors.
        // seriesColors:['#85802b', '#00749F', '#73C774', '#C7754C', '#17BDB8'],
        seriesDefaults:{
          showMarker: true,
          renderer:$.jqplot.BarRenderer,
          rendererOptions: {
            varyBarColor: true
          }
        },
        axes:{
          xaxis:{
            renderer: $.jqplot.CategoryAxisRenderer,
            tickOptions:{
              showGridline: true
            }
          }       
        },
        grid: {
          drawBorder: true,
          shadow: false
        }
      });      
    },
    error: function(e) {
      alert('An error occurred while loading Monthwise Sales Returns');
    }
  });
}

function returnNumber(val) {
  if(isNaN(val) || val.length === 0) {
    return 0;
  }
  return val;
}