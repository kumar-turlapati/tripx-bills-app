<?php 

namespace ClothingRm\Finance\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Finance\Model\SuppOpbal;
use ClothingRm\Suppliers\Model\Supplier;

class FinSuppOpBalController {
	
	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->supp_model = new Supplier;
    $this->supp_op_model = new SuppOpbal;
    $this->flash = new Flash;
	}

  // supplier opbal create action
	public function supplierOpBalCreateAction(Request $request) {
    $page_error = $page_success = '';
    $submitted_data = $form_errors = $suppliers_a = $search_params = [];
    $modes_a = array(-1=>'Choose',0=>'Debit',1=>'Credit');
    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all());
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $this->supp_op_model->create_supplier_opbal($form_data);
        if($result['status']===true) {
          $message = 'Opening balance added successfully with code ` '.$result['opBalCode'].' `';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/fin/supp-opbal/create');
        } else {
          $page_error = $result['apierror'];
          $submitted_data = $request->request->all();
        }
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $request->request->all();
      }
    }

    $client_locations = Utilities::get_client_locations(true, false, true);  
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_errors' => $form_errors,
      'modes' => $modes_a,
      'submitted_data' => $submitted_data,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'client_locations' => array(''=>'All Locations') + $client_locations,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Create Supplier Opening Balance',
      'icon_name' => 'fa fa-university',
    );

    // render template
    return array($this->template->render_view('supp-opbal-create', $template_vars), $controller_vars);
	}

  // supplier opbal update action
	public function supplierOpBalUpdateAction(Request $request) {
    $page_error = $page_success = $opbal_code = '';
    $submitted_data = $form_errors = $suppliers_a = $search_params = [];
    $modes_a = array(-1=>'Choose',0=>'Debit',1=>'Credit');

    $opbal_code = !is_null($request->get('opBalCode')) ? Utilities::clean_string($request->get('opBalCode')) : '';

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all(),true);
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $this->supp_op_model->update_supplier_opbal($opbal_code,$form_data);
        if($result['status']) {
          $message = 'Opening balance updated successfully';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/fin/supp-opbal/create');
        } else {
          $page_error = $result['apierror'];
          $submitted_data = $request->request->all();
        }
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $request->request->all();
      }
    } else {
      $submitted_data = $this->_validate_opbal_code($opbal_code);
    }

    $client_locations = Utilities::get_client_locations(true);  
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_errors' => $form_errors,
      'suppliers' => $suppliers_a,
      'modes' => $modes_a,
      'submitted_data' => $submitted_data,
      'opbal_code' => $opbal_code,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'client_locations' => array(''=>'All Locations') + $client_locations,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Update Supplier Opening Balance',
      'icon_name' => 'fa fa-university',
    );

    // render template
    return array($this->template->render_view('supp-opbal-update', $template_vars), $controller_vars);
	}

  // supplier opbal list action
	public function supplierOpBalListAction(Request $request) {
    $balances = $filter_params = [];

    $location_code = !is_null($request->get('locationCode')) ? Utilities::clean_string($request->get('locationCode')) : '';

    $filter_params['locationCode'] = $location_code;
    $filter_params['perPage'] = 500;

    $result = $this->supp_op_model->get_supp_opbal_list($filter_params);

    // dump($result);
    if($result['status']) {
      $balances = $result['balances'];
    }

    $client_locations = Utilities::get_client_locations(true);  
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

     // prepare form variables.
    $template_vars = array(
      'balances' => $balances,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'locationCode' => $location_code,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Billwise Supplier Opening Balances',
      'icon_name' => 'fa fa-university',
    );

    // render template
    return array($this->template->render_view('supp-opbal-list', $template_vars), $controller_vars);
	}

  // validate form data
  private function _validate_form_data($form_data=[]) {
    $errors = $cleaned_params = [];

    $supplier_name = Utilities::clean_string($form_data['suppName']);
    $action = Utilities::clean_string($form_data['action']);
    $amount = Utilities::clean_string($form_data['amount']);
    $bill_no = Utilities::clean_string($form_data['billNo']);
    $bill_date = Utilities::clean_string($form_data['billDate']);
    $credit_days = Utilities::clean_string($form_data['creditDays']);
    $location_code = Utilities::clean_string($form_data['locationCode']);

    if($supplier_name === '') {
      $errors['suppName'] = 'Invalid supplier name';
    } else {
      $cleaned_params['suppName'] = $supplier_name;
    }
    if(!is_numeric($action) || $action<0 || $action>1) {
      $errors['action'] = 'Invalid action';
    } else {
      $cleaned_params['action'] = (int)$action;
    }
    if(!is_numeric($amount) || $amount<=0) {
      $errors['amount'] = 'Invalid amount';
    } else {
      $cleaned_params['amount'] = $amount;
    }
    if($bill_no === '') {
      $errors['billNo'] = 'Invalid bill no.';
    } else {
      $cleaned_params['billNo'] = $bill_no;
    }
    if($bill_date === '') {
      $errors['billDate'] = 'Invalid bill date';
    } else {
      $cleaned_params['billDate'] = $bill_date;
    }
    if(is_numeric($credit_days) && $credit_days>0) {
      $cleaned_params['creditDays'] = $credit_days;
    } else {
      $errors['creditDays'] = 'Invalid credit days';
    }
    if($location_code === '') {
      $errors['locationCode'] = 'Invalid store name';
    } else {
      $cleaned_params['locationCode'] = $location_code;
    }
    if(count($errors)>0) {
      return array('status' => false, 'errors' => $errors);
    } else {
      return array('status' => true, 'cleaned_params' => $cleaned_params);
    }
  }

  // validate opbal code.
  private function _validate_opbal_code($opbal_code='') {
    $supp_opbal_details = $this->supp_op_model->get_supp_opbal_details($opbal_code);
    if($supp_opbal_details['status']) {
      return $supp_opbal_details['opBalDetails'];
    } else {
      $this->flash->set_flash_message('Invalid entry',1);
      Utilities::redirect('/fin/supp-opbal/list');        
    }
  }
}

/*
  public function supplierOpBalImportAction(Request $request) {

    $op_a = ['append' => 'Append to existing data', 'remove' => 'Remove existing data and append'];
    $allowed_extensions = ['xls', 'ods', 'xlsx'];
    $upload_errors = [];

    $controller_vars = [];
    $template_vars = [
      'op_a' => $op_a,
      'upload_errors' => $upload_errors,
    ];

    // render template_vars
    return array($this->template->render_view('supp-opbal-import', $template_vars), $controller_vars);    
  }

  // Supplier Billwise outstanding.
  public function supplierBillwiseOsAction(Request $request) {
    $records = $suppliers_a = $search_params = array();

    if($request->get('supplierCode') && $request->get('supplierCode')!=='') {
      $sel_supp_id = Utilities::clean_string($request->get('supplierCode'));
      $search_params['supplierCode'] = $sel_supp_id;
    } else {
      $sel_supp_id = '';
    }

    $fin_model = new SuppOpbal();
    $supplier_api_call = new Supplier;

    $result = $fin_model->get_supp_billwise_outstanding($search_params);
    // dump($result);
    if($result['status']) {
      $records = $result['balances'];
    }

    $supp_params['pagination'] = 'no';
    $suppliers = $supplier_api_call->get_suppliers(0,0,$supp_params);
    if($suppliers['status']) {
        $suppliers_a += array('All Suppliers')+$suppliers['suppliers'];
    }

     // prepare form variables.
    $template_vars = array(
      'records' => $records,
      'suppliers' => $suppliers_a,
      'sel_supp_id' => $sel_supp_id,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Payables - Billwise',
      'icon_name' => 'fa fa-check',
    );

    // render template
    return array($this->template->render_view('supp-outstanding',$template_vars),$controller_vars);    
  }

  // Supplier Billwise outstanding.
  public function supplierBillwiseAsonAction(Request $request) {
    $records = $suppliers_a = $search_params = array();

    // if($request->get('supplierCode') && $request->get('supplierCode')!=='') {
    //   $sel_supp_id = Utilities::clean_string($request->get('supplierCode'));
    //   $search_params['supplierCode'] = $sel_supp_id;
    // } else {
    //   $sel_supp_id = '';
    // }

    $fin_model = new SuppOpbal();
    $supplier_api_call = new Supplier;

    $result = $fin_model->get_supp_billwise_os_ason($search_params);
    // dump($result);
    if($result['status']) {
      $records = $result['balances'];
    }

    // $supp_params['pagination'] = 'no';
    // $suppliers = $supplier_api_call->get_suppliers(0,0,$supp_params);
    // if($suppliers['status']) {
    //     $suppliers_a += array('All Suppliers')+$suppliers['suppliers'];
    // }

     // prepare form variables.
    $template_vars = array(
      'records' => $records,
      // 'suppliers' => $suppliers_a,
      // 'sel_supp_id' => $sel_supp_id,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Payables - As on date',
      'icon_name' => 'fa fa-question',
    );

    // render template
    return array($this->template->render_view('supp-outstanding-ason',$template_vars),$controller_vars);    
  }

  // Supplier ledger
  public function supplierLedger(Request $request) {

    $records = $suppliers_a = array();
    $supp_code = $supplier_name = '';

    $fin_model = new SuppOpbal();
    $supplier_api_call = new Supplier;

    // suppliers
    $supp_params['pagination'] = 'no';
    $suppliers = $supplier_api_call->get_suppliers(0,0,$supp_params);
    if($suppliers['status']) {
      $suppliers_a  = $suppliers['suppliers'];
    }

    // dump($suppliers_a);

    if( (!is_null($request->get('suppCode')) && $request->get('suppCode') !== '') ||
        count($request->request->all()) > 0
      ) {
      $supp_code = Utilities::clean_string($request->get('suppCode'));
      $response = $fin_model->get_supplier_ledger($supp_code);
      if($response['status']===true) {
        $records = $response['data'];
        usort($records, function($a, $b) {
          return strtotime($a["tranDate"]) - strtotime($b["tranDate"]);
        });
      }
      $supplier_name = ' - '.$suppliers_a[$supp_code];
    }

    // build variables
    $template_vars = array(
      'records' => $records,
      'sel_supp_id' => $supp_code,
      'suppliers' => $suppliers_a,
    );
    $controller_vars = array(
      'page_title' => "Finance Management ".$supplier_name.' - Ledger',
      'icon_name' => 'fa fa-book',
    );

    // render template
    return array($this->template->render_view('supplier-ledger',$template_vars),$controller_vars);     
  }*/
