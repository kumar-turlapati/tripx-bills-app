<?php 

namespace ClothingRm\Finance\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Finance\Model\Finance;

class BanksController
{
	protected $views_path;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
	}

	public function bankCreateAction(Request $request) {

    $page_error = $page_success = $bank_code = '';
    $submitted_data = $form_errors = array();

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all());
      $status = $validate_form['status'];
      if($status) {
        $flash = new Flash();
        $form_data = $validate_form['cleaned_params'];
        $fin_model = new Finance();
        $result = $fin_model->create_bank($form_data);
        // dump($result);
        // exit;
        if($result['status']) {
          $message = 'Bank name added successfully with code ` '.$result['bankCode'].' `';
          $flash->set_flash_message($message);
        } else {
          $message = 'An error occurred while creating bank.';
          $flash->set_flash_message($message,1);          
        }
        Utilities::redirect('/fin/bank/list');
      } else {
        $form_errors = $validate_form['errors'];
      }
    }

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_errors' => $form_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Bank Accounts',
      'icon_name' => 'fa fa-university',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('bank-create', $template_vars), $controller_vars);
	}

  public function bankUpdateAction(Request $request) {

    $page_error = $page_success = $bank_code = '';
    $submitted_data = $form_errors = array();
    $flash = new Flash();
    $fin_model = new Finance();    

    if(count($request->request->all()) > 0) {
      $validate_form = $this->_validate_form_data($request->request->all());
      $status = $validate_form['status'];
      if($status) {
        $form_data = $validate_form['cleaned_params'];
        $result = $fin_model->update_bank($form_data,$form_data['bank_code']);
        if($result['status']===true) {
          $message = 'Bank details were updated successfully';
          $flash->set_flash_message($message);
          Utilities::redirect('/fin/bank/list');
        } else {
          $message = 'An error occurred while updating bank details.';
          $flash->set_flash_message($message,1);
          Utilities::redirect('/fin/bank/list');          
        }
      } else {
        $form_errors = $validate_form['errors'];
        $submitted_data = $request->request->all();
      }
    } else {
      $bank_code = Utilities::clean_string($request->get('bankCode'));
      $bank_details = $fin_model->get_bank_details($bank_code);
      if($bank_details['status']) {
        $submitted_data = $bank_details['bankDetails'];
      } else {
        $flash->set_flash_message('Invalid bank code',1);
        Utilities::redirect('/fin/bank/list');        
      }
    }

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_errors' => $form_errors,
      'submitted_data' => $submitted_data,
      'bank_code' => $bank_code,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Bank Accounts',
      'icon_name' => 'fa fa-university',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('bank-update', $template_vars), $controller_vars);
  }  

  public function banksListAction(Request $request) {
    $banks = array();
    
    $fin_model = new Finance();
    $result = $fin_model->banks_list();
    if($result['status']) {
      $banks = $result['banks'];
    }

     // prepare form variables.
    $template_vars = array(
      'banks' => $banks,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Banks',
      'icon_name' => 'fa fa-university',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('banks-list', $template_vars), $controller_vars);    

  }

  /*********************************** validate form data ********************************/
  private function _validate_form_data($form_data=array()) {
    $errors = $cleaned_params = array();

    $account_name = $form_data['accountName'];
    $bank_name = $form_data['bankName'];
    $address = $form_data['address'];
    $account_number = $form_data['accountNo'];
    $ifsc_code = $form_data['ifscCode'];
    $phone = $form_data['phone'];
    $bank_code = (isset($form_data['bankCode'])?$form_data['bankCode']:'');

    if($account_name == '') {
      $errors['accountName'] = 'Account name is required.';
    } else {
      $cleaned_params['accountName'] = $account_name;
    }

    if($bank_name == '') {
      $errors['bankName'] = 'Bank name is required.';
    } else {
      $cleaned_params['bankName'] = $bank_name;
    }

    if($account_number === '' || !is_numeric($account_number)) {
      $errors['accountNo'] = 'Invalid Account number.';
    } else {
      $cleaned_params['accountNo'] = $account_number;      
    }

    if($phone !== '' && !is_numeric($phone)) {
      $errors['phone'] = 'Phone number should contain digits only.';
    } else {
      $cleaned_params['phone'] = $phone;
    }

    $cleaned_params['bank_code'] = $bank_code;
    $cleaned_params['ifscCode'] = $ifsc_code;
    $cleaned_params['address'] = $address;

    if(count($errors)>0) {
      return array('status'=>false, 'errors'=>$errors);
    } else {
      return array('status'=>true, 'cleaned_params'=>$cleaned_params);
    }
  }


}