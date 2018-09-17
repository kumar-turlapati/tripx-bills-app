<?php 

namespace ClothingRm\Finance\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Finance\Model\CustOpbal;
use ClothingRm\Customers\Model\Customers;

class FinCustOpBalController {
	
	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->cust_model = new Customers;
    $this->cust_op_model = new CustOpbal;
    $this->flash_obj = new Flash;    
	}

	public function customerOpBalCreateAction(Request $request) {
    $page_error = $page_success = '';
    $submitted_data = $form_errors = $search_params = [];
    $modes_a = array(-1=>'Choose',0=>'Debit',1=>'Credit');

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all());
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $this->cust_op_model->create_customer_opbal($form_data);
        if($result['status']) {
          $message = 'Opening balance added successfully with code ` '.$result['opBalCode'].' `';
          $this->flash_obj->set_flash_message($message);
          Utilities::redirect('/fin/cust-opbal/create');
        } else {
          $this->flash_obj->set_flash_message($result['apierror'],1);
          $submitted_data = $request->request->all();
        }
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $request->request->all();
      }
    }

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_errors' => $form_errors,
      'modes' => $modes_a,
      'submitted_data' => $submitted_data,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Customers',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('cust-opbal-create', $template_vars), $controller_vars);
	}

  public function customerOpBalUpdateAction(Request $request) {
    $page_error = $page_success = '';
    $submitted_data = $form_errors = $search_params = [];
    $modes_a = array(-1=>'Choose',0=>'Debit',1=>'Credit');

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all());
      $opbal_code = $request->get('opBalCode');
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $this->cust_op_model->update_customer_opbal($form_data, $opbal_code);
        if($result['status']) {
          $message = 'Opening balance updated successfully.';
          $this->flash_obj->set_flash_message($message);
          Utilities::redirect('/fin/cust-opbal/list');
        } else {
          $this->flash_obj->set_flash_message($result['apierror'],1);
          $submitted_data = $request->request->all();
        }
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $request->request->all();
      }
    } elseif( !is_null($request->get('opBalCode')) ) {
      $opbal_code = Utilities::clean_string($request->get('opBalCode'));
      $submitted_data = $this->_validate_opbal_code($opbal_code);
    }

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_errors' => $form_errors,
      'modes' => $modes_a,
      'submitted_data' => $submitted_data,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Customers',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('cust-opbal-update', $template_vars), $controller_vars);
  }

  public function customerOpBalDeleteAction(Request $request) {
    if( !is_null($request->get('opBalCode')) ) {
      $opbal_code = Utilities::clean_string($request->get('opBalCode'));
      $submitted_data = $this->_validate_opbal_code($opbal_code);
      if(is_array($submitted_data) && count($submitted_data) > 0) {
        // delete the record.
        $result = $this->cust_op_model->delete_customer_opbal($opbal_code);
        if($result['status']) {
          $this->flash_obj->set_flash_message('Entry deleted successfully.');
        } else {
          $this->flash_obj->set_flash_message($result['apierror'],1);
        }
      } else {
        $this->flash_obj->set_flash_message('Flushed entry or entry does not exists', 1);
      }
    }
    Utilities::redirect('/fin/cust-opbal/list');
  }

  public function customerOpBalListAction(Request $request) {

    $customers_list = $customers = $search_params = [];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';

    $page_no = !is_null($request->get('pageNo')) ? $request->get('pageNo') : 1;
    $per_page = !is_null($request->get('perPage')) ? $request->get('perPage') : 300;
    $customer_name = is_null($request->get('custName')) ? '' : Utilities::clean_string($request->get('custName'));
    $state_code = is_null($request->get('stateCode')) ? '' : Utilities::clean_string($request->get('stateCode'));

    $search_params = [
      'pageNo' => $page_no,
      'perPage' => $per_page,
      'custName' => $customer_name,
      'stateCode' => $state_code,
    ];    

    $customers_list = $this->cust_op_model->get_cust_opbal_list($search_params);
    $api_status = $customers_list['status'];
    // dump($customers_list);
    // exit;

    if($api_status) {
      if(count($customers_list['response']['customers']) >0) {
        $slno = Utilities::get_slno_start(count($customers_list['response']['customers']),$per_page,$page_no);
        $to_sl_no = $slno + $per_page;
        $slno++;

        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start + 10;
        }

        if($customers_list['response']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $customers_list['response']['total_pages'];
        }

        if($customers_list['response']['total_records'] < $per_page) {
          $to_sl_no = ($slno+$customers_list['response']['total_records'])-1;
        }

        $customers = $customers_list['response']['customers'];
        $total_pages = $customers_list['response']['total_pages'];
        $total_records = $customers_list['response']['total_records'];
        $record_count = $customers_list['response']['total_records'];
      } else {
        $page_error = $customers_list['apierror'];
      }
    } else {
      $page_error = $customers_list['apierror'];
    }

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'customers' => $customers,
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
      'page_title' => 'Customers',
      'icon_name' => 'fa fa-smile-o',
    );

    // render template
    return array($this->template->render_view('cust-opbal-list', $template_vars), $controller_vars);
  }

  // validate form data
  private function _validate_form_data($form_data=array(),$is_update=false) {
    $errors = $cleaned_params = array();

    $cust_name = Utilities::clean_string($form_data['custName']);
    $action = Utilities::clean_string($form_data['action']);
    $amount = Utilities::clean_string($form_data['amount']);
    $bill_no = Utilities::clean_string($form_data['billNo']);
    $bill_date = Utilities::clean_string($form_data['billDate']);
    $credit_days = Utilities::clean_string($form_data['creditDays']);

    if($cust_name === '') {
      $errors['custName'] = 'Invalid customer name.';
    } else {
      $cleaned_params['custName'] = $cust_name;
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
      $errors['billNo'] = 'Invalid Bill No.';
    } else {
      $cleaned_params['billNo'] = $bill_no;
    }
    if($bill_date === '') {
      $errors['billDate'] = 'Invalid Bill Date';
    } else {
      $cleaned_params['billDate'] = $bill_date;
    }
    if(is_numeric($credit_days) && $credit_days>0) {
      $cleaned_params['creditDays'] = $credit_days;
    } else {
      $errors['creditDays'] = 'Invalid Credit Days';
    }

    if(count($errors)>0) {
      return array('status'=>false, 'errors'=>$errors);
    } else {
      return array('status'=>true, 'cleaned_params'=>$cleaned_params);
    }    
  }

  // validate opbal code.
  private function _validate_opbal_code($opbal_code='') {
    $cust_opbal_details = $this->cust_op_model->get_cust_opbal_details($opbal_code);
    if($cust_opbal_details['status']) {
      return $cust_opbal_details['opBalDetails'];
    } else {
      $this->flash_obj->set_flash_message('Invalid entry',1);
      Utilities::redirect('/fin/cust-opbal/list');        
    }
  }  

/*	public function supplierOpBalUpdateAction(Request $request) {
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
  }*/
}