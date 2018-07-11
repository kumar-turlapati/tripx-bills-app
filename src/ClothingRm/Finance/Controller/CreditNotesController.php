<?php 

namespace ClothingRm\Finance\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Finance\Model\CreditNote;

class CreditNotesController
{
	protected $views_path,$finmodel;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
    $this->cv_model = new CreditNote;
    $this->flash = new Flash;
	}

  # create credit note.
  public function cnCreateAction(Request $request) {
    $form_data = $form_errors = [];
    $api_error = '';

    $cn_types_a = Utilities::get_credit_note_types();

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations();

    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $validation = $this->_validate_form_data($form_data);
      if( $validation['status'] === false ) {
        $form_errors = $validation['errors'];
        $this->flash->set_flash_message('You have errors in this Form. Please fix them before saving this voucher.',1);
      } else {
        # hit api and process sales transaction.
        $cleaned_params = $validation['cleaned_params'];
        $api_response = $this->cv_model->create_credit_note($cleaned_params);
        if($api_response['status']) {
          $this->flash->set_flash_message('Credit note with Voucher No. <b>`'.$api_response['cnNo'].'`</b> created successfully.');
          Utilities::redirect('/fin/credit-note/create');
        } else {
          $page_error = $api_response['apierror'];
          $this->flash->set_flash_message($page_error,1);
        }
      }
    }

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Credit Notes',
      'icon_name' => 'fa fa-inr',
    );

    // template variables
    $template_vars = array(
      'cn_types' => array(''=>'Choose') + $cn_types_a,
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'api_error' => $api_error,
      'client_locations' => $client_locations,
    );    

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('create-credit-note', $template_vars), $controller_vars);     
  }

  # update credit note.
  public function cnUpdateAction(Request $request) {



    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Credit Notes',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('update-credit-note', $template_vars), $controller_vars);     
  }

  # delete credit note.
  public function cnDeleteAction(Request $request) {
    $cn_no = !is_null($request->get('cnNo')) ? Utilities::clean_string($request->get('cnNo')) : '';
    if($cn_no > 0) {
      $voucher_details = $this->cv_model->get_credit_note_details([], $cn_no);
      if($voucher_details['status'] === false) {
        $this->flash->set_flash_message('Invalid voucher number (or) voucher not exists',1);         
        Utilities::redirect('/fin/credit-notes');
      }
      $api_response = $this->cv_model->delete_credit_note($cn_no);
      $status = $api_response['status'];
      if($status === false) {
        $this->flash->set_flash_message($api_response['apierror'], 1);
      } else {
        $this->flash->set_flash_message('Voucher with No. <b>'.$voc_no. '</b> deleted successfully.');
      }
    } else {
      $this->flash->set_flash_message('Please choose a Voucher number to delete.');
    }

    Utilities::redirect('/fin/credit-notes'); 
  }

  # credit notes list action
  public function cnListAction(Request $request) {

    $cnotes_a = $search_params = [];
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
    $from_date = $request->get('fromDate')!==null ? Utilities::clean_string($request->get('fromDate')) : '01-'.date('m').'-'.date("Y");
    $to_date = $request->get('toDate')!==null ? Utilities::clean_string($request->get('toDate')) : date("d-m-Y");
    $location_code = $request->get('locationCode')!==null ? Utilities::clean_string($request->get('locationCode')) : '';
    $page_no = $request->get('pageNo')!==null ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = 100;

    $search_params = array(
      'fromDate' => $from_date,
      'toDate' => $to_date,
      'locationCode' => $location_code,
      'pageNo' => $page_no,
      'perPage' => $per_page,
    );

    $api_response = $this->cv_model->get_credit_notes($search_params);
    if($api_response['status']) {
      if(count($api_response['data']['cnotes'])>0) {
          $slno = Utilities::get_slno_start(count($api_response['data']['cnotes']),$per_page,$page_no);
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
          $cnotes_a = $api_response['data']['cnotes'];
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
      'cnotes' => $cnotes_a,
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
      'page_title' => 'Finance Management - Credit Notes',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('credit-notes-list', $template_vars), $controller_vars);    
  }

  private function _validate_form_data($form_data=array()) {
    $form_errors = $cleaned_params = [];
    $cn_types_a = array_keys(Utilities::get_credit_note_types());

    $location_code = Utilities::clean_string($form_data['locationCode']);
    $cn_type = Utilities::clean_string($form_data['cnType']);
    $ref_no = Utilities::clean_string($form_data['refNo']);
    $cn_value = Utilities::clean_string($form_data['cnValue']);

    if( isset($location_code) && ctype_alnum($location_code) ) {
      $cleaned_params['locationCode'] = $location_code;
    } else {
      $form_errors['locationCode'] = 'Invalid location code.';
    }

    if(in_array($cn_type, $cn_types_a)) {
      $cleaned_params['cnType'] = $cn_type;
      # validate ref. no here itself.
      if($cn_type === 'lo') {
        if(is_numeric($ref_no) && strlen($ref_no)<=6) {
          $cleaned_params['refNo'] = $ref_no;
        } else {
          $form_errors['refNo'] = 'Invalid Card No. Must be less than or equal to 6 digits.';
        }
      } elseif($cn_type === 'ge') {
        if(is_numeric($ref_no) && strlen($ref_no)===10) {
          $cleaned_params['refNo'] = $ref_no;
        } else {
          $form_errors['refNo'] = 'Invalid Mobile No. Must be less 10 digits.';
        }
      }
    } else {
      $form_errors['cnType'] = 'Invalid credit note type.';
    }

    if(is_numeric($cn_value) && $cn_value>0) {
      $cleaned_params['cnValue'] = $cn_value;
    } else {
      $form_errors['cnValue'] = 'Invalid credit note value.';
    }

    if(count($form_errors)>0) {
      return array('status'=>false, 'errors'=>$form_errors);
    } else {
      return array('status'=>true, 'cleaned_params'=>$cleaned_params);
    }
  }
}

/*  private function _map_voucher_data($form_data) {
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
  public function paymentCreateAction(Request $request) {

    $page_error = $page_success = $bank_code = '';
    $submitted_data = $form_errors = array();
    $parties = array(''=>'Choose');

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all());
      $status = $validate_form['status'];
      if($status) {
        $flash = new Flash();
        $fin_model = new Finance();
        $form_data = $validate_form['cleaned_params'];
        $result = $fin_model->create_payment_voucher($this->_map_voucher_data($form_data));
        // dump($result);
        // exit;
        if($result['status']===true) {
          $message = 'Payment voucher created successfully with Voucher No. ` '.$result['vocNo'].' `';
          $flash->set_flash_message($message);
        } else {
          $message = 'An error occurred while creating payment voucher.';
          $flash->set_flash_message($message,1);          
        }
        Utilities::redirect('/fin/payment-voucher/create');
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
    $banks_list = $this->finmodel->banks_list();
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
  public function paymentUpdateAction(Request $request) {

    $page_error = $page_success = $bank_code = '';
    $submitted_data = $form_errors = array();
    $parties = array(''=>'Choose');
    $voc_no = 0;

    $flash = new Flash();
    $fin_model = new Finance();

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all());
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $fin_model->update_payment_voucher($this->_map_voucher_data($form_data),$form_data['vocNo']);
        if($result['status']===true) {
          $message = 'Payment voucher no. `'.$form_data['vocNo'].'` updated successfully';
          $flash->set_flash_message($message);
        } else {
          $message = 'An error occurred while updating payment voucher.';
          $flash->set_flash_message($message,1);          
        }
        Utilities::redirect('/fin/payment-vouchers');
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $request->request->all();
      }
    } elseif(!is_null($request->get('vocNo'))) {
      $voc_no = $request->get('vocNo');
      $voucher_details = $fin_model->get_payment_voucher_details($voc_no);
      if($voucher_details['status']===false) {
        $flash->set_flash_message('Invalid voucher number (or) voucher not exists',1);         
        Utilities::redirect('/fin/payment-vouchers');
      } else {
        $submitted_data = $voucher_details['data'];
      }
    } else {
      $flash->set_flash_message('Invalid voucher number (or) voucher not exists',1);         
      Utilities::redirect('/fin/payment-vouchers');
    }

    # get party names
    $supplier_api_call = new Supplier;
    $supp_params['pagination'] = 'no';
    $suppliers = $supplier_api_call->get_suppliers(0,0,$supp_params);
    if($suppliers['status']) {
        $parties += $suppliers['suppliers'];
    }

    # get bank names
    $banks_list = $this->finmodel->banks_list();
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
*/
