<?php

namespace Atawa;

class Flash
{

  /**
   * set flash message to be used on other page.
   *
   * @param str $message
   */
  public function set_flash_message($message = '', $error=0) {
    if(isset($_SESSION['__FLASH'])) {
      unset($_SESSION['__FLASH']);
    }
    $_SESSION['__FLASH']['message'] = $message;
    $_SESSION['__FLASH']['error']   = $error;
  }

  /**
   * get flash message to be used on other page.
   *
   * @param str $message
   */
  public function get_flash_message() {
    if(isset($_SESSION['__FLASH'])) {
      $message = $_SESSION['__FLASH']['message'];
      $status  = $_SESSION['__FLASH']['error'];
      return array('message'=>$message, 'error'=>$status);
    } else {
      return '';
    }
  }

  /**
   * print flash message to be used on other page.
   *
   * @param str $message
   */
  public function print_flash_message($return=true) {
    $flash                  =   $this->get_flash_message();
    if(is_array($flash) && count($flash)>0) {
      $flash_message_error  =   $flash['error'];
      $flash_message        =   $flash['message'];
    } else {
      $flash_message        =   '';
    }

    if($flash_message != '' && $flash_message_error) {
      $message =  "<div class='alert alert-danger' role='alert'>
      							<strong>$flash_message</strong>
                  </div>";
    } elseif($flash_message != '') {
      $message =  "<div class='alert alert-success messageContainer' role='alert'>
      								<strong>$flash_message</strong>
                   </div>";
    } else {
    	$message = '';
    }
    
    unset($_SESSION['__FLASH']);

    if($return) {
      return $message;
    } else {
      echo $message;
    }
  }
}