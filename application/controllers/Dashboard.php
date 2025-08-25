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

        // === Tambahan: ambil filter tahun & periode dari query string ===
        $tahun = $this->input->get('tahun') ?: date('Y');
        $periode = $this->input->get('periode') ?: 'Tahunan';
        // =================================================================

        // Ambil data sasaran program sesuai unit kerja
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
                // Ambil hasil indikator
                $hasil = $this->db
                    ->select('hasil')
                    ->where('indikator_kinerja_id', $ik->id)
                    ->where('tahun', $tahun)
                    ->where('periode', $periode)
                    ->get('indikator_hasil')
                    ->row();

                // Ambil target + tipe_target dari indikator_kinerja
                $targetData = $this->db
                    ->select('target, tipe_target') // ganti jadi tipe_target
                    ->where('id', $ik->id)
                    ->get('indikator_kinerja')
                    ->row();

                $hasilValue = $hasil ? (float) $hasil->hasil : 0;
                $targetValue = ($targetData && $targetData->target > 0) ? (float) $targetData->target : 1;
                $tipeTarget = $targetData ? $targetData->tipe_target : 'jumlah';

                // Hitung persentase tergantung tipe_target
                if ($tipeTarget === 'persentase') {
                    // hasil sudah berupa %
                    $ik->persentase = $hasilValue;
                } else {
                    // tipe jumlah → hasil ÷ target × 100 (dibatasi max 100%)
                    $ik->persentase = ($hasilValue / $targetValue) * 100;
                }

                // Simpan juga nilai asli & target untuk ditampilkan
                $ik->hasil = $hasilValue;
                $ik->target = $targetValue;
                $ik->tipe_target = $tipeTarget;

                // Ambil indikator_data
                $master_data = $this->db
                    ->select('id, nama')
                    ->where('indikator_kinerja_id', $ik->id)
                    ->group_by('nama')
                    ->get('indikator_data')
                    ->result();

                $data_indikator = [];
                foreach ($master_data as $md) {
                    $nilai = $this->db
                        ->select('nilai')
                        ->where('indikator_kinerja_id', $ik->id)
                        ->where('nama', $md->nama)
                        ->where('tahun', $tahun)
                        ->where('periode', $periode)
                        ->get('indikator_data')
                        ->row();

                    $data_indikator[] = (object) [
                        'id' => $md->id,
                        'nama' => $md->nama,
                        'nilai' => $nilai ? $nilai->nilai : null
                    ];
                }

                $ik->data_indikator = $data_indikator;
            }


            $sp->indikator = $indikator;
        }

        $data = [
            'title' => 'SI-MONEV',
            'unit_kerja' => $unit_kerja,
            'unit_kerja_list' => $unit_kerja_list,
            'sasaran_program' => $sasaran_program,
            'role' => $role,
            // kirim juga tahun & periode ke view agar dropdown bisa "selected"
            'tahun' => $tahun,
            'periode' => $periode
        ];



        // Layout
        $this->load->view('layout/lay_header', $data);
        $this->load->view('layout/lay_nav', $data);
        $this->load->view('dashboard_kinerja', $data);
        $this->load->view('layout/lay_footer');
    }
}
