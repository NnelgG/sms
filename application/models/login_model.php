<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Login_model extends CI_Model{
    
    /**
     * Check the login credentials of the user
     * @param string $email : This is email of the user
     * @param string $password : This is encrypted password of the user
     */
    function loginMe($email, $password){
        $this->db->select('BaseTbl.userId, BaseTbl.password, BaseTbl.name, BaseTbl.roleId, Roles.role');
        $this->db->from('scheduler_users as BaseTbl');
        $this->db->join('scheduler_roles as Roles','Roles.roleId = BaseTbl.roleId');
        $this->db->where('BaseTbl.email', $email);
        $this->db->where('BaseTbl.isDeleted', 0);

        $query = $this->db->get();
        
        $user = $query->result();   //get user from query result
        
        //is user not empty?
        if(!empty($user)){
            $this->load->library('password_library');   //req to load

            //is password matched?
            if($this->password_library->validate_password($password, $user[0]->password)){
                return $user;
            } else {
                return array();
            }
        } else {
            return array();
        }
    }

    //check email exist
    function checkEmailExist($email){
        $this->db->select('userId');
        $this->db->where('email', $email);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get('scheduler_users');

        if ($query->num_rows() > 0){
            return true;
        } else {
            return false;
        }
    }

    /**
     * insert reset password data
     * @param {array} $data : reset password data
     * @return {boolean} $result : TRUE/FALSE
     */
    function resetPasswordUser($data){
        $result = $this->db->insert('scheduler_reset_password', $data);

        if($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * get customer information by email-id for forget password email
     * @param string $email : Email id of customer
     * @return object $result : Information of customer
     */
    function getCustomerInfoByEmail($email){
        $this->db->select('userId, email, name');
        $this->db->from('scheduler_users');
        $this->db->where('isDeleted', 0);
        $this->db->where('email', $email);
        
        $query = $this->db->get();

        return $query->result();
    }

    /**
     * check correct activation details for forget password.
     * @param string $email : Email id of user
     * @param string $activation_id : This is activation string
     */
    function checkActivationDetails($email, $activation_id){
        $this->db->select('id');
        $this->db->from('scheduler_reset_password');
        $this->db->where('email', $email);
        $this->db->where('activation_id', $activation_id);
        
        $query = $this->db->get();
        
        return $query->num_rows;
    }

    // create new password by reset link
    function createPasswordUser($email, $password){
        $this->db->where('email', $email);
        $this->db->where('isDeleted', 0);
        $this->db->update('scheduler_users', array('password'=>getHashedPassword($password)));
        $this->db->delete('scheduler_reset_password', array('email'=>$email));
    }
}

?>