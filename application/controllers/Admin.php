<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Load model Sasaran_model
        $this->load->model('Sasaran_model');
    }

    public function index()
    {
        $data = [
            'title' => 'E-MONEV',
            'sasaran_program' => $this->get_sasaran_program()
        ];

        $this->load->view('layout/lay_header', $data);
        $this->load->view('layout/lay_nav', $data);
        $this->load->view('admin', $data);
        $this->load->view('layout/lay_footer');
    }

    public function form_input($unit_kerja = null)
    {
        // Ambil dari query string jika parameter kosong
        if (empty($unit_kerja)) {
            $unit_kerja = $this->input->get('unit_kerja');
        }

        $unit_kerja = !empty($unit_kerja) ? urldecode($unit_kerja) : null;

        $data = [
            'title' => 'Admin ' . ($unit_kerja ?? ''),
            'unit_kerja' => $unit_kerja,
            'sasaran_program' => $this->get_sasaran_program($unit_kerja)
        ];

        $this->load->view('layout/lay_header', $data);
        $this->load->view('layout/lay_nav', $data);
        $this->load->view('admin', $data);
        $this->load->view('layout/lay_footer');
    }
    public function simpan_data()
    {
        // Load library dan helper yang dibutuhkan
        $this->load->library('upload');
        $this->load->database();

        $tahun_periode = $this->input->post('tahun_periode', true);
        $periode = $this->input->post('periode', true);
        $user_id = $this->session->userdata('user_id'); // pastikan sudah login

        // Mulai transaksi
        $this->db->trans_start();

        // 1️⃣ Upload file laporan (bisa multiple)
        $uploaded_files = [];
        // if (!empty($_FILES['file_pendukung']['name'][0])) {
        //     $files = $_FILES;
        //     $count = count($_FILES['file_pendukung']['name']);

        //     for ($i = 0; $i < $count; $i++) {
        //         $_FILES['file_pendukung']['name'] = $files['file_pendukung']['name'][$i];
        //         $_FILES['file_pendukung']['type'] = $files['file_pendukung']['type'][$i];
        //         $_FILES['file_pendukung']['tmp_name'] = $files['file_pendukung']['tmp_name'][$i];
        //         $_FILES['file_pendukung']['error'] = $files['file_pendukung']['error'][$i];
        //         $_FILES['file_pendukung']['size'] = $files['file_pendukung']['size'][$i];

        //         $config['upload_path'] = './uploads/laporan/';
        //         $config['allowed_types'] = 'jpg|jpeg|png|gif|pdf|zip|rar';
        //         $config['max_size'] = 5120; // 5MB
        //         $config['encrypt_name'] = true;

        //         $this->upload->initialize($config);

        //         if ($this->upload->do_upload('file_pendukung')) {
        //             $data_upload = $this->upload->data();
        //             $uploaded_files[] = $data_upload['file_name'];
        //         } else {
        //             // Jika gagal upload
        //             $this->session->set_flashdata('error', $this->upload->display_errors());
        //             redirect('dashboard'); // atau halaman form
        //         }
        //     }
        // }

        // 2️⃣ Simpan nilai indikator
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'indikator_') === 0) {
                $indikator_data_id = str_replace('indikator_', '', $key);
                $nilai = (float) $value;

                // Log: mulai cek data indikator
                log_message('debug', "Cek indikator_data id=$indikator_data_id, periode=$periode");

                // Cek apakah data indikator dengan id dan periode sudah ada
                $cek = $this->db
                    ->where('id', $indikator_data_id)
                    ->where('periode', $periode)
                    ->get('indikator_data')
                    ->row();

                if ($cek) {
                    // Log: update data indikator
                    log_message('debug', "Update indikator_data id=$indikator_data_id dengan nilai=$nilai dan periode=$periode");

                    // Data sudah ada, update nilai dan periode
                    $this->db->where('id', $indikator_data_id);
                    $this->db->update('indikator_data', [
                        'nilai' => $nilai,
                        'periode' => $periode
                    ]);
                    $indikator_kinerja_id = $cek->indikator_kinerja_id;
                } else {
                    // Log: insert data indikator baru
                    log_message('debug', "Insert indikator_data baru berdasarkan id asli $indikator_data_id dengan nilai=$nilai dan periode=$periode");

                    // Data belum ada, insert baru
                    // Ambil indikator_kinerja_id dulu dari data indikator yang asli (bukan filtered by periode)
                    $indikator_data_asli = $this->db->get_where('indikator_data', ['id' => $indikator_data_id])->row();

                    if ($indikator_data_asli) {
                        $indikator_kinerja_id = $indikator_data_asli->indikator_kinerja_id;

                        $this->db->insert('indikator_data', [
                            'nama' => $indikator_data_asli->nama,
                            'indikator_kinerja_id' => $indikator_kinerja_id,
                            'nilai' => $nilai,
                            'periode' => $periode
                        ]);
                    } else {
                        log_message('error', "Data indikator_data asli dengan id $indikator_data_id tidak ditemukan");
                    }
                }
            }
        }


        // Selesaikan transaksi
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        } else {
            $this->session->set_flashdata('success', 'Data berhasil disimpan.');
        }

        redirect('dashboard'); // atau halaman lain sesuai
    }

    // Tambahkan method ini untuk memanggil model
    private function get_sasaran_program($unit_kerja = null)
    {
        return $this->Sasaran_model->get_sasaran_program($unit_kerja);
    }
}
