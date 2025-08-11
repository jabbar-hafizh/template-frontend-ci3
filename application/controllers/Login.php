<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('User_model');
    }

    public function index()
    {
        $data['title'] = 'SI-MONEV';
        $this->load->view('layout/lay_header', $data);
        $this->load->view('login_view');
        $this->load->view('layout/lay_footer');

    }

    public function login()
    {
        if ($this->input->post()) {
            $nip = $this->input->post('nip', true);
            $password = $this->input->post('password', true);

            if (empty($nip) || empty($password)) {
                $this->session->set_flashdata('error', 'NIP dan Password wajib diisi.');
                redirect('login');
                return; // ⬅ penting supaya tidak lanjut eksekusi
            }

            $user = $this->User_model->get_user_by_nip($nip);

            if ($user) {
                if (md5($password) === $user->password) {
                    $data = [
                        'user_id' => $user->id,
                        'nama' => $user->nama,
                        'role' => $user->role,
                        'logged_in' => true
                    ];
                    $this->session->set_userdata($data);
                    redirect('dashboard');
                    return;
                } else {
                    $this->session->set_flashdata('error', 'Password salah.');
                    redirect('login');
                    return;
                }
            } else {
                $this->session->set_flashdata('error', 'Akun tidak terdaftar.');
                redirect('login');
                return;
            }
        } else {
            redirect('login');
        }
    }


    public function logout()
    {
        $this->session->set_userdata('role', 'guest');
        // Hapus semua session
        $this->session->unset_userdata('role');
        $this->session->unset_userdata('logged_in');
        $this->session->sess_destroy();

        // Redirect ke halaman login
        redirect('login');
    }
}
