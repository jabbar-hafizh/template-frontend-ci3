<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    private $table = 'user';

    // Fungsi untuk memeriksa login (pakai MD5 atau ganti ke password_hash/password_verify)
    public function cek_login($nip, $password)
    {
        $this->db->where('nip', $nip);
        $this->db->where('password', md5($password)); // ganti dengan password_verify untuk keamanan lebih
        $query = $this->db->get($this->table);

        return ($query->num_rows() == 1) ? $query->row() : false;
    }

    // Fungsi untuk mengambil user berdasarkan NIP
    public function get_user_by_nip($nip)
    {
        return $this->db->get_where($this->table, ['nip' => $nip])->row();
    }
}
