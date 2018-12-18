<?php 

namespace ClothingRm\Grn\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Grn\Model\GrnNew;
use ClothingRm\Inward\Model\Inward;
use ClothingRm\Suppliers\Model\Supplier;

class GrnControllerNew 
{
  protected $views_path;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash;
    $this->inward_model = new Inward;
    $this->supplier_model = new Supplier;
    $this->grn_model = new GrnNew;    
  }

  // GRN entry Action
  public function grnEntryCreateAction(Request $request) {
    
    if(is_null($request->get('poNo')) ) {
      $this->flash->set_flash_message('PO Number is required to generate a GRN.', 1);
      Utilities::redirect('/grn/list');
    } else {
      $po_no = Utilities::clean_string($request->get('poNo'));
      $po_code = Utilities::clean_string($request->get('poCode'));
    }

    # initialize variables.
    $form_data = $form_errors = $suppliers_a = array();
    $total_item_rows = 0;
    $api_error = '';

    if( count($request->request->all()) > 0 ) {
      $submitted_data = $request->request->all();
      $validation_status = $this->_validate_form_data($submitted_data);

      // dump($validation_status);
      // exit;

      if($validation_status['status']===true) {
        $cleaned_params = $validation_status['cleaned_params'];
        $cleaned_params['poNo'] = $po_no;
        $cleaned_params['poCode'] = $po_code;
        # hit api
        $api_response = $this->grn_model->createGRN($cleaned_params);
        if($api_response['status']===true) {
          $message = 'GRN created successfully with entry code ` '.$api_response['grnCode'].' `';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/grn/list');
        } else {
          $api_error = $api_response['apierror'];
          $form_data = $submitted_data;
        }
      } else {
        $form_errors = $validation_status['form_errors'];
        $form_data = $submitted_data;
      }
    }

    # get PO Details based on PO Number;
    $purchase_response = $this->inward_model->get_purchase_details($po_code);

    if($purchase_response['status']) {

      $purchase_details = $purchase_response['purchaseDetails'];
      # check GRN is already generated for this PO.
      if($purchase_details['grnFlag'] === 'yes') {
        $this->flash->set_flash_message('GRN is already generated for this PO.',1);
        Utilities::redirect('/grn/list');
      }

      // dump($purchase_details);
      // exit;

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
      $hsn_sac_codes = array_column($purchase_details['itemDetails'], 'hsnSacCode');
      $lot_nos = array_column($purchase_details['itemDetails'], 'lotNo');
      $packed_qtys = array_column($purchase_details['itemDetails'], 'packedQty');

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
      $form_data['hsnSacCodes'] = $hsn_sac_codes;
      $form_data['itemCode'] = $item_codes;
      $form_data['lotNos'] = $lot_nos;
      $form_data['packedQtys'] = $packed_qtys;

    # invalid PO No. redirect user.
    } else {
      $this->flash->set_flash_message('Invalid PO No (or) PO does not exists.',1);
      Utilities::redirect('/grn/list');
    }

    # loop through credit days
    for($i=1;$i<=365;$i++) {
      $credit_days_a[$i] = $i;
    }

    # get suppliers list
    $suppliers = $this->supplier_model->get_suppliers(0,0,[]);
    if($suppliers['status']) {
      $suppliers_a += $suppliers['suppliers'];
    }

    # theme variables.
    $controller_vars = array(
      'page_title' => 'Godown Receipt Note',
      'icon_name' => 'fa fa-gavel',
    );
    $template_vars = array(
      'utilities' => new Utilities,      
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'credit_days_a' => array(0=>'Choose')+$credit_days_a,
      'suppliers' => array(''=>'Choose')+$suppliers_a,
      'payment_methods' => Constants::$PAYMENT_METHODS_PURCHASE,
      'total_item_rows' => count($form_data['itemName']),
      'api_error' => $api_error,
    );

    return array($this->template->render_view('grn-create',$template_vars),$controller_vars);
  }

  // GRN list action
  public function grnListAction(Request $request) {
    $suppliers= $search_params = $suppliers_a = $grns_a = array();

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';

    $page_no = !is_null($request->get('pageNo')) && is_numeric($request->get('pageNo')) ? $request->get('pageNo') : 1;
    $per_page = !is_null($request->get('perPage')) && is_numeric($request->get('perPage')) ? $request->get('perPage') : 100;
    $from_date = !is_null($request->get('fromDate')) && $request->get('fromDate') !== '' ? $request->get('fromDate') : '01-'.date('m').'-'.date("Y");
    $to_date = !is_null($request->get('toDate')) && $request->get('toDate') !== '' ? $request->get('toDate') : date("d-m-Y");
    $supplier_id = !is_null($request->get('supplierID')) && $request->get('supplierID') !== '' ? $request->get('supplierID') : '';

    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $search_params['fromDate'] = $form_data['fromDate'];
      $search_params['toDate'] = $form_data['toDate'];
      $search_params['supplierID'] = $form_data['supplierID'];
    } else {
      $search_params['fromDate'] = $from_date;
      $search_params['toDate'] = $to_date;
      $search_params['supplierID'] = $supplier_id;
    }

    $search_params['perPage'] = $per_page;
    $search_params['pageNo'] = $page_no;

    $supplier_api_call = new Supplier;
    $suppliers = $this->supplier_model->get_suppliers(0,0);
    if($suppliers['status']) {
        $suppliers_a = $suppliers['suppliers'];
    }

    $grn_api_response = $this->grn_model->get_grns($page_no,$per_page,$search_params);
    $api_status = $grn_api_response['status'];     

      # check api status
      if($api_status) {
        # check whether we got products or not.
        if(count($grn_api_response['grns'])>0) {
          $slno = Utilities::get_slno_start(count($grn_api_response['grns']), $per_page, $page_no);
          $to_sl_no = $slno+$per_page;
          $slno++;
          if($page_no<=3) {
            $page_links_to_start = 1;
            $page_links_to_end = 10;
          } else {
            $page_links_to_start = $page_no-3;
            $page_links_to_end = $page_links_to_start+10;            
          }
          if($grn_api_response['total_pages']<$page_links_to_end) {
            $page_links_to_end = $grn_api_response['total_pages'];
          }
          if($grn_api_response['record_count'] < $per_page) {
            $to_sl_no = ($slno+$grn_api_response['record_count'])-1;
          }
          $grns_a = $grn_api_response['grns'];
          $total_pages = $grn_api_response['total_pages'];
          $total_records = $grn_api_response['total_records'];
          $record_count = $grn_api_response['record_count'];
        } else {
          $page_error = $grn_api_response['apierror'];
        }
      } else {
        $page_error = $grn_api_response['apierror'];
      }           

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'suppliers' => array(''=>'Choose')+$suppliers_a,
      'grns' => $grns_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'GRN Register',
      'icon_name' => 'fa fa-gavel',
    );

    // render template
    return array($this->template->render_view('grn-register',$template_vars),$controller_vars);
  }

  // GRN view action
  public function grnViewAction(Request $request) {
    # initialize variables.
    $po_no = '';
    $submitted_data = $grn_details = $suppliers = $suppliers_a = array();
    $search_params = array();

    $suppliers_a = array(''=>'Choose');      

    $qtys_a = array(0=>'Sel');
    for($i=1;$i<=500;$i++) {
      $qtys_a[$i] = $i;
      if($i<=365) {
        $credit_days_a[$i] = $i;
      }
    }

    $suppliers = $this->supplier_model->get_suppliers(0,0,$search_params);
    if($suppliers['status']) {
      $suppliers_a += $suppliers['suppliers'];
    }

    if($request->get('grnCode') && $request->get('grnCode')!=='') {
      $grn_code = Utilities::clean_string($request->get('grnCode'));
      $grn_response = $this->grn_model->get_grn_details($grn_code);
      // dump($grn_response);
      // exit;
      if($grn_response['status']===true) {
        $grn_details = $grn_response['grnDetails'];
      } else {
        $page_error =   $grn_response['apierror'];
        $this->flash->set_flash_message($page_error,1);
        Utilities::redirect('/grn/list');
      }
      $page_title = 'View GRN Transaction';
    } else {
      $flash->set_flash_message('Invalid GRN',1);
      Utilities::redirect('/grn/list');
    }        

    // prepare form variables.
    $template_vars = array(
      'suppliers' => $suppliers,
      'payment_methods' => Constants::$PAYMENT_METHODS_PURCHASE,
      'credit_days_a' => array(0=>'Choose') +$credit_days_a,
      'suppliers' => $suppliers_a,
      'qtys_a' => $qtys_a,   
      'grn_details' => $grn_details,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'View GRN Entry',
      'icon_name' => 'fa fa-gavel',
    );

    // render template
    return array($this->template->render_view('grn-view-new',$template_vars),$controller_vars);    
  }

  // Validate form data
  private function _validate_form_data($submitted_data=[]) {
    $form_errors = $cleaned_params = [];

    $grn_date = Utilities::clean_string($submitted_data['grnDate']);
    if(Utilities::is_valid_fin_date($grn_date)) {
      $cleaned_params['grnDate'] = $grn_date;
    } else {
      $form_errors['grnDate'] = 'GRN Date is out of Financial year dates.';
    }    

    if(isset($submitted_data['billNo']) && $submitted_data['billNo'] != '' ) {
      $cleaned_params['billNo'] = Utilities::clean_string($submitted_data['billNo']);
    } else {
      $form_errors['billNo'] = 'Bill No. is mandatory for GRN';
    }
    if( isset($submitted_data['acceptedQty']) && count($submitted_data['acceptedQty'])>0 ) {
      foreach($submitted_data['acceptedQty'] as $item_code=>$qty) {
        if(!is_numeric($qty)  || $qty<0) {
          $form_errors['acceptedQty'][$item_code] = 'Invalid Qty';
        } else {
          $cleaned_params['grnItems'][$item_code] = $qty;
        }
      }
    } else {
      $form_errors['acceptedQty'] = 'Invalid quantities';      
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