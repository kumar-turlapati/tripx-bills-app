<?php 

namespace ClothingRm\Finance\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Finance\Model\Finance;
use ClothingRm\Customers\Model\Customers;

class ReceiptsController
{
	protected $template,$fin_model,$cust_model,$flash;

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->fin_model = new Finance;
    $this->cust_model = new Customers;
    $this->flash = new Flash;
	}

  // receipts create action.
  public function receiptCreateAction(Request $request) {
    $page_error = $page_success = '';
    $submitted_data = $form_errors = [];
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $validate_form = $this->_validate_form_data($request->request->all());
      $status = $validate_form['status'];
      if($status) {
        $mapped_data = $this->_map_voucher_data($validate_form['cleaned_params']);
        $result = $this->fin_model->create_receipt_voucher($mapped_data);
        if($result['status']) {
          $message = 'Receipt voucher created successfully with Voucher No. ` '.$result['vocNo'].' `';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/fin/receipt-voucher/create');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error,1);
          $submitted_data = $form_data;
        }
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $form_data;
      }
    }

    // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_errors' => $form_errors,
      'submitted_data' => $submitted_data,
      'payment_methods' => array(''=>'Choose') + Utilities::get_fin_payment_methods(),
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Receipts',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('receipt-voucher-create', $template_vars), $controller_vars);
  }

  // receipts update action.
  public function receiptUpdateAction(Request $request) {
    $page_error = $page_success = '';
    $submitted_data = $form_errors = $form_data = [];
    if( is_null($request->get('vocNo')) && is_numeric($request->get('vocNo'))) {
      $this->flash->set_flash_message('Invalid voucher number (or) voucher not exists',1);         
      Utilities::redirect('/fin/receipt-vouchers');
    } else {
      $voc_no = Utilities::clean_string($request->get('vocNo'));
    }

    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $validate_form = $this->_validate_form_data($request->request->all());
      $status = $validate_form['status'];
      if($status) {
        $mapped_data = $this->_map_voucher_data($validate_form['cleaned_params']);
        $result = $this->fin_model->update_receipt_voucher($mapped_data, $voc_no);
        if($result['status']) {
          $message = 'Receipt voucher update successfully.';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/fin/receipt-vouchers');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error,1);
          $submitted_data = $form_data;
        }
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $form_data;
      }
    } elseif(!is_null($request->get('vocNo'))) {
      $voc_no = $request->get('vocNo');
      $voucher_details = $this->fin_model->get_receipt_voucher_details($voc_no);
      if($voucher_details['status']===false) {
        $this->flash->set_flash_message('Invalid voucher number (or) voucher not exists',1);         
        Utilities::redirect('/fin/receipt-vouchers');
      } else {
        $form_data = $voucher_details['data'];
      }
    }

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_errors' => $form_errors,
      'submitted_data' => $form_data,
      'payment_methods' => array(''=>'Choose') +  Utilities::get_fin_payment_methods(),
      'voc_no' => $voc_no,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Receipts',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('receipt-voucher-update', $template_vars), $controller_vars);
  }

  // receipts list action
  public function receiptsListAction(Request $request) {

    $parties = $vouchers = $search_params = $vouchers_a = $customers = [];
    $party_code = $bank_code = $page_error = '';
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    // parse request parameters.
    $from_date = $request->get('fromDate')!==null ? Utilities::clean_string($request->get('fromDate')) : date("01-m-Y");
    $to_date = $request->get('toDate')!==null ? Utilities::clean_string($request->get('toDate')) : date("d-m-Y");
    $party_name = $request->get('partyName')!==null ? Utilities::clean_string($request->get('partyName')):'';
    $page_no = $request->get('pageNo')!==null ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = 100;

    $search_params = array(
      'fromDate' => $from_date,
      'toDate' => $to_date,
      'partyName' => $party_name,
      'pageNo' => $page_no,
      'perPage' => $per_page,
    );

    // initiate finance model
    $api_response = $this->fin_model->get_receipt_vouchers_list($search_params);
    if($api_response['status']===true) {
      if(count($api_response['data']['response']['receipts'])>0) {
          $slno = Utilities::get_slno_start(count($api_response['data']['response']['receipts']),$per_page,$page_no);
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

          $vouchers_a = $api_response['data']['response']['receipts'];
          $total_pages = $api_response['data']['response']['total_pages'];
          $total_records = $api_response['data']['response']['total_records'];
          $record_count = $api_response['data']['response']['this_page'];
      } else {
        $page_error = $api_response['apierror'];
      }
    } else {
      $page_error = $api_response['apierror'];
    }

     // prepare form variables.
    $template_vars = array(
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
      'page_title' => 'Finance Management - Receipts',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('receipt-vouchers-list', $template_vars), $controller_vars);    
  }

  // Receipts delete action.
  public function receiptDeleteAction(Request $request) {
    $voc_no = $request->get('vocNo');
    $voucher_details = $this->fin_model->get_receipt_voucher_details($voc_no);
    if($voucher_details['status']===false) {
      $flash->set_flash_message('Invalid voucher number (or) voucher not exists',1);         
    # delete voucher.
    } else {
      $api_response = $this->fin_model->delete_receipt_voucher($voc_no);
      $status = $api_response['status'];
      if($status===false) {
        $this->flash->set_flash_message('Unable to delete the voucher.', 1);
      } else {
        $this->flash->set_flash_message('Voucher with No. <b>'.$voc_no. '</b> deleted successfully');
      }
    }
    Utilities::redirect('/fin/receipt-vouchers');
  }  

  // receivables list ason action
  public function receivablesListAsonAction(Request $request) {

    $receivables = array();

     // initiate finance model
    $fin_model = new Finance();
    $api_response = $$this->fin_model->get_receivables_ason();

    if($api_response['status']===true) {
      $receivables = $api_response['receivables'];
    } else {
      $page_error = $api_response['apierror'];
    }

     // prepare form variables.
    $template_vars = array(
      'receivables' => $receivables,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Receivables',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('receivables-list-ason', $template_vars), $controller_vars);    
  }

  /*********************************** validate form data ******************************************/
  private function _validate_form_data($form_data=array()) {
    $errors = $cleaned_params = array();
    // var_dump($form_data);

    $tran_date = Utilities::clean_string($form_data['tranDate']);
    $party_name = Utilities::clean_string($form_data['partyName']);
    $bill_no = isset($form_data['billNo']) ? Utilities::clean_string($form_data['billNo']) : '';
    $payment_method = Utilities::clean_string($form_data['paymentMode']);
    $amount = Utilities::clean_string($form_data['amount']);
    $narration = Utilities::clean_string($form_data['narration']);
    $bank_name = Utilities::clean_string($form_data['bankName']);
    $ref_no = Utilities::clean_string($form_data['refNo']);
    $ref_date = Utilities::clean_string($form_data['refDate']);

    if($party_name === '') {
      $errors['partyName'] = 'Party name is mandatory';
    } else {
      $cleaned_params['partyName'] = $party_name;
    }
    if($bill_no === '') {
      $errors['billNo'] = 'Bill no. is mandatory';
    } else {
      $cleaned_params['billNo'] = $bill_no;
    }
    if(!is_numeric($amount)) {
      $errors['amount'] = 'Invalid amount';
    } else {
      $cleaned_params['amount'] = $amount;
    }
    if($payment_method ==='b' || $payment_method === 'p') {
      if($bank_name === '') {
        $errors['bankName'] = 'Bank name is required for Bank or PDC payment modes';
      } else {
        $cleaned_params['bankName'] = $bank_name;
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

  private function _map_voucher_data($form_data = []) {
    $data_array = array();
    foreach($form_data as $key=>$value) {
      if($key === 'paymentMode') {
        switch($form_data[$key]) {
          case 'b':
            $data_array['paymentMode'] = 'bank';
            $data_array['refNo'] = $form_data['refNo'];
            $data_array['refDate'] = $form_data['refDate'];
            $data_array['bankName'] = $form_data['bankName'];
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
            $data_array['bankName'] = $form_data['bankName'];
            break;          
        }
      } elseif($key !== 'vocNo') {
        $data_array[$key] = $value;
      }
    }
    return $data_array;
  }
}