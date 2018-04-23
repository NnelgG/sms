<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class to control all user related operations.
 */
class Sms_rud extends BaseController {
	public static $fb = array();

    /**
     * This is default constructor of the class
     */
    public function __construct(){
        parent::__construct();

		//req to load
		require_once APPPATH . 'libraries/php-graph-sdk-5.x/src/Facebook/autoload.php';
        $this->load->model('sms_rud_model');

        self::$fb = new Facebook\Facebook(['app_id' => APP_ID, 'app_secret' => APP_SECRET, 'default_graph_version' => DEFAULT_GRAPH_VERSION, 'fileUpload' => true]);

        $this->isLoggedIn();   
    }
    
    /**
     * Used to load the sms list
     */
    public function smsListing() {
        if($this->isAdmin() == TRUE){
            $this->loadThis();
        }else{
            $searchText = $this->input->post('searchText');
            $data['searchText'] = $searchText;
            
            $this->load->library('pagination');
            
            $count = $this->sms_rud_model->smsListingCount($searchText);

            $returns = $this->paginationCompress("smsListing/", $count, 5);
            
            //req data
            $data['fbUserRecords'] = $this->sms_rud_model->fbUserListing();
            $data['fbPageRecords'] = $this->sms_rud_model->fbPageListing();
            $data['smsRecords'] = $this->sms_rud_model->smsListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'Scheduler : SMS Listing';
            
            $this->loadViews("sms_rud/index", $this->global, $data, NULL);
        }
    }

    /**
     * Used to load sms edit information
     * @param number $smsId : Optional : This is sms id
     */
    function editSmsOld($smsId = NULL){
        if($this->isAdmin() == TRUE){
            $this->loadThis();
        }else{
            if($smsId == null){
                redirect('smsListing');
            }

            //req to load
            $this->load->library('cias_library');

       		//req data
            $data['fbUserRecords'] = $this->sms_rud_model->fbUserListing();
            $dataFbUserRecords = $data['fbUserRecords'];

            //detect fb users w/ valid 'a' token
            if(!empty($dataFbUserRecords)){
            	//echo '----not empty data fb user records <br>';

            	$data['fbUserRecords'] = array();	//reset data fbUserRecords

            	foreach ($dataFbUserRecords as $row) {
            		//echo '--------user id: ' . $row->user_id . ' on process '; 

            		$access_token = $this->cias_library->fbGetValidateAccessToken($row->user_id);	//get&validate 'a' token

            		//echo '--------access token: ' . $access_token . '<br>';
					
            		$fbUser = array('user_id' => $row->user_id, 'first_name' => $row->first_name, 'last_name' => $row->last_name, 'access_token' => $access_token);
					array_push($data['fbUserRecords'], $fbUser);
            	}
            }

            //req data
            $data['fbPageRecords'] = $this->sms_rud_model->fbPageListing();
            
            $data['smsInfo'] = $this->sms_rud_model->getSmsInfo($smsId); // get sms info
            
            $smsInfo = json_decode(json_encode($data['smsInfo']), True); // stdClass to array
            unset($data['smsInfo']); // unset array data smsInfo

            // prep key value pairs for array data smsInfo
		    foreach ($smsInfo as $row){
		        $id = $row['id'];
		        $postTo = $row['postTo'];
		        $scheduleDtm = $row['scheduleDtm'];
		        $message = $row['message'];
		        $link = $row['link'];
		        $image = $row['image'];
		        $placeId = $row['placeId'];
		        $place = ''; if(!empty($placeId)){ $place = $this->fbGetPlaceInfo($placeId); }
		        $privacy = $row['privacy'];
		    }

		    // re-set array data smsInfo
			$data['smsInfo'] = (object) array(0 => 
				array('id' => $id, 
			    	'postTo' => $postTo, 
			    	'scheduleDtm' => $scheduleDtm, 
			    	'message' => $message,
			    	'link' => $link,
			    	'image' => $image,
			    	'placeId' => $placeId,
			    	'place' => $place,
			    	'privacy' => $privacy)
			);

		    //echo '<pre>'; print_r($data['smsInfo']); echo '</pre>';
            
            $this->global['pageTitle'] = 'Scheduler : Edit Scheduled Post';
            
            $this->loadViews("sms_rud/editSmsOld", $this->global, $data, NULL);
        }
    }

    /**
     * Used to edit the user information
     */
    function editSms(){
        if($this->isAdmin() == TRUE){
            $this->loadThis();
        }else{
        	//echo '---- isAdmin <br>';
            
            $fb = self::$fb; 	//instantiate facebook

        	//step 1 : get input -- untrusted data
        	$smsId = $this->input->post('smsId');
        	$fbUserIds = $this->input->post('fbUserId');	//array of fb user ids--selected from form
	        $fbPageIds = $this->input->post('fbPageId');	//array of fb page ids--selected from form
	        $data['post']['message'] = $this->input->post('message');
	        $data['post']['link'] = $this->input->post('link');
	        $data['post']['placeId'] = $this->input->post('placeId');
	        $data['post']['place'] = $this->input->post('place');
	        $data['post']['privacy'] = $this->input->post('privacy');
	        //is not empty file attachment?
			$data['post']['source'] = ''; 
	        $dataPostSource = $this->input->post('source');	//get old source
	        if(!empty($_FILES["fileToUpload"]["name"])){ //new source?
	        	$data['post']['source'] = $_FILES["fileToUpload"]["name"]; 
	        }else if(!empty($dataPostSource)){ 	//not empty old ource?
	        	$data['post']['source'] = $dataPostSource; 
	        }
	        $data['post']['scheduledDtm'] = $this->input->post('scheduledDtm');

	        //echo '---- Data post: <pre>'; print_r($data['post']); echo '</pre>';

	        //step 2: validation
	        $data['valErrors'] = $this->_validatesmsPost($data['post']);
	        if(!empty($data['valErrors'])){ redirect('editSmsOld/' . $smsId); }

	        //echo '--- Data errors <pre>'; print_r($data['valErrors']); echo '</pre>';

			//step 3 : sanitize
	        $message = filter_var($data['post']['message'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	        $link = $data['post']['link'];
	        $placeId = (!empty($data['post']['place'])) ? $data['post']['placeId'] : '';
	        $privacy = (!empty($data['post']['privacy'])) ? $data['post']['privacy'] : 'ALL_FRIENDS';
	        $source = $data['post']['source'];
	        if(!empty($_FILES["fileToUpload"]["name"])){	//is not empty file attachment?
	        	//echo "------: not empty source <br>";
	        	$fileToUpload_tmpName = explode(".", $_FILES["fileToUpload"]["name"]);
	        	$fileToUpload_newName = round(microtime(true)) . '.' . end($fileToUpload_tmpName);	//rename(randomized) fileToUpload
	        	$source = $this->fbUploadFile(basename($fileToUpload_newName));	//upload
	        	//$source = $this->fbUploadFile($_FILES["fileToUpload"]["name"]);	//default fileToUpload name
	        }
	        $scheduledDtm = $data['post']['scheduledDtm'];

	        //echo "---- base name fileToUpload name: $source </br>";

	        // prepend & implode postTo
	        $arr_postTo = array();
	        if(!empty($fbUserIds))
	        	foreach ($fbUserIds as $row) { $row = 'fb-user-' . $row; array_push($arr_postTo, $row); }

	        if(!empty($fbPageIds))
	        	foreach ($fbPageIds as $row) { $row = 'fb-page-' . $row; array_push($arr_postTo, $row); }

	        if($arr_postTo > 1)
	        	$arr_postTo = implode('|', $arr_postTo);

	        //echo '---- postTo <br>';
	        //echo '<pre>'; print_r($arr_postTo); echo '</pre>';

	        // is schedule or post?
	       	if(!empty($scheduledDtm)){	//start: schedule post
	        	//echo '-------- schedule <br>';

	        	date_default_timezone_set('Asia/Manila'); 	//system's timezone settings
	        	$scheduledDtm = $scheduledDtm . ':00';

				$nowDtmBuff = date('Y-m-d H:i');
				$nowDtmBuff = date_create($nowDtmBuff);
				$nowDtmBuff->modify("+5 minutes");	//now datetime +5mins

				//echo "--------scheduledDtm: $scheduledDtm <br>";
	       		//echo '--------nowDtmBuff: ' . date_format($nowDtmBuff, 'Y-m-d H:i:00') . '<br>';

	       		// is valid scheduled datetime?
	       		if($scheduledDtm >= date_format($nowDtmBuff, 'Y-m-d H:i:00')){
					//echo '------------ valid datetime<br>';

		        	//start: get duplicate sms posts
					$dup_smsPosts = $this->sms_rud_model->smsPostsGetBySchedDtm($smsId, $scheduledDtm);	//scheduled posts

		        	//echo '----------------duplicate scheduled posts count: ' . count($dup_smsPosts) . ' duplicate scheduled posts: <pre>'; print_r($dup_smsPosts); echo '</pre>';

		       		// is empty duplicate sms posts ?
		        	if(empty($dup_smsPosts)){
		        		//echo '---------------- No duplicate scheduled posts <br>';

				        $updated_smsPost = array(
				        	'createdBy' => '1',
				        	'createdDtm' => date('Y-m-d H:i:s'),
				        	'postTo' => $arr_postTo,
				        	'scheduleDtm' => $scheduledDtm,
				        	'postedDtm' =>  NULL,
				        	'message' => $message,
				        	'image' => $source,
				        	'link' => $link,
				        	'placeId' => $placeId,
				        	'privacy' => $privacy
				        	);

						//echo '---------------- Updated sms post: <pre>'; print_r($updated_smsPost); echo '</pre>';

		                $this->sms_rud_model->editSms($smsId, $updated_smsPost);
		                //echo "--------------------successfully scheduled <br>";
		                $this->session->set_flashdata('success', 'Message updated successfully');
		        	}else{
		        		//echo '----------------FOUND duplicate scheduled posts <br>';
		        		//echo '<pre>'; print_r($dup_smsPosts); echo '</pre>';
		        		$this->session->set_flashdata('error', 'Duplicate message schedule found');
		        	}
	       		}else{
	       			//echo '------------invalid datetime<br>';
	       			$this->session->set_flashdata('error', 'Invalid datetime.');
	       		}	//end: schedule post
	       	}
        }

        redirect('editSmsOld/' . $smsId);
    }

    /**
     * Used to delete sms using sms id
     * @return boolean $result : TRUE / FALSE
     */
    function deleteSms(){
        if($this->isAdmin() == TRUE){
            echo(json_encode(array('status'=>'access')));
        }else{
            $smsId = $this->input->post('smsId');
            
            $result = $this->sms_rud_model->deleteSms($smsId);
            
            if ($result > 0) { echo(json_encode(array('status'=>TRUE))); }
            else { echo(json_encode(array('status'=>FALSE))); }
        }
    }

    /**
     * Used to upload file 
     * @param string $file_name : attached file--file name
     */
	public function fbUploadFile($file_name){
		//echo "function : fbUploadFile : start <br>";

		//init
		$target_dir = SMS_UPLOAD_DIR;
        $target_file = $target_dir . $file_name;
	    
	    //is fileToUpload uploaded?
	    if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)){
	        echo "---- The file ". basename($_FILES["fileToUpload"]["name"]). " has been uploaded. <br>";

	        return $target_file;
	    }else{ 
	    	return 0;
	    	echo "---- Sorry, there was an error uploading your file. <br>"; 
	    }

        //echo "function: fbUploadFile : end <br>";
	}

   	/**
     * Used to get fb place info by place id
     */
	public function fbGetPlaceInfo($placeId){
        //echo 'function : fbGetPlaceInfo : start<br>';
       
		$fb = self::$fb; 	//instantiate facebook

        try {
            // Returns a `Facebook\FacebookResponse` object
        	$app_id = APP_ID; $app_secret = APP_SECRET;

            $response = $fb->get("$placeId?fields=name", "$app_id|$app_secret");

            if(!empty($response)){  //not empty response?
                //echo 'response not empty<br>';
                $response_graphNode = $response->getGraphNode();   //response to graphEdge convertion
                
                return $response_graphNode['name'];
            }
        }catch(Facebook\Exceptions\FacebookResponseException $e){
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        }catch(Facebook\Exceptions\FacebookSDKException $e){
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        //echo 'function : fbGetPlaceInfo : end';
	}

    /**
     * Used to set flashdata error
     * @param array $data : post data
     */
	private function _validatesmsPost($data){
		echo '--- Data <pre>'; print_r($data); echo '</pre>';

		$errors = array();

		//empty all?
		if(empty($data['place'])) $data['placeId'] = '';
		if(empty($data['message']) && empty($data['link']) && empty($data['source']) && empty($data['placeId'])){
			$errors['other'] = 'This post appears to be blank. Please write something or attach a link or photo to post.';
		}

		//form validation : set rules
		$this->form_validation->set_error_delimiters('','');
		$this->form_validation->set_rules('place','Place','trim|max_length[256]|xss_clean');
		$this->form_validation->set_rules('message','Message','trim|max_length[2000]|xss_clean');
		$this->form_validation->set_rules('link','Link','trim|max_length[256]|xss_clean');
		if($_FILES["fileToUpload"]["name"] != ''){ $this->form_validation->set_rules('source','Source','callback_validatefileToUpload'); }
		$this->form_validation->set_rules('privacy','Privacy','trim|max_length[256]|xss_clean');

		if((!$this->form_validation->run()) || (empty($data['message']) && empty($data['link']) && empty($data['source']) && empty($data['placeId']))){
			//get form error
			$errors['message'] = form_error('message');
			$errors['link'] = form_error('link');
			$errors['source'] = form_error('source');
			$errors['place'] = form_error('place');	
			$errors['privacy'] = form_error('privacy');

			//set flash data : error
			$this->session->set_flashdata('error', '<p>'.$errors['other'].'</p><p>'.$errors['message'].'</p><p>'.$errors['link'].'</p><p>'.$errors['source'].'</p><p>'.$errors['place'].'</p><p>'.$errors['privacy']);
		}

		echo '--- Errors <pre>'; print_r($errors); echo '</pre>';

		return $errors;
	}

    /**
     * A callback function
     * Used to validate file to upload
     */
    function validatefileToUpload(){
		$file_to_upload = $_FILES["fileToUpload"]["name"];	//init file--file name to upload

		$target_dir = APPPATH . 'uploads/'; 	//upload folder loc
        $target_file = $target_dir . $file_to_upload;

		// Allow certain file formats
		$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
		if($imageFileType != "jpeg" && $imageFileType != "jpg" && $imageFileType != "bmp" && $imageFileType != "png" && $imageFileType != "gif" && $imageFileType != "tiff") {
            $this->form_validation->set_message('validatefileToUpload', 'Please select only file with JPG/PNG/JPEG/GIF extension.');
            return false;
		}

	    //is set post?
		//if(isset($_POST["submit"])) {
		    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
		    //is actual image file?
		    if($check !== false) {
		    } else {
				$this->form_validation->set_message('validatefileToUpload', 'Please select a valid image file.');
            	return false;
		    }
		//}

		//is file size lower than 3000kb?
		if($_FILES["fileToUpload"]["size"] > 10000000){	//3000kb
			$this->form_validation->set_message('validatefileToUpload', 'Image file must be less than 10mb.');
			return false;
		}

		return true;
    }

    /**
     * A Pre-post function (called via javascript validate library remote method)
     * Used to instantly display error message from form
     */
    function validatePostMessage(){
		$this->form_validation->set_rules('message','Message','trim|max_length[2000]|xss_clean');

		if(!$this->form_validation->run())
			//echo '"' . form_error('message') . '"';
			echo '"The Message field must not exceed 2000 characters"';
		else
			echo("true");
    }

    function validatePostLink(){
		$this->form_validation->set_rules('link','Link','trim|max_length[256]|xss_clean');

		$link = $this->input->post('link');

		if(!$this->form_validation->run()){
			//echo '"' . form_error('link') . '"';
			echo '"The Link field must not exceed 256 characters"';
		}else if(!(filter_var($link, FILTER_VALIDATE_URL))){
			echo '"The Link field must be a valid URL."';
		}else{
			echo("true");
		}
    }

    function validatePostPlace(){
		$this->form_validation->set_rules('place','Place','trim|max_length[256]|xss_clean');

		$placeId = $this->input->post('placeId');
		$place = $this->input->post('place');

		if(!$this->form_validation->run()){
			//echo '"' . form_error('place') . '"';
			echo '"The Place field must not exceed 256 characters"';
		}else{
			echo("true");
		}
    }


}

?>