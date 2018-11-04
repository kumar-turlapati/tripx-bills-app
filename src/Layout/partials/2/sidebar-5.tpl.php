<aside>
  <div id="sidebar"  class="nav-collapse"> 
    <ul class="sidebar-menu">
      <li class="active">
        <a class="" href="/dashboard"> <i class="icon_house_alt"></i> Dashboard</a> 
      </li>
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-bars"></i> Masters <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li><a href="/customers/list"><i class="fa fa-smile-o"></i> Customers</a></li>
          <li><a href="/loyalty-members/list"><i class="fa fa-diamond"></i> Loyalty Members</a></li>          
        </ul>
      </li>
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-newspaper-o"></i> Vouchers <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li><a href="/sales/entry"><i class="fa fa-keyboard-o"></i> Sales Entry - Manual</a></li>
          <li><a href="/sales/entry-with-barcode"><i class="fa fa-barcode"></i> Sales Entry - Barcode</a></li>          <li><a href="/stock-transfer/out"><i class="fa fa-truck"></i> Stock transfer</a></li>
          <li><a href="/fin/pc-voucher/create"><i class="fa fa-inr"></i> Petty Cash Voucher</a></li>
        </ul>
      </li>
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-book"></i> Registers <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li><a href="/sales/list"><i class="fa fa-inr"></i> Sales Register</a></li>
          <li><a href="/sales-return/list"><i class="fa fa-inr"></i> Sales Return Register</a></li>
          <li><a href="/fin/credit-notes"><i class="fa fa-sign-out"></i> Credit Notes Register</a></li>
          <li><a href="/stock-transfer/register"><i class="fa fa-truck"></i> Stock Transfer Register</a></li>
          <li><a href="/fin/pc-vouchers"><i class="fa fa-money"></i> Petty Cash Register</a></li>
          <li><a href="/fin/petty-cash-book"><i class="fa fa-inr"></i> Petty Cash Book</a></li>
          <li><a href="/barcodes/list"><i class="fa fa-barcode"></i> Barcodes Register</a></li>          
        </ul>
      </li>
      <?php /*
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-database"></i> Inventory <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li><a href="/inventory/track-item"><i class="fa fa-exchange"></i> Item Track</a></li>          
          <li><a href="/inventory/available-qty"><i class="fa fa-cubes"></i> Available Qtys.</a></li>
        </ul>
      </li> */ ?>
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-search"></i> Search <span class="menu-arrow arrow_carrot-right"></span> 
        </a>
        <ul class="sub">
          <li><a href="/sales/search-bills"><i class="fa fa-inr"></i> Invoices</a></li>
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
              <li><a href="/reports/sales-register"><i class="fa fa-angle-right"></i> Sales Register</a></li>
              <li><a href="/reports/day-sales"><i class="fa fa-angle-right"></i> Sales by Day</a></li>
              <?php /*<li><a href="/reports/sales-summary-by-month"><i class="fa fa-angle-right"></i> Sales Summary</a></li> */ ?>
            </ul>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</aside>