<?php

namespace App\Controllers;

use App\Models\UserModel;


use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{

    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper('form');
    }
public function login()
{
    if ($this->request->getPost()) {

    $rules = [
    'username' => 'required|min_length[6]',
    'password' => 'required|min_length[7]|numeric',
];

if ($this->validate($rules)) {
	//code pengecekan data user
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        $dataUser = $this->userModel ->where(['username' => $username])->first();

if ($dataUser) {
	if (password_verify($password, $dataUser['password'])) {
                session()->set([
                    'username' => $dataUser['username'],
                    'email' => $dataUser['email'],
                    'role' => $dataUser['role'],
                    'login_time' => date('Y-m-d H:i:s'),
                    'isLoggedIn' => TRUE
                ]);

                return redirect()->to(base_url('/'));
            } else {
                session()->setFlashdata('failed', 'Username & Password Salah');
                return redirect()->back();
            }
        } else {
            session()->setFlashdata('failed', 'Username Tidak Ditemukan');
            return redirect()->back();
        }
    } else {
    session()->setFlashdata('failed', $this->validator->listErrors());
    return redirect()->back();
}
        } else {
        return view('v_login');
    }
}public function logout()
{
    session()->destroy();
    return redirect()->to('login');
}
}
