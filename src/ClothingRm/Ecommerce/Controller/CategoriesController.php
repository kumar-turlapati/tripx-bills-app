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

use ClothingRm\Ecommerce\Model\Category;

class CategoriesController {

  private $template, $flash, $gallery_model, $products_model;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash;
    $this->category_model = new Category;
  }  

  // create category
  public function createCategory(Request $request) {
    $form_errors = $submitted_data = [];
    $mod = !is_null($request->get('mod')) ? Utilities::clean_string($request->get('mod')) : 'Category';

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $s3_result = $this->_upload_images_to_s3($cleaned_params['files']);
        if(count($s3_result) > 0) {
          unset($cleaned_params['files']);
          $cleaned_params['files'] = $s3_result;
          $result = $this->category_model->category_create($cleaned_params);
          if($result['status']) {
            $this->flash->set_flash_message('<i class="fa fa-check aria-hidden="true"></i>&nbsp;Category created successfully.');
            Utilities::redirect('/ecom/category/create');
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
      'page_title' => 'Create eCommerce '.ucwords($mod),
      'icon_name' => 'fa fa-files-o',
    );

    // template variables
    $template_vars = array(
      'form_errors' => $form_errors,
      'form_data' => $submitted_data,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'weights' => range(0, 49),
      'categories' => ["-1" => 'Choose']+$this->get_categories(),
      'mod' => ucwords($mod),
    );

    return array($this->template->render_view('category-create',$template_vars),$controller_vars);
  }

  // update category
  public function updateCategory(Request $request) {
    $form_errors = $submitted_data = $existing_data = [];

    $category_id = !is_null($request->get('categoryID')) ? Utilities::clean_string($request->get('categoryID')) : '';
    $mod = !is_null($request->get('mod')) ? Utilities::clean_string($request->get('mod')) : 'Category';

    if($category_id === '') {
      $this->flash->set_flash_message('Invalid Category', 1);
      Utilities::redirect('/ecom/categories/list');      
    } else {
      $category_details = $this->category_model->get_category_details($category_id);
      if($category_details['status'] === false) {
        $this->flash->set_flash_message('Invalid Category', 1);
        Utilities::redirect('/ecom/categories/list');  
      }
      $submitted_data = $existing_data = $category_details['categoryDetails'];
      if($mod === 'subcategory') {
        $parent_id = $submitted_data['parentID'];
      } else {
        $parent_id = 0;
      }
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
        $result = $this->category_model->category_update($cleaned_params, $category_id);
        if($result['status']) {
          $this->flash->set_flash_message('<i class="fa fa-check aria-hidden="true"></i>&nbsp;Category updated successfully.');
          Utilities::redirect('/ecom/categories/list');
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
      'page_title' => 'Update eCommerce '.ucwords($mod),
      'icon_name' => 'fa fa-files-o',
    );

    // template variables
    $template_vars = array(
      'form_errors' => $form_errors,
      'form_data' => $submitted_data,
      'existing_data' => $existing_data,
      'client_code' => $_SESSION['ccode'],
      'weights' => range(0, 49),
      'categories' => ["-1" => 'Choose']+$this->get_categories(),
      'mod' => ucwords($mod),
      'parentID' => $parent_id,
    );

    return array($this->template->render_view('category-update',$template_vars),$controller_vars);
  }

  // delete category
  public function deleteCategory(Request $request) {
    $category_id = !is_null($request->get('categoryID')) ? Utilities::clean_string($request->get('categoryID')) : '';
    if($category_id === '') {
      $this->flash->set_flash_message('Invalid Category', 1);
      Utilities::redirect('/ecom/categories/list');
    }
    $result = $this->category_model->category_delete($category_id);
    if($result['status']) {
      $this->flash->set_flash_message('<i class="fa fa-times aria-hidden="true"></i>&nbsp;Category deleted successfully.');
    } else {
      $this->flash->set_flash_message($result['apierror'], 1);
    }
    Utilities::redirect('/ecom/categories/list');
  }

  // categories list
  public function categoriesList(Request $request) {
    $categories = array(''=>'All Categories');

    $categories_list = $search_params = $categories = [];
    $search_params = [];
    $page_success = $page_error = '';

    $page_no = !is_null($request->get('pageNo')) ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = !is_null($request->get('perPage')) ? Utilities::clean_string($request->get('perPage')) : 50;

    $search_params = [
      'pageNo' => $page_no,
      'perPage' => $per_page,
    ];

    $categories_list = $this->category_model->categories_list($search_params);
    $api_status = $categories_list['status'];
    // check api status
    if($api_status) {
      $categories = $categories_list['categories']['categories'];
    } else {
      $page_error = $categories_list['apierror'];
    }

    // build variables
    $controller_vars = array(
      'page_title' => 'eCommerce Categories',
      'icon_name' => 'fa fa-files-o',
    );

    $template_vars = array(
      'categories' => $categories,
      'search_params' => $search_params,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'flash_obj'  => $this->flash,
    );

    // render template
    return array($this->template->render_view('categories-list',$template_vars),$controller_vars);
  }

  // sub categories list
  public function subCategoriesList(Request $request) {

    $categories = array(''=>'All Categories');

    $categories_list = $search_params = $categories = [];
    $search_params = [];
    $page_success = $page_error = '';

    $page_no = !is_null($request->get('pageNo')) ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = !is_null($request->get('perPage')) ? Utilities::clean_string($request->get('perPage')) : 50;
    $parent = !is_null($request->get('categoryID')) ? Utilities::clean_string($request->get('categoryID')) : 0;

    if($parent === 0) {
      Utilities::redirect('/ecom/categories/list');
    }

    $search_params = [
      'pageNo' => $page_no,
      'perPage' => $per_page,
      'parentID' => $parent
    ];

    $categories_list = $this->category_model->categories_list($search_params);
    $api_status = $categories_list['status'];
    // check api status
    if($api_status) {
      $categories = $categories_list['categories']['categories'];
    } else {
      $page_error = $categories_list['apierror'];
    }

    // build variables
    $controller_vars = array(
      'page_title' => 'eCommerce Subcategories',
      'icon_name' => 'fa fa-files-o',
    );

    $template_vars = array(
      'categories' => $categories,
      'search_params' => $search_params,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'flash_obj'  => $this->flash,
    );

    // render template
    return array($this->template->render_view('sub-categories-list',$template_vars),$controller_vars);
  }

  private function _validate_form_data($form_data = []) {
    $files = $_FILES;
    $one_file_found = false;
    $file_types_a = ['image/jpeg', 'image/gif', 'image/png'];
    $cleaned_params = $form_errors = [];

    $category_name = Utilities::clean_string($form_data['categoryName']);
    $weight = Utilities::clean_string($form_data['weight']);
    $parent = Utilities::clean_string($form_data['parent']);
    $category_desc_short = Utilities::clean_string($form_data['categoryDescShort']);
    $category_desc_long = Utilities::clean_string($form_data['categoryDescLong']);

    if($category_name !== '') {
      $cleaned_params['categoryName'] = $category_name;
    } else {
      $form_errors['categoryName'] = 'Invalid category name.';
    }
    if($weight !== '' && is_numeric($weight)) {
      $cleaned_params['weight'] = $weight;
    } else {
      $form_errors['weight'] = 'Invalid weight.';
    }
    $cleaned_params['parent'] = $parent;
    $cleaned_params['categoryDescShort'] = $category_desc_short;
    $cleaned_params['categoryDescLong'] = $category_desc_long;

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

    $category_name = Utilities::clean_string($form_data['categoryName']);
    $weight = Utilities::clean_string($form_data['weight']);
    $parent = Utilities::clean_string($form_data['parent']);
    $category_desc_short = Utilities::clean_string($form_data['categoryDescShort']);
    $category_desc_long = Utilities::clean_string($form_data['categoryDescLong']);

    if($category_name !== '') {
      $cleaned_params['categoryName'] = $category_name;
    } else {
      $form_errors['categoryName'] = 'Invalid category name.';
    }
    if($weight !== '' && is_numeric($weight)) {
      $cleaned_params['weight'] = $weight;
    } else {
      $form_errors['weight'] = 'Invalid weight.';
    }
    $cleaned_params['parent'] = $parent;
    $cleaned_params['categoryDescShort'] = $category_desc_short;
    $cleaned_params['categoryDescLong'] = $category_desc_long;


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
    // if(!$one_file_found) {
    //   $form_errors['image_0'] = 'Image is required.';
    // }

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
          $key_name = $client_code.'/categories/'.$file_details['file_name'];
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

  // get categories from api.
  private function get_categories() {
    $categories = [];
    $categories_list = $this->category_model->categories_list([]);
    $api_status = $categories_list['status'];
    // check api status
    if($api_status) {
      $category_keys = array_column($categories_list['categories']['categories'], 'categoryID');
      $category_values = array_column($categories_list['categories']['categories'], 'categoryName');
      return array_combine($category_keys, $category_values);
    } else {
      return [];
    }    
  }
} // end of class.
