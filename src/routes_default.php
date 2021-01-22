<?php

use Symfony\Component\Routing;
use Symfony\Component\HttpFoundation\Response;

$routes = new Routing\RouteCollection();
$routes->add('default_route', new Routing\Route('/', array(
  '_controller' => 'User\\Controller\\LoginController::indexAction',
)));
$routes->add('login', new Routing\Route('/login', array(
  '_controller' => 'User\\Controller\\LoginController::indexAction',
)));
$routes->add('forgot_password', new Routing\Route('/forgot-password', array(
  '_controller' => 'User\\Controller\\LoginController::forgotPasswordAction',
)));
$routes->add('reset_password', new Routing\Route('/reset-password', array(
  '_controller' => 'User\\Controller\\LoginController::resetPasswordAction',
)));
$routes->add('send_otp', new Routing\Route('/send-otp', array(
  '_controller' => 'User\\Controller\\LoginController::sendOTPAction',
)));
$routes->add('error_device', new Routing\Route('/error-device', array(
  '_controller' => 'User\\Controller\\DashBoardController::errorActionDevice',
)));
$routes->add('qbId', new Routing\Route('/id__mapper', array(
  '_controller' => 'User\\Controller\\LoginController::idMapper',
)));
$routes->add('logout', new Routing\Route('/logout', array(
  '_controller' => 'User\\Controller\\LoginController::logoutAction',
)));

// additional routes for apps
$routes->add('report_printIndent_app', new Routing\Route('/app-print-indent', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsIndentController::printIndentApp'
)));

$routes->add('report_printIndentWr_app', new Routing\Route('/app-print-indent-wor', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsIndentController::printIndentWoRateApp'
)));

return $routes;