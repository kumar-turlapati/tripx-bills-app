<?php 

namespace ClothingRm\Inward\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Suppliers\Model\Supplier;
use ClothingRm\Taxes\Model\Taxes;
use ClothingRm\Inward\Model\Inward;
use User\Model\User;

class InwardController
{
  private $template, $supplier_model;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->supplier_model = new Supplier;
    $this->taxes_model = new Taxes;
    $this->inward_model = new Inward;
    $this->flash = new Flash;
    $this->user_model = new User;
  }

  // inward entry action
  public function inwardEntryAction(Request $request) {

    $credit_days_a = $suppliers_a = $payment_methods = $client_locations = [];
    $taxes_a = $taxes = $taxes_raw = [];
    $form_errors = $form_data = [];
    $api_error = '';
    
    $total_item_rows = !is_null($request->get('tr')) && is_numeric($request->get('tr')) ? (int)$request->get('tr') : 50;

    for($i=1;$i<=365;$i++) {
      $credit_days_a[$i] = $i;
    }

    $suppliers = $this->supplier_model->get_suppliers(0,0,[]);
    if($suppliers['status']) {
      $suppliers_a += $suppliers['suppliers'];
    }

    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }

    # get client details
    $client_details = Utilities::get_client_details();
    $client_business_state = $client_details['locState'];

    # get client locations
    $client_locations_resp = $this->user_model->get_client_locations();
    if($client_locations_resp['status']) {
      foreach($client_locations_resp['clientLocations'] as $loc_details) {
        $client_locations[$loc_details['locationCode']] = $loc_details['locationName'];
      }
    }

    # check if form is submitted.
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $validation_status = $this->_validate_form_data($submitted_data,false);
      if($validation_status['status']===true) {
        $cleaned_params = $validation_status['cleaned_params'];
        // hit api
        $api_response = $this->inward_model->createInward($cleaned_params);
        if($api_response['status']) {
          $message = 'Inward entry created successfully with entry code ` '.$api_response['inwardCode'].' `';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/inward-entry');
        } else {
          $api_error = $api_response['apierror'];
          $form_data = $submitted_data;
        }
      } else {
        $form_errors = $validation_status['form_errors'];
        $form_data = $submitted_data;
      }

    # check whether the redirection is from bulk upload form.
    } else {
      if(!is_null($request->get('bupToken')) && 
              $request->get('bupToken') !== '' &&
              isset($_SESSION['inwardBulkUpload']['token']) && 
              $_SESSION['inwardBulkUpload']['token'] === $request->get('bupToken')
        ) {
        if(!is_null($request->get('tr')) && (int)$request->get('tr') <= 150) {
          $total_item_rows = (int)$request->get('tr');
        } else {
          $total_item_rows = 50;
        }
        $uploaded_data = $_SESSION['inwardBulkUpload']['uploadedData'];
        $form_data = $this->_map_uploaded_data_with_form_data($uploaded_data);
        unset($_SESSION['inwardBulkUpload']);
      } else {
        $total_item_rows = 50;
      }
    }

    # theme variables.
    $controller_vars = array(
      'page_title' => 'Inward Material Entry - Create',
      'icon_name' => 'fa fa-laptop',
    );
    $template_vars = array(
      'utilities' => new Utilities,
      'credit_days_a' => array(0=>'Choose')+$credit_days_a,
      'suppliers' => array(''=>'Choose')+$suppliers_a,
      'payment_methods' => Constants::$PAYMENT_METHODS_PURCHASE,
      'taxes' => $taxes,
      'taxes_raw' => $taxes_raw,
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'total_item_rows' => $total_item_rows,
      'api_error' => $api_error,
      'states_a' => array(0=>'Choose') + Constants::$LOCATION_STATES,
      'supply_type_a' => array('' => 'Choose', 'inter' => 'Interstate', 'intra' => 'Intrastate'),
      'client_business_state' => $client_business_state,
      'client_locations' => array(''=>'Choose') + $client_locations,
    );

    return array($this->template->render_view('inward-entry',$template_vars),$controller_vars);
  }
  
  // inward entry update action
  public function inwardEntryUpdateAction(Request $request) {

    # initiate variables.
    $credit_days_a = $suppliers_a = $payment_methods = $client_locations = [];
    $taxes_a = $taxes = $taxes_raw = [];
    $form_errors = $form_data = [];
    $api_error = '';
    
    $total_item_rows = 50;

    for($i=1;$i<=365;$i++) {
      $credit_days_a[$i] = $i;
    }

    $suppliers = $this->supplier_model->get_suppliers(0,0,[]);
    if($suppliers['status']) {
      $suppliers_a += $suppliers['suppliers'];
    }

    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }

    # get client details
    $client_details = Utilities::get_client_details();
    $client_business_state = $client_details['locState'];

    # get client locations
    $client_locations_resp = $this->user_model->get_client_locations();
    if($client_locations_resp['status']) {
      foreach($client_locations_resp['clientLocations'] as $loc_details) {
        $client_locations[$loc_details['locationCode']] = $loc_details['locationName'];
      }
    }    

    # validate purchase code.
    if( is_null($request->get('purchaseCode')) ) {
      $this->flash->set_flash_message('Invalid purchase code.', 1);
      Utilities::redirect('/inward-entry');
    } else {
      $purchase_code = Utilities::clean_string($request->get('purchaseCode'));
      $purchase_response = $this->inward_model->get_purchase_details($purchase_code);
      // dump($purchase_response);
      // exit;

      if($purchase_response['status']) {
        $purchase_details = $purchase_response['purchaseDetails'];

        # convert received item details to template item details.
        $item_names = array_column($purchase_details['itemDetails'],'itemName');
        $inward_qtys = array_column($purchase_details['itemDetails'],'itemQty');
        $free_qtys = array_column($purchase_details['itemDetails'],'freeQty');
        $billed_qtys = array_column($purchase_details['itemDetails'],'billedQty');
        $mrps = array_column($purchase_details['itemDetails'],'mrp');
        $item_rates = array_column($purchase_details['itemDetails'],'itemRate');
        $tax_percents = array_column($purchase_details['itemDetails'],'taxPercent');
        $discounts = array_column($purchase_details['itemDetails'],'discountAmount');
        $igst_amounts = array_column($purchase_details['itemDetails'],'igstAmount');
        $cgst_amounts = array_column($purchase_details['itemDetails'],'cgstAmount');
        $sgst_amounts = array_column($purchase_details['itemDetails'],'sgstAmount');
        $hsn_codes = array_column($purchase_details['itemDetails'],'hsnSacCode');
        $packed_qtys = array_column($purchase_details['itemDetails'], 'packedQty');
        if(count($item_names)>50) {
          $total_item_rows = count($item_names);
        }

        # unset item details from api data.
        unset($purchase_details['itemDetails']);

        # create form data variable.
        $form_data = $purchase_details;

        $form_data['itemName'] = $item_names;
        $form_data['inwardQty'] = $inward_qtys;
        $form_data['freeQty'] = $free_qtys;
        $form_data['billedQty'] = $billed_qtys;
        $form_data['igstAmount'] = $igst_amounts;
        $form_data['cgstAmount'] = $cgst_amounts;
        $form_data['sgstAmount'] = $sgst_amounts;        
        $form_data['itemRate'] = $item_rates;
        $form_data['taxPercent'] = $tax_percents;
        $form_data['mrp'] = $mrps;
        $form_data['itemDiscount'] = $discounts;
        $form_data['hsnCodes'] = $hsn_codes;
        $form_data['packedQty'] = $packed_qtys;
        if($form_data['grnFlag'] === 'yes') {
          $page_error = 'GRN is already generated for PO No. `'.$purchase_details['poNo']."`. You can't edit now.";
          $this->flash->set_flash_message($page_error, 1);
          Utilities::redirect('/inward-entry/list');
        } else {
          $is_grn_generated = false;
        }
      } else {
        $this->flash->set_flash_message($purchase_response['apierror'], 1);
        Utilities::redirect('/inward-entry');
      }
    }

    $suppliers = $this->supplier_model->get_suppliers(0,0,[]);
    if($suppliers['status']) {
      $suppliers_a += $suppliers['suppliers'];
    }

    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }

    $client_details = Utilities::get_client_details();
    $client_business_state = $client_details['locState'];    

    # check if form is submitted.
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $validation_status = $this->_validate_form_data($submitted_data, $is_grn_generated);
      if($validation_status['status']===true) {
        $cleaned_params = $validation_status['cleaned_params'];
        # hit api
        $api_response = $this->inward_model->updateInward($cleaned_params, $purchase_code);
        if($api_response['status']===true) {
          $message = 'Inward entry updated successfully with code `'.$purchase_code.'`';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/inward-entry');
        } else {
          $page_error = $api_response['apierror'];
          $form_data = $submitted_data;
        }
      } else {
        $form_errors = $validation_status['form_errors'];
        $form_data = $submitted_data;
      }
    }

    # theme variables.
    $controller_vars = array(
      'page_title' => 'Inward Material Entry - Update Transaction',
      'icon_name' => 'fa fa-laptop',
    );
    $template_vars = array(
      'utilities' => new Utilities,
      'credit_days_a' => array(0=>'Choose')+$credit_days_a,
      'suppliers' => array(''=>'Choose')+$suppliers_a,
      'payment_methods' => Constants::$PAYMENT_METHODS_PURCHASE,
      'taxes' => $taxes,
      'taxes_raw' => $taxes_raw,
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'total_item_rows' => $total_item_rows,
      'api_error' => $api_error,
      'states_a' => array(0=>'Choose') + Constants::$LOCATION_STATES,
      'supply_type_a' => array('' => 'Choose', 'inter' => 'Interstate', 'intra' => 'Intrastate'),
      'client_business_state' => $client_business_state,
      'client_locations' => array(''=>'Choose') + $client_locations,      
    );

    return array($this->template->render_view('inward-entry-update',$template_vars),$controller_vars);
  }

  // inward entry view action
  public function inwardEntryViewAction(Request $request) {

    # initiate variables.
    $credit_days_a = $suppliers_a = $payment_methods = [];
    $taxes_a = $taxes = $taxes_raw = [];
    $form_errors = $form_data = [];
    $page_error = '';

    $total_item_rows = 30;

    for($i=1;$i<=365;$i++) {
      $credit_days_a[$i] = $i;
    }

    $client_details = Utilities::get_client_details();
    $client_business_state = $client_details['locState'];

    # get client locations
    $client_locations_resp = $this->user_model->get_client_locations();
    if($client_locations_resp['status']) {
      foreach($client_locations_resp['clientLocations'] as $loc_details) {
        $client_locations[$loc_details['locationCode']] = $loc_details['locationName'];
      }
    }    

    # validate purchase code.
    if( is_null($request->get('purchaseCode')) ) {
      $this->flash->set_flash_message('Invalid purchase code.');
      Utilities::redirect('/inward-entry');
    } else {
      $purchase_code = Utilities::clean_string($request->get('purchaseCode'));
      $purchase_response = $this->inward_model->get_purchase_details($purchase_code);
      if($purchase_response['status']===true) {
        $purchase_details = $purchase_response['purchaseDetails'];
        $total_item_rows = count($purchase_details['itemDetails']);

        // dump($purchase_details['itemDetails']);
        // exit;

        # convert received item details to template item details.
        $item_names = array_column($purchase_details['itemDetails'],'itemName');
        $inward_qtys = array_column($purchase_details['itemDetails'],'itemQty');
        $free_qtys = array_column($purchase_details['itemDetails'],'freeQty');
        $lot_nos = array_column($purchase_details['itemDetails'],'lotNo');
        $mrps = array_column($purchase_details['itemDetails'],'mrp');
        $item_rates = array_column($purchase_details['itemDetails'],'itemRate');
        $discounts = array_column($purchase_details['itemDetails'],'discountAmount');                
        $tax_percents = array_column($purchase_details['itemDetails'],'taxPercent');
        $hsn_codes = array_column($purchase_details['itemDetails'],'hsnSacCode');
        $packed_qtys = array_column($purchase_details['itemDetails'],'packedQty');
        $billed_qtys = array_column($purchase_details['itemDetails'],'billedQty');        

        # unser item details from api data.
        unset($purchase_details['itemDetails']);

        # create form data variable.
        $form_data = $purchase_details;
        if(isset($form_data['adjAmount'])) {
          $form_data['adjustment'] = $form_data['adjAmount'];
          unset($form_data['adjAmount']);
        } else {
          $form_data['adjustment'] = 0;
        }

        $form_data['itemName'] = $item_names;
        $form_data['inwardQty'] = $inward_qtys;
        $form_data['freeQty'] = $free_qtys;
        $form_data['billedQty'] = $billed_qtys;
        $form_data['lotNo'] = $lot_nos;
        $form_data['itemRate'] = $item_rates;
        $form_data['taxPercent'] = $tax_percents;
        $form_data['mrp'] = $mrps;
        $form_data['itemDiscount'] = $discounts;  
        $form_data['hsnCodes'] = $hsn_codes;
        $form_data['packedQty'] = $packed_qtys;        
        if($form_data['grnFlag'] === 'yes') {
          $is_grn_generated = true;
        } else {
          $is_grn_generated = false;
        }
      } else {
        $this->flash->set_flash_message($purchase_response['apierror'], 1);
        Utilities::redirect('/inward-entry');
      }
    }

    $suppliers = $this->supplier_model->get_suppliers(0,0,[]);
    if($suppliers['status']) {
      $suppliers_a += $suppliers['suppliers'];
    }

    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }

    # theme variables.
    $controller_vars = array(
      'page_title' => 'Inward Material Entry - View Transaction',
      'icon_name' => 'fa fa-eye',
    );
    $template_vars = array(
      'utilities' => new Utilities,
      'credit_days_a' => array(0=>'Choose')+$credit_days_a,
      'suppliers' => array(''=>'Choose')+$suppliers_a,
      'payment_methods' => Constants::$PAYMENT_METHODS_PURCHASE,
      'taxes' => $taxes,
      'taxes_raw' => $taxes_raw,
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'total_item_rows' => $total_item_rows,
      'page_error' => $page_error,
      'is_grn_generated' => $is_grn_generated,
      'states_a' => array(0=>'Choose') + Constants::$LOCATION_STATES,
      'supply_type_a' => array('' => 'Choose', 'inter' => 'Interstate', 'intra' => 'Intrastate'),
      'client_business_state' => $client_business_state,
      'client_locations' => array(''=>'Choose') + $client_locations,      
    );

    return array($this->template->render_view('inward-entry-view',$template_vars),$controller_vars);
  }

  // inward list action
  public function inwardListAction(Request $request) {

    $suppliers = $search_params = $suppliers_a = $purchases_a = [];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';

    // ---------- get location codes from api ------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    $page_no = is_null($request->get('pageNo')) ? 1 : $request->get('pageNo');
    $per_page = is_null($request->get('perPage')) ? 100 : $request->get('perPage');
    if(count($request->request->all())>0) {
      $search_params = $request->request->all();
    } else {
      if(!is_null($request->get('fromDate'))) {
        $search_params['fromDate'] = Utilities::clean_string($request->get('fromDate'));
      }
      if(!is_null($request->get('toDate'))) {
        $search_params['toDate'] = Utilities::clean_string($request->get('toDate'));
      }
      if(!is_null($request->get('supplierID'))) {
        $search_params['supplierID'] = Utilities::clean_string($request->get('supplierID'));
      }
      if(!is_null($request->get('locationCode'))) {
        $search_params['locationCode'] = Utilities::clean_string($request->get('locationCode'));
      }      
    }

    // dump($search_params);
    // exit;

    $suppliers = $this->supplier_model->get_suppliers(0,0);
    if($suppliers['status']) {
      $suppliers_a = array(''=>'All Suppliers')+$suppliers['suppliers'];
    }

    $purchase_api_call = $this->inward_model->get_purchases($page_no,$per_page,$search_params);
    $api_status = $purchase_api_call['status'];        

    // check api status
    if($api_status) {
      // check whether we got products or not.
      if(count($purchase_api_call['purchases'])>0) {
        $slno = Utilities::get_slno_start(count($purchase_api_call['purchases']), $per_page, $page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;

        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no - 3;
          $page_links_to_end = $page_links_to_start + 10;
        }

        if($purchase_api_call['total_pages']<$page_links_to_end) {
          $page_links_to_end = $purchase_api_call['total_pages'];
        }

        if($purchase_api_call['record_count'] < $per_page) {
          $to_sl_no = ($slno+$purchase_api_call['record_count'])-1;
        }

        $purchases_a = $purchase_api_call['purchases'];
        $total_pages = $purchase_api_call['total_pages'];
        $total_records = $purchase_api_call['total_records'];
        $record_count = $purchase_api_call['record_count'];
      } else {
        $page_error = $purchase_api_call['apierror'];
      }

    } else {
      $page_error = $purchase_api_call['apierror'];
    }

    # prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'suppliers' => $suppliers_a,
      'purchases' => $purchases_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'client_locations' => ['' => 'All Stores'] + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Purchase Register',
      'icon_name' => 'fa fa-laptop',
    );

    # render template
    return array($this->template->render_view('inward-register',$template_vars),$controller_vars);
  }

  // update inward status accept or reject.
  public function updateInwardStatusAction(Request $request) {

    # allow this option only to the administrator.
    if(isset($_SESSION['utype']) && (int)$_SESSION['utype'] !== 3) {
      $this->flash_obj->set_flash_message("Permission Error: You are not authorized to perform this action.", 1);
      Utilities::redirect('/inward-entry/list');
    }

    # initiate variables.
    $credit_days_a = $suppliers_a = $payment_methods = $client_locations = [];
    $taxes_a = $taxes = $taxes_raw = [];
    $form_errors = $form_data = [];
    $api_error = $po_number = $po_code = '';
    $inward_status_a = [-1=>'Choose', 0=>'Pending', 1=>'Approved', 2=>'Rejected'];    
    
    $total_item_rows = 0;

    for($i=1;$i<=365;$i++) {
      $credit_days_a[$i] = $i;
    }

    $suppliers = $this->supplier_model->get_suppliers(0,0,[]);
    if($suppliers['status']) {
      $suppliers_a += $suppliers['suppliers'];
    }

    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }

    # get client details
    $client_details = Utilities::get_client_details();
    $client_business_state = $client_details['locState'];

    # get client locations
    $client_locations_resp = $this->user_model->get_client_locations();
    if($client_locations_resp['status']) {
      foreach($client_locations_resp['clientLocations'] as $loc_details) {
        $client_locations[$loc_details['locationCode']] = $loc_details['locationName'];
      }
    }    

    # validate purchase code.
    if( is_null($request->get('purchaseCode')) ) {
      $this->flash->set_flash_message('Invalid purchase code.', 1);
      Utilities::redirect('/inward-entry/list');
    } else {
      $purchase_code = Utilities::clean_string($request->get('purchaseCode'));
      $purchase_response = $this->inward_model->get_purchase_details($purchase_code);
      // dump($purchase_response);
      // exit;

      if($purchase_response['status']) {
        $purchase_details = $purchase_response['purchaseDetails'];
        $po_number = $purchase_details['poNo'];

        # convert received item details to template item details.
        $item_names = array_column($purchase_details['itemDetails'],'itemName');
        $inward_qtys = array_column($purchase_details['itemDetails'],'itemQty');
        $free_qtys = array_column($purchase_details['itemDetails'],'freeQty');
        $billed_qtys = array_column($purchase_details['itemDetails'],'billedQty');
        $mrps = array_column($purchase_details['itemDetails'],'mrp');
        $item_rates = array_column($purchase_details['itemDetails'],'itemRate');
        $tax_percents = array_column($purchase_details['itemDetails'],'taxPercent');
        $discounts = array_column($purchase_details['itemDetails'],'discountAmount');
        $igst_amounts = array_column($purchase_details['itemDetails'],'igstAmount');
        $cgst_amounts = array_column($purchase_details['itemDetails'],'cgstAmount');
        $sgst_amounts = array_column($purchase_details['itemDetails'],'sgstAmount');
        $hsn_codes = array_column($purchase_details['itemDetails'],'hsnSacCode');
        $packed_qtys = array_column($purchase_details['itemDetails'], 'packedQty');

        $total_item_rows = count($item_names);

        # unset item details from api data.
        unset($purchase_details['itemDetails']);

        # create form data variable.
        $form_data = $purchase_details;

        $form_data['itemName'] = $item_names;
        $form_data['inwardQty'] = $inward_qtys;
        $form_data['freeQty'] = $free_qtys;
        $form_data['billedQty'] = $billed_qtys;
        $form_data['igstAmount'] = $igst_amounts;
        $form_data['cgstAmount'] = $cgst_amounts;
        $form_data['sgstAmount'] = $sgst_amounts;        
        $form_data['itemRate'] = $item_rates;
        $form_data['taxPercent'] = $tax_percents;
        $form_data['mrp'] = $mrps;
        $form_data['itemDiscount'] = $discounts;
        $form_data['hsnCodes'] = $hsn_codes;
        $form_data['packedQty'] = $packed_qtys;
        if($form_data['grnFlag'] === 'yes') {
          $page_error = 'GRN is already generated for PO No. `'.$purchase_details['poNo']."`. You can't edit now.";
          $this->flash->set_flash_message($page_error, 1);
          Utilities::redirect('/inward-entry/list');
        } else {
          $is_grn_generated = false;
        }
      } else {
        $this->flash->set_flash_message($purchase_response['apierror'], 1);
        Utilities::redirect('/inward-entry');
      }
    }

    $suppliers = $this->supplier_model->get_suppliers(0,0,[]);
    if($suppliers['status']) {
      $suppliers_a += $suppliers['suppliers'];
    }

    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }

    $client_details = Utilities::get_client_details();
    $client_business_state = $client_details['locState'];
    // ------------------------------------- check for form Submission --------------------------------
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_ar_po_data($submitted_data);
      if($form_validation['status']===false) {
        $this->flash->set_flash_message('You have errors in this form. Please fix them before you save', 1);
        $form_errors = $form_validation['errors'];
      } else {
        $api_response = $this->inward_model->change_inward_status($form_validation['cleaned_params'], $purchase_code);
        if($api_response['status']) {
          $this->flash->set_flash_message('PO No. <b>`'.$po_number.'`</b> updated successfully.');
          Utilities::redirect('/inward-entry/list');
        } else {
          $page_error = $api_response['apierror'];
          $this->flash->set_flash_message($page_error,1);
        }
      }
    }

    // theme variables.
    $controller_vars = array(
      'page_title' => 'Purchases - Approve / Reject PO',
      'icon_name' => 'fa fa-laptop',
    );
    $template_vars = array(
      'utilities' => new Utilities,
      'credit_days_a' => array(0=>'Choose')+$credit_days_a,
      'suppliers' => array(''=>'Choose')+$suppliers_a,
      'payment_methods' => Constants::$PAYMENT_METHODS_PURCHASE,
      'taxes' => $taxes,
      'taxes_raw' => $taxes_raw,
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'total_item_rows' => $total_item_rows,
      'api_error' => $api_error,
      'states_a' => array(0=>'Choose') + Constants::$LOCATION_STATES,
      'supply_type_a' => array('' => 'Choose', 'inter' => 'Interstate', 'intra' => 'Intrastate'),
      'client_business_state' => $client_business_state,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'inward_status_a' => $inward_status_a,
      'errors' => $form_errors,
    );

    return array($this->template->render_view('change-inward-status', $template_vars),$controller_vars);
  }

  public function searchInwardAction(Request $request) {

    $search_params = $bills = $form_data = $form_errors = [];
    $slno = 0;
    
    $search_by_a = Constants::$INW_SEARCH_OPTIONS;

    // get location codes from api
    $client_locations = Utilities::get_client_locations();

    // check for filter variables.
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $validation = $this->_validate_search_form_data($form_data, $search_by_a, $client_locations);
      if($validation['status']) {
        $search_params = $validation['cleaned_params'];
        $api_response = $this->inward_model->search_purchase_bills($search_params);
        if($api_response['status']) {
          if(count($api_response['bills'])>0) {
            $bills = $api_response['bills'];
          } else {
            $page_error = $api_response['apierror'];
            $this->flash->set_flash_message($page_error, 1);
          }
        } else {
          $page_error = $api_response['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
      } else {
        $page_error = 'You have errors in the Form. Please check before you submit.';
        $form_errors = $validation['errors'];
        $this->flash->set_flash_message($page_error, 1);      
      }
    }

    // prepare form variables.
    $template_vars = array(
      'bills' => $bills,
      'form_data' => $form_data,
      'form_errors' => $form_errors,
      'search_by_a' => [''=>'Choose'] + $search_by_a,
      'sl_no' => 1,
      'client_locations' => $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
    );

    // theme variables.
    $controller_vars = array(
      'page_title' => 'Purchases - Search Bills',
      'icon_name' => 'fa fa-search',
    );

    return array($this->template->render_view('search-purchase-bills', $template_vars), $controller_vars);
  }

  // validate form data
  private function _validate_form_data($form_data=[], $is_grn_generated=false) {

    $form_errors = $cleaned_params = [];
    $is_one_item_found = false;

    $purchase_date = Utilities::clean_string($form_data['purchaseDate']);
    if(Utilities::is_valid_fin_date($purchase_date)) {
      $cleaned_params['purchaseDate'] = $purchase_date;
    } else {
      $form_errors['purchaseDate'] = 'PO Date is out of Financial year dates.';
    }

    // validate supplier name
    if( isset($form_data['supplierID']) && $form_data['supplierID'] === '') {
      $form_errors['supplierID'] = 'Invalid supplier name.';
    } else {
      $cleaned_params['supplierID'] = Utilities::clean_string($form_data['supplierID']);
    }

    $cleaned_params['poNo'] = Utilities::clean_string($form_data['poNo']);

    // validate payment method
    if( isset($form_data['paymentMethod']) && (int)$form_data['paymentMethod'] === 1) {
      $credit_days = (int)$form_data['creditDays'];
      if($credit_days>0) {
        $cleaned_params['creditDays'] = $credit_days;
        $cleaned_params['paymentMethod'] = 1;
      } else {
        $form_errors['creditDays'] = 'Credit days are mandatory.';
      }
    } else {
      $cleaned_params['paymentMethod'] = Utilities::clean_string($form_data['paymentMethod']);
    }

    // validate location code
    if( isset($form_data['locationCode']) && ctype_alnum($form_data['locationCode']) ) {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['locationCode'] = 'Invalid location code.';
    }

    // remarks field
    if(isset($form_data['remarks']) && $form_data['remarks'] !=='' ) {
      $cleaned_params['remarks'] = Utilities::clean_string($form_data['remarks']);
    } else {
      $cleaned_params['remarks'] = '';
    }

    //validate various charges.
    $packing_charges =  Utilities::clean_string($form_data['packingCharges']);
    $shipping_charges = Utilities::clean_string($form_data['shippingCharges']);
    $insurance_charges = Utilities::clean_string($form_data['insuranceCharges']);
    $other_charges = Utilities::clean_string($form_data['otherCharges']);
    $transporter_name = Utilities::clean_string($form_data['transporterName']);
    $lr_no = Utilities::clean_string($form_data['lrNos']);
    $lr_date = Utilities::clean_string($form_data['lrDate']);
    $chalan_no = Utilities::clean_string($form_data['challanNo']);
        
    if($packing_charges !== '' && !is_numeric($packing_charges)) {
      $form_errors['packingCharges'] = 'Invalid input. Must be numeric.';
    } else {
      $cleaned_params['packingCharges'] = $packing_charges;
    }
    if($shipping_charges !== '' && !is_numeric($shipping_charges)) {
      $form_errors['shippingCharges'] = 'Invalid input. Must be numeric.';
    } else {
      $cleaned_params['shippingCharges'] = $shipping_charges;
    }
    if($insurance_charges !== '' && !is_numeric($insurance_charges)) {
      $form_errors['insuranceCharges'] = 'Invalid input. Must be numeric.';
    } else {
      $cleaned_params['insuranceCharges'] = $insurance_charges;
    }
    if($other_charges !== '' && !is_numeric($other_charges)) {
      $form_errors['otherCharges'] = 'Invalid input. Must be numeric.';
    } else {
      $cleaned_params['otherCharges'] = $other_charges;
    }
    $cleaned_params['transporterName'] = $transporter_name;
    $cleaned_params['lrNos'] = $lr_no;
    $cleaned_params['lrDate'] = $lr_date;
    $cleaned_params['challanNo'] = $chalan_no;        

    // validate line items only if grn is not generated.
    if($is_grn_generated===false) {

      // validate line item details
      $item_names_a = $form_data['itemName'];
      $inward_qtys_a = $form_data['inwardQty'];
      $free_qtys_a = $form_data['freeQty'];
      $mrps_a = $form_data['mrp'];
      $item_rates_a = $form_data['itemRate'];
      $tax_percents_a = $form_data['taxPercent'];
      $item_discounts = $form_data['itemDiscount'];
      $item_hsnsac_codes_a = $form_data['hsnSacCode'];
      $packed_qty_a = $form_data['packedQty'];
      // $rack_nos_a = $form_data['rackNo'];
      $category_names_a = $form_data['categoryName'];
      $brand_names_a = $form_data['brandName'];

      foreach($item_names_a as $key=>$item_name) {
        if($item_name !== '') {

          $is_one_item_found = true;
          $cleaned_exp_date = '';

          $inward_qty = Utilities::clean_string($inward_qtys_a[$key]);
          $free_qty = Utilities::clean_string($free_qtys_a[$key]);
          $mrp = Utilities::clean_string($mrps_a[$key]);
          $item_rate = Utilities::clean_string($item_rates_a[$key]);
          $tax_percent = Utilities::clean_string($tax_percents_a[$key]);
          $discount_amount = Utilities::clean_string($item_discounts[$key]);
          $hsn_sac_code = Utilities::clean_string($item_hsnsac_codes_a[$key]);
          $packed_qty = Utilities::clean_string($packed_qty_a[$key]);
          // $rack_no = Utilities::clean_string($rack_nos_a[$key]);
          $category_name = Utilities::clean_string($category_names_a[$key]);
          $brand_name = Utilities::clean_string($brand_names_a[$key]);

          $cleaned_params['itemDetails']['itemName'][] = $item_name;
          $cleaned_params['itemDetails']['categoryName'][] = $category_name;
          $cleaned_params['itemDetails']['brandName'][] = $brand_name;
          // $cleaned_params['itemDetails']['rackNo'][] = $rack_no;

          if( !is_numeric($inward_qty) ) {
            $form_errors['itemDetails'][$key]['inwardQty'] = 'Invalid item qty';
          } else {
            $cleaned_params['itemDetails']['inwardQty'][] = $inward_qty;
          }

          # validate free qty only if value is available.
          if($free_qty !== '') {
            if( !is_numeric($free_qty) ) {
              $form_errors['itemDetails'][$key]['freeQty'] = 'Invalid item qty';
            } elseif($free_qty>$inward_qty) {
              $form_errors['itemDetails'][$key]['freeQty'] = 'Invalid item qty';
            } else {
              $cleaned_params['itemDetails']['freeQty'][] = $free_qty;
            }
          } else {
            $cleaned_params['itemDetails']['freeQty'][] = 0;
          }

          if( !is_numeric($mrp) || $mrp < $item_rate ) {
            $form_errors['itemDetails'][$key]['mrp'] = 'Invalid MRP';
          } else {
            $cleaned_params['itemDetails']['mrp'][] = $mrp;
          }
          if( !is_numeric($item_rate) ) {
            $form_errors['itemDetails'][$key]['itemRate'] = 'Invalid item rate';
          } else {
            $cleaned_params['itemDetails']['itemRate'][] = $item_rate;
          }
          if( !is_numeric($tax_percent) ) {
            $form_errors['itemDetails'][$key]['taxPercent'] = 'Invalid tax percent';
          } else {
            $cleaned_params['itemDetails']['taxPercent'][] = $tax_percent;
          }
          if( $discount_amount !== '' && !is_numeric($discount_amount) ) {
            $form_errors['itemDetails'][$key]['itemDiscount'] = 'Invalid discount amount';
          } else {
            $cleaned_params['itemDetails']['itemDiscount'][] = $discount_amount;
          }
          # validate hsn / sac code.
          if( $hsn_sac_code !=='' && !is_numeric(str_replace(' ', '', $hsn_sac_code)) ) {
            $form_errors['itemDetails'][$key]['hsnSacCode'] = 'Invalid HSN or SAC code';
          } else {
            $cleaned_params['itemDetails']['hsnSacCode'][] = $hsn_sac_code;
          }
          # validate packed qty.
          if(is_numeric($packed_qty) && $packed_qty > 0) {
            $cleaned_params['itemDetails']['packedQty'][] = $packed_qty;
          } else {
            $form_errors['itemDetails'][$key]['packedQty'] = 'Invalid Packed Qty.';
          }
        }
      }
      if($is_one_item_found===false) {
        $form_errors['itemDetailsError'] = 'At least one item is required in PO.';
      }
    }

    if(count($form_errors)>0) {
      return [
        'status' => false,
        'form_errors' => $form_errors,
      ];
    } else {
      return [
        'status' => true,
        'cleaned_params' => $cleaned_params,
      ];      
    }
  }

  // validate ar data
  private function _validate_ar_po_data($form_data= []) {
    $cleaned_params = $form_errors = [];
    $indent_status = isset($form_data['arStatus']) ? (int)Utilities::clean_string($form_data['arStatus']) : -1;
    $indent_remarks = isset($form_data['arRemarks']) ? Utilities::clean_string($form_data['arRemarks']) : '';
    if($indent_status === 1 || $indent_status === 2) {
      $cleaned_params['arStatus'] = $indent_status;
    } else {
      $form_errors['arStatus'] = 'Invalid Status. Must Approve or Reject.';
    }

    $cleaned_params['arRemarks'] = $indent_remarks;

    # return response.
    if(count($form_errors)>0) {
      return [
        'status' => false,
        'errors' => $form_errors,
      ];
    } else {
      return [
        'status' => true,
        'cleaned_params' => $cleaned_params,
      ];
    }
  }

  // validate search form data
  private function _validate_search_form_data($form_data=[], $search_by_a=[], $client_locations=[]) {
    $cleaned_params = $form_errors = [];

    $search_by = Utilities::clean_string($form_data['searchBy']);
    if($search_by === 'itemname') {
      $search_value = Utilities::clean_string($form_data['searchValueP']);
    } else {
      $search_value = Utilities::clean_string($form_data['searchValue']);
    }
    
    $location_code = Utilities::clean_string($form_data['locationCode']);

    if($search_by !== '' && in_array($search_by, array_keys($search_by_a))) {
      $cleaned_params['searchBy'] = $search_by;
    } else {
      $form_errors['searchBy'] = 'Invalid';
    }

    if($location_code !== '' && in_array($location_code, array_keys($client_locations))) {
      $cleaned_params['locationCode'] = $location_code;
    } else {
      $form_errors['locationCode'] = 'Invalid';
    }

    $cleaned_params['searchValue'] = $search_value;
    $cleaned_params['locationCode'] = $location_code;

    if(count($form_errors)>0) {
      return [
        'status' => false,
        'errors' => $form_errors,
      ];
    } else {
      return [
        'status' => true,
        'cleaned_params' => $cleaned_params,
      ];
    }
  }

  // map uploaded data with current form data.
  private function _map_uploaded_data_with_form_data($uploaded_data=[]) {
    // dump($uploaded_data);
    $form_data = [];
    $form_data['purchaseDate'] = $uploaded_data['purchaseDate'];
    $form_data['creditDays'] = $uploaded_data['creditDays'];
    $form_data['paymentMethod'] = $uploaded_data['paymentMethod'];
    $form_data['supplierID'] = $uploaded_data['supplierID'];
    $form_data['poNo'] = '';
    $form_data['locationCode'] = $uploaded_data['locationCode'];
    $form_data['supplyType'] = $uploaded_data['supplyType'];
    $form_data['supplierStateID'] = $uploaded_data['supplierStateID'];
    $form_data['supplierGSTNo'] = $uploaded_data['supplierGSTNo'];            
    foreach($uploaded_data['itemDetails'] as $key => $item_details) {
      $form_data['itemName'][$key] = $item_details['MatchedItemName'];
      $form_data['inwardQty'][$key] = $item_details['ItemQty'];
      $form_data['freeQty'][$key] = 0;      
      $form_data['mrp'][$key] = $item_details['SellingPriceOrMRP'];
      $form_data['itemRate'][$key] = $item_details['ItemRate'];
      $form_data['taxPercent'][$key] = $item_details['TaxPercent'];
      $form_data['itemDiscount'][$key] = $item_details['DiscountAmount'];
      $form_data['hsnCodes'][$key] = $item_details['HsnSacCode'];
      $form_data['packedQty'][$key] = $item_details['PackedQty'];
      $form_data['categoryName'][$key] = $item_details['CategoryName'];
      $form_data['rackNo'][$key] = $item_details['RackNo'];
      $form_data['brandName'][$key] = $item_details['BrandName'];
    }
    return $form_data;
  }
}