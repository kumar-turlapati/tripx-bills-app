<aside>
  <div id="sidebar"  class="nav-collapse "> 
    <ul class="sidebar-menu">
      <li class="active">
        <a class="" href="/dashboard"> <i class="icon_house_alt"></i> Dashboard</a> 
      </li>
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-bars"></i> Masters <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li><a href="/products/list"><i class="fa fa-cubes"></i> Products</a></li>
          <li><a href="/categories/list"><i class="fa fa-window-restore"></i> Product Categories</a></li>
          <li><a href="/suppliers/list"><i class="fa fa-address-card"></i> Suppliers</a></li>
          <li><a href="/customers/list"><i class="fa fa-smile-o"></i> Customers</a></li>
          <li><a href="/taxes/list"><i class="fa fa-scissors"></i> Taxes</a></li>
          <li><a href="/loyalty-members/list"><i class="fa fa-diamond"></i> Loyalty Members</a></li>
          <li><a href="/locations/list"><i class="fa fa-window-restore"></i> Stores</a></li>
          <li><a href="/fin/bank/list"><i class="fa fa-university"></i> Banks</a></li>
          <li><a href="/bu/list"><i class="fa fa-user-circle-o"></i> Business Users</a></li>
        </ul>
      </li>
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-newspaper-o"></i> Vouchers <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li><a href="/inward-entry"><i class="fa fa-compass"></i> Inward entry</a></li>
          <li><a href="/inward-entry/bulk-upload"><i class="fa fa-upload"></i> Inward entry from files</a></li>          
          <?php /*<li><a href="/sales/entry"><i class="fa fa-inr"></i> Sales entry</a></li> */ ?>
          <li><a href="/sales/entry-with-barcode"><i class="fa fa-inr"></i> Sales entry</a></li>          
          <li><a href="/fin/payment-voucher/create"><i class="fa fa-question"></i> Payment</a></li>
          <li><a href="/fin/receipt-voucher/create"><i class="fa fa-circle-o"></i> Receipt</a></li>
          <li><a href="/stock-transfer/out"><i class="fa fa-truck"></i> Stock transfer</a></li>
          <li><a href="/fin/pc-voucher/create"><i class="fa fa-money"></i> Petty cash voucher</a></li>
          <li><a href="/inventory/stock-adjustment"><i class="fa fa-adjust"></i> Stock adjustment</a></li>
          <li><a href="/sales-indent/create"><i class="fa fa-delicious"></i> Sales indent</a></li>                  
        </ul>
      </li>
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-book"></i> Registers <span class="menu-arrow arrow_carrot-right"></span> 
        </a>
        <ul class="sub">
          <li><a href="/sales/list"><i class="fa fa-inr"></i> Sales register</a></li>
          <li><a href="/sales-return/list"><i class="fa fa-inr"></i> Sales return register</a></li>
          <li><a href="/inward-entry/list"><i class="fa fa-compass"></i> Inward register</a></li>
          <li><a href="/grn/list"><i class="fa fa-laptop"></i> GRN register</a></li>
          <li><a href="/fin/payment-vouchers"><i class="fa fa-inr"></i> Payments register</a></li> 
          <li><a href="/fin/credit-notes"><i class="fa fa-sign-out"></i> Credit notes register</a></li>
          <li><a href="/stock-transfer/register"><i class="fa fa-truck"></i> Stock transfer register</a></li>          
          <li><a href="/fin/pc-vouchers"><i class="fa fa-money"></i> Petty cash register</a></li>
          <li><a href="/fin/petty-cash-book"><i class="fa fa-inr"></i> Petty cash book</a></li>
          <li><a href="/inventory/stock-adjustments-list"><i class="fa fa-adjust"></i> Stock adjustment register</a></li>
          <li><a href="/purchase-return/register"><i class="fa fa-laptop"></i> Purchase return register</a></li>
          <li><a href="/barcodes/list"><i class="fa fa-barcode"></i> Barcodes register</a></li>
          <li><a href="/sales-indents/list"><i class="fa fa-inr"></i> Sales indent register</a></li>
        </ul>
      </li>
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-database"></i> Inventory <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <?php /*
          <li><a href="/inventory/item-threshold-list"><i class="fa fa-list-ol"></i> Threshold Qtys.</a></li>
          <li><a href="/inventory/track-item"><i class="fa fa-exchange"></i> Item Track</a></li>*/ ?>
          <li><a href="/opbal/list"><i class="fa fa-inbox"></i> Inventory openings</a></li>          
          <li><a href="/inventory/available-qty"><i class="fa fa-cubes"></i> Stock in hand</a></li>
          <li><a href="/barcode/opbal"><i class="fa fa-barcode"></i> Barcodes for opening</a></li>          
        </ul>
      </li>
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-search"></i> Search <span class="menu-arrow arrow_carrot-right"></span> 
        </a>
        <ul class="sub">
          <li><a href="/sales/search-bills"><i class="fa fa-square-o"></i> Sale Bills</a></li>
        </ul>
      </li>
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-lemon-o"></i> Promo Offers <span class="menu-arrow arrow_carrot-right"></span> 
        </a>
        <ul class="sub">
          <li><a href="/promo-offers/entry"><i class="fa fa-plus-circle"></i> Create promo offer</a></li>
          <li><a href="/promo-offers/list"><i class="fa fa-list"></i> Promo offers list</a></li>          
        </ul>
      </li>
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-magic"></i> Marketing <span class="menu-arrow arrow_carrot-right"></span> 
        </a>
        <ul class="sub">
          <li><a href="/campaigns/list"><i class="fa fa-random"></i> Campaigns</a></li>
        </ul>
      </li>      
      <li class="sub-menu">
        <a href="javascript:" class="">
          <i class="fa fa-cogs"></i> Admin Panel <span class="menu-arrow arrow_carrot-right"></span> 
        </a>
        <ul class="sub">
          <li>
            <a href="/upload-inventory" title="Upload inventory and opening balances">
              <i class="fa fa-upload"></i> Import Inventory
            </a>
          </li>
          <li>
            <a href="/devices/list" title="Register devices to access the application">
              <i class="fa fa-lock"></i> Devices List
            </a>
          </li>
          <li>
            <a href="/users/list" title="Manage users who use this app">
              <i class="fa fa-user-circle"></i> System Users
            </a>
          </li>
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
              <li><a href="/report-options/stock-report"><i class="fa fa-angle-right"></i> Stock Report</a></li>
              <li><a href="/report-options/opening-balances"><i class="fa fa-angle-right"></i> Op.Bal Report</a></li>              
            </ul>
          </li>
          <li class="sub-menu">
            <a href="javascript:">
              <i class="fa fa-inr"></i> Sales <span class="menu-arrow arrow_carrot-right"></span> 
            </a>
            <ul class="sub">
              <li><a href="/report-options/sales-register"><i class="fa fa-angle-right"></i> Sales Register</a></li>
              <li><a href="/report-options/itemwise-sales-report"><i class="fa fa-angle-right"></i> Itemwise Sales</a></li>
              <li><a href="/report-options/itemwise-sales-returns"><i class="fa fa-angle-right"></i> Itemwise Sales Returns</a></li>
              <li><a href="/report-options/sales-return-register"><i class="fa fa-angle-right"></i> Sales Return Register</a></li>
              <li><a href="/report-options/day-sales-report"><i class="fa fa-angle-right"></i> Sales by Day</a></li>              
            </ul>            
          </li>
          <li class="sub-menu">
            <a href="javascript:">
              <i class="fa fa-plane"></i> Marketing <span class="menu-arrow arrow_carrot-right"></span> 
            </a>
            <ul class="sub">
              <li><a href="/report-options/indent-item-avail"><i class="fa fa-angle-right"></i> Item Availability</a></li>
              <li><a href="/report-options/indent-itemwise"><i class="fa fa-angle-right"></i> Indents Itemwise</a></li>
              <li><a href="/report-options/indent-agentwise"><i class="fa fa-angle-right"></i> Indents Ag.wise</a></li>
              <li><a href="/report-options/indent-statewise"><i class="fa fa-angle-right"></i> Indents Statewise</a></li>
              <li><a href="/report-options/print-indents-agentwise"><i class="fa fa-angle-right"></i> Indents All By Agent</a></li> 
              <li><a href="/report-options/indent-register"><i class="fa fa-angle-right"></i> Indent Register</a></li>
              <li><a href="/report-options/indent-dispatch-summary"><i class="fa fa-angle-right"></i> Dispatch Summary</a></li>              
            </ul>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</aside>