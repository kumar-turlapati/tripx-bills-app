<?php
  $category_id = isset($_SESSION['bc'])&&$_SESSION['bc']>0?(int)$_SESSION['bc']:0;

  $products_label = (isset($category_id) && $category_id===1)?'Medicines':'Products';
  $products_url = (isset($category_id) && $category_id===1)?'/medicines/list':'/products/list';
  $products_icon = (isset($category_id) && $category_id===1)?'fa fa-medkit':'fa fa-tasks';

  $cust_label = (isset($category_id) && $category_id===1)?'Patients':'Customers';
  $cust_url = (isset($category_id) && $category_id===1)?'/patients/list':'/customers/list';  
?>
<!--sidebar start-->
<aside>
  <div id="sidebar"  class="nav-collapse "> 
    <!-- sidebar menu start-->
    <ul class="sidebar-menu">
      <li class="active"> <a class="" href="/dashboard"> <i class="icon_house_alt"></i> Dashboard </a> </li>
      <li class="sub-menu">
        <a href="javascript:" class="">
          <i class="fa fa-bars"></i> Masters <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li><a href="<?php echo $cust_url ?>"><i class="fa fa-smile-o"></i> <?php echo $cust_label ?></a></li>        
          <li><a href="<?php echo $products_url ?>"><i class="<?php echo $products_icon ?>"></i> <?php echo $products_label ?></a></li>
          <?php if($category_id===1): ?>
            <li><a href="/doctors/list"><i class="fa fa-user-md"></i> Doctors</a></li>
          <?php endif; ?>         
          <li><a href="/suppliers/list"><i class="fa fa-users"></i> Suppliers</a></li>
        </ul>
      </li>
      <li class="sub-menu">
        <a href="javascript:" class="">
          <i class="fa fa-inr"></i> Sales <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li><a href="/sales/entry"><i class="fa fa-keyboard-o"></i> Sales Entry</a></li>
          <li><a href="/sales/list"><i class="fa fa-list-ol"></i> Daywise Sales List</a></li>          
          <li><a href="/sales-return/list"><i class="fa fa-repeat"></i> Daywise Sales Return List</a></li>          
        </ul>
      </li>
      <li class="sub-menu">
        <a href="javascript:" class="">
          <i class="fa fa-compass"></i> Purchases <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li><a href="/inward-entry"><i class="fa fa-keyboard-o"></i> Purchase Entry</a></li>
          <li><a href="/purchase/list"><i class="fa fa-list-ol"></i> Monthwise Purchases List</a></li>          
        </ul>
      </li>
      <li class="sub-menu">
        <a href="javascript:" class="">
          <i class="fa fa-database"></i> Inventory&nbsp;&amp;&nbsp;Stores<span class="menu-arrow arrow_carrot-right"></span> 
        </a>
        <ul class="sub">
          <li><a href="/inventory/track-item"><i class="fa fa-exchange"></i> Item Track</a></li>          
          <li><a href="/inventory/available-qty"><i class="fa fa-medkit"></i> Available Qtys.</a></li>
          <li><a href="/grn/list"><i class="fa fa-list-ol"></i> GRNs</a></li>          
        </ul>
      </li>        
      <li class="sub-menu">
        <a href="javascript:" class="">
          <i class="fa fa-money"></i> Finance <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li><a href="/fin/billwise-outstanding"><i class="fa fa-question"></i> Payables - Billwise</a></li>          
          <li><a href="/fin/supp-outstanding-ason"><i class="fa fa-check"></i> Payables - As on date</a></li>
          <li><a href="/fin/receivables-ason"><i class="fa fa-bullseye"></i> Receivables - As on date</a></li>
          <li class="sub-menu">
            <a href="javascript:void(0);" class="">
              <i class="fa fa-keyboard-o"></i> Vouchers<span class="menu-arrow arrow_carrot-right"></span> 
            </a>
            <ul class="sub">
              <li><a href="/fin/payment-voucher/create"><i class="fa fa-inr"></i> Payment Vouchers</a></li>
              <li><a href="/fin/receipt-voucher/create"><i class="fa fa-question"></i> Receipt Vouchers</a></li>
              <li><a href="/fin/payment-vouchers"><i class="fa fa-inr"></i> Payment Vouchers List</a></li> 
              <li><a href="/fin/receipt-vouchers"><i class="fa fa-check"></i> Receipt Vouchers List</a></li> 
            </ul>
          </li>
        </ul>
      </li>      
      <li class="sub-menu">
        <a href="javascript:" class="">
          <i class="fa fa-search"></i> Search <span class="menu-arrow arrow_carrot-right"></span> 
        </a>
        <ul class="sub">
          <li><a href="/inventory/search-medicines"><i class="fa fa-medkit"></i> Medicines</a></li>
          <li><a href="/sales/search-bills"><i class="fa fa-square-o"></i> Sale Bills</a></li>
        </ul>        
      </li>
      <li class="sub-menu">
        <a href="javascript:">
          <i class="fa fa-sitemap fa-3x"></i> Reports <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li class="sub-menu">
            <a data-toggle="modal" href="javascript:">
              <i class="fa fa-database"></i> Inventory&nbsp;&amp;&nbsp;Stores <span class="menu-arrow arrow_carrot-right"></span>
            </a>
            <ul class="sub">
              <li><a href="/report-options/grn-register"><i class="fa fa-angle-right"></i> GRN Register</a></li>
              <li><a href="/report-options/expiry-report"><i class="fa fa-angle-right"></i> Expiry Report</a></li>
              <li><a href="/report-options/material-movement"><i class="fa fa-angle-right"></i> Material Movement</a></li>
              <li><a href="/print-itemthr-level" target="_blank"><i class="fa fa-angle-right"></i> Threshold Report</a></li>
              <li><a href="/item-master" target="_blank"><i class="fa fa-angle-right"></i> Inventory master</a></li>
            </ul>
          </li>
          <li class="sub-menu">
            <a href="javascript:">
              <i class="fa fa-inr"></i> Sales <span class="menu-arrow arrow_carrot-right"></span> 
            </a>
            <ul class="sub">
              <li><a href="/report-options/sales-register"><i class="fa fa-angle-right"></i> Sales Register</a></li>
              <li><a href="/report-options/sales-by-mode"><i class="fa fa-angle-right"></i> Credit Sales</a></li>              
              <li><a href="/report-options/itemwise-sales-report"><i class="fa fa-angle-right"></i> Itemwise Sales</a></li>
              <li><a href="/report-options/itemwise-sales-report-bymode"><i class="fa fa-angle-right"></i> Itemwise Sales (Mode)</a></li>
              <li><a href="/report-options/itemwise-sales-returns"><i class="fa fa-angle-right"></i> Itemwise Sales Returns</a></li>              
              <li><a href="/report-options/sales-return-register"><i class="fa fa-angle-right"></i> Sales Return Register</a></li>
              <li><a href="/report-options/day-sales-report"><i class="fa fa-angle-right"></i> Sales by Day</a></li>
              <li><a href="/report-options/sales-summary-patient"><i class="fa fa-angle-right"></i> Patient Bill Summary</a></li>
            </ul>            
          </li>
          <li class="sub-menu">
            <a href="javascript:" class="">
              <i class="fa fa-money"></i> Finance <span class="menu-arrow arrow_carrot-right"></span> 
            </a>
            <ul class="sub">
              <li><a href="/report-options/supplier-payments-due"><i class="fa fa-group"></i> Supp. Payments Due</a></li>
              <li><a href="/report-options/payables-monthwise"><i class="fa fa-check"></i> Payables - Monthwise</a></li> 
            </ul>
          </li>          
        </ul>
      </li>
    </ul>
    <!-- sidebar menu end--> 
  </div>
</aside>
<!--sidebar end-->