<?php 
if($page_links_to_end>1 && $total_records>0):
  if(!isset($query_params)) {
    $query_params = '';
  }
  if(!isset($pagination_url)) {
    $pagination_url = '';
  }
?>
  <ul class="pagination">
      <div class="display-count">
        Displaying <?php echo ($sl_no > 0 ? $sl_no : 0).' - '.$to_sl_no.' of '.$total_records ?>
      </div>
      <?php
        for($i=$page_links_to_start;$i<=$page_links_to_end;$i++):
          if((int)$i===(int)$current_page) {
            $class_name = 'active';
          } else {
            $class_name = '';
          }
      ?>
          <li class="<?php echo $class_name ?>">
            <a href="<?php echo $pagination_url.'/'.$i.$query_params ?>">
              <?php echo $i ?>
            </a>
          </li>
      <?php endfor; ?>
  </ul>
<?php endif; ?>