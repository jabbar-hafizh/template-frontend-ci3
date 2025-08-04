<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserModel extends CI_Model {

  public function getListUser() {
      return $this->db->get('users')->result();
  }

 

}
