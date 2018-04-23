<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Twitter extends CI_Controller {

    public function __construct(){
        parent::__construct();

        $this->load->model('twitter_model');  //req to load
    }

    public function crawl(){
        
    	//req to load
        require_once APPPATH . 'libraries/Twitter-API-Search-Tweets-or-Hashtags-master/twitteroauth/twitteroauth.php';
        echo 'requires :    twitter api<br>';

		$max_id = "";
		$i_tw = 0;

		foreach (range(1, 10) as $i) { // up to x pages 	//temp
			$query = array(
				"q" => "suzuki",
			    "count" => 10,
			    "result_type" => "recent",
			    "lang" => "en",
			    "max_id" => $max_id,
			);
		 
		 	//api request
		  	$tweets = $this->search($query);
		  	echo 'twitter api request<br>';

		  	echo 'Count tweets: ' . count($tweets) . '<br>';

			if(!empty($tweets)){
				echo 'tweets statuses not empty<br>';

				foreach ($tweets->statuses as $key => $tweet) {
			        //isset field?  //sanitize string 	//format
			        $created_at_formatted = date("Y-m-d H:i:s", strtotime($tweet->created_at));
			        $text = (isset($tweet->text)) ? filter_var($tweet->text, FILTER_SANITIZE_STRING) : '';
			        $source = (isset($tweet->source)) ? filter_var($tweet->source, FILTER_SANITIZE_STRING) : '';
			        $in_reply_to_status = (isset($tweet->in_reply_to_status_id)) ? filter_var($tweet->in_reply_to_status_id, FILTER_SANITIZE_STRING) : '';
			        $in_reply_to_user_id = (isset($tweet->in_reply_to_user_id)) ? filter_var($tweet->in_reply_to_user_id, FILTER_SANITIZE_STRING) : '';
			        $user_id = (isset($tweet->user_id)) ? filter_var($tweet->user_id, FILTER_SANITIZE_STRING) : '';
			        $retweet_count = (isset($tweet->retweet_count)) ? filter_var($tweet->retweet_count, FILTER_SANITIZE_STRING) : '';
			        $retweeted = (isset($tweet->retweeted)) ? filter_var($tweet->retweeted, FILTER_SANITIZE_STRING) : '';
			        $language = (isset($tweet->language)) ? filter_var($tweet->language, FILTER_SANITIZE_STRING) : '';

				    echo '<strong>Tweet i: </strong>' . $i_tw . '<br>';
				    echo '<strong>Id: </strong>' . $tweet->id . '<br>';
				    echo '<strong>Created at: </strong>' . $created_at_formatted . '<br>';
				    echo '<strong>Text: </strong>' . $text . '<br>';
				    echo '<strong>Source: </strong>' . $source . '<br>';
				    //echo '<strong>Truncated: </strong>' . $tweet->truncated . '<br>';
				    echo '<strong>In reply to status id: </strong>' . $in_reply_to_status . '<br>';
				    echo '<strong>In reply to user id str: </strong>' . $in_reply_to_user_id . '<br>';
				    echo '<strong>User Profile: </strong><br>';
				    echo '<strong>----User Id: </strong>' . $user_id . '<br>';
				    echo '<strong>----Name: </strong>' . $tweet->user->name . '<br>';
				    echo '<strong>----Location: </strong>' . $tweet->user->location . '<br>';
				    echo '<strong>----URL: </strong>' . $tweet->user->url . '<br>';
				    echo '<strong>----Description: </strong>' . $tweet->user->description . '<br>';
				    echo '<strong>----Followers count: </strong>' . $tweet->user->followers_count . '<br>';
				    echo '<strong>----Friends count: </strong>' . $tweet->user->friends_count . '<br>';
				    echo '<strong>----Created at: </strong>' . $tweet->user->created_at . '<br>';
				    echo '<strong>----Language: </strong>' . $tweet->user->lang . '<br>';
				    echo '<strong>----Profile img url: </strong>' . $tweet->user->profile_image_url . '<br>';
				    echo '<strong>Retweet Count: </strong>' . $retweet_count . '<br>';
				    echo '<strong>Retweeted: </strong>' . $retweeted . '<br>';
				    echo '<strong>Language: </strong>' . $language . '<br>';
				    echo '+ + + + + + + + + + + + + + + + + + + +<br>';

		            //tweet details--for later use--batch insert
		            $array_tweets[$i_tw]['tweet_id'] = $tweet->id;
		            $array_tweets[$i_tw]['created_at'] = $created_at_formatted;
		            $array_tweets[$i_tw]['text'] = $text;
		            $array_tweets[$i_tw]['source'] = $source;
		            $array_tweets[$i_tw]['in_reply_to_status_id'] = $in_reply_to_status;
		            $array_tweets[$i_tw]['in_reply_to_user_id'] = $in_reply_to_user_id;
		            $array_tweets[$i_tw]['user_id'] = $user_id;
		            $array_tweets[$i_tw]['retweet_count'] = $retweet_count;
		            $array_tweets[$i_tw]['retweeted'] = $retweeted;
		            $array_tweets[$i_tw]['language'] = $language;

				    $max_id = $tweet->id_str; // Set max_id for the next search page
				    $i_tw++;
			  	}
			}else{
				echo 'tweets statuses empty<br>';
			}

		}

		$this->twitter_model->batchInsertTweets($array_tweets);   //batch insert
    }

	function search(array $query){
		$toa = new TwitterOAuth('jQpx7Upvr5xi7NOuuJDjUFwTk', 'n9YPFjmU6ncQF312P55FeKPu0D0tEU5DlI5PrWuKYCeORbuTwR', '865090536184532994-qpvQtTrNbVnM26ufkbcsDZBUtB6hKEM', 'pTsVcS0yFH37m2BcSQDDcyEp0wGV2i1XVz8ejH0WYOC1h');
		return $toa->get('search/tweets', $query);
	}

    /*public function crawller() {
        echo 'function :    index :    start<br>';
        
        require_once APPPATH . 'libraries/Twitter-API-Search-Tweets-or-Hashtags-master/twitteroauth/twitteroauth.php';
        echo 'requires :    twitter api<br>';

		//set access tokens here
		$consumer_key = "jQpx7Upvr5xi7NOuuJDjUFwTk";
		$consumer_secret = "n9YPFjmU6ncQF312P55FeKPu0D0tEU5DlI5PrWuKYCeORbuTwR";
		$access_token = "865090536184532994-qpvQtTrNbVnM26ufkbcsDZBUtB6hKEM";
		$access_token_secret = "pTsVcS0yFH37m2BcSQDDcyEp0wGV2i1XVz8ejH0WYOC1h";

		//authorization
		$twitter = new TwitterOAuth($consumer_key,$consumer_secret,$access_token,$access_token_secret);

		//api request
		$tweets = $twitter->get('https://api.twitter.com/1.1/search/tweets.json?q=suzuki&result_type=recent&count=100');
		echo 'twitter api request<br>';

		$count_tweets = count($tweets);

		echo 'Count tweets: ' . $count_tweets . '<br>';

		$i_tw = 0;

		foreach ($tweets->statuses as $key => $tweet) {
            //isset field?  //sanitize string
            $tw_created_at_formatted = date("Y-m-d H:i:s", strtotime($tweet->created_at));
            $tw_text = (isset($tweet->text)) ? filter_var($tweet->text, FILTER_SANITIZE_STRING) : '';
            $tw_source = (isset($tweet->source)) ? filter_var($tweet->source, FILTER_SANITIZE_STRING) : '';
            $in_reply_to_status = (isset($tweet->in_reply_to_status_id)) ? filter_var($tweet->in_reply_to_status_id, FILTER_SANITIZE_STRING) : '';
            $in_reply_to_user_id = (isset($tweet->in_reply_to_user_id)) ? filter_var($tweet->in_reply_to_user_id, FILTER_SANITIZE_STRING) : '';
            $tw_user_id = (isset($tweet->user_id)) ? filter_var($tweet->user_id, FILTER_SANITIZE_STRING) : '';
            $retweet_count = (isset($tweet->retweet_count)) ? filter_var($tweet->retweet_count, FILTER_SANITIZE_STRING) : '';
            $retweeted = (isset($tweet->retweeted)) ? filter_var($tweet->retweeted, FILTER_SANITIZE_STRING) : '';
            $tw_language = (isset($tweet->language)) ? filter_var($tweet->language, FILTER_SANITIZE_STRING) : '';

		    echo '<strong>Tweet i: </strong>' . $i_tw . '<br>';
		    echo '<strong>Id: </strong>' . $tweet->id . '<br>';
		    echo '<strong>Created at: </strong>' . $tw_created_at_formatted . '<br>';
		    echo '<strong>Text: </strong>' . $tw_text . '<br>';
		    echo '<strong>Source: </strong>' . $tw_source . '<br>';
		    //echo '<strong>Truncated: </strong>' . $tweet->truncated . '<br>';
		    echo '<strong>In reply to status id: </strong>' . $in_reply_to_status . '<br>';
		    echo '<strong>In reply to user id str: </strong>' . $in_reply_to_user_id . '<br>';
		    echo '<strong>User Profile: </strong><br>';
		    echo '<strong>----User Id: </strong>' . $tw_user_id . '<br>';
		    echo '<strong>----Name: </strong>' . $tweet->user->name . '<br>';
		    echo '<strong>----Location: </strong>' . $tweet->user->location . '<br>';
		    echo '<strong>----URL: </strong>' . $tweet->user->url . '<br>';
		    echo '<strong>----Description: </strong>' . $tweet->user->description . '<br>';
		    echo '<strong>----Followers count: </strong>' . $tweet->user->followers_count . '<br>';
		    echo '<strong>----Friends count: </strong>' . $tweet->user->friends_count . '<br>';
		    echo '<strong>----Created at: </strong>' . $tweet->user->created_at . '<br>';
		    echo '<strong>----Language: </strong>' . $tweet->user->lang . '<br>';
		    echo '<strong>----Profile img url: </strong>' . $tweet->user->profile_image_url . '<br>';
		    echo '<strong>Retweet Count: </strong>' . $retweet_count . '<br>';
		    echo '<strong>Retweeted: </strong>' . $retweeted . '<br>';
		    echo '<strong>Language: </strong>' . $tw_language . '<br>';
		    echo '+ + + + + + + + + + + + + + + + + + + +<br>';

            //collect each tweet info--for later use--batch insert
            //current tweet details
            $array_tweets[$i_tw]['tweet_id'] = $tweet->id;
            $array_tweets[$i_tw]['created_at'] = $tw_created_at_formatted;
            $array_tweets[$i_tw]['text'] = $tw_text;
            $array_tweets[$i_tw]['source'] = $tw_source;
            $array_tweets[$i_tw]['in_reply_to_status_id'] = $in_reply_to_status;
            $array_tweets[$i_tw]['in_reply_to_user_id'] = $in_reply_to_user_id;
            $array_tweets[$i_tw]['user_id'] = $tw_user_id;
            $array_tweets[$i_tw]['retweet_count'] = $retweet_count;
            $array_tweets[$i_tw]['retweeted'] = $retweeted;
            $array_tweets[$i_tw]['language'] = $tw_language;

		    $i_tw++;
		}

		$continue = $this->twitter_model->batchInsertTweets($array_tweets);   //batch insert

        echo 'function :    index :    end<br>';
    }*/

}
