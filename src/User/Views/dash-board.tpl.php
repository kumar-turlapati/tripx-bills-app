<?php 
  // echo $cur_month;
  // echo $cur_year;
  // dump($cal_months);
  // dump($cal_years);
  // dump($_SESSION);
  $user_type = (int)$_SESSION['utype'];
  $tabs = [];

  if($user_type === 3 || $user_type === 9) {
    $tabs = [
              '#tSales' => ['Sales', 'tSales'], 
              '#tPurchases' => ['Purchases', 'tPurchases'], 
              '#tInventory' => ['Inventory', 'tInventory'], 
              '#tFinance' => ['Finance', 'tFinance'],
            ];
  } elseif($user_type === 7) {
    $tabs = [
              '#tPurchases' => ['Purchases', 'tPurchases'], 
              '#tInventory' => ['Inventory', 'tInventory'], 
            ];
  } else {
    $tabs = [
              '#tSales' => ['Sales', 'tSales'], 
            ];
  }
?>
<div>
  <?php if( count($tabs) > 0):?>
    <ul class="nav nav-tabs" role="tablist" id="dbContainer">
    <?php foreach($tabs as $key => $tab_details): ?>
      <li role="presentation" class="active">
        <a href="<?php echo $key ?>" aria-controls="<?php echo $tab_details[1] ?>" role="tab" data-toggle="tab"><?php echo $tab_details[0] ?></a>
      </li>
    <?php endforeach; ?>
    </ul>

    <div class="tab-content">
      <?php if($user_type === 3 || $user_type === 9 || $user_type === 5): ?>
        <div role="tabpanel" class="tab-pane active" id="tSales">
          <div class="row">
            <div class="col-md-6" id="daySales">
              <div class="widgetSec">
                <div class="widgetHeader">Today's Sale - <?php echo $today ?></div>
                <div class="widgetContent">
                  <table class="table priceTable">
                    <tbody>
                      <tr>
                        <td>Cash Sale</td>
                        <td align="right"><div id="ds-cashsale"></div></td>
                      </tr>
                      <tr>
                        <td>Card Sale</td>
                        <td align="right"><div id="ds-cardsale"></div></td>
                      </tr>
                      <tr>
                        <td>Card / Cash / Cnote Sale</td>
                        <td align="right"><div id="ds-splitsale"></div></td>
                      </tr>
                      <?php /*
                      <tr>
                        <td><b>Totals</b></td>
                        <td align="right"><b><span id="ds-totals"></span></b></td>
                      </tr>
                      <tr>
                        <td ><b>Sales Return</b></td>
                        <td align="right"><b><span id="ds-returns"></span></b></td>
                      </tr>*/?>
                      <tr>
                        <td ><b>Net Sales</b></td>
                        <td align="right"><b><span id="ds-netsale"></span></b></td>
                      </tr>
                      <tr>
                        <td ><b>Cash in hand</b></td>
                        <td align="right"><b><span id="ds-cashinhand"></span></b></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <?php if($user_type === 3 || $user_type === 9): ?>
              <div class="col-md-6" id="monthwiseSales">
                <div class="widgetSec">
                  <div class="widgetHeader">Cumulative sales for the month of <?php echo $mon_year_string ?></div>
                  <div class="widgetContent">
                    <table class="table priceTable">
                      <tbody>
                        <tr>
                          <td>Cash Sale</td>
                          <td align="right"><div id="cs-cashsale"></div></td>
                        </tr>
                        <tr>
                          <td>Card Sale</td>
                          <td align="right"><div id="cs-cardsale"></div></td>
                        </tr>
                        <tr>
                          <td>Card / Cash / Cnote Sale</td>
                          <td align="right"><div id="cs-splitsale"></div></td>
                        </tr>
                        <tr>
                          <td ><b>Net Sales</b></td>
                          <td align="right"><b><span id="cs-netsale" style="text-decoration:underline;"></span></b></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>
          <?php if($user_type === 3 || $user_type === 9): ?>      
            <div class="row" id="salesDayGraph">
              <div class="col-md-12">
                <div class="widgetSec">
                  <div class="widgetHeader">Daywise Sales Summary</div>
                  <div class="widgetContent">
                    <div class="subHeader">
                    <form class="form-inline" id="salesGraphFilter">
                      <select class="form-control" id="sgf-month">
                        <?php 
                          foreach($cal_months as $key=>$value):
                            $selected = ((int)$key===(int)$cur_month?'selected':'');
                        ?>
                         <option value="<?php echo $key ?>" <?php echo $selected ?>>
                            <?php echo $value ?>
                         </option>
                        <?php endforeach; ?>
                      </select>
                      <select class="form-control" id="sgf-year">
                        <?php 
                          foreach($cal_years as $key=>$value): 
                            $selected = ((int)$key==(int)$cur_year?'selected':'');                
                        ?>
                         <option value="<?php echo $key ?>" <?php echo $selected ?>>
                            <?php echo $value ?>
                         </option>
                        <?php endforeach; ?>
                      </select>
                      <input type="hidden" name="saleMonth" id="saleMonth" value="<?php echo $cur_month ?>" />
                      <input type="hidden" name="saleYear" id="saleYear" value="<?php echo $cur_year ?>" />                
                      <input class="btn btn-primary" type="button" value="Reload" id="sfGraphReload" name="sfGraphReload" />
                     </form>
                    </div>
                     <div id="salesGraph"></div>              
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <div role="tabpanel" class="tab-pane" id="tPurchases">...</div>
      <div role="tabpanel" class="tab-pane" id="tInventory">...</div>
      <div role="tabpanel" class="tab-pane" id="tFinance">...</div>
    </div>
  <?php endif; ?>
</div>