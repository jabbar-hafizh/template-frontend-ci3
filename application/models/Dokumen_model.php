<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dokumen_model extends CI_Model
{
    public function insert($data)
    {
        return $this->db->insert('dokumen_laporan', $data);
    }
}
    