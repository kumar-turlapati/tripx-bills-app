<?php

use Symfony\Component\Routing;
use Symfony\Component\HttpFoundation\Response;

$routes = new Routing\RouteCollection();

/* default routes available in every routes file */
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
$routes->add('qbId', new Routing\Route('/id__mapper', array(
  '_controller' => 'User\\Controller\\LoginController::idMapper',
)));
$routes->add('auto_logout', new Routing\Route('/__id__lo', array(
  '_controller' => 'User\\Controller\\LoginController::autoLogout',
)));

$routes->add('logout', new Routing\Route('/logout', array(
  '_controller' => 'User\\Controller\\LoginController::logoutAction',
)));
$routes->add('me', new Routing\Route('/me', array(
  '_controller' => 'User\\Controller\\UserController::editProfileAction',
)));
$routes->add('dashboard', new Routing\Route('/dashboard', array(
  '_controller' => 'User\\Controller\\DashBoardController::indexAction',
)));

// sales combo
$routes->add('sales_combo_add', new Routing\Route('/sales-combo/add', array(
  '_controller' => 'ClothingRm\\SalesCombos\\Controller\\SalesCombosController::createSalesCombo',
)));
$routes->add('sales_combo_update', new Routing\Route('/sales-combo/update/{comboCode}', array(
  '_controller' => 'ClothingRm\\SalesCombos\\Controller\\SalesCombosController::updateSalesCombo',
  'comboCode' => null,
)));
$routes->add('sales_combo_list', new Routing\Route('/sales-combo/list', array(
  '_controller' => 'ClothingRm\\SalesCombos\\Controller\\SalesCombosController::listSalesCombo'
)));
$routes->add('sales_entry_combos', new Routing\Route('/sales-entry/combos', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\SalesEntryComboController::salesEntryAction',
)));
$routes->add('gate_pass_entry', new Routing\Route('/gate-pass/entry', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\GatePassController::gatePassEntryAction',
)));
$routes->add('gate_pass_delete', new Routing\Route('/gate-pass/remove/{salesCode}', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\GatePassController::gatePassRemoveAction',
  'salesCode' => null,
)));
$routes->add('get_invoice_no', new Routing\Route('/get-invoice-no', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\GatePassController::getInvoiceNo',
)));
$routes->add('gatepass_register', new Routing\Route('/gate-pass/register/{pageNo}', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\SalesEntryWoBarcode::gatePassRegisterAction',
  'pageNo' => null,
)));

// discount manager
$routes->add('discount_manager', new Routing\Route('/discount-manager/{pageNo}', array(
  '_controller' => 'ClothingRm\\DiscountManager\\Controller\\DiscountManagerController::discountManager',
  'pageNo' => null,
)));
$routes->add('discount_entry_upsert', new Routing\Route('/discount-manager/upsert', array(
  '_controller' => 'ClothingRm\\DiscountManager\\Controller\\DiscountManagerController::upsertDiscountEntry',
)));
$routes->add('discount_entry_delete', new Routing\Route('/discount-manager-delete', array(
  '_controller' => 'ClothingRm\\DiscountManager\\Controller\\DiscountManagerController::deleteDiscountEntry',
)));

// stock audit controller
$routes->add('stock_audit_add', new Routing\Route('/stock-audit/create', array(
  '_controller' => 'ClothingRm\\StockAudit\\Controller\\StockAuditController::createStockAudit',
)));
$routes->add('stock_audit_update', new Routing\Route('/stock-audit/update/{auditCode}', array(
  '_controller' => 'ClothingRm\\StockAudit\\Controller\\StockAuditController::updateStockAudit',
  'auditCode' => null,
)));
$routes->add('stock_audit_print', new Routing\Route('/stock-audit/print/{auditCode}', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Inventory\\Controller\\InventoryReportsController::printStockAuditReport',
  'auditCode' => null,
)));
$routes->add('stock_audit_list', new Routing\Route('/stock-audit/register', array(
  '_controller' => 'ClothingRm\\StockAudit\\Controller\\StockAuditController::stockAuditsRegister'
)));
$routes->add('stock_audit_items_list', new Routing\Route('/stock-audit/items/{auditCode}/{pageNo}', array(
  '_controller' => 'ClothingRm\\StockAudit\\Controller\\StockAuditController::stockAuditItems',
  'auditCode' => null,
  'pageNo' => null,
)));

// fin year routes
$routes->add('finy_add', new Routing\Route('/finy/create', array(
  '_controller' => 'Settings\\Controller\\FinyController::createFinYear',
)));
$routes->add('finy_update', new Routing\Route('/finy/update/{finyCode}', array(
  '_controller' => 'Settings\\Controller\\FinyController::updateFinYear',
  'finyCode' => null,
)));
$routes->add('finy_list', new Routing\Route('/finy/list', array(
  '_controller' => 'Settings\\Controller\\FinyController::listFinYears',
)));
$routes->add('finy_set_active', new Routing\Route('/finy/set-active', array(
  '_controller' => 'Settings\\Controller\\FinyController::setActiveFinYear',
)));
$routes->add('finy_switch', new Routing\Route('/finy/switch', array(
  '_controller' => 'Settings\\Controller\\FinyController::switchFinYear',
)));

$routes->add('finy_slnos_add', new Routing\Route('/finy-slnos/create', array(
  '_controller' => 'Settings\\Controller\\FinySlnosController::createFinySlnos',
)));
$routes->add('finy_slnos_update', new Routing\Route('/finy-slnos/update/{finySlnoCode}', array(
  '_controller' => 'Settings\\Controller\\FinySlnosController::updateFinySlnos',
  'finySlnoCode' => null,
)));
$routes->add('finy_slnos_list', new Routing\Route('/finy-slnos/list', array(
  '_controller' => 'Settings\\Controller\\FinySlnosController::listFinySlnos',
)));

$routes->add('maintenance_mode', new Routing\Route('/maintenance-mode', array(
  '_controller' => 'Settings\\Controller\\GeneralSettingsController::maintenanceMode',
)));

// products or services list
$routes->add('products_list', new Routing\Route('/products/list/{pageNo}/{perPage}', array(
  '_controller' => 'ClothingRm\\Products\\Controller\\ProductsController::listProductsOrServices',
  'pageNo' => 1,
  'perPage' => 100,
)));
$routes->add('products_create', new Routing\Route('/products/create', array(
  '_controller' => 'ClothingRm\\Products\\Controller\\ProductsController::createProductService',
  'itemCode' => null
)));
$routes->add('products_update', new Routing\Route('/products/update/{itemCode}', array(
  '_controller' => 'ClothingRm\\Products\\Controller\\ProductsController::createProductService',
  'itemCode' => null
)));

// product categories
$routes->add('create_category', new Routing\Route('/category/create', array(
  '_controller' => 'ClothingRm\\Categories\\Controller\\CategoriesController::createCategory',
)));
$routes->add('update_category', new Routing\Route('/category/update/{categoryCode}', array(
  '_controller' => 'ClothingRm\\Categories\\Controller\\CategoriesController::updateCategory',
  'categoryCode' => null,
)));
$routes->add('categories_list', new Routing\Route('/categories/list/{pageNo}/{perPage}', array(
  '_controller' => 'ClothingRm\\Categories\\Controller\\CategoriesController::listCategories',
  'pageNo' => 1,
  'perPage' => 100,
)));

// mfg list
$routes->add('create_mfg', new Routing\Route('/mfg/create', array(
  '_controller' => 'ClothingRm\\Mfg\\Controller\\MfgController::createMfg',
)));
$routes->add('update_mfg', new Routing\Route('/mfg/update/{mfgCode}', array(
  '_controller' => 'ClothingRm\\Mfg\\Controller\\MfgController::updateMfg',
  'mfgCode' => null,
)));
$routes->add('mfgs_list', new Routing\Route('/mfgs/list/{pageNo}/{perPage}', array(
  '_controller' => 'ClothingRm\\Mfg\\Controller\\MfgController::listMfgs',
  'pageNo' => 1,
  'perPage' => 100,
)));

// supplier routes
$routes->add('suppliers_create', new Routing\Route('/suppliers/create', array(
  '_controller' => 'ClothingRm\\Suppliers\\Controller\\SupplierController::supplierCreateAction',
)));
$routes->add('suppliers_update', new Routing\Route('/suppliers/update/{supplierCode}', array(
  '_controller' => 'ClothingRm\\Suppliers\\Controller\\SupplierController::supplierCreateAction',
  'supplierCode' => null,
)));
$routes->add('suppliers_delete', new Routing\Route('/suppliers/remove/{supplierCode}', array(
  '_controller' => 'ClothingRm\\Suppliers\\Controller\\SupplierController::supplierRemoveAction',
  'supplierCode' => null,    
)));
$routes->add('suppliers_view', new Routing\Route('/suppliers/view/{supplierCode}', array(
  '_controller' => 'ClothingRm\\Suppliers\\Controller\\SupplierController::supplierViewAction',
  'supplierCode' => null,    
)));
$routes->add('suppliers_list', new Routing\Route('/suppliers/list/{pageNo}/{perPage}', array(
  '_controller' => 'ClothingRm\\Suppliers\\Controller\\SupplierController::suppliersListAction',
  'pageNo' => 1,
  'perPage' => 50,
)));

// inward entry
$routes->add('inward_entry', new Routing\Route('/inward-entry', array(
  '_controller' => 'ClothingRm\\Inward\\Controller\\InwardController::inwardEntryAction',
)));
$routes->add('inward_entry_update', new Routing\Route('/inward-entry/update/{purchaseCode}', array(
  '_controller' => 'ClothingRm\\Inward\\Controller\\InwardController::inwardEntryUpdateAction',
  'purchaseCode' => null,
)));
$routes->add('inward_entry_view', new Routing\Route('/inward-entry/view/{purchaseCode}', array(
  '_controller' => 'ClothingRm\\Inward\\Controller\\InwardController::inwardEntryViewAction',
  'purchaseCode' => null,
)));
$routes->add('inward_register', new Routing\Route('/inward-entry/list/{pageNo}/{perPage}', array(
  '_controller' => 'ClothingRm\\Inward\\Controller\\InwardController::inwardListAction',
  'pageNo' => 1,
  'perPage' => 100,
)));
$routes->add('inward_entry_update_status', new Routing\Route('/inward-entry/update-status/{purchaseCode}', array(
  '_controller' => 'ClothingRm\\Inward\\Controller\\InwardController::updateInwardStatusAction',
  'purchaseCode' => null,
)));
$routes->add('inward_entry_bulk_upload', new Routing\Route('/inward-entry/bulk-upload', array(
  '_controller' => 'ClothingRm\\Inward\\Controller\\InwardBulkUploadController::inwardEntryBulkUploadAction',
)));
$routes->add('purch_bill_search', new Routing\Route('/purchases/search-bills', array(
  '_controller' => 'ClothingRm\\Inward\\Controller\\InwardController::searchInwardAction',
)));

// purchase returns
$routes->add('purchase_return_entry', new Routing\Route('/purchase-return/entry/{poCode}', array(
  '_controller' => 'ClothingRm\\PurchaseReturns\\Controller\\PurchaseReturnsController::purchaseReturnEntryAction',
  'poCode' => null,
)));
$routes->add('purchase_return_register', new Routing\Route('/purchase-return/register', array(
  '_controller' => 'ClothingRm\\PurchaseReturns\\Controller\\PurchaseReturnsController::purchaseReturnRegisterAction',
)));
$routes->add('purchase_return_delete', new Routing\Route('/purchase-return/delete/{returnCode}', array(
  '_controller' => 'ClothingRm\\PurchaseReturns\\Controller\\PurchaseReturnsController::purchaseReturnDeleteAction',
  'returnCode' => null,
)));
$routes->add('purchase_return_view', new Routing\Route('/purchase-return/view/{returnCode}', array(
  '_controller' => 'ClothingRm\\PurchaseReturns\\Controller\\PurchaseReturnsController::purchaseReturnViewAction',
  'returnCode' => null,
)));

// GRN routes
$routes->add('grn_create_new', new Routing\Route('/grn/create', array(
  '_controller' => 'ClothingRm\\Grn\\Controller\\GrnControllerNew::grnEntryCreateAction',
)));
$routes->add('grn_view', new Routing\Route('/grn/view/{grnCode}', array(
  '_controller' => 'ClothingRm\\Grn\\Controller\\GrnControllerNew::grnViewAction',
  'grnCode' => null,
)));
$routes->add('grn_list', new Routing\Route('/grn/list', array(
  '_controller' => 'ClothingRm\\Grn\\Controller\\GrnControllerNew::grnListAction',
)));

// Sales routes
$routes->add('sales_entry_wo_barcode', new Routing\Route('/sales/entry', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\SalesEntryWoBarcode::salesEntryAction',
)));
$routes->add('sales_update_wo_barcode', new Routing\Route('/sales/update/{salesCode}', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\SalesEntryWoBarcode::salesUpdateAction',
  'salesCode' => null,
)));
$routes->add('sales_entry_with_barcode', new Routing\Route('/sales/entry-with-barcode', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\SalesEntryWithBarcode::salesEntryAction',
)));
$routes->add('sales_entry_with_indent', new Routing\Route('/sales/entry-with-indent/{indentCode}', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\SalesEntryWithIndent::salesEntryAction',
  'indentCode' => null,
)));
$routes->add('sales_update_with_barcode', new Routing\Route('/sales/update-with-barcode/{salesCode}', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\SalesEntryWithBarcode::salesUpdateAction',
  'salesCode' => null,
)));
$routes->add('sales_list', new Routing\Route('/sales/list/{pageNo}', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\SalesEntryWoBarcode::salesListAction',
  'pageNo' => null,
)));
$routes->add('sales_bill_search', new Routing\Route('/sales/search-bills', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\SalesEntryWoBarcode::saleBillsSearchAction',
)));
$routes->add('sales_view', new Routing\Route('/sales/view-invoice/{salesCode}', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\SalesEntryWoBarcode::salesViewAction',
  'salesCode' => null,
)));
$routes->add('sales_add_shipping_info', new Routing\Route('/sales/shipping-info/{salesCode}', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\SalesEntryWoBarcode::salesShippingEntryAction',
  'salesCode' => null,
)));
$routes->add('indent_vs_sales', new Routing\Route('/indent-vs-sales/{pageNo}', array(
  '_controller' => 'ClothingRm\\SalesIndent\\Controller\\SalesIndentController::indentVsSales',
  'pageNo' => null,
)));
$routes->add('indent_vs_sales_by_item', new Routing\Route('/indent-vs-sales-by-item/{pageNo}', array(
  '_controller' => 'ClothingRm\\SalesIndent\\Controller\\SalesIndentController::indentVsSalesByItem',
  'pageNo' => null,
)));
$routes->add('release_indent_items', new Routing\Route('/release-indent-items/{pageNo}', array(
  '_controller' => 'ClothingRm\\SalesIndent\\Controller\\SalesIndentController::releaseIndentItems',
  'pageNo' => null,
)));

// einvoice routes...
$routes->add('generate_einvoice', new Routing\Route('/sales/generate-einvoice/{salesCode}', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\EInvoiceController::generateEinvoiceAction',
  'salesCode' => null,
)));
$routes->add('einvoices_list', new Routing\Route('/einvoices/list/{pageNo}', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\EInvoiceController::eInvoiceRegister',
  'pageNo' => null
)));
$routes->add('view_einvoice', new Routing\Route('/einvoice/view/{invoiceCode}', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\EInvoiceController::eInvoiceView',
  'invoiceCode' => null,
)));
$routes->add('einvoice_cancel', new Routing\Route('/einvoice/cancel-irn/{irn}/{invoiceCode}', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\EInvoiceController::cancelEinvoice',
  'irn' => null,
  'invoiceCode' => null,
)));
$routes->add('einvoice_waybill', new Routing\Route('/einvoice/generate-eway-bill/{irn}/{invoiceCode}', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\EInvoiceController::generateEwayBill',
  'irn' => null,
  'invoiceCode' => null,
)));
$routes->add('generate_qrcode', new Routing\Route('/qrcode', array(
  '_controller' => 'ClothingRm\\Sales\\Controller\\EInvoiceController::generateQrCode',
)));

// Sales Return routes
$routes->add('sales_return_entry', new Routing\Route('/sales-return/entry/{salesCode}', array(
  '_controller' => 'ClothingRm\\SalesReturns\\Controller\\SalesReturnsController::salesReturnEntryAction',
  'salesCode' => null,
)));
$routes->add('sales_return_view', new Routing\Route('/sales-return/view/{salesCode}/{salesReturnCode}', array(
  '_controller' => 'ClothingRm\\SalesReturns\\Controller\\SalesReturnsController::salesReturnViewAction',
  'salesCode' => null,
  'salesReturnCode' => null,
)));
$routes->add('sales_return_list', new Routing\Route('/sales-return/list', array(
  '_controller' => 'ClothingRm\\SalesReturns\\Controller\\SalesReturnsController::salesReturnListAction',
)));
$routes->add('report_printSalesReturnBill', new Routing\Route('/print-sales-return-bill', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsSalesReturnController::printSalesReturnBill',
)));

// opening balances
$routes->add('openings_list', new Routing\Route('/opbal/list/{pageNo}/{perPage}', array(
  '_controller' => 'ClothingRm\\Openings\\Controller\\OpeningsController::opBalListAction',
  'pageNo' => null,
  'perPage' => null,    
)));
$routes->add('openings_add', new Routing\Route('/opbal/add', array(
  '_controller' => 'ClothingRm\\Openings\\Controller\\OpeningsController::opBalCreateAction',    
)));
$routes->add('openings_update', new Routing\Route('/opbal/update/{opCode}', array(
  '_controller' => 'ClothingRm\\Openings\\Controller\\OpeningsController::opBalCreateAction',
  'opCode' => null,
)));

// inventory
$routes->add('qty_available', new Routing\Route('/inventory/available-qty/{pageNo}/{perPage}', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryController::availableQtyList',
  'pageNo' => null,
  'perPage' => null,
)));
$routes->add('item_track', new Routing\Route('/inventory/track-item/{pageNo}/{perPage}', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryController::trackItem',   
  'pageNo' => null,
  'perPage' => null,
)));
$routes->add('item_qty_available', new Routing\Route('/inventory/search-products', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryController::searchItem',   
)));
$routes->add('add_stock_adjustment', new Routing\Route('/inventory/stock-adjustment', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryController::addStockAdjustment',   
)));
$routes->add('delete_stock_adjustment', new Routing\Route('/inventory/stock-adjustment/delete/{adjCode}', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryController::deleteStockAdjustment',
  'adjCode' => null,
)));
$routes->add('stock_adjustment_list', new Routing\Route('/inventory/stock-adjustments-list/{pageNo}/{perPage}', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryController::getAllStockAdjustments',
  'pageNo' => null,
  'perPage' => null,
)));
$routes->add('add_item_threshold', new Routing\Route('/inventory/item-threshold-add', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryController::itemThresholdAdd',   
)));
$routes->add('update_item_threshold', new Routing\Route('/inventory/item-threshold-update/{thrCode}', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryController::itemThresholdUpdate',
  'thrCode' => null,      
)));
$routes->add('del_item_threshold', new Routing\Route('/inventory/item-threshold-delete', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryController::itemThresholdDelete',
  'thrCode' => null,      
)));
$routes->add('list_item_threshold', new Routing\Route('/inventory/item-threshold-list/{pageNo}', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryController::itemThresholdList',
  'pageNo' => 1,
)));
$routes->add('refresh_stock', new Routing\Route('/inventory/refresh-cb', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryController::refreshCb',
)));
$routes->add('refresh_stock_with_indents', new Routing\Route('/inventory/refresh-cb-indents', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryController::refreshCbIndents',
)));
$routes->add('inventory_sample_check', new Routing\Route('/inventory/sample-checker', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryController::productSampleChecker',   
)));

// mrp register
$routes->add('change_mrp', new Routing\Route('/inventory/change-mrp/{pageNo}', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryMrpController::changeMrpAction',
  'pageNo' => null,
)));
$routes->add('mrp_register', new Routing\Route('/inventory/changed-mrp-register/{pageNo}', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryMrpController::mrpRegisterAction',
  'pageNo' => 1,
)));
$routes->add('update_selling_price', new Routing\Route('/inventory/change-selling-price', array(
  '_controller' => 'ClothingRm\\Inventory\\Controller\\InventoryMrpController::bulkMrpUpdateAction',
)));

// async calls
$routes->add('async', new Routing\Route('/async/{apiString}', array(
  '_controller' => 'ClothingRm\\Async\\Controller\\AsyncController::asyncRequestAction',
  'apiString' => null,
)));

// customer routes
$routes->add('customers_create', new Routing\Route('/customers/create', array(
  '_controller' => 'ClothingRm\\Customers\\Controller\\CustomersController::customerCreateAction',
)));
$routes->add('customers_update', new Routing\Route('/customers/update/{customerCode}', array(
  '_controller' => 'ClothingRm\\Customers\\Controller\\CustomersController::customerUpdateAction',
  'customerCode' => null,
)));
$routes->add('customers_view', new Routing\Route('/customers/view/{custCode}', array(
  '_controller' => 'ClothingRm\\Customers\\Controller\\CustomersController::customerViewAction',
  'custCode' => null,
)));
$routes->add('customers_list', new Routing\Route('/customers/list/{pageNo}/{perPage}', array(
  '_controller' => 'ClothingRm\\Customers\\Controller\\CustomersController::customerListAction',
  'pageNo' => null,
  'perPage' => null,
)));

// business user routes
$routes->add('bu_create', new Routing\Route('/bu/create', array(
  '_controller' => 'BusinessUsers\\Controller\\BusinessUsersController::buCreateAction',
)));
$routes->add('bu_update', new Routing\Route('/bu/update/{userCode}', array(
  '_controller' => 'BusinessUsers\\Controller\\BusinessUsersController::buUpdateAction',
  'userCode' => null,
)));
$routes->add('bu_view', new Routing\Route('/bu/view/{userCode}', array(
  '_controller' => 'BusinessUsers\\Controller\\BusinessUsersController::buViewAction',
  'custCode' => null,
)));
$routes->add('bu_list', new Routing\Route('/bu/list/{pageNo}/{perPage}', array(
  '_controller' => 'BusinessUsers\\Controller\\BusinessUsersController::buListAction',
  'pageNo' => null,
  'perPage' => null,
)));

// Campaigns Management
$routes->add('create_campaign', new Routing\Route('/campaigns/create', array(
  '_controller' => 'Campaigns\\Controller\\CampaignsController::addCampaign',
)));
$routes->add('update_campaign', new Routing\Route('/campaigns/update/{campaignCode}', array(
  '_controller' => 'Campaigns\\Controller\\CampaignsController::updateCampaign',
  'campaignCode' => null,
)));
$routes->add('list_campaigns', new Routing\Route('/campaigns/list', array(
  '_controller' => 'Campaigns\\Controller\\CampaignsController::listCampaigns',
)));

// Supplier opening balance
$routes->add('fin_supp_opbal_create', new Routing\Route('/fin/supp-opbal/create', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\FinSuppOpBalController::supplierOpBalCreateAction',
)));
$routes->add('fin_supp_opbal_update', new Routing\Route('/fin/supp-opbal/update/{opBalCode}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\FinSuppOpBalController::supplierOpBalUpdateAction',
  'opBalCode' => null,
)));
$routes->add('fin_supp_opbal_list', new Routing\Route('/fin/supp-opbal/list', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\FinSuppOpBalController::supplierOpBalListAction',
)));
$routes->add('fin_supp_opbal_import', new Routing\Route('/fin/supp-opbal/import', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\FinSuppOpBalController::supplierOpBalImportAction',
)));

// Customers opening balance
$routes->add('fin_cust_opbal_create', new Routing\Route('/fin/cust-opbal/create', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\FinCustOpBalController::customerOpBalCreateAction',
)));
$routes->add('fin_cust_opbal_update', new Routing\Route('/fin/cust-opbal/update/{opBalCode}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\FinCustOpBalController::customerOpBalUpdateAction',
  'opBalCode' => null,
)));
$routes->add('fin_cust_opbal_delete', new Routing\Route('/fin/cust-opbal/remove/{opBalCode}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\FinCustOpBalController::customerOpBalDeleteAction',
  'opBalCode' => null,
)));
$routes->add('fin_cust_opbal_list', new Routing\Route('/fin/cust-opbal/list/{pageNo}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\FinCustOpBalController::customerOpBalListAction',
  'pageNo' => null,
)));
$routes->add('fin_cust_opbal_import', new Routing\Route('/fin/cust-opbal/import', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\FinCustOpBalController::customerOpBalImportAction',
)));


// Receivables
$routes->add('receipt_voc_create', new Routing\Route('/fin/receipt-voucher/create', array(
'_controller' => 'ClothingRm\\Finance\\Controller\\ReceiptsController::receiptCreateAction',
)));
$routes->add('receipt_voc_update', new Routing\Route('/fin/receipt-voucher/update/{vocNo}', array(
'_controller' => 'ClothingRm\\Finance\\Controller\\ReceiptsController::receiptUpdateAction',
'vocNo' => null,
)));
$routes->add('receipt_voc_delete', new Routing\Route('/fin/receipt-voucher/delete/{vocNo}', array(
'_controller' => 'ClothingRm\\Finance\\Controller\\ReceiptsController::receiptDeleteAction',
'vocNo' => null,
)));
$routes->add('receipt_voc_list', new Routing\Route('/fin/receipt-vouchers/{pageNo}', array(
'_controller' => 'ClothingRm\\Finance\\Controller\\ReceiptsController::receiptsListAction',
'pageNo' => null,
)));

// Payment vouchers
$routes->add('payment_voc_create', new Routing\Route('/fin/payment-voucher/create', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\PaymentsController::paymentCreateAction',
)));
$routes->add('payment_voc_update', new Routing\Route('/fin/payment-voucher/update/{vocNo}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\PaymentsController::paymentUpdateAction',
  'vocNo' => null,
)));
$routes->add('payment_voc_delete', new Routing\Route('/fin/payment-voucher/delete/{vocNo}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\PaymentsController::paymentDeleteAction',
  'vocNo' => null,
)));
$routes->add('payment_voc_list', new Routing\Route('/fin/payment-vouchers/{pageNo}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\PaymentsController::paymentsListAction',
  'pageNo' => null,
)));

// Credit notes
$routes->add('cn_create', new Routing\Route('/fin/credit-note/create', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\CreditNotesController::cnCreateAction',
)));
$routes->add('cn_update', new Routing\Route('/fin/credit-note/update/{cnNo}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\CreditNotesController::cnUpdateAction',
  'cnNo' => null,
)));
$routes->add('cn_delete', new Routing\Route('/fin/credit-note/delete/{cnNo}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\CreditNotesController::cnDeleteAction',
  'cnNo' => null,
)));
$routes->add('cn_view', new Routing\Route('/fin/credit-note/view/{cnCode}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\CreditNotesController::cnViewAction',
  'cnCode' => null,
)));
$routes->add('cn_list', new Routing\Route('/fin/credit-notes/{pageNo}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\CreditNotesController::cnListAction',
  'pageNo' => null,
)));

// Debit notes
$routes->add('dn_create', new Routing\Route('/fin/debit-note/create', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\DebitNotesController::dnCreateAction',
)));
$routes->add('dn_update', new Routing\Route('/fin/debit-note/update/{dnCode}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\DebitNotesController::dnUpdateAction',
  'dnNo' => null,
)));
$routes->add('dn_delete', new Routing\Route('/fin/debit-note/delete/{dnCode}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\DebitNotesController::dnDeleteAction',
  'dnCode' => null,
)));
$routes->add('dn_view', new Routing\Route('/fin/debit-note/view/{dnCode}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\DebitNotesController::dnViewAction',
  'dnCode' => null,
)));
$routes->add('dn_list', new Routing\Route('/fin/debit-notes/{pageNo}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\DebitNotesController::dnListAction',
  'pageNo' => null,
)));

// Petty cash vouchers
$routes->add('pc_voc_create', new Routing\Route('/fin/cash-voucher/create', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\PettyCashController::pettyCashVoucherCreateAction',
)));
$routes->add('sales_to_cb_register', new Routing\Route('/fin/sales2cb/register', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\PettyCashController::salesCashToCashBookRegister',
)));
$routes->add('post_sales_data_in_cb', new Routing\Route('/fin/post-sales2cb', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\PettyCashController::postSalesCashToCashBook',
)));
$routes->add('pc_voc_update', new Routing\Route('/fin/cash-voucher/update/{vocNo}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\PettyCashController::pettyCashVoucherUpdateAction',
  'vocNo' => null,
)));
$routes->add('pc_voc_list', new Routing\Route('/fin/cash-vouchers/{pageNo}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\PettyCashController::pettyCashVoucherListAction',
  'pageNo' => null,
)));
$routes->add('pc_voc_delete', new Routing\Route('/fin/cash-voucher/delete/{vocNo}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\PettyCashController::pettyCashVoucherDeleteAction',
  'vocNo' => null,
)));
$routes->add('petty_cash_book', new Routing\Route('/fin/cash-book/{pageNo}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\PettyCashController::pettyCashBookAction',
  'pageNo' => null,
)));

// Banks management
$routes->add('bank_create', new Routing\Route('/fin/bank/create', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\BanksController::bankCreateAction',
)));
$routes->add('bank_update', new Routing\Route('/fin/bank/update/{bankCode}', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\BanksController::bankUpdateAction',
  'bankCode' => null,
)));
$routes->add('banks_list', new Routing\Route('/fin/bank/list', array(
  '_controller' => 'ClothingRm\\Finance\\Controller\\BanksController::banksListAction',
)));

// Users management
$routes->add('users_list', new Routing\Route('/users/list', array(
  '_controller' => 'User\\Controller\\UserController::listUsersAction',
)));
$routes->add('users_list_online', new Routing\Route('/users/online', array(
  '_controller' => 'User\\Controller\\UserController::listOnlineUsersAction',
)));
$routes->add('users_update', new Routing\Route('/users/update/{uuid}', array(
  '_controller' => 'User\\Controller\\UserController::updateUserAction',
  'uuid' => null,
)));
$routes->add('users_delete', new Routing\Route('/users/delete/{uuid}', array(
  '_controller' => 'User\\Controller\\UserController::deleteUserAction',
  'uuid' => null,
)));
$routes->add('users_update_app', new Routing\Route('/users/update-app/{uuid}', array(
  '_controller' => 'User\\Controller\\UserController::updateUserActionApp',
  'uuid' => null,
)));
$routes->add('users_create', new Routing\Route('/users/create', array(
  '_controller' => 'User\\Controller\\UserController::createUserAction',
)));

// Admin Options
$routes->add('adminOptions_deleteGRN', new Routing\Route('/admin-options/delete-grn', array(
  '_controller' => 'ClothingRm\\AdminOptions\\Controller\\AdminOptionsController::deleteGRN',
)));
$routes->add('adminOptions_deletePO', new Routing\Route('/admin-options/delete-po', array(
  '_controller' => 'ClothingRm\\AdminOptions\\Controller\\AdminOptionsController::deletePO',
)));
$routes->add('adminOptions_deleteInvoice', new Routing\Route('/admin-options/delete-invoice', array(
  '_controller' => 'ClothingRm\\AdminOptions\\Controller\\AdminOptionsController::deleteInvoice',
)));
$routes->add('adminOptions_deleteSalesReturn', new Routing\Route('/admin-options/delete-sales-return', array(
  '_controller' => 'ClothingRm\\AdminOptions\\Controller\\AdminOptionsController::deleteSalesReturn',
)));
$routes->add('adminOptions_orgSummary', new Routing\Route('/admin-options/org-summary', array(
  '_controller' => 'ClothingRm\\AdminOptions\\Controller\\AdminOptionsController::orgSummary',
)));
$routes->add('deleted_register', new Routing\Route('/deleted-vouchers', array(
  '_controller' => 'ClothingRm\\AdminOptions\\Controller\\AdminOptionsController::deletedVouchers',
)));
$routes->add('export_indents', new Routing\Route('/admin-options/export-indents', array(
  '_controller' => 'ClothingRm\\AdminOptions\\Controller\\AdminOptionsController::exportIndents',
)));
$routes->add('export_pos', new Routing\Route('/admin-options/export-pos', array(
  '_controller' => 'ClothingRm\\AdminOptions\\Controller\\AdminOptionsController::exportPos',
)));

// file uploader
$routes->add('upload_inventory', new Routing\Route('/upload-inventory', array(
  '_controller' => 'ClothingRm\\AdminOptions\\Controller\\UploadInventoryController::uploadInventoryAction',
)));
$routes->add('update_cat_brand', new Routing\Route('/update-category-brand', array(
  '_controller' => 'ClothingRm\\AdminOptions\\Controller\\UploadInventoryController::updateCategoryBrandAction',
)));
$routes->add('upload_debtors', new Routing\Route('/upload-debtors', array(
  '_controller' => 'ClothingRm\\AdminOptions\\Controller\\UploadBalancesController::uploadDebtorsAction',
)));
$routes->add('upload_creditors', new Routing\Route('/upload-creditors', array(
  '_controller' => 'ClothingRm\\AdminOptions\\Controller\\UploadBalancesController::uploadCreditorsAction',
)));

// Taxes Management
$routes->add('add_tax', new Routing\Route('/taxes/add', array(
  '_controller' => 'Taxes\\Controller\\TaxesController::addTax',
)));
$routes->add('update_tax', new Routing\Route('/taxes/update/{taxCode}', array(
  '_controller' => 'Taxes\\Controller\\TaxesController::updateTax',
  'taxCode' => null,
)));
$routes->add('list_taxes', new Routing\Route('/taxes/list', array(
  '_controller' => 'Taxes\\Controller\\TaxesController::listTaxes',
)));

// HSN / SAC codes Management
$routes->add('add_hsnsac', new Routing\Route('/hsnsac/add', array(
  '_controller' => 'HsnSacCodes\\Controller\\HsnSacCodesController::addHsnSacCode',
)));
$routes->add('update_hsnsac', new Routing\Route('/hsnsac/update/{hsnSacUniqueCode}', array(
  '_controller' => 'HsnSacCodes\\Controller\\HsnSacCodesController::updateHsnSacCode',
  'hsnSacUniqueCode' => null,
)));
$routes->add('list_hsnsac_codes', new Routing\Route('/hsnsac/list/{pageNo}', array(
  '_controller' => 'HsnSacCodes\\Controller\\HsnSacCodesController::listHsnSacCodes',
  'pageNo' => null,
)));

// promotional offers management.
$routes->add('create_offer', new Routing\Route('/promo-offers/entry', array(
  '_controller' => 'ClothingRm\\PromoOffers\\Controller\\PromoOffersController::promoOfferEntryAction',
)));
$routes->add('update_offer', new Routing\Route('/promo-offers/update/{offerCode}', array(
  '_controller' => 'ClothingRm\\PromoOffers\\Controller\\PromoOffersController::promoOfferUpdateAction',
  'offerCode' => null,
)));
$routes->add('list_offers', new Routing\Route('/promo-offers/list', array(
  '_controller' => 'ClothingRm\\PromoOffers\\Controller\\PromoOffersController::promoOffersListAction',
)));

// error page
$routes->add('error_page', new Routing\Route('/error', array(
  '_controller' => 'User\\Controller\\DashBoardController::errorAction',
)));
$routes->add('error_page_404', new Routing\Route('/error-404', array(
  '_controller' => 'User\\Controller\\DashBoardController::errorActionNotFound',
)));
$routes->add('error_device', new Routing\Route('/error-device', array(
  '_controller' => 'User\\Controller\\DashBoardController::errorActionDevice',
)));
$routes->add('force_logout', new Routing\Route('/force-logout', array(
  '_controller' => 'User\\Controller\\DashBoardController::forceLogoutAction',
)));

// Stock transfer routes.
$routes->add('stock_out_transfer', new Routing\Route('/stock-transfer/out', array(
  '_controller' => 'ClothingRm\\StockTransfer\\Controller\\StockOutController::stockOutCreateAction',
)));
$routes->add('stock_out_transfer_view', new Routing\Route('/stock-transfer/out/{transferCode}', array(
  '_controller' => 'ClothingRm\\StockTransfer\\Controller\\StockOutController::stockOutViewAction',
  'transferCode' => null,
)));
$routes->add('stock_out_transfer_validate', new Routing\Route('/stock-transfer/validate/{transferCode}', array(
  '_controller' => 'ClothingRm\\StockTransfer\\Controller\\StockOutController::stockOutValidateAction',
  'transferCode' => null,
)));
$routes->add('stock_out_transfer_delete', new Routing\Route('/stock-transfer/delete/{transferCode}', array(
  '_controller' => 'ClothingRm\\StockTransfer\\Controller\\StockOutController::stockOutDeleteAction',
  'transferCode' => null,
)));
$routes->add('stock_out_transfer_list', new Routing\Route('/stock-transfer/register/{pageNo}', array(
  '_controller' => 'ClothingRm\\StockTransfer\\Controller\\StockOutController::stockOutTransactionsList',
  'pageNo' => null,
)));
$routes->add('choose_st_location', new Routing\Route('/stock-transfer/choose-location', array(
  '_controller' => 'ClothingRm\\StockTransfer\\Controller\\StockTransferController::stockTransferLocationAction',
)));

// loyalty programme
$routes->add('loyalty_member_add', new Routing\Route('/loyalty-member/add', array(
  '_controller' => 'ClothingRm\\Loyalty\\Controller\\LoyaltyController::addLoyaltyMember',
)));
$routes->add('loyalty_member_update', new Routing\Route('/loyalty-member/update/{memberCode}', array(
  '_controller' => 'ClothingRm\\Loyalty\\Controller\\LoyaltyController::updateLoyaltyMember',
  'memberCode' => null,
)));
$routes->add('loyalty_member_list', new Routing\Route('/loyalty-members/list/{pageNo}', array(
  '_controller' => 'ClothingRm\\Loyalty\\Controller\\LoyaltyController::listLoyaltyMembers',
  'pageNo' => null,
)));
$routes->add('loyalty_member_ledger', new Routing\Route('/loyalty-member/ledger/{memberCode}/{pageNo}', array(
  '_controller' => 'ClothingRm\\Loyalty\\Controller\\LoyaltyController::getLoyaltyMemberLedger',
  'memberCode' => null,
  'pageNo' => null,
)));

// Devices management.
$routes->add('device_add', new Routing\Route('/device/add', array(
  '_controller' => 'Devices\\Controller\\DevicesController::addDeviceAction',
)));
$routes->add('device_update', new Routing\Route('/device/update/{deviceCode}', array(
  '_controller' => 'Devices\\Controller\\DevicesController::updateDeviceAction',
  'deviceCode' => null,
)));
$routes->add('devices_list', new Routing\Route('/devices/list/{pageNo}', array(
  '_controller' => 'Devices\\Controller\\DevicesController::listDevicesAction',
  'pageNo' => null,
)));
$routes->add('device_delete', new Routing\Route('/device/delete/{deviceCode}', array(
  '_controller' => 'Devices\\Controller\\DevicesController::deleteDeviceAction',
  'deviceCode' => null,
)));
$routes->add('show_device_name', new Routing\Route('/device/show-name', array(
  '_controller' => 'Devices\\Controller\\DevicesController::showDeviceName',
)));

// stores management
$routes->add('create_location', new Routing\Route('/location/create', array(
  '_controller' => 'Location\\Controller\\LocationController::addLocation',
)));
$routes->add('update_location', new Routing\Route('/location/update/{locationCode}', array(
  '_controller' => 'Location\\Controller\\LocationController::updateLocation',
  'locationCode' => null,
)));
$routes->add('list_locations', new Routing\Route('/locations/list/{pageNo}', array(
  '_controller' => 'Location\\Controller\\LocationController::listLocations',
  'pageNo' => null,
)));

// barcode
$routes->add('barcode_generate', new Routing\Route('/barcode/generate/{purchaseCode}', array(
  '_controller' => 'ClothingRm\\Barcode\\Controller\\BarcodeController::generateBarcodeAction',
  'purchaseCode' => null,
)));
$routes->add('barcodes_list', new Routing\Route('/barcodes/list/{pageNo}', array(
  '_controller' => 'ClothingRm\\Barcode\\Controller\\BarcodeController::barcodesListAction',
  'pageNo' => null,
)));
$routes->add('barcodes_print', new Routing\Route('/barcodes/print', array(
  '_controller' => 'ClothingRm\\Barcode\\Controller\\BarcodeController::printBarcodesAction',
)));
$routes->add('barcode_opening', new Routing\Route('/barcode/opbal/{pageNo}', array(
  '_controller' => 'ClothingRm\\Barcode\\Controller\\BarcodeController::generateBarcodesOpbalAction',
  'pageNo' => null,
)));
$routes->add('barcode_closing', new Routing\Route('/barcode/cbbal/{pageNo}', array(
  '_controller' => 'ClothingRm\\Barcode\\Controller\\BarcodeController::generateBarcodesClosingbalAction',
  'pageNo' => null,
)));
$routes->add('barcode_closing_itemwise', new Routing\Route('/barcode/cbbal-itemwise/{pageNo}', array(
  '_controller' => 'ClothingRm\\Barcode\\Controller\\BarcodeController::generateBarcodesClosingbalItemwiseAction',
  'pageNo' => null,
)));

// sales indent
$routes->add('create_sindent', new Routing\Route('/sales-indent/create', array(
  '_controller' => 'ClothingRm\\SalesIndent\\Controller\\SalesIndentController::createIndent',
)));
$routes->add('create_sindent_samples', new Routing\Route('/sales-indent/create-from-samples', array(
  '_controller' => 'ClothingRm\\SalesIndent\\Controller\\SalesIndentController::createIndentFromSamples',
)));
$routes->add('update_sindent', new Routing\Route('/sales-indent/update/{indentCode}', array(
  '_controller' => 'ClothingRm\\SalesIndent\\Controller\\SalesIndentController::updateIndent',
  'indentCode' => null,
)));
$routes->add('change_sindent_status', new Routing\Route('/sales-indent/update-status/{indentCode}', array(
  '_controller' => 'ClothingRm\\SalesIndent\\Controller\\SalesIndentController::updateIndentStatus',
  'indentCode' => null,
)));
$routes->add('list_sindents', new Routing\Route('/sales-indents/list/{pageNo}', array(
  '_controller' => 'ClothingRm\\SalesIndent\\Controller\\SalesIndentController::listIndents',
  'pageNo' => null,
)));
$routes->add('create_sindent_mobile', new Routing\Route('/sales-indent/create/mobile', array(
  '_controller' => 'ClothingRm\\SalesIndent\\Controller\\SalesIndentController::createIndentMobileView',
)));
$routes->add('create_sindent_mobile_s2', new Routing\Route('/sales-indent/create/mobile/step2', array(
  '_controller' => 'ClothingRm\\SalesIndent\\Controller\\SalesIndentController::createIndentMobileViewStep2',
)));

// reports - sales
$routes->add('report_sales_register', new Routing\Route('/reports/sales-register', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Sales\\Controller\\SalesReportsController::printSalesRegister',
)));
$routes->add('report_sales_b2b_invoice', new Routing\Route('/sales-invoice-b2b/{salesCode}', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Sales\\Controller\\SalesReportsController::printB2BSalesInvoice',
  'salesCode' => null,
)));
$routes->add('report_sales_b2b_invoice_with_irn', new Routing\Route('/sales-invoice-irn/{salesCode}', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Sales\\Controller\\SalesReportsController::printB2BSalesInvoiceWithIrn',
  'salesCode' => null,
)));
$routes->add('report_sales_register_itemwise', new Routing\Route('/reports/itemwise-sales-register', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Sales\\Controller\\SalesReportsController::itemwiseSalesRegister',
)));
$routes->add('report_day_sales_summary', new Routing\Route('/reports/day-sales', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Sales\\Controller\\SalesReportsController::daySalesReport',
)));
$routes->add('report_sales_summary_month', new Routing\Route('/reports/sales-summary-by-month', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Sales\\Controller\\SalesReportsController::salesSummaryByMonth',
)));
$routes->add('report_itemwise_sales', new Routing\Route('/reports/sales-by-tax-rate', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Sales\\Controller\\SalesReportsController::salesByTaxRate',
)));
$routes->add('report_hsnsac_sales', new Routing\Route('/reports/sales-by-hsn-sac-code', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Sales\\Controller\\SalesReportsController::salesByHsnCodes',
)));
$routes->add('report_sales_billwise_itemwise', new Routing\Route('/reports/sales-billwise-itemwise', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Sales\\Controller\\SalesReportsController::salesBillwiseItemwise',
)));
$routes->add('report_dispatch_register', new Routing\Route('/reports/sales-dispatch-register', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Sales\\Controller\\SalesReportsController::salesDispatchRegister',
)));
$routes->add('report_billitemwise_report', new Routing\Route('/reports/sales-billwise-itemwise-casewise', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Sales\\Controller\\SalesReportsController::salesBillwiseItemwiseCasewise',
)));
$routes->add('report_sales_upi', new Routing\Route('/reports/sales-upi-register', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Sales\\Controller\\SalesReportsController::salesUpiPaymentsRegister',
)));
$routes->add('report_sales_mis', new Routing\Route('/reports/sales-mis', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Sales\\Controller\\SalesReportsController::salesMisByFilter',
)));

// reports - inventory
$routes->add('report_stock', new Routing\Route('/reports/stock-report', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Inventory\\Controller\\InventoryReportsController::stockReport',
)));
$routes->add('report_stock_brandwise', new Routing\Route('/reports/stock-report-brandwise', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Inventory\\Controller\\InventoryReportsController::stockReportBrandwise',
)));
$routes->add('report_stock_indent', new Routing\Route('/reports/stock-report-indent', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Inventory\\Controller\\InventoryReportsController::stockReportIndent',
)));
$routes->add('report_openingBalance', new Routing\Route('/reports/opbal', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Inventory\\Controller\\InventoryReportsController::openingBalances',
)));
$routes->add('report_profitability', new Routing\Route('/reports/inventory-profitability', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Inventory\\Controller\\InventoryReportsController::inventoryProfitability',
)));
$routes->add('report_materialMovement', new Routing\Route('/reports/material-movement', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Inventory\\Controller\\InventoryReportsController::materialMovement',
)));
$routes->add('report_stock_transfer', new Routing\Route('/reports/stock-transfer-register', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Inventory\\Controller\\InventoryReportsController::stockTransferRegister',
)));
$routes->add('report_stock_adjustment', new Routing\Route('/reports/stock-adjustment-register', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Inventory\\Controller\\InventoryReportsController::stockAdjustmentRegister',
)));
$routes->add('report_stock_adjustments', new Routing\Route('/reports/stock-adj-register', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Inventory\\Controller\\InventoryReportsController::stockTransferRegister',
)));
$routes->add('report_printGRN', new Routing\Route('/print-grn/{grnCode}', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Inventory\\Controller\\InventoryReportsController::printGRN',
  'grnCode' => null,
)));
$routes->add('report_printPO', new Routing\Route('/print-po/{purchaseCode}', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Inventory\\Controller\\InventoryReportsController::printPO',
  'purchaseCode' => null,
)));

// reports - purchases
$routes->add('report_po_register', new Routing\Route('/reports/po-register', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Purchases\\Controller\\PurchaseReportsController::poRegister',
)));
$routes->add('report_po_register_itemwise', new Routing\Route('/reports/po-register-itemwise', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Purchases\\Controller\\PurchaseReportsController::itemwisePoRegister',
)));
$routes->add('report_po_return_register', new Routing\Route('/reports/po-return-register', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Purchases\\Controller\\PurchaseReportsController::poReturnRegister',
)));
$routes->add('report_grn_register', new Routing\Route('/reports/grn-register', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Purchases\\Controller\\PurchaseReportsController::grnRegister',
)));

// reports - masters
$routes->add('report_item_master', new Routing\Route('/reports/item-master', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Masters\\Controller\\MastersReportsController::itemMasterReport',
)));
$routes->add('report_item_master_barcodes', new Routing\Route('/reports/item-master-with-barcodes', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Masters\\Controller\\MastersReportsController::itemMasterWithBarcodes',
)));
$routes->add('report_customer_master', new Routing\Route('/reports/customer-master', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Masters\\Controller\\MastersReportsController::customerMasterReport',
)));

// reports - finance
$routes->add('report_payables', new Routing\Route('/reports/payables', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Finance\\Controller\\FinReportsController::payablesAction',
)));
$routes->add('report_receivables', new Routing\Route('/reports/receivables', array(
  '_controller' => 'ClothingRm\\ReportsByModule\\Finance\\Controller\\FinReportsController::receivablesAction',
)));


$routes->add('report_filterOptions', new Routing\Route('/report-options/{reportName}', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsController::reportOptions',
  'reportName' => null,
)));
$routes->add('report_printSalesBillSmall', new Routing\Route('/print-sales-bill-small', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsController::printSalesBillSmall',
)));
$routes->add('report_printSalesBill', new Routing\Route('/print-sales-bill', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsController::printSalesBill',
)));
$routes->add('report_printSalesBillCombo', new Routing\Route('/print-sales-bill-combo', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsController::printSalesBillCombo',
)));
$routes->add('report_inventoryProfitability', new Routing\Route('/inventory-profitability', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsInventoryController::inventoryProfitability',
)));

$routes->add('report_printIndent', new Routing\Route('/print-indent', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsIndentController::printIndent',
)));
$routes->add('report_printIndentWr', new Routing\Route('/print-indent-wor', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsIndentController::printIndentWoRate',
)));

// year end jobs
$routes->add('post_inventory', new Routing\Route('/year-end-jobs/post-inventory', array(
  '_controller' => 'YearendJobs\\Controller\\YearendJobsController::postInventory',
)));
$routes->add('post_barcodes', new Routing\Route('/year-end-jobs/post-barcodes', array(
  '_controller' => 'YearendJobs\\Controller\\YearendJobsController::postBarcodes',
)));
$routes->add('post_debtors', new Routing\Route('/year-end-jobs/post-debtors', array(
  '_controller' => 'YearendJobs\\Controller\\YearendJobsController::postDebtors',
)));
$routes->add('post_creditors', new Routing\Route('/year-end-jobs/post-creditors', array(
  '_controller' => 'YearendJobs\\Controller\\YearendJobsController::postCreditors',
)));

// adj reasons
$routes->add('add_adj_reason', new Routing\Route('/stock-adj-reason/add', array(
  '_controller' => 'StockAdjReasons\\Controller\\StockAdjReasonsController::addAdjReason',
)));
$routes->add('update_adj_reason', new Routing\Route('/stock-adj-reason/update/{reasonCode}', array(
  '_controller' => 'StockAdjReasons\\Controller\\StockAdjReasonsController::updateAdjReason',
  'reasonCode' => null,
)));
$routes->add('list_adj_reasons', new Routing\Route('/stock-adj-reasons/list', array(
  '_controller' => 'StockAdjReasons\\Controller\\StockAdjReasonsController::listAdjReasonCodes',
)));

// sales categories
$routes->add('add_sales_category', new Routing\Route('/sales-category/add', array(
  '_controller' => 'SalesCategory\\Controller\\SalesCategoryController::addSalesCategory',
)));
$routes->add('update_sales_category', new Routing\Route('/sales-category/update/{categoryCode}', array(
  '_controller' => 'SalesCategory\\Controller\\SalesCategoryController::updateSalesCategory',
  'categoryCode' => null,
)));
$routes->add('list_sale_categories', new Routing\Route('/sales-category/list', array(
  '_controller' => 'SalesCategory\\Controller\\SalesCategoryController::listSalesCategories',
)));

$routes->add('report_indentItemAvail', new Routing\Route('/indent-item-avail', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsIndentController::indentItemAvailability',
)));
$routes->add('report_indentItemwise', new Routing\Route('/indent-itemwise', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsIndentController::indentItemwiseBooked',
)));
$routes->add('report_indentAgentwise', new Routing\Route('/indent-agentwise', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsIndentController::indentAgentwiseBooked',
)));
$routes->add('report_indentStatewise', new Routing\Route('/indent-statewise', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsIndentController::indentStatewiseBooked',
)));
$routes->add('report_print_indents_of_agent', new Routing\Route('/print-indents-agentwise', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsIndentController::printIndentsAgentwise',
)));
$routes->add('report_indentRegister', new Routing\Route('/indent-register', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsIndentController::indentRegister',
)));
$routes->add('report_dispatchSummary', new Routing\Route('/indent-dispatch-summary', array(
  '_controller' => 'ClothingRm\\Reports\\Controller\\ReportsIndentController::indentDispatchSummary',
)));

//Ecommerce routes.
$routes->add('gallery_create', new Routing\Route('/gallery/create', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\GalleryController::createGallery',
)));
$routes->add('gallery_update', new Routing\Route('/gallery/update/{locationCode}/{galleryCode}', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\GalleryController::updateGallery',
  'locationCode' => null,
  'galleryCode' => null,
)));
$routes->add('gallery_delete', new Routing\Route('/gallery/delete/{locationCode}/{galleryCode}/{pageNo}', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\GalleryController::deleteGallery',
  'galleryCode' => null,
  'locationCode' => null,
  'pageNo' => null,
)));
$routes->add('galleries_list', new Routing\Route('/galleries/list/{pageNo}', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\GalleryController::galleriesList',
  'pageNo' => null,
)));
$routes->add('catalog_create', new Routing\Route('/catalog/create', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\CatalogController::createCatalog',
)));
$routes->add('catalog_update', new Routing\Route('/catalog/update/{catalogCode}', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\CatalogController::updateCatalog',
)));
$routes->add('catalog_delete', new Routing\Route('/catalog/delete/{catalogCode}', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\CatalogController::deleteCatalog',
)));
$routes->add('catalog_list', new Routing\Route('/catalog/list', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\CatalogController::catalogsList',
)));
$routes->add('catalog_items', new Routing\Route('/catalog/items/{catalogCode}/{pageNo}', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\CatalogController::catalogItems',
  'catalogCode' => null,
  'pageNo' => null,
)));
$routes->add('catalog_view', new Routing\Route('/catalog/view/{catalogCode}/{pageNo}', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\CatalogController::catalogView',
  'catalogCode' => null,
  'pageNo' => null,
)));
$routes->add('add_item_to_catalog', new Routing\Route('/async-catalogitem', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\CatalogController::addItemToCatalog',
)));
$routes->add('remove_item_from_catalog', new Routing\Route('/async-catalogitem-remove', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\CatalogController::removeItemFromCatalog',
)));

// create catalog categories
$routes->add('ecom_category_create', new Routing\Route('/ecom/category/create', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\CategoriesController::createCategory',
)));
$routes->add('ecom_category_update', new Routing\Route('/ecom/category/update/{categoryID}', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\CategoriesController::updateCategory',
  'categoryID' => null,
)));
$routes->add('ecom_category_delete', new Routing\Route('/ecom/category/delete/{categoryID}', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\CategoriesController::deleteCategory',
  'categoryID' => null,
)));
$routes->add('ecom_category_list', new Routing\Route('/ecom/categories/list', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\CategoriesController::categoriesList',
)));
$routes->add('ecom_sub_category_list', new Routing\Route('/ecom/subcategories/{categoryID}', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\CategoriesController::subCategoriesList',
  'categoryID' => null,
)));

// app content
$routes->add('ecom_app_content_create', new Routing\Route('/ecom/app-content/create', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\AppContentController::createContent',
)));
$routes->add('ecom_app_content_update', new Routing\Route('/ecom/app-content/update/{contentID}', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\AppContentController::updateContent',
  'categoryID' => null,
)));
$routes->add('ecom_app_content_delete', new Routing\Route('/ecom/app-content/delete/{contentID}', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\AppContentController::deleteContent',
  'categoryID' => null,
)));
$routes->add('ecom_app_content_list', new Routing\Route('/ecom/app-content/list', array(
  '_controller' => 'ClothingRm\\Ecommerce\\Controller\\AppContentController::contentList',
)));

// lead routes.
$routes->add('lead_create', new Routing\Route('/lead/create', array(
  '_controller' => 'Leads\\Controller\\LeadsController::leadCreateAction',
)));
$routes->add('lead_update', new Routing\Route('/lead/update/{leadCode}', array(
  '_controller' => 'Leads\\Controller\\LeadsController::leadUpdateAction',
)));
$routes->add('lead_remove', new Routing\Route('/lead/remove/{leadCode}', array(
  '_controller' => 'Leads\\Controller\\LeadsController::leadRemoveAction',
  'leadCode' => null,
)));
$routes->add('leads_list', new Routing\Route('/leads/list/{pageNo}/{perPage}', array(
  '_controller' => 'Leads\\Controller\\LeadsController::leadListAction',
  'pageNo' => 1,
  'perPage' => 100,
)));
$routes->add('lead_details', new Routing\Route('/lead/details/{leadCode}', array(
  '_controller' => 'Leads\\Controller\\LeadsController::leadDetailsAction',
)));
$routes->add('lead_import', new Routing\Route('/lead/import', array(
  '_controller' => 'Leads\\Controller\\LeadsController::importLeadsAction',
)));

// task routes.
$routes->add('task_create', new Routing\Route('/task/create', array(
  '_controller' => 'Tasks\\Controller\\TasksController::taskCreateAction',
)));
$routes->add('task_update', new Routing\Route('/task/update/{taskCode}', array(
  '_controller' => 'Tasks\\Controller\\TasksController::taskUpdateAction',
)));
$routes->add('task_remove', new Routing\Route('/task/remove/{taskCode}', array(
  '_controller' => 'Tasks\\Controller\\TasksController::taskRemoveAction',
  'taskCode' => null,
)));
$routes->add('tasks_list', new Routing\Route('/tasks/list/{pageNo}/{perPage}', array(
  '_controller' => 'Tasks\\Controller\\TasksController::taskListAction',
  'pageNo' => 1,
  'perPage' => 100,
)));
$routes->add('task_details', new Routing\Route('/task/details/{taskCode}', array(
  '_controller' => 'Tasks\\Controller\\TasksController::taskDetailsAction',
)));

// appt routes.
$routes->add('appt_create', new Routing\Route('/appointment/create', array(
  '_controller' => 'Appointments\\Controller\\AppointmentsController::appointmentCreateAction',
)));
$routes->add('appt_update', new Routing\Route('/appointment/update/{appointmentCode}', array(
  '_controller' => 'Appointments\\Controller\\AppointmentsController::appointmentUpdateAction',
)));
$routes->add('appt_remove', new Routing\Route('/appointment/remove/{appointmentCode}', array(
  '_controller' => 'Appointments\\Controller\\AppointmentsController::appointmentRemoveAction',
  'appointmentCode' => null,
)));
$routes->add('appts_list', new Routing\Route('/appointments/list/{pageNo}/{perPage}', array(
  '_controller' => 'Appointments\\Controller\\AppointmentsController::appointmentListAction',
  'pageNo' => 1,
  'perPage' => 100,
)));
$routes->add('appt_details', new Routing\Route('/appointment/details/{appointmentCode}', array(
  '_controller' => 'Appointments\\Controller\\AppointmentsController::taskDetailsAction',
)));

// app user routes
$routes->add('app_user_create', new Routing\Route('/app-user/create', array(
  '_controller' => 'User\\Controller\\UserController::createAppUserAction',
)));
$routes->add('app_user_list', new Routing\Route('/users/app', array(
  '_controller' => 'User\\Controller\\UserController::listAppUsersAction',
)));
$routes->add('app_user_update', new Routing\Route('/app-users/update/{uuid}', array(
  '_controller' => 'User\\Controller\\UserController::updateUserAction',
  'uuid' => null,
)));

// whatsapp routes
$routes->add('whatsapp_shipping_update', new Routing\Route('/whatsapp/shipping-update', array(
  '_controller' => 'Whatsapp\\Controller\\WhatsappController::pushShippingUpdate',
)));

// whatsapp callback hook
$routes->add('whatsapp_message_delivery_callback', new Routing\Route('/whatsapp/callback-hook', array(
  '_controller' => 'Whatsapp\\Controller\\WhatsappController::callBackHook',
)));

return $routes;
