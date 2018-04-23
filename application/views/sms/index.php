<style type="text/css">
    #upload_link{ text-decoration: none; }
    #fileToUpload{ display:none }
    .el-div-prev-child{
        position: relative;
        width: 150px;
        margin-bottom: 10px;
    }
    .el-img-prev{
        width: 150px; max-height: 100%;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .remove-img-prev{
        position: absolute;
        top: 0px;
        right: 0px;
        width: 25px;
        height: 25px;
        background:url("<?php echo base_url(); ?>assets/dist/img/icon-close3.png") no-repeat center center;
    }
    #div-place, #div-privacy, #div-scheduledDtm, #div-fileToUpload { display: none; }
    textarea { resize: vertical; }
</style>

<!--req to load : autoSuggest -->
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

<!--req to load : dateTime picker -->
<!-- Minified Bootstrap CSS -->
<!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">-->
<!-- Minified JS library -->
<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>-->
<!-- Minified Bootstrap JS -->
<!--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>-->
<link href="<?php echo base_url(); ?>assets/bootstrap-datetimepicker-master/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
<script src="<?php echo base_url(); ?>assets/bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.min.js"></script>

<script type="text/javascript">
    // location auto suggest
    function autoSuggest(){
        $.post(baseURL + "/sms/sms/fbSearchPlace", { q_place: $("#place").val() }).done(function(results_set){
            alert(results_set);

            var contents = JSON.parse(results_set); var searchKeywords = [];
            
            for (var i=0; i < contents.data.length; i++){ searchKeywords.push(contents.data[i].name); }
            
            $("#place").autocomplete({ source: searchKeywords });
            
            $("#place").change(function(){
                for (var i=0; i < contents.data.length; i++){
                    if (contents.data[i].name === $(this).val()){ $("#placeId").val(contents.data[i].id); }
                }
            });
        });
    }

    // ---------- start : preview image
    //upload link on click, trigger input fileToUpload instead
    $(function(){ $("#upload_link").on('click', function(e){ e.preventDefault(); $("#fileToUpload:hidden").trigger('click'); }); });

    function previewImage(input) {
        //alert('function : previewImage');
        
        if (input.files && input.files[0]) {
            var elDivPrevChild = document.getElementById('el-div-prev-child');;
            if(elDivPrevChild != null){ elDivPrevChild.parentNode.removeChild(elDivPrevChild); }

            var reader = new FileReader();  //init FileReader

            reader.onload = function(e) {
                $('#el-div-prev').append("<div id='el-div-prev-child' class='el-div-prev-child'><img class='el-img-prev' src='"+e.target.result+"'><span class='remove-img-prev'></span></div>");
                //alert($('#fileToUpload').val());

                //remove
                $('.remove-img-prev').on('click', function(){
                    $('#fileToUpload').val(''); //reset fileToUpload value
                    var elDivPrevChildToRemove = document.getElementById('el-div-prev-child');
                    elDivPrevChildToRemove.remove();    //remove element
                });
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    // preview image, call function previewImage() after selecting image
    $(function(){ $("#fileToUpload").on("change", function(){ previewImage(this); }); });

    //datetime
    $(function(){
        var today = new Date();
        var date = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
        var time = today.getHours() + ":" + today.getMinutes();
        var dateTime = date+' '+time;
        $("#scheduledDtm").datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
            autoclose: true,
            todayBtn: true,
            startDate: dateTime
        });
    });

    // ---------- end : preview image

    //check atleast 1 checkbox
    $(document).ready(function () {
        $('.submit').click(function() {
          checked = $("input[type=checkbox]:checked").length;
          if(!checked) { alert("Please select atleast one profile."); return false; }
        });
    });

    // show/hide fields
    function showPlace(){ $("#div-place").show(); $("#div-privacy").hide(); $("#div-scheduledDtm").hide(); $("#div-fileToUpload").hide(); }
    function showPrivacy(){ $("#div-place").hide(); $("#div-privacy").show(); $("#div-scheduledDtm").hide(); $("#div-fileToUpload").hide(); }
    function showScheduledDtm(){ $("#div-place").hide(); $("#div-privacy").hide(); $("#div-scheduledDtm").show(); $("#div-fileToUpload").hide(); }
    function showfileToUpload(){ $("#div-place").hide(); $("#div-privacy").hide(); $("#div-scheduledDtm").hide(); $("#div-fileToUpload").show(); }
</script>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-calendar-o"></i> Social Media Scheduler
        <small>Schedule , Post , Track Messages</small>
      </h1>
    </section>

    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-9">
              <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header">
                        <!--<h3 class="box-title">Schedule New Post</h3>-->
                    </div><!-- /.box-header -->

                    <form role="form" id="post" action="<?php echo base_url() ?>sms/smsPost" method="post" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-3">
                                   <div class="box-body table-responsive no-padding">
                                      <table class="table table-hover">
                                        <tr>
                                          <thead><label>Social Media Profiles</label></thead>
                                        </tr>
                                        <?php
                                        if(!empty($fbUserRecords)) {
                                            foreach($fbUserRecords as $row) {
                                        ?>
                                        <tr>
                                          <td><input type="checkbox" id="checkbox" name="fbUserId[]" value=<?php echo $row['user_id'] ?>></td>
                                          <td><?php echo $row['first_name'] . ' ' . $row['last_name'] ?></td>
                                          <td><?php /*if (!empty($row['access_token'])) echo '1'; else echo '0';*/ ?></td>
                                        </tr>
                                        <?php
                                            }
                                        }
                                        ?>

                                        <?php
                                        if(!empty($fbPageRecords)) {
                                            foreach($fbPageRecords as $row) {
                                        ?>
                                        <tr>
                                          <td><input type="checkbox" id="checkbox" name="fbPageId[]" value=<?php echo $row->page_id ?>></td>
                                          <td><?php echo $row->name ?></td>
                                          <td></td>
                                        </tr>
                                        <?php
                                            }
                                        }
                                        ?>
                                      </table>
                                    </div>
                                </div>
                                <div class="col-md-9">                                
                                    <div class="form-group">
                                        <textarea class="form-control" id="message" name="message" maxlength="2100" rows="5" placeholder="Compose message"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="link" name="link" placeholder="Add a link" maxlength="356">
                                    </div>
                                </div>

                                <div class="col-md-3"></div>
                                <div class="col-md-9" align="right">
                                    <div class="form-group">
                                        <a class="btn btn-sm btn-default" onclick="showPlace();"><i class="fa fa-location-arrow"></i></a>
                                        <a class="btn btn-sm btn-default" onclick="showPrivacy();"><i class="fa fa-lock"></i></a>
                                        <a class="btn btn-sm btn-default" onclick="showScheduledDtm();"><i class="fa fa-calendar"></i></a>
                                        <a class="btn btn-sm btn-default" onclick="showfileToUpload();"><i class="fa fa-file-photo-o"></i></a>
                                    </div>
                                </div>

                                <div class="col-md-3"></div>
                                <div class="col-md-9" align="right">                                
                                    <div id="div-place" class="form-group">
                                        <input type="hidden" class="form-control" id="placeId" name="placeId">
                                        <input type="text" class="form-control" id="place" name="place" placeholder="Where are you?" maxlength="356" onkeyup="autoSuggest()">
                                    </div>
                                    <div id="div-privacy" class="form-group">
                                        <select class="form-control" id="privacy" name="privacy">
                                            <option value="ALL_FRIENDS" selected>Privacy and sharing options:</option>
                                            <option value="SELF">Private</option>
                                            <option value="ALL_FRIENDS">Friends</option>
                                            <option value="EVERYONE">Everyone</option>
                                        </select>
                                    </div>
                                    <div id="div-scheduledDtm" class="form-group">
                                        <input type="text" id="scheduledDtm" name="scheduledDtm" class="form-control" size="16" placeholder="Select date..." readonly>
                                        <!--<a class="btn btn-sm btn-default" href="#"><i class="fa fa-calendar-o"></i></a>-->
                                        <!--<a class="btn btn-sm btn-default" href="#"><i class="fa fa-location-arrow"></i></a>-->
                                        <!--<a class="btn btn-sm btn-default" href="#"><i class="fa fa-lock"></i></a>-->
                                    </div>
                                    <div id="div-fileToUpload" class="form-group">
                                        <div id="el-div-prev"></div>
                                        Select your photo
                                        <a id="upload_link" class="btn btn-sm btn-default" href="#"><i class="fa fa-plus"></i></a>
                                        <input type="file" id="fileToUpload" name="fileToUpload" accept="image/jpeg, image/bmp, image/png, image/tiff" />
                                        <!--<input type="file" id="fileToUpload" name="fileToUpload" accept="image/jpeg, image/bmp, image/png, image/gif, image/tiff" />-->
                                    </div>
                                </div>
                            </div>
                        </div><!-- /.box-body -->

                        <div class="box-footer" align="right">
                            <input type="submit" class="btn btn-primary submit" value="Submit" />
                            <input type="reset" class="btn btn-default" value="Reset" />
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-3">
                <?php
                    $this->load->helper('form');
                    $error = $this->session->flashdata('error');
                    if($error)
                    {
                ?>

                <div class="alert alert-danger alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <?php echo $this->session->flashdata('error'); ?>                    
                </div>
                <?php } ?>
                <?php  
                    $success = $this->session->flashdata('success');
                    if($success)
                    {
                ?>

                <div class="alert alert-success alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <?php echo $this->session->flashdata('success'); ?>
                </div>
                <?php } ?>

                <div class="row">
                    <div class="col-md-12">
                        <?php echo validation_errors('<div class="alert alert-danger alert-dismissable">', ' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button></div>'); ?>
                    </div>
                </div>
            </div>
        </div>    
    </section>
</div>

<script src="<?php echo base_url(); ?>assets/js/post.js" type="text/javascript"></script>