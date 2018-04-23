<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Facebook_model extends CI_Model{
    public static $tbl_fb_pages = 'fb_pages';
    public static $tbl_fb_posts = 'fb_posts';
    public static $tbl_fb_comments = 'fb_comments';
    public static $tbl_fb_ch_comments = 'fb_ch_comments';

    /**
     * used to insert facebook pages in batch
     * @param array $fb_pages : This is facebook page details
     */
    public function batchInsertFbPages($fb_pages){
        //batch insert
        //$this->db->insert_batch('fb_pages', $fb_pages);

        //we used native php insert query due to
        //limited functionality of active record on batch_insert update on key duplicate

        $count_fb_pages = count($fb_pages);

        $sql = 'INSERT INTO ' . self::$tbl_fb_pages . ' (page_id, name, category, about, emails, location, phone, website, birthday) VALUES ';
        
        foreach ($fb_pages as $row) {
            $sql .= '("'.$row['page_id'].'", "'.$row['name'].'", "'.$row['category'].'", "'.$row['about'].'", "'.$row['emails'].'", "'.$row['location'].'", "'.$row['phone'].'", "'.$row['website'].'", "'.$row['birthday'].'")';
            
            //put a comma if it is not the last array of values
            if($count_fb_pages > 1)
            $sql .= ', ';

            $count_fb_pages--;
        }

        //update on key duplicate
        $sql .= ' ON DUPLICATE KEY UPDATE';
        $sql .= ' name = VALUES(name), category = VALUES(category), about = VALUES(about), emails = VALUES(emails), location = VALUES(location), phone = VALUES(phone), website = VALUES(website), birthday = VALUES(birthday);';

        $this->db->query($sql);
    }

    /**
     * used to insert facebook posts in batch
     * @param array $fb_posts : This is facebook posts details
     */
    public function batchInsertFbPosts($fb_posts){

        $count_fb_posts = count($fb_posts);

        $sql = 'INSERT INTO ' . self::$tbl_fb_posts . ' (post_id, post_from, created_time, message) VALUES ';
        
        foreach ($fb_posts as $row) {
            $sql .= '("'.$row['post_id'].'", "'.$row['post_from'].'", "'.$row['created_time'].'", "'.$row['message'].'")';
            
            //put a comma if it is not the last array of values
            if($count_fb_posts > 1)
            $sql .= ', ';

            $count_fb_posts--;
        }

        //update on key duplicate
        $sql .= ' ON DUPLICATE KEY UPDATE';
        $sql .= ' message = VALUES(message);';

        $this->db->query($sql);
    }

    /**
     * used to insert facebook comments in batch
     * @param array $fb_comments : This is facebook comments details
     */
    public function batchInsertFbComments($fb_comments){

        $count_fb_comments = count($fb_comments);

        $sql = 'INSERT INTO ' . self::$tbl_fb_comments . ' (post_id, comment_id, created_time, message) VALUES ';
        
        foreach ($fb_comments as $row) {
            $sql .= '("'.$row['post_id'].'", "'.$row['comment_id'].'", "'.$row['created_time'].'", "'.$row['message'].'")';
            
            //put a comma if it is not the last array of values
            if($count_fb_comments > 1)
            $sql .= ', ';

            $count_fb_comments--;
        }

        //update on key duplicate
        $sql .= ' ON DUPLICATE KEY UPDATE';
        $sql .= ' message = VALUES(message);';

        $this->db->query($sql);
    }

    /**
     * used to insert facebook child comments in batch
     * @param array $fb_ch_comments : This is facebook child comments details
     */
    public function batchInsertFbChildComments($fb_ch_comments){

        $count_fb_ch_comments = count($fb_ch_comments);

        $sql = 'INSERT INTO ' . self::$tbl_fb_ch_comments . ' (comment_id, ch_comment_id, created_time, message) VALUES ';
        
        foreach ($fb_ch_comments as $row) {
            $sql .= '("'.$row['comment_id'].'", "'.$row['ch_comment_id'].'", "'.$row['created_time'].'", "'.$row['message'].'")';
            
            //put a comma if it is not the last array of values
            if($count_fb_ch_comments > 1)
            $sql .= ', ';

            $count_fb_ch_comments--;
        }

        //update on key duplicate
        $sql .= ' ON DUPLICATE KEY UPDATE';
        $sql .= ' message = VALUES(message);';

        $this->db->query($sql);
    }

    /*
    foreach ($fb_pages as $row_ap) {
        $sql .= 'INSERT INTO fb_pages (page_id, page_name, page_about, emails, location, phone, website, birthday)
        VALUES ("'.$row_ap['page_id'].'", "'.$row_ap['page_name'].'", "'.$row_ap['page_about'].'", "'.$row_ap['emails'].'", "'.$row_ap['location'].'", "'.$row_ap['phone'].'", "'.$row_ap['website'].'", "'.$row_ap['birthday'].'");';
    }
    */
}

?>