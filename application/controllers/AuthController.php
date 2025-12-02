<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuthController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	// register->login->logout

	public function register()
	{
		$jsonData = file_get_contents('php://input');

		if ($request = $this->toolbox->isValidJSON($jsonData)) {
			$name = $request->name;
			$email = $request->email;
			$password = $request->password;

			if (empty($name) || empty($email) || empty($password)) {
				$response = ['status' => false, 'message' => 'Missing required parameters'];
				$this->toolbox->response($response);
				die();
			}

			$insertData = [
				'name' => $name,
				'email' => $email,
				'password' => password_hash($password, PASSWORD_DEFAULT)
			];

			if ($this->DatabaseModel->insertIntoTable('users', $insertData)) {
				$response = [
					'status' => true,
					'message' => 'Registration successful'
				];
			} else {
				$response = [
					'status' => false,
					'message' => 'Registration failed'
				];
			}
		} else {
			$response = [
				'status' => false,
				'message' => 'Invalid JSON'
			];
		}

		$this->toolbox->response($response);
	}

	public function login()
	{
		$jsonData = file_get_contents('php://input');

		if ($request = $this->toolbox->isValidJSON($jsonData)) {
			$email = $request->email;
			$password = $request->password;

			if (!empty($email) && !empty($password)) {

				$userData = $this->DatabaseModel->fetchTableData('users', ['email' => $email, 'isDeleted' => 0, 'status' => 1]);
				if (!empty($userData)) {
					$dbPassword = $userData[0]->password;

					if (password_verify($password, $dbPassword)) {
						$jwtData = [
							'userId' => $userData[0]->id,
							'email' => $userData[0]->email
						];
						$token = $this->jwt->encode($jwtData);
						$response = [
							'status' => true,
							'token' => $token,
							'message' => 'User logged in successfully'
						];
					} else {
						$response = [
							'status' => false,
							'message' => 'Invalid user password'
						];
					}
				} else {
					$response = [
						'status' => false,
						'message' => 'Invalid user email'
					];
				}
			} else {
				$response = [
					'status' => false,
					'message' => 'Required missing parameters'
				];
			}
		} else {
			$response = [
				'status' => false,
				'message' => 'Invalid JSON'
			];
		}

		$this->toolbox->response($response);
	}

	public function test() 
	{
		// $userData = $this->DatabaseModel->fetchTableData('users');

		// $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDgzOTM1MzEsImV4cCI6MTc0ODM5MzU5MSwiZGF0YSI6eyJ1c2VySWQiOiIyIiwiZW1haWwiOiJhZGl0eWF0ZXN0QGdtYWlsLmNvbSJ9fQ.B1hVNPS4OIb33UsIc43azhNbbknKstNi_zStkCvg_OM";

		// $t = $this->jwt->decode($token);

		// var_dump($t); die;

		$d = $this->jwt->verifyToken();
		var_dump($d); die;
	}
}
