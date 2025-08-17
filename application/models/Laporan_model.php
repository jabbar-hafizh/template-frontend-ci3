<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Laporan_model extends CI_Model
{
    public function getLaporanLengkap()
    {
        $this->db->select('dl.id, dl.periode, dl.tahun, dl.created_at, u.nama, dl.unit_kerja, dl.url');
        $this->db->from('dokumen_laporan dl');
        $this->db->join('user u', 'u.id = dl.user_id', 'left');
        return $this->db->get()->result();
    }


}
