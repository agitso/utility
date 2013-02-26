<?php
namespace Ag\Utility;

class TokenGenerator {

	/**
	 * @param int $length
	 * @return string
	 */
	public static function generateToken($length = 4) {
		$token = '';

		$characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));

		$max = count($characters) - 1;

		for ($i = 0; $i < $length; $i++) {
			$rand = mt_rand(0, $max);
			$token .= $characters[$rand];
		}

		return $token;
	}
}
?>