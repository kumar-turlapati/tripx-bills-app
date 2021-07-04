<aside>
  <div id="sidebar"  class="nav-collapse"> 
    <ul class="sidebar-menu">
      <li class="active">
        <a class="" href="/dashboard"> <i class="icon_house_alt"></i> Dashboard</a> 
      </li>
      <li>
        <a href="/finy/switch"> <i class="fa fa-exchange"></i> Switch Financial Year</a> 
      </li>      
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-bars"></i> Masters <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li><a href="/customers/list"><i class="fa fa-smile-o"></i>&nbsp;Customers</a></li>
          <li><a href="/products/list"><i class="fa fa-cubes"></i>&nbsp;Products</a></li>
          <?php /*<li><a href="/loyalty-members/list"><i class="fa fa-diamond"></i> Loyalty Members</a></li> */ ?>
        </ul>
      </li>
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-newspaper-o"></i> Vouchers <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li><a href="/sales-indent/create"><i class="fa fa-delicious"></i> Sales Indent</a></li>
          <li><a href="/sales-indent/create/mobile"><i class="fa fa-mobile"></i> Sales Indent (Mobile V.)</a></li>
          <li><a href="/sales-indent/create-from-samples"><i class="fa fa-delicious"></i> Sales Indent (Samples)</a></li>
          <li><a href="/sales/entry"><i class="fa fa-keyboard-o"></i> Sales Entry - Manual</a></li>
          <li><a href="/sales/entry-with-barcode"><i class="fa fa-barcode"></i> Sales Entry - Barcode</a></li>
          <li><a href="/sales-entry/combos"><i class="fa fa-shopping-basket" aria-hidden="true"></i> Sales Entry - Combos</a></li>
          <li><a href="/stock-transfer/out"><i class="fa fa-truck"></i> Stock transfer</a></li>
          <li><a href="/fin/cash-voucher/create"><i class="fa fa-inr"></i> Cash Voucher</a></li>
          <?php /*<li><a href="/stock-audit/create"><i class="fa fa-check"></i> Phy. Stock Audit</a></li> */ ?>
          <li><a href="/fin/credit-note/create"><i class="fa fa-sign-out"></i> Credit note</a></li>
          <li><a href="/fin/receipt-voucher/create"><i class="fa fa-circle-o"></i> Receipt</a></li>
        </ul>
      </li>
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-book"></i> Registers <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li><a href="/sales/list"><i class="fa fa-inr"></i> Sales Register</a></li>
          <li><a href="/sales-return/list"><i class="fa fa-undo"></i> Sales Return Register</a></li>
          <li><a href="/fin/credit-notes"><i class="fa fa-sign-out"></i> Credit Notes Register</a></li>
          <li><a href="/stock-transfer/register"><i class="fa fa-truck"></i> Stock Transfer Register</a></li>
          <li><a href="/fin/cash-vouchers"><i class="fa fa-money"></i> Cash Register</a></li>
          <li><a href="/fin/cash-book"><i class="fa fa-book"></i> Cash Book</a></li>
          <li><a href="/barcodes/list"><i class="fa fa-barcode"></i> Barcodes Register</a></li>          
          <?php /*<li><a href="/discount-manager"><i class="fa fa-hand-peace-o"></i> Discount Manager</a></li> */ ?>
          <li><a href="/fin/sales2cb/register"><i class="fa fa-arrow-right"></i> Sales2CB Register</a></li>
          <li><a href="/sales-indents/list"><i class="fa fa-delicious"></i> Sales Indent Register</a></li>
          <li><a href="/indent-vs-sales"><i class="fa fa-compress"></i> Indent Vs Sales</a></li>
          <li><a href="/indent-vs-sales-by-item"><i class="fa fa-compress"></i> Indent vs Sales(Item)</a></li>
          <?php /*<li><a href="/stock-audit/register"><i class="fa fa-check"></i> Stock Audit Register</a></li> */ ?>
        </ul>
      </li>
      <li class="sub-menu">
        <a href="javascript:void(0)">
          <i class="fa fa-search"></i> Search <span class="menu-arrow arrow_carrot-right"></span> 
        </a>
        <ul class="sub">
          <li><a href="/sales/search-bills"><i class="fa fa-inr"></i> Invoices</a></li>
          <li><a href="/inventory/track-item"><i class="fa fa-angle-double-up"></i> Item Track</a></li>
        </ul>
      </li>
      <?php /*
      <li>
        <a href="/inventory/available-qty"><i class="fa fa-database"></i> Stock In Hand</a>
      </li> */?>
      <li>
        <a href="/tasks/list"><i class="fa fa-tasks"></i> My Tasks</a>
      </li>
      <li class="sub-menu">
        <a href="javascript:">
          <i class="fa fa-sitemap fa-3x"></i> Reports <span class="menu-arrow arrow_carrot-right"></span>
        </a>
        <ul class="sub">
          <li class="sub-menu">
            <a href="javascript:void(0);">
              <i class="fa fa-inr"></i> Sales <span class="menu-arrow arrow_carrot-right"></span> 
            </a>
            <ul class="sub">
              <li><a href="/reports/sales-register"><i class="fa fa-angle-right"></i> Sales Register</a></li>
              <li><a href="/reports/day-sales"><i class="fa fa-angle-right"></i> Sales by Day</a></li>
              <li><a href="/reports/itemwise-sales-register"><i class="fa fa-angle-right"></i> Itemwise Sales Register</a></li>
              <li><a href="/reports/sales-billwise-itemwise-casewise"><i class="fa fa-angle-right"></i> Casewise Sales Register</a></li>              
              <li><a href="/reports/sales-billwise-itemwise"><i class="fa fa-angle-right"></i> Bill&amp;Item wise Sales</a></li>
              <li><a href="/reports/sales-dispatch-register"><i class="fa fa-angle-right"></i> Sales Dispatch Register</a></li>
              <li><a href="/reports/sales-upi-register"><i class="fa fa-angle-right"></i>UPI/EMI Pmts Register</a></li>
            </ul>
          </li>
          <li class="sub-menu">
            <a data-toggle="modal" href="javascript:">
              <i class="fa fa-database"></i> Inventory&nbsp;&amp;&nbsp;Stores <span class="menu-arrow arrow_carrot-right"></span>
            </a>
            <ul class="sub">
              <li><a href="/reports/stock-transfer-register"><i class="fa fa-angle-right"></i> Stock Transfer</a></li>
              <li><a href="/reports/stock-adjustment-register"><i class="fa fa-angle-right"></i> Stock Adjustments</a></li>              
            </ul>
          </li>
          <li class="sub-menu">
            <a href="javascript:">
              <i class="fa fa-money"></i> Finance <span class="menu-arrow arrow_carrot-right"></span> 
            </a>
            <ul class="sub">
              <li><a href="/reports/receivables"><i class="fa fa-angle-right"></i> Receivables</a></li>              
            </ul>            
          </li>          
        </ul>
      </li>
    </ul>
  </div>
</aside>