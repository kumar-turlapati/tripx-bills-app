<?php

namespace ClothingRm\AdminOptions\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Sales\Model\Sales;
use ClothingRm\Purchases\Model\Purchases;
use ClothingRm\Inventory\Model\Inventory;
use User\Model\User;

class AdminOptionsController
{
	protected $views_path, $template, $sales_model, $purch_model, $flash;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
		$this->template = new Template($this->views_path);
		$this->sales_model = new Sales();
		$this->purch_model = new Purchases();
    $this->inv_model = new Inventory();
    $this->user_model = new User();
		$this->flash = new Flash();
	}

  # ask for bill no
	public function askForBillNo(Request $request) { 
		
		$page_error = $page_title = $bill_no = '';

    if(count($request->request->all()) > 0) {
    	$bill_no = Utilities::clean_string($request->get('editBillNo'));
    	$bill_type = Utilities::clean_string($request->get('billType'));
    	if($bill_type === 'sale') {
    		$bill_details = $this->sales_model->get_sales_details($bill_no,true);
    		if($bill_details['status']) {
    			Utilities::redirect('/admin-options/edit-sales-bill?billNo='.$bill_no);
    		} else {
    			$page_error = 'Invalid Bill No.';
    		}    		
    	} elseif($bill_type === 'purc') {
    		$bill_details = $this->purch_model->get_purchase_details($bill_no, true);
    		if($bill_details['status']) {
    			Utilities::redirect('/admin-options/edit-po?poNo='.$bill_no);
    		} else {
          $this->flash->set_flash_message('Invalid PO No. (or) PO does not exist.',1);
          Utilities::redirect('/admin-options/enter-bill-no?billType=purc');
    		}
    	}
    }

    # check for filter variables.
    if(!is_null($request->get('billType')) && 
    	  $request->get('billType')!=='' &&
    	  ($request->get('billType') === 'sale' || $request->get('billType') === 'purc')
    	) {
    	$bill_type = Utilities::clean_string($request->get('billType'));
    } else {
    	$bill_type = 'sale';
    }

    switch ($bill_type) {
    	case 'sale':
    		$page_title = 'Edit Sales Bill';
    		$label_name = 'Enter bill no. to edit';
    		$icon_name = 'fa fa-inr';
    		break;
    	case 'purc':
    		$page_title = 'Edit Purchase Bill';
    		$label_name = 'Enter PO no. to edit';
    		$icon_name = 'fa fa-compass';
    		break;
    }

     // prepare form variables.
    $template_vars = array(
    	'label_name' => $label_name,
    	'page_error' => $page_error,
    	'bill_no' => $bill_no,
    	'bill_type' => $bill_type,
    );

    // build variables
    $controller_vars = array(
      'page_title' => $page_title,
      'icon_name' => $icon_name,
    );

    // render template
    return array($this->template->render_view('ask-for-billno',$template_vars),$controller_vars);
	}

  # edit sales bill with limited information
	public function editSalesBillAction(Request $request) {

    $errors = $sales_details = $submitted_data = array();
    $page_error = $page_success = '';

    if($request->get('billNo') && $request->get('billNo')!=='') {
      $bill_no = Utilities::clean_string($request->get('billNo'));
      $sales_response = $this->sales_model->get_sales_details($bill_no,true);
      if($sales_response['status']===true) {
        $sales_details = $sales_response['saleDetails'];
      } else {
        $page_error = $sales_response['apierror'];
        $flash->set_flash_message($page_error,1);
        Utilities::redirect('/admin-options/enter-bill-no');
      }
    } else {
      $this->flash->set_flash_message('Invalid Bill No. (or) Bill does not exist.',1);
      Utilities::redirect('/admin-options/enter-bill-no');
    }

    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $sales_code = $sales_details['invoiceCode'];
      $sales_response = $this->sales_model->updateSale($submitted_data,$sales_code);
     	$status = $sales_response['status'];
     	if($status===false) {
        if(isset($sales_response['errors'])) {
          if(isset($sales_response['errors']['itemDetails'])) {
            $page_error = $sales_response['errors']['itemDetails'];
            unset($sales_response['errors']['itemDetails']);
          }
          $errors = $sales_response['errors'];
        } elseif(isset($sales_response['apierror'])) {
          $page_error = $sales_response['apierror'];
        }
     	} else {
        $this->flash->set_flash_message('Sales transaction with Bill No. <b>'.$sales_details['billNo']. '</b> updated successfully');
        $redirect_url = '/admin-options/edit-sales-bill?billNo='.$bill_no;
        Utilities::redirect($redirect_url);
     	}

    } elseif(count($sales_details)>0) {
      $submitted_data = $sales_details;
    }

    $qtys_a = array(0=>'Sel');
		$doctors_a = array(-1=>'Choose', 0=>'D.M.O')+$this->sales_model->get_doctors();
    $ages_a[0] = 'Choose';
    for($i=1;$i<=150;$i++) {
      $ages_a[$i] = $i;
    }
    for($i=1;$i<=365;$i++) {
      $credit_days_a[$i] = $i;
    }
    for($i=1;$i<=500;$i++) {
      $qtys_a[$i] = $i;
    }

    // prepare form variables.
    $template_vars = array(
	    'sale_types' => Constants::$SALE_TYPES,
	    'sale_modes' => Constants::$SALE_MODES,
	    'status' => Constants::$RECORD_STATUS,
	    'doctors' => $doctors_a,
	    'age_categories' => Constants::$AGE_CATEGORIES,
	    'genders' => array(''=>'Choose') + Constants::$GENDERS,
	    'payment_methods' => Constants::$PAYMENT_METHODS,
	    'ages' => $ages_a,
	    'credit_days_a' => array(0=>'Choose') +$credit_days_a,
	    'qtys_a' => $qtys_a,
	    'yes_no_options' => array(''=>'Choose', 1=>'Yes', 0=>'No'),
	    'errors' => $errors,
	    'page_error' => $page_error,
	    'page_success' => $page_success,
	    'btn_label' => 'Edit sale transaction',
	    'submitted_data' => $submitted_data,
	    'flash' => $this->flash,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Edit Sales Bill',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('edit-sale-bill',$template_vars),$controller_vars);
	}

  # update business information
	public function editBusinessInfoAction(Request $request) {

    $form_data = $states = $form_errors = array();

    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $validation = $this->_validate_businessinfo($form_data);
      $status = $validation['status'];
      if($status) {
        $form_data = $validation['cleaned_params'];
        $result = $this->user_model->update_client_details($form_data);
        if($result['status']===true) {
          $message = 'Information updated successfully. Changes will be updated after you logout from current session.';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/admin-options/edit-business-info');
        } else {
          $message = 'An error occurred while updating your information.';
          $this->flash->set_flash_message($message,1);
          Utilities::redirect('/admin-options/edit-business-info');
        }
      } else {
        $form_errors = $validation['errors'];
        $client_details = $form_data;
      }

    } else {
      // get client details.
      $client_details = $this->user_model->get_client_details()['clientDetails'];
    }

    $states_a = Constants::$LOCATION_STATES;
    asort($states_a);

    // template variables
    $template_vars = array(
      'states' => array(0=>'Choose')+$states_a,
      'page_error' => '',
      'page_success' => '',
      'form_data' => $client_details,
      'form_errors' => $form_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Update Business Information',
      'icon_name' => 'fa fa-building',
    );

    // render template
    return array($this->template->render_view('edit-business-info',$template_vars),$controller_vars);
	}

  # delete Sale Bill
  public function deleteSaleBill(Request $request) {

    $page_error = $page_title = $bill_no = '';

    if(count($request->request->all()) > 0) {
      $bill_no = Utilities::clean_string($request->get('delSaleBill'));
      $bill_details = $this->sales_model->get_sales_details($bill_no,true);
      if($bill_details['status']) {
        # delete sale bill api.
        $api_response = $this->sales_model->removeSalesTransaction($bill_details['saleDetails']['invoiceCode']);
        $status = $api_response['status'];
        if($status===false) {
          if(isset($api_response['errors'])) {
            if(isset($api_response['errors']['itemDetails'])) {
              $page_error = $api_response['errors']['itemDetails'];
              unset($api_response['errors']['itemDetails']);
            }
            $errors = $api_response['errors'];
          } elseif(isset($api_response['apierror'])) {
            $page_error = $api_response['apierror'];
          }
        } else {
          $this->flash->set_flash_message('Sales transaction with Bill No. <b>'.$bill_no. '</b> deleted successfully');
          Utilities::redirect('/admin-options/delete-sale-bill');
        }
      } else {
        $page_error = 'Invalid Bill No. (or) Bill does not exist.';
      }
    }

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'bill_no' => $bill_no,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Delete Sale Bill',
      'icon_name' => 'fa fa-times',
    );

    // render template
    return array($this->template->render_view('delete-sale-bill',$template_vars),$controller_vars);
  }  

  # validation of business info.
  private function _validate_businessinfo($form_data=array()) {
    
    $cleaned_params = $errors = array();
    $image_data = '';

    $states_a = array_keys(Constants::$LOCATION_STATES);

    $business_name = Utilities::clean_string($form_data['businessName']);
    $gst_no = Utilities::clean_string($form_data['gstNo']);
    $dl_no = Utilities::clean_string($form_data['dlNo']);
    $address1 = Utilities::clean_string($form_data['address1']);
    $address2 = Utilities::clean_string($form_data['address2']);
    $state_id = Utilities::clean_string($form_data['locState']);
    $pincode = Utilities::clean_string($form_data['pincode']);
    $phones = Utilities::clean_string($form_data['phones']);

    # check logo information.
    if( isset($_FILES['logoName']) && $_FILES['logoName']['name'] !== '') {
      $file_details = $_FILES['logoName'];
      if( exif_imagetype($file_details['tmp_name']) !== 2 ) {
        $errors['logoName'] = 'Invalid Business Logo. Only .jpg or .jpeg file formats are allowed.';
      } else {
        $image_info = file_get_contents($file_details['tmp_name']);
        $image_data = 'data:' . $file_details['type'] . ';base64,' . base64_encode($image_info);
      }
    }

    if( !ctype_alnum(str_replace(' ', '', $business_name)) ) {
      $errors['businessName'] = 'Invalid business name. Only alphabets and digits are allowed.';
    } else {
      $cleaned_params['businessName'] = $business_name;
    }
    if($gst_no !== '' && strlen(str_replace('_','',$gst_no)) !== 15 ) {
      $errors['gstNo'] = 'Invalid GST No.';
    } else {
      $cleaned_params['gstNo'] = $gst_no;
    }
    if(in_array($state_id, $states_a) === false) {
      $errors['locState'] = 'Invalid State.';
    } else {
      $cleaned_params['locState'] = $state_id;
    }
    if($pincode !== '' && !is_numeric($pincode)) {
      $errors['pincode'] = 'Invalid Pincode.';
    } else {
      $cleaned_params['pincode'] = $pincode;
    }    

    $cleaned_params['dlNo'] = $dl_no;
    $cleaned_params['address1'] = $address1;
    $cleaned_params['address2'] = $address2;
    $cleaned_params['phones'] = $phones;
    $cleaned_params['logoData'] = $image_data;

    if(count($errors)>0) {
      return array('status'=>false, 'errors'=>$errors);
    } else {
      return array('status'=>true, 'cleaned_params'=>$cleaned_params);
    }

  }
}