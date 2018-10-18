<?php 

namespace ClothingRm\PurchaseReturns\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Purchases\Model\Purchases;
use ClothingRm\Suppliers\Model\Supplier;
use ClothingRm\Taxes\Model\Taxes;
use ClothingRm\Inward\Model\Inward;
use ClothingRm\PurchaseReturns\Model\PurchaseReturns;
use User\Model\User;

class PurchaseReturnsController
{
  private $template, $supplier_model;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->supplier_model = new Supplier;
    $this->taxes_model = new Taxes;
    $this->inward_model = new Inward;
    $this->flash = new Flash;
    $this->purchase_model = new Purchases;
    $this->user_model = new User;
    $this->pr_model = new PurchaseReturns;
  }

  // entry
  public function purchaseReturnEntryAction(Request $request) {

    $credit_days_a = $suppliers_a = $payment_methods = $client_locations = [];
    $taxes_a = $taxes = $taxes_raw = [];
    $form_errors = $form_data = [];
    $api_error = '';
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

    # get purchase details if purchase code is available.
    if( !is_null($request->get('pc')) ) {
      $purchase_code = Utilities::clean_string($request->get('pc'));
      $purchase_response = $this->purchase_model->get_purchase_details($purchase_code);
      if($purchase_response['status']) {
        $purchase_details = $purchase_response['purchaseDetails'];

        # convert received item details to template item details.
        $item_names = array_column($purchase_details['itemDetails'],'itemName');
        $item_codes = array_column($purchase_details['itemDetails'],'itemCode');        
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
        $lot_nos = array_column($purchase_details['itemDetails'],'lotNo');
        $packed_qtys = array_column($purchase_details['itemDetails'], 'packedQty');        

        $total_item_rows = is_array($item_names) && count($item_names) > 0 ? count($item_names) : 30;

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
        $form_data['lotNos'] = $lot_nos;
        $form_data['itemCode'] = $item_codes;
        $form_data['packedQty'] = $packed_qtys;
      } else {
        $this->flash->set_flash_message($purchase_response['apierror'], 1);
        Utilities::redirect('/purchase-return/entry');
      }
    } else {
      $this->flash->set_flash_message('Invalid Purchase Code (or) Invalid entry', 1);
      Utilities::redirect('/purchase-return/register');      
    }

    # check if form is submitted.
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $validation_status = $this->_validate_form_data($submitted_data,$item_codes,$lot_nos,$inward_qtys);
      if($validation_status['status']) {
        $cleaned_params = $validation_status['cleaned_params'];
        # hit api
        $api_response = $this->pr_model->createPurchaseReturn($cleaned_params, $purchase_code);
        if($api_response['status']) {
          $message = 'Purchase return entry created successfully with code ` '.$api_response['returnCode'].' `';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/purchase-return/register');
        } else {
          $api_error = $api_response['apierror'];
          $form_data = $submitted_data;
          $form_data['returnQty'] = array_values($submitted_data['returnQty']);
        }
      } else {
        $form_errors = $validation_status['form_errors'];
        $form_data['returnQty'] = array_values($submitted_data['returnQty']);
      }
    }

    # theme variables.
    $controller_vars = array(
      'page_title' => 'Purchase Return Entry',
      'icon_name' => 'fa fa-undo',
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

    return array($this->template->render_view('purchase-return-entry',$template_vars),$controller_vars);
  }

  // view action
  public function purchaseReturnViewAction(Request $request) {
    $purchase_return_code = !is_null($request->get('returnCode')) ? Utilities::clean_string($request->get('returnCode')) : '';
    if(ctype_alnum($purchase_return_code)) {
      $pr_details = $this->pr_model->get_purchase_return_details($purchase_return_code);
      if($pr_details['status'] === false) {
        $this->flash->set_flash_message('Invalid purchase return entry !',1);         
        Utilities::redirect('/purchase-return/register');
      } else {
        $return_details = $pr_details['prDetails']['returnDetails'];
        $return_item_details = $pr_details['prDetails']['returnDetails']['itemDetails'];
        unset($return_details['itemDetails']);
      }
    } else {
      $this->flash->set_flash_message('Invalid purchase return format !',1);         
      Utilities::redirect('/purchase-return/register');      
    }

    # theme variables.
    $controller_vars = array(
      'page_title' => 'Purchase Return Entry - View',
      'icon_name' => 'fa fa-undo',
    );
    $template_vars = array(
      'utilities' => new Utilities,
      'return_details' => $return_details,
      'return_item_details' => $return_item_details,
    );

    # render template
    return array($this->template->render_view('purchase-return-view',$template_vars),$controller_vars);
  }

  // list returns
  public function purchaseReturnRegisterAction(Request $request) {

    $suppliers = $search_params = $suppliers_a = $returns_a = [];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';

    $page_no = is_null($request->get('pageNo')) ? 1 : $request->get('pageNo');
    $per_page = is_null($request->get('perPage')) ? 100 : $request->get('perPage');
    $location_code = is_null($request->get('locationCode')) ? '' : $request->get('locationCode');
    $supplier_code = is_null($request->get('supplierCode')) ? '' : $request->get('supplierCode');
    $from_date = is_null($request->get('fromDate')) ? '01-'.date('m').'-'.date("Y") : $request->get('fromDate');
    $to_date = is_null($request->get('toDate')) ? date("d-m-Y") : $request->get('toDate');

    if(count($request->request->all()) > 0) {
      $search_params = $request->request->all();
    } else {
      $search_params['pageNo'] = $page_no;
      $search_params['per_page'] = $per_page;
      $search_params['locationCode'] = $location_code;
      $search_params['supplierCode'] = $supplier_code;
      $search_params['fromDate'] = $from_date;
      $search_params['toDate'] = $to_date;
    }

    $suppliers = $this->supplier_model->get_suppliers(0,0);
    if($suppliers['status']) {
      $suppliers_a = array(''=>'All Suppliers')+$suppliers['suppliers'];
    }

    $pr_api_call = $this->pr_model->purchase_return_register($search_params);
    $api_status = $pr_api_call['status'];

    // dump($pr_api_call);
    // exit;

    # check api status
    if($api_status) {
      # check whether we got products or not.
      if(count($pr_api_call['response']['returns'])>0) {
        $slno = Utilities::get_slno_start(count($pr_api_call['response']['returns']), $per_page, $page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no - 3;
          $page_links_to_end = $page_links_to_start + 10;
        }
        if($pr_api_call['response']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $pr_api_call['response']['total_pages'];
        }
        if($pr_api_call['response']['total_records'] < $per_page) {
          $to_sl_no = ($slno+$pr_api_call['response']['total_records'])-1;
        }
        $returns_a = $pr_api_call['response']['returns'];
        $total_pages = $pr_api_call['response']['total_pages'];
        $total_records = $pr_api_call['response']['total_records'];
      } else {
        $page_error = $purchase_api_call['apierror'];
      }
    } else {
      $page_error = $pr_api_call['apierror'];
    }

    # prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'suppliers' => $suppliers_a,
      'returns' => $returns_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Purchase Return Register',
      'icon_name' => 'fa fa-undo',
    );

    # render template
    return array($this->template->render_view('purchase-return-register',$template_vars),$controller_vars);
  }

  // delete action
  public function purchaseReturnDeleteAction(Request $request) {
    $purchase_return_code = !is_null($request->get('returnCode')) ? Utilities::clean_string($request->get('returnCode')) : '';
    if(ctype_alnum($purchase_return_code)) {
      $pr_details = $this->pr_model->get_purchase_return_details($purchase_return_code);
      if($pr_details['status'] === false) {
        $this->flash->set_flash_message('Invalid purchase return entry !',1);         
        Utilities::redirect('/purchase-return/register');
      }
      $api_response = $this->pr_model->delete_purchase_return($purchase_return_code);
      $status = $api_response['status'];
      if($status === false) {
        $this->flash->set_flash_message('Unable to delete the entry.', 1);
      } else {
        $this->flash->set_flash_message('Entry deleted successfully', 1);
      }
    } else {
      $this->flash->set_flash_message('Please choose entry to delete.', 1);
    }

    Utilities::redirect('/purchase-return/register');
  }

  private function _validate_form_data($submitted_data=[], $item_codes=[], $lot_nos=[], $inward_qtys=[]) {

    $form_errors = $cleaned_params = [];
    $is_one_item_found = false;

    $cleaned_params['returnDate'] = Utilities::clean_string($submitted_data['returnDate']);
    $cleaned_params['items'] = [];
    $return_qtys = $submitted_data['returnQty'];

    $i = 0;
    foreach($return_qtys as $return_key => $return_qty) {
      $purchase_key = $item_codes[$i].'__'.$lot_nos[$i];
      $inward_qty = $inward_qtys[$i];
      if($return_key === $purchase_key && $return_qty > 0) {
        $is_one_item_found = true;
        if($return_qty <= $inward_qty) {
          $cleaned_params['items'][$purchase_key] = $return_qty;
        } else {
          $form_errors['itemDetails'][$i][$purchase_key] = 'Invalid / Excess Qty.';
        }
      }
      $i++;
    }

    if(!$is_one_item_found) {
      $form_errors['itemDetailsError'] = 'At least one item qty. is required for purchase return!';
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
}