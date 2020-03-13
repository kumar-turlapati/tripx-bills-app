<?php 

namespace ClothingRm\Inward\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;
use Atawa\Importer;

use ClothingRm\Suppliers\Model\Supplier;
use ClothingRm\Taxes\Model\Taxes;
use ClothingRm\Inward\Model\Inward;
use User\Model\User;

class InwardBulkUploadController
{
  private $template, $supplier_model;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->supplier_model = new Supplier;
    $this->taxes_model = new Taxes;
    $this->inward_model = new Inward;
    $this->flash = new Flash;
    $this->user_model = new User;
  }

  # inward entry action
  public function inwardEntryBulkUploadAction(Request $request) {

    $credit_days_a = $suppliers_a = $payment_methods = $client_locations = [];
    $taxes_a = $taxes = $taxes_raw = [];
    $form_errors = $form_data = [];
    $api_error = '';
    
    $total_item_rows = 30;

    for($i=1;$i<=365;$i++) {
      $credit_days_a[$i] = $i;
    }

    $suppliers = $this->supplier_model->get_suppliers(0,0,[]);
    if($suppliers['status']) {
      $suppliers_a += $suppliers['suppliers'];
    }

    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }

    # get client details
    $client_details = Utilities::get_client_details();
    $client_business_state = $client_details['locState'];

    # get client locations
    $client_locations_resp = $this->user_model->get_client_locations();
    if($client_locations_resp['status']) {
      foreach($client_locations_resp['clientLocations'] as $loc_details) {
        $client_locations[$loc_details['locationCode']] = $loc_details['locationName'];
      }
    }

    # check if form is submitted.
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $validation_status = $this->_validate_form_data($submitted_data,$client_business_state);
      if($validation_status['status']) {
        $cleaned_params = $validation_status['cleaned_params'];
        # if everything is fine redirect to inward entry page.
        if(isset($_SESSION['inwardBulkUpload'])) {
          unset($_SESSION['inwardBulkUpload']);
        }
        $session_token = Utilities::generate_unique_string(20);
        $_SESSION['inwardBulkUpload'] = array(
          'token' => $session_token,
          'uploadedData' => $cleaned_params,
        );

        if(isset($cleaned_params['itemDetails']) && count($cleaned_params['itemDetails']) > 0) {
          $total_rows = count($cleaned_params['itemDetails']);
        } else {
          $total_rows = 25;
        }
        $this->flash->set_flash_message('Please check below details and click on Save button to Save this inward.');        
        Utilities::redirect('/inward-entry?bupToken='.$session_token.'&tr='.$total_rows);
      } else {
        $form_errors = $validation_status['form_errors'];
        $form_data = $submitted_data;
      }
    }

    # theme variables.
    $controller_vars = array(
      'page_title' => 'Inward Material Entry - Upload from files',
      'icon_name' => 'fa fa-upload',
    );
    $template_vars = array(
      'utilities' => new Utilities,
      'credit_days_a' => array(0=>'Choose')+$credit_days_a,
      'suppliers' => array(''=>'Choose')+$suppliers_a,
      'payment_methods' => Constants::$PAYMENT_METHODS_PURCHASE,
      'taxes' => $taxes,
      'taxes_raw' => $taxes_raw,
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'total_item_rows' => $total_item_rows,
      'api_error' => $api_error,
      'states_a' => array(0=>'Choose') + Constants::$LOCATION_STATES,
      'supply_type_a' => array('' => 'Choose', 'inter' => 'Interstate', 'intra' => 'Intrastate'),
      'client_business_state' => $client_business_state,
      'client_locations' => array(''=>'Choose') + $client_locations,
    );

    return array($this->template->render_view('inward-entry-bulk-upload',$template_vars),$controller_vars);
  }
  
  /**************************************** Private functions ***********************************/
  private function _validate_form_data($form_data=[], $client_business_state='') {

    $form_errors = $cleaned_params = $missing_fields = $imp_record_errors = [];
    $allowed_extensions = ['xls', 'ods', 'xlsx'];
    $inward_upload_fields = ["MatchedItemName", "ItemQty", "ItemRate", "DiscountAmount", "TaxableValue", "TaxPercent", "SellingPriceOrMRP", "HsnSacCode"];

    $is_one_item_found = false;
    $all_fields_exists = true;

    $cleaned_params['purchaseDate'] = Utilities::clean_string($form_data['purchaseDate']);
    $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);

    # validate supplier name
    if( isset($form_data['supplierID']) && $form_data['supplierID'] === '') {
      $form_errors['supplierID'] = 'Invalid supplier name.';
    } else {
      $supplier_code = Utilities::clean_string($form_data['supplierID']);
      $supplier_details = $this->supplier_model->get_supplier_details($supplier_code);
      if($supplier_details['status']) {
        $cleaned_params['supplierStateID'] = $supplier_details['supplierDetails']['stateCode'];
        $cleaned_params['supplierGSTNo'] = $supplier_details['supplierDetails']['tinNo'];
        $cleaned_params['supplierID'] = $supplier_code;
        if((int)$client_business_state !== (int)$supplier_details['supplierDetails']['stateCode']) {
          $cleaned_params['supplyType'] = 'inter';
        } else {
          $cleaned_params['supplyType'] = 'intra';
        }
      }
    }

    # validate PO No
/*    if( isset($form_data['poNo']) && $form_data['poNo'] === '') {
      $form_errors['poNo'] = 'PO number is mandatory.';
    } else {
      $cleaned_params['poNo'] = Utilities::clean_string($form_data['poNo']);
    }*/

    # validate payment method
    if( isset($form_data['paymentMethod']) && (int)$form_data['paymentMethod'] === 1) {
      $credit_days = (int)$form_data['creditDays'];
      if($credit_days>0) {
        $cleaned_params['creditDays'] = $credit_days;
        $cleaned_params['paymentMethod'] = 1;
      } else {
        $form_errors['creditDays'] = 'Credit days are mandatory.';
      }
    } else {
      $cleaned_params['paymentMethod'] = Utilities::clean_string($form_data['paymentMethod']);
    }

    # validate location code
    if( isset($form_data['locationCode']) && ctype_alnum($form_data['locationCode']) ) {
      $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);
    } else {
      $form_errors['locationCode'] = 'Invalid location code.';
    }

    # validate payment method
    if( isset($form_data['paymentMethod']) && (int)$form_data['paymentMethod'] === 1) {
      $credit_days = (int)$form_data['creditDays'];
      if($credit_days>0) {
        $cleaned_params['creditDays'] = $credit_days;
        $cleaned_params['paymentMethod'] = 1;
      } else {
        $form_errors['creditDays'] = 'Credit days are mandatory.';
      }
    } else {
      $cleaned_params['paymentMethod'] = 0;
      $cleaned_params['creditDays'] = 0;
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
        $form_errors['fileName'] = 'Invalid file uploaded. Only (.ods, .xls, .xlsx) file formats are allowed';
      }

      # upload file to server
      $file_upload_path = __DIR__.'/../../../../bulkuploads/inward';
      $storage = new \Upload\Storage\FileSystem($file_upload_path);
      $file = new \Upload\File('fileName', $storage);

      $uploaded_file_name = $file->getNameWithExtension();
      $uploaded_file_ext = $file->getExtension();
      if(!in_array($uploaded_file_ext, $allowed_extensions)) {
        $form_errors['fileName'] = 'Invalid file extesion after uploading file.';
      }

      # upload file.
      $new_filename = 'inwardUpload_'.time();
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
      if(is_array($imported_records) && count($imported_records) > 0 && count($imported_records) <= 50 ) {
        $fields_extracted = array_keys($imported_records[0]);
        # check whether all the fields are existing or not.
        foreach($inward_upload_fields as $field_name) {
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
            // dump($item_details);
            $item_name = Utilities::clean_string($item_details['MatchedItemName']);
            $qty = (float)Utilities::clean_string($item_details['ItemQty']);
            $mrp = (float)Utilities::clean_string($item_details['SellingPriceOrMRP']);
            $item_rate = (float)Utilities::clean_string($item_details['ItemRate']);
            $tax_percent = (float)Utilities::clean_string($item_details['TaxPercent']);
            $item_discount = (float)Utilities::clean_string($item_details['DiscountAmount']);
            $hsn_sac_code = Utilities::clean_string($item_details['HsnSacCode']);
            $packed_qty = Utilities::clean_string($item_details['PackedQty']);
            $category_name = Utilities::clean_string($item_details['CategoryName']);
            $rack_no = Utilities::clean_string($item_details['RackNo']);
            $brand_name = Utilities::clean_string($item_details['BrandName']);
            $cno = Utilities::clean_string($item_details['ContainerOrCaseNo']);
            $uom_name = Utilities::clean_string($item_details['UomName']);
            $barcode = Utilities::clean_string($item_details['Barcode']);
            $item_sku = isset($item_details['ItemSku']) ? Utilities::clean_string($item_details['ItemSku']) : '';
            $item_style_code = isset($item_details['ItemStyleCode']) ? Utilities::clean_string($item_details['ItemStyleCode']) : '';
            $item_size = isset($item_details['ItemSize']) ? Utilities::clean_string($item_details['ItemSize']) : '';
            $item_color = isset($item_details['ItemColor']) ? Utilities::clean_string($item_details['ItemColor']) : '';
            $item_sleeve = isset($item_details['ItemSleeve']) ? Utilities::clean_string($item_details['ItemSleeve']) : '';
            $batch_no = isset($item_details['BatchNo']) ? Utilities::clean_string($item_details['BatchNo']) : '';
            $expiry_date = isset($item_details['ExpiryDate']) ? Utilities::clean_string($item_details['ExpiryDate']) : ''; 
            $wholesale_price = isset($item_details['WholesalePrice']) && is_numeric($item_details['WholesalePrice']) ? Utilities::clean_string($item_details['WholesalePrice']) : '';
            $online_price = isset($item_details['OnlinePrice']) && is_numeric($item_details['OnlinePrice'])? Utilities::clean_string($item_details['OnlinePrice']) : ''; 

            $imported_records[$key]['ContainerOrCaseNo'] = $cno;
            $imported_records[$key]['UomName'] = $uom_name;
            $imported_records[$key]['Barcode'] = $barcode;
            $imported_records[$key]['ItemSku'] = $item_sku;
            $imported_records[$key]['ItemStyleCode'] = $item_style_code;
            $imported_records[$key]['ItemSize'] = $item_size;
            $imported_records[$key]['ItemColor'] = $item_color;
            $imported_records[$key]['ItemSleeve'] = $item_sleeve;
            $imported_records[$key]['BatchNo'] = $batch_no;

            if($item_name === '') {
              $form_errors['itemDetails'][$key]['MatchedItemName'] = 'Matched item name is required.';
            } else {
              $imported_records[$key]['MatchedItemName'] = $item_name;
            }
            if($qty <= 0) {
              $form_errors['itemDetails'][$key]['ItemQty'] = 'Invalid item qty.';
            } else {
              $imported_records[$key]['ItemQty'] = $qty;
            }
            if($mrp <= 0) {
              $form_errors['itemDetails'][$key]['SellingPriceOrMRP'] = 'Invalid selling price / mrp.';
            } else {
              $imported_records[$key]['SellingPriceOrMRP'] = $mrp;
            }
            if($item_rate <= 0) {
              $form_errors['itemDetails'][$key]['ItemRate'] = 'Invalid item rate.';
            } else {
              $imported_records[$key]['ItemRate'] = $item_rate;
            }
            if($tax_percent <= 0) {
              $form_errors['itemDetails'][$key]['TaxPercent'] = 'Invalid tax percent.';
            } else {
              $imported_records[$key]['TaxPercent'] = $tax_percent;
            }
            if($item_discount !== '' && $item_discount < 0) {
              $form_errors['itemDetails'][$key]['DiscountAmount'] = 'Invalid discount value.';
            } elseif($item_discount > 0) {
              $imported_records[$key]['DiscountAmount'] = $item_discount;
            } else {
              $imported_records[$key]['DiscountAmount'] = 0;
            }
            if($item_discount !== '' && $item_discount < 0) {
              $form_errors['itemDetails'][$key]['DiscountAmount'] = 'Invalid discount value.';
            } elseif($item_discount > 0) {
              $imported_records[$key]['DiscountAmount'] = $item_discount;
            } else {
              $imported_records[$key]['DiscountAmount'] = 0;
            }
            if($packed_qty !== '' && $packed_qty > 0) {
              $imported_records[$key]['PackedQty'] = $packed_qty;
            } else {
              $form_errors['itemDetails'][$key]['PackedQty'] = 'Invalid Packed Qty.';
            }            
            if($hsn_sac_code !== '') {
              if(!is_numeric(str_replace([' '], '', $hsn_sac_code))) {
                $form_errors['itemDetails'][$key]['HsnSacCode'] = 'Invalid HSN/SAC code.';
              } else {
                $imported_records[$key]['HsnSacCode'] = $hsn_sac_code;
              }
            } else {
              $imported_records[$key]['HsnSacCode'] = '';
            }
            if($category_name !== '') {
              $imported_records[$key]['CategoryName'] = $category_name;
            } else {
              $imported_records[$key]['CategoryName'] = '';
            }
            if($rack_no !== '') {
              $imported_records[$key]['RackNo'] = $rack_no;
            } else {
              $imported_records[$key]['RackNo'] = '';
            }
            if($brand_name !== '') {
              $imported_records[$key]['BrandName'] = $brand_name;
            } else {
              $imported_records[$key]['BrandName'] = '';
            }
            if($expiry_date !== '') {
              $expiry_date_cleaned = str_replace('D', '', $expiry_date);
              echo $expiry_date_cleaned;
              $exp_date_a = explode('-', $expiry_date_cleaned);
              if(is_array($exp_date_a) && count($exp_date_a) === 3 && checkdate($exp_date_a[1], $exp_date_a[0], $exp_date_a[2])) {
                $imported_records[$key]['ExpiryDate'] = $expiry_date_cleaned;
              }
            }
            if($wholesale_price !== '') {
              if($wholesale_price <= 0) {
                $form_errors['itemDetails'][$key]['WholesalePrice'] = 'Invalid wholesale price.';
              } else {
                $imported_records[$key]['WholesalePrice'] = $wholesale_price;
              }
            } else {
              $imported_records[$key]['WholesalePrice'] = 0;
            }
            if($online_price !== '') {
              if($online_price <= 0) {
                $form_errors['itemDetails'][$key]['OnlinePrice'] = 'Invalid online price.';
              } else {
                $imported_records[$key]['OnlinePrice'] = $online_price;
              }
            } else {
              $imported_records[$key]['OnlinePrice'] = 0;
            }
          }
          $cleaned_params['itemDetails'] = $imported_records;
        }
      } elseif(count($imported_records) > 0) {
        $form_errors['fileName'] = '<i class="fa fa-times" aria-hidden="true"></i> Only 50 rows per file is allowed.';        
      } else {
        $form_errors['fileName'] = 'Unable to retrieve rows.';        
      }
    }

    // dump($cleaned_params);
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
