$(window).load(function() {
  var pageUrl = window.location.pathname;
  $(".se-pre-con").fadeOut("slow");
  setTimeout(function(){
    if($('#bQ').length > 0) {
      var bQi = new Fingerprint2();
      bQi.get(function(_bq_result) {
        $('#__bq_pub').val(_bq_result);
        $.post('/id__mapper', $('#bQ').serialize());
      });
    }
  }, 100);
  if($('#qbMain').length > 0 && pageUrl !== '/forgot-password') {
    var qbTimer = setInterval(function(){$.ajax({type: "POST",url: "/__id__lo",success: function (response) {if(response === 'expired') {clearInterval(qbTimer);window.location.href = '/force-logout';}}});}, 10000);
  }
});

(function (global) { 
  if(typeof (global) === "undefined") {
    throw new Error("window is undefined");
  }
  var _hash = "!";
  var noBackPlease = function () {
    global.location.href += "#";
    global.setTimeout(function () {
      global.location.href += "!";
    }, 50);
  };
  global.onhashchange = function () {
    if (global.location.hash !== _hash) {
      global.location.hash = _hash;
    }
  };
/*  global.onload = function () {            
    noBackPlease();
    document.body.onkeydown = function (e) {
      var elm = e.target.nodeName.toLowerCase();
      if (e.which === 8 && (elm !== 'input' && elm  !== 'textarea')) {
        e.preventDefault();
      }
      e.stopPropagation();
    };          
  }*/
})(window);

function initializeJS() {
	jQuery('.date').datepicker();
  jQuery('.date').datepicker({
    format: 'dd/mm/yyyy',
  }).on('changeDate', function(e){
    jQuery(this).datepicker('hide');
  });
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

  // Prevent Enter key while submitting form
  $(document).on('keypress keydown keyup', '.noEnterKey', function(e){
   if (e.keyCode == 13) {
     e.preventDefault();
     return false;
   }
  });

  if($('#cashVoucherForm').length > 0) {
    $('#action').on('change', function(e){
      var vocType = $(this).val();
      if(vocType === 'payment') {
        $('#cnNo').attr('disabled', false);
      } else {
        $('#cnNo').attr('disabled', true);
      }
    });
  }

  if($('#appContentRedirectionContainer').length > 0) {
    $('#enableRedirection').on('change', function(e){
      var option = $(this).val();
      if(parseInt(option) === 1) {
        $('#appContentRedirectionContainer').show();
      } else {
        $('#appContentRedirectionContainer').hide();
        $('#catalogName').val('');
        $('#itemName').val('');
      }
    });    
  }

  if( $('#salesIndentForm').length > 0 ) {
    $('.saveUpdateIndent').on('click', function(e){
      e.preventDefault();
      $('.saveUpdateIndent, .cancelButton, .deleteOwItem').attr('disabled', true);
      $('#salesIndentForm').submit();
    });
  }

  if($('#eInvoiceForm').length > 0) {
    $('#generateEinvoice').on('click', function(e){
      e.preventDefault();
      $('#generateEinvoice, .cancelButton').attr('disabled', true);
      $('#eInvoiceForm').submit();
    });
  }

  if($('#irnCancelForm').length > 0) {
    $('#cancelIrn').on('click', function(e){
      e.preventDefault();
      bootbox.confirm("Are you sure. You want to cancel this IRN?", function(result) {
        if(result === true) {
          $('#cancelIrn, .cancelButton').attr('disabled', true);
          $('#irnCancelForm').submit();
        } else {
          return;
        }
      }); 
    });    
  }  

  if( $('#eWayBillByIrn').length > 0 ) {
    $('#transportMode').on('change', function(){
      var transportMode = parseInt($('#transportMode').val());
      if(transportMode === 0) {
        $('#transportByRoad, #transportByOtherModes').hide();
        $('#vehicleType').val('0');
        $('#vehicleNo, #transportDocNumber').val('');
      } else if(transportMode === 1) {
        $('#transportByRoad').show();
        $('#transportByOtherModes').hide();
        $('#transportDocNumber').val('');
      } else {
        $('#transportByOtherModes').show();
        $('#transportByRoad').hide();
        $('#vehicleType').val('0');
        $('#vehicleNo').val('');
      }
    });

    $('#cancelIrn').on('click', function(e){
      e.preventDefault();
      $('#cancelIrn, .cancelButton').attr('disabled', true);
      $('#eWayBillByIrn').submit();
    });     
  }

  if( $('#buForm').length > 0) {
    $('#showAllCustomers').on('change', function(e){
      var inputValue = $(this).val();
      if(inputValue == 3) {
        $('#gstStateIdsInput').show();
      } else {
        $('#stateIDs').val('');
        $('#gstStateIdsInput').hide();
      }
    });
  }

  if($('.postSc2CB').length > 0) {
    $('.postSc2CB').on('click', function(e){
      var buttonObjId = $(this).attr('id');
      var objId = buttonObjId.split('_')[1];
      if($('#dt_'+objId).length>0 && $('#amt_'+objId).length>0) {
        var tranDate = $('#dt_'+objId).val();
        var tranAmount = $('#amt_'+objId).val();
        var locationCode = $('#locationCode').val();
        var params = 'dt='+tranDate+'&amt='+tranAmount+'&locationCode='+locationCode;
        if(locationCode === '') {
          bootbox.alert({
            message: "Invalid location code."
          });
          return false;
        }
        jQuery.ajax("/fin/post-sales2cb", {
          type: "POST",
          data: params,
          beforeSend: function() {
            $('#ps_'+objId).html('<img src="/assets/img/wait.gif" alt="Wait.." />');
            $('#vn_'+objId).html('<img src="/assets/img/wait.gif" alt="Wait.." />');
            $('#vd_'+objId).html('<img src="/assets/img/wait.gif" alt="Wait.." />');
            $('.postSc2CB').attr('disabled', true);
          },
          success: function(apiResponse) {
            $('.postSc2CB').attr('disabled', false);
            var apiStatus = apiResponse.status;
            if(apiStatus) {
              bootbox.alert({
                message: 'Transaction posted successfully with Voucher No. `' + apiResponse.vocNo + '`'
              });
              $('#ps_'+objId).html('<span style="color:green;font-weight:bold;font-size:14px;">Posted</span>');
              $('#vn_'+objId).html('<span style="color:green;font-weight:bold;font-size:14px;">'+apiResponse.vocNo+'</span>');
              $('#vd_'+objId).html('');
              $('#btn_'+objId).remove();
            } else {
              bootbox.alert({
                message: apiResponse.errorMessage
              });
              $('#ps_'+objId).html('');
              $('#vn_'+objId).html('');
              $('#vd_'+objId).html('');
            }
          },
          error: function(e) {
            $('.postSc2CB').attr('disabled', false);
            bootbox.alert({
              message: "Unable to process your request."
            });            
          }
        });
      }
      e.preventDefault();
    });
  }

  if($('#galleryForm').length > 0) {
    $('.showImage').on('click', function() {
      var imagePath = $(this).find('img').attr('src');
      var imageName = $(this).attr('title');
      $('#imagePreview').attr('src', imagePath);
      $('#modal-title').text(imageName);
      $('#imagemodal').modal('show');
    });
    $('#inputType').on('change', function(e){
      var inputType = $(this).val();
      if(inputType === 'barcode') {
        $('#barcodeInput').show();
        $('#imgBarcode').focus();
      } else {
        $('#barcodeInput').hide();
      }
    });
  }

  // crm related
  /************************************************************************************************/

  /* lead remove duplicates choice while importing */
  if($('#removeDuplicates').length > 0) {
    $('#removeDuplicates').on('change', function(e){
      e.preventDefault();
      var userChoice = parseInt($(this).val());
      if(userChoice === 0) {
        $('#duplicatesDiv, #maErrorId').hide();
      } else if(userChoice === 1) {
        $('#duplicatesDiv').show();      
      }
    });
  }

  /************************************************************************************************/
  // Combo Bills entry.
  if( $('#comboBillEntry').length>0 ) {

    $('#SaveCombo').on('click', function(e){
      e.preventDefault();
      $(this).attr('disabled', true);
      $('.cancelButton').attr('disabled', true);
      $('#comboBillEntry').submit();
    });

    $('#comboDiscount').on('keypress', function(e){
       if (e.keyCode == 13) {
        comboCall(0); 
        e.preventDefault();
       }
    });
    
    function comboCall(itemId, fromSource) {
     var citemCode = $('#cicode_'+itemId).val();
     jQuery.ajax("/async/getComboItemDetails", {
        type: "POST",
        data: $('#comboBillEntry').serialize(),
        beforeSend: function() {
          if(citemCode !== '') {
            $('#inameTd_'+itemId).html('<img src="/assets/img/wait.gif" alt="Wait.." />');
            $('#qtyavaTd_'+itemId).html('<img src="/assets/img/wait.gif" alt="Wait.." />');
            $('#mrpTd_'+itemId).html('<img src="/assets/img/wait.gif" alt="Wait.." />');
            $('#grossAmountTd_'+itemId).html('<img src="/assets/img/wait.gif" alt="Wait.." />');
            // $('.grossAmount').html('<img src="/assets/img/wait.gif" alt="Wait.." />');
            // $('.roundOff').html('<img src="/assets/img/wait.gif" alt="Wait.." />');
            // $('.netPay').html('<img src="/assets/img/wait.gif" alt="Wait.." />');          
          }
        }, 
        success: function(itemDetails) {
          if(itemDetails.status === 'success') {
            var roundOff = netPay = roundedNetPay = 0;
            var avaQtys = itemDetails.response.comboItemAvaQtys;
            var itemMrps = itemDetails.response.comboItemMrps;
            var itemDiscounts = itemDetails.response.comboItemDiscounts;
            var itemNames = itemDetails.response.comboItemNames;
            var itemAmounts = itemDetails.response.comboItemAmounts;
            for(i=0; i<6; i++) {
              if(i in itemNames && itemNames[i] !== '') {
                $('#iname_'+i).val(itemNames[i]);
                $('#inameTd_'+i).text(itemNames[i]);

                $('#qtyava_'+i).val(avaQtys[i]);
                $('#qtyavaTd_'+i).html('<i class="fa fa-cubes" aria-hidden="true"></i> '+avaQtys[i]);
                
                $('#mrp_'+i).val(itemMrps[i]);
                $('#mrpTd_'+i).html('<i class="fa fa-inr" aria-hidden="true"></i> '+parseFloat(itemMrps[i]).toFixed(2));

                $('#discount_'+i).val(itemDiscounts[i]);
                $('#grossAmountTd_'+i).html('<i class="fa fa-inr" aria-hidden="true"></i> '+parseFloat(itemAmounts[i]).toFixed(2));

                netPay += parseFloat(itemAmounts[i]);
                roundedNetPay = Math.round(netPay);
                roundOff = parseFloat(roundedNetPay)-netPay;

                $('.grossAmount').text(netPay.toFixed(2));
                $('.roundOff').text(roundOff.toFixed(2));
                $('.netPay').text(roundedNetPay.toFixed(2));

                if(fromSource === 'fromQty') {
                  var nextItemId = parseInt(itemId) + 1;
                  $('#cicode_'+nextItemId).focus();
                } else {
                  $('#qty_'+itemId).focus();
                }
              } else {
                emptyComboValues(i);
              }
            }
          } else {
            alert("Invalid combo code !");
            $('#inameTd_'+itemId).html('');
            $('#qtyavaTd_'+itemId).html('');
            $('#mrpTd_'+itemId).html('');
            $('#grossAmountTd_'+itemId).html('');
          }
        },
        error: function(e) {
          $('#inameTd_'+itemId).html('');
          $('#qtyavaTd_'+itemId).html('');
          $('#mrpTd_'+itemId).html('');
          $('#grossAmountTd_'+itemId).html('');
          alert('Item code not found in the Selected store.');
        }
     });
    }
    function emptyComboValues(itemId) {
      $('#inameTd_'+itemId).text('');
      $('#qtyavaTd_'+itemId).text('');
      $('#mrpTd_'+itemId).text('');
      $('#grossAmountTd_'+itemId).text('');

      $('#iname_'+itemId).val('');
      $('#qtyava_'+itemId).val('');
      $('#mrp_'+itemId).val('');
      $('#discount_'+itemId).val('');
      $('#qty_'+itemId).val('');
    }
    
    /*
    $('.comboBarcode').on('keypress', function (e) {
     if (e.keyCode == 13) {
       var barcode = $(this).val();
       var locationCode = $('#locationCode').val();
       jQuery.ajax("/async/getComboItemDetailsByCode?bc="+barcode+'&locationCode='+locationCode, {
          success: function(itemDetails) {
            if(itemDetails.status === 'success') {
            }
          },
          error: function(e) {
            alert('Barcode not found in the Selected store.');
          }
       });
       comboCall();
       e.preventDefault();
     }
    });
    */
    $('#comPaymentMethod').on('change', function(){
      var paymentMethod = parseInt(returnNumber($(this).val()));
      if(paymentMethod === 1) {
        $('#comboCardDetails').show();
        $('#comboWalletDetails, #comboSplitPaymentMethods').hide();
        $('#splitPaymentCash, #splitPaymentCard, #splitPaymentWallet, #splitPaymentCn, #cnNo, #walletID, #walletRefNo').val('');
      } else if(paymentMethod === 2) {
        $('#comboSplitPaymentMethods').show();
        $('#comboWalletDetails, #comboCardDetails').hide();
        $('#cardNo, #authCode, #walletID, #walletRefNo').val('');
      } else if(paymentMethod === 4) {
        $('#comboWalletDetails').show();
        $('#comboCardDetails, #comboSplitPaymentMethods').hide();
        $('#splitPaymentCash, #splitPaymentCard, #splitPaymentWallet, #splitPaymentCn, #cnNo, #cardNo, #authCode').val('');
      } else {
        $('#comboCardDetails, #comboSplitPaymentMethods, #comboWalletDetails').hide();
        $('#splitPaymentCash, #splitPaymentCard, #splitPaymentWallet, #splitPaymentCn, #cnNo, #walletID, #walletRefNo, #cardNo, #authCode').val('')
      }
    });

    $('.comboItemCode').on('keypress', function (e) {
     if (e.keyCode == 13) {
       var itemId = $(this).attr('id').split('_')[1];
       var ic = $(this).val();
       if(ic === '') {
        return false;
       }
       if(ic.length > 0) {
         $('#cbarcode_'+itemId).attr('disabled', true);
         if(ic.length<2) {
          ic = '0'+ic;
          $(this).val(ic);
         } else {
          emptyComboValues(itemId);
         }
         $('#rowError_'+itemId).hide();
          var locationCode = $('#locationCode').val();
          var itemId = $(this).attr('id').split('_')[1];
          var saleQty = returnNumber($('#qty_'+itemId).val());
          if(saleQty <= 0) {
            // $('#qty_'+itemId).val(1);
            /* check if item code already exists and add 1 qty. to that row. */
            $('.comboItemCode').each(function(i, obj) {
              var existingItemCode = $(this).val();
              var existingItemId = $(this).attr('id').split('_')[1];
              // console.log(existingItemCode, ic, existingItemId, itemId);
              if(existingItemCode == ic && existingItemId !== itemId) {
                var existingQty = parseInt(returnNumber($('#qty_'+existingItemId).val()));
                var newQty = parseInt(existingQty)+1;
                $('#qty_'+existingItemId).val(newQty);
                $('#cicode_'+itemId).val('');
                // $('#cbarcode_'+itemId).attr('disabled', false);
                itemId = existingItemId;
                return false;
              }
            });
         }
         comboCall(itemId); 
         e.preventDefault();
       }
     }
    });

    $('.comboItemCode').on('keyup', function (e) {
     if (e.keyCode == 8) {
      var stringLength = $(this).val().length;
      if(stringLength <= 0 ) {
        var itemId = $(this).attr('id').split('_')[1];
        emptyComboValues(itemId);
        comboCall(itemId);
      }      
     }
    });

    $('.comboItemQty').on('keypress', function (e) {
     var itemId = $(this).attr('id').split('_')[1];
     if (e.keyCode == 13) {
      var itemQty = parseFloat(returnNumber($(this).val()));
      var nextItemId = parseInt(itemId) + 1;
      if(itemQty > 0) {
        comboCall(itemId, 'fromQty');
        e.preventDefault();
      } else {
        return false;
      }
     }
    });
  }

  if( $('#dfyDashboard').length > 0 ) {
    $.post('/async/finyDefault', $('#dfyDashboard').serialize());
  }

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

  if( $('#productsList').length > 0 ) {
    $('.plRackNumbers').Tabledit({
      url: '/async/updateRackNo',      
      editButton: false,
      deleteButton: false,
      hideIdentifier: false,
      columns: {
        identifier: [1,'itemCode'],
        editable: [
          [3, 'rackNumber']
        ]
      },
      onDraw: function() {
        if($('[name="rackNumber"]').length>0) {
          $('[name="rackNumber"]').attr('title', 'Add / Update Rack no. and hit Enter key to Save.');
        }
      },
      // onAjax: function(action, serialize) {
      //   var urlParams = new URLSearchParams(serialize);
      //   var rackNumber = returnNumber(parseFloat(urlParams.get('rackNumber')));
      //   if(rackNumber.length <= 0) {
      //     bootbox.alert({
      //       message: "New MRP should be greater than old MRP. If you want to reduce the current MRP, please use Promocodes or Discount option."
      //     });
      //     return false;
      //   }
      //   return;
      // },
      onSuccess: function(axResponse, textStatus, jqXHR) {
        if(axResponse.status === 'success') {
          var responseMessage = axResponse.response.updateMessage;
        } else if(axResponse.status === 'failed') {
          var responseMessage = 'Error: ' + axResponse.errorcode + ', ' + axResponse.errortext;
        } else {
          var responseMessage = 'Unknown Error.';
        }
        bootbox.alert({
          message: responseMessage
        });
      }
    });  
  }

  if( $('#bulkUpdateSellingPriceFrm').length > 0 ) {
    // $('.itemnames').Tabledit({
    //   url: '/async/change-mrp',      
    //   editButton: false,
    //   deleteButton: false,
    //   hideIdentifier: false,
    //   columns: {
    //     identifier: [1,'lotAndItem'],
    //     editable: [
    //       [2, 'newMrp']
    //     ]
    //   },
    //   onDraw: function() {
    //     if($('[name="newMrp"]').length>0) {
    //       $('[name="newMrp"]').attr('title', 'Add new MRP and Hit Enter to Save.');
    //     }
    //   },
    //   onAjax: function(action, serialize) {
    //     var urlParams = new URLSearchParams(serialize);
    //     var newMrp = returnNumber(parseFloat(urlParams.get('newMrp')));
    //     if(newMrp > 0) {
    //       var lotAndItem = urlParams.get('lotAndItem');
    //       var oldMrp = returnNumber(parseFloat($('#om__'+lotAndItem).text()));
    //       if(newMrp > oldMrp) {
    //         return true;
    //       } else {
    //         bootbox.alert({
    //           message: "New MRP should be greater than old MRP. If you want to reduce the current MRP, please use Promocodes or Discount option."
    //         });
    //         return false;
    //       }
    //     }
    //     return;
    //   },
    //   onSuccess: function(axResponse, textStatus, jqXHR) {
    //     if(axResponse.status === 'success') {
    //       var responseMessage = axResponse.response.updateMessage;
    //     } else if(axResponse.status === 'failed') {
    //       var responseMessage = 'Error: ' + axResponse.errorcode + ', ' + axResponse.errortext;
    //     } else {
    //       var responseMessage = 'Unknown Error.';
    //     }
    //     bootbox.alert({
    //       message: responseMessage
    //     });
    //   }
    // });
    $('#bulkUpdateSellingPriceSubmit').on('click', function(e){
      e.preventDefault();
      $('#bulkUpdateSellingPriceSubmit, #bulkUpdateSellingPrice').attr('disabled', true);
      $('#bulkUpdateSellingPriceFrm').submit();
    });
  }

  if( $('#discountForm').length > 0 && $('.itemdiscounts').length>0) {
    $('.itemdiscounts').Tabledit({
      url: '/async/sdiscount',      
      deleteButton: false,
      hideIdentifier: false,
      columns: {
        identifier: [1,'inLotNo'],
        editable: [
          [5, 'discountPercent'],
          [7, 'endDate']
        ]
      },
      onDraw: function() {
      },
      onAjax: function(action, serialize) {
        var urlParams = new URLSearchParams(serialize);
        var inLotNo = urlParams.get('inLotNo');
        var lotNo = inLotNo.split('____')[1];
        var dp = returnNumber(parseFloat(urlParams.get('discountPercent')));
        var da = returnNumber(parseFloat(urlParams.get('discountAmount')));
        var endDate = urlParams.get('endDate');
        var mrp = returnNumber(parseFloat($('#mrp_'+lotNo).text()));
        if(dp > 0 && dp <= 100 ) {
          var discountAmount = (mrp*dp/100).toFixed(2);
          if(discountAmount > 0) {
            $('#da_'+lotNo).text(discountAmount);
          } else {
            bootbox.alert({
              message: "Invalid Discount Percent. Must be between 1 - 100 !"
            });            
            return false;
          }
        /*
        } else if(da > 0) {
          var discountPercent = (da/mrp*100).toFixed(2);
          if(discountPercent > 0) {
            $('#dp_'+lotNo).text(discountPercent);
          } else {
            bootbox.alert({
              message: "Invalid Discount Amount !"
            });
            return false;
          } */
        } else {
          bootbox.alert({
            message: "Please add a valid discount percent."
          });          
          return false;
        }
        return;
      },
      onSuccess: function(axResponse, textStatus, jqXHR) {
        if(axResponse.status === 'success') {
          var responseMessage = 'Discount added successfully.';
        } else if(axResponse.status === 'failed') {
          var responseMessage = 'Error: ' + axResponse.errorcode + ', ' + axResponse.errortext;
        } else {
          var responseMessage = 'Unknown Error.';
        }
        bootbox.alert({
          message: responseMessage
        });
      }
    });
  }  

  if($('#stockAuditItems').length > 0) {
    $('#itemnames').Tabledit({
      url: '/async/auditQty?locationCode=' + $('#locationCode').val() + '&aC=' + $('#aC').val(),
      editButton: false,
      deleteButton: false,
      hideIdentifier: false,
      columns: {
        identifier: [1,'itemName'],
        editable: [
          [5, 'phyQty']
        ]
      }
    });
    $('.tabledit-input').on('keypress keydown keyup', function (e) {
       if(e.keyCode == 13) {
         e.preventDefault();
       }
    });
    $('#saLockSubmit').on('click', function (e) {
      e.preventDefault();
      bootbox.confirm("Are you sure. You want to Lock and Submit the Audit?", function(result) {
        if(result === true) {
          $('#stockAuditItems').append('<input type="hidden" name="op" value="saLockSubmit" />'); 
          $(this).attr('disabled', true);
          $('#stockAuditItems').submit();
        } else {
          return;
        }
      });      
    });
    $('#saPhyQty').on('click', function (e) {
      e.preventDefault();
      $('#stockAuditItems').append('<input type="hidden" name="op" value="saPhyQty" />'); 
      $(this).html('<i class="fa fa-recycle"></i> Processing, Please Wait....');
      $(this).attr('disabled', true);
      $('#filterSubmit, #filterReset, #printAuditReport').attr('disabled', true);
      $('#stockAuditItems').submit();
    });
    $('#printAuditReport').on('click', function(e){
      e.preventDefault();
      var auditCode = $('#aC').val();
      window.location.href = '/stock-audit/print/'+auditCode;
    });
   }

  if( $('#owBarcode').length>0 ) {

    $('#showServiceCode').on('click', function(e) {
      e.preventDefault();
      $('#serviceCode').show();
      $('#owBarcode, #showServiceCode').hide();
      $('#scanText').text('Service Code');
      $('#showBarcode').show();
    });

    $('#scanWindowMainButton').on('click', function(e) {
      e.preventDefault();
      var customerName = $('#name').val();
      if(customerName.length > 0) {
        $('#scanWindowMainDiv, #scanWindowTr').show();
      } else {
        bootbox.alert({
          message: "Customer name is mandatory :("
        });        
      }
    });

    $('#showBarcode').on('click', function(e) {
      e.preventDefault();
      $('#owBarcode').show();
      $('#serviceCode, #showBarcode').hide();
      $('#scanText').text('Scan Barcode');
      $('#showServiceCode').show();
    });

    $('#serviceCode').on('keypress', function (e) {
       if (e.keyCode == 13) {
         var serviceCode = $('#serviceCode').val();
         var locationCode = $('#locationCode').val();
         jQuery.ajax("/async/getItemDetailsByCode?sc="+serviceCode+'&locationCode='+locationCode, {
            success: function(itemDetails) {
              if(itemDetails.status === 'success') {
                var objLength = Object.keys(itemDetails.response.bcDetails).length;
                if(objLength > 0) {
                  var itemName = '';
                  jQuery.each(itemDetails.response.bcDetails, function (index, lotNoDetails) {
                    lotNosResponse[lotNoDetails.lotNo] = lotNoDetails;
                    itemName = lotNoDetails.itemName;
                  });
                  var selectedLotNoDetails = itemDetails.response.bcDetails[0];
                  var barCodeItem = '[ '+serviceCode+' ] '+itemName;
                  $('#lastScannedSaleItem').html(barCodeItem);
                  __injectOutwardItemRow(selectedLotNoDetails, serviceCode, '');
                  $('#serviceCode').val('');
                } else {
                  alert('Service code not found');                
                }
              } else {
                alert('[ '+serviceCode + ' ] Service code is not available.');
                $('#serviceCode').val('');
              }
            },
            error: function(e) {
              alert('Service code not found in the Selected Store or Location.');
            }
         });
         e.preventDefault();
       }
    });

    $('#customerType').on('change', function(e){
      if($('#tBodyowItems tr').length > 0) {
        var customerType = $(this).val();
        if(customerType === 'B2C') {
          $('#siOtherInfoWindow').hide();
          $('#packingCharges, #shippingCharges, #insuranceCharges, #otherCharges').val('');
        } else if(customerType === 'b2b') {
          $('#siOtherInfoWindow').show();
          $('#name').addClass('cnameAc');
        } else {
          $('#siOtherInfoWindow').hide();
          $('#packingCharges, #shippingCharges, #insuranceCharges, #otherCharges').val('');          
        }
      } else {
        $(this).val('B2C');
        alert('Scan Barcode first.');
      }
    });

    $(document).on('click', '#selectedDualLotNo', function(){
      $('#dualLotModal').modal('hide');
      var billingRate = $('#billingRate').val();
      var selectedLotNo = $('[name="dualLotRadios"]:checked').val();
      var barcode = $('#owBarcode').val().split('+').join("");
      var selectedLotNoDetails = lotNosResponse[selectedLotNo];
      var barCodeItem = '[ '+barcode+' ] '+selectedLotNoDetails.itemName;
      if(selectedLotNoDetails.cno !== '') {
        barCodeItem += '<span style="font-size: 12px;padding-left:5px;">CASE No: '+selectedLotNoDetails.cno+'</span>';
      }
      $('#lastScannedSaleItem').html(barCodeItem);
      __injectOutwardItemRow(selectedLotNoDetails, barcode, billingRate);
      $('#owBarcode').val('');
    });

    $(document).on('click', '#selectedDualLotNoCancel', function(){
      $('#dualLotModal').modal('hide');
      $('#owBarcode').focus().val('');
    });

    var indentItems = [];
    $('input[name="indentItems"]').each(function(i, e){
      indentItems[i] = $(this).val();
    })

    $('#owBarcode').on('keypress', function (e) {
       var billingRate = $('#billingRate').val();
       if (e.keyCode == 13) {
         var barcodeOriginal = $(this).val();
         var barcode = $(this).val().split('+').join("");
         var locationCode = $('#locationCode').val();
         jQuery.ajax("/async/getItemDetailsByCode?bc="+barcode+'&locationCode='+locationCode+'&qtyZero=false', {
            success: function(itemDetails) {
              if(itemDetails.status === 'success') {
                var objLength = Object.keys(itemDetails.response.bcDetails).length;
                if(objLength > 0) {
                  var itemName = '';
                  jQuery.each(itemDetails.response.bcDetails, function (index, lotNoDetails) {
                    lotNosResponse[lotNoDetails.lotNo] = lotNoDetails;
                    itemName = lotNoDetails.itemName;
                  });

                  // validate if indent and the scanned item is part of indent.
                  if(indentItems.length > 0) {
                    if(!indentItems.includes(itemName)) {
                      alert('Scanned item [ '+itemName+' ] does not exists in the Indent.');
                      $('#owBarcode').val('');
                      return false;
                    }
                  }

                  if(objLength === 1) {
                    var selectedLotNoDetails = itemDetails.response.bcDetails[0];
                    var barCodeItem = '[ '+barcode+' ] '+itemName;
                    if(selectedLotNoDetails.cno !== '') {
                      barCodeItem += '<span style="font-size: 12px;padding-left:5px;">CASE No: '+selectedLotNoDetails.cno+'</span>';
                    }
                    $('#lastScannedSaleItem').html(barCodeItem);
                    __injectOutwardItemRow(selectedLotNoDetails, barcode, billingRate);
                    $('#owBarcode').val('');
                  } else {
                    $('#dualLotNosTitle').text(itemName);
                    var dualBarcodesHtml = '<form>';
                    dualBarcodesHtml += '<div class="table-responsive">';
                    dualBarcodesHtml += '<table class="table table-striped table-hover font12" id="dualLotNosTable" style="margin-bottom:0px;">';
                    dualBarcodesHtml += '<thead><tr>';
                    dualBarcodesHtml += '<th>&nbsp;</th>';
                    dualBarcodesHtml += '<th style="text-align: center;">Lot No.</th>';
                    dualBarcodesHtml += '<th style="text-align: center;">Batch No.</th>';
                    dualBarcodesHtml += '<th style="text-align: center;">Cno/Box No.</th>';
                    dualBarcodesHtml += '<th style="text-align: center;">Available</th>';
                    dualBarcodesHtml += '</tr></thead>';
                    dualBarcodesHtml += '<tbody>';
                    jQuery.each(itemDetails.response.bcDetails, function (index, lotNoDetails) {
                      var lotNo = lotNoDetails.lotNo;
                      var batchNo = lotNoDetails.batchNo;
                      var availableQty = lotNoDetails.availableQty;
                      var cno = lotNoDetails.cno;
                      dualBarcodesHtml += '<tr>';
                      dualBarcodesHtml += '<td style="vertical-align: middle; padding-left: 10px;" align="center">';
                      dualBarcodesHtml += '<input type="radio" name="dualLotRadios" value="'+lotNo+'" style="visibility: visible; margin-top:0px;" />';
                      dualBarcodesHtml += '</td>';
                      dualBarcodesHtml += '<td style="vertical-align: middle">'+lotNo+'</td>';
                      dualBarcodesHtml += '<td style="vertical-align: middle">'+batchNo+'</td>';
                      dualBarcodesHtml += '<td style="vertical-align: middle">'+cno+'</td>';
                      dualBarcodesHtml += '<td style="vertical-align: middle">'+availableQty+'</td>';
                      dualBarcodesHtml += '</tr>';
                    });
                    dualBarcodesHtml += '</tbody>';
                    dualBarcodesHtml += '</table>';
                    dualBarcodesHtml += '</div>';
                    $('#dualLots').html(dualBarcodesHtml);
                    $('#dualLotModal').modal('show');
                  }
                } else {
                  alert('Barcode not found');                
                }
              } else {
                alert('[ '+barcodeOriginal + ' ] Barcode is not available or out of stock.');
                $('#owBarcode').val('');
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
            var hlIndex = hlIndexId.split('_');
            var trRowContainer = $('#tr_'+hlIndex[1]+'_'+hlIndex[2]);
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
                $('#paymentMethodWindow, #customerWindow, #splitPaymentWindow, #saveWindow, #owItemsTable, #siOtherInfoWindow, #remarksWindow').hide();
                $('#owBarcode').val('');
                $('#lastScannedSaleItem').text('');
              }
            }
          }
        } else {
          return;
        }
      });
    });

    function __injectOutwardItemRow(itemDetails, barcode, billingRate) {
      // alert('you are here.......'+barcode+billingRate);
      // console.log(itemDetails, '-----------');
      var itemName = itemDetails.itemName;
      var nextIndex = 0;
      var lotNo = itemDetails.lotNo;
      var taxPercent = itemDetails.taxPercent;
      var availableQty = itemDetails.availableQty;
      var upp = itemDetails.upp;
      var moq = itemDetails.mOq;
      var cno = itemDetails.cno;
      var hsnSacCode = itemDetails.hsnSacCode;
      var discountAmount = itemDetails.discountAmount;
      var orderQty = (parseFloat(moq)*1).toFixed(2);
      var customerType = $('#customerType').val();
      var totalRows = $('#tBodyowItems tr').length;
      var itemType = itemDetails.itemType;

      if(billingRate === 'wholesale') {
        var mrp = itemDetails.wholesalePrice;
      } else if(billingRate === 'online') {
        var mrp = itemDetails.onlinePrice;
      } else if(billingRate === 'ex') {
        var mrp = itemDetails.exMill;
      } else {
        var mrp = itemDetails.mrp;
      }      

      if( $('#editKey').length>0 ) {
        var editableMrps = $('#editKey').val();
      } else {
        var editableMrps = 0;
      }

      if( $('#dKey').length>0 ) {
        var manDisc = $('#dKey').val();
      } else {
        var manDisc = 1;
      }

      $('#paymentMethodWindow, #customerWindow, #owItemsTable, #saveWindow, #tFootowItems, #remarksWindow').show();
      if(customerType === 'b2c') {
        $('#siOtherInfoWindow').hide();
      } else if(customerType === 'b2b') {
        $('#siOtherInfoWindow').show();        
      } else {
        $('#siOtherInfoWindow').hide();        
      }
      
      if( $('#tr_'+barcode+'_'+lotNo).length > 0) {
        var trIdWithLotNo = '#tr_'+barcode+'_'+lotNo;
        var trExistingQty = $(trIdWithLotNo+' .saleItemQty').val();
        var trAddedQty = parseFloat(trExistingQty)+parseFloat(orderQty);
        var thisId = $(trIdWithLotNo).attr('index');
        $(trIdWithLotNo+' .saleItemQty').val(trAddedQty.toFixed(2));
        updateSaleItemRow(thisId);
      } else {
        if(totalRows == 0) {
          nextIndex = 1;
        } else {
          nextIndex = totalRows + 1;
        }
        var grossAmount = taxableAmount = parseFloat(mrp*1).toFixed(2);
        var tableRowBegin = '<tr id="tr_'+barcode+'_'+lotNo+'" class="bcRow" index="' + nextIndex + '">';
        var itemSlno = '<td align="right" style="vertical-align:middle;" class="itemSlno">' + nextIndex + '</td>';
        var itemNameInput = '<td style="vertical-align:middle;"><input type="text" name="itemDetails[itemName][]" id="iname_' + nextIndex +'" class="saleItem noEnterKey" size="30" index="' + nextIndex + '"  value="' + itemName + '" style="width:190px;" readonly/></td>';
        var lotNoInput = '<td style="vertical-align:middle;">'+
                         '<input type="text" class="form-control lotNo" name="itemDetails[lotNo][]" id="lotNo_' + nextIndex + '" index="' + nextIndex + '" value="' + lotNo + '"  readonly />';
        if(cno !== '') {
          lotNoInput += '<span style="font-size:11px;text-align:center;font-weight:bold;color:#FC4445">CASE: '+cno+'</span></td>';
        } else {
          lotNoInput += '</td>';
        }

        var qtyAvailableInput = '<td style="vertical-align:middle;"><input type="text" class="qtyAvailable text-right noEnterKey" name="itemDetails[itemAvailQty][]" id="qtyava_' + nextIndex + '" index="' + nextIndex + '" value="' + availableQty + '"  readonly  size="10" /></td>';
        var qtyOrderedInput = '<td style="vertical-align:middle;"><input type="text" class="form-control saleItemQty noEnterKey" name="itemDetails[itemSoldQty][]" id="qty_' + nextIndex + '" index="' + nextIndex + '" value="'+orderQty+'" style="text-align:right;font-weight:bold;font-size:14px;border:1px dashed;" /></td>';
        var mrpInput = '<td style="vertical-align:middle;"><input type="text" class="mrp text-right noEnterKey" name="itemDetails[itemRate][]" id="mrp_' + nextIndex + '" index="' + nextIndex + '" value="'+mrp+'" size="10"';
        if(parseInt(editableMrps) === 0 && itemType === 'p') {
          mrpInput += ' readonly';
        }
        mrpInput += ' /></td>';
        var grossAmount = '<td class="grossAmount" id="grossAmount_'+nextIndex+'" index="'+nextIndex+'" style="vertical-align:middle;text-align:right;">'+grossAmount+'</td>';
        var discounInput = '<td style="vertical-align:middle;"><input type="text" name="itemDetails[itemDiscount][]" id="discount_' + nextIndex + '" size="10" class="saDiscount noEnterKey" index="'+nextIndex+'" value="'+discountAmount+'"';
        if(parseInt(manDisc) === 0) {
          discounInput += ' readonly';
        }
        discounInput += ' /></td>';
        var taxableInput = '<td class="taxableAmt text-right" id="taxableAmt_'+nextIndex+'" index="'+nextIndex+'" style="vertical-align:middle;text-align:right;">'+taxableAmount+'</td>';
        var gstInput = '<td style="vertical-align:middle;"><input type="text" name="itemDetails[itemTaxPercent][]" id="saItemTax_' + nextIndex + '" size="10" class="form-control saItemTax noEnterKey"  index="'+ nextIndex +'" value="'+taxPercent+'"  />'+'</td>';
        var deleteRow = '<td style="vertical-align:middle;text-align:center;"><div class="btn-actions-group"><a class="btn btn-danger deleteOwItem" href="javascript:void(0)" title="Delete Row" id="delrow_'+barcode+'_'+lotNo+'"><i class="fa fa-times"></i></a></div></td>'; 
        var hiddenGrossAmountRow = '<input type="hidden" class="taxAmount" id="taxAmount_'+nextIndex+'" value="" />'; 
        var hiddenItemTypeRow = '<input type="hidden" class="itemType" name="itemDetails[itemType][]" id="itemType_'+nextIndex+'" value="'+itemType+'" />';
        var hiddenHsnSacRow = '<input type="hidden" class="hsnSacCode" id="hsnSac_'+nextIndex+'" value="'+hsnSacCode+'" />';       
        // var hiddenHsnSacRow = '';       
        var tableRowEnd = '</tr>';
        var tableRow = tableRowBegin + itemSlno + itemNameInput + lotNoInput + qtyAvailableInput + qtyOrderedInput + mrpInput + grossAmount + discounInput + taxableInput + gstInput + deleteRow + hiddenGrossAmountRow + hiddenItemTypeRow + hiddenHsnSacRow + tableRowEnd;
        $('#tBodyowItems').prepend(tableRow);
      }

      // trigger change
      // $('.saleItemQty').trigger('change');
      updateSaleItemRow(nextIndex);
    }
  }

  if( $('#gpBarcode').length>0 ) {

    var gpLotNoWithRates = [];
    var gpLotNoWithQtys = [];
    jQuery.each(gpLotNos, function (index, value) {
      gpLotNoWithRates[value] = gpRates[index]; // from template.
    });
    jQuery.each(gpLotNos, function (index, value) {
      gpLotNoWithQtys[value] = gpQtys[index]; // from template.
    });

    $(document).on('click', '#selectedDualLotNo', function(){
      $('#dualLotModal').modal('hide');
      var billingRate = $('#billingRate').val();
      var selectedLotNo = $('[name="dualLotRadios"]:checked').val();
      var barcode = $('#gpBarcode').val().split('+').join("");
      var selectedLotNoDetails = lotNosResponse[selectedLotNo];
      var barCodeItem = '[ '+barcode+' ] '+selectedLotNoDetails.itemName;
      if(selectedLotNoDetails.cno !== '') {
        barCodeItem += '<span style="font-size: 12px;padding-left:5px;">CASE No: '+selectedLotNoDetails.cno+'</span>';
      }
      $('#lastScannedSaleItem').html(barCodeItem);
      __injectOutwardItemRow(selectedLotNoDetails, barcode, billingRate);
      $('#gpBarcode').val('');
    });

    $(document).on('click', '#selectedDualLotNoCancel', function(){
      $('#dualLotModal').modal('hide');
      $('#gpBarcode').focus().val('');
    });

    $('#gpBarcode').on('keypress', function (e) {
       if (e.keyCode == 13) {
         var barcode = $(this).val().split('+').join("");
         var locationCode = $('#locationCode').val();
         jQuery.ajax("/async/getItemDetailsByCode?qtyZero=false&bc="+barcode+'&locationCode='+$('#locationCode').val(), {
            success: function(itemDetails) {
              if(itemDetails.status === 'success') {
                var objLength = Object.keys(itemDetails.response.bcDetails).length;
                if(objLength > 0) {
                  var itemName = '';
                  jQuery.each(itemDetails.response.bcDetails, function (index, lotNoDetails) {
                    itemName = lotNoDetails.itemName;
                  });

                  if(objLength === 1) {
                    var selectedLotNoDetails = itemDetails.response.bcDetails[0];
                    if(typeof gpLotNoWithRates[selectedLotNoDetails.lotNo] === 'undefined') {
                      alert('Invalid item (or) item does not exists in this Invoice !');
                      $('#gpBarcode').val('');
                      $('#genGp').attr('disabled', true);
                      return false;
                    } else {
                      $('#genGp').attr('disabled', false);
                    }
                    var barCodeItem = '[ '+barcode+' ] '+itemName;
                    if(selectedLotNoDetails.cno !== '') {
                      barCodeItem += '<span style="font-size: 12px;padding-left:5px;">CASE No: '+selectedLotNoDetails.cno+'</span>';
                    }
                    $('#lastScannedSaleItem').html(barCodeItem);
                    $('#gpItemsTable, #gpInvoiceInfo, #gpActionButtons').show();
                    __injectGpItemRow(selectedLotNoDetails, barcode);
                    $('#gpBarcode').val('');
                  } else {
                    $('#dualLotNosTitle').text(itemName);
                    var dualBarcodesHtml = '<form>';
                    dualBarcodesHtml += '<div class="table-responsive">';
                    dualBarcodesHtml += '<table class="table table-striped table-hover font12" id="dualLotNosTable" style="margin-bottom:0px;">';
                    dualBarcodesHtml += '<thead><tr>';
                    dualBarcodesHtml += '<th>&nbsp;</th>';
                    dualBarcodesHtml += '<th style="text-align: center;">Lot No.</th>';
                    dualBarcodesHtml += '<th style="text-align: center;">Batch No.</th>';
                    dualBarcodesHtml += '<th style="text-align: center;">Cno/Box No.</th>';
                    dualBarcodesHtml += '<th style="text-align: center;">Available</th>';
                    dualBarcodesHtml += '</tr></thead>';
                    dualBarcodesHtml += '<tbody>';
                    jQuery.each(itemDetails.response.bcDetails, function (index, lotNoDetails) {
                      var lotNo = lotNoDetails.lotNo;
                      var batchNo = lotNoDetails.bno;
                      var availableQty = lotNoDetails.closingQty;
                      var cno = lotNoDetails.cno;
                      dualBarcodesHtml += '<tr>';
                      dualBarcodesHtml += '<td style="vertical-align: middle; padding-left: 10px;" align="center">';
                      dualBarcodesHtml += '<input type="radio" name="dualLotRadios" value="'+lotNo+'" style="visibility: visible; margin-top:0px;" />';
                      dualBarcodesHtml += '</td>';
                      dualBarcodesHtml += '<td style="vertical-align: middle">'+lotNo+'</td>';
                      dualBarcodesHtml += '<td style="vertical-align: middle">'+batchNo+'</td>';
                      dualBarcodesHtml += '<td style="vertical-align: middle">'+cno+'</td>';
                      dualBarcodesHtml += '<td style="vertical-align: middle">'+availableQty+'</td>';
                      dualBarcodesHtml += '</tr>';
                    });
                    dualBarcodesHtml += '</tbody>';
                    dualBarcodesHtml += '</table>';
                    dualBarcodesHtml += '</div>';
                    $('#dualLots').html(dualBarcodesHtml);
                    $('#dualLotModal').modal('show');
                  }
                } else {
                  alert('Barcode not found');                
                }
              } else {
                alert('[ '+barcode + ' ] Barcode is not available or out of stock.');
                $('#gpBarcode').val('');
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
            var hlIndex = hlIndexId.split('_');
            var trRowContainer = $('#tr_'+hlIndex[1]+'_'+hlIndex[2]);
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
                $('#paymentMethodWindow, #customerWindow, #splitPaymentWindow, #saveWindow, #owItemsTable, #siOtherInfoWindow, #remarksWindow').hide();
                $('#owBarcode').val('');
                $('#lastScannedSaleItem').text('');
              }
            }
          }
        } else {
          return;
        }
      });
    });

    function __injectGpItemRow(itemDetails, barcode) {
      // alert('you are here.......'+barcode+billingRate);
      // console.log(itemDetails, '-----------');
      var itemName = itemDetails.itemName;
      var nextIndex = 0;
      var lotNo = itemDetails.lotNo;
      var taxPercent = itemDetails.taxPercent;
      var availableQty = itemDetails.availableQty;
      var upp = itemDetails.upp;
      var moq = itemDetails.mOq;
      var cno = itemDetails.cno;
      var hsnSacCode = itemDetails.hsnSacCode;
      var discountAmount = itemDetails.discountAmount;
      var customerType = $('#customerType').val();
      var totalRows = $('#tBodyowItems tr').length;
      var itemType = itemDetails.itemType;
      var orderQty = parseFloat(gpLotNoWithQtys[lotNo]).toFixed(2);
      var mrp = gpLotNoWithRates[lotNo];
      
      if( $('#tr_'+barcode+'_'+lotNo).length > 0) {
        var trIdWithLotNo = '#tr_'+barcode+'_'+lotNo;
        var trExistingQty = $(trIdWithLotNo+' .saleItemQty').val();
        var trAddedQty = parseFloat(trExistingQty)+parseFloat(orderQty);
        var thisId = $(trIdWithLotNo).attr('index');
        $(trIdWithLotNo+' .saleItemQty').val(trAddedQty.toFixed(2));
        // updateSaleItemRow(thisId);
      } else {
        if(totalRows == 0) {
          nextIndex = 1;
        } else {
          nextIndex = totalRows + 1;
        }
        var grossAmount = taxableAmount = parseFloat(mrp*1).toFixed(2);
        var tableRowBegin = '<tr id="tr_'+barcode+'_'+lotNo+'" class="bcRow" index="' + nextIndex + '">';
        var itemSlno = '<td align="right" style="vertical-align:middle;font-size:14px;" class="itemSlno">' + nextIndex + '</td>';
        var itemNameInput = '<td style="vertical-align:middle;font-size:14px;">' +itemName+'</td>';
        var lotNoInput = '<td style="vertical-align:middle;font-size:14px;">'+lotNo;
        if(cno !== '') {
          lotNoInput += '<span style="font-size:11px;text-align:center;font-weight:bold;color:#FC4445">CASE: '+cno+'</span></td>';
        } else {
          lotNoInput += '</td>';
        }
        var lotNoInputHidden = '<input type="hidden" value="'+lotNo+'" name="itemDetails[lotNo][]" />';

        var qtyOrderedInput = '<td style="vertical-align:middle;text-align:right;font-size:14px;">'+orderQty+'</td>';
        var mrpInput = '<td style="vertical-align:middle;font-size:14px;text-align:right;">'+mrp+'</td>';
        var tableRowEnd = '</tr>';
        var tableRow = tableRowBegin + itemSlno + itemNameInput + lotNoInput + qtyOrderedInput + mrpInput + lotNoInputHidden + tableRowEnd;
        $('#tBodyowItems').prepend(tableRow);
      }
      // updateSaleItemRow(nextIndex);
    }
  }

  if( $('#addAdjEntryFrm').length > 0) {
    var lotNosResponse = [];

    $(document).on('click', '#modalAdjustmentSelect', function(){
      $('#modalAdustment').modal('hide');
      var billingRate = $('#billingRate').val();
      var selectedLotNo = $('[name="dualLotRadios"]:checked').val();
      var barcode = $('#adjBarcode').val().split('+').join("");
      var selectedLotNoDetails = lotNosResponse[selectedLotNo];
      $('#itemName').val(selectedLotNoDetails.itemName);
      $('#lotNo').val(selectedLotNoDetails.lotNo);
      $('#adjQty').val(parseFloat(selectedLotNoDetails.availableQty).toFixed(2));
      $('#adjBarcode').val('');
    });

    $(document).on('click', '#modalAdjustmentCancel', function(){
      $('#modalAdustment').modal('hide');
      $('#adjBarcode').focus().val('');
    });    

    $('#adjBarcode').on('keypress', function(e){
      if (e.keyCode == 13) {
        var barcode = $(this).val().split('+').join("");
        var locationCode = $('#locationCode').val();
        if(locationCode === '') {
          alert('Choose a Store first.');
          return false;
        }
        var locationCode = $('#locationCode').val();
        jQuery.ajax("/async/getItemDetailsByCode?bc="+barcode+'&locationCode='+locationCode+'&qtyZero=false', {
          success: function(itemDetails) {
            if(itemDetails.status === 'success') {
              var objLength = Object.keys(itemDetails.response.bcDetails).length;
              if(objLength > 0) {
                var itemName = '';
                jQuery.each(itemDetails.response.bcDetails, function (index, lotNoDetails) {
                  lotNosResponse[lotNoDetails.lotNo] = lotNoDetails;
                  itemName = lotNoDetails.itemName;
                });
                if(objLength === 1) {
                  selectedLotNoDetails = itemDetails.response.bcDetails[0];
                  $('#itemName').val(selectedLotNoDetails.itemName);
                  $('#lotNo').val(selectedLotNoDetails.lotNo);
                  $('#adjQty').val(parseFloat(selectedLotNoDetails.availableQty).toFixed(2));
                  $('#adjBarcode').val('');
                  $('#adjBarcode').focus();
                } else {
                  $('#dualLotNosTitle').text(itemName);
                  var dualBarcodesHtml = '<form>';
                  dualBarcodesHtml += '<div class="table-responsive">';
                  dualBarcodesHtml += '<table class="table table-striped table-hover font12" id="dualLotNosTable" style="margin-bottom:0px;">';
                  dualBarcodesHtml += '<thead><tr>';
                  dualBarcodesHtml += '<th>&nbsp;</th>';
                  dualBarcodesHtml += '<th style="text-align: center;">Lot No.</th>';
                  dualBarcodesHtml += '<th style="text-align: center;">Batch No.</th>';
                  dualBarcodesHtml += '<th style="text-align: center;">Cno/Box No.</th>';
                  dualBarcodesHtml += '<th style="text-align: center;">Available</th>';
                  dualBarcodesHtml += '</tr></thead>';
                  dualBarcodesHtml += '<tbody>';
                  jQuery.each(itemDetails.response.bcDetails, function (index, lotNoDetails) {
                    var lotNo = lotNoDetails.lotNo;
                    var batchNo = lotNoDetails.bno;
                    var availableQty = lotNoDetails.closingQty;
                    var cno = lotNoDetails.cno;
                    dualBarcodesHtml += '<tr>';
                    dualBarcodesHtml += '<td style="vertical-align: middle; padding-left: 10px;" align="center">';
                    dualBarcodesHtml += '<input type="radio" name="dualLotRadios" value="'+lotNo+'" style="visibility: visible; margin-top:0px;" />';
                    dualBarcodesHtml += '</td>';
                    dualBarcodesHtml += '<td style="vertical-align: middle">'+lotNo+'</td>';
                    dualBarcodesHtml += '<td style="vertical-align: middle">'+batchNo+'</td>';
                    dualBarcodesHtml += '<td style="vertical-align: middle">'+cno+'</td>';
                    dualBarcodesHtml += '<td style="vertical-align: middle">'+availableQty+'</td>';
                    dualBarcodesHtml += '</tr>';
                  });
                  dualBarcodesHtml += '</tbody>';
                  dualBarcodesHtml += '</table>';
                  dualBarcodesHtml += '</div>';
                  $('#modalAdjustmentLotNos').html(dualBarcodesHtml);
                  $('#modalAdustment').modal('show');
                }
              } else {
                alert('Barcode not found');                
              }
            } else {
              alert('Barcode ['+barcode+'] not found in the Selected store.');
            }
          },
          error: function(e) {
            alert('Network error. Please try again.');
          }
        });
        e.preventDefault();
      }
    });
    $('#adjSave').on("click", function(e){
      $(this).attr('disabled', true);
      $('#adjSave, #adjCancel').attr('disabled', true);
      $('#addAdjEntryFrm').submit();
    });
  }

  if($('#waShippingUpdateFrm').length > 0) {
    $('#waShippingUpdate').on("click", function(e){
      e.preventDefault();
      $(this).attr('disabled', true);
      $('#waShippingUpdateCancel, #waShippingUpdate').attr('disabled', true);
      $('#waShippingUpdateFrm').submit();
    });
    $('#showDisclaimer').on("change", function(e){
      var showDisclaimer = $(this).val();
      if(parseInt(showDisclaimer) === 1) {
        $('#disclaimerMessageDiv').show();
      } else {
        $('#disclaimerMessageDiv').hide();
      }
    });
  }

  // refresh balances.
  if($('#refreshCbForm').length > 0) {
    $('#refreshCbYes').on('click', function(e){
      e.preventDefault();
      $(this).attr('disabled', true);
      $('#refreshCbWoIndentsCancel').attr('disabled', true);
      $('#infoText').text('Please wait while the Stock refreshes...');
      $('#refreshCbForm').submit();
    });

    $('#refreshCbWithIndentYes').on('click', function(e){
      e.preventDefault();
      $(this).attr('disabled', true);
      $('#refreshCbWithIndentCancel').attr('disabled', true);
      $('#infoText').text('Please wait while the Stock refreshes...');
      $('#refreshCbForm').submit();
    });
  }

  // download pos.
  if( $('#exportPOsForm').length > 0 ) {
    $('#downloadPOsAction').on('click', function(e){
      e.preventDefault();
      $(this).attr('disabled', true);
      $('#downloadPOsAction').attr('disabled', true);
      $('#exportPOsForm').submit();
    });
  }

  // delete gatepass.
  jQuery('.delGatepass').on("click", function(e){
    e.preventDefault();
    var delUrl = jQuery(this).attr('href');
    bootbox.confirm("Are you sure. You want to delete this Gatepass?", function(result) {
      if(result===true) {
        window.location.href=delUrl;
      } else {
        return;
      }
    });
  });

  // delete user
  jQuery('.delUser').on("click", function(e){
    e.preventDefault();
    var delUrl = jQuery(this).attr('href');
    bootbox.confirm("Are you sure. You want to delete this User?", function(result) {
      if(result === true) {
        window.location.href=delUrl;
      } else {
        return;
      }
    });
  });

  if( $('#galleryForm').length > 0) {
    $('#imgBarcode').on('keypress', function(e){
      if (e.keyCode == 13) {
        var barcode = $(this).val().split('+').join("");
        var locationCode = $('#locationCode').val();
        if(barcode.length !== 13) {
          alert('Invalid barcode');
          return false;
        } else if(locationCode === '') {
          alert('Choose a Store first.');
          return false;
        }
        var locationCode = $('#locationCode').val();
        jQuery.ajax("/async/getItemDetailsByCode?bc="+barcode+'&locationCode='+locationCode+'&qtyZero=false', {
          success: function(itemDetails) {
            if(itemDetails.status === 'success') {
              var objLength = Object.keys(itemDetails.response.bcDetails).length;
              if(objLength > 0) {
                var itemName = itemDetails.response.bcDetails.itemName;
                var lotNo = itemDetails.response.bcDetails.lotNo;
                var itemSku = itemDetails.response.bcDetails.itemSku;
                var itemSleeve = itemDetails.response.bcDetails.itemSleeve;
                var itemStylecode = itemDetails.response.bcDetails.itemStylecode;
                var itemColor = itemDetails.response.bcDetails.itemColor;
                $('#itemName, #itemDescription').val(itemName);
                $('#lotNo').val(lotNo);
                $('#itemSku').val(itemSku);
                $('#itemStylecode').val(itemStylecode);
                $('#itemColor').val(itemColor);
                $('#itemSleeve').val(itemSleeve);
                $('#imgBarcode').val('');
                $('#imgBarcode').focus();
              } else {
                alert('Barcode is not valid.');                
              }
            } else {
              alert('Barcode not found.');                
            }
          },
          error: function(e) {
            alert('Barcode not found in the Selected store.');
          }
        });
      }
      e.preventDefault();
    });
    $('#imgUpload').on("click", function(e){
      $(this).attr('disabled', true);
      $('#imgUpload, #imgUploadCancel').attr('disabled', true);
      $('#galleryForm').submit();
    });
    $('.showImage').on('click', function() {
      var imagePath = $(this).find('img').attr('src');
      var imageName = $(this).attr('title');
      $('#imagePreview').attr('src', imagePath);
      $('#modal-title').text(imageName);
      $('#imagemodal').modal('show');
    });
    $('#inputType').on('change', function(e){
      var inputType = $(this).val();
      if(inputType === 'barcode') {
        $('#barcodeInput').show();
        $('#imgBarcode').focus();
      } else {
        $('#barcodeInput').hide();
      }
    });
  }

  if( $('.transBarcode').length > 0) {
    var lotNosResponse = [];
    var selectedLotNoDetails = null;
    var barcode = '';
    var barcodeId = '';
    var rowId = '';

    $(document).on('click', '#modalStockTransferSelect', function(){
      $('#modalStockTransfer').modal('hide');
      var selectedLotNo = $('[name="dualLotRadios"]:checked').val();
      selectedLotNoDetails = lotNosResponse[selectedLotNo];
      updateTrasferRowFromBarcode(selectedLotNoDetails);
    });

    $(document).on('click', '#modalStockTransferCancel', function(){
      $('#dualLotModal').modal('hide');
      $('#owBarcode').focus().val('');
    });    

    $('.transBarcode').on('blur', function(e){
      var barcode = $(this).val();
      var barcodeId = $(this).attr('id');
      var rowId = barcodeId.split('_')[1];
      if(barcode === '') {
        $('#iname_'+rowId).removeAttr("readonly");
      }
    });

    function updateTrasferRowFromBarcode(selectedLotNoDetails) {
      var isLotExists = false;
      var itemName = selectedLotNoDetails.itemName;
      var availableQty = parseFloat(selectedLotNoDetails.availableQty).toFixed(2);
      var lotNo = selectedLotNoDetails.lotNo;
      var cno = selectedLotNoDetails.cno;
      var mOq = parseFloat(selectedLotNoDetails.mOq).toFixed(2);
      var taxPercent = parseFloat(selectedLotNoDetails.taxPercent).toFixed(2);
      var mrp = parseFloat(selectedLotNoDetails.mrp).toFixed(2);
      var transferQty = parseFloat(mOq*1).toFixed(2);
      var itemAmount = parseFloat(mrp*transferQty).toFixed(2);
      var lotNoRef = $('#lotNo_'+rowId);
      var isLotExists = false;
      var existingLotId = null;
      var selectedLotId = selectedLotNo = null;

      /* check if lot no is already selected. */
      jQuery('.lotNo').each(function(i, obj) {
        selectedLotNo = $(this).val();
        selectedLotId = $(this).attr('id');
        if(selectedLotNo === lotNo && rowId !== i) {
          isLotExists = true;
          existingLotId = i;
        }
      });

      if(isLotExists) {
        $('#barcode_'+rowId).focus().val('');
        var selectedLotIndex = existingLotId;
        var selectedLotQty = parseFloat($('#qty_'+selectedLotIndex).val());
        var newTransferQty = parseFloat(transferQty)+parseFloat(selectedLotQty); 
        var itemAmount = parseFloat(mrp*newTransferQty).toFixed(2);
        var availQty = parseFloat($('#qtyava_'+selectedLotIndex).val());
        if(newTransferQty <= availableQty) {
          $('#qty_'+selectedLotIndex).val(newTransferQty);
          $('#grossAmount_'+selectedLotIndex).text(itemAmount);
          updateTransferOutItemRow(selectedLotIndex);
        } else {
          alert('Available  Qty. is less than Transfer Qty!! Please check.');
          return false;
        }
      } else {
        $('#iname_'+rowId).attr('readonly', 'readonly');
        $('#iname_'+rowId).val(itemName);
        $('#qtyava_'+rowId).val(availableQty);
        $('#cno_'+rowId).text(cno);
        $('#qty_'+rowId).val(transferQty);
        $('#mrp_'+rowId).val(mrp);
        $('#grossAmount_'+rowId).text(itemAmount);
        $('#saItemTax_'+rowId+' option[value="'+taxPercent+'"]').attr('selected', 'selected');
        
        $('#qtyava_'+rowId).attr('readonly', 'readonly');
        $('#mrp_'+rowId).attr('readonly', 'readonly');
        jQuery(lotNoRef).html(
          jQuery("<option></option>").
          attr("value",lotNo).
          text(lotNo)
        );
        updateTransferOutItemRow(rowId);
        var nextRowId = rowId+1;
        $('#barcode_'+nextRowId).focus();
      }
    }

    $('.transBarcode').on('keypress', function (e) {
     if (e.keyCode == 13) {
        barcode = $(this).val().split('+').join("");
        barcodeId = $(this).attr('id');
        rowId = parseInt(barcodeId.split('_')[1]);
       var locationCode = $('#fromLocation').val();
       jQuery.ajax("/async/getItemDetailsByCode?bc="+barcode+'&locationCode='+locationCode+'&qtyZero=false', {
          success: function(itemDetails) {
            if(itemDetails.status === 'success') {
              var objLength = Object.keys(itemDetails.response.bcDetails).length;
              if(objLength > 0) {
                var itemName = '';
                jQuery.each(itemDetails.response.bcDetails, function (index, lotNoDetails) {
                  lotNosResponse[lotNoDetails.lotNo] = lotNoDetails;
                  itemName = lotNoDetails.itemName;
                });
                if(objLength === 1) {
                  selectedLotNoDetails = itemDetails.response.bcDetails[0];
                  updateTrasferRowFromBarcode(selectedLotNoDetails);
                } else {
                  $('#dualLotNosTitle').text(itemName);
                  var dualBarcodesHtml = '<form>';
                  dualBarcodesHtml += '<div class="table-responsive">';
                  dualBarcodesHtml += '<table class="table table-striped table-hover font12" id="dualLotNosTable" style="margin-bottom:0px;">';
                  dualBarcodesHtml += '<thead><tr>';
                  dualBarcodesHtml += '<th>&nbsp;</th>';
                  dualBarcodesHtml += '<th style="text-align: center;">Lot No.</th>';
                  dualBarcodesHtml += '<th style="text-align: center;">Batch No.</th>';
                  dualBarcodesHtml += '<th style="text-align: center;">Cno/Box No.</th>';
                  dualBarcodesHtml += '<th style="text-align: center;">Available</th>';
                  dualBarcodesHtml += '</tr></thead>';
                  dualBarcodesHtml += '<tbody>';
                  jQuery.each(itemDetails.response.bcDetails, function (index, lotNoDetails) {
                    var lotNo = lotNoDetails.lotNo;
                    var batchNo = lotNoDetails.bno;
                    var availableQty = lotNoDetails.closingQty;
                    var cno = lotNoDetails.cno;
                    dualBarcodesHtml += '<tr>';
                    dualBarcodesHtml += '<td style="vertical-align: middle; padding-left: 10px;" align="center">';
                    dualBarcodesHtml += '<input type="radio" name="dualLotRadios" value="'+lotNo+'" style="visibility: visible; margin-top:0px;" />';
                    dualBarcodesHtml += '</td>';
                    dualBarcodesHtml += '<td style="vertical-align: middle">'+lotNo+'</td>';
                    dualBarcodesHtml += '<td style="vertical-align: middle">'+batchNo+'</td>';
                    dualBarcodesHtml += '<td style="vertical-align: middle">'+cno+'</td>';
                    dualBarcodesHtml += '<td style="vertical-align: middle">'+availableQty+'</td>';
                    dualBarcodesHtml += '</tr>';
                  });
                  dualBarcodesHtml += '</tbody>';
                  dualBarcodesHtml += '</table>';
                  dualBarcodesHtml += '</div>';
                  $('#modalStockTransferLotNos').html(dualBarcodesHtml);
                  $('#modalStockTransfer').modal('show');
                }
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
  }

  if( $('#stBarcode').length>0 ) {
    $('#stBarcode').on('keypress', function (e) {
     if (e.keyCode == 13) {
       var barcode = $(this).val().split('+').join("");
       // if(barcode.length !== 13) {
       //  alert('Invalid barcode');
       //  return false;
       // }
       var locationCode = $('#locationCode').val();
       var transferCode = $('#transferCode').val();
       var scannedQty = 0;
       var transferQty = parseFloat($('#trTransQty').text());
       if(scannedQty < transferQty) {
         jQuery.ajax("/async/getTrDetailsByCode?barcode="+barcode+'&locationCode='+locationCode+'&transferCode='+transferCode+'&qtyZero=false', {
           beforeSend: function() {
              $('#stBarcode').attr('disabled', true);
              $('#stBarcode').val('Validating Barcode. Please wait...');              
            },
            success: function(itemDetails) {
              if(itemDetails.status === 'success') {
                scannedQty += parseFloat(itemDetails.qty);
                transferQty = parseFloat($('#trTransQty').text());
                var diffQty = parseFloat(transferQty - scannedQty);
                // console.log(scannedQty, transferQty, diffQty);
                if(diffQty == 0) {
                  $('#stBarcode').attr('disabled', true);
                  bootbox.alert({
                    message: "Scanning completed. Click on Save Button / స్కానింగ్ పూర్తి ఐనది. ఈ బదిలీ సేవ్ చేయటానికి అంగీకరణ బటన్ ప్రెస్ చెయ్యండి."
                  });
                  $('#stBarcode').val('');
                  $('#trScannedQty').text(scannedQty.toFixed(2));
                  $('#trDiff').text(diffQty.toFixed(2));                  
                } else if(diffQty < 0) {
                  $('#stBarcode').attr('disabled', true);
                  var message = "Scanned Qty. is more than actual Transfer Qty. and it is invalid. Please Rescan Again / స్కాన్ చేసిన సరుకు అసలు ట్రాన్స్ఫర్ చేయబడిన సరుకు కన్నా ఎక్కువగా ఉన్నది. ఇది చెల్లదు. మీరు మరల సరి చూసుకొని స్కాన్ చేయ గలరు.";
                  alert(message);
                  if($('#transferCode').length>0) {
                    window.location.href = "/stock-transfer/validate/"+$('#transferCode').val();
                  } else {
                    window.location.href = "/stock-transfer/list";
                  }
                } else {
                  $('#trScannedQty').text(scannedQty.toFixed(2));
                  $('#trDiff').text(diffQty.toFixed(2));
                  $('#stBarcode').attr('disabled', false);
                  $('#stBarcode').val('');
                  $('#stBarcode').focus();
                }
              } else {
                bootbox.alert({
                  message: itemDetails.error
                });
                $('#stBarcode').attr('disabled', false);
                $('#stBarcode').val('');
                $('#stBarcode').focus();
              }
            },
            error: function(e) {
              bootbox.alert({
                message: "Barcode not found in this transfer. Please check. / మీరు స్కాన్ చేసిన బార్ కోడ్ ఈ ట్రాన్స్ఫర్ ఆర్డర్  లో లేదు. దయచేసి సరి చూసుకోండి."
              });
              $('#stBarcode').attr('disabled', false);
              $('#stBarcode').val('');
              $('#stBarcode').focus();
            }
         });
       } else {
          bootbox.alert({
            message: "Scanning completed already. Click on Save Button / స్కానింగ్ పూర్తి ఐనది. ఈ బదిలీ సేవ్ చేయటానికి అంగీకరణ బటన్ ప్రెస్ చెయ్యండి."
          });
       }
       e.preventDefault();
     }
    });
  }

  if( $('#samplesBarcode').length>0 ) {
    $('#samplesBarcode').on('keypress', function (e) {
     if (e.keyCode == 13) {
      var barcode = $(this).val().split('+').join("");
      jQuery.ajax("/async/samplesCheck?barcode="+barcode, {
        beforeSend: function() {
          $('#samplesBarcode').attr('disabled', true);
          $('#samplesBarcode').val('Validating Barcode. Please wait...');              
        },
        success: function(itemDetails) {
          if(itemDetails.status === 'success') {
            var itemName = itemDetails.response.itemName;
            var brandName = itemDetails.response.brandName;
            var closingQty =  itemDetails.response.closingQty;
            var categoryName = itemDetails.response.categoryName;
            // var exMill = itemDetails.response.exmillPrice;
            var exMill = 'n/a';
            var wholesale = itemDetails.response.wholesalePrice;
            var onlinePrice = itemDetails.response.onlinePrice;
            var mrp = itemDetails.response.mrp;
            var uomName = itemDetails.response.uomName;
            $('#itemName').text(itemName);
            $('#avaQty').text(closingQty+' '+uomName.toLowerCase());
            $('#brandName').text(brandName);
            $('#categoryName').text(categoryName);
            // $('#exMill').text(parseFloat(exMill).toFixed(2));
            $('#exMill').text(exMill);
            $('#wholesale').text(parseFloat(wholesale).toFixed(2));
            $('#mrp').text(parseFloat(mrp).toFixed(2));
            $('#online').text(parseFloat(onlinePrice).toFixed(2));

            $('#sampleShower').show();

            $('#samplesBarcode').attr('disabled', false);
            $('#samplesBarcode').val('');
            $('#samplesBarcode').focus();
          } else {
            $('#samplesBarcode').focus();
            bootbox.alert({
              message: 'Invalid Barcode (or) Out of stock!'
            });
            $('#samplesBarcode').attr('disabled', false);
            $('#samplesBarcode').val('');
            $('#itemName').text('');
            $('#avaQty').text('');
            $('#brandName').text('');
            $('#categoryName').text('');
            $('#exMill').text('');
            $('#wholesale').text('');
            $('#mrp').text('');
            $('#online').text('');
            $('#sampleShower').hide();
          }
        },
        error: function(e) {
          bootbox.alert({
            message: "Barcode not found"
          });
          $('#samplesBarcode').attr('disabled', false);
          $('#samplesBarcode').val('');
          $('#samplesBarcode').focus();
          $('#itemName').text('');
          $('#avaQty').text('');
          $('#brandName').text('');
          $('#categoryName').text('');
          $('#exMill').text('');
          $('#wholesale').text('');
          $('#mrp').text('');
          $('#online').text('');          
          $('#sampleShower').hide();
        }
      });
       e.preventDefault();
     }
    });
  }  

  if( $('#indentBarcode').length>0 ) {
    var lotNosResponse = [];

    function updateIndentFormTotals() {
      var totalQty = totalAmount = 0;
      $(".saleItemQty").each(function(index, obj) {
        totalQty += returnNumber(parseFloat($(this).val()));
      });
      $(".grossAmount").each(function(index, obj) {
        totalAmount += returnNumber(parseFloat($(this).text()));
      });
      totalQty = totalQty.toFixed(2);
      totalAmount = totalAmount.toFixed(2);
      $('#totalItems, #indentScannedQty').text(totalQty);
      $('#grossAmount').text(totalAmount);
    }

    $(document).on('click', '#selectedDualLotNoIndentCancel', function(){
      $('#dualLotModalIndent').modal('hide');
      $('#indentBarcode').focus().val('');
    });

    $(document).on('click', '#selectedDualLotNoIndent', function(){
      $('#dualLotModalIndent').modal('hide');
      var billingRate = $('#billingRate').val();
      var selectedLotNo = $('[name="dualLotRadios"]:checked').val();
      var barcode = $('#indentBarcode').val();
      var selectedLotNoDetails = lotNosResponse[selectedLotNo];
      var barCodeItem = '[ '+barcode+' ] '+selectedLotNoDetails.itemName;
      if(selectedLotNoDetails.cno !== '') {
        barCodeItem += '<span style="font-size: 12px;padding-left:5px;">CASE No: '+selectedLotNoDetails.cno+'</span>';
      }
      $('#lastScannedSaleItem').html(barCodeItem);
      __injectIndentItemRow(selectedLotNoDetails, barcode, billingRate);
      $('#indentBarcode').focus().val('');
    });

    if( $('.messageContainer').length>0 ) {
      $('.messageContainer').fadeOut(5000);
    }

    if( $('#billingRate').length>0 ) {
      $('#billingRate').on('change', function(){
        window.location.href = '/sales-indent/create?br='+$('#billingRate').val();
      });
    }    

    $('#indentBarcode').on('keypress', function (e) {
     if (e.keyCode == 13) {
       // var barcode = parseInt($(this).val());
       // if(isNaN(barcode)) {
       //  alert('Invalid Barcode format...');
       //  return false;
       // }
       var barcode = $(this).val().split('+').join("");
       var locationCode = $('#locationCode').val();
       var billingRate = $('#billingRate').val();
       jQuery.ajax("/async/getItemDetailsByCode?bc="+barcode+'&locationCode='+locationCode+'&sl=true&ind=true'+'&qtyZero=false', {
          success: function(itemDetails) {
            if(itemDetails.status === 'success') {
              var objLength = Object.keys(itemDetails.response.bcDetails).length;
              if(objLength > 0) {
                var itemName = '';
                jQuery.each(itemDetails.response.bcDetails, function (index, lotNoDetails) {
                  lotNosResponse[lotNoDetails.lotNo] = lotNoDetails;
                  itemName = lotNoDetails.itemName;
                });
                if(objLength === 1) {
                  var selectedLotNoDetails = itemDetails.response.bcDetails[0];
                  var barCodeItem = '[ '+barcode+' ] '+itemName;
                  if(selectedLotNoDetails.cno !== '') {
                    barCodeItem += '<span style="font-size: 12px;padding-left:5px;">CASE No: '+selectedLotNoDetails.cno+'</span>';
                  }
                  $('#lastScannedSaleItem').html(barCodeItem);
                  __injectIndentItemRow(selectedLotNoDetails, barcode, billingRate);
                  $('#indentBarcode').val('');
                } else {
                  $('#dualLotNosTitleIndent').text(itemName);
                  var dualBarcodesHtml = '<form>';
                  dualBarcodesHtml += '<div class="table-responsive">';
                  dualBarcodesHtml += '<table class="table table-striped table-hover font12" id="dualLotNosTable" style="margin-bottom:0px;">';
                  dualBarcodesHtml += '<thead><tr>';
                  dualBarcodesHtml += '<th>&nbsp;</th>';
                  dualBarcodesHtml += '<th style="text-align: center;">Lot No.</th>';
                  dualBarcodesHtml += '<th style="text-align: center;">Batch No.</th>';
                  dualBarcodesHtml += '<th style="text-align: center;">Cno/Box No.</th>';
                  dualBarcodesHtml += '<th style="text-align: center;">Available</th>';
                  dualBarcodesHtml += '</tr></thead>';
                  dualBarcodesHtml += '<tbody>';
                  jQuery.each(itemDetails.response.bcDetails, function (index, lotNoDetails) {
                    var lotNo = lotNoDetails.lotNo;
                    var batchNo = lotNoDetails.bno;
                    var availableQty = lotNoDetails.closingQty;
                    var cno = lotNoDetails.cno;
                    dualBarcodesHtml += '<tr>';
                    dualBarcodesHtml += '<td style="vertical-align: middle; padding-left: 10px;" align="center">';
                    dualBarcodesHtml += '<input type="radio" name="dualLotRadios" value="'+lotNo+'" style="visibility: visible; margin-top:0px;" />';
                    dualBarcodesHtml += '</td>';
                    dualBarcodesHtml += '<td style="vertical-align: middle">'+lotNo+'</td>';
                    dualBarcodesHtml += '<td style="vertical-align: middle">'+batchNo+'</td>';
                    dualBarcodesHtml += '<td style="vertical-align: middle">'+cno+'</td>';
                    dualBarcodesHtml += '<td style="vertical-align: middle">'+availableQty+'</td>';
                    dualBarcodesHtml += '</tr>';
                  });
                  dualBarcodesHtml += '</tbody>';
                  dualBarcodesHtml += '</table>';
                  dualBarcodesHtml += '</div>';
                  $('#dualLotsIndent').html(dualBarcodesHtml);
                  $('#dualLotModalIndent').modal('show');
                }
                // if(itemDetails.response.bcDetails.availableQty <= 0) {
                //   alert("Qty. not available");
                //   $('#indentBarcode').focus();
                //   $('#indentBarcode').val('');                  
                //   return false;
                // }
                // __injectIndentItemRow(itemDetails.response.bcDetails, barcode);
              } else {
                alert('Barcode not found');
              }
            } else {
              var errorText = itemDetails.reason;
              alert("[ "+ barcode + ' ] ' + errorText);
              $('#indentBarcode').focus();
              $('#indentBarcode').val('');              
            }
          },
          error: function(e) {
            alert('An error occurred while fetching Barcode');
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
            var hlIndex = hlIndexId.split('_');
            var trRowContainer = $('#tr_'+hlIndex[1]+'_'+hlIndex[2]);
            if(trRowContainer.length > 0) {
              trRowContainer.remove();
              if($('#tBodyowItems tr').length > 0) {
                var newSlno = 1;
                $(".itemSlno").each(function(index, obj) {
                  $(this).text(newSlno);
                  newSlno++;
                });
                updateIndentFormTotals();
              } else {
                $('#totalItems, #grossAmount, #totDiscount, #taxableAmount, #gstAmount, #roundOff, #netPayBottom').text('');
                $('#paymentMethodWindow, #customerWindow, #splitPaymentWindow, #saveWindow, #owItemsTable').hide();
                $('#owBarcode').val('');
                $('#indentScannedQty, #lastScannedSaleItem').text('');                
              }
            }
          }
        } else {
          return;
        }
      });
    });

    function __injectIndentItemRow(itemDetails, barcode, billingRate) {
      var itemName = itemDetails.itemName;
      var nextIndex = 0;
      var lotNo = itemDetails.lotNo;
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

      if(billingRate === 'wholesale') {
        var mrp = itemDetails.wholesalePrice;
      } else if(billingRate === 'online') {
        var mrp = itemDetails.onlinePrice;
      } else if(billingRate === 'ex') {
        var mrp = itemDetails.exMill;        
      } else {
        var mrp = itemDetails.mrp;
      }

      $('#customerWindow, #owItemsTable, #saveWindow, #tFootowItems').show();
      
      if( $('#tr_'+barcode+'_'+lotNo).length > 0) {
        var trIdWithLotNo = '#tr_'+barcode+'_'+lotNo;
        var trIndex = $(trIdWithLotNo).attr('index');
        var trExistingQty = $(trIdWithLotNo+' .saleItemQty').val();
        var trAddedQty = parseFloat(trExistingQty) + parseFloat(orderQty);
        var grossAmount = parseFloat(parseFloat(trAddedQty)*parseFloat(mrp)).toFixed(2);
        $(trIdWithLotNo+' .saleItemQty').val(trAddedQty); 
        $('#grossAmount_'+trIndex).text(grossAmount);
      } else {
        if(totalRows == 0) {
          nextIndex = 1;
        } else {
          nextIndex = totalRows + 1;
        }
        var grossAmount = taxableAmount = parseFloat(mrp*moq).toFixed(2);
        var tableRowBegin = '<tr id="tr_'+barcode+'_'+lotNo+'" class="bcRow" index="' + nextIndex + '">';
        var itemSlno = '<td align="right" style="vertical-align:middle;" class="itemSlno">' + nextIndex + '</td>';
        var itemNameInput = '<td style="vertical-align:middle;"><input type="text" name="itemDetails[itemName][]" id="iname_' + nextIndex +'" class="saleItem noEnterKey" index="' + nextIndex + '"  value="' + itemName + '" size="30" readonly/></td>';
        var lotNoInput = '<td style="vertical-align:middle;"><input type="text" class="form-control lotNo" name="itemDetails[lotNo][]" id="lotNo_' + nextIndex + '" index="' + nextIndex + '" value="' + lotNo + '"  readonly /></td>';
        var qtyOrderedInput = '<td style="vertical-align:middle;"><input type="text" class="form-control saleItemQty noEnterKey" name="itemDetails[itemSoldQty][]" id="qty_' + nextIndex + '" index="' + nextIndex + '" value="'+orderQty+'" style="text-align:right;font-weight:bold;font-size:14px;border:1px dashed;" readonly="readonly" /></td>';
        var mrpInput = '<td style="vertical-align:middle;text-align:center;"><input type="text" class="mrp text-right noEnterKey" name="itemDetails[itemRate][]" id="mrp_' + nextIndex + '" index="' + nextIndex + '" value="'+mrp+'" size="10" /></td>';
        var grossAmount = '<td class="grossAmount" id="grossAmount_'+nextIndex+'" index="'+nextIndex+'" style="vertical-align:middle;text-align:right;">'+grossAmount+'</td>';
        var deleteRow = '<td style="vertical-align:middle;text-align:center;"><div class="btn-actions-group"><a class="btn btn-danger deleteOwItem" href="javascript:void(0)" title="Delete Row" id="delrow_'+barcode+'_'+lotNo+'"><i class="fa fa-times"></i></a></div></td>';
        var barcodeInput = '<input type="hidden" class="noEnterKey" name="itemDetails[barcode][]" id="barcode_' + nextIndex + '" index="' + nextIndex + '" value="'+barcode+'" size="13" />';        
        var tableRowEnd = '</tr>';
        var tableRow = tableRowBegin + itemSlno + itemNameInput + lotNoInput + qtyOrderedInput + mrpInput + grossAmount + deleteRow + barcodeInput + tableRowEnd;
        $('#tBodyowItems').prepend(tableRow);
      }
      $('#locationCode').val(locationCode);
      updateIndentFormTotals();
    }
  }

  if( $('#indentBarcodeSample').length>0 ) {
    var lotNosResponse = [];

    function updateIndentFormTotals() {
      var totalQty = totalAmount = 0;
      $(".saleItemQty").each(function(index, obj) {
        totalQty += returnNumber(parseFloat($(this).val()));
      });
      $(".grossAmount").each(function(index, obj) {
        totalAmount += returnNumber(parseFloat($(this).text()));
      });
      totalQty = totalQty.toFixed(2);
      totalAmount = totalAmount.toFixed(2);
      $('#totalItems, #indentScannedQty').text(totalQty);
      $('#grossAmount').text(totalAmount);
    }

    $(document).on('click', '#selectedDualLotNoIndentCancel', function(){
      $('#dualLotModalIndent').modal('hide');
      $('#indentBarcode').focus().val('');
    });

    $(document).on('click', '#selectedDualLotNoIndent', function(){
      $('#dualLotModalIndent').modal('hide');
      var billingRate = $('#billingRate').val();
      var selectedLotNo = $('[name="dualLotRadios"]:checked').val();
      var barcode = $('#indentBarcode').val();
      var selectedLotNoDetails = lotNosResponse[selectedLotNo];
      var barCodeItem = '[ '+barcode+' ] '+selectedLotNoDetails.itemName;
      if(selectedLotNoDetails.cno !== '') {
        barCodeItem += '<span style="font-size: 12px;padding-left:5px;">CASE No: '+selectedLotNoDetails.cno+'</span>';
      }
      $('#lastScannedSaleItem').html(barCodeItem);
      __injectIndentItemRow(selectedLotNoDetails, barcode, billingRate);
      $('#indentBarcode').focus().val('');
    });

    if( $('.messageContainer').length>0 ) {
      $('.messageContainer').fadeOut(5000);
    }

    if( $('#billingRate').length>0 ) {
      $('#billingRate').on('change', function(){
        window.location.href = '/sales-indent/create-from-samples?br='+$('#billingRate').val();
      });
    }    

    $('#indentBarcodeSample').on('keypress', function (e) {
     if (e.keyCode == 13) {
       // var barcode = parseInt($(this).val());
       // if(isNaN(barcode)) {
       //  alert('Invalid Barcode format...');
       //  return false;
       // }
       var barcode = $(this).val().split('+').join("");
       var locationCode = $('#locationCode').val();
       var billingRate = $('#billingRate').val();
       jQuery.ajax("/async/getItemDetailsByBarcodeSample?bc="+barcode, {
          success: function(itemDetails) {
            // console.log('itemDetails....', itemDetails);
            if(itemDetails.status === 'success') {
              var objLength = Object.keys(itemDetails.response).length;
              if(objLength > 0) {
                var itemName = '';
                jQuery.each(itemDetails.response, function (index, lotNoDetails) {
                  lotNosResponse[lotNoDetails.lotNo] = lotNoDetails;
                });
                var selectedLotNoDetails = itemDetails.response;
                var barCodeItem = '[ '+barcode+' ] '+itemDetails.response.itemName;
                if(selectedLotNoDetails.cno !== '') {
                  barCodeItem += '<span style="font-size: 12px;padding-left:5px;"></span>';
                }
                $('#lastScannedSaleItem').html(barCodeItem);
                __injectIndentItemRow(selectedLotNoDetails, barcode, billingRate);
                $('#indentBarcodeSample').val('');
              } else {
                alert('Barcode not found');
              }
            } else {
              var errorText = itemDetails.reason;
              alert("[ "+ barcode + ' ] ' + errorText);
              $('#indentBarcode').focus();
              $('#indentBarcode').val('');              
            }
          },
          error: function(e) {
            alert('An error occurred while fetching Barcode');
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
            var hlIndex = hlIndexId.split('_');
            var trRowContainer = $('#tr_'+hlIndex[1]+'_'+hlIndex[2]);
            if(trRowContainer.length > 0) {
              trRowContainer.remove();
              if($('#tBodyowItems tr').length > 0) {
                var newSlno = 1;
                $(".itemSlno").each(function(index, obj) {
                  $(this).text(newSlno);
                  newSlno++;
                });
                updateIndentFormTotals();
              } else {
                $('#totalItems, #grossAmount, #totDiscount, #taxableAmount, #gstAmount, #roundOff, #netPayBottom').text('');
                $('#paymentMethodWindow, #customerWindow, #splitPaymentWindow, #saveWindow, #owItemsTable').hide();
                $('#owBarcode').val('');
                $('#indentScannedQty, #lastScannedSaleItem').text('');                
              }
            }
          }
        } else {
          return;
        }
      });
    });

    function __injectIndentItemRow(itemDetails, barcode, billingRate) {
      var itemName = itemDetails.itemName;
      var nextIndex = 0;
      var lotNo = itemDetails.lotNo;
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

      if(billingRate === 'wholesale') {
        var mrp = itemDetails.wholesalePrice;
      } else if(billingRate === 'online') {
        var mrp = itemDetails.onlinePrice;
      } else if(billingRate === 'ex') {
        var mrp = itemDetails.exMill;        
      } else {
        var mrp = itemDetails.mrp;
      }

      $('#customerWindow, #owItemsTable, #saveWindow, #tFootowItems').show();
      
      if( $('#tr_'+barcode+'_'+lotNo).length > 0) {
        var trIdWithLotNo = '#tr_'+barcode+'_'+lotNo;
        var trIndex = $(trIdWithLotNo).attr('index');
        var trExistingQty = $(trIdWithLotNo+' .saleItemQty').val();
        var trAddedQty = parseFloat(trExistingQty) + parseFloat(orderQty);
        var grossAmount = parseFloat(parseFloat(trAddedQty)*parseFloat(mrp)).toFixed(2);
        $(trIdWithLotNo+' .saleItemQty').val(trAddedQty); 
        $('#grossAmount_'+trIndex).text(grossAmount);
      } else {
        if(totalRows == 0) {
          nextIndex = 1;
        } else {
          nextIndex = totalRows + 1;
        }
        var grossAmount = taxableAmount = parseFloat(mrp*moq).toFixed(2);
        var tableRowBegin = '<tr id="tr_'+barcode+'_'+lotNo+'" class="bcRow" index="' + nextIndex + '">';
        var itemSlno = '<td align="right" style="vertical-align:middle;" class="itemSlno">' + nextIndex + '</td>';
        var itemNameInput = '<td style="vertical-align:middle;"><input type="text" name="itemDetails[itemName][]" id="iname_' + nextIndex +'" class="saleItem noEnterKey" index="' + nextIndex + '"  value="' + itemName + '" size="30" readonly/></td>';
        var lotNoInput = '<td style="vertical-align:middle;"><input type="text" class="form-control lotNo" name="itemDetails[lotNo][]" id="lotNo_' + nextIndex + '" index="' + nextIndex + '" value="' + lotNo + '"  readonly /></td>';
        var qtyOrderedInput = '<td style="vertical-align:middle;"><input type="text" class="form-control saleItemQty noEnterKey" name="itemDetails[itemSoldQty][]" id="qty_' + nextIndex + '" index="' + nextIndex + '" value="'+orderQty+'" style="text-align:right;font-weight:bold;font-size:14px;border:1px dashed;" readonly="readonly" /></td>';
        var mrpInput = '<td style="vertical-align:middle;text-align:center;"><input type="text" class="mrp text-right noEnterKey" name="itemDetails[itemRate][]" id="mrp_' + nextIndex + '" index="' + nextIndex + '" value="'+mrp+'" size="10" /></td>';
        var grossAmount = '<td class="grossAmount" id="grossAmount_'+nextIndex+'" index="'+nextIndex+'" style="vertical-align:middle;text-align:right;">'+grossAmount+'</td>';
        var deleteRow = '<td style="vertical-align:middle;text-align:center;"><div class="btn-actions-group"><a class="btn btn-danger deleteOwItem" href="javascript:void(0)" title="Delete Row" id="delrow_'+barcode+'_'+lotNo+'"><i class="fa fa-times"></i></a></div></td>';
        var barcodeInput = '<input type="hidden" class="noEnterKey" name="itemDetails[barcode][]" id="barcode_' + nextIndex + '" index="' + nextIndex + '" value="'+barcode+'" size="13" />';        
        var tableRowEnd = '</tr>';
        var tableRow = tableRowBegin + itemSlno + itemNameInput + lotNoInput + qtyOrderedInput + mrpInput + grossAmount + deleteRow + barcodeInput + tableRowEnd;
        $('#tBodyowItems').prepend(tableRow);
      }
      $('#locationCode').val(locationCode);
      updateIndentFormTotals();
    }
  }


  if( $('#salesIndentMobileV').length>0 ) {
    var lotNosResponse = [];
    // getGeoLocation();
    $('#mobileIndentItem').on('click', function(e){
      e.preventDefault();
      var itemName = jQuery('#itemName').val();
      var bnoFirstOption = jQuery("<option></option>").attr("value","").text("Choose");        
      if(itemName !== '') {
       var data = {itemName:itemName};
       jQuery.ajax("/async/getItemBatchesByCode", {
          data: data,
          method:"POST",
          success: function(lotNos) {
            if(lotNos.status === 'success') {
              var objLength = Object.keys(lotNos).length;
              var lotNoRef = jQuery('#lotNo');
              if(objLength>0) {
                // console.log(lotNosResponse, 'lot nos reponse....');
                lotNoRef.empty().append(bnoFirstOption);
                jQuery.each(lotNos.response.bcDetails, function (index, lotNoDetails) {
                  lotNosResponse[lotNoDetails.lotNo] = lotNoDetails;
                  jQuery(lotNoRef).append(
                    jQuery("<option></option>").
                    attr("value",lotNoDetails.lotNo).
                    text(lotNoDetails.mOq + ' [ Available: ' + lotNoDetails.availableQty + ' ] - '+lotNoDetails.lotNo)
                  );
                });
                $('.itemOtherInfo, .formButtons').show();
              }
            } else {
              alert('Item not available.');
            }
          },
          error: function(e) {
            alert('Out of Stock !');
          }
       });
      } else if(parseInt(itemName.length) === 0){
        alert('Item name required.');
      }
    });
    $('.indentOrderQty').on('blur', function(e){
      var lotNo = jQuery('#lotNo').val();
      var orderQty = returnNumber(parseFloat($('#orderQty').val()));
      if( (lotNo in lotNosResponse) && orderQty > 0) {
        var availableQty = returnNumber(parseFloat(lotNosResponse[lotNo].availableQty));
        var mOq = returnNumber(parseFloat(lotNosResponse[lotNo].mOq));
        var mrp = returnNumber(parseFloat(lotNosResponse[lotNo].mrp));
        var wholesalePrice =  returnNumber(parseFloat(lotNosResponse[lotNo].wholesalePrice));
        orderQty = returnNumber(parseFloat(orderQty*mOq).toFixed(2));
        if(orderQty > availableQty) {
          alert('Order Qty. must be less than or equal to available Qty.');
          $(this).val('');
          $(this).focus();
          return false;
        }
        if(wholesalePrice > 0) {
          $('#mrp').val(wholesalePrice);
        } else {
          $('#mrp').val(mrp);
        }
        $('#orderQty').val(orderQty);
      }
    });
  }

  $('.cancelButton').on('click', function(e) {
    e.preventDefault();
    $(this).attr('disabled', true);
    $('.cancelOp').attr('disabled', true);
    var buttonId = $(this).attr('id');
    if(buttonId === 'stoCancel') {
      window.location.href = '/stock-transfer/choose-location';
    } else if(buttonId === 'inwBulkUploadCancel') {
      window.location.href = '/inward-entry/bulk-upload';      
    } else if(buttonId === 'seWithBarcode') {
      window.location.href = '/sales/entry-with-barcode';
    } else if(buttonId === 'seWoBarcode') {
      window.location.href = '/sales/entry';
    } else if(buttonId === 'scombos') {
      window.location.href = '/sales-entry/combos';      
    } else if(buttonId === 'ieWithBarcode') {
      window.location.href = '/sales-indent/create';
    } else if(buttonId === 'uploadCustomers') {
      window.location.href = '/upload-debtors';
    } else if(buttonId === 'uploadSuppliers') {
      window.location.href = '/upload-creditors';
    } else if(buttonId === 'validateStButton') {
      window.location.href = '/stock-transfer/register';
    } else if(buttonId === 'shippingPage') {
      window.location.href = '/sales/list';      
    } else if(buttonId === 'stransfer') {
      window.location.href = '/stock-transfer/register';      
    } else if(buttonId === 'comboCancel') {
      window.location.href = '/sales-combo/add';      
    } else if(buttonId === 'adjCancel') {
      window.location.href = '/inventory/stock-adjustment';
    } else if(buttonId === 'imgUploadCancel') {
      window.location.href = '/gallery/create';
    } else if(buttonId === 'imgUpdateCancel') {
      var gc = $('#gc').val();
      var lc = $('#lc').val();
      window.location.href = '/gallery/update'+'/'+lc+'/'+gc;      
    } else if(buttonId === 'catalogAddCancel') {
      window.location.href = '/catalog/create';
    } else if(buttonId === 'catalogUpdateCancel') {
      window.location.href = '/catalog/list';
    } else if(buttonId === 'manCreditNoteCancel') {
      window.location.href = '/fin/credit-note/create';
    } else if(buttonId === 'manDebitNoteCancel') {
      window.location.href = '/fin/debit-note/create';
    } else if(buttonId === 'arIndentStatus') {
      window.location.href = '/sales-indents/list';      
    } else if(buttonId === 'bulkUpdateSellingPrice') {
      window.location.href = '/inventory/change-selling-price';      
    } else if(buttonId === 'leadCreate') {
      window.location.href = '/lead/create';      
    } else if(buttonId === 'leadUpdate') {
      window.location.href = '/leads/list';
    } else if(buttonId === 'leadImport') {
      window.location.href = '/lead/import';        
    } else if(buttonId === 'taskSave') {
      window.location.href = '/task/create';        
    } else if(buttonId === 'appointmentSave') {
      window.location.href = '/appointment/create';    
    } else if(buttonId === 'appointmentUpdate') {
      window.location.href = '/appointments/list';
    } else if(buttonId === 'genGpCancel') {
      window.location.href = '/get-invoice-no';
    } else if(buttonId === 'ecomCatCancel') {
      window.location.href = '/ecom/category/create';
    } else if(buttonId === 'ecomCreateAppCancel') {
      window.location.href = '/ecom/app-content/create';       
    } else if(buttonId === 'refreshCbWoIndentsCancel') {
      window.location.href = '/dashboard';       
    } else if(buttonId === 'refreshCbWithIndentCancel') {
      window.location.href = '/dashboard';
    } else if(buttonId === 'waShippingUpdateCancel') {
      window.location.href = '/dashboard';        
    } else if(buttonId === 'exportIndent') {
      window.location.href = '/dashboard';
    } else if(buttonId === 'downloadPO') {
      window.location.href = '/admin-options/export-pos';      
    } else if(buttonId === 'cancelIrnCancel' || buttonId === 'cancelGenerateEinvoice') {
      window.location.href = '/einvoices/list';      
    }
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

  // stock transfer delete entry
  if( $('.delStransfer').length>0 ) {
    jQuery('.delStransfer').on("click", function(e){
      e.preventDefault();
      var delUrl = jQuery(this).attr('href');
      bootbox.confirm("Are you sure. You want to remove this Stock transfer?", function(result) {
        if(result===true) {
          window.location.href=delUrl;
        } else {
          return;
        }
      });
    });
  }

  // delete catalog. 
  if( $('.delCatalog').length>0 ) {
    jQuery('.delCatalog').on("click", function(e){
      e.preventDefault();
      var delUrl = jQuery(this).attr('href');
      bootbox.confirm("Are you sure. You want to delete this Catalog?", function(result) {
        if(result===true) {
          window.location.href=delUrl;
        } else {
          return;
        }
      });
    });
  }

  // delete gallery
  if( $('.delGallery').length>0 ) {
    jQuery('.delGallery').on("click", function(e){
      e.preventDefault();
      var delUrl = jQuery(this).attr('href');
      bootbox.confirm("Are you sure. You want to delete this Gallery?", function(result) {
        if(result===true) {
          window.location.href=delUrl;
        } else {
          return;
        }
      });
    });
  }

  // delete ecom subcategory
  if( $('.delEcomSubcat').length>0 ) {
    jQuery('.delEcomSubcat').on("click", function(e){
      e.preventDefault();
      var delUrl = jQuery(this).attr('href');
      bootbox.confirm("Are you sure. You want to delete this Category?", function(result) {
        if(result===true) {
          window.location.href=delUrl;
        } else {
          return;
        }
      });
    });
  }

  //delete ecom content
  if( $('.delEcomContent').length>0 ) {
    jQuery('.delEcomContent').on("click", function(e){
      e.preventDefault();
      var delUrl = jQuery(this).attr('href');
      bootbox.confirm("Are you sure. You want to delete this Content?", function(result) {
        if(result===true) {
          window.location.href=delUrl;
        } else {
          return;
        }
      });
    });
  }  

  if( $('.delCNote').length>0 ) {
    jQuery('.delCNote').on("click", function(e){
      e.preventDefault();
      var delUrl = jQuery(this).attr('href');
      bootbox.confirm("Are you sure. You want to delete this Credit Note?", function(result) {
        if(result===true) {
          window.location.href=delUrl;
        } else {
          return;
        }
      });
    });
  }

  if( $('.delDNote').length>0 ) {
    jQuery('.delDNote').on("click", function(e){
      e.preventDefault();
      var delUrl = jQuery(this).attr('href');
      bootbox.confirm("Are you sure. You want to delete this Debit note?", function(result) {
        if(result===true) {
          window.location.href=delUrl;
        } else {
          return;
        }
      });
    });
  }

  if( $('.taskDelete').length>0 ) {
    jQuery('.taskDelete').on("click", function(e){
      e.preventDefault();
      var delUrl = jQuery(this).attr('href');
      bootbox.confirm("Are you sure. You want to delete this Task?", function(result) {
        if(result===true) {
          window.location.href=delUrl;
        } else {
          return;
        }
      });
    });
  }

  if( $('.appointmentDelete').length>0 ) {
    jQuery('.appointmentDelete').on("click", function(e){
      e.preventDefault();
      var delUrl = jQuery(this).attr('href');
      bootbox.confirm("Are you sure. You want to delete this Appointment?", function(result) {
        if(result===true) {
          window.location.href=delUrl;
        } else {
          return;
        }
      });
    });
  }  

  if( $('.leadDelete').length>0 ) {
    jQuery('.leadDelete').on("click", function(e){
      e.preventDefault();
      var delUrl = jQuery(this).attr('href');
      bootbox.confirm("Are you sure. You want to delete this Lead?", function(result) {
        if(result===true) {
          window.location.href=delUrl;
        } else {
          return;
        }
      });
    });
  }  

  // add item to catalog
  if($('.addItemToCatalog').length > 0) {
    $('.addItemToCatalog').on('click', function(e){
      e.preventDefault();
      var queryParams = $(this).attr('href');
      if(queryParams.length > 0) {
        var catalogArray = queryParams.split('/');
        jQuery.ajax("/async-catalogitem", {
          data: {lc: catalogArray[0], cc: catalogArray[1], ic:catalogArray[2]},
          method:"POST",
          success: function(response) {
            if(response.status==='success') {
              alert('Item added to Catalog successfully :)');
              window.location.reload(true);
            } else if(response.status==='failed') {
              alert(response.reason);
            } 
          },
          error: function(e) {
            alert('An error occurred while add Item to Catalog :(');
          }
        });
      } else {
        return false;
      }
    });
  }

  // remove item from catalog
  if($('.removeItemFromCatalog').length > 0) {
    $('.removeItemFromCatalog').on('click', function(e){
      e.preventDefault();
      var queryParams = $(this).attr('href');
      if(queryParams.length > 0) {
        jQuery.ajax("/async-catalogitem-remove", {
          data: {ic:queryParams},
          method:"POST",
          success: function(response) {
            if(response.status==='success') {
              alert('Item removed from Catalog successfully :)');
              window.location.reload(true);
            } else if(response.status==='failed') {
              alert(response.reason);
            } 
          },
          error: function(e) {
            alert('An error occurred while removing item from Catalog :(');
          }
        });
      } else {
        return false;
      }
    });
  }  

  // delete discount configuration.
  if( $('.delDiscount').length>0 ) {
    jQuery('.delDiscount').on("click", function(e){
      e.preventDefault();
      var delUrl = jQuery(this).attr('href');
      bootbox.confirm("Are you sure. You want to remove this Discount entry?", function(result) {
        if(result===true) {
          window.location.href = delUrl;
        } else {
          return;
        }
      });
    });
  }

  if($('#searchPurchaseBills').length > 0) {
    $('#searchBy').on('change', function(e){
      var searchBy = $(this).val();
      if(searchBy === 'itemname') {
        $('#svProducts').show();
        $('#svAll').hide();
      } else {
        $('#svProducts').hide();
        $('#svAll').show();
      }
      $('#searchValue, #searchValueP').val('');      
    });
  }

  // stockout form
  if( $('#stockOutForm').length>0 ) {
    var lotNosResponse = [];

    $('#transferSubmitBtn').on("click", function(e){
      $(this).attr('disabled', true);
      $('#stransfer').attr('disabled', true);
      $('#stockOutForm').submit();
    });

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
                  var cno = lotNoDetails.cno;
                  var lotNoText = lotNoDetails.lotNo;
                  if(cno !== '') {
                    lotNoText += ' [ CASE: ' + cno + ' ]';
                  }
                  lotNosResponse[lotNoDetails.lotNo] = lotNoDetails;
                  jQuery(lotNoRef).append(
                    jQuery("<option></option>").
                    attr("value",lotNoDetails.lotNo).
                    text(lotNoText)
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
        jQuery('#qty_'+itemIndex).val('');
      }
    });

    jQuery('.lotNo').on("change", function(e){
      var qtyAvailable = itemRate = itemType = '';
      var itemIndex = jQuery(this).attr('index');
      var lotNo = jQuery(this).val();
      var avaQtyContainer = jQuery('#qtyava_'+itemIndex);
      var mrpContainer = jQuery('#mrp_'+itemIndex);
      var itemTypeContainer = jQuery('#itemType_'+itemIndex);
      var itemName = jQuery('#iname_'+itemIndex).val();
      if(lotNo !== '') {
        var selectedLotNo = '';
        var lotFound = false;
        jQuery('.lotNo').each(function(i, obj) {
          selectedLotNo = $(this).val();
          if(selectedLotNo === lotNo && parseInt(itemIndex) !== parseInt(i)) {
            lotFound = true;
          }
        });

        if(lotFound) {
          $('#lotNo_'+itemIndex).val('');
          jQuery('#qtyava_'+itemIndex).val('');
          jQuery('#qty_'+itemIndex).val('');
          jQuery('#mrp_'+itemIndex).val('');
          jQuery('#iname_'+itemIndex).val('');
          jQuery('#barcode_'+itemIndex).val('');
          bootbox.alert({
            message: "This Lot No. is already selected for `" +itemName+"`. A Lot No. must be unique and selected only once in the bill against the same item."
          });
          return false;              
        } else if(Object.keys(lotNosResponse[lotNo]).length>0) {
          jQuery('#qtyava_'+itemIndex).val(lotNosResponse[lotNo].closingQty);
          jQuery('#qty_'+itemIndex).val(lotNosResponse[lotNo].mOq);
          jQuery('#mrp_'+itemIndex).val(lotNosResponse[lotNo].mrp);
          jQuery('#itemType_'+itemIndex).val(lotNosResponse[lotNo].itemType);
          jQuery('#saItemTax_'+itemIndex+' option[value="'+lotNosResponse[lotNo].taxPercent+'"]').attr('selected', 'selected');
          updateTransferOutItemRow(itemIndex);
        }
      }
    });

    jQuery('.saleItemQty').on('blur', function(){
      var transferQty = parseInt($(this).val());
      var itemIndex = jQuery(this).attr('index');
      var lotNoRef = $('#lotNo_'+itemIndex);
      var avaQty = $('#qtyava_'+itemIndex).val();
      var bnoFirstOption = jQuery("<option></option>").attr("value","").text("Choose");
      if(transferQty>0 && (parseFloat(transferQty) <= parseFloat(avaQty))) {
        updateTransferOutItemRow(itemIndex);
      } else {
        bootbox.alert({
          message: "Transfer Qty. is more than Available Qty."
        });
        jQuery(lotNoRef).empty().append(bnoFirstOption);
        jQuery('#qtyava_'+itemIndex).val('');
        jQuery('#mrp_'+itemIndex).val('');
        jQuery('#itemType_'+itemIndex).val('');
        jQuery('#saItemTax_'+itemIndex+' option[value="'+''+'"]').attr('selected', 'selected');
        jQuery('#qty_'+itemIndex).val('');
        jQuery('#qtyava_'+itemIndex).val('');
        jQuery('#iname_'+itemIndex).val('');
        jQuery('#grossAmount_'+itemIndex).text('');
        updateTransferOutItemRow(itemIndex);
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
        //updateTransferOutApplicableTax(itemIndex, grossAmount, reqQty);
      }
      $('#saItemTax_'+itemIndex+' option[value="'+taxPercent+'"]').attr('selected', 'selected');            
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
      var shippingCharges = insuranceCharges = otherCharges = 0;
      var totAvailableQty = totTransferQty = 0;
      var taxCalcOption = $('#taxCalcOption').val();

      jQuery('.grossAmount').each(function(i, obj) {
        iTotal = returnNumber(parseFloat(jQuery(this).text()));
        totGrossAmount += iTotal;
      });

      jQuery('.qtyAvailable').each(function(i, obj) {
        iTotal = returnNumber(parseFloat(jQuery(this).val()));
        totAvailableQty += iTotal;
      });

      jQuery('.saleItemQty').each(function(i, obj) {
        iTotal = returnNumber(parseFloat(jQuery(this).val()));
        totTransferQty += iTotal;
      });         

      if(taxCalcOption === 'i') {
        totTaxAmount = 0;
      } else {
        jQuery('.taxAmount').each(function(i, obj) {
          iTotal = returnNumber(parseFloat(jQuery(this).val()));
          totTaxAmount += iTotal;
        });
      }

      // console.log(totAvailableQty, totTransferQty);

      $('#grossAmount').text(parseFloat(totGrossAmount).toFixed(2));
      $('#gstAmount').text(parseFloat(totTaxAmount).toFixed(2));
      $('.stAvaQty').text(parseFloat(totAvailableQty).toFixed(2));
      $('.stTraQty').text(parseFloat(totTransferQty).toFixed(2));

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
      extraParams:{
        locationCode: function() {
           if($('#locationCode').length>0) {
            return $('#locationCode').val();
           } else if($('#fromLocation').length>0) {
            return $('#fromLocation').val();
           } else {
            return '';
           }
        }
      },
      'max': 0,
    });
  }

  // catalogAC
  if(jQuery('.catalogAc').length>0) {
    $('.catalogAc').autocomplete("/async/catalogAc", {
      width: 342,
      cacheLength:0,
      selectFirst:false,
      minChars:2,
      'max': 0,
    });
  }

  // catalogItemAc
  if(jQuery('.catalogItemAc').length>0) {
    $('.catalogItemAc').autocomplete("/async/catalogItemAc", {
      width: 342,
      cacheLength:0,
      selectFirst:false,
      minChars:2,
      'max': 0,
    });
  }

  // brand names autocomplete
  if(jQuery('.brandAc').length>0) {
    $('.brandAc').autocomplete("/async/brandAc", {
      width: 300,
      cacheLength:0,
      selectFirst:false,
      minChars:1,
      extraParams:{
        locationCode: function() {
           if($('#locationCode').length>0) {
            return $('#locationCode').val()
           } else {
            return '';
           }
        }
      },
      'max': 0,
    });
  }

  // categories autocomplete
  if(jQuery('.catAc').length>0) {
    $('.catAc').autocomplete("/async/catAc", {
      width: 300,
      cacheLength:0,
      selectFirst:false,
      minChars:1,
      extraParams:{
        locationCode: function() {
         if($('#locationCode').length>0) {
          return $('#locationCode').val()
         } else {
          return '';
         }
        }
      },
      'max': 0,
    });
  }

  // UOMs autocomplete
  if(jQuery('.uomAc').length>0) {
    $('.uomAc').autocomplete("/async/uomAc", {
      width: 300,
      cacheLength:0,
      selectFirst:false,
      minChars:1,
      extraParams:{
        locationCode: function() {
         if($('#locationCode').length>0) {
          return $('#locationCode').val()
         } else {
          return '';
         }
        }
      },
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
      if(customerType === 'b2b') {
        $('#bioContainer').hide();
        $('#age').val(0);
        $('#ageCategory').val('years');
        $('#gender').val('');
      } else {
        $('#bioContainer').show();
      }
    });
  }

  if($('#editPOAfterGRN').length>0) {
    $('#grnDelete').on('click', function(e){
      e.preventDefault();
      bootbox.confirm("Are you sure. You want to delete and re-create GRN for this PO?", function(result) {
        if(result===true) {
          $(this).attr('disabled', true);
          $('#editPOAfterGRN').submit();
        } else {
          return;
        }
      });
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

  if( $('#delInvoiceForm').length>0 ) {
    jQuery('#invoiceDelete').on('click', function(e){
      e.preventDefault();
      bootbox.confirm("Are you sure. You want to delete this Invoice?", function(result) {
        if(result===true) {
          $(this).attr('disabled', true);
          $('#delInvoiceForm').submit();
        } else {
          return;
        }
      });
    });
  }  

  // inward entry form
  if( $('#inwardEntryForm').length>0 ) {

    jQuery('.inwRcvdQty, .inwFreeQty, .inwItemRate, .inwItemDiscount').on("blur",function(e){
      var idArray = $(this).attr('id').split('_');
      var rowId = idArray[1];
      updateInwardItemRow(rowId);

      // autofill brand, category, hsn, uom if exists against item.
      var brandName = $('#brandName_'+rowId).val();
      var categoryName = $('#categoryName_'+rowId).val();
      var uom = $('#uom_'+rowId).val();
      var barcode = $('#barcode_'+rowId).val();
      var hsnSacCode = $('#hsnSacCode'+rowId).val();
      if(brandName === '' || categoryName === '' || uom === '' || hsnSacCode === '') {
        var itemName = $('#itemName_'+rowId).val();
        jQuery.ajax("/async/itd?pn="+itemName+"&locationCode="+$('#locationCode').val(), {
          method: "GET",
          success: function(apiResponse) {
            if(apiResponse.status === 'success') {
              $('#brandName_'+rowId).val(apiResponse.response.mfgName);
              $('#categoryName_'+rowId).val(apiResponse.response.catName);
              $('#uom_'+rowId).val(apiResponse.response.uom);
              $('#hsnSacCode_'+rowId).val(apiResponse.response.hsnSacCode);
              $('#barcode_'+rowId).val(apiResponse.response.barcode);
/*            } else {
              alert('Brand, Category, UOM, HSN/SAC Code and Supplier Barcode not found in master.');*/
            }
          },
          error: function(e) {
            alert('An error occurred while fetching Item Information.');
          }
        });
      }
    });

    jQuery('#packingCharges, #shippingCharges, #insuranceCharges, #otherCharges').on("blur", function(e){
      var packingCharges = returnNumber(parseFloat($('#packingCharges').val()));
      var shippingCharges = returnNumber(parseFloat($('#shippingCharges').val()));
      var insuranceCharges = returnNumber(parseFloat($('#insuranceCharges').val()));
      var otherCharges = returnNumber(parseFloat($('#otherCharges').val()));
      var totTaxableAmount = totalTaxAmount = finalAmount = 0;
      var netPay = roundedNetPay = 0;

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

      netPay = parseFloat(totTaxableAmount+totalTaxAmount) + parseFloat(packingCharges) + parseFloat(shippingCharges) + parseFloat(insuranceCharges) + parseFloat(otherCharges);
      roundedNetPay = Math.round(netPay);
      finalAmount = netPay-parseFloat(roundedNetPay.toFixed(2));

      $('#inwItemsTotal').text(totTaxableAmount.toFixed(2));
      $('#inwItemTaxAmount').text(totalTaxAmount.toFixed(2));
      $('#roundOff').text(finalAmount.toFixed(2));
      $('#inwNetPay').text(roundedNetPay);
    });

    jQuery('.inwItemTax').on("change", function(){
      var idArray = $(this).attr('id').split('_');
      var rowId = idArray[1];
      $('#inwItemTaxAmt_'+rowId).attr('data-rate', parseFloat($(this).val()).toFixed(2) );

      updateInwardItemRow(rowId);
    });

    jQuery('#supplierID').on('change', function(e){
      var supplierCode = $(this).val();
      if(supplierCode === '') {
        $('#supplierState, #supplierGSTNo, #supplyType').val('');
      } else {
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
      }
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
      var packedQty = parseFloat( returnNumber($('#packed_'+rowId).val()) );

      var billedQty = (rcvdQty - freeQty)*packedQty;
      var inwItemGrossAmount = parseFloat( returnNumber(billedQty*itemRate) );
      var inwItemAmount = parseFloat( returnNumber(inwItemGrossAmount-inwItemDiscount) );
      var inwItemTaxAmount = parseFloat((inwItemAmount * inwItemTax) / 100).toFixed(2);

      var packingCharges = returnNumber(parseFloat($('#packingCharges').val()));
      var shippingCharges = returnNumber(parseFloat($('#shippingCharges').val()));
      var insuranceCharges = returnNumber(parseFloat($('#insuranceCharges').val()));
      var otherCharges = returnNumber(parseFloat($('#otherCharges').val()));

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

      netPay = parseFloat(totTaxableAmount+totalTaxAmount) + parseFloat(packingCharges) + parseFloat(shippingCharges) + parseFloat(insuranceCharges) + parseFloat(otherCharges);
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

            totalTaxable = (parseFloat(totalTaxable) + parseFloat(thisGrossAmount)).toFixed(2); 
            totalTax = (parseFloat(totalTax) + parseFloat($(this).val())).toFixed(2);
          }
        });

        // console.log('totalTax is...', typeof totalTax, totalTax);

        $("#taxAmount_"+taxCode).val(parseFloat(totalTax).toFixed(2));

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
      });
    }
  }

  // outward entry form
  if( $('#outwardEntryForm').length>0 ) {

    var lotNosResponse = [];

    $('#customerType').on('change', function(e){
      var customerType = $(this).val();
      if(customerType === 'b2c') {
        $('#siOtherInfoWindow').hide();
        $('#packingCharges, #shippingCharges, #insuranceCharges, #otherCharges').val('');
      } else if(customerType === 'b2b') {
        $('#siOtherInfoWindow').show();
        $('#name').addClass('cnameAc');
      } else {
        $('#siOtherInfoWindow').hide();
        $('#packingCharges, #shippingCharges, #insuranceCharges, #otherCharges').val('');          
      }
    });

    $('#SaveInvoice').on('click', function(e){
      e.preventDefault();
      $(this).attr('disabled', true);
      $('.cancelButton').attr('disabled', true);
      $('#outwardEntryForm').submit();
    });

    // show Card No and authcode if Payment mode is credit. Show Split Payment inputs as well
    $('#saPaymentMethod').on('change', function(){
      var paymentMethod = parseInt($(this).val());
      if(paymentMethod === 1) {
        $('#containerCardNo, #containerAuthCode').show();
        $('#containerWalletName, #containerWalletRef').hide();
      } else if(paymentMethod === 3) {
        $('#containerCrDays').show();
        $('#containerCardNo, #containerAuthCode, #containerWalletName, #containerWalletRef').hide();
      } else if(paymentMethod === 4) {
        $('#containerWalletName, #containerWalletRef').show();
        $('#containerCardNo, #containerAuthCode, #containerCrDays').hide();        
      } else {
        $('#containerCardNo, #containerAuthCode, #containerCrDays, #containerWalletName, #containerWalletRef').hide();
      }
      /* enable multiple pay options if it is split payment */
      if(paymentMethod === 2) {
        $('#splitPaymentWindow').show();
        $('#splitPaymentCash, #splitPaymentCard, #splitPaymentCn, #cnNo, #splitPaymentWallet').attr('disabled', false);
        $('#containerCardNo, #containerAuthCode, #containerWalletName, #containerWalletRef').show();
        $('#containerCrDays').hide();
      } else {
        $('#splitPaymentCash, #splitPaymentCard, #splitPaymentCn, #cnNo, #splitPaymentWallet').val('');
        $('#splitPaymentCash, #splitPaymentCard, #splitPaymentCn, #cnNo, #splitPaymentWallet').attr('disabled', true);
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
      if(itemName !== '') {
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
                  text(lotNoDetails.lotNo + ' [ Packing: ' + lotNoDetails.mOq + ' ]')
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
      var itemIndex = parseInt(jQuery(this).attr('index'));
      var lotNo = jQuery(this).val();
      var avaQtyContainer = jQuery('#qtyava_'+itemIndex);
      var mrpContainer = jQuery('#mrp_'+itemIndex);
      var itemTypeContainer = jQuery('#itemType_'+itemIndex);
      var billingRate = $('#billingRate').val();
      if(lotNo !== '') {
        if(Object.keys(lotNosResponse[lotNo]).length>0) {
          var selectedLotNo = '';
          jQuery('.lotNo').each(function(i, obj) {
            selectedLotNo = $(this).val();
            if(selectedLotNo === lotNo && itemIndex !== i) {
              $('#lotNo_'+itemIndex).val('');
              bootbox.alert({
                message: "This Lot No. is already selected.  A Lot No. must be unique and selected only once in the bill against the same item."
              });
              return false;              
            }
          });
          jQuery('#qtyava_'+itemIndex).val(lotNosResponse[lotNo].closingQty);
          jQuery('#qty_'+itemIndex).val(lotNosResponse[lotNo].mOq);
          
          if(billingRate === 'wholesale') {
            jQuery('#mrp_'+itemIndex).val(lotNosResponse[lotNo].wholesalePrice);
          } else if(billingRate === 'online') {
            jQuery('#mrp_'+itemIndex).val(lotNosResponse[lotNo].onlinePrice);
          } else if(billingRate === 'ex') {
            jQuery('#mrp_'+itemIndex).val(lotNosResponse[lotNo].mrpDn);
          } else {
            jQuery('#mrp_'+itemIndex).val(lotNosResponse[lotNo].mrp);
          }
          jQuery('#itemType_'+itemIndex).val(lotNosResponse[lotNo].itemType);
          jQuery('#saItemTax_'+itemIndex+' option[value="'+lotNosResponse[lotNo].taxPercent+'"]').attr('selected', 'selected');
          if(returnNumber(parseFloat(lotNosResponse[lotNo].discountAmount)) > 0) {
            var discountAmount = returnNumber(parseFloat(lotNosResponse[lotNo].discountAmount)) * returnNumber(parseFloat(lotNosResponse[lotNo].mOq));
          } else {
            var discountAmount = 0;
          }
          $('#discount_'+itemIndex).val(discountAmount.toFixed(2));
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

    $('#outwardEntryForm').on('change', '.saleItemQty', function() {
      var itemIndex = jQuery(this).attr('index');
      var avaQty = returnNumber(parseFloat($('#qtyava_'+itemIndex).val()));
      var itemType = $('#itemType_'+itemIndex).val();
      var itemQty = returnNumber(parseFloat($(this).val()));
      if($('#in').length === 0) {
        if(itemQty > avaQty && itemType === 'p') {
          alert('Qty. not available');
          $(this).val('');
          $(this).focus();
          return false;
        }
      }
      if(typeof lotNosResponse !== 'undefined' && lotNosResponse.length > 0) {
        var itemQty = $(this).val();
        var lotNo = $('#lotNo_'+itemIndex).val();
        if(returnNumber(parseFloat(lotNosResponse[lotNo].discountAmount)) > 0) {
          var discountAmount = returnNumber(parseFloat(lotNosResponse[lotNo].discountAmount)) * returnNumber(parseFloat(itemQty));
        } else {
          var discountAmount = 0;
        }
        $('#discount_'+itemIndex).val(discountAmount.toFixed(2));
      }
      updateSaleItemRow(itemIndex);
    });

    $('#outwardEntryForm').on('change', '.mrp', function(){
      var itemIndex = jQuery(this).attr('index');      
      updateSaleItemRow(itemIndex);
    });

    // $('#outwardEntryForm').on('blur', '.saItemTax', function(){
    //   var itemIndex = jQuery(this).attr('index');
    //   updateSaleItemRow(itemIndex);
    // });

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

      // alert(itemIndex+' updateSaleItemRow()');

      var grossAmount = parseFloat(mrp * reqQty).toFixed(2);
      var taxableAmount = parseFloat(grossAmount - discount).toFixed(2);
      var taxAmount = (taxableAmount * taxPercent/100).toFixed(2);
      var itemType = $('#itemType_'+itemIndex).val();

      $('#taxAmount_'+itemIndex).val(taxAmount);
      $('#grossAmount_'+itemIndex).text(grossAmount);
      $('#taxableAmt_'+itemIndex).text(taxableAmount);

      // console.log('hsn sac code is....', hsnSacCode, itemIndex, $('#hsnSac_'+itemIndex).val());

      updateApplicableTax(itemIndex, taxableAmount, reqQty);
      updateSaleItemTotal();
    }

    // update applicable tax
    function updateApplicableTax(itemIndex, taxableAmount, reqQty) {
      if(parseFloat(reqQty)>0) {
        var hsnSacCode = $('#hsnSac_'+itemIndex).val();
        jQuery.ajax("/async/get-tax-percent?taxableValue="+taxableAmount+'&reqQty='+reqQty+'&hsn='+hsnSacCode+'&dm=cl', {
          success: function(response) {
            if(response.status === 'success') {
              var taxPercent = parseFloat(response.taxPercent).toFixed(2);
              // $('#saItemTax_'+itemIndex+' option[value="'+taxPercent+'"]').attr('selected', 'selected');            
              $('#saItemTax_'+itemIndex).val(taxPercent);            
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
        var itemType = jQuery(this).attr('index');
        if($('#iname_'+itemIndex).val() !== '' && $('#itemType_'+itemIndex).val() === 'p') {
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

  // manual credit note Form
  if( $('#manCreditNoteForm').length>0 ) {
    $('#manCreditNoteForm').on('change', '.saleItemQty', function() {
      var itemIndex = jQuery(this).attr('index');
      updateManualCreditNoteFormRow(itemIndex);
    });
    $('#manCreditNoteForm').on('change', '.mrp', function() {
      var itemIndex = jQuery(this).attr('index');
      updateManualCreditNoteFormRow(itemIndex);
    });
    $('#manCreditNoteForm').on('change', '.saItemTax', function() {
      var itemIndex = jQuery(this).attr('index');
      updateManualCreditNoteFormRow(itemIndex);
    });
    $('#manCreditNoteForm').on('change', '#taxCalcOption', function(){
      var totalsTaxable = totalsGST = totalsItems = 0;
      var taxCalcOption = $(this).val();
      $('#manCreditNoteForm .saleItemQty').each(function(index) {
        var rowId = index+1;
        var itemQty = returnNumber(parseFloat($('#qty_'+rowId).val()));
        var mrp = returnNumber(parseFloat($('#mrp_'+rowId).val()));
        var gstPercent = returnNumber(parseFloat($('#saItemTax_'+rowId).val()));
        if(taxCalcOption === 'i') {
          var taxableAmount = Math.round(itemQty*mrp, 2);
          var totalAmount = taxableAmount;
          var gstValue = 0;
        } else {
          var taxableAmount = Math.round(itemQty*mrp, 2);
          var gstValue = Math.round(taxableAmount*gstPercent/100, 2);
          var totalAmount = taxableAmount+gstValue;
        }
        if(itemQty > 0 && mrp > 0) {
          $('#taxable_'+rowId).text(taxableAmount.toFixed(2));
          $('#taxAmount_'+rowId).text(gstValue.toFixed(2));
          $('#totalAmount_'+rowId).text(totalAmount.toFixed(2));
        }
      });

      updateManualCreditNoteFormTotals();
    });
    $('#manCreditNoteForm').on('change', '.mCreditNoteType', function() {
      var mCreditNoteType = jQuery(this).val();
      if(mCreditNoteType === 'wo') {
        $('#mCnOptionalFields').hide();
        $('#mCnItems').hide();
        $('#cnValue').val('');
        for(i=0;i<10;i++) {
          var index = i+1;
          $('#iname_'+index).val('');
          $('#qty_'+index).val('');
          $('#mrp_'+index).val('');
          $('#purchaseRate_'+index).val('');
          $('#saItemTax_'+index).val('');
          $('#taxable_'+index).text('');
          $('#taxAmount_'+index).text('');
          $('#totalAmount_'+index).text('');
        }
        $('.totalTaxable, .totalGST, .totalAmount').text('');
      } else {
        $('#mCnOptionalFields').show();
        $('#mCnItems').show();
      }
    });
    function updateManualCreditNoteFormRow(rowId) {
      var itemQty = returnNumber(parseFloat($('#qty_'+rowId).val()));
      var mrp = returnNumber(parseFloat($('#mrp_'+rowId).val()));
      var gstPercent = returnNumber(parseFloat($('#saItemTax_'+rowId).val()));
      var taxCalcOption = $('#taxCalcOption').val();
      if(taxCalcOption === 'i') {
        var taxableAmount = Math.round(itemQty*mrp, 2);
        var totalAmount = taxableAmount;
        var gstValue = 0;
      } else {
        var taxableAmount = Math.round(itemQty*mrp, 2);
        var gstValue = Math.round(taxableAmount*gstPercent/100, 2);
        var totalAmount = taxableAmount+gstValue;
      }

      $('#taxable_'+rowId).text(taxableAmount.toFixed(2));
      $('#taxAmount_'+rowId).text(gstValue.toFixed(2));
      $('#totalAmount_'+rowId).text(totalAmount.toFixed(2));
      updateManualCreditNoteFormTotals();
    }
    function updateManualCreditNoteFormTotals() {
      var totalTaxable = totalGST = totalAmount = 0;
      jQuery('.rowTaxable').each(function(i, obj) {
        var taxable = jQuery(this).text();
        var gst = $('#taxAmount_'+i).text();
        var amount = $('#totalAmount_'+i).text();
        totalTaxable += returnNumber(parseFloat(taxable));
        totalGST += returnNumber(parseFloat(gst));
        totalAmount += returnNumber(parseFloat(amount));
      });
      $('.totalTaxable').text(totalTaxable.toFixed(2));
      $('.totalGST').text(totalGST.toFixed(2));
      $('.totalAmount').text(totalAmount.toFixed(2));
      $('#cnValue').val(totalAmount.toFixed(2));
    }
    $('#manCreditNoteSubmit').on("click", function(e){
      $(this).attr('disabled', true);
      $('#manCreditNoteCancel').attr('disabled', true);
      $('#manCreditNoteForm').submit();
    });    
  }

  // manual debit note Form
  if( $('#manDebitNoteForm').length>0 ) {
    $('#manDebitNoteForm').on('blur', '.dnReturnQty', function() {
      var itemIndex = jQuery(this).attr('index');
      updateManualDebitNoteFormRow(itemIndex);
    });
    $('#manDebitNoteForm').on('blur', '.purchaseRate', function() {
      var itemIndex = jQuery(this).attr('index');
      updateManualDebitNoteFormRow(itemIndex);
    });
    $('#manDebitNoteForm').on('change', '.dnItemTax', function() {
      var itemIndex = jQuery(this).attr('index');
      updateManualDebitNoteFormRow(itemIndex);
    });
    $('#manDebitNoteForm').on('change', '#taxCalcOption', function(){
      var totalsTaxable = totalsGST = totalsItems = 0;
      var taxCalcOption = $(this).val();
      $('#manDebitNoteForm .dnReturnQty').each(function(index) {
        var rowId = index;
        var itemQty = returnNumber(parseFloat($('#qty_'+rowId).val()));
        var purchaseRate = returnNumber(parseFloat($('#purchaseRate_'+rowId).val()));
        var gstPercent = returnNumber(parseFloat($('#dnItemTax_'+rowId).val()));
        if(taxCalcOption === 'i') {
          var taxableAmount = Math.round(itemQty*purchaseRate, 2);
          var totalAmount = taxableAmount;
          var gstValue = 0;
        } else {
          var taxableAmount = Math.round(itemQty*purchaseRate, 2);
          var gstValue = Math.round(taxableAmount*gstPercent/100, 2);
          var totalAmount = taxableAmount+gstValue;
        }
        if(itemQty > 0 && purchaseRate > 0) {
          $('#taxable_'+rowId).text(taxableAmount.toFixed(2));
          $('#taxAmount_'+rowId).text(gstValue.toFixed(2));
          $('#totalAmount_'+rowId).text(totalAmount.toFixed(2));
        }
      });
      updateManualDebitNoteFormTotals();
    });
    $('#manDebitNoteForm').on('change', '.mDebitNoteType', function() {
      var mDebitNoteType = jQuery(this).val();
      if(mDebitNoteType === 'wo') {
        $('#mDnOptionalFields').hide();
        $('#mDnItems').hide();
        $('#cnValue').val('');
        for(i=0;i<15;i++) {
          var index = i+1;
          $('#iname_'+index).val('');
          $('#qty_'+index).val('');
          $('#mrp_'+index).val('');
          $('#purchaseRate_'+index).val('');
          $('#saItemTax_'+index).val('');
          $('#taxable_'+index).text('');
          $('#taxAmount_'+index).text('');
          $('#totalAmount_'+index).text('');
        }
        $('.totalTaxable, .totalGST, .totalAmount').text('');
      } else {
        $('#mDnOptionalFields').show();
        $('#mDnItems').show();
      }
    });
    function updateManualDebitNoteFormRow(rowId) {
      var itemQty = returnNumber(parseFloat($('#qty_'+rowId).val()));
      var mrp = returnNumber(parseFloat($('#purchaseRate_'+rowId).val()));
      var gstPercent = returnNumber(parseFloat($('#dnItemTax_'+rowId).val()));
      var taxCalcOption = $('#taxCalcOption').val();
      if(taxCalcOption === 'i') {
        var taxableAmount = Math.round(itemQty*mrp, 2);
        var totalAmount = taxableAmount;
        var gstValue = 0;
      } else {
        var taxableAmount = Math.round(itemQty*mrp, 2);
        var gstValue = Math.round(taxableAmount*gstPercent/100, 2);
        var totalAmount = taxableAmount+gstValue;
      }
      if(taxableAmount > 0) {
        $('#taxable_'+rowId).text(taxableAmount.toFixed(2));
      } else {
        $('#taxable_'+rowId).text('');
      }
      if(gstValue > 0) {
        $('#taxAmount_'+rowId).text(gstValue.toFixed(2));
      } else {
        $('#taxAmount_'+rowId).text('');
      }
      if(totalAmount > 0) {
        $('#totalAmount_'+rowId).text(totalAmount.toFixed(2));
      } else {
        $('#totalAmount_'+rowId).text('');
      }
      updateManualDebitNoteFormTotals();
    }
    function updateManualDebitNoteFormTotals() {
      var totalTaxable = totalGST = totalAmount = 0;
      jQuery('.rowTaxable').each(function(i, obj) {
        var taxable = jQuery(this).text();
        var gst = $('#taxAmount_'+i).text();
        var amount = $('#totalAmount_'+i).text();
        totalTaxable += returnNumber(parseFloat(taxable));
        totalGST += returnNumber(parseFloat(gst));
        totalAmount += returnNumber(parseFloat(amount));
      });
      $('.totalTaxable').text(totalTaxable.toFixed(2));
      $('.totalGST').text(totalGST.toFixed(2));
      $('.totalAmount').text(totalAmount.toFixed(2));
      $('#dnValue').val(totalAmount.toFixed(2));
    }
    $('#manDebitNoteSubmit').on("click", function(e){
      $(this).attr('disabled', true);
      $('#manDebitNoteCancel').attr('disabled', true);
      $('#manDebitNoteForm').submit();
    });

    if( $('.manDnBarcode').length > 0) {

      var lotNosResponse = [];
      var selectedLotNoDetails = null;
      var barcode = '';
      var barcodeId = '';
      var rowId = '';
      var barcodeOriginal = '';

      $(document).on('click', '#modalManDebitNoteCancel', function(){
        $('#modalManualDebitNote').modal('hide');
        var selectedLotNo = $('[name="dualLotRadios"]:checked').val();
        selectedLotNoDetails = lotNosResponse[selectedLotNo];
        updateManDebitNoteRowFromBarcode(selectedLotNoDetails);
      });

      $(document).on('click', '#modalManDebitNoteSelect', function(){
        $('#dualLotModal').modal('hide');
        $('#owBarcode').focus().val('');
      });    

      $('.transBarcode').on('blur', function(e){
        var barcode = $(this).val().split('+').join("");
        var barcodeId = $(this).attr('id');
        var rowId = barcodeId.split('_')[1];
        if(barcode === '') {
          $('#iname_'+rowId).removeAttr("readonly");
        }
      });

      function updateManDebitNoteRowFromBarcode(selectedLotNoDetails) {
        var isLotExists = false;
        var itemName = selectedLotNoDetails.itemName;
        var availableQty = parseFloat(selectedLotNoDetails.availableQty).toFixed(2);
        var lotNo = selectedLotNoDetails.lotNo;
        var cno = selectedLotNoDetails.cno;
        var mOq = parseFloat(selectedLotNoDetails.mOq).toFixed(2);
        var taxPercent = parseFloat(selectedLotNoDetails.taxPercent).toFixed(2);
        var transferQty = parseFloat(mOq*1).toFixed(2);
        var itemAmount = parseFloat(mrpDn*transferQty).toFixed(2);
        var lotNoRef = $('#lotNo_'+rowId);
        var isLotExists = false;
        var existingLotId = null;
        var selectedLotId = selectedLotNo = null;
        var mrpDnActual = parseFloat(selectedLotNoDetails.mrpDn).toFixed(2);
        var exMill = parseFloat(selectedLotNoDetails.exMill).toFixed(2);
        if(exMill > 0) {
          var mrpDn = exMill;
        } else {
          var mrpDn = mrpDnActual;
        }
        /* check if lot no is already selected. */
        jQuery('.lotNo').each(function(i, obj) {
          selectedLotNo = $(this).val();
          selectedLotId = $(this).attr('id');
          if(selectedLotNo === lotNo && rowId !== i) {
            isLotExists = true;
            existingLotId = i;
          }
        });

        if(isLotExists) {
          $('#barcode_'+rowId).focus().val('');
          var selectedLotIndex = existingLotId;
          var selectedLotQty = parseFloat($('#qty_'+selectedLotIndex).val());
          var newTransferQty = parseFloat(transferQty)+parseFloat(selectedLotQty); 
          var itemAmount = parseFloat(mrpDn*newTransferQty).toFixed(2);
          var availQty = parseFloat($('#qtyava_'+selectedLotIndex).val());
          if(newTransferQty <= availableQty) {
            $('#qty_'+selectedLotIndex).val(newTransferQty);
            $('#grossAmount_'+selectedLotIndex).text(itemAmount);
            updateManualDebitNoteFormRow(selectedLotIndex);
          } else {
            alert('Available  Qty. is less than Transfer Qty!! Please check.');
            return false;
          }
        } else {
          $('#iname_'+rowId).attr('readonly', 'readonly');
          $('#iname_'+rowId).val(itemName);
          $('#qtyava_'+rowId).val(availableQty);
          // $('#cno_'+rowId).text(cno);
          $('#qty_'+rowId).val(transferQty);
          $('#purchaseRate_'+rowId).val(mrpDn);
          $('#grossAmount_'+rowId).text(itemAmount);
          $('#dnItemTax_'+rowId+' option[value="'+taxPercent+'"]').attr('selected', 'selected');
          
          $('#qtyava_'+rowId).attr('readonly', 'readonly');
          $('#mrp_'+rowId).attr('readonly', 'readonly');
          jQuery(lotNoRef).html(
            jQuery("<option></option>").
            attr("value",lotNo).
            text(lotNo)
          );
          updateManualDebitNoteFormRow(rowId);
          var nextRowId = rowId+1;
          $('#barcode_'+nextRowId).focus();
        }
      }

      $('.manDnBarcode').on('keypress', function (e) {
       if (e.keyCode == 13) {
          barcodeOriginal = $(this).val();
          barcode = $(this).val().split('+').join("");
          barcodeId = $(this).attr('id');
          rowId = parseInt(barcodeId.split('_')[1]);
          var locationCode = $('#locationCode').val();
          jQuery.ajax("/async/getItemDetailsByCode?bc="+barcode+'&locationCode='+locationCode+'&qtyZero=false', {
              success: function(itemDetails) {
                if(itemDetails.status === 'success') {
                  var objLength = Object.keys(itemDetails.response.bcDetails).length;
                  if(objLength > 0) {
                    var itemName = '';
                    jQuery.each(itemDetails.response.bcDetails, function (index, lotNoDetails) {
                      lotNosResponse[lotNoDetails.lotNo] = lotNoDetails;
                      itemName = lotNoDetails.itemName;
                    });
                    if(objLength === 1) {
                      selectedLotNoDetails = itemDetails.response.bcDetails[0];
                      updateManDebitNoteRowFromBarcode(selectedLotNoDetails);
                    } else {
                      $('#dualLotNosTitle').text(itemName);
                      var dualBarcodesHtml = '<form>';
                      dualBarcodesHtml += '<div class="table-responsive">';
                      dualBarcodesHtml += '<table class="table table-striped table-hover font12" id="dualLotNosTable" style="margin-bottom:0px;">';
                      dualBarcodesHtml += '<thead><tr>';
                      dualBarcodesHtml += '<th>&nbsp;</th>';
                      dualBarcodesHtml += '<th style="text-align: center;">Lot No.</th>';
                      dualBarcodesHtml += '<th style="text-align: center;">Batch No.</th>';
                      dualBarcodesHtml += '<th style="text-align: center;">Cno/Box No.</th>';
                      dualBarcodesHtml += '<th style="text-align: center;">Available</th>';
                      dualBarcodesHtml += '</tr></thead>';
                      dualBarcodesHtml += '<tbody>';
                      jQuery.each(itemDetails.response.bcDetails, function (index, lotNoDetails) {
                        var lotNo = lotNoDetails.lotNo;
                        var batchNo = lotNoDetails.bno;
                        var availableQty = lotNoDetails.closingQty;
                        var cno = lotNoDetails.cno;
                        dualBarcodesHtml += '<tr>';
                        dualBarcodesHtml += '<td style="vertical-align: middle; padding-left: 10px;" align="center">';
                        dualBarcodesHtml += '<input type="radio" name="dualLotRadios" value="'+lotNo+'" style="visibility: visible; margin-top:0px;" />';
                        dualBarcodesHtml += '</td>';
                        dualBarcodesHtml += '<td style="vertical-align: middle">'+lotNo+'</td>';
                        dualBarcodesHtml += '<td style="vertical-align: middle">'+batchNo+'</td>';
                        dualBarcodesHtml += '<td style="vertical-align: middle">'+cno+'</td>';
                        dualBarcodesHtml += '<td style="vertical-align: middle">'+availableQty+'</td>';
                        dualBarcodesHtml += '</tr>';
                      });
                      dualBarcodesHtml += '</tbody>';
                      dualBarcodesHtml += '</table>';
                      dualBarcodesHtml += '</div>';
                      $('#modalManualDebitNoteLotNos').html(dualBarcodesHtml);
                      $('#modalManualDebitNote').modal('show');
                    }
                  } else {
                    alert('Barcode not found');                
                  }
                } else {
                  alert('Invalid Barcode / Barcode does not exists');
                }
              },
              error: function(e) {
                alert('Barcode not found in the Selected store.');
              }
          });
          e.preventDefault();
       }
      });
    }
    $('#manDebitNoteForm').on('blur', '.dnItem', function() {
      var itemName = jQuery(this).val();
      var itemIndex = jQuery(this).attr('index');
      var lotNoRef = $('#lotNo_'+itemIndex);
      var barcode = $('#barcode_'+itemIndex).val().split('+').join("");
      var bnoFirstOption = jQuery("<option></option>").attr("value","").text("Choose");
      if(itemName.length > 0 && barcode.length === 0) {
       var locationCode = $('#locationCode').val();
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
                  var cno = lotNoDetails.cno;
                  var lotNoText = lotNoDetails.lotNo;
                  if(cno !== '') {
                    lotNoText += ' [ CASE: ' + cno + ' ]';
                  }
                  lotNosResponse[lotNoDetails.lotNo] = lotNoDetails;
                  jQuery(lotNoRef).append(
                    jQuery("<option></option>").
                    attr("value",lotNoDetails.lotNo).
                    text(lotNoText)
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
        jQuery('#purchaseRate_'+itemIndex).val('');
        jQuery('#dnItemTax_'+itemIndex+' option[value="'+''+'"]').attr('selected', 'selected');
        jQuery('#qty_'+itemIndex).val('');
        updateManualDebitNoteFormRow(itemIndex);
      }
    });

    $('#manDebitNoteForm').on('change', '.lotNo', function() {
      var qtyAvailable = itemRate = itemType = '';
      var itemIndex = jQuery(this).attr('index');
      var lotNo = jQuery(this).val();
      var avaQtyContainer = jQuery('#qtyava_'+itemIndex);
      var mrpContainer = jQuery('#purchaseRate_'+itemIndex);
      var itemName = jQuery('#iname_'+itemIndex).val();
      // var itemTypeContainer = jQuery('#itemType_'+itemIndex);
      if(lotNo !== '') {
        var selectedLotNo = '';
        var lotFound = false;
        jQuery('.lotNo').each(function(i, obj) {
          selectedLotNo = $(this).val();
          if(selectedLotNo === lotNo && parseInt(itemIndex) !== parseInt(i)) {
            lotFound = true;
          }
        });

        if(lotFound) {
          $('#lotNo_'+itemIndex).val('');
          jQuery('#qtyava_'+itemIndex).val('');
          jQuery('#qty_'+itemIndex).val('');
          jQuery('#purchaseRate_'+itemIndex).val('');
          jQuery('#iname_'+itemIndex).val('');
          jQuery('#barcode_'+itemIndex).val('');
          bootbox.alert({
            message: "This Lot No. is already selected for `" +itemName+"`. A Lot No. must be unique and selected only once in the bill against the same item."
          });
          return false;              
        } else if(Object.keys(lotNosResponse[lotNo]).length>0) {
          jQuery('#qtyava_'+itemIndex).val(lotNosResponse[lotNo].closingQty);
          jQuery('#qty_'+itemIndex).val(lotNosResponse[lotNo].mOq);
          jQuery('#purchaseRate_'+itemIndex).val(lotNosResponse[lotNo].mrp);
          // jQuery('#itemType_'+itemIndex).val(lotNosResponse[lotNo].itemType);
          jQuery('#dnItemTax_'+itemIndex+' option[value="'+lotNosResponse[lotNo].taxPercent+'"]').attr('selected', 'selected');
          updateManualDebitNoteFormRow(itemIndex);
        }
      }
    });

    $(document).on('click', '#modalManDebitNoteSelect', function(){
      $('#dualLotModal').modal('hide');
      var billingRate = $('#billingRate').val();
      var selectedLotNo = $('[name="dualLotRadios"]:checked').val();
      var barcode = $('.manDnBarcode').val().split('+').join("");
      var selectedLotNoDetails = lotNosResponse[selectedLotNo];
      var barCodeItem = '[ '+barcode+' ] '+selectedLotNoDetails.itemName;
      if(selectedLotNoDetails.cno !== '') {
        barCodeItem += '<span style="font-size: 12px;padding-left:5px;">CASE No: '+selectedLotNoDetails.cno+'</span>';
      }
      $('#lastScannedSaleItem').html(barCodeItem);
      updateManDebitNoteRowFromBarcode(selectedLotNoDetails);
      $('.manDnBarcode').focus().val('');
      $('#modalManualDebitNote').modal('hide');
    });

    $(document).on('click', '#modalManDebitNoteCancel', function(){
      $('#modalManualDebitNote').modal('hide');
      $('.manDnBarcode').focus().val('');
    });    
  }  

  // sales return window.
  if( $('#salesReturnWindow').length > 0 ) {
    if($('#srBarcode').length > 0) {
      $('#srBarcode').focus();
      $('#srBarcode').on('keypress', function(e){
        if (e.keyCode == 13) {
          var barcode = $(this).val().split('+').join("");
          var locationCode = $('#locationCode').val();
          if(barcode.length !== 13) {
            alert('Invalid barcode');
            return false;
          } else if(locationCode === '') {
            alert('Choose a Store first.');
            return false;
          }
          var locationCode = $('#locationCode').val();
          jQuery.ajax("/async/getItemDetailsByCode?bc="+barcode+'&locationCode='+locationCode+'&qtyZero=false', {
            success: function(itemDetails) {
              if(itemDetails.status === 'success') {
                var objLength = Object.keys(itemDetails.response.bcDetails).length;
                var lotNoId = moQ = '';
                if(objLength > 0) {
                  var itemName = itemDetails.response.bcDetails.itemName;
                  var lotNo = itemDetails.response.bcDetails[0].lotNo;
                  $('#salesReturnTable tr').each(function() {
                    var lotNoTd = $(this).find(".returnLotNo");
                    if(lotNoTd.text() === lotNo) {
                      lotNoId = lotNoTd.attr('id').split('_')[1];
                    }
                  });
                  if(parseInt(lotNoId) >= 0) {
                    var prevReturns = parseFloat(returnNumber($('#returnason_'+lotNoId).text()));
                    var soldQty = parseFloat(returnNumber($('#sold_'+lotNoId).text()));
                    var balReturns = parseFloat(returnNumber(soldQty-prevReturns)).toFixed(2);
                    $('#returnQty_'+lotNoId).val(balReturns);
                    $('#srBarcode').val('');
                    $('.returnQty').trigger('blur')
                  } else {
                    alert('Barcode not found in this Invoice.');
                  }

                } else {
                  alert('Barcode not found');                
                }
              }
            },
            error: function(e) {
              alert('Barcode not found in the Selected store.');
            }
          });
        }
        e.preventDefault();
      });
    }
    $('.returnQty').on("blur", function(e){
      var returnItemId = $(this).attr('id').split('_')[1];
      var returnRate = parseFloat(returnNumber($('#returnRate_'+returnItemId).text()));
      var soldQty = parseFloat(returnNumber($('#sold_'+returnItemId).text()));
      var prevReturns = parseFloat(returnNumber($('#returnason_'+returnItemId).text()));
      var returnQty = parseFloat(returnNumber($(this).val()));
      if(returnQty > 0 && (returnQty > (soldQty - prevReturns))) {
        alert('Return Qty. is greater than available Qty. for return.');
        $(this).val('');
        $(this).focus();
        return false;
      }
      var returnValue = parseFloat(returnNumber(returnRate*returnQty));
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
    $('#srActionButton').on('click', function(e){
      e.preventDefault();
      $('#srActionButton').attr('disabled', true);
      $('#salesReturnWindow').submit();
    });
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
    var locationCode = $('#selLc').val();
    if (typeof locationCode === 'undefined') {
      locationCode = '';
    }
    jQuery.ajax("/async/day-sales?lc="+locationCode,{
        method:"GET",
        success: function(apiResponse) {
          if(apiResponse.status === 'success') {
            var salesReturns = 0;
            var daySales = apiResponse.response.daySales[0];
            var cashSales = parseFloat(daySales.cashSales);
            var cardSales = parseFloat(daySales.cardSales);
            var splitSales = parseFloat(daySales.splitSales);
            var walletSales = parseFloat(daySales.walletSales);
            var creditSales = parseFloat(daySales.creditSales);
            var salesReturn = parseFloat(daySales.returnAmount);
            var totalSales = parseFloat(cashSales+cardSales+splitSales+creditSales+walletSales) - salesReturn;
            var cashReceipts = parseFloat(daySales.cashReceipts);
            var cashPayments = parseFloat(daySales.cashPayments);
            var cashInHand = parseFloat( (parseFloat(daySales.cashInHand)+parseFloat(daySales.cashReceipts))-parseFloat(daySales.cashPayments));
            $('#ds-cashsale').text(cashSales.toFixed(2));
            $('#ds-cardsale').text(cardSales.toFixed(2));
            $('#ds-walletsale').text(walletSales.toFixed(2));
            $('#ds-splitsale').text(splitSales.toFixed(2));
            $('#ds-creditsale').text(creditSales.toFixed(2));
            $('#ds-returns').text(salesReturn.toFixed(2));            
            $('#ds-netsale').text(totalSales.toFixed(2));
            $('#ds-cashinhand').text(cashInHand.toFixed(2));                                          
            $('#ds-cashpayments').text(cashPayments.toFixed(2));                                          
            $('#ds-cashreceipts').text(cashReceipts.toFixed(2));                                          
          }
        },
        error: function(e) {
          alert('An error occurred while fetching Day Sales');
        }
    });
  }

  if($('#dbLocationCode').length > 0) {
    $('#dbLocationCode').on('change', function() {
      var locationCode = $(this).val();
      if(locationCode.length > 0)
        window.location.href = '/dashboard?lc='+locationCode;
      else
        window.location.href = '/dashboard';
    });
  }

  if($('#salesDayGraph').length > 0) {
    $('#sfGraphReload').on("click", function(e){
      var curMonth = $('#sgf-month').val();
      var curYear =  $('#sgf-year').val();
      $('#saleMonth').val(curMonth);
      $('#saleYear').val(curYear);
      monthWiseSales();
    });
  }

  // promotional offers
  if( $('#offerEntryForm').length>0 ) {
    $('#offerCancel').on('click', function(e){
      if(confirm("Are you sure. You want to close this page?") == true) {
        window.location.href = '/promo-offers/list';
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
  if( $('#paymentVocForm').length > 0  || $('#receiptVocForm').length > 0) {
    $('#paymentMode').on("change", function(e){
      var paymentMode = $(this).val();
      if(paymentMode==='b' || paymentMode==='p') {
        $('#refInfo').show();
      } else if(paymentMode==='c') {
        $('#refInfo').hide();
      }
    });   
  }

  if($('#receiptVocForm').length > 0) {
    var billNosResponse = [];
    $('#custBillNos').on('click', function(e){
      e.preventDefault();
      var customerName = jQuery('#partyName').val();
      var billNoRef = $('#custBillNo');
      var billNoFirstOption = jQuery("<option></option>").attr("value","").text("Choose");        
      var optionCount = $(billNoRef).children('option').length;
      if(customerName !== '') {
       var data = {custName:customerName};
       jQuery.ajax("/async/getBillNos", {
          data: data,
          method:"POST",
          success: function(billNos) {
            if(billNos.status === 'success') {
              var objLength = Object.keys(billNos.response).length;
              if(parseInt(objLength) > 0) {
                jQuery(billNoRef).empty().append(billNoFirstOption);
                jQuery.each(billNos.response, function (index, billDetails) {
                  billNosResponse[billDetails.billNo] = billDetails;
                  jQuery(billNoRef).append(
                    jQuery("<option></option>").
                    attr("value",encodeHTML(billDetails.billNo)).
                    text(billDetails.billNo + ' { ' + billDetails.balAmount + ' }')
                  );
                });
                $('#custBillNo').attr('disabled', false);
              }
            } else if(billNos.status === 'failed') {
              alert(billsNos.reason);
            }
          },
          error: function(e) {
            alert('An error occurred while fetching Batch Nos.');
          }
       });          
      }
    });
    $('#custBillNo').on('change', function(e){
      var billNo = $(this).val();
      if(billNo !== '' && Object.keys(billNosResponse[billNo]).length>0) {
        jQuery('#amount').val(billNosResponse[billNo].balAmount);
      }
    });
    $('#processStatus').on('change', function(e) {
      var processStatus = $(this).val();
      if(parseInt(processStatus) === 4) {
        $('#rejectReasonDiv').show();
      } else {
        $('#rejectReasonDiv').hide();
      }
    });
  }

  if( $('#debitVocEntryForm').length > 0 ) {
    var billNosResponse = [];    
    $('#supplierCode').on('change', function(e){
      e.preventDefault();
      var supplierCode = $(this).val();
      var billNoRef = $('#suppBillNo');
      var billNoFirstOption = jQuery("<option></option>").attr("value","").text("Choose");        
      var optionCount = $(billNoRef).children('option').length;
      if(supplierCode !== '') {
       var data = {suppCode:supplierCode};
       jQuery.ajax("/async/getSuppBillNos", {
          data: data,
          method:"POST",
          success: function(billNos) {
            if(billNos.status === 'success') {
              var objLength = Object.keys(billNos.response).length;
              if(parseInt(objLength) > 0) {
                jQuery(billNoRef).empty().append(billNoFirstOption);
                jQuery.each(billNos.response, function (index, billDetails) {
                  billNosResponse[billDetails.billNo] = billDetails;
                  jQuery(billNoRef).append(
                    jQuery("<option></option>").
                    attr("value",encodeHTML(billDetails.billNo)).
                    text(billDetails.billNo + ' { ' + billDetails.balAmount + ' }')
                  );
                });
              }
            } else if(billNos.status === 'failed') {
              alert(billsNos.reason);
            }
          },
          error: function(e) {
            alert('An error occurred while fetching Batch Nos.');
          }
       });          
      }
    });
    $('#suppBillNo').on('change', function(e){
      var billNo = $(this).val();
      if(billNo !== '' && Object.keys(billNosResponse[billNo]).length>0) {
        jQuery('#amount').val(billNosResponse[billNo].balAmount);
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
        bootbox.alert({
          message: response.message
        });        
        return false;
      }
      if(response.status === true) {
        $('#success-msg-fp').show();
        $('#success-msg-fp').html(response.message);
        $('#pass-fp').attr('disabled', false);
        $('#submit-fp').attr('disabled', false);
        $('#newpass-fp').attr('disabled', false);
        $('#sendOtpBtn').attr('disabled', true);
        $('#resendOTPLink').show();
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
  /*
  $(document).bind("contextmenu",function(e){
    alert('We are sorry. Right click is disabled!');
    return false;
  });
  */
});

function printSalesBill(bill_no) {
  var printUrl = '/print-sales-bill?billNo='+bill_no;
  var openWindow = window.open(printUrl, "_blank", "scrollbars=yes,titlebar=yes,resizable=yes,width=400,height=400");
  openWindow.print();  
}

function printSalesBillCombo(bill_no) {
  var printUrl = '/print-sales-bill-combo?billNo='+bill_no;
  var openWindow = window.open(printUrl, "_blank", "scrollbars=yes,titlebar=yes,resizable=yes,width=400,height=400");
  openWindow.print();
}

function printSalesBillSmall(bill_no) {
  var printUrl = '/print-sales-bill-small?billNo='+bill_no;
  var openWindow = window.open(printUrl, "_blank", "scrollbars=yes,titlebar=yes,resizable=yes,width=400,height=400");
  openWindow.print();  
}

function printSalesBillGST(bill_no) {
  var printUrl = '/print-sales-bill-gst?billNo='+bill_no;
  var openWindow = window.open(printUrl, "_blank", "scrollbars=yes,titlebar=yes,resizable=yes,width=400,height=400");
  openWindow.print();
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
  var locationCode = $('#selLc').val();
  var saleDate = [];
  var saleAmounts = [];
  var totCashSales = totSplitSales = totCardSales = totSales = totSalesReturns = totNetSales = 0;
  var totWalletSales = totCreditSales = 0;
  jQuery.ajax("/async/monthly-sales?saleMonth="+sgfMonth+'&saleYear='+sgfYear+'&lc='+locationCode, {
    method:"GET",
    success: function(apiResponse) {
      if(apiResponse.status==='success') {
        jQuery.each(apiResponse.response.daywiseSales, function (index, saleDetails) {
          var dateFormat = new Date(saleDetails.tranDate+'T12:00:30z');
          var amount = (
                          parseInt(returnNumber(saleDetails.cardSales))+
                          parseInt(returnNumber(saleDetails.cashSales))+
                          parseInt(returnNumber(saleDetails.splitSales)) + 
                          parseInt(returnNumber(saleDetails.creditSales)) + 
                          parseInt(returnNumber(saleDetails.walletSales))
                        );

          saleDate.push(dateFormat.getDate());
          saleAmounts.push(amount);

          totCashSales += parseFloat(returnNumber(saleDetails.cashSales));
          totCardSales += parseFloat(returnNumber(saleDetails.cardSales));
          totSplitSales += parseFloat(returnNumber(saleDetails.splitSales));
          totCreditSales += parseFloat(returnNumber(saleDetails.creditSales));
          totWalletSales += parseFloat(returnNumber(saleDetails.walletSales));
          totSalesReturns += parseFloat(returnNumber(saleDetails.returnAmount));

          totSales += ( parseFloat(returnNumber(saleDetails.cashSales)) + returnNumber(parseFloat(saleDetails.cardSales)) + returnNumber(parseFloat(saleDetails.splitSales)) + returnNumber(parseFloat(saleDetails.creditSales)) + returnNumber(parseFloat(saleDetails.walletSales)) );
        });

        totNetSales = parseFloat(returnNumber(totSales)) - parseFloat(returnNumber(totSalesReturns));

        $('#cs-cashsale').text(totCashSales.toFixed(2));
        $('#cs-cardsale').text(totCardSales.toFixed(2));
        $('#cs-walletsale').text(totWalletSales.toFixed(2));
        $('#cs-splitsale').text(totSplitSales.toFixed(2));
        $('#cs-creditsale').text(totCreditSales.toFixed(2));

        $('#cs-totals').text(totSales.toFixed(2));
        $('#cs-sreturn').text(totSalesReturns.toFixed(2));
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

function getGeoLocation() {
  if(navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(successGeoLocation, failedGeoLocation);
  } else {
    alert("Geolocation is not supported by this browser. Please update your Browser.");
  }
}

function successGeoLocation(position) {
  $('#gLat').val(position.coords.latitude);
  $('#gLng').val(position.coords.longitude);
  $('#gAcc').val(position.coords.accuracy);
}

function failedGeoLocation(err) {
  if(err.code == 1) {
    alert("You must Allow Location Access to proceed further.");
  }
}

function encodeHTML(s) {
  return s.split('&').join('&amp;').split('<').join('&lt;').split('"').join('&quot;').split("'").join('&#39;');
}
