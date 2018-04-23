<style type="text/css">
  #table-sms{
    margin-left: 1% !important;
    margin-right: 1% !important;
    width: 98% !important;
  }
  th.header{
    cursor: pointer; 
    padding-left: 12px !important;
    background:url("<?php echo base_url(); ?>assets/dist/img/tbl-sort-asc-desc6.png") no-repeat left center;
  }
  th.headerSortDown{ 
    background:url("<?php echo base_url(); ?>assets/dist/img/tbl-sort-desc6.png") no-repeat left center;
  }
  th.headerSortUp{
    background:url("<?php echo base_url(); ?>assets/dist/img/tbl-sort-asc6.png") no-repeat left center;
  }
  .text-excerpt{
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
    max-width: 500px;
  }
  small{
    color: gray;
  }
</style>

<!--req to load : tablesorter-master -->
<script src="<?php echo base_url(); ?>assets/tablesorter-master/jquery.tablesorter.js"></script>
<script type="text/javascript">
  $(document).ready(function(){ 
    $("#table-sms").tablesorter(
      { headers: { 1: { sorter: false}, 2: { sorter: false}, 3: {sorter: false}, 4: {sorter: false}, 7: {sorter: false} } }
    ); 
  });
</script>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-calendar-o"></i> Social Media Scheduler
        <small>Add , Edit , Delete Posts</small>
      </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12 text-right">
                <div class="form-group">
                    <a class="btn btn-primary" href="<?php echo base_url(); ?>sms"><i class="fa fa-plus"></i> Add New</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Scheduled Posts List</h3>
                    <div class="box-tools">
                        <form action="<?php echo base_url() ?>smsListing" method="POST" id="searchList">
                            <div class="input-group">
                              <input type="text" name="searchText" value="<?php echo $searchText; ?>" class="form-control input-sm pull-right" style="width: 150px;" placeholder="Search"/>
                              <div class="input-group-btn">
                                <button class="btn btn-sm btn-default searchList"><i class="fa fa-search"></i></button>
                              </div>
                            </div>
                        </form>
                    </div>
                </div><!-- /.box-header -->
                <div class="box-body table-responsive no-padding">
                  <table id="table-sms" class="table table-hover">
                    <thead>
                      <tr>
                        <th>Schedule</th>
                        <!--<th></th>-->
                        <th>Message</th>
                        <th class="text-center">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php

                    /*echo '<pre>'; print_r($fbUserRecords); echo '<pre>';
                    echo '<pre>'; print_r($fbPageRecords); echo '<pre>';
                    $fbPageRecords = json_decode(json_encode($fbPageRecords), True);        
                    $name= array_map(function($element) {
                      return $element['name'];
                    }, $fbPageRecords);
                    print_r($name);*/

                    if(!empty($smsRecords))
                    {
                        foreach($smsRecords as $record)
                        {
                    ?>
                    <tr>
                      <td><?php echo /*$record->id . ' ' .*/ $record->scheduleDtm ?></td>
                      <!--<td><img src="<?php echo str_replace(FCPATH, base_url(), $record->image); ?>" class="user-image" alt="User Image" width="50px"/></td>-->
                      <td>
                        <div class='text-excerpt'><?php echo $record->message ?></div>
                        <?php
                        // explode & truncate postTo
                        $arr_postTo = array();
                        if(!empty($record->postTo)){
                          $postTo = explode('|', $record->postTo);
                          foreach ($postTo as $row) {
                            if(strpos($row, 'fb-user') !== false){ $fbUserId = str_replace('fb-user-', '', $row); array_push($arr_postTo, $fbUserId); }
                            if(strpos($row, 'fb-page') !== false){ $fbPageId = str_replace('fb-page-', '', $row); array_push($arr_postTo, $fbPageId); }
                          }
                          //print_r($arr_postTo);

                          $arr_postToName = array();
                          foreach ($fbUserRecords as $row) {
                            if(in_array($row->user_id, $arr_postTo)){ array_push($arr_postToName, /*$row->user_id . ' ' .*/ $row->first_name . ' ' . $row->last_name); }
                          }
                          foreach ($fbPageRecords as $row) {
                            if(in_array($row->page_id, $arr_postTo)){ array_push($arr_postToName, /*$row->page_id . ' ' .*/ $row->name); }
                          }
                          //print_r($arr_postToName);

                          if(count($arr_postToName) > 1){
                            echo '<small>To :' . $arr_postToName[0] . ' +' . (count($arr_postToName)-1) . '</small>';
                          }else{
                            echo '<small>To :' . $arr_postToName[0] . '</small>';
                          }
                        }
                        ?>
                      </td>
                      <td class="text-center">
                          <a class="btn btn-sm btn-info" href="<?php echo base_url().'editSmsOld/'.$record->id; ?>"><i class="fa fa-pencil"></i></a>
                          <a class="btn btn-sm btn-danger deleteSms" href="#" data-smsid="<?php echo $record->id; ?>"><i class="fa fa-trash"></i></a>
                      </td>
                    </tr>
                    <?php
                        }
                    }
                    ?>
                    </tbody>
                  </table>
                  
                </div><!-- /.box-body -->
                <div class="box-footer clearfix">
                    <?php echo $this->pagination->create_links(); ?>
                </div>
              </div><!-- /.box -->
            </div>
        </div>
    </section>
</div>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/common.js" charset="utf-8"></script>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('ul.pagination li a').click(function (e) {
            e.preventDefault();            
            var link = jQuery(this).get(0).href;            
            var value = link.substring(link.lastIndexOf('/') + 1);
            jQuery("#searchList").attr("action", baseURL + "smsListing/" + value);
            jQuery("#searchList").submit();
        });
    });
</script>
