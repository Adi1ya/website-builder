<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuthController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('DatabaseModel');
	}

	public function register()
	{
		if ($this->input->method() !== 'post') {
			show_404();
		}

		$name             = trim($this->input->post('name', TRUE));
		$email            = trim($this->input->post('email', TRUE));
		$password         = $this->input->post('password');
		$confirmPassword  = $this->input->post('confirm_password');

		if (!$name || !$email || !$password || !$confirmPassword) {
			$this->session->set_flashdata('error', 'All fields are required');
			redirect('register');
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->session->set_flashdata('error', 'Invalid email address');
			redirect('register');
		}

		if ($password !== $confirmPassword) {
			$this->session->set_flashdata('error', 'Passwords do not match');
			redirect('register');
		}

		$user = $this->DatabaseModel->fetchTableData('users', ['email' => $email]);

		if (!empty($user)) {

			if ((int)$user[0]->is_deleted === 1) {
				$this->session->set_flashdata('error', 'User already exists and is deleted');
				redirect('register');
			}

			if ((int)$user[0]->status === 0) {
				$this->session->set_flashdata('error', 'User already exists but is inactive');
				redirect('register');
			}

			$this->session->set_flashdata('error', 'User already exists');
			redirect('register');
		}

		$insertData = [
			'name'       => $name,
			'email'      => $email,
			'password'   => password_hash($password, PASSWORD_BCRYPT),
			'status'     => 1,
			'is_deleted' => 0,
			'created_at' => date('Y-m-d H:i:s')
		];

		$userId = $this->DatabaseModel->insertIntoTable('users', $insertData);

		if (!$userId) {
			$this->session->set_flashdata('error', 'Registration failed. Please try again.');
			redirect('register');
		}

		$this->session->set_userdata([
			'user_id'    => $userId,
			'user_name'  => $name,
			'user_email' => $email,
			'logged_in'  => TRUE
		]);

		$this->session->set_flashdata('success', 'Registration successful');
		redirect('dashboard');
	}


	public function login() {}

	public function logout() {}
}
