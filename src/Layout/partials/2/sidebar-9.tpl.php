<aside>
  <div id="sidebar" class="nav-collapse">
    <ul class="sidebar-menu">
      <li class="active">
        <a class="" href="/dashboard"> <i class="icon_house_alt"></i> Dashboard</a> 
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
          <li><a href="/loyalty-members/list"><i class="fa fa-diamond"></i> Loyalty members register</a></li>
          <li><a href="/fin/petty-cash-book"><i class="fa fa-inr"></i> Petty cash book</a></li>
          <li><a href="/purchase-return/register"><i class="fa fa-laptop"></i> Purchase return register</a></li>
          <li><a href="/barcodes/list"><i class="fa fa-barcode"></i> Barcodes register</a></li>
          <li><a href="/sales-indents/list"><i class="fa fa-inr"></i> Sales indent register</a></li>
          <li><a href="/bu/list"><i class="fa fa-user-circle-o"></i> Business Users</a></li>          
        </ul>
      </li>      
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-database"></i> Inventory <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li><a href="/opbal/list"><i class="fa fa-inbox"></i> Inventory openings</a></li>          
          <li><a href="/inventory/available-qty"><i class="fa fa-cubes"></i> Stock in hand</a></li>
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
          <li><a href="/promo-offers/entry"><i class="fa fa-lemon-o"></i> Create promo offer</a></li>
          <li><a href="/promo-offers/list"><i class="fa fa-list"></i> Promo offers list</a></li>          
        </ul>
      </li>      
      <li class="sub-menu">
        <a href="javascript:">
          <i class="fa fa-sitemap fa-3x"></i> Reports <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
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
              <li><a href="/report-options/sales-summary-by-month"><i class="fa fa-angle-right"></i> Sales by Month</a></li>
              <li><a href="/report-options/mom-comparison"><i class="fa fa-angle-right"></i> MoM Sales Comparison</a></li>
            </ul>            
          </li>
          <li class="sub-menu">
            <a data-toggle="modal" href="javascript:">
              <i class="fa fa-database"></i> Inventory&nbsp;&amp;&nbsp;Stores <span class="menu-arrow arrow_carrot-right"></span>
            </a>
            <ul class="sub">
              <li><a href="/report-options/stock-report-new"><i class="fa fa-angle-right"></i> Stock Report</a></li>
              <li><a href="/report-options/adj-entries"><i class="fa fa-angle-right"></i> Adjustment Report</a></li>
              <li><a href="/report-options/material-movement"><i class="fa fa-angle-right"></i> Material Movement</a></li>
              <li><a href="/print-itemthr-level" target="_blank"><i class="fa fa-angle-right"></i> Threshold Report</a></li>
              <li><a href="/item-master" target="_blank"><i class="fa fa-angle-right"></i> Inventory master</a></li>
            </ul>
          </li>
          <li class="sub-menu">
            <a href="javascript:">
              <i class="fa fa-inr"></i> GST Reports <span class="menu-arrow arrow_carrot-right"></span> 
            </a>
            <ul class="sub">
              <li><a href="/report-options/sales-summary-tax-rate"><i class="fa fa-angle-right"></i> Sales - ByTaxRate</a></li>
            </ul>            
          </li>
        </ul>
      </li>
    </ul>
  </div>
</aside>

<?php 
/*
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
              <li><a href="/report-options/stock-report-new"><i class="fa fa-angle-right"></i> Stock Report</a></li>
              <li><a href="/report-options/adj-entries"><i class="fa fa-angle-right"></i> Adjustment Report</a></li>
              <li><a href="/report-options/grn-register"><i class="fa fa-angle-right"></i> GRN Register</a></li>
              <li><a href="/report-options/material-movement"><i class="fa fa-angle-right"></i> Material Movement</a></li>
              <li><a href="/print-itemthr-level" target="_blank"><i class="fa fa-angle-right"></i> Threshold Report</a></li>
              <li><a href="/report-options/io-analysis" target="_blank"><i class="fa fa-angle-right"></i> I-O Analysis</a></li>              
              <li><a href="/item-master" target="_blank"><i class="fa fa-angle-right"></i> Inventory master</a></li>
              <li><a href="/report-options/inventory-profitability"><i class="fa fa-level-up"></i> Inventory Profitability</a></li>
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
              <li><a href="/report-options/itemwise-sales-returns"><i class="fa fa-angle-right"></i> Itemwise Sales Returns</a></li>
              <li><a href="/report-options/sales-return-register"><i class="fa fa-angle-right"></i> Sales Return Register</a></li>
              <li><a href="/report-options/day-sales-report"><i class="fa fa-angle-right"></i> Sales by Day</a></li>              
              <li><a href="/report-options/sales-summary-by-month"><i class="fa fa-angle-right"></i> Sales by Month</a></li>
              <li><a href="/report-options/mom-comparison"><i class="fa fa-angle-right"></i> MoM Sales Comparison</a></li>
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
      <li class="sub-menu">
        <a href="javascript:" class="">
          <i class="fa fa-cogs"></i> Admin Panel <span class="menu-arrow arrow_carrot-right"></span> 
        </a>
        <ul class="sub">
          <li>
            <a href="/admin-options/edit-business-info"><i class="fa fa-building"></i> Update Business Details</a>
          </li>          
          <li>
            <a href="/users/list"><i class="fa fa-users"></i> Users</a>
          </li>
          <li>
            <a href="/admin-options/upload-inventory" title="Upload inventory and opening balances">
              <i class="fa fa-upload"></i> Upload inventory
            </a>
          </li>
          <li>
            <a href="/admin-options/enter-bill-no?billType=sale" title="This option allows the user to allow/remove Discount from Sale Bill">
              <i class="fa fa-inr"></i> Add/Remove Bill Discount
            </a>
          </li>
          <li>
            <a href="/admin-options/delete-sale-bill" title="Remove sale bill from system">
              <i class="fa fa-times"></i> Delete Sale Bill
            </a>
          </li>
          <li>
            <a href="/admin-options/update-batch-qtys" title="This option will update available item quantities from Stock Report that will be shown on Sales Entry screen">
              <i class="fa fa-database"></i> Update Available Qtys.
            </a>
          </li>
        </ul>        
      </li> 
      <li class="sub-menu">
        <a href="javascript:" class="">
          <i class="fa fa-database"></i> Inventory&nbsp;&amp;&nbsp;Stores<span class="menu-arrow arrow_carrot-right"></span> 
        </a>
        <ul class="sub">
          <li><a href="/opbal/list"><i class="fa fa-folder-open"></i> Openings</a></li>
          <li><a href="/inventory/stock-adjustments-list"><i class="fa fa-adjust"></i> Adjustments</a></li>        
          <li><a href="/grn/list"><i class="fa fa-list-ol"></i> GRNs</a></li>          
          <li><a href="/inventory/item-threshold-list"><i class="fa fa-bullhorn"></i> Threshold Qtys.</a></li>          
        </ul>
      </li> 
      */?>