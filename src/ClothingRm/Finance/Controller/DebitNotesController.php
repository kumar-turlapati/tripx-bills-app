<?php 

namespace ClothingRm\Finance\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Suppliers\Model\Supplier;
use ClothingRm\Finance\Model\DebitNote;
use ClothingRm\Inward\Model\Inward;

class DebitNotesController {

	protected $template, $dn_model, $flash, $inward_model;

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->dn_model = new DebitNote;
    $this->flash = new Flash;
    $this->inward_model = new Inward;
    $this->supplier_model = new Supplier;       
	}

  // create debit note.
  public function dnCreateAction(Request $request) {
    $form_data = $form_errors = $suppliers_a = [];
    $purchase_details = [];

    $api_error = '';

    // get purchase details if purchase code is available.
    if( !is_null($request->get('pc')) ) {
      $purchase_code = Utilities::clean_string($request->get('pc'));
      $purchase_response = $this->inward_model->get_purchase_details($purchase_code);
      if($purchase_response['status']) {
        $purchase_details = $purchase_response['purchaseDetails'];
      } else {
        $this->flash->set_flash_message($purchase_response['apierror'], 1);
        Utilities::redirect('/purchase-return/entry');
      }
    } else {
      $this->flash->set_flash_message('Invalid parameter in Debit Note.', 1);
      Utilities::redirect('/purchase-return/register');
    }

    // get suppliers data
    $suppliers = $this->supplier_model->get_suppliers(0,0,[]);
    if($suppliers['status']) {
      $suppliers_a += $suppliers['suppliers'];
    }  

    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $validation = $this->_validate_form_data($form_data, $purchase_details);
      if( $validation['status'] === false ) {
        $form_errors = $validation['errors'];
        $this->flash->set_flash_message('You have errors in this Form. Please fix them before saving this voucher.',1);
      } else {
        # hit api and process sales transaction.
        $cleaned_params = $validation['cleaned_params'];
        $api_response = $this->dn_model->create_debit_note($cleaned_params);
        if($api_response['status']) {
          $this->flash->set_flash_message('Debit note with Voucher No. <b>`'.$api_response['dnNo'].'`</b> created successfully.');
          Utilities::redirect('/fin/debit-note/create');
        } else {
          $page_error = $api_response['apierror'];
          $this->flash->set_flash_message($page_error,1);
        }
      }

    } else {
      $form_data['supplierCode'] = $purchase_details['supplierCode'];
      $form_data['billNo'] = $purchase_details['billNo'];
      $form_data['amount'] = $purchase_details['netPay'];
    }

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Debit Notes',
      'icon_name' => 'fa fa-minus-square',
    );

    // template variables
    $template_vars = array(
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'api_error' => $api_error,
      'suppliers_a' => $suppliers_a,
    );    

    // render template
    return array($this->template->render_view('create-debit-note', $template_vars), $controller_vars);     
  }

  // update debit note.
  public function dnUpdateAction(Request $request) {



    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Credit Notes',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('update-credit-note', $template_vars), $controller_vars);     
  }

  // delete debit note.
  public function dnDeleteAction(Request $request) {
    $dn_code = !is_null($request->get('dnCode')) ? Utilities::clean_string($request->get('dnCode')) : '';
    if($dn_code !== '') {
      $voucher_details = $this->dn_model->get_debit_note_details($dn_code);
      if($voucher_details['status'] === false) {
        $this->flash->set_flash_message('Invalid voucher number (or) voucher does not exists.',1);
        Utilities::redirect('/fin/debit-notes');
      }
      $api_response = $this->dn_model->delete_debit_note($dn_code);
      $status = $api_response['status'];
      if($status === false) {
        $this->flash->set_flash_message($api_response['apierror'], 1);
      } else {
        $this->flash->set_flash_message('Voucher with code <b>'.$dn_code. '</b> deleted successfully.');
      }
    } else {
      $this->flash->set_flash_message('Please choose a Voucher to delete.');
    }
    Utilities::redirect('/fin/debit-notes');
  }

  // debit notes list action
  public function dnListAction(Request $request) {

    $dnotes_a = $search_params = [];
    $page_error = '';
    
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
    }

    # parse request parameters.
    $from_date = $request->get('fromDate')!== null ? Utilities::clean_string($request->get('fromDate')) : '01-'.date('m').'-'.date("Y");
    $to_date = $request->get('toDate')!== null ? Utilities::clean_string($request->get('toDate')) : date("d-m-Y");
    $location_code = $request->get('locationCode')!== null ? Utilities::clean_string($request->get('locationCode')) : '';
    $page_no = $request->get('pageNo')!== null ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = 100;

    $search_params = array(
      'fromDate' => $from_date,
      'toDate' => $to_date,
      'locationCode' => $location_code,
      'pageNo' => $page_no,
      'perPage' => $per_page,
    );

    $api_response = $this->dn_model->get_debit_notes($search_params);
    if($api_response['status']) {
      if(count($api_response['data']['dnotes'])>0) {
          $slno = Utilities::get_slno_start(count($api_response['data']['dnotes']),$per_page,$page_no);
          $to_sl_no = $slno+$per_page;
          $slno++;
          if($page_no <= 3) {
            $page_links_to_start = 1;
            $page_links_to_end = 10;
          } else {
            $page_links_to_start = $page_no-3;
            $page_links_to_end = $page_links_to_start+10;            
          }
          if($api_response['data']['total_pages']<$page_links_to_end) {
            $page_links_to_end = $api_response['data']['total_pages'];
          }
          if($api_response['data']['this_page'] < $per_page) {
            $to_sl_no = ($slno+$api_response['data']['this_page'])-1;
          }
          $dnotes_a = $api_response['data']['dnotes'];
          $total_pages = $api_response['data']['total_pages'];
          $total_records = $api_response['data']['total_records'];
          $record_count = $api_response['data']['this_page'];
      } else {
        $page_error = $api_response['apierror'];
      }
    } else {
      $page_error = $api_response['apierror'];
    }

    // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'dnotes' => $dnotes_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
      'client_locations' => ['' => 'All Stores'] + $client_locations,
      'location_ids' => $location_ids,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Debit Notes',
      'icon_name' => 'fa fa-minus-square',
    );

    // render template
    return array($this->template->render_view('debit-notes-list', $template_vars), $controller_vars);    
  }

  private function _validate_form_data($form_data=[], $po_details=[]) {
    $form_errors = $cleaned_params = [];

    $supplier_code = Utilities::clean_string($form_data['supplierCode']);
    $bill_no = Utilities::clean_string($form_data['billNo']);
    $amount = Utilities::clean_string($form_data['amount']);

    $po_bill_no = $po_details['billNo'];
    $po_netpay = $po_details['netPay'];
    $po_supplier_code = $po_details['supplierCode'];

    if($supplier_code !== '' && $supplier_code === $po_supplier_code) {
      $cleaned_params['supplierCode'] = $supplier_code;
    } else {
      $form_errors['supplierCode'] = 'Supplier name is required.';
    }
    if($bill_no === $po_bill_no) {
      $cleaned_params['billNo'] = $bill_no;
    } else {
      $form_errors['billNo'] = 'Invalid bill number.';
    }    
    if(is_numeric($amount) && $amount < 0) {
      $cleaned_params['amount'] = $amount;
    } elseif(is_numeric($amount) && $amount > 0 && $amount <= $po_netpay) {
      $cleaned_params['amount'] = $amount;
    } else {
      $form_errors['amount'] = 'Invalid amount.';
    }

    $cleaned_params['dnType'] = 'ma';
    $cleaned_params['locationCode'] = $po_details['locationID'];
    $cleaned_params['purchaseCode'] = $po_details['purchaseCode'];

    if(count($form_errors)>0) {
      return array('status'=>false, 'errors'=>$form_errors);
    } else {
      return array('status'=>true, 'cleaned_params'=>$cleaned_params);
    }
  }
}