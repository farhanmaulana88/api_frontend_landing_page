<?php
spl_autoload_register(function ($class_name) {
	$preg_match = preg_match('/^mikehaertl\\\wkhtmlto\\\/', $class_name);

	if (1 === $preg_match) {
		$class_name = preg_replace('/\\\/', '/', $class_name);
		$class_name = preg_replace('/^mikehaertl\\/wkhtmlto\\//', '', $class_name);
		require_once(__DIR__ . '/mikehaertl/phpwkhtmltopdf/src/' . $class_name . '.php');
		require_once(__DIR__ . '/mikehaertl/php-tmpfile/src/File.php');
		require_once(__DIR__ . '/mikehaertl/php-shellcommand/src/Command.php');

	}
});