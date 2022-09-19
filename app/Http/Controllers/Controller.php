<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function doesUserExist($username, $secret)
	{
		$result = User::where(['username' => $username, 'secret_key' => $secret])->first();

		if ($result) {
			return $this->finalResponse(true, 'Authorized user!', NULL, $result);
		} else {
			return $this->finalResponse(false, 'Invalid / Unauthorized access!');
		}
	}

	public function finalResponse(bool $status = NULL, string $status_message = NULL, string $message = NULL, $data = NULL)
	{
		$status = (empty($status)) ? false : $status;
		$status_message = (empty($status_message)) ? 'Something went wrong!' : $status_message;
		$message = (empty($message)) ? '' : $message;

		if (!empty($data)) {
			return [
				'status' => $status,
				'status_message' => $status_message,
				'message' => $message,
				'data' => $data
			];
		} else {
			return [
				'status' => $status,
				'status_message' => $status_message,
				'message' => $message
			];
		}
	}
}
