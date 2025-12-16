<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuthController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('DatabaseModel');
		$this->load->library('form_validation');
	}

	public function register()
	{
		if ($this->input->method(TRUE) !== 'POST') {
			show_404();
		}

		$this->form_validation->set_rules('name', 'Name', 'required|trim');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'required');
		$this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('register');
			return;
		}

		$name = $this->input->post('name', TRUE);
		$email = $this->input->post('email', TRUE);
		$password = $this->input->post('password');

		$user = $this->DatabaseModel->fetchTableData('users', ['email' => $email]);

		if (!empty($user)) {
			$existing = $user[0];
			if ((int)$existing->is_deleted === 1) {
				$this->session->set_flashdata('error', 'User already exists and is deleted');
				redirect('register');
				return;
			}

			if ((int)$existing->status === 0) {
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

		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('login');
			return;
		}

		$email = $this->input->post('email', TRUE);
		$password = $this->input->post('password');

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

		// Security: regenerate session ID after login
		$this->session->sess_regenerate(TRUE);

		$this->session->set_flashdata('success', 'Login successful');
		redirect('dashboard');
	}

	public function logout()
	{
		if (!$this->session->userdata('logged_in')) {
			show_404();
		}

		$this->session->sess_regenerate(TRUE); // invalidate session ID
		$this->session->sess_destroy();

		$this->session->set_flashdata('success', 'You have been logged out successfully');
		redirect('login');
	}
}
