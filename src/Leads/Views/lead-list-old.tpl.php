<?php
  use Atawa\Utilities;
?>
<div class="container">
  <h2 class="mainheading">All Leads</h2>
  <div class="row">
    <div class="col-sm-4 col-md-3 filterpanel">
      <div class="buttonholder">
        <button data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btnIcon rightIco dropdown-toggle" type="button">Filter by Lead Status<span><i class="fa fa-caret-down"></i></span></button>
        <ul class="dropdown-menu">
          <?php foreach($lead_status_a as $lead_status_key => $lead_status_value): ?>
            <li>
              <input type="checkbox" name="leadStatus" value="<?php echo $lead_status_key ?>" /><?php echo $lead_status_value ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="clearfix"></div>
      <div class="filtermenu">
        <section>
          <div class="headerFilter">
            <div class="visible-xs pull-left"><i class="fa fa-filter"></i>&nbsp;&nbsp;&nbsp;</div>
            More Lead Filters
            <div class="rightIcon pull-right visible-xs"><i class="fa fa-angle-down"></i></div>
            <div class="pull-right hidden-xs"><i class="fa fa-filter"></i></div>
          </div>
          <ul class="filterList">
            <li>
              <input type="checkbox" name="" id="">Lead Source
              <div class="subcontain">
                <ul>
                  <?php foreach($lead_sources_a as $lead_source_key => $lead_source_value): ?>
                    <li>
                      <input type="checkbox" name="leadStatus" value="<?php echo $lead_source_key ?>" /><?php echo $lead_source_value ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </li>
            <li>
              <input type="checkbox" name="" id="">Email id
              <div class="subcontain">
                <ul>
                  <li><input type="text" name="filterEmail" value="" /></li>
                </ul>
              </div>              
            </li>
            <li>
              <input type="checkbox" name="" id="">Phone
              <div class="subcontain">
                <ul>
                  <li><input type="text" name="filterPhone" value="" /></li>
                </ul>
              </div>
            </li>
            <li>
              <input type="checkbox" name="" id="">First Name
              <div class="subcontain">
                <ul>
                  <li><input type="text" name="filterFirstName" value="" /></li>
                </ul>
              </div>              
            </li>
            <li>
              <input type="checkbox" name="" id="">Business Name
              <div class="subcontain">
                <ul>
                  <li><input type="text" name="filterBusinessName" value="" /></li>
                </ul>
              </div>
            </li>
            <li>
              <input type="checkbox" name="" id="">Mobile
              <div class="subcontain">
                <ul>
                  <li><input type="text" name="filterMobile" value="" /></li>
                </ul>
              </div>              
            </li>
            <li>
              <input type="checkbox" name="" id="">Lead Industry
              <div class="subcontain">
                <ul>
                  <?php foreach($lead_industries_a as $lead_industry_key => $lead_industry_value): ?>
                    <li>
                      <input type="checkbox" name="leadStatus" value="<?php echo $lead_industry_key ?>" /><?php echo $lead_industry_value ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </li>
            <li>
              <input type="checkbox" name="" id="">Lead Rating
              <div class="subcontain">
                <ul>
                  <?php foreach($lead_ratings_a as $lead_rating_key => $lead_rating_value): ?>
                    <li>
                      <input type="checkbox" name="leadStatus" value="<?php echo $lead_rating_key ?>" /><?php echo $lead_rating_value ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>         
            </li>
          </ul>
        </section>
      </div>
    </div>
    <div class="col-xs-12 col-sm-8 col-md-9">
      <div class="totalRecord"><?php echo $record_count ?> <small>Leads</small></div>
      <div class="pull-right">
        <button class="btnIcon addnewbtn" type="button" onclick="window.location.href='/lead/create'">
          <span><i class="fa fa-plus"></i></span>Add New
        </button>
        <button class="btnIcon importbtn margin-left-15" type="button" onclick="window.location.href='/lead/import'">
          <span><i class="fa fa-random"></i></span>Import
        </button>
      </div>
      <div class="clearfix"></div>
      <div class="leadTableWrap">
        <table cellpadding="0" cellspacing="0" class="leaTable" border="0">
          <thead>
            <tr>
              <th>&nbsp;</th>
              <th><b>Buisness Name / Name</b></th>
              <th><b>Mobile</b></th>
              <th><b>Status</b></th>
              <th><b>Actions</b></th>
            </tr>
          </thead>
          <tbody>
            <?php
              foreach($leads as $lead_details):
                $lead_code = $lead_details['leadCode'];
                $email = $lead_details['email'];
                $mobile = $lead_details['mobile'];
                $status = Utilities::get_lead_status($lead_details['leadStatusId'], false);
                if($lead_details['businessName'] !== '') {
                  $name = $lead_details['businessName'];
                } else {
                  $name = $lead_details['firstName'].' '.$lead_details['lastName'];
                }
            ?>
              <tr>
                <td>
                  <input type="checkbox" name="" id="">
                </td>
                <td><?php echo $name ?></td>
                <td><?php echo $mobile ?></td>
                <td><?php echo $status ?></td>
                <td>
                  <button class="editBtn" onclick="window.location.href='/lead/update/<?php echo $lead_code ?>'" title="Update lead information">
                    <i class="fa fa-pencil"></i>
                  </button>
                  <button class="dlrBtn" title="Delete this lead">
                    <i class="fa fa-trash"></i>
                  </button>
                </td>
              </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>    
</div>