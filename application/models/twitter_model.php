<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Twitter_model extends CI_Model{
    public static $tbl_tweets = 'tw_tweets';

    /**
     * used to insert tweets in batch
     * @param array $tweets : This is tweets details
     */
    public function batchInsertTweets($tweets){
        //we used native php insert query due to
        //limited functionality of active record on batch_insert update on key duplicate

        $count_tweets = count($tweets);

        $sql = 'INSERT INTO ' . self::$tbl_tweets . ' (tweet_id, created_at, text, source, in_reply_to_status_id, in_reply_to_user_id, user_id, language) VALUES ';
        
        foreach ($tweets as $row) {
            $sql .= '("'.$row['tweet_id'].'", "'.$row['created_at'].'", "'.$row['text'].'", "'.$row['source'].'", "'.$row['in_reply_to_status_id'].'", "'.$row['in_reply_to_user_id'].'", "'.$row['user_id'].'", "'.$row['language'].'")';
            
            //put a comma if it is not the last array of values
            if($count_tweets > 1)
            $sql .= ', ';

            $count_tweets--;
        }

        //update on key duplicate
        $sql .= ' ON DUPLICATE KEY UPDATE';
        $sql .= ' text = VALUES(text);';

        $this->db->query($sql);
    }

}

?>