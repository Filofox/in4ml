<?php
if( isset( $_FILES[ 'file' ] ) && count( $_FILES[ 'file' ] ) ){
	$uid = substr( sha1( rand() ), 0, 6 ) . microtime(true);
	
	// Make new temp directory for this upload
	$temp_dir = sys_get_temp_dir();
	
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