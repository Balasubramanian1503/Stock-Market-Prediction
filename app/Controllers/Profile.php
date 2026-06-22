<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LogModel;

class Profile extends BaseController
{
    protected $userModel;
    protected $logModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->logModel = new LogModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $userId = session()->get('userId');
        $user = $this->userModel->find($userId);

        if (!$user) {
            return redirect()->to('/login')->with('error', 'User session invalid. Please log in.');
        }

        return view('profile', [
            'user' => $user,
            'title' => 'My Profile'
        ]);
    }

    public function update()
    {
        $userId = session()->get('userId');
        $user = $this->userModel->find($userId);

        if (!$user) {
            return redirect()->to('/login')->with('error', 'User session invalid. Please log in.');
        }

        // 1. Build validation rules dynamically
        $rules = [
            'name'  => 'required|min_length[3]|max_length[100]',
            'email' => "required|valid_email|is_unique[users.email,id,{$userId}]",
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $rules['password'] = 'required|min_length[6]';
            $rules['confirm_password'] = 'required|matches[password]';
        }

        if (!$this->validate($rules)) {
            return view('profile', [
                'user' => $user,
                'validation' => $this->validator,
                'title' => 'My Profile'
            ]);
        }

        // 2. Prepare database update fields
        $updateData = [
            'id'    => $userId,
            'name'  => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
        ];

        if (!empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // 3. Save updates
        if ($this->userModel->save($updateData)) {
            // Update active session variables
            session()->set([
                'name'  => $updateData['name'],
                'email' => $updateData['email']
            ]);

            // Save log action
            $this->logModel->save([
                'action'  => 'PROFILE_UPDATE',
                'details' => "Updated profile. Name: {$updateData['name']} | Email: {$updateData['email']}",
                'user_id' => $userId
            ]);

            return redirect()->to('/profile')->with('success', 'Profile updated successfully!');
        } else {
            return redirect()->to('/profile')->with('error', 'Failed to update profile. Please try again.');
        }
    }
}
