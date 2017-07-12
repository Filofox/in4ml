<?php

if( isset( $_GET[ 'uploader' ] ) && $_GET[ 'uploader' ]=='simple-ajax-uploader'){
	require('../js/lib/simple-ajax-uploader/extras/Uploader.php');

	function return_bytes($val) {
	    $val = trim($val);
	    $last = strtolower($val[strlen($val)-1]);
	    switch($last) {
	        // The 'G' modifier is available since PHP 5.1.0
	        case 'g':
	            $val *= 1024;
	        case 'm':
	            $val *= 1024;
	        case 'k':
	            $val *= 1024;
	    }

	    return $val;
	}


	$uploader = new FileUpload('uploadFile');
	// Get the lowest of the two values
	$max_size = min( return_bytes( ini_get( 'post_max_size' ) ), return_bytes( ini_get( 'upload_max_filesize' ) ) );

	 $uploader->sizeLimit = $max_size;

		$uid = substr( sha1( rand() ), 0, 6 ) . microtime(true);

		// Make new temp directory for this upload
		$temp_dir = sys_get_temp_dir();
		$temp_dir .= (substr($temp_dir, -1) == DIRECTORY_SEPARATOR) ? '' : '/';
		$target_dir = $temp_dir . 'in4ml_' . $uid . '/';
		mkdir( $temp_dir . 'in4ml_' . $uid, 0777, true );

	$result = $uploader->handleUpload($target_dir);
	if ($result) {
		rename( $target_dir . $uploader->getFileName(), $target_dir . $uploader->getFileName() . '.' . $uid );
		echo($uid);
	} else {
		echo('ERROR');
	}
	exit;
} elseif ( isset( $_FILES[ 'file' ] ) && count( $_FILES[ 'file' ] ) ){
	$uid = substr( sha1( rand() ), 0, 6 ) . microtime(true);

	// Make new temp directory for this upload
	$temp_dir = sys_get_temp_dir();
	$temp_dir .= (substr($temp_dir, -1) == DIRECTORY_SEPARATOR) ? '' : '/';
	$target_dir = $temp_dir . 'in4ml_' . $uid . '/';

	mkdir( $temp_dir . 'in4ml_' . $uid, 0777, true );

	// Move file to temp directory
	move_uploaded_file( $_FILES[ 'file' ][ 'tmp_name' ], $target_dir . $_FILES[ 'file' ][ 'name' ] . '.' . $uid );

	echo($uid);
} else {
	echo( 'ERROR' );
}
if ( !function_exists('sys_get_temp_dir')) {
	function sys_get_temp_dir() {
		if( $temp=getenv('TMP') )        return $temp;
		if( $temp=getenv('TEMP') )        return $temp;
		if( $temp=getenv('TMPDIR') )    return $temp;

		$temp=tempnam(__FILE__,'');

		if (file_exists($temp)) {
			unlink($temp);
			return dirname($temp);
		}
		return null;
	}
}
?>
