<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Laporan_model extends CI_Model
{
    public function getLaporanLengkap()
    {
        $this->db->select('dl.id, sp.unit_kerja, dl.periode, dl.tahun, dl.created_at, u.nama, dl.url');
        $this->db->from('dokumen_laporan dl');
        $this->db->join('sasaran_program sp', 'sp.id = dl.sasaran_program_id');
        $this->db->join('user u', 'u.id = dl.user_id');
        return $this->db->get()->result();
    }

}
