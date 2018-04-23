<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Sms_rud_model extends CI_Model{
	public static $tbl_sms_posts = 'sms_posts';
	public static $tbl_fb_users = 'fb_users';
	public static $tbl_fb_pages = 'fb_pages';

    /**
     * Used to get the fb user listing
     * @return array $result : This is result
     */
    function fbUserListing(){
        $this->db->select('user_id, first_name, last_name');
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
        $this->db->select('page_id, name');
        $this->db->from(self::$tbl_fb_pages);
        
        $query = $this->db->get();
        
        $result = $query->result();        
        
        return $result;
    }
    /**
     * Used to get the sms listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    /*function smsListingCount($searchText = ''){
        $this->db->select('fb_s_p.id, fb_s_p.createdBy, fb_s_p.createdDtm, fb_s_p.isPage, fb_s_p.postTo, fb_s_p.scheduledDtm, fb_s_p.postedDtm, fb_s_p.message, fb_s_p.link, fb_s_p.image, fb_s_p.place, fb_s_p.privacy, fb_u.first_name, fb_u.last_name, fb_p.name');
        $this->db->join('fb_users as fb_u', 'fb_u.user_id = fb_s_p.postTo', 'left');
        $this->db->join('fb_pages as fb_p', 'fb_p.page_id = fb_s_p.postTo', 'left');
        $this->db->from('sms_posts as fb_s_p');

        if(!empty($searchText)) {
            $likeCriteria = "(fb_s_p.scheduledDtm  LIKE '%".$searchText."%'
                            OR  fb_s_p.message  LIKE '%".$searchText."%'
                            OR  fb_s_p.link  LIKE '%".$searchText."%'
                            OR  fb_u.first_name  LIKE '%".$searchText."%'
                            OR  fb_u.last_name  LIKE '%".$searchText."%'
                            OR  fb_p.name  LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }

        $this->db->order_by('fb_s_p.createdDtm', 'asc');

        $query = $this->db->get();
        
        return count($query->result());
    }*/
    function smsListingCount($searchText = ''){
        //$this->db->select('id, createdBy, createdDtm, postTo, scheduleDtm, postedDtm, message, link, image, placeId, privacy');
        $this->db->from(self::$tbl_sms_posts);

        if(!empty($searchText)) {
            $likeCriteria = "(scheduleDtm  LIKE '%".$searchText."%'
                            OR  message  LIKE '%".$searchText."%'
                            OR  link  LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }

        $this->db->where('postedDtm IS NULL');

        $this->db->order_by('scheduleDtm', 'desc');

        $query = $this->db->get();
        
        return count($query->result());
    }


    /**
     * Used to get the sms listing
     * @return array $result : This is result
     */
    /*function smsListing($searchText = '', $page, $segment){
        $this->db->select('fb_s_p.id, fb_s_p.createdBy, fb_s_p.createdDtm, fb_s_p.isPage, fb_s_p.postTo, fb_s_p.scheduledDtm, fb_s_p.postedDtm, fb_s_p.message, fb_s_p.link, fb_s_p.image, fb_s_p.place, fb_s_p.privacy, fb_u.first_name, fb_u.last_name, fb_p.name');
        $this->db->join('fb_users as fb_u', 'fb_u.user_id = fb_s_p.postTo', 'left');
        $this->db->join('fb_pages as fb_p', 'fb_p.page_id = fb_s_p.postTo', 'left');
        $this->db->from('sms_posts as fb_s_p');
        
        if(!empty($searchText)) {
            $likeCriteria = "(fb_s_p.scheduledDtm  LIKE '%".$searchText."%'
                            OR  fb_s_p.message  LIKE '%".$searchText."%'
                            OR  fb_s_p.link  LIKE '%".$searchText."%'
                            OR  fb_u.first_name  LIKE '%".$searchText."%'
                            OR  fb_u.last_name  LIKE '%".$searchText."%'
                            OR  fb_p.name  LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }

        $this->db->order_by('fb_s_p.createdDtm', 'asc');

        $this->db->limit($page, $segment);

        $query = $this->db->get();
        
        $result = $query->result();        
        
        return $result;
    }*/
    function smsListing($searchText = '', $page, $segment){
        $this->db->select('id, createdBy, createdDtm, postTo, scheduleDtm, postedDtm, message, link, image, placeId, privacy');
        $this->db->from(self::$tbl_sms_posts);

        if(!empty($searchText)) {
            $likeCriteria = "(scheduleDtm  LIKE '%".$searchText."%'
                            OR  message  LIKE '%".$searchText."%'
                            OR  link  LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }

        $this->db->where('postedDtm IS NULL');
        
        $this->db->order_by('scheduleDtm', 'desc');

        $this->db->limit($page, $segment);

        $query = $this->db->get();
        
        $result = $query->result();        
        
        return $result;
    }

    /**
     * Used to get sms information by sms id
     * @param number $smsId : This is sms id
     * @return array $result : This is user information
     */
    function getSmsInfo($smsId){
        $this->db->select('id, createdBy, createdDtm, postTo, scheduleDtm, postedDtm, message, link, image, placeId, privacy');
        $this->db->from(self::$tbl_sms_posts);
        $this->db->where('id', $smsId);
        
        $query = $this->db->get();
        
        return $query->result();
    }

    /**
     * Used to update the sms information
     * @param number $smsId : This is sms id
     * @param array $smsInfo : This is sms updated information
     */
    function editSms($smsId, $smsInfo){
        $this->db->where('id', $smsId);
        $this->db->update(self::$tbl_sms_posts, $smsInfo);
        
        return TRUE;
    }

    /**
     * Used to delete the sms information
     * @param number $smsId : This is sms id
     * @return boolean $result : TRUE / FALSE
     */
    function deleteSms($smsId){
        $this->db->where('id', $smsId);
        $this->db->delete(self::$tbl_sms_posts); 
        
        return $this->db->affected_rows();
    }

    /**
     * Used to get the sms posts by schedule datetime
     * @param string $smsId : sms id
     * @param string $sched_dateTime : input datetime
     * @return array $result : This is result
     */
    function smsPostsGetBySchedDtm($smsId, $schedDateTime){
        $this->db->select('id, scheduleDtm');
        $this->db->from(self::$tbl_sms_posts);
        $this->db->where('id !=', $smsId);
        $this->db->where('scheduleDtm', $schedDateTime);

        $query = $this->db->get();
        
        $result = $query->row();

        if(!empty($result))
            return $result;  
    }

    

}

  