<?php
spl_autoload_register(function ($class_name) {
	$preg_match = preg_match('/^Psr\\\/', $class_name);

	if (1 === $preg_match) {
		require_once(__DIR__ . '/psr-16.php');
	} else if (false === $preg_match) {
		assert(false, 'Error preg_match().');
	}
});