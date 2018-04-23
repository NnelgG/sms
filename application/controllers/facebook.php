<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

class Facebook extends BaseController {
	/*public static $fb = array();

    public function __construct(){
        parent::__construct();
        
        //req to load
		require_once APPPATH . 'libraries/php-graph-sdk-5.x/src/Facebook/autoload.php';
        $this->load->model('facebook_model');

		self::$fb = new Facebook\Facebook(['app_id' => APP_ID, 'app_secret' => APP_SECRET, 'default_graph_version' => DEFAULT_GRAPH_VERSION, 'fileUpload' => true]);

        $this->isLoggedIn();   
    }*/

    /*public function authFbUser(){
		$app_id = APP_ID;
		$app_secret = APP_SECRET;
		$my_url = "http://192.168.0.99/sms/sms/";
	     
		//known valid access token stored in a database 
		$access_token = "";

		$code = isset($_REQUEST["code"]);

		echo 'request code: ';
		echo '<pre>' . var_dump($code) . '</pre><br>';
	    
		//If we get a code, it means that we have re-authed the user 
		//and can get a valid access_token. 
		if (isset($code)) {
			echo 'isset code: ';
			echo '<pre>' . var_dump($code) . '</pre><br>';

			$token_url = "https://graph.facebook.com/oauth/access_token?client_id=" . APP_ID . "&redirect_uri=" . urlencode($my_url) . "&client_secret=" . APP_SECRET . "&code=" . $code . "&display=popup";
		    echo 'token URL: ';
		    echo '<pre>' . var_dump($token_url) . '</pre><br>';

		    $response = file_get_contents($token_url);
		    echo 'response: ';
		    echo '<pre>' . var_dump($response) . '</pre><br>';

		    $params = null;
		    parse_str($response, $params);

		    echo 'params: ';
		    echo '<pre>' . var_dump($params) . '</pre><br>';

		    $access_token = $params['access_token'];

		    echo 'a token: ';
		    echo '<pre>' . var_dump($access_token) . '</pre><br>';
	  	}
     
		// Attempt to query the graph:
		$graph_url = "https://graph.facebook.com/me?" . "access_token=" . $access_token;
		$response = $this->curl_get_file_contents($graph_url);
		$decoded_response = json_decode($response);

		echo 'decoded response: ';
		echo '<pre>' . var_dump($decoded_response) . '</pre><br>';
	    
		//Check for errors 
		if ($decoded_response->error) {
			// check to see if this is an oAuth error:
		    if ($decoded_response->error->type== "OAuthException") {
		    	// Retrieving a valid access token. 
		    	$dialog_url= "https://www.facebook.com/dialog/oauth?" . "client_id=" . APP_ID . "&redirect_uri=" . urlencode($my_url);
		    	echo("<script> top.location.href='" . $dialog_url . "'</script>");
		    }else{
		      echo "other error has happened";
		    }
		}else{
	  		//success
	    	echo("success" . $decoded_response->name);
	    	echo($access_token);
	  	}    
    }

	//note this wrapper function exists in order to circumvent PHP’s 
	//strict obeying of HTTP error codes.  In this case, Facebook 
	//returns error code 400 which PHP obeys and wipes out 
	//the response.
	function curl_get_file_contents($URL) {
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $URL);
		$contents = curl_exec($c);
		$err  = curl_getinfo($c,CURLINFO_HTTP_CODE);
		curl_close($c);
		if ($contents) return $contents;
		else return FALSE;
	}*/

    /*public function login(){
	    if (!session_id()) {
		    session_start();
		}

		$fb = self::$fb; 	//instantiate facebook

		$helper = $fb->getRedirectLoginHelper();	//obtain user 'a' token

		//permissions
		$permissions = ['email, publish_actions, manage_pages, publish_pages'];

		$loginUrl = $helper->getLoginUrl('http://192.168.0.99/sms/facebook/fb_callback', $permissions);

		echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
    }

    public function fb_callback(){
	    if (!session_id()) {
	    	session_start();
		}

		$fb = self::$fb; 	//instantiate facebook

		$helper = $fb->getRedirectLoginHelper();	//obtain user 'a' token

		try {
			$accessToken = $helper->getAccessToken();	//get access token
		}catch(Facebook\Exceptions\FacebookResponseException $e) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		}catch(Facebook\Exceptions\FacebookSDKException $e) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		//is access token set?
		if(!isset($accessToken)){
			if ($helper->getError()){
			    header('HTTP/1.0 401 Unauthorized');
			    echo "Error: " . $helper->getError() . "\n";
			    echo "Error Code: " . $helper->getErrorCode() . "\n";
			    echo "Error Reason: " . $helper->getErrorReason() . "\n";
			    echo "Error Description: " . $helper->getErrorDescription() . "\n";
		  	}else{
			    header('HTTP/1.0 400 Bad Request');
			    echo 'Bad request';
		  	}
		  exit;
		}

		// Logged in
		echo '<h3>Access Token</h3>';
		echo '<pre>';
		var_dump($accessToken->getValue());
		echo '</pre>';

		// The OAuth 2.0 client handler helps us manage access tokens
		$oAuth2Client = $fb->getOAuth2Client();

		// Get access token metadata from debugToken
		$tokenMetadata = $oAuth2Client->debugToken($accessToken);
		echo '<h3>Metadata</h3>';
		echo '<pre>';
		var_dump($tokenMetadata);
		echo '</pre>';

		// Validation (these will throw FacebookSDKException's when they fail)
		$tokenMetadata->validateAppId('198412677388382');

		// If you know the user ID this access token belongs to, you can validate it here
		//$tokenMetadata->validateUserId('123');
		$tokenMetadata->validateExpiration();

		// is access token long lived?
		if(!$accessToken->isLongLived()){
			try{
				//Exchanges a short-lived access token for a long-lived one
		    	$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
		  	}catch (Facebook\Exceptions\FacebookSDKException $e){
			    echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
			    exit;
		  	}

			echo '<h3>Long-lived</h3>';
			var_dump($accessToken->getValue());
		}

		$_SESSION['fb_access_token'] = (string) $accessToken;

		// User is logged in with a long-lived access token.
		// You can redirect them to a members-only page.
		// header('Location: https://example.com/members.php');
    }*/

}
