<?php

	if (php_sapi_name() != 'cli') {
		die('This application must be run on the command line.');
	}

	require_once __DIR__.'/config.php';

	while(true) {
		echo 'cmd: # ';
		$handle = fopen ("php://stdin","r");
		$line = fgets($handle);
		switch(trim($line)) {
			case 'list':
				$nodes = json_decode(file_get_contents('.nodes.json'), true);
				print_r($nodes);
			break;
		}

		if(preg_match('#^exec ([^\:]+)\:(.+)$#', trim($line), $execarr)) {
			if(isset($nodes[$execarr[1]])) {
				file_put_contents(__DIR__.'/.rs-'.md5($execarr[1]), trim($execarr[2]));
				echo "> command sent, waiting for an answer...";
				while(true) {
					if(file_exists(__DIR__.'/.cr-'.md5($execarr[1]))) {
						unset($a); exec('cat '.escapeshellarg(__DIR__.'/.cr-'.md5($execarr[1])).' | openssl enc -d -aes-128-cbc -base64 -A -salt -pass pass:'.escapeshellarg($passphrase), $a);
						echo "\n< ".implode("\n", $a)."\n";
						unlink(__DIR__.'/.cr-'.md5($execarr[1]));
						break;
					} else {
						echo '.';
						sleep(1);
					}
				}
			}
		}
	}
