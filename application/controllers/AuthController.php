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
		if ($this->input->method() !== 'POST') {
			show_404();
		}

		$name             = trim($this->input->post('name', TRUE));
		$email            = trim($this->input->post('email', TRUE));
		$password         = $this->input->post('password');
		$confirmPassword  = $this->input->post('confirm_password');

		if (!$name || !$email || !$password || !$confirmPassword) {
			$this->session->set_flashdata('error', 'All fields are required');
			redirect('register');
			return;
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->session->set_flashdata('error', 'Invalid email address');
			redirect('register');
			return;
		}

		if ($password !== $confirmPassword) {
			$this->session->set_flashdata('error', 'Passwords do not match');
			redirect('register');
			return;
		}

		$user = $this->DatabaseModel->fetchTableData('users', ['email' => $email]);

		if (!empty($user)) {

			if ((int)$user[0]->is_deleted === 1) {
				$this->session->set_flashdata('error', 'User already exists and is deleted');
				redirect('register');
				return;
			}

			if ((int)$user[0]->status === 0) {
				$this->session->set_flashdata('error', 'User already exists but is inactive');
				redirect('register');
				return;
			}

			$this->session->set_flashdata('error', 'User already exists');
			redirect('register');
			return;
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
			return;
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


	public function login()
	{
		if ($this->input->method(TRUE) !== 'POST') {
			show_404();
		}

		$email    = trim($this->input->post('email', TRUE));
		$password = $this->input->post('password', TRUE);

		if (empty($email)) {
			$this->session->set_flashdata('error', 'Email is required');
			redirect('login');
			return;
		}

		if (empty($password)) {
			$this->session->set_flashdata('error', 'Password is required');
			redirect('login');
			return;
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->session->set_flashdata('error', 'Invalid email address');
			redirect('login');
			return;
		}

		$user = $this->DatabaseModel->fetchTableData('users', ['email' => $email]);

		if (empty($user)) {
			$this->session->set_flashdata('error', 'Invalid email or password');
			redirect('login');
			return;
		}

		$user = $user[0];

		if ((int)$user->is_deleted === 1) {
			$this->session->set_flashdata('error', 'Your account has been deleted');
			redirect('login');
			return;
		}

		if ((int)$user->status === 0) {
			$this->session->set_flashdata('error', 'Your account is inactive');
			redirect('login');
			return;
		}

		if (!password_verify($password, $user->password)) {
			$this->session->set_flashdata('error', 'Invalid email or password');
			redirect('login');
			return;
		}

		$this->session->set_userdata([
			'user_id'    => $user->id,
			'user_name'  => $user->name,
			'user_email' => $user->email,
			'logged_in'  => TRUE
		]);

		// IMPORTANT SECURITY:
		// Regenerate session ID after authentication to prevent session fixation attacks.
		// Old session ID is destroyed and a new secure one is issued.
		$this->session->sess_regenerate(TRUE);

		$this->session->set_flashdata('success', 'Login successful');
		redirect('dashboard');
	}


	public function logout()
	{
		if (!$this->session->userdata('logged_in')) {
			show_404();
		}

		// Regenerate session ID to invalidate the current session
		// (prevents session reuse / fixation after logout)
		$this->session->sess_regenerate(TRUE);

		// Destroy all session data
		$this->session->sess_destroy();

		$this->session->set_flashdata('success', 'You have been logged out successfully');
		redirect('login');
	}
}
