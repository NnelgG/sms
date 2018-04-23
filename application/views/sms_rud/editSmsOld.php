<?php

$smsId = '';
$postTo = '';
$scheduleDtm = '';
$message = '';
$link = '';
$image = '';
$placeId = '';
$place = '';
$privacy = '';

if(!empty($smsInfo)){
    
    $smsInfo = json_decode(json_encode($smsInfo), True); // stdClass to array

    foreach ($smsInfo as $row){
        $smsId = $row['id'];
        $postTo = $row['postTo'];
        $scheduleDtm = $row['scheduleDtm'];
        $message = $row['message'];
        $link = $row['link'];
        $image = $row['image'];
        $placeId = $row['placeId'];
        $place = $row['place'];
        $privacy = $row['privacy'];
    }
}

?>

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
                    $('#source').val(''); //reset source value
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

        $('.remove-img-prev').on('click', function(){
        $('#fileToUpload').val(''); //reset fileToUpload value
        $('#source').val(''); //reset source value
        var elDivPrevChildToRemove = document.getElementById('el-div-prev-child');
        elDivPrevChildToRemove.remove();    //remove element
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
        <small>Edit Your Post</small>
      </h1>
    </section>
    <!--<?php echo '<pre>'; print_r($smsInfo); echo '</pre>'; ?>-->
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-9">
              <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header">
                        <!--<h3 class="box-title">Enter Post Details</h3>-->
                    </div><!-- /.box-header -->
                    
                    <form role="form" id="post" action="<?php echo base_url() ?>editSms" method="post" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-3">
                                   <div class="box-body table-responsive no-padding">
                                      <table class="table table-hover">
                                        <tr>
                                          <thead><label>Social Media Profiles</label></thead>
                                        </tr>
                                        <?php
                                            // explode & truncate postTo
                                            $arr_postTo = array();
                                            if(!empty($postTo)){
                                                $postTo = explode('|', $postTo);
                                                foreach ($postTo as $row) {
                                                    if(strpos($row, 'fb-user') !== false){ $fbUserId = str_replace('fb-user-', '', $row); array_push($arr_postTo, $fbUserId); }
                                                    if(strpos($row, 'fb-page') !== false){ $fbPageId = str_replace('fb-page-', '', $row); array_push($arr_postTo, $fbPageId); }
                                                }
                                              //echo '<pre>'; print_r($arr_postTo); echo '</pre>';
                                            }

                                        if(!empty($fbUserRecords)) {
                                            foreach($fbUserRecords as $row) {
                                        ?>
                                        <tr>
                                          <td>
                                            <input type="checkbox" id="checkbox" name="fbUserId[]" value=<?php echo $row['user_id'] ?> <?php if(in_array($row['user_id'], $arr_postTo)) echo 'checked' ?>>
                                            </td>
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
                                          <td>
                                            <input type="checkbox" id="checkbox" name="fbPageId[]" value=<?php echo $row->page_id ?> <?php if(in_array($row->page_id, $arr_postTo)) echo 'checked' ?>>
                                          </td>
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
                                        <input type="hidden" value="<?php echo $smsId; ?>" name="smsId" id="smsId" />
                                        <textarea class="form-control" id="message" name="message" maxlength="2100" rows="5" placeholder="Compose message"><?php echo $message; ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="link" name="link" placeholder="Add a link" maxlength="356" value="<?php echo $link; ?>">
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
                                        <input type="hidden" class="form-control" id="placeId" name="placeId" value="<?php echo $placeId; ?>">
                                        <input type="text" class="form-control" id="place" name="place" placeholder="Where are you?" maxlength="356" value="<?php echo $place; ?>" onkeyup="autoSuggest()">
                                    </div>
                                    <div id="div-privacy" class="form-group">
                                        <select class="form-control" id="privacy" name="privacy">
                                            <option value="ALL_FRIENDS" <?php if(empty($privacy)) { echo "selected=selected"; } ?>>Targetting options</option>
                                            <option value="SELF" <?php if($privacy == 'SELF') { echo "selected=selected"; } ?>>Private</option>
                                            <option value="ALL_FRIENDS" <?php if($privacy == 'ALL_FRIENDS') { echo "selected=selected"; } ?>>Friends</option>
                                            <option value="EVERYONE" <?php if($privacy == 'EVERYONE') { echo "selected=selected"; } ?>>Everyone</option>
                                        </select>
                                    </div>
                                    <div id="div-scheduledDtm" class="form-group">
                                        <input type="text" id="scheduledDtm" name="scheduledDtm" class="form-control" size="16" placeholder="Select date..." value="<?php echo $scheduleDtm; ?>" readonly>
                                        <!--<a class="btn btn-sm btn-default" href="#"><i class="fa fa-calendar-o"></i></a>-->
                                        <!--<a class="btn btn-sm btn-default" href="#"><i class="fa fa-location-arrow"></i></a>-->
                                        <!--<a class="btn btn-sm btn-default" href="#"><i class="fa fa-lock"></i></a>-->
                                    </div>
                                    <div id="div-fileToUpload" class="form-group">
                                        <?php if(!empty($image)){ ?>
                                            <div id="el-div-prev">
                                                <div id='el-div-prev-child' class='el-div-prev-child'>
                                                    <img class='el-img-prev' src="<?php echo str_replace(FCPATH, base_url(), $image); ?>">
                                                    <span class='remove-img-prev'></span>
                                                </div>
                                            </div>
                                        <?php }else{ ?>
                                            <div id="el-div-prev"></div>
                                        <?php } ?>

                                        Select your photo
                                        <a id="upload_link" class="btn btn-sm btn-default" href="#"><i class="fa fa-plus"></i></a>
                                        <input type="file" id="fileToUpload" name="fileToUpload" accept="image/jpeg, image/bmp, image/png, image/tiff" />
                                        <input type="hidden" id="source" name="source" value="<?php echo $image; ?>"/>
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