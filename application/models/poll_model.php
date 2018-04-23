<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Poll_model extends CI_Model{
    public static $tbl_sms_posts = 'sms_posts';

    /**
     * Used to get the sms post/s listing
     * @return array $result : This is result
     */
    function smsPostListing($nowDtm){
        $this->db->select('id, createdBy, createdDtm, postTo, scheduleDtm, postedDtm, message, link, image, video, placeId, privacy');
        $this->db->from(self::$tbl_sms_posts);

        $this->db->where('scheduleDtm', $nowDtm);
        $this->db->where('postedDtm IS NULL');

        $query = $this->db->get();
        
        $result = $query->result();        
        
        return $result;
    }

    /**
     * Used to update fb scheduled post/s
     * Set postedDtm equals to nowDtm
     */
    function smsPostUpdatePostedDtm($smsId, $nowDtm){
        $data = array('postedDtm' => $nowDtm);

        $this->db->where('id', $smsId);
        $this->db->update(self::$tbl_sms_posts, $data);
    }

}

  