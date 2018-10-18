<?php 

namespace ClothingRm\Finance\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Finance\Model\Finance;
use ClothingRm\Suppliers\Model\Supplier;

class PaymentsController
{
	protected $views_path,$fin_model;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
    $this->fin_model = new Finance;
    $this->flash = new Flash;
	}

  // Payments create action.
	public function paymentCreateAction(Request $request) {

    $page_error = $page_success = $bank_code = '';
    $submitted_data = $form_errors = array();
    $parties = array(''=>'Choose');

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all());
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $this->fin_model->create_payment_voucher($this->_map_voucher_data($form_data));
        if($result['status']) {
          $message = 'Payment voucher created successfully with Voucher No. ` '.$result['vocNo'].' `';
          $this->flash->set_flash_message($message);
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error,1);
          $submitted_data = $form_data;
        }
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $request->request->all();
      }
    }

    # get party names
    $supplier_api_call = new Supplier;
    $supp_params['pagination'] = 'no';
    $suppliers = $supplier_api_call->get_suppliers(0,0,$supp_params);
    if($suppliers['status']) {
      $parties += $suppliers['suppliers'];
    }
    # get bank names
    $banks_list = $this->fin_model->banks_list();
    if($banks_list['status']===false) {
      $bank_names = array(''=>'Choose');
    } else {
      $bank_names = array(''=>'Choose')+
                    Utilities::process_key_value_pairs($banks_list['banks'],'bankCode','bankName');
    }

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_errors' => $form_errors,
      'submitted_data' => $submitted_data,
      'parties' => $parties,
      'payment_methods' => array(''=>'Choose')+Utilities::get_fin_payment_methods(),
      'bank_names' => $bank_names,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Payments',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('payment-voucher-create', $template_vars), $controller_vars);
	}

  // Payments update action.
  public function paymentUpdateAction(Request $request) {

    $page_error = $page_success = $bank_code = '';
    $submitted_data = $form_errors = array();
    $parties = array(''=>'Choose');
    $voc_no = 0;

    if(!is_null($request->get('vocNo'))) {
      $voc_no = $request->get('vocNo');
      $voucher_details = $this->fin_model->get_payment_voucher_details($voc_no);
      if($voucher_details['status']===false) {
        $this->flash->set_flash_message('Invalid voucher number (or) voucher does not exists',1);         
        Utilities::redirect('/fin/payment-vouchers');
      } else {
        $submitted_data = $voucher_details['data'];
      }
    } else {
      $flash->set_flash_message('Invalid voucher number (or) voucher does not exists',1);         
      Utilities::redirect('/fin/payment-vouchers');
    }    

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all());
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $this->fin_model->update_payment_voucher($this->_map_voucher_data($form_data),$form_data['vocNo']);
        if($result['status']) {
          $message = 'Payment voucher no. `'.$form_data['vocNo'].'` updated successfully';
          $this->flash->set_flash_message($message);
        } else {
          $this->flash->set_flash_message($result['apierror'],1);
        }
        Utilities::redirect('/fin/payment-voucher/update/'.$voc_no);
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $request->request->all();
      }
    }

    # get party names
    $supplier_api_call = new Supplier;
    $supp_params['pagination'] = 'no';
    $suppliers = $supplier_api_call->get_suppliers(0,0,$supp_params);
    if($suppliers['status']) {
        $parties += $suppliers['suppliers'];
    }

    # get bank names
    $banks_list = $this->fin_model->banks_list();
    if($banks_list['status']===false) {
      $bank_names = array(''=>'Choose');
    } else {
      $bank_names = array(''=>'Choose')+
                    Utilities::process_key_value_pairs($banks_list['banks'],'bankCode','bankName');
    }    

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_errors' => $form_errors,
      'submitted_data' => $submitted_data,
      'parties' => $parties,
      'payment_methods' => array(''=>'Choose')+Utilities::get_fin_payment_methods(),
      'bank_names' => $bank_names,
      'voc_no' => $voc_no,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Payments',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('payment-voucher-update', $template_vars), $controller_vars);
  }

  // Payments delete action.
  public function paymentDeleteAction(Request $request) {
    $voc_no = $request->get('vocNo');
    $voucher_details = $this->fin_model->get_payment_voucher_details($voc_no);
    if($voucher_details['status']===false) {
      $flash->set_flash_message('Invalid voucher number (or) voucher not exists',1);         
    # delete voucher.
    } else {
      $api_response = $this->fin_model->delete_payment_voucher($voc_no);
      $status = $api_response['status'];
      if($status===false) {
        $this->flash->set_flash_message('Unable to delete the voucher.', 1);
      } else {
        $this->flash->set_flash_message('Voucher with No. <b>'.$voc_no. '</b> deleted successfully');
      }
    }

    Utilities::redirect('/fin/payment-vouchers');
  }

  // payments list action
  public function paymentsListAction(Request $request) {

    $parties = $vouchers = $search_params = $vouchers_a = array();
    $party_code = $bank_code = $page_error = '';
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    // parse request parameters.
    $from_date = $request->get('fromDate')!==null?Utilities::clean_string($request->get('fromDate')):'01-'.date('m').'-'.date("Y");
    $to_date = $request->get('toDate')!==null?Utilities::clean_string($request->get('toDate')):date("d-m-Y");
    $party_code = $request->get('partyCode')!==null?Utilities::clean_string($request->get('partyCode')):'';
    $bank_code = $request->get('bankCode')!==null?Utilities::clean_string($request->get('bankCode')):'';
    $page_no = $request->get('pageNo')!==null?Utilities::clean_string($request->get('pageNo')):1;
    $per_page = 100;

    $search_params = array(
      'fromDate' => $from_date,
      'toDate' => $to_date,
      'partyCode' => $party_code,
      'bankCode' => $bank_code,
      'pageNo' => $page_no,
      'perPage' => $per_page,
    );

    // initiate finance model
    $api_response = $this->fin_model->get_payment_vouchers_list($search_params);
    if($api_response['status']===true) {
      if(count($api_response['data']['response']['payments'])>0) {
          $slno = Utilities::get_slno_start(count($api_response['data']['response']['payments']),$per_page,$page_no);
          $to_sl_no = $slno+$per_page;
          $slno++;
          if($page_no<=3) {
            $page_links_to_start = 1;
            $page_links_to_end = 10;
          } else {
            $page_links_to_start = $page_no-3;
            $page_links_to_end = $page_links_to_start+10;            
          }
          if($api_response['data']['response']['total_pages']<$page_links_to_end) {
            $page_links_to_end = $api_response['data']['response']['total_pages'];
          }
          if($api_response['data']['response']['this_page'] < $per_page) {
            $to_sl_no = ($slno+$api_response['data']['response']['this_page'])-1;
          }

          $vouchers_a = $api_response['data']['response']['payments'];
          $total_pages = $api_response['data']['response']['total_pages'];
          $total_records = $api_response['data']['response']['total_records'];
          $record_count = $api_response['data']['response']['this_page'];
      } else {
        $page_error = $api_response['apierror'];
      }
    } else {
      $page_error = $api_response['apierror'];
    }

    // get party names
    $supplier_api_call = new Supplier;
    $supp_params['pagination'] = 'no';
    $suppliers = $supplier_api_call->get_suppliers(0,0,$supp_params);
    if($suppliers['status']) {
        $parties += $suppliers['suppliers'];
    }

    // get bank names
    $banks_list = $this->fin_model->banks_list();
    if($banks_list['status']===false) {
      $bank_names = array(''=>'Choose');
    } else {
      $bank_names = array(''=>'Choose')+
                    Utilities::process_key_value_pairs($banks_list['banks'],'bankCode','bankName');
    }    

     // prepare form variables.
    $template_vars = array(
      'parties' => array(''=>'Party name')+$parties,
      'bank_names' => array(''=>'Bank name')+$bank_names,
      'party_code' => $party_code,
      'bank_code' => $bank_code,
      'page_error' => $page_error,
      'vouchers' => $vouchers_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Payments',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('payment-vouchers-list', $template_vars), $controller_vars);    
  }

  /*********************************** validate form data ******************************************/
  private function _validate_form_data($form_data=array()) {
    $errors = $cleaned_params = array();
    // var_dump($form_data);

    $tran_date = Utilities::clean_string($form_data['tranDate']);
    $party_code = Utilities::clean_string($form_data['partyCode']);
    $bill_no = Utilities::clean_string($form_data['billNo']);
    $payment_method = Utilities::clean_string($form_data['paymentMode']);
    $amount = Utilities::clean_string($form_data['amount']);
    $narration = Utilities::clean_string($form_data['narration']);
    $bank_code = Utilities::clean_string($form_data['bankCode']);
    $ref_no = Utilities::clean_string($form_data['refNo']);
    $ref_date = Utilities::clean_string($form_data['refDate']);

    if($party_code===''&&($payment_method=='b'||$payment_method==='p')) {
      $errors['partyCode'] = 'Party name is mandatory';
    } else {
      $cleaned_params['partyCode'] = $party_code;
    }
    if($bill_no==''&&($payment_method=='b'||$payment_method==='p')) {
      $errors['billNo'] = 'Bill no. is mandatory';
    } else {
      $cleaned_params['billNo'] = $bill_no;
    }
    if(is_numeric($amount) && $amount > 0) {
      $cleaned_params['amount'] = $amount;
    } else {
      $errors['amount'] = 'Invalid amount';
    }

    if(isset($form_data['vocNo']) && is_numeric($form_data['vocNo'])) {
      $cleaned_params['vocNo'] = $form_data['vocNo'];
    } else {
      $cleaned_params['vocNo'] = 0;
    }

    if($payment_method==='b' || $payment_method==='p') {
      if($bank_code==='') {
        $errors['bankCode'] = 'Bank name is required for Bank or PDC payment modes';
      } else {
        $cleaned_params['bankCode'] = $bank_code;
      }
      if($ref_no==='') {
        $errors['refNo'] = 'Ref. no is required for Bank or PDC payment modes';
      } else {
        $cleaned_params['refNo'] = $ref_no;
      }
      if(strtotime($ref_date)<=time() && $payment_method==='p') {
        $errors['refDate'] = 'Ref. date should be greater than today for PDC';
      } else {
        $cleaned_params['refDate'] = $ref_date;
      }
    }
    // var_dump($errors);
    if(count($errors)>0) {
      return array('status'=>false, 'errors'=>$errors);
    } else {
      $cleaned_params['tranDate'] = $tran_date;
      $cleaned_params['narration'] = $narration;
      $cleaned_params['paymentMode'] = $payment_method;
      return array('status'=>true, 'cleaned_params'=>$cleaned_params);
    }
  }

  private function _map_voucher_data($form_data) {
    $data_array = array();
    foreach($form_data as $key=>$value) {
      if($key==='paymentMode') {
        switch($form_data[$key]) {
          case 'b':
            $data_array['paymentMode'] = 'bank';
            $data_array['refNo'] = $form_data['refNo'];
            $data_array['refDate'] = $form_data['refDate'];
            $data_array['bankCode'] = $form_data['bankCode'];
            break;
          case 'c':
            $data_array['paymentMode'] = 'cash';
            $data_array['refNo'] = '';
            $data_array['refDate'] = '0000-00-00';            
            break;
          case 'p':
            $data_array['paymentMode'] = 'bank';
            $data_array['isPdc'] = true;
            $data_array['refNo'] = $form_data['refNo'];
            $data_array['refDate'] = $form_data['refDate'];
            $data_array['bankCode'] = $form_data['bankCode'];
            break;          
        }
      } elseif($key!=='vocNo') {
        $data_array[$key] = $value;
      }
    }
    return $data_array;
  }
}