<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form', 'url']);
    }

    public function login()
    {
        // Redirect if already logged in
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login');
    }

    public function loginSubmit()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return view('auth/login', [
                'validation' => $this->validator
            ]);
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->userModel->where('email', $email)->first();

        if ($user) {
            if ($user['status'] !== 'active') {
                return redirect()->to('/login')->with('error', 'Your account has been deactivated. Please contact support.');
            }

            if (password_verify($password, $user['password'])) {
                // Set session variables
                session()->set([
                    'userId'     => $user['id'],
                    'name'       => $user['name'],
                    'email'      => $user['email'],
                    'role'       => $user['role'],
                    'isLoggedIn' => true
                ]);

                // Record log
                $logModel = new \App\Models\LogModel();
                $logModel->save([
                    'action'  => 'LOGIN',
                    'details' => "User {$user['name']} ({$user['email']}) logged in successfully.",
                    'user_id' => $user['id']
                ]);

                if ($user['role'] === 'admin') {
                    return redirect()->to('/admin');
                } else {
                    return redirect()->to('/dashboard');
                }
            }
        }

        return redirect()->to('/login')->with('error', 'Invalid Email or Password. Please try again.');
    }

    public function register()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/register');
    }

    public function registerSubmit()
    {
        $rules = [
            'name'             => 'required|min_length[3]|max_length[100]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return view('auth/register', [
                'validation' => $this->validator
            ]);
        }

        $userData = [
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'     => 'user',
            'status'   => 'active'
        ];

        if ($this->userModel->save($userData)) {
            // Log registration
            $newUserId = $this->userModel->getInsertID();
            $logModel = new \App\Models\LogModel();
            $logModel->save([
                'action'  => 'REGISTRATION',
                'details' => "New user registered: {$userData['name']} ({$userData['email']})",
                'user_id' => $newUserId
            ]);

            return redirect()->to('/login')->with('success', 'Registration successful! Please login.');
        } else {
            return redirect()->to('/register')->with('error', 'Failed to register. Please try again.');
        }
    }

    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    public function forgotPasswordSubmit()
    {
        $rules = [
            'email' => 'required|valid_email'
        ];

        if (!$this->validate($rules)) {
            return view('auth/forgot_password', [
                'validation' => $this->validator
            ]);
        }

        $email = $this->request->getPost('email');
        $user = $this->userModel->where('email', $email)->first();

        // Simulate sending a recovery email for demonstration
        if ($user) {
            return redirect()->to('/forgot-password')->with('success', 'A password recovery link has been sent to ' . esc($email) . ' (Simulated for demonstration!).');
        } else {
            return redirect()->to('/forgot-password')->with('error', 'No account found with this email address.');
        }
    }

    public function logout()
    {
        // Log logout
        if (session()->get('isLoggedIn')) {
            $logModel = new \App\Models\LogModel();
            $logModel->save([
                'action'  => 'LOGOUT',
                'details' => "User " . session()->get('name') . " (" . session()->get('email') . ") logged out.",
                'user_id' => session()->get('userId')
            ]);
        }

        session()->destroy();
        return redirect()->to('/login')->with('success', 'Logged out successfully.');
    }
}
