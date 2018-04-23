<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

class Poll extends BaseController {
	public static $fb = array();

    /**
     * This is default constructor of the class
     */
    public function __construct(){
        parent::__construct();
       
		//req to load
		$this->load->model('poll_model');
		$this->load->model('sms_model');
		$this->load->library('cias_library');
    }

    public function index(){
		$cont = true;
		$i = 0;

		while ($cont && $i <= 1440){
	  		sleep(60);

	  		date_default_timezone_set('Asia/Manila');
			$nowDtm = date("Y-m-d H:i:00");

	  		//req data
			$data['smsPosts'] = $this->poll_model->smsPostListing($nowDtm);
			$dataSmsPostRecords = $data['smsPosts'];

			echo "----nowDtm: $nowDtm <br>";

            // is not empty sms posts?
            if(!empty($dataSmsPostRecords)){
            	$i_source = 0; $array_sources = array();
            	foreach ($dataSmsPostRecords as $row) {
            		//echo '<pre>'; print_r($row); echo '</pre>';
            		
            		// is not empty placeId?
            		$placeId = (!empty($row->placeId)) ? $row->placeId : '';

            		$this->smsPost(true, $row->postTo, $row->message, $row->link, $placeId, $row->privacy, $row->image);
            		
            		//img sources--for later use--unlink (delete)
            		if(!empty($row->image)){
	                    $array_sources[$i_source]['source_id'] = $row->image;
	                    $i_source++;
            		}

            		$this->poll_model->smsPostUpdatePostedDtm($row->id, $nowDtm);	// update
            	}

            	//unlink (delete) image
        		if(count($i_source > 0)){
                    echo 'image sources: <pre>'; var_dump($array_sources); echo '</pre>';
                    foreach ($array_sources as $row) {
                    	if(file_exists($row['source_id'])){ 
                    		echo '---------------- unlink (delete): '. $row['source_id'] . '<br>';
                    		unlink($row['source_id']); 
                    	} 
                    }
        		}
            }else{
            	echo "----$i: empty <br>";
            }

            $i++;

	   		ob_flush(); flush();
		}
    }

	public function smsPost($isPostScheduled=false, $postTo, $message, $link, $placeId, $privacy='SELF', $source='') {
        echo 'function : smsPost : start <br>';

        $isValidPostParams = true;	//temp

    	// step 1 : get input
    	$postTo = $postTo;
        $data['post']['message'] = $message;
        $data['post']['link'] = $link;
        $data['post']['placeId'] = $placeId;
        $data['post']['privacy'] = $privacy;
        $data['post']['source'] = $source;

        //echo "<pre>$postTo "; print_r($data['post']); echo '</pre><br>';

        // step 3: validate post params
        // log error if not validated

        if($isValidPostParams){
        	echo '----is valid post params <br>';

			// step 2 : sanitize
		    $message = filter_var($data['post']['message'], FILTER_SANITIZE_STRING);
		    $link = $data['post']['link'];
		    $placeId = $data['post']['placeId'];
		    $privacy =  $data['post']['privacy'];
		    $source = $data['post']['source'];

	        // explode post to
		    if(!empty($postTo)){
		    	echo '-------- not empty expPostTo <br>';
		    	$expPostTo = explode('|', $postTo);
		    	//echo '<pre>'; print_r($expPostTo); echo '</pre><br>';

				// each expPostTo (facebook ids of user and pages) will undergo below process...
			    foreach ($expPostTo as $row) {			    	
			    	//segregate social media platforms
					if (strpos($row, 'fb') !== false) {
					    //echo '------------ row expPostTo fb: ' . $row . '<br>';
					
						// segregate facebook user to page
						if(strpos($row, 'user') !== false){
							$fbUserId = str_replace('fb-user-', '', $row); // remove 'fb-user' from string
							echo '---------------- row expPostTo fb user: ' . $fbUserId . '<br>';

				    		$fbUserAToken = $this->cias_library->fbGetValidateAccessToken($fbUserId); // get 'a' token

					    	// is not empty file attachment? Y: init post params for photo upload
					        if(!empty($source)){
					        	$sourceFbUser = $source;
					        	$messageFbUser = $message . ' ' . $link;

					        	$postParams = array("message"=>$messageFbUser, "source"=>$sourceFbUser, "link"=>'', "place"=>$placeId, "privacy"=>"{'value':'$privacy'}");
					        }else{
					        	$messageFbUser = $message;

					        	$postParams = array("message"=>$messageFbUser, "link"=>$link, "place"=>$placeId, "privacy"=>"{'value':'$privacy'}");
					        }

					        //post
					        $isSuccessFbPostAsUser = $this->cias_library->smsPostAsUser($fbUserId, $postParams, $fbUserAToken);

					        echo '---------------- is success fb post as user: ' . $isSuccessFbPostAsUser . '<br>';
				    	}else if(strpos($row, 'page') !== false){
							$fbPageId = str_replace('fb-page-', '', $row); // remove 'fb-page' from string
							echo '---------------- row expPostTo fb page : ' . $fbPageId . '<br>';

							$fbUserId = $this->sms_model->fbGetPageUserId($fbPageId);
				        	$fbUserAToken = $this->cias_library->fbGetValidateAccessToken($fbUserId);	//fb user access token

					    	//is not empty file attachment? Y: init post params for photo upload
					        if(!empty($source)){
					        	$sourcFbPage = $source;
					        	$messageFbPage = $message . ' ' . $link;

					        	$postParamsPage = array("message"=>$messageFbPage, "source"=>$sourcFbPage, "link"=>'', "place"=>$placeId);
					        }else{
					        	$messageFbPage = $message;

					        	$postParamsPage = array("message"=>$messageFbPage, "link"=>$link, "place"=>$placeId);
					        }

					        //post
					        $isSuccessFbPostAsPage = $this->cias_library->smsPostAsPage($fbUserId, $fbPageId, $postParamsPage, $fbUserAToken);

					        echo '---------------- is success fb post as page: ' . $isSuccessFbPostAsPage . '<br>';
						}
					}
			    }
		    }
        }else{
       		//log error
        }

        echo 'function : smsPost : end <br>';
    }

}

?>

