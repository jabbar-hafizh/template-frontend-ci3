<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sasaran_model extends CI_Model
{
    public function get_sasaran_program($unit_kerja = null)
    {
        // Ambil data sasaran program
        $this->db->select('sp.id as sp_id, sp.nama as sp_nama');
        $this->db->from('sasaran_program sp');

        if (!empty($unit_kerja)) {
            $this->db->where('sp.unit_kerja', $unit_kerja);
        }

        $sasaran = $this->db->get()->result();

        foreach ($sasaran as &$sp) {
            // Ambil indikator untuk setiap sasaran program
            $this->db->select('ik.id, ik.nama');
            $this->db->from('indikator_kinerja ik');
            $this->db->where('ik.sasaran_program_id', $sp->sp_id);
            $indikator = $this->db->get()->result();

            foreach ($indikator as &$ik) {
                // Ambil data indikator (parameter input) untuk setiap indikator
                $this->db->select('di.id, di.nama');
                $this->db->from('indikator_data di');
                $this->db->where('di.indikator_kinerja_id', $ik->id);
                $this->db->where('di.periode IS NULL', null, false);
                $this->db->where('di.tahun IS NULL', null, false);
                $this->db->where('di.nilai IS NULL', null, false); // 🔹 Filter hanya nilai NULL
                $ik->data_indikator = $this->db->get()->result();
            }


            // Tambahkan indikator ke objek sasaran program
            $sp->indikator = $indikator;
        }

        return $sasaran;
    }

    public function get_with_indikator($tahun, $periode)
    {
        // Ambil semua sasaran program
        $sasaran = $this->db->get('sasaran_program')->result();

        foreach ($sasaran as &$sp) {
            // Ambil indikator kinerja per sasaran
            $sp->indikator = $this->db->where('sasaran_program_id', $sp->id)
                ->get('indikator_kinerja')
                ->result();

            foreach ($sp->indikator as &$ik) {
                // Cari hasil dari tabel indikator_hasil sesuai tahun & periode
                $hasil = $this->db->where('indikator_kinerja_id', $ik->id)
                    ->where('tahun', $tahun)
                    ->where('periode', $periode)
                    ->get('indikator_hasil')
                    ->row();

                // Persentase = hasil langsung (kalau null → 0)
                $ik->persentase = $hasil ? (float) $hasil->hasil : 0;

                // Ambil data indikator (detail)
                $ik->data_indikator = $this->db->where('indikator_kinerja_id', $ik->id)
                    ->get('indikator_data')
                    ->result();
            }
        }

        return $sasaran;
    }
}
