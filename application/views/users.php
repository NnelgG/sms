<style type="text/css">
  #table-users{
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
  td{
    padding-left: 12px !important;
  }
</style>

<!--req to load : tablesorter-master -->
<script src="<?php echo base_url(); ?>assets/tablesorter-master/jquery.tablesorter.js"></script>
<script type="text/javascript">
  $(document).ready(function(){ 
          $("#table-users").tablesorter(
              { headers: { 3: { sorter: false}, 4: {sorter: false}, 5: {sorter: false} } }
            ); 
      } 
  );
</script>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-users"></i> User Management
        <small>Add, Edit, Delete</small>
      </h1>
    </section>
    <section class="content">
        <div class="row">
             <div class="col-xs-12 text-right">
                <div class="form-group">
                    <a class="btn btn-primary" href="<?php echo base_url(); ?>addNew"><i class="fa fa-plus"></i> Add New</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Users List</h3>
                    <div class="box-tools">
                        <form action="<?php echo base_url() ?>userListing" method="POST" id="searchList">
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
                  <table id="table-users" class="table table-hover">
                    <thead>
                      <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Role</th>
                        <th class="text-center">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(!empty($userRecords))
                    {
                        foreach($userRecords as $record)
                        {
                    ?>
                    <tr>
                      <td><?php echo $record->userId ?></td>
                      <td><?php echo $record->name ?></td>
                      <td><?php echo $record->email ?></td>
                      <td><?php echo $record->mobile ?></td>
                      <td><?php echo $record->role ?></td>
                      <td class="text-center">
                          <a class="btn btn-sm btn-info" href="<?php echo base_url().'editOld/'.$record->userId; ?>"><i class="fa fa-pencil"></i></a>
                          <a class="btn btn-sm btn-danger deleteUser" href="#" data-userid="<?php echo $record->userId; ?>"><i class="fa fa-trash"></i></a>
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
            jQuery("#searchList").attr("action", baseURL + "userListing/" + value);
            jQuery("#searchList").submit();
        });
    });
</script>
