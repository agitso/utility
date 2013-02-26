<?php
namespace Ag\Utility;

class HashCalculator {

	/**
	 * @param array $array
	 * @param string $salt
	 * @param string $algo
	 * @return string
	 */
	public static function hash($array, $salt = '', $algo = 'md5') {
		return hash($algo, implode('', array_values($array)).$salt);
	}
}
?>