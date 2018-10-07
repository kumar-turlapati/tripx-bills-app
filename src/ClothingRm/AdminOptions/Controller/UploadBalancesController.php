<?php

namespace ClothingRm\AdminOptions\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;
use Atawa\Importer;

use Spreadsheet_Excel_Reader;

use ClothingRm\Customers\Model\Customers;
use ClothingRm\Suppliers\Model\Supplier;
use User\Model\User;

class UploadBalancesController {
	
  protected $views_path, $template, $customer_model, $flash;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
		$this->template = new Template($this->views_path);
    $this->customer_model = new Customers;
    $this->supplier_model = new Supplier;
		$this->flash = new Flash;
    $this->user_model = new User;    
	}

  // upload debtors action
  public function uploadDebtorsAction(Request $request) {
    // variable assignments.
    $import_data = $form_errors = [];
    $upload_errors = [];
    $op_a = ['append' => 'Append to existing data', 'remove' => 'Remove existing data and append'];
    $allowed_extensions = ['xls', 'ods', 'xlsx'];
    $redirect_url = '/upload-debtors';
    $op = -1;

    // form submit
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $validate_data = $this->_validate_import_form_data($form_data);
      # if form is not valid don't process.
      if($validate_data['status'] === false) {
        $form_errors = $validate_data['errors'];
      } else {

        $upload_type = $validate_data['cleaned_params']['op'];

        # check uploaded file information
        $file_details = $_FILES['fileName'];
        $file_name = $file_details['name'];
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);

        // check if we have valid file extension
        if(!in_array($extension, $allowed_extensions)) {
          $this->flash->set_flash_message('Invalid file uploaded. Only (.ods, .xlsx) file formats are allowed',1);
          Utilities::redirect($redirect_url);
        }

        // upload file to server
        $file_upload_path = __DIR__.'/../../../../bulkuploads';
        $storage = new \Upload\Storage\FileSystem($file_upload_path);
        $file = new \Upload\File('fileName', $storage);

        $uploaded_file_name = $file->getNameWithExtension();
        $uploaded_file_ext = $file->getExtension();
        if(!in_array($uploaded_file_ext, $allowed_extensions)) {
          $this->flash->set_flash_message('Invalid file extension',1);
          Utilities::redirect($redirect_url);        
        }

        // upload file.
        $new_filename = 'objectUploadDeb_'.time();
        $file->setName($new_filename);
        try {
          $file->upload();
        } catch (\Exception $e) {
          $this->flash->set_flash_message('Unknown error. Unable to upload your file.',1);
          Utilities::redirect($redirect_url);
        }

        // get file path from uploaded operation.
        $file_path = $file_upload_path.'/'.$new_filename.'.'.$uploaded_file_ext;

        // initiate importer
        $importer = new Importer($file_path);
        $imported_records = $importer->_import_data();
        if(count($imported_records)>301) {
          $this->flash->set_flash_message('Only 300 rows are allowed per file upload.', 1);
          Utilities::redirect($redirect_url);
        }

        // validate imported leads.
        $validation_response = $this->_validate_imported_records_debtors($imported_records);
        if($validation_response['status'] === false) {
          $this->flash->set_flash_message('Could not upload. You have errors in the file. Please check below.', 1);
          $upload_errors = $validation_response['errors'];
        } else {
          $cleaned_records = $validation_response['records'];
          // hit api with data.
          $api_response = $this->customer_model->upload_debtors($cleaned_records,$upload_type);
          if($api_response['status'] === false) {
            $this->flash->set_flash_message('Unable to upload Customers Data. Please contact QwikBills administrator.',1);
            Utilities::redirect($redirect_url);
          } else {
            $this->flash->set_flash_message('Customers Data Uploaded Successfully.');
            Utilities::redirect($redirect_url);
          }
        }
      }
    }

    // prepare form variables.
    $template_vars = array(
      'op_a' => array('-1' => 'Choose') + $op_a,
      'op' => $op,
      'flash' => $this->flash,
      'form_errors' => $form_errors,
      'upload_errors' => $upload_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Upload Customers Data',
      'icon_name' => 'fa fa-smile-o',
    );

    return array($this->template->render_view('upload-customers-data', $template_vars), $controller_vars);
  }

  // upload debtors action
  public function uploadCreditorsAction(Request $request) {

    // variable assignments.
    $import_data = $form_errors = [];
    $upload_errors = [];
    $op_a = ['append' => 'Append to existing data', 'remove' => 'Remove existing data and append'];
    $allowed_extensions = ['xls', 'ods', 'xlsx'];
    $redirect_url = '/upload-creditors';
    $op = -1;

    // form submit
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $validate_data = $this->_validate_import_form_data($form_data);
      # if form is not valid don't process.
      if($validate_data['status'] === false) {
        $form_errors = $validate_data['errors'];
      } else {

        $upload_type = $validate_data['cleaned_params']['op'];

        # check uploaded file information
        $file_details = $_FILES['fileName'];
        $file_name = $file_details['name'];
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);

        // check if we have valid file extension
        if(!in_array($extension, $allowed_extensions)) {
          $this->flash->set_flash_message('Invalid file uploaded. Only (.ods, .xlsx) file formats are allowed',1);
          Utilities::redirect($redirect_url);
        }

        // upload file to server
        $file_upload_path = __DIR__.'/../../../../bulkuploads';
        $storage = new \Upload\Storage\FileSystem($file_upload_path);
        $file = new \Upload\File('fileName', $storage);

        $uploaded_file_name = $file->getNameWithExtension();
        $uploaded_file_ext = $file->getExtension();
        if(!in_array($uploaded_file_ext, $allowed_extensions)) {
          $this->flash->set_flash_message('Invalid file extension',1);
          Utilities::redirect($redirect_url);        
        }

        // upload file.
        $new_filename = 'objectUploadCredit_'.time();
        $file->setName($new_filename);
        try {
          $file->upload();
        } catch (\Exception $e) {
          $this->flash->set_flash_message('Unknown error. Unable to upload your file.',1);
          Utilities::redirect($redirect_url);
        }

        // get file path from uploaded operation.
        $file_path = $file_upload_path.'/'.$new_filename.'.'.$uploaded_file_ext;

        // initiate importer
        $importer = new Importer($file_path);
        $imported_records = $importer->_import_data();
        if(count($imported_records)>301) {
          $this->flash->set_flash_message('Only 300 rows are allowed per file upload.', 1);
          Utilities::redirect($redirect_url);
        }

        // validate imported leads.
        $validation_response = $this->_validate_imported_records_creditors($imported_records);
        if($validation_response['status'] === false) {
          $this->flash->set_flash_message('Could not upload. You have errors in the file. Please check below.', 1);
          $upload_errors = $validation_response['errors'];
        } else {
          $cleaned_records = $validation_response['records'];
          // hit api with data.
          $api_response = $this->supplier_model->upload_creditors($cleaned_records,$upload_type);
          if($api_response['status'] === false) {
            $this->flash->set_flash_message('Unable to upload Suppliers Data. Please contact QwikBills administrator.',1);
            Utilities::redirect($redirect_url);
          } else {
            $this->flash->set_flash_message('Suppliers Data Uploaded Successfully.');
            Utilities::redirect($redirect_url);
          }
        }
      }
    }

    // prepare form variables.
    $template_vars = array(
      'op_a' => array('-1' => 'Choose') + $op_a,
      'op' => $op,
      'flash' => $this->flash,
      'form_errors' => $form_errors,
      'upload_errors' => $upload_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Upload Suppliers Data',
      'icon_name' => 'fa fa-users',
    );

    return array($this->template->render_view('upload-suppliers-data', $template_vars), $controller_vars);
  }  

  // validate debtors
  private function _validate_imported_records_debtors($imported_records=[]) {
    $one_d_array = array_keys($imported_records[0]);
    $cleaned_array = [];
    $xl_errors = [];
    $error_flag = false;
    foreach($imported_records as $key => $imported_record_details) {
      $customer_name = Utilities::clean_string($imported_record_details['CustomerName']);
      if($customer_name !== '') {
        $row_no = $key+2;
        $gst_no = Utilities::clean_string($imported_record_details['GSTNo']);
        $customer_type = Utilities::clean_string($imported_record_details['CustomerType']);
        $address = Utilities::clean_string($imported_record_details['Address']);
        $city_name = Utilities::clean_string($imported_record_details['CityName']);
        $state_id = Utilities::clean_string($imported_record_details['StateCode']);
        $country_id = Utilities::clean_string($imported_record_details['CountryCode']);
        $pincode = Utilities::clean_string($imported_record_details['Pincode']);
        $phones = Utilities::clean_string($imported_record_details['Phones']);
        $mobile_no = Utilities::clean_string($imported_record_details['MobileNo']);
        $bill_no = Utilities::clean_string($imported_record_details['BillNo']);
        $bill_date = Utilities::clean_string($imported_record_details['BillDate']);
        $balance = Utilities::clean_string($imported_record_details['Balance']);
        $credit_days = Utilities::clean_string($imported_record_details['CreditDays']);
        $sexe_id = Utilities::clean_string($imported_record_details['SalesExecutiveId']);

        if($customer_type !== 'c' && $customer_type !== 'b') {
          $error_flag = true;
          $xl_errors[$key]['CustomerType'] = 'Invalid Customer type at Row - '.$row_no;
        }
/*        if($city_name === '') {
          $error_flag = true;
          $xl_errors[$key]['CityName'] = 'Invalid City Name at Row - '.$row_no;
        }
        if(!Utilities::validate_state_code($state_id)) {
          $error_flag = true;
          $xl_errors[$key]['StateCode'] = 'Invalid State Code at Row - '.$row_no;
        }
        if(!is_numeric($country_id)) {
          $error_flag = true;
          $xl_errors[$key]['CountryCode'] = 'Invalid Country Code at Row - '.$row_no;
        }
        if($pincode !== '' && !ctype_digit(str_replace([' ', '', '-'], ['','',''], $pincode))) {
          $error_flag = true;
          $xl_errors[$key]['Pincode'] = 'Invalid Pincode at Row - '.$row_no;
        }*/
        if($bill_no !== '' && !Utilities::validate_date($bill_date)) {
          $error_flag = true;
          $xl_errors[$key]['BillDate'] = 'Invalid Bill Date at Row - '.$row_no;
        }
        if($sexe_id !== '' && !Utilities::validateEmail($sexe_id)) {
          $error_flag = true;
          $xl_errors[$key]['SalesExecutiveId'] = 'Invalid Sales Executive ID. The ID should be in email format. - '.$row_no;
        }

        if($error_flag === false) {
          $cleaned_array[$key]['CustomerName'] = $customer_name;
          $cleaned_array[$key]['GSTNo'] = $gst_no;
          $cleaned_array[$key]['CustomerType'] = $customer_type;
          $cleaned_array[$key]['Address'] = $address;
          $cleaned_array[$key]['CityName'] = $city_name;
          $cleaned_array[$key]['StateCode'] = $state_id;
          $cleaned_array[$key]['CountryCode'] = $country_id;
          $cleaned_array[$key]['Pincode'] = $pincode;
          $cleaned_array[$key]['Phones'] = $phones;
          $cleaned_array[$key]['MobileNo'] = substr($mobile_no,0,10);
          $cleaned_array[$key]['BillNo'] = $bill_no;
          $cleaned_array[$key]['BillDate'] = $bill_date;
          $cleaned_array[$key]['Balance'] = $balance;
          $cleaned_array[$key]['CreditDays'] = $credit_days;
          $cleaned_array[$key]['SalesExecutiveId'] = $sexe_id;
        }
      }
    }
    if(count($xl_errors)>0) {
      $response = ['status' => false, 'errors' => $xl_errors];
    } else {
      $response = ['status' => true, 'records' => $cleaned_array];
    }
    return $response;
  }

  // validate creditors
  private function _validate_imported_records_creditors($imported_records=[]) {
    $one_d_array = array_keys($imported_records[0]);
    $cleaned_array = [];
    $xl_errors = [];
    $error_flag = false;
    foreach($imported_records as $key => $imported_record_details) {
      $customer_name = Utilities::clean_string($imported_record_details['CustomerName']);
      if($customer_name !== '') {
        $row_no = $key+2;
        $gst_no = Utilities::clean_string($imported_record_details['GSTNo']);
        $address = Utilities::clean_string($imported_record_details['Address']);
        $city_name = Utilities::clean_string($imported_record_details['CityName']);
        $state_id = Utilities::clean_string($imported_record_details['StateCode']);
        $country_id = Utilities::clean_string($imported_record_details['CountryCode']);
        $pincode = Utilities::clean_string($imported_record_details['Pincode']);
        $phones = Utilities::clean_string($imported_record_details['Phones']);
        $mobile_no = Utilities::clean_string($imported_record_details['MobileNo']);
        $bill_no = Utilities::clean_string($imported_record_details['BillNo']);
        $bill_date = Utilities::clean_string($imported_record_details['BillDate']);
        $balance = Utilities::clean_string($imported_record_details['Balance']);
        $credit_days = Utilities::clean_string($imported_record_details['CreditDays']);

/*        if($city_name === '') {
          $error_flag = true;
          $xl_errors[$key]['CityName'] = 'Invalid City Name at Row - '.$row_no;
        }
        if(!Utilities::validate_state_code($state_id)) {
          $error_flag = true;
          $xl_errors[$key]['StateCode'] = 'Invalid State Code at Row - '.$row_no;
        }
        if(!is_numeric($country_id)) {
          $error_flag = true;
          $xl_errors[$key]['CountryCode'] = 'Invalid Country Code at Row - '.$row_no;
        }
        if($pincode !== '' && !ctype_digit(str_replace([' ', '', '-'], ['','',''], $pincode))) {
          $error_flag = true;
          $xl_errors[$key]['Pincode'] = 'Invalid Pincode at Row - '.$row_no;
        }*/
        if($bill_no !== '' && !Utilities::validate_date($bill_date)) {
          $error_flag = true;
          $xl_errors[$key]['BillDate'] = 'Invalid Bill Date at Row - '.$row_no;
        }

        if($error_flag === false) {
          $cleaned_array[$key]['CustomerName'] = $customer_name;
          $cleaned_array[$key]['GSTNo'] = $gst_no;
          $cleaned_array[$key]['Address'] = $address;
          $cleaned_array[$key]['CityName'] = $city_name;
          $cleaned_array[$key]['StateCode'] = $state_id;
          $cleaned_array[$key]['CountryCode'] = $country_id;
          $cleaned_array[$key]['Pincode'] = $pincode;
          $cleaned_array[$key]['Phones'] = $phones;
          $cleaned_array[$key]['MobileNo'] = substr($mobile_no,0,10);
          $cleaned_array[$key]['BillNo'] = $bill_no;
          $cleaned_array[$key]['BillDate'] = $bill_date;
          $cleaned_array[$key]['Balance'] = $balance;
          $cleaned_array[$key]['CreditDays'] = $credit_days;
        }
      }
    }
    if(count($xl_errors)>0) {
      $response = ['status' => false, 'errors' => $xl_errors];
    } else {
      $response = ['status' => true, 'records' => $cleaned_array];
    }
    return $response;
  }  

  # validate import form data
  private function _validate_import_form_data($form_data = '') {
    $form_errors = [];
    $op_a = ['append' => 'Append to existing data', 'remove' => 'Remove existing data and append'];

    $op = Utilities::clean_string($form_data['op']);

    # check uploaded file information
    $file_details = $_FILES['fileName'];
    $file_name = $file_details['name'];
    if(trim($file_name) === '') {
      $form_errors['fileName'] = 'Please upload a file.';
    }
    if( $op < 0 || !in_array($op, array_keys($op_a)) ) {
      $form_errors['op'] = 'Please choose an option';
    } else {
      $cleaned_params['op'] = $op;
    }

    if(count($form_errors)>0) {
      return array(
        'status' => false,
        'errors' => $form_errors,
      );
    } else {
      return array(
        'status' => true,
        'cleaned_params' => $cleaned_params,
      );
    }
  }
}