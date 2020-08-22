<?php 

namespace ClothingRm\Ecommerce\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;
use Atawa\Config\Config;
use Atawa\S3;

use ClothingRm\Ecommerce\Model\Gallery;
use ClothingRm\Products\Model\Products;

class GalleryController {

  private $template, $flash, $gallery_model, $products_model;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash;
    $this->gallery_model = new Gallery;
    $this->products_model = new Products;
  }  

  // create gallery
  public function createGallery(Request $request) {
    $form_errors = $submitted_data = [];
    $client_locations = Utilities::get_client_locations(false, false, true);
    $billing_rates = ['mrp' => 'M.R.P', 'wholesale' => 'Wholesale', 'online' => 'Online', 'purch' => 'Exmill'];

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      // validate location code as first priority.
      $location_code = Utilities::clean_string($submitted_data['locationCode']);
      if(!array_key_exists($location_code, $client_locations) || $location_code === '') {
        $this->flash->set_flash_message('<i class="fa fa-exclamation" aria-hidden="true"></i> Invalid location.');
        Utilities::redirect('/ecom/upload-product-image');
      }

      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $cleaned_params['locationCode'] = $location_code;
        $s3_result = $this->_upload_images_to_s3($cleaned_params['files'], $location_code);
        if(count($s3_result) > 0) {
          unset($cleaned_params['files']);
          $cleaned_params['files'] = $s3_result;
          $result = $this->gallery_model->gallery_create($cleaned_params);
          if($result['status']) {
            $this->flash->set_flash_message('<i class="fa fa-check aria-hidden="true"></i>&nbsp;Gallery created successfully.');
            Utilities::redirect('/gallery/create');
          } else {
            $page_error = $result['apierror'];
            $this->flash->set_flash_message($page_error, 1);
          }
        }
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    // theme variables
    $controller_vars = array(
      'page_title' => 'Create Product Gallery',
      'icon_name' => 'fa fa-picture-o',
    );

    // template variables
    $template_vars = array(
      'form_errors' => $form_errors,
      'form_data' => $submitted_data,
      'client_locations' => array(''=>'Choose Store') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'billing_rates' => $billing_rates,
    );

    return array($this->template->render_view('gallery-create',$template_vars),$controller_vars);
  }

  // update gallery
  public function updateGallery(Request $request) {
    $form_errors = $submitted_data = $existing_gallery_details = [];
    $billing_rates = ['mrp' => 'M.R.P', 'wholesale' => 'Wholesale', 'online' => 'Online', 'purch' => 'Exmill'];

    // if form is submitted we don't need location ids.
    if( count($request->request->all())>0 ) {
      $client_locations = Utilities::get_client_locations();
    } else {
      $client_locations = Utilities::get_client_locations(true);
    }
    // dump($client_locations);

    $location_code = !is_null($request->get('locationCode')) ? Utilities::clean_string($request->get('locationCode')) : '';
    $gallery_code = !is_null($request->get('galleryCode')) ? Utilities::clean_string($request->get('galleryCode')) : '';
    if($location_code === '' || $gallery_code === '') {
      $this->flash->set_flash_message('Invalid Gallery (or) Store');
      Utilities::redirect('/galleries/list');
    }

    $gallery_details_response = $this->gallery_model->get_gallery_details($location_code, $gallery_code);
    if(is_array($gallery_details_response) && $gallery_details_response['status']) {
      $existing_gallery_details = $gallery_details_response['galleryDetails'];
      if(isset($existing_gallery_details['locationID']) && (int)$existing_gallery_details['locationID'] > 0) {
        foreach($client_locations as $location_key => $location_value) {
          $location_key_a = explode('`', $location_key);
          if(is_array($location_key_a) && 
             count($location_key_a) === 2 && 
             (int)$existing_gallery_details['locationID'] === (int)$location_key_a[1]) {
            $existing_gallery_details['locationCode'] = $location_key_a[0];
          }
        }
      }
    } else {
      $this->flash->set_flash_message('Invalid Gallery');
      Utilities::redirect('/galleries/list');
    }


    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();

      // validate location code as first priority.
      $location_code = Utilities::clean_string($submitted_data['locationCode']);
      if(!array_key_exists($location_code, $client_locations) || $location_code === '') {
        $this->flash->set_flash_message('<i class="fa fa-exclamation" aria-hidden="true"></i> Invalid location.');
        Utilities::redirect('/galleries/list');
      }

      $form_validation = $this->_validate_form_data_update($submitted_data, $existing_gallery_details);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $cleaned_params['locationCode'] = $location_code;
        $s3_result = $this->_upload_images_to_s3($cleaned_params['files'], $location_code);
        if(count($s3_result) > 0) {
          unset($cleaned_params['files']);
          $cleaned_params['files'] = $s3_result;
          $result = $this->gallery_model->gallery_update($cleaned_params, $gallery_code);
          if($result['status']) {
            $this->flash->set_flash_message('<i class="fa fa-check aria-hidden="true"></i>&nbsp;Gallery updated successfully.');
            Utilities::redirect('/gallery/update/'.$location_code.'/'.$gallery_code);
          } else {
            $page_error = $result['apierror'];
            $this->flash->set_flash_message($page_error, 1);
          }
        }
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    // theme variables
    $controller_vars = array(
      'page_title' => 'Update Product Gallery',
      'icon_name' => 'fa fa-picture-o',
    );

    // template variables
    $template_vars = array(
      'form_errors' => $form_errors,
      'form_data' => $submitted_data,
      'client_locations' => array(''=>'Choose Store') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'existing_gallery_details' => $existing_gallery_details,
      'billing_rates' => $billing_rates,
    );

    return array($this->template->render_view('gallery-update',$template_vars),$controller_vars);
  }

  // delete gallery
  public function deleteGallery(Request $request) {
    $gallery_code = !is_null($request->get('galleryCode')) ? Utilities::clean_string($request->get('galleryCode')) : '';
    $location_code = !is_null($request->get('locationCode')) ? Utilities::clean_string($request->get('locationCode')) : '';
    $page_no = !is_null($request->get('pageNo')) && is_numeric($request->get('pageNo')) ? Utilities::clean_string($request->get('pageNo')) : 1;
    if($gallery_code === '' || $location_code === '') {
      $this->flash->set_flash_message('Invalid Gallery or location');
      Utilities::redirect('/galleries/list');
    }
    $result = $this->gallery_model->gallery_delete($location_code, $gallery_code);
    if($result['status']) {
      $this->flash->set_flash_message('<i class="fa fa-times aria-hidden="true"></i>&nbsp;Gallery deleted successfully.');
    } else {
      $this->flash->set_flash_message($result['apierror'], 1);
    }
    Utilities::redirect('/galleries/list/'.$page_no.'?locationCode='.$location_code);
  }

  // galleries list
  public function galleriesList(Request $request) {
    $categories = array(''=>'All Categories');

    $galleries_list = $search_params = $products = $location_ids = $location_codes = [];
    $search_params = [];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';

    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';

    $page_no = !is_null($request->get('pageNo')) ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = !is_null($request->get('perPage')) ? Utilities::clean_string($request->get('perPage')) : 100;
    $item_name = !is_null($request->get('itemName')) ? Utilities::clean_string($request->get('itemName')) : '';
    $category = !is_null($request->get('category')) ? Utilities::clean_string($request->get('category')) : '';
    $mfg = !is_null($request->get('mfg')) ? Utilities::clean_string($request->get('mfg')) : '';
    $location_code = $request->get('locationCode')!== null ? Utilities::clean_string($request->get('locationCode')) : $default_location;

    $search_params = [
      'itemName' => $item_name,
      'category' => $category,
      'mfg' => $mfg,
      'locationCode' => $location_code,
      'pageNo' => $page_no,
      'perPage' => $per_page,
    ];

    // ---------- get location codes from api -----------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    $galleries_list = $this->gallery_model->galleries_list($search_params);
    $api_status = $galleries_list['status'];
    $client_id = Utilities::get_current_client_id();
    $categories = array('' => 'All Categories') + $this->products_model->get_product_categories($location_code);

    // check api status
    if($api_status) {
      // check whether we got products or not.
      if(count($galleries_list['galleries']['items']) >0) {
        $slno = Utilities::get_slno_start(count($galleries_list['galleries']['items']), $per_page, $page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;            
        }
        if($galleries_list['galleries']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $galleries_list['galleries']['total_pages'];
        }
        if(count($galleries_list['galleries']['items']) < $per_page) {
          $to_sl_no = ($slno+count($galleries_list['galleries']['items']))-1;
        }
        $products = $galleries_list['galleries']['items'];
        $total_pages = $galleries_list['galleries']['total_pages'];
        $total_records = $galleries_list['galleries']['total_records'];
        $record_count = $galleries_list['galleries']['total_records'];
      } else {
        $page_error = $galleries_list['apierror'];
      }
    } else {
      $page_error = $galleries_list['apierror'];
    }

    // build variables
    $controller_vars = array(
      'page_title' => 'Product Galleries',
      'icon_name' => 'fa fa-picture-o',
    );

    $template_vars = array(
      'products' => $products,
      'categories' => $categories,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'flash_obj'  => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'location_ids' => $location_ids,
      'location_code' => $location_code,
      'location_codes' => $location_codes,
      'page_no' => $page_no, 
    );

    // render template
    return array($this->template->render_view('galleries-list',$template_vars),$controller_vars);;
  }


  private function _validate_form_data($form_data = []) {
    $files = $_FILES;
    $one_file_found = false;
    $file_types_a = ['image/jpeg', 'image/gif', 'image/png'];
    $cleaned_params = $form_errors = [];

    $item_name = Utilities::clean_string($form_data['itemName']);
    $style_code = Utilities::clean_string($form_data['itemStylecode']);
    $item_color =  Utilities::clean_string($form_data['itemColor']);
    $item_description = Utilities::clean_string($form_data['itemDescription']);
    $billing_rate = Utilities::clean_string($form_data['billingRate']);
    $packed_qty = Utilities::clean_string($form_data['packedQty']);
    $brand_url = Utilities::clean_string($form_data['brandUrl']);

    if($item_name !== '') {
      $cleaned_params['itemName'] = $item_name;
    } else {
      $form_errors['itemName'] = 'Invalid item name.';
    }
    if($style_code !== '') {
      $cleaned_params['itemStylecode'] = $style_code;
    } else {
      $form_errors['itemStylecode'] = 'Invalid style code.';
    }
    if($item_description !== '') {
      $cleaned_params['itemDescription'] = $item_description;
    } else {
      $form_errors['itemDescription'] = 'Invalid item description.';
    }
    if(floatval($packed_qty) > 0) {
      $cleaned_params['packedQty'] = $packed_qty;
    } else {
      $form_errors['packedQty'] = 'Invalid packed qty.';
    }

    $cleaned_params['itemColor'] = $item_color;
    $cleaned_params['billingRate'] = $billing_rate;
    $cleaned_params['brandUrl'] = $brand_url;

    foreach($files as $key => $file_details) {
      $file_name = $file_details['name'];
      $tmp_name = $file_details['tmp_name'];
      $file_type = $file_details['type'];
      $file_size = $file_details['size'];
      if($file_name !== '') {
        $one_file_found = true;
        // check file type matches and size is under 2mb.
        if($file_size <= 2000000 && in_array($file_type, $file_types_a)) {
          list($width, $height) = getimagesize($tmp_name);
          if( (int)$width === (int)$height) {
            $cleaned_params['files'][] = ['tmp_name' => $tmp_name, 'file_name' => $file_name, 'content_type' => $file_type, 'is_upload' => true];
          } else {
            $form_errors[$key] = 'Image height and width should be same.';
          }
        } else {
          $form_errors[$key] = 'Invalid file type or size is more than 2mb.';
        }
      }
    }

    if(!$one_file_found) {
      $form_errors['images'] = 'At least one image is required for an item.';
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

  private function _validate_form_data_update($form_data = [], $existing_gallery_details=[]) {
    $files = $_FILES;
    $one_file_found = false;
    $file_types_a = ['image/jpeg', 'image/gif', 'image/png'];
    $cleaned_params = $form_errors = [];

    $item_name = Utilities::clean_string($form_data['itemName']);
    $style_code = Utilities::clean_string($form_data['itemStylecode']);
    $item_color =  Utilities::clean_string($form_data['itemColor']);
    $item_description = Utilities::clean_string($form_data['itemDescription']);
    $billing_rate = Utilities::clean_string($form_data['billingRate']);
    $delete_images = isset($form_data['delImage']) && count($form_data['delImage']) > 0 ? $form_data['delImage'] : [];
    $weight_a = isset($form_data['weight']) && count($form_data['weight']) > 0 ? $form_data['weight'] : [];
    $packed_qty = Utilities::clean_string($form_data['packedQty']);
    $brand_url = Utilities::clean_string($form_data['brandUrl']);

    if($item_name !== '') {
      $cleaned_params['itemName'] = $item_name;
    } else {
      $form_errors['itemName'] = 'Invalid item name.';
    }
    if($style_code !== '') {
      $cleaned_params['itemStylecode'] = $style_code;
    } else {
      $form_errors['itemStylecode'] = 'Invalid style code.';
    }
    if($item_description !== '') {
      $cleaned_params['itemDescription'] = $item_description;
    } else {
      $form_errors['itemDescription'] = 'Invalid item description.';
    }
    if(floatval($packed_qty) > 0) {
      $cleaned_params['packedQty'] = $packed_qty;
    } else {
      $form_errors['packedQty'] = 'Invalid packed qty.';
    }
    
    $cleaned_params['billingRate'] = $billing_rate;
    $cleaned_params['brandUrl'] = $brand_url;

    // get uploaded files.
    foreach($files as $key => $file_details) {
      $file_name = $file_details['name'];
      $tmp_name = $file_details['tmp_name'];
      $file_type = $file_details['type'];
      $file_size = $file_details['size'];
      if($file_name !== '') {
        $one_file_found = true;
        // check file type matches and size is under 2mb.
        if($file_size <= 2000000 && in_array($file_type, $file_types_a)) {
          list($width, $height) = getimagesize($tmp_name);
          if( (int)$width === (int)$height) {
            $cleaned_params['files'][] = ['tmp_name' => $tmp_name, 'file_name' => $file_name, 'content_type' => $file_type, 'is_upload' => true];
          } else {
            $form_errors[$key] = 'Image height and width should be same.';
          }
        } else {
          $form_errors[$key] = 'Invalid file type or size is more than 2mb.';
        }
      } else {
        $key_a = explode('_', $key);
        $image_key = $key_a[1];
        if(isset($existing_gallery_details['images'][$image_key])) {
          $ex_image_details = $existing_gallery_details['images'][$image_key];
          $cleaned_params['files'][] = [
            'file_name' => $ex_image_details['imageName'], 
            'content_type' => $ex_image_details['type'], 
            'date_uploaded' => $ex_image_details['dateUploaded'],
            'hash' => $ex_image_details['hash'],
            'is_upload' => false,
            'weight' => isset($weight_a[$image_key]) ? $weight_a[$image_key] : $ex_image_details['weight'],
            'status' => isset($delete_images[$image_key]) && (int)$delete_images[$image_key] === 0 ? 0 : 1,
          ];
        }
      }
    }

    $cleaned_params['itemColor'] = $item_color;

    // dump($cleaned_params);
    // exit;

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

  private function _upload_images_to_s3($files = [], $location_code='') {
    $s3_urls = [];
    $s3_config = Config::get_s3_details();
    $s3 = new S3($s3_config['IAM_KEY'], $s3_config['IAM_SECRET'], false, $s3_config['END_POINT_FULL'], $s3_config['END_POINT_SHORT']);

    $client_code = isset($_SESSION['ccode']) && $_SESSION['ccode'] !== '' ? $_SESSION['ccode'] : '';

    // dump($files, 'in s3 function....');
    // exit;

    if(is_array($files) && count($files) > 0) {
      foreach($files as $file_key => $file_details) {
        $is_uploaded = $file_details['is_upload'];
        
        if($is_uploaded) {
          $meta_headers = [
            'Content-Disposition' => "inline; filename=$file_details[file_name]",
            'Content-Type' => $file_details['content_type'],
          ];
          $key_name = $client_code.'/'.$location_code.'/'.$file_details['file_name'];
          $upload_result = $s3->putObjectFile($file_details['tmp_name'], 
                                              $s3_config['BUCKET_NAME'], 
                                              $key_name, 
                                              S3::ACL_PUBLIC_READ, 
                                              [], 
                                              $meta_headers
                                            );
          if($upload_result) {
            $info = $s3->getObjectInfo($s3_config['BUCKET_NAME'], $key_name);
            $info['status'] = 1;
            $s3_urls[] = ['file_name' => $file_details['file_name'], 'uploaded' => $info];
          }
        // create uploaded object....
        } else {
          $uploaded = [];
          $uploaded['date'] = $file_details['date_uploaded'];
          $uploaded['time'] = $file_details['date_uploaded'];
          $uploaded['hash'] = $file_details['hash'];
          $uploaded['type'] = $file_details['content_type'];
          $uploaded['status'] = $file_details['status'];
          $uploaded['weight'] = $file_details['weight'];
          $s3_urls[] = ['file_name' => $file_details['file_name'], 'uploaded' => $uploaded];
        }
      }
    }

    return $s3_urls;
  }

} // end of class.
