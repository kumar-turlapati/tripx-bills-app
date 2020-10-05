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

use ClothingRm\Ecommerce\Model\AppContent;

class AppContentController {

  private $template, $flash, $app_content_model;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash;
    $this->app_content_model = new AppContent;
  }  

  // create content
  public function createContent(Request $request) {
    $form_errors = $submitted_data = [];
    $content_categories = ["main-banner" => 'Main Banner', 'hot-sellers' => 'Hot Sellers', 'top-brands' => 'Top Brands'];
    $redirection_a = [0 => 'No', 1=>'Yes'];

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $s3_result = $this->_upload_images_to_s3($cleaned_params['files']);
        if(count($s3_result) > 0) {
          unset($cleaned_params['files']);
          $cleaned_params['files'] = $s3_result;
          $result = $this->app_content_model->content_create($cleaned_params);
          if($result['status']) {
            $this->flash->set_flash_message('<i class="fa fa-check aria-hidden="true"></i>&nbsp;Content created successfully.');
            Utilities::redirect('/ecom/app-content/create');
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
      'page_title' => 'Create App Content',
      'icon_name' => 'fa fa-mobile',
    );

    // template variables
    $template_vars = array(
      'form_errors' => $form_errors,
      'form_data' => $submitted_data,
      'redirection_a' => $redirection_a,
      'categories' => ['' => 'Choose']+$content_categories,
      'weights' => range(0, 49),
    );

    return array($this->template->render_view('app-content-create',$template_vars),$controller_vars);
  }

  // update content
  public function updateContent(Request $request) {
    $form_errors = $submitted_data = $existing_data = [];
    $content_categories = ["main-banner" => 'Main Banner', 'hot-sellers' => 'Hot Sellers', 'top-brands' => 'Top Brands'];
    $redirection_a = [0 => 'No', 1=>'Yes'];

    $content_id = !is_null($request->get('contentID')) ? Utilities::clean_string($request->get('contentID')) : '';

    if($content_id === '') {
      $this->flash->set_flash_message('Empty Content Code', 1);
      Utilities::redirect('/ecom/app-content/list');      
    } else {
      $content_details = $this->app_content_model->get_content_details($content_id);
      if($content_details['status'] === false) {
        $this->flash->set_flash_message('Invalid Content Code', 1);
        Utilities::redirect('/ecom/app-content/list');  
      }
      $submitted_data = $existing_data = $content_details['contentDetails'];
    }

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data_update($submitted_data);
      // dump($form_validation);
      // exit;
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        if(isset($cleaned_params['files'])) {
          $s3_result = $this->_upload_images_to_s3($cleaned_params['files']);
          if(count($s3_result) > 0) {
            unset($cleaned_params['files']);
            $cleaned_params['files'] = $s3_result;
          }
        }
        $result = $this->app_content_model->content_update($cleaned_params, $content_id);
        if($result['status']) {
          $this->flash->set_flash_message('<i class="fa fa-check aria-hidden="true"></i>&nbsp;Content updated successfully.');
          Utilities::redirect('/ecom/app-content/list');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }

      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    // theme variables
    $controller_vars = array(
      'page_title' => 'Update App Content ',
      'icon_name' => 'fa fa-mobile',
    );

    // template variables
    $template_vars = array(
      'form_errors' => $form_errors,
      'form_data' => $submitted_data,
      'existing_data' => $existing_data,
      'client_code' => $_SESSION['ccode'],
      'redirection_a' => $redirection_a,
      'categories' => ['' => 'Choose']+$content_categories,
      'weights' => range(0, 49),
    );

    return array($this->template->render_view('app-content-update',$template_vars),$controller_vars);
  }

  // delete content
  public function deleteContent(Request $request) {
    $content_id = !is_null($request->get('contentID')) ? Utilities::clean_string($request->get('contentID')) : '';
    if($content_id === '') {
      $this->flash->set_flash_message('Invalid Content', 1);
      Utilities::redirect('/ecom/app-content/list');
    }
    $result = $this->app_content_model->content_delete($content_id);
    if($result['status']) {
      $this->flash->set_flash_message('<i class="fa fa-times aria-hidden="true"></i>&nbsp;Content deleted successfully.');
    } else {
      $this->flash->set_flash_message($result['apierror'], 1);
    }
    Utilities::redirect('/ecom/app-content/list');
  }

  // content list
  public function contentList(Request $request) {
    $categories = array(''=>'All Categories');

    $content_list = $search_params = $content = [];
    $search_params = [];
    $page_success = $page_error = '';

    $page_no = !is_null($request->get('pageNo')) ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = !is_null($request->get('perPage')) ? Utilities::clean_string($request->get('perPage')) : 50;

    $search_params = [
      'pageNo' => $page_no,
      'perPage' => $per_page,
    ];

    $content_list = $this->app_content_model->content_list($search_params);
    $api_status = $content_list['status'];
    // check api status
    if($api_status) {
      $content = $content_list['content']['content'];
    } else {
      $page_error = $content_list['apierror'];
    }

    // build variables
    $controller_vars = array(
      'page_title' => 'eCommerce App Content',
      'icon_name' => 'fa fa-mobile',
    );

    $template_vars = array(
      'content' => $content,
      'search_params' => $search_params,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'flash_obj'  => $this->flash,
    );

    // render template
    return array($this->template->render_view('app-content-list',$template_vars),$controller_vars);
  }

  private function _validate_form_data($form_data = []) {
    $files = $_FILES;
    $one_file_found = false;
    $file_types_a = ['image/jpeg', 'image/gif', 'image/png'];
    $cleaned_params = $form_errors = [];

    $content_title = Utilities::clean_string($form_data['contentTitle']);
    $content_category = Utilities::clean_string($form_data['contentCategory']);
    $enable_redirection = Utilities::clean_string($form_data['enableRedirection']);
    $catalog_name = Utilities::clean_string($form_data['catalogName']);
    $item_name = Utilities::clean_string($form_data['itemName']);
    $status = Utilities::clean_string($form_data['status']);
    $weight = Utilities::clean_string($form_data['weight']);

    $cleaned_params['enableRedirection'] = $enable_redirection;
    $cleaned_params['status'] = $status;
    $cleaned_params['weight'] = $weight;
    
    if($content_title !== '') {
      $cleaned_params['contentTitle'] = $content_title;
    } else {
      $form_errors['contentTitle'] = 'Invalid content title.';
    }
    if($content_category !== '') {
      $cleaned_params['contentCategory'] = $content_category;
    } else {
      $form_errors['contentCategory'] = 'Invalid content category.';
    }
    
    if($enable_redirection) {
      if($catalog_name === '' && $item_name === '') {
        $form_errors['catalogName'] = 'Either catalog or item name is required';
      } else {
        $cleaned_params['catalogName'] = $catalog_name;
        $cleaned_params['itemName'] = $item_name;
      }
    } else {
      $cleaned_params['catalogName'] = '';
      $cleaned_params['itemName'] = '';
    }

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
          $cleaned_params['files'][] = ['tmp_name' => $tmp_name, 'file_name' => $file_name, 'content_type' => $file_type, 'is_upload' => true];
          // if( (int)$width === (int)$height) {
          // } else {
          //   $form_errors[$key] = 'Image height and width should be same.';
          // }
        } else {
          $form_errors[$key] = 'Invalid file type or size is more than 2mb.';
        }
      }
    }

    if(!$one_file_found) {
      $form_errors['image_0'] = 'Image is required.';
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

    $content_title = Utilities::clean_string($form_data['contentTitle']);
    $content_category = Utilities::clean_string($form_data['contentCategory']);
    $enable_redirection = Utilities::clean_string($form_data['enableRedirection']);
    $catalog_name = Utilities::clean_string($form_data['catalogName']);
    $item_name = Utilities::clean_string($form_data['itemName']);
    $status = Utilities::clean_string($form_data['status']);
    $weight = Utilities::clean_string($form_data['weight']);

    $cleaned_params['enableRedirection'] = $enable_redirection;
    $cleaned_params['status'] = $status;
    $cleaned_params['weight'] = $weight;

    if($content_title !== '') {
      $cleaned_params['contentTitle'] = $content_title;
    } else {
      $form_errors['contentTitle'] = 'Invalid content title.';
    }
    if($content_category !== '') {
      $cleaned_params['contentCategory'] = $content_category;
    } else {
      $form_errors['contentCategory'] = 'Invalid content category.';
    }
    
    if($enable_redirection) {
      if($catalog_name === '' && $item_name === '') {
        $form_errors['catalogName'] = 'Either catalog or item name is required';
      } else {
        $cleaned_params['catalogName'] = $catalog_name;
        $cleaned_params['itemName'] = $item_name;
      }
    } else {
      $cleaned_params['catalogName'] = '';
      $cleaned_params['itemName'] = '';
    }

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
          $cleaned_params['files'][] = ['tmp_name' => $tmp_name, 'file_name' => $file_name, 'content_type' => $file_type, 'is_upload' => true];
          // if( (int)$width === (int)$height) {
          // } else {
          //   $form_errors[$key] = 'Image height and width should be same.';
          // }
        } else {
          $form_errors[$key] = 'Invalid file type or size is more than 2mb.';
        }
      }
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

  private function _upload_images_to_s3($files = []) {
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
          $key_name = $client_code.'/app-content/'.$file_details['file_name'];
          $upload_result = $s3->putObjectFile($file_details['tmp_name'], 
                                              $s3_config['BUCKET_NAME'], 
                                              $key_name, 
                                              S3::ACL_PUBLIC_READ, 
                                              [], 
                                              $meta_headers
                                            );
          // dump($key_name, $upload_result);
          // exit;
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
