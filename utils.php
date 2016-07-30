<?php

function _filterRegexp() {
	return '/^(引子|第.{1,15}章)/';
}

function _addEtag() {
    // always send headers
    $etag = 'bajdcc_cache_system';
    header("Etag: $etag"); 
    // exit if not modified
    if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
        header("HTTP/1.1 304 Not Modified");
        return false;
    }
	return true;
}

function getIP() {
	if (getenv('HTTP_CLIENT_IP')) {
		$ip = getenv('HTTP_CLIENT_IP');
	}
	elseif (getenv('HTTP_X_FORWARDED_FOR')) {
		$ip = getenv('HTTP_X_FORWARDED_FOR');
	}
	elseif (getenv('HTTP_X_FORWARDED')) {
		$ip = getenv('HTTP_X_FORWARDED');
	}
	elseif (getenv('HTTP_FORWARDED_FOR')) {
		$ip = getenv('HTTP_FORWARDED_FOR');
	}
	elseif (getenv('HTTP_FORWARDED')) {
		$ip = getenv('HTTP_FORWARDED');
	}
	else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}