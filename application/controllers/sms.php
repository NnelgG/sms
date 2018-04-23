<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

class SMS extends BaseController {
	public static $fb = array();

    /**
     * This is default constructor of the class
     */
    public function __construct(){
        parent::__construct();
       
		//req to load
		$this->load->model('sms_model');
        $this->load->library('cias_library');

		self::$fb = new Facebook\Facebook(['app_id' => APP_ID, 'app_secret' => APP_SECRET, 'default_graph_version' => DEFAULT_GRAPH_VERSION, 'fileUpload' => true]);

        $this->isLoggedIn();   
    }

    /**
     * Used to get and segregate fb users, pages that has valid 'a' token
     * Load view sms index w/ req data
     */
    public function index(){
    	//echo 'function : index : start <br>';

        if($this->isAdmin() == TRUE){
            $this->loadThis();	//access denied
        }else{
        	//req data
            $data['fbUserRecords'] = $this->sms_model->fbUserListing();
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
            $data['fbPageRecords'] = $this->sms_model->fbPageListing();

            $this->global['pageTitle'] = 'Scheduler : Post Schedules';
            
            $this->loadViews("sms/index", $this->global, $data, NULL);
        } 

        //echo 'function : index : end <br>';
    }

	public function smsPost(){
		//echo 'function : smsPost : start <br>';
		
		if($this->isAdmin() == TRUE){
            $this->loadThis();	//access denied
        }else{
        	//echo '---- isAdmin <br>';
            
            $fb = self::$fb; 	//instantiate facebook

        	//step 1 : get input -- untrusted data
        	$fbUserIds = $this->input->post('fbUserId');	//array of fb user ids--selected from form
	        $fbPageIds = $this->input->post('fbPageId');	//array of fb page ids--selected from form
	        $data['post']['message'] = $this->input->post('message');
	        $data['post']['link'] = $this->input->post('link');
	        $data['post']['placeId'] = $this->input->post('placeId');
	        $data['post']['place'] = $this->input->post('place');
	        $data['post']['privacy'] = $this->input->post('privacy');
	        $data['post']['source'] = ''; if(!empty($_FILES["fileToUpload"]["name"])){ $data['post']['source'] = $_FILES["fileToUpload"]["name"]; }	//is not empty file attachment?
	        $data['post']['scheduledDtm'] = $this->input->post('scheduledDtm');

	        //echo '---- Data post: <pre>'; print_r($data['post']); echo '</pre>';

	        //step 2: validation
	        $data['valErrors'] = $this->_validatesmsPost($data['post']);
	        if(!empty($data['valErrors'])){ /*echo '---- not empty data errors: <br>';*/ redirect('sms'); }

	        //echo '--- Data errors <pre>'; print_r($data['valErrors']); echo '</pre>';
	        //echo '--- Count data errors: ' . count($data['valErrors']) . '<br>'	;

			//step 3 : sanitize
	        $message = filter_var($data['post']['message'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	        $link = $data['post']['link'];
	        $placeId = (!empty($data['post']['placeId'])) ? $data['post']['placeId'] : '';
	        $privacy = (!empty($data['post']['privacy'])) ? $data['post']['privacy'] : 'ALL_FRIENDS';
	        if(!empty($_FILES["fileToUpload"]["name"])){	//is not empty file attachment?
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
					$dup_smsPosts = $this->sms_model->smsPostsGetBySchedDtm($scheduledDtm);	//scheduled posts

		        	//echo '----------------duplicate scheduled posts count: ' . count($dup_smsPosts) . ' duplicate scheduled posts: <pre>'; print_r($dup_smsPosts); echo '</pre>';

		       		// is empty duplicate sms posts ?
		        	if(empty($dup_smsPosts)){
		        		//echo '---------------- No duplicate scheduled posts <br>';

				        $toInsert_smsPost = array(
				        	'createdBy' => '1',
				        	'createdDtm' => date('Y-m-d H:i:s'),
				        	'postTo' => $arr_postTo,
				        	'scheduleDtm' => $scheduledDtm,
				        	'postedDtm' =>  NULL,
				        	'message' => $message,
				        	'image' => (!empty($source)) ? $source : '',
				        	'link' => $link,
				        	'placeId' => $placeId,
				        	'privacy' => $privacy
				        	);

						//echo '---------------- To insert sms post: <pre>'; print_r($toInsert_smsPost); echo '</pre>';

		                $this->sms_model->insertSmsPost($toInsert_smsPost);
		                //echo "--------------------successfully scheduled <br>";
		                $this->session->set_flashdata('success', 'Message scheduled successfully');
		        	}else{
		        		//echo '----------------FOUND duplicate scheduled posts <br>';
		        		$this->session->set_flashdata('error', 'Duplicate message schedule found');
		        	}
	       		}else{
	       			//echo '------------invalid datetime<br>';
	       			$this->session->set_flashdata('error', 'Invalid datetime.');
	       		}	//end: schedule post

	       	}else{	//start: post immediately
				if(!empty($fbUserIds)){
		        	//echo '--------not empty fbUserIds<br>';
			        
			        if(!empty($source)){
			        	$sourceFbUser = $source;
			        	$messageFbUser = $message . ' ' . $link;

			        	//echo '--------source fb user: ' . $sourceFbUser . '<br>';

			        	$fbUserPostParams = array("message" => $messageFbUser, "source" => $sourceFbUser, "link" => '', "place" => $placeId, "privacy" => "{'value':'$privacy'}");
			        }else{
			        	$messageFbUser = $message;

			        	$fbUserPostParams = array("message" => $messageFbUser, "link" => $link, "place" => $placeId, "privacy" => "{'value':'$privacy'}");
			        }

			        foreach ($fbUserIds as $fbUserId) {
			        	$aToken = $this->cias_library->fbGetValidateAccessToken($fbUserId);	//get&validate 'a' token

			        	//echo '------------user id: ' . $fbUserId . ' return : ' . $aToken . '<br>';
			        	
			        	if(!empty($aToken)){
			        		//echo '----------------not empty access token <br>';
			        		$this->cias_library->smsPostAsUser($fbUserId, $fbUserPostParams, $aToken);
			        		$this->session->set_flashdata('success', 'Message posted successfully');
			        	}else{
			        		//echo '----------------aToken empty <br>';
			        	}
			        }
		        }	//end : post as fb user

				if(!empty($fbPageIds)){
		        	//echo '--------not empty fbPageIds<br>';

			        if(!empty($source)){
			        	$sourceFbPage = $source;
			        	$messageFbPage = $message . ' ' . $link;

			        	$fbPagePostParams = array("message" => $messageFbPage, "source" => $sourceFbPage, "link" => '', "place" => $placeId);
			        }else{
			        	$messageFbPage = $message;

			        	$fbPagePostParams = array("message" => $messageFbPage, "link" => $link, "place" => $placeId);
			        }

			        foreach ($fbPageIds as $fbPageId) {
			        	$fbUserId = $this->sms_model->fbGetPageUserId($fbPageId);
			        	$aToken = $this->cias_library->fbGetValidateAccessToken($fbUserId);	//fb user access token

			        	//echo '------------page id: ' . $fbPageId . ' return : ' . $aToken . '<br>';
						
						//echo '<pre>'; print_r($fbPagePostParams); echo '</pre>';

			        	if(!empty($aToken)){
			        		//echo '----------------not empty access token <br>';
							$this->cias_library->smsPostAsPage($fbUserId, $fbPageId, $fbPagePostParams, $aToken);
							$this->session->set_flashdata('success', 'Message posted successfully');
			        	}else{
			        		//echo '----------------aToken empty <br>';
			        	}
			        }	
		        }	//end : post as page

		        //unlink (delete) image source
		        if((!empty($source)) && (file_exists($source))){ /*echo "------------source: $source <br>";*/ unlink($source); }
	       	}	//end: post immediately
	       	
        }

        redirect('sms/index');
        
        //echo 'function : smsPost : end <br>';
    }

    /**
     * Used to upload file 
     * @param string $file_name : attached file--file name
     */
	public function fbUploadFile($file_name){
		echo "function : fbUploadFile : start <br>";

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

        echo "function: fbUploadFile : end <br>";
	}

    /**
     * Used to populate place dropdown
     */
	public function fbSearchPlace(){
        //echo 'function :    fbSearchPlace :    start<br>';
       
		$fb = self::$fb; 	//instantiate facebook

		$q_place = $this->input->post('q_place');

        try {
            // Returns a `Facebook\FacebookResponse` object
        	$app_id = APP_ID; $app_secret = APP_SECRET;

            //$response = $fb->get("search?type=place&q=$q_place&fields=id,name&limit=25", "$app_id|$app_secret");

            $response = $fb->get("search?type=place&q=Makati&fields=id,name&limit=25", "$app_id|$app_secret");

            if(!empty($response)){  //not empty response?
                //echo 'response not empty<br>';
                $response_graphEdge = $response->getGraphEdge();   //response to graphEdge convertion
                
                if(!empty($response_graphEdge)){   //not empty response graphEdge?
					$array = $response->getDecodedBody();
					$je_array = json_encode($array);
				    
                    //echo '<pre>';
                    print_r($je_array);
                    //echo '</pre>';
                }
            }
        }catch(Facebook\Exceptions\FacebookResponseException $e){
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        }catch(Facebook\Exceptions\FacebookSDKException $e){
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        //echo 'function :    fbSearchPlace : end';
	}

    /**
     * Used to set flashdata error
     * @param array $data : post data
     */
	private function _validatesmsPost($data){
		$errors = array();

		//empty all?
		if(empty($data['place'])) $data['placeId'] = '';
		if(empty($data['message']) && empty($data['link']) && empty($data['source']) && empty($data['placeId'])){
			$errors['other'] = 'This post appears to be blank. Please write something or attach a link or photo to post.';
		}

		//form validation : set rules
		$this->form_validation->set_error_delimiters('','');
		$this->form_validation->set_rules('message','Message','trim|max_length[2000]|xss_clean');
		$this->form_validation->set_rules('link','Link','trim|max_length[256]|xss_clean');
		if($_FILES["fileToUpload"]["name"] != ''){ $this->form_validation->set_rules('source','Source','callback_validatefileToUpload'); }
		$this->form_validation->set_rules('place','Place','trim|max_length[256]|xss_clean');
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

		return $errors;
	}

    /**
     * A callback function
     * Used to validate file to upload
     */
    function validatefileToUpload(){
		$file_to_upload = $_FILES["fileToUpload"]["name"];	//init file--filename to upload

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

		//is file size lower than 10000kb?
		if($_FILES["fileToUpload"]["size"] > 10000000){	//10000kb
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