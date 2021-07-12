<?php 

namespace ClothingRm\Inventory\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;
use Atawa\Importer;

use ClothingRm\Inventory\Model\Inventory;
use ClothingRm\Inventory\Model\InventoryMrp;
use ClothingRm\Products\Model\Products;

class InventoryMrpController {

  protected $views_path;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->inven_api = new Inventory;
    $this->inven_mrp_api = new InventoryMrp;
    $this->product_api_call = new Products;    
    $this->flash = new Flash;
  }

  public function changeMrpAction(Request $request = null) {
    // pagination variables.
    $total_pages = $total_records = $record_count = $page_no = 0;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';
    
    $client_locations = $location_ids = $location_codes = $items_a = [];
    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';    

    // get location codes from api
    $client_locations = Utilities::get_client_locations(true, false, true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    if(is_null($request->get('locationCode'))) {
      $search_params['locationCode'] = $default_location;
    } else {
      $search_params['locationCode'] = Utilities::clean_string($request->get('locationCode'));
    }    
    if(is_null($request->get('pageNo'))) {
      $search_params['pageNo'] = $page_no = 1;
    } else {
      $search_params['pageNo'] = $page_no = (int)$request->get('pageNo');
    }
    if(is_null($request->get('perPage'))) {
      $search_params['perPage'] = $per_page = 100;
    } else {
      $search_params['perPage'] = $per_page = (int)$request->get('perPage');
    }
    if(is_null($request->get('category'))) {
      $search_params['category'] = '';
    } else {
      $search_params['category'] = Utilities::clean_string($request->get('category'));
    }
    if(is_null($request->get('brandName'))) {
      $search_params['brandName'] = '';
    } else {
      $search_params['brandName'] = Utilities::clean_string($request->get('brandName'));
    }
    if(is_null($request->get('psName'))) {
      $search_params['psName'] = '';
    } else {
      $search_params['psName'] = Utilities::clean_string($request->get('psName'));
    }

    $search_params['onlyPOItems'] = 1;

    // dump($search_params);
    // exit;

    // get categories
    $categories = array('' => 'All Categories')+$this->product_api_call->get_product_categories($search_params['locationCode']);    

    // hit API.
    $items_api_call = $this->inven_api->get_available_qtys($search_params);
    $api_status = $items_api_call['status'];

    // dump($items_api_call);
    // exit;

    // check api status
    if($api_status) {
      if(count($items_api_call['items'])>0) {
        $slno = Utilities::get_slno_start(count($items_api_call['items']), $per_page, $page_no);
        $to_sl_no = $slno + $per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;
        }
        if($items_api_call['total_pages']<$page_links_to_end) {
          $page_links_to_end = $items_api_call['total_pages'];
        }
        if($items_api_call['total_records'] < $per_page) {
          $to_sl_no = ($slno + $items_api_call['total_records'])-1;
        }

        $items_a = $items_api_call['items'];
        $total_pages = $items_api_call['total_pages'];
        $total_records = $items_api_call['total_records'];
        $record_count = $items_api_call['total_records'];
      } else {
        $page_error = $items_api_call['apierror'];
      }
    } else {
      $this->flash->set_flash_message($items_api_call['apierror'], 1);
    }    

    // prepare form variables.
    $template_vars = array(
      'items' => $items_a,
      'total_pages' => $total_pages,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'client_locations' => ['' => 'All Stores'] + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'flash_obj' => $this->flash,
      'categories' => $categories,
      'default_location' => $default_location,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Update MRP in Purchase Orders',
      'icon_name' => 'fa fa-tasks',
    );

    // render template
    return array($this->template->render_view('change-mrp', $template_vars),$controller_vars);
  }

  public function mrpRegisterAction(Request $request = null) {
    // pagination variables.
    $total_pages = $total_records = $record_count = $page_no = 0;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';
    
    $client_locations = $location_ids = $location_codes = $items_a = [];
    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';    

    // get location codes from api
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    if(is_null($request->get('locationCode'))) {
      $search_params['locationCode'] = $default_location;
    } else {
      $search_params['locationCode'] = Utilities::clean_string($request->get('locationCode'));
    }    
    if(is_null($request->get('pageNo'))) {
      $search_params['pageNo'] = $page_no = 1;
    } else {
      $search_params['pageNo'] = $page_no = (int)$request->get('pageNo');
    }
    if(is_null($request->get('perPage'))) {
      $search_params['perPage'] = $per_page = 100;
    } else {
      $search_params['perPage'] = $per_page = (int)$request->get('perPage');
    }
    if(is_null($request->get('category'))) {
      $search_params['category'] = '';
    } else {
      $search_params['category'] = Utilities::clean_string($request->get('category'));
    }
    if(is_null($request->get('brandName'))) {
      $search_params['brandName'] = '';
    } else {
      $search_params['brandName'] = Utilities::clean_string($request->get('brandName'));
    }
    if(is_null($request->get('psName'))) {
      $search_params['psName'] = '';
    } else {
      $search_params['psName'] = Utilities::clean_string($request->get('psName'));
    }

    // get categories
    $categories = array('' => 'All Categories')+$this->product_api_call->get_product_categories($search_params['locationCode']);    

    // hit API.
    $items_api_call = $this->inven_api->changed_mrp_register($search_params);
    $api_status = $items_api_call['status'];

    // dump($items_api_call);
    // exit;

    // check api status
    if($api_status) {
      if(count($items_api_call['items'])>0) {
        $slno = Utilities::get_slno_start(count($items_api_call['items']), $per_page, $page_no);
        $to_sl_no = $slno + $per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;
        }
        if($items_api_call['total_pages']<$page_links_to_end) {
          $page_links_to_end = $items_api_call['total_pages'];
        }
        if($items_api_call['total_records'] < $per_page) {
          $to_sl_no = ($slno + $items_api_call['total_records'])-1;
        }

        $items_a = $items_api_call['items'];
        $total_pages = $items_api_call['total_pages'];
        $total_records = $items_api_call['total_records'];
        $record_count = $items_api_call['total_records'];
      } else {
        $page_error = $items_api_call['apierror'];
      }
    } else {
      $this->flash->set_flash_message($items_api_call['apierror'], 1);
    }    

    // prepare form variables.
    $template_vars = array(
      'items' => $items_a,
      'total_pages' => $total_pages,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'client_locations' => ['' => 'All Stores'] + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'flash_obj' => $this->flash,
      'categories' => $categories,
      'default_location' => $default_location,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Changed Selling Prices Register',
      'icon_name' => 'fa fa-edit',
    );

    // render template
    return array($this->template->render_view('changed-mrp-register', $template_vars),$controller_vars);
  }

  public function bulkMrpUpdateAction(Request $request = null) {

    $client_locations = $location_ids = $location_codes = [];
    $form_errors = $form_data = [];
    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';

    // get location codes from api
    $client_locations = Utilities::get_client_locations();
    foreach($client_locations as $location_key => $location_value) {
      $location_codes[$location_key] = $location_value;      
    }

    // check if form is submitted.
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $validation_status = $this->_validate_form_data($submitted_data);
      if($validation_status['status']) {
        $cleaned_params = $validation_status['cleaned_params'];
        // hit api with data.
        $api_response = $this->inven_mrp_api->selling_price_bulk_update($cleaned_params);
        if($api_response['status']===false) {
          $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i> '.$api_response['apierror'],1);
          Utilities::redirect('/inventory/change-selling-price');
        } else {
          $this->flash->set_flash_message('<i class="fa fa-check" aria-hidden="true"></i> '.$api_response['response']['updateMessage']);
          Utilities::redirect('/inventory/change-selling-price');
        }
      } else {
        $form_errors = $validation_status['form_errors'];
        $form_data = $submitted_data;
      }
    }

    // prepare form variables.
    $template_vars = array(
      'client_locations' => ['' => 'Choose a Location'] + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'flash_obj' => $this->flash,
      'default_location' => $default_location,
      'utilities' => new Utilities,
      'api_error' => '',
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'update_for_a' => ['' => 'Choose', 'opening' => 'Opening Balances', 'purchase' => 'Purchase Orders'],
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Bulk Update - Selling Price',
      'icon_name' => 'fa fa-edit',
    );

    // render template
    return array($this->template->render_view('bulk-update-selling-price', $template_vars),$controller_vars);    
  }

  private function _validate_form_data($form_data=[]) {

    $allowed_extensions = ['xlsx'];
    $form_errors = $cleaned_params = $missing_fields = $imp_record_errors = [];
    $upload_fields = ["ItemName", "LotNo", "NewMrp", "NewWholesalePrice", "NewOnlinePrice", "NewExmill"];

    $is_one_item_found = false; 
    $all_fields_exists = true;

    // dump($form_data);
    // exit;

    // validate location code
    if( isset($form_data['locationCode']) && ctype_alnum($form_data['locationCode']) ) {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['locationCode'] = 'Invalid location';
    }

    // validate billing rate
    if( isset($form_data['billingRate']) && 
        ($form_data['billingRate'] === 'all' || 
         $form_data['billingRate'] === 'mrp' || 
         $form_data['billingRate'] === 'wholesale' || 
         $form_data['billingRate'] === 'online' ||
         $form_data['billingRate'] === 'exmill'
        )
      ) {
      $cleaned_params['billingRate'] = $form_data['billingRate'];
    } else {
      $form_errors['billingRate'] = 'Invalid billing rate';
    }

    // validate update for
    if( isset($form_data['updateFor']) && 
        (
          $form_data['updateFor'] === 'opening' ||
          $form_data['updateFor'] === 'purchase'
        )
    ) {
      $cleaned_params['updateFor'] = Utilities::clean_string($form_data['updateFor']);
    } else {
      $form_errors['updateFor'] = 'Update for Opening or POs required';
    }    

    # validate uploaded file information.
    if(isset($_FILES['fileName']) && trim($_FILES['fileName']['name']) === '') {
      $form_errors['fileName'] = 'Please upload a file.';
    } else {  
      # validate file information.
      $file_details = $_FILES['fileName'];
      $file_name = $file_details['name'];
      $extension = pathinfo($file_name, PATHINFO_EXTENSION);

      # check if we have valid file extension
      if(!in_array($extension, $allowed_extensions)) {
        $form_errors['fileName'] = 'Invalid file. Only (.xlsx) is allowed';
        return [
          'status' => false,
          'form_errors' => $form_errors,
        ];
      }

      # upload file to server
      $file_upload_path = __DIR__.'/../../../../bulkuploads';
      $storage = new \Upload\Storage\FileSystem($file_upload_path);
      $file = new \Upload\File('fileName', $storage);

      $uploaded_file_name = $file->getNameWithExtension();
      $uploaded_file_ext = $file->getExtension();
      if(!in_array($uploaded_file_ext, $allowed_extensions)) {
        $form_errors['fileName'] = 'Invalid file extesion.';
      }

      # upload file.
      $new_filename = 'bulkMrpUpdate_'.time();
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

      if(is_array($imported_records) && count($imported_records) > 0 && count($imported_records) <= 350 ) {
        $fields_extracted = array_keys($imported_records[0]);
        # check whether all the fields are existing or not.
        foreach($upload_fields as $field_name) {
          if(!in_array($field_name, $fields_extracted)) {
            $missing_fields[] = $field_name;
            $all_fields_exists = false;
          }
        }
        if(!$all_fields_exists) {
          $form_errors['fileName'] = 'This uploaded file does not contain mandatory field (s) < '.implode(',', $missing_fields). ' >. Please add all missing fields and upload the file again.';
        } else {
          # validate imported records.
          foreach($imported_records as $key => $item_details) {
            $item_name = Utilities::clean_string($item_details['ItemName']);
            $lot_no =  Utilities::clean_string($item_details['LotNo']);
            $mrp = isset($item_details['NewMrp']) ? Utilities::clean_string($item_details['NewMrp']) : ''; 
            $wholesale_price = isset($item_details['NewWholesalePrice']) && is_numeric($item_details['NewWholesalePrice']) ? Utilities::clean_string($item_details['NewWholesalePrice']) : '';
            $online_price = isset($item_details['NewOnlinePrice']) && is_numeric($item_details['NewOnlinePrice'])? Utilities::clean_string($item_details['NewOnlinePrice']) : ''; 

            if($item_name === '') {
              $form_errors['itemDetails'][$key]['ItemName'] = 'Item name is required';
            }
            if(!ctype_alnum($lot_no)) {
              $form_errors['itemDetails'][$key]['LotNo'] = 'Invalid Lot No';
            }

            if($mrp !== '') {
              if($mrp < 0) {
                $form_errors['itemDetails'][$key]['NewMrp'] = 'Invalid MRP.';
              } else {
                $imported_records[$key]['NewMrp'] = $mrp;
              }
            }

            if($wholesale_price !== '') {
              if($wholesale_price < 0) {
                $form_errors['itemDetails'][$key]['WholesalePrice'] = 'Invalid wholesale price';
              } else {
                $imported_records[$key]['NewWholesalePrice'] = $wholesale_price;
              }
            }

            if($online_price !== '') {
              if($online_price < 0) {
                $form_errors['itemDetails'][$key]['OnlinePrice'] = 'Invalid online price';
              } else {
                $imported_records[$key]['NewOnlinePrice'] = $online_price;
              }
            }

            if(count($imported_records[$key]) > 0) {
              $imported_records[$key]['ItemName'] = $item_name;
              $imported_records[$key]['LotNo'] = $lot_no;
            } else {
              $form_errors['itemDetails'][$key]['ItemName'] = 'Minimum one rate is required';
            }
          }
          $cleaned_params['itemDetails'] = $imported_records;
        }
      } elseif(count($imported_records) > 350) {
        $form_errors['fileName'] = '<i class="fa fa-times" aria-hidden="true"></i> Only 350 rows per file are allowed.';
      } else {
        $form_errors['fileName'] = 'No records found.';        
      }
    }

    // dump($cleaned_params, $imported_records);
    // exit;

    if(count($form_errors)>0) {
      return [
        'status' => false,
        'form_errors' => $form_errors,
      ];
    } else {
      return [
        'status' => true,
        'cleaned_params' => $cleaned_params,
      ];
    }
  }
}
