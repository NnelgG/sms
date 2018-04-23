<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Sms_model extends CI_Model{
    public static $tbl_fb_users = 'fb_users';
    public static $tbl_fb_pages = 'fb_pages';
    public static $tbl_sms_posts = 'sms_posts';

    /**
     * Used to get the fb user listing
     * @return array $result : This is result
     */
    function fbUserListing(){
        $this->db->select('id, user_id, first_name, last_name, access_token');
        $this->db->from(self::$tbl_fb_users);
        
        $query = $this->db->get();
        
        $result = $query->result();        
        
        return $result;
    }
    
    /**
     * Used to get the fb page listing count
     * @return array $result : This is result
     */
    function fbPageListing(){
        $this->db->select('id, user_id, page_id, name, access_token');
        $this->db->from(self::$tbl_fb_pages);
        
        $query = $this->db->get();
        
        $result = $query->result();        
        
        return $result;
    }

    /**
     * Used to get the fb user 'a' token
     * @param string $fb_id : Facebook id of user/page
     * @param boolean $is_page : 
     * @return string $result : This is result
     */
    function fbGetValidateAccessToken($fb_id, $is_page = false){
        $this->db->select('access_token');
        
        if($is_page == false){
            $this->db->from(self::$tbl_fb_users);
            $this->db->where('user_id', $fb_id);
        }else{
            $this->db->from(self::$tbl_fb_pages);
            $this->db->where('page_id', $fb_id);
        }

        $query = $this->db->get();
        
        $result = $query->row();

        if(!empty($result))
            return $result->access_token;  
    }

    /**
     * Used to get the fb page owner id
     * @return string $result : This is result
     */
    function fbGetPageUserId($fb_page_id){
        $this->db->select('user_id');
        $this->db->from(self::$tbl_fb_pages);
        $this->db->where('page_id', $fb_page_id);

        $query = $this->db->get();
        
        $result = $query->row();

        return $result->user_id;  
    }

    /**
     * Used to insert sms post
     * @param array $smsPost : This is sms post details
     */
    function insertSmsPost($smsPost){
        $this->db->insert(self::$tbl_sms_posts, $smsPost); 
    }

    /**
     * Used to get the sms posts by schedule datetime
     * @param string $sched_dateTime : input datetime
     * @return array $result : This is result
     */
    function smsPostsGetBySchedDtm($schedDateTime){
        $this->db->select('postTo');
        $this->db->from(self::$tbl_sms_posts);
        $this->db->where('scheduleDtm', $schedDateTime);

        $query = $this->db->get();
        
        $result = $query->row();

        if(!empty($result))
            return $result->postTo;  
    }

}

  