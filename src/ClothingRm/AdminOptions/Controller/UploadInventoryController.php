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

use ClothingRm\Openings\Model\Openings;
use ClothingRm\Inventory\Model\Inventory;
use User\Model\User;

class UploadInventoryController
{
	
  protected $views_path, $template, $sales_model, $purch_model, $flash;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
		$this->template = new Template($this->views_path);
    $this->openings_model = new Openings;
    $this->inven_model = new Inventory;
		$this->flash = new Flash;
    $this->user_model = new User;    
	}

  // upload inventory action
  public function uploadInventoryAction(Request $request) {

    # variable assignments.
    $unique_leads = $import_data = $form_errors = [];
    $upload_errors = [];
    $op_a = ['append' => 'Append to existing data', 'remove' => 'Remove existing data and append'];
    $allowed_extensions = ['xls', 'ods', 'xlsx'];
    $redirect_url = '/upload-inventory';
    $op = -1;

    # ---------- get location codes from api -----------------------
    $client_locations_resp = $this->user_model->get_client_locations();
    if($client_locations_resp['status']) {
      foreach($client_locations_resp['clientLocations'] as $loc_details) {
        $client_locations[$loc_details['locationCode']] = $loc_details['locationName'];
      }
    }    

    # form submit
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $validate_data = $this->_validate_import_form_data($form_data);
      # if form is not valid don't process.
      if($validate_data['status'] === false) {
        $form_errors = $validate_data['errors'];
      } else {

        $upload_type = $validate_data['cleaned_params']['op'];
        $location_code = $validate_data['cleaned_params']['locationCode'];

        # check uploaded file information
        $file_details = $_FILES['fileName'];
        $file_name = $file_details['name'];
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);

        # check if we have valid file extension
        if(!in_array($extension, $allowed_extensions)) {
          $this->flash->set_flash_message('Invalid file uploaded. Only (.ods, .xls, .xlsx) file formats are allowed',1);
          Utilities::redirect($redirect_url);
        }

        # upload file to server
        $file_upload_path = __DIR__.'/../../../../bulkuploads';
        $storage = new \Upload\Storage\FileSystem($file_upload_path);
        $file = new \Upload\File('fileName', $storage);

        $uploaded_file_name = $file->getNameWithExtension();
        $uploaded_file_ext = $file->getExtension();
        if(!in_array($uploaded_file_ext, $allowed_extensions)) {
          $this->flash->set_flash_message('Invalid file extension',1);
          Utilities::redirect($redirect_url);        
        }

        # upload file.
        $new_filename = 'objectUpload_'.time();
        $file->setName($new_filename);
        try {
          $file->upload();
        } catch (\Exception $e) {
          $this->flash->set_flash_message('Unknown error. Unable to upload your file.',1);
          Utilities::redirect($redirect_url);        
        }

        # get file path from uploaded operation.
        $file_path = $file_upload_path.'/'.$new_filename.'.'.$uploaded_file_ext;

        # initiate importer
        $importer = new Importer($file_path);
        $imported_records = $importer->_import_data();

        # validate imported leads.
        $validation_response = $this->_validate_imported_records($imported_records);
        if($validation_response['status'] === false) {
          $this->flash->set_flash_message('Could not upload. You have errors in the file. Please check below.', 1);
          $upload_errors = $validation_response['errors'];
        } else {
          $cleaned_records = $validation_response['records'];
          # hit api with data.
          $api_response = $this->openings_model->upload_inventory($cleaned_records,$upload_type,$location_code);
          if($api_response['status']===false) {
            $this->flash->set_flash_message('Unable to update inventory. Please contact QwikBills administrator.',1);
            Utilities::redirect($redirect_url);
          } else {
            $this->flash->set_flash_message('Inventory updated successfully.');
            Utilities::redirect($redirect_url);
          }
        }
      }
    }

    # prepare form variables.
    $template_vars = array(
      'op_a' => array('-1' => 'Choose') + $op_a,
      'op' => $op,
      'flash' => $this->flash,
      'form_errors' => $form_errors,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'upload_errors' => $upload_errors,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Upload Inventory',
      'icon_name' => 'fa fa-database',
    );

    return array($this->template->render_view('inventory-import', $template_vars), $controller_vars);
  }

  public function updateCategoryBrandAction(Request $request) {

    $allowed_extensions = ['xls', 'ods', 'xlsx'];
    $redirect_url = '/update-category-brand';

    // form submit
    if(count($request->request->all()) > 0) {

      # check uploaded file information
      $file_details = $_FILES['fileName'];
      $file_name = $file_details['name'];
      $extension = pathinfo($file_name, PATHINFO_EXTENSION);

      # check if we have valid file extension
      if(!in_array($extension, $allowed_extensions)) {
        $this->flash->set_flash_message('Invalid file uploaded. Only (.ods, .xls, .xlsx) file formats are allowed',1);
        Utilities::redirect($redirect_url);
      }

      # upload file to server
      $file_upload_path = __DIR__.'/../../../../bulkuploads';
      $storage = new \Upload\Storage\FileSystem($file_upload_path);
      $file = new \Upload\File('fileName', $storage);

      $uploaded_file_name = $file->getNameWithExtension();
      $uploaded_file_ext = $file->getExtension();
      if(!in_array($uploaded_file_ext, $allowed_extensions)) {
        $this->flash->set_flash_message('Invalid file extension',1);
        Utilities::redirect($redirect_url);        
      }

      # upload file.
      $new_filename = 'objectUpload_'.time();
      $file->setName($new_filename);
      try {
        $file->upload();
      } catch (\Exception $e) {
        $this->flash->set_flash_message('Unknown error. Unable to upload your file.',1);
        Utilities::redirect($redirect_url);        
      }

      # get file path from uploaded operation.
      $file_path = $file_upload_path.'/'.$new_filename.'.'.$uploaded_file_ext;

      # initiate importer
      $importer = new Importer($file_path);
      $imported_records = $importer->_import_data();
      if(is_array($imported_records) && count($imported_records)>0) {
        // hit api with data.
        $api_response = $this->inven_model->update_cat_brand($imported_records);
        if($api_response['status'] === false) {
          $this->flash->set_flash_message('Unable to upload inventory. Please contact QwikBills administrator.',1);
          Utilities::redirect($redirect_url);
        } else {
          $this->flash->set_flash_message('Inventory updated successfully.');
          Utilities::redirect($redirect_url);
        }
      }
    }

    // prepare form variables.
    $template_vars = array(
      'flash' => $this->flash,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Update Category & Brand Information in Item Master from Excel File',
      'icon_name' => 'fa fa-database',
    );

    return array($this->template->render_view('update-category-brand', $template_vars), $controller_vars);    
  }


  // validating imported leads.
  private function _validate_imported_records($imported_records=[]) {
    $one_d_array = array_keys($imported_records[0]);
    $cleaned_array = [];
    $xl_errors = [];
    $error_flag = false;
    foreach($imported_records as $key => $imported_record_details) {
      if(isset($imported_record_details['ItemName'])) {
        $item_name = Utilities::clean_string($imported_record_details['ItemName']);
      } else {
        $error_flag = true;
        $xl_errors[$key]['ItemName'] = '`ItemName` column is not available. Check your Excel File..';
        break;
      }
      if($item_name !== '') {
        $closing_qty = Utilities::clean_string($imported_record_details['ClosingQty']);
        $purchase_price = Utilities::clean_string($imported_record_details['PurchasePrice']);
        $sale_price = Utilities::clean_string($imported_record_details['SalePrice']);
        // $units_per_pack = Utilities::clean_string($imported_record_details['UnitsPerPack']);
        $category_name = Utilities::clean_string($imported_record_details['CategoryName']);
        $gst = Utilities::clean_string($imported_record_details['GST']);
        $hsn_sac_code = Utilities::clean_string($imported_record_details['HsnSacCode']);
        $packed_qty = Utilities::clean_string($imported_record_details['PackedQty']);
        $brand_name = Utilities::clean_string($imported_record_details['BrandName']);
        $rack_no = Utilities::clean_string($imported_record_details['RackNo']);
        $item_sku = Utilities::clean_string($imported_record_details['ItemSku']);
        $cno = Utilities::clean_string($imported_record_details['ContainerOrCaseNo']);
        $uom_name = Utilities::clean_string($imported_record_details['UomName']);        

        if(!is_numeric($closing_qty)) {
          $error_flag = true;
          $xl_errors[$key]['ClosingQty'] = 'Invalid closing qty at Row - '.($key+2);
        }
        if($purchase_price !== '' && !is_numeric($purchase_price)) {
          $error_flag = true;
          $xl_errors[$key]['PurchasePrice'] = 'Invalid purchase price at Row - '.($key+2);
        }
        if(!is_numeric($sale_price)) {
          $error_flag = true;
          $xl_errors[$key]['SalePrice'] = 'Invalid sale price at Row - '.($key+2);
        }
        // if($units_per_pack !== '' && !is_numeric($units_per_pack)) {
        //   $error_flag = true;
        //   $xl_errors[$key]['UnitsPerPack'] = 'Invalid units per pack at Row - '.($key+2);
        // }
        if(!is_numeric($gst)) {
          $error_flag = true;
          $xl_errors[$key]['GST'] = 'Invalid GST percent at Row - '.($key+2);
        }
        if($hsn_sac_code !== '' && !is_numeric(str_replace([' '], '', $hsn_sac_code))) {
          $error_flag = true;
          $xl_errors[$key]['GST'] = 'Invalid HsnSacCode at Row - '.($key+2);          
        }
        if(!is_numeric($packed_qty) || $packed_qty < 0) {
          $error_flag = true;
          $xl_errors[$key]['PackedQty'] = 'Invalid Packed Qty. at Row - '.($key+2);
        }

        if(!$error_flag) {
          $cleaned_array[$key]['ItemName'] = $item_name;
          $cleaned_array[$key]['ClosingQty'] = $closing_qty;
          $cleaned_array[$key]['PurchasePrice'] = $purchase_price;
          $cleaned_array[$key]['SalePrice'] = $sale_price;
          // $cleaned_array[$key]['UnitsPerPack'] = $units_per_pack;
          $cleaned_array[$key]['CategoryName'] = $category_name;
          $cleaned_array[$key]['GST'] = $gst;
          $cleaned_array[$key]['HsnSacCode'] = $hsn_sac_code;
          $cleaned_array[$key]['PackedQty'] = round($packed_qty,2);
          $cleaned_array[$key]['BrandName'] = $brand_name;
          $cleaned_array[$key]['RackNo'] = $rack_no;
          $cleaned_array[$key]['ItemSku'] = $item_sku;
          $cleaned_array[$key]['ContainerOrCaseNo'] = $cno;
          $cleaned_array[$key]['UomName'] = $uom_name;
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
    $remove_duplicates_a = [0 => 'No', 1 => 'Yes'];

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

    # validate location code
    if( isset($form_data['locationCode']) && ctype_alnum($form_data['locationCode']) ) {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['locationCode'] = 'Invalid location code.';
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