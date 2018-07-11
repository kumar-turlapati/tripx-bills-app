<?php
  use Atawa\Utilities;

  if(isset($form_data['promoCode'])) {
    $offer_code = $form_data['promoCode'];
  } elseif(isset($form_data['offerCode'])) {
    $offer_code = $form_data['offerCode'];    
  } else {
    $offer_code = '';
  }
  if(isset($form_data['offerType'])) {
    $offer_type = $form_data['offerType'];
  } elseif(isset($form_data['promoType'])) {
    $offer_type = $form_data['promoType'];
  } else {
    $offer_type = '';
  }
  if(isset($form_data['promoDesc'])) {
    $offer_desc = $form_data['promoDesc'];
  } elseif(isset($form_data['offerDesc'])) {
    $offer_desc = $form_data['offerDesc'];    
  } else {
    $offer_desc = '';
  }
  if(isset($form_data['startDate']) && $form_data['startDate']!=='') {
    $start_date = date("d-m-Y", strtotime($form_data['startDate']));
  } else {
    $start_date = date("d-m-Y");
  }
  if(isset($form_data['endDate']) && $form_data['endDate']!=='') {
    $end_date = date("d-m-Y", strtotime($form_data['endDate']));
  } else {
    $end_date = date("d-m-Y");
  }
  if(isset($form_data['status'])) {
    $status = $form_data['status'];
  } else {
    $status = '';
  }
  if(isset($form_data['productName'])) {
    $product_name = $form_data['productName'];
  } else {
    $product_name = '';
  }
  if(isset($form_data['discountOnProduct'])) {
    $discount_on_product = $form_data['discountOnProduct'];
  } else {
    $discount_on_product = '';
  }
  if(isset($form_data['totalProducts'])) {
    $total_products = $form_data['totalProducts'];
  } else {
    $total_products = '';
  }
  if(isset($form_data['freeProducts'])) {
    $free_products = $form_data['freeProducts'];
  } else {
    $free_products = '';
  }
  if(isset($form_data['billValue'])) {
    $bill_value = $form_data['billValue'];
  } else {
    $bill_value = '';
  }  
  if(isset($form_data['discountOnBillValue'])) {
    $discount_on_bill_value = $form_data['discountOnBillValue'];
  } else {
    $discount_on_bill_value = '';
  }

  if($offer_type === 'a') {
    $acontainer_class = '';
    $bcontainer_class = $ccontainer_class = 'style="display:none;"';
  } elseif($offer_type === 'b') {
    $bcontainer_class = '';
    $acontainer_class = $ccontainer_class = 'style="display:none;"';
  } elseif($offer_type === 'c') {
    $ccontainer_class = '';
    $acontainer_class = $bcontainer_class = 'style="display:none;"';
  } else {
    $acontainer_class = $bcontainer_class = $ccontainer_class = 'style="display:none;"';
  }
?>
<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12"> 
    
    <!-- Panel starts -->
    <section class="panel">
      <h2 class="hdg-reports text-center">Create Offer</h2>
      <div class="panel-body">

        <?php echo Utilities::print_flash_message() ?>

        <!-- Right links starts -->
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/promo-offers/list" class="btn btn-default">
              <i class="fa fa-book"></i> Offers List
            </a>
          </div>
        </div>
        <!-- Right links ends --> 
        
        <!-- Form starts -->
        <form 
          class="form-validate form-horizontal"
          method="POST"
          id="offerEntryForm"
          autocomplete="off"
        >
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
             <label class="control-label">Promo code (max. 10 characters)</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="offerCode"
                id="offerCode"
                value="<?php echo $offer_code ?>"
                style="border:1px dashed; color: #FFA902;text-transform:uppercase"
                maxlength="10"            
              >
              <?php if(isset($form_errors['offerCode'])): ?>
                <span class="error"><?php echo $form_errors['offerCode'] ?></span>
              <?php endif; ?>
              <input type="hidden" name="pO" id="pO" value="<?php echo $offerCode ?>" />
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Promo type</label>
              <div class="select-wrap">
                <select class="form-control" name="offerType" id="offerType">
                  <?php 
                    foreach($offer_types as $key=>$value): 
                      if($offer_type === $key) {
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
              <?php if(isset($form_errors['offerType'])): ?>
                <span class="error"><?php echo $form_errors['offerType'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Promo description (100 characters maximum)</label>
              <input
                type="text"
                class="form-control noEnterKey"
                name="offerDesc"
                id="offerDesc"
                value="<?php echo $offer_desc ?>"
                maxlength="100"
              >
              <?php if(isset($form_errors['offerDesc'])): ?>
                <span class="error"><?php echo $form_errors['offerDesc'] ?></span>
              <?php endif; ?>
            </div>           
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Start date (dd-mm-yyyy)</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $start_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $start_date ?>" size="16" type="text" readonly name="startDate" id="startDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($errors['startDate'])): ?>
                    <span class="error"><?php echo $errors['startDate'] ?></span>
                  <?php endif; ?>  
                </div>
              </div>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">End date (dd-mm-yyyy)</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $end_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $end_date ?>" size="16" type="text" readonly name="endDate" id="endDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($errors['endDate'])): ?>
                    <span class="error"><?php echo $errors['endDate'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Status</label>
              <div class="select-wrap">
                <select class="form-control" name="status" id="status">
                  <?php 
                    foreach($status_a as $key=>$value): 
                      if($status === $key) {
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
              <?php if(isset($form_errors['status'])): ?>
                <span class="error"><?php echo $form_errors['status'] ?></span>
              <?php endif; ?>              
            </div>            
          </div>
          <div class="form-group" id="aContainer" <?php echo $acontainer_class ?>>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
             <label class="control-label">Product name</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="productName"
                id="productName"
                value="<?php echo $product_name ?>"
              >
              <?php if(isset($form_errors['productName'])): ?>
                <span class="error"><?php echo $form_errors['productName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Discount(%)</label>
              <input
                type="text"
                class="form-control noEnterKey"
                name="discountOnProduct"
                id="discountOnProduct"
                value="<?php echo $discount_on_product ?>"
                maxlength="250"
              >
              <?php if(isset($form_errors['discountOnProduct'])): ?>
                <span class="error"><?php echo $form_errors['discountOnProduct'] ?></span>
              <?php endif; ?>
            </div>           
          </div>
          <div class="form-group" id="bContainer" <?php echo $bcontainer_class ?>>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
             <label class="control-label">Total products</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="totalProducts"
                id="totalProducts"
                value="<?php echo $total_products ?>"
              >
              <?php if(isset($form_errors['totalProducts'])): ?>
                <span class="error"><?php echo $form_errors['totalProducts'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Free products</label>
              <input
                type="text"
                class="form-control noEnterKey"
                name="freeProducts"
                id="freeProducts"
                value="<?php echo $free_products ?>"
                maxlength="250"
              >
              <?php if(isset($form_errors['freeProducts'])): ?>
                <span class="error"><?php echo $form_errors['freeProducts'] ?></span>
              <?php endif; ?>
            </div>           
          </div>
          <div class="form-group" id="cContainer" <?php echo $ccontainer_class ?>>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
             <label class="control-label">Bill value</label>
              <input 
                type="text"
                class="form-control noEnterKey"
                name="billValue"
                id="billValue"
                value="<?php echo $bill_value ?>"
              >
              <?php if(isset($form_errors['billValue'])): ?>
                <span class="error"><?php echo $form_errors['billValue'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Discount (%)</label>
              <input
                type="text"
                class="form-control noEnterKey"
                name="discountOnBillValue"
                id="discountOnBillValue"
                value="<?php echo $discount_on_bill_value ?>"
              >
              <?php if(isset($form_errors['discountOnBillValue'])): ?>
                <span class="error"><?php echo $form_errors['discountOnBillValue'] ?></span>
              <?php endif; ?>
            </div>           
          </div>
          <div class="text-center">
            <button class="btn btn-danger" id="offerCancel">
              <i class="fa fa-times"></i> Cancel
            </button>
            <button class="btn btn-primary" id="offerSave">
              <i class="fa fa-save"></i> Save
            </button>
          </div>
        </form>
        <!-- Form ends -->
      </div>
    </section>
    <!-- Panel ends --> 
  </div>
</div>
<!-- Basic Forms ends -->