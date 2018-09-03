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
	}

	public function supplierOpBalCreateAction(Request $request) {
    $page_error = $page_success = '';
    $submitted_data = $form_errors = $suppliers_a = $search_params = array();
    $modes_a = array(-1=>'Choose',0=>'Debit',1=>'Credit');

    $supplier_api_call = new Supplier;
    $search_params['pagination'] = 'no';

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all());
      $status = $validate_form['status'];
      if($status) {
        $flash = new Flash();
        $form_data = $validate_form['cleaned_params'];
        $fin_model = new SuppOpbal();
        $result = $fin_model->create_supplier_opbal($form_data);
        // dump($result);
        // exit;
        if($result['status']===true) {
          $message = 'Opening balance added successfully with code ` '.$result['opBalCode'].' `';
          $flash->set_flash_message($message);
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

    $suppliers = $supplier_api_call->get_suppliers(0,0,$search_params);
    if($suppliers['status']) {
        $suppliers_a = array(''=>'Choose')+$suppliers['suppliers'];
    }    

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_errors' => $form_errors,
      'suppliers' => $suppliers_a,
      'modes' => $modes_a,
      'submitted_data' => $submitted_data,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Suppliers',
      'icon_name' => 'fa fa-university',
    );

    // render template
    return array($this->template->render_view('supp-opbal-create', $template_vars), $controller_vars);
	}

	public function supplierOpBalUpdateAction(Request $request) {
    $page_error = $page_success = $opbal_code = '';
    $submitted_data = $form_errors = $suppliers_a = $search_params = array();
    $modes_a = array(-1=>'Choose',0=>'Debit',1=>'Credit');

    $supplier_api_call = new Supplier;
    $flash = new Flash;
    $fin_model = new SuppOpbal;

    $search_params['pagination'] = 'no';

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all(),true);
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $opbal_code = $form_data['opBalCode'];
        unset($form_data['opBalCode']);
        $result = $fin_model->update_supplier_opbal($opbal_code,$form_data);
        if($result['status']===true) {
          $message = 'Opening balance updated successfully';
          $flash->set_flash_message($message);
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
      $opbal_code = Utilities::clean_string($request->get('opBalCode'));
      $submitted_data = $this->_validate_opbal_code($opbal_code);
    }

    $suppliers = $supplier_api_call->get_suppliers(0,0,$search_params);
    if($suppliers['status']) {
        $suppliers_a = array(''=>'Choose')+$suppliers['suppliers'];
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
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Suppliers',
      'icon_name' => 'fa fa-university',
    );

    // render template
    return array($this->template->render_view('supp-opbal-update', $template_vars), $controller_vars);
	}

	public function supplierOpBalListAction(Request $request) {
    $balances = array();
    
    $fin_model = new SuppOpbal();
    $result = $fin_model->get_supp_opbal_list(array('per_page'=>300));
    // dump($result);
    if($result['status']) {
      $balances = $result['balances'];
    }

     // prepare form variables.
    $template_vars = array(
      'balances' => $balances,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Suppliers',
      'icon_name' => 'fa fa-university',
    );

    // render template
    return array($this->template->render_view('supp-opbal-list', $template_vars), $controller_vars);
	}

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
  }

  // Validate form data
  private function _validate_form_data($form_data=array(),$is_update=false) {
    $errors = $cleaned_params = array();

    $supp_code = Utilities::clean_string($form_data['suppCode']);
    $action = Utilities::clean_string($form_data['action']);
    $amount = Utilities::clean_string($form_data['amount']);
    if($is_update) {
      $opbal_code = Utilities::clean_string($form_data['opBalCode']);
      $this->_validate_opbal_code($opbal_code);
      $cleaned_params['opBalCode'] = $opbal_code;
    }

    if($supp_code === '') {
      $errors['suppCode'] = 'Invalid supplier code';
    } else {
      $cleaned_params['suppCode'] = $supp_code;
    }
    if(!is_numeric($action) || $action<0 || $action>1) {
      $errors['action'] = 'Invalid action';
    } else {
      $cleaned_params['action'] = (int)$action===0?'d':'c'; //$action; //;
    }
    if(!is_numeric($amount) || $amount<=0) {
      $errors['amount'] = 'Invalid amount';
    } else {
      $cleaned_params['amount'] = $amount;
    }

    $cleaned_params['openingDate'] = Utilities::clean_string($form_data['openDate']);

    if(count($errors)>0) {
      return array('status'=>false, 'errors'=>$errors);
    } else {
      return array('status'=>true, 'cleaned_params'=>$cleaned_params);
    }    
  }

  // validate opbal code.
  private function _validate_opbal_code($opbal_code='') {
    $fin_model = new SuppOpbal();
    $supp_opbal_details = $fin_model->get_supp_opbal_details($opbal_code);
    if($supp_opbal_details['status']) {
      return $this->_map_api_data($supp_opbal_details['opBalDetails']);
    } else {
      $flash->set_flash_message('Invalid entry',1);
      Utilities::redirect('/fin/supp-opbal/list');        
    }
  }

  private function _map_api_data($opbal_details=array()) {
    $supp_code = $opbal_details['supplierCode'];
    if($opbal_details['action']==='c') {
      $action = 1;
    } else {
      $action = 0;
    }
    $opbal_details['suppCode'] = $supp_code;
    $opbal_details['action'] = $action;
    unset($opbal_details['supplierCode']);
    return $opbal_details;
  }
}