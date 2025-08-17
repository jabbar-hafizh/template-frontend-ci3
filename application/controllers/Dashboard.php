<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function index()
    {
        // Ambil role dari session
        $role = $this->session->userdata('role');

        // Kalau belum login atau bukan admin → jadikan guest
        if (!$role) {
            $role = 'guest';
            $this->session->set_userdata('role', 'guest');
        }

        // Kalau role admin tapi session hilang (logout) → anggap guest
        if ($role !== 'admin') {
            $role = 'guest';
        }

        // Ambil semua unit kerja unik
        $unit_kerja_list = $this->db
            ->select('unit_kerja')
            ->group_by('unit_kerja')
            ->get('sasaran_program')
            ->result();

        // Ambil unit kerja dari query string, default "Kepala Badan"
        $unit_kerja = $this->input->get('unit_kerja') ?: 'Kepala Badan';

        // Ambil data sasaran program
        $sasaran_program = $this->db
            ->select('id, nama, unit_kerja')
            ->where('unit_kerja', $unit_kerja)
            ->get('sasaran_program')
            ->result();

        foreach ($sasaran_program as $sp) {
            $indikator = $this->db
                ->select('id, nama, tipe_target, target')
                ->where('sasaran_program_id', $sp->id)
                ->get('indikator_kinerja')
                ->result();

            foreach ($indikator as $ik) {
                $data_indikator = $this->db
                    ->select('id, nama, nilai')
                    ->where('indikator_kinerja_id', $ik->id)
                    ->get('indikator_data')
                    ->result();

                $total_nilai = 0;
                $total_hasil = 0;

                foreach ($data_indikator as $data) {
                    $total_nilai += $data->nilai;
                    $total_hasil += $data->nilai;
                }

                $ik->persentase = ($total_hasil > 0)
                    ? round(($total_nilai / $total_hasil) * 100, 2)
                    : 0;

                $ik->data_indikator = $data_indikator;
            }

            $sp->indikator = $indikator;
        }

        $data = [
            'title' => 'SI-MONEV',
            'unit_kerja' => $unit_kerja,
            'unit_kerja_list' => $unit_kerja_list,
            'sasaran_program' => $sasaran_program,
            'role' => $role
        ];

        // Layout
        $this->load->view('layout/lay_header', $data);
        $this->load->view('layout/lay_nav', $data);
        $this->load->view('dashboard_kinerja', $data);
        $this->load->view('layout/lay_footer');
    }
}
