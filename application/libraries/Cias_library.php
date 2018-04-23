<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Cias_library
 * Class definition here
 * @author : Glenn Escrimadora
 * @version : 1.0
 * @since : 18 January 2018
 */
class Cias_library extends CI_Controller {
	public static $fb = array();

    /**
     * This is default constructor of the class
     */
    public function __construct(){
        parent::__construct();
       
		//req to load
		require_once APPPATH . 'libraries/php-graph-sdk-5.x/src/Facebook/autoload.php';
		$this->load->model('sms_model');	//req to load

		self::$fb = new Facebook\Facebook(['app_id'=>APP_ID, 'app_secret'=>APP_SECRET, 'default_graph_version'=>DEFAULT_GRAPH_VERSION, 'fileUpload'=>TRUE]);
    }

    /**
     * Used to get, validate fb user|page 'a' token 
     * @param string $fbId : Facebook id of user|page
     * @param boolean $isFbPage :
     * @return string result : 
     */
    public function fbGetValidateAccessToken($fbId, $isFbPage = FALSE){
    	//echo '------------function : fbGetValidateAccessToken : start <br>';

		$my_url = "http://scheduler.benfrancia.org/facebook/login";
		$access_token = $this->sms_model->fbGetValidateAccessToken($fbId, $isFbPage);	//'a' token stored in a database

		if(isset($_GET['code'])){ $code = $_REQUEST['code']; }

		//is set code? Y: means have re-authed the user|page and can get a valid 'a' token. 
		if(isset($code)){
			//echo '----------------isset code <br>';
		    $token_url = "https://graph.facebook.com/oauth/access_token?client_id=" . APP_ID . "&redirect_uri=" . urlencode($my_url) . "&client_secret=" . APP_SECRET . "&code=" . $code . "&display=popup";
		    $response = file_get_contents($token_url);
		    $params = null;
		    parse_str($response, $params);
		    $access_token = $params['access_token'];
		}

		//Attempt to query the graph
		$graph_url = "https://graph.facebook.com/$fbId?" . "access_token=" . $access_token;
		$response = $this->curlGetFileContents($graph_url);
		$decodedResponse = json_decode($response);
		$decodedResponseErr = !empty($decodedResponse->error) ? $decodedResponse->error : '';

		//is not empty decoded response error?
		if(!empty($decodedResponseErr)){
			//echo '----------------not empty decoded response error <br>';
			
			//Check for errors 
			if($decodedResponse->error){
				// check to see if this is an oAuth error:
		    	if ($decodedResponse->error->type== "OAuthException") {
			    	// Retrieving a valid access token. 
			    	$dialogUrl= "https://www.facebook.com/dialog/oauth?"."client_id=" . APP_ID . "&redirect_uri=" . urlencode($my_url);
			    	//echo("<script> top.location.href='" . $dialogUrl . "'</script>");
			    	return 0;
		    	}else{
		      		//echo "--------------------other error has happened";
		    		return 0;
		    	}
		  	}
		}else{
	  		//success
		    //echo "--------------------success: $decodedResponse->name <br>";
		    return $access_token;
		}

		//echo '------------function : fbGetValidateAccessToken : end <br>';
    }

	public function curlGetFileContents($URL) {
	    $c = curl_init();
	    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($c, CURLOPT_URL, $URL);
	    $contents = curl_exec($c);
	    $err  = curl_getinfo($c,CURLINFO_HTTP_CODE);
	    curl_close($c);
	    if ($contents) return $contents;
	    else return FALSE;
	}

    /**
     * Used to post as user to facebook
     * @param string $fbUserId : fb user id
     * @param string $fbUserPostParams : fb user post params
     * @param string $fbUserAToken : fb user 'a' token
     */
    public function smsPostAsUser($fbUserId, $fbUserPostParams, $fbUserAToken){
    	//echo 'function : smsPostAsUser : start <br>';
    	
    	$fb = self::$fb; 	//instantiate facebook

		try{
	        if(!empty($fbUserPostParams['source'])){
	        	//echo '----not empty post params source <br>';
	        	
	        	$fbUserPostParams['source'] = $fb->fileToUpload($fbUserPostParams['source']);
	        	$response = $fb->post("/$fbUserId/photos", $fbUserPostParams, $fbUserAToken);	// Returns a `Facebook\FacebookResponse` object
	        }else{
	        	//echo '----empty post params source <br>';

	        	$response = $fb->post("/$fbUserId/feed", $fbUserPostParams, $fbUserAToken);	// Returns a `Facebook\FacebookResponse` object
	        }
	        
	        //echo 'posted<br>';
        }catch(Facebook\Exceptions\FacebookResponseException $e){
	        echo 'Graph returned an error: ' . $e->getMessage();
	        exit;
        }catch(Facebook\Exceptions\FacebookSDKException $e){
	        echo 'Facebook SDK returned an error: ' . $e->getMessage();
	        exit;
        }
        
        //$graphNode = $response->getGraphNode();
        
        //echo 'function : smsPostAsUser : end <br>';
    }

    /**
     * Used to post as page to facebook
     * @param string $fbUserId : fb user id
     * @param string $fbPageId : fb page id
     * @param string $fbPagePostParams : fb page post params
     * @param string $fbUserAToken : fb user 'a' token
     */
	public function smsPostAsPage($fbUserId, $fbPageId, $fbPagePostParams, $fbUserAToken){
		//echo 'function : smsPostAsPage : start<br>';
		//ini_set('display_errors', 1);	//display detailed http error/s

		$fb = self::$fb;	//instantiate facebook
		$helper = $fb->getRedirectLoginHelper();	//obtain user 'a' token
		
		if(isset($fbUserAToken)){
			//echo "----is set a token<br>";
			
			if (isset($fbUserAToken)) {
				$fb->setDefaultAccessToken($fbUserAToken);
				//echo "--------is set fb a token, set default a token <br>";
			}

			if (isset($_GET['code'])) { header('Location: ./'); }	// redirect the user back to the same page if it has "code" GET variable
			
			//getting basic info about user
			try{
				//echo "----try 0<br>";
				$profileReq = $fb->get("/$fbUserId");
				$profile = $profileReq->getGraphNode()->asArray();
			}catch(Facebook\Exceptions\FacebookResponseException $e){
				echo 'Graph returned an error: ' . $e->getMessage();	// When Graph returns an error
				//session_destroy();
				//redirecting user back to app login page
				//header("Location: ./");
				//exit;	//log error
			}catch(Facebook\Exceptions\FacebookSDKException $e){
				echo 'Facebook SDK returned an error: ' . $e->getMessage();	// When validation fails or other local issues
				//exit;	//log error
			}

			//post on behalf of page
			$pages = $fb->get("/$fbUserId/accounts");
			$pages = $pages->getGraphEdge()->asArray();
			
			foreach ($pages as $key) {
				if ($key['id'] == $fbPageId) {
					//echo '--------' . $key['id'] . ' : ' . $key['name'] . ' found. <br>'; 
					if(!empty($fbPagePostParams['source'])){
						$fbPagePostParams['source'] = $fb->fileToUpload($fbPagePostParams['source']);
						//echo '------------not empty post params page source <br>'; 
						//echo '<pre>' . var_dump($fbPagePostParams['source']) . '</pre>';
						$response = $fb->post("/$fbPageId/photos", $fbPagePostParams, $key['access_token']);
						//echo '------------posted post params with source <br>'; 
					}else{
						//echo '------------empty post params page source <br>';  
						$response = $fb->post("/$fbPageId/feed", $fbPagePostParams, $key['access_token']);
						//echo '------------posted post params without source <br>'; 
					}
			    	
			    	//$graphNode = $response->getGraphNode();
					//print_r($response);
				}
			}
		}else{
			//return error
		}

		//echo 'function : smsPostAsPage : end<br>';
	}


}