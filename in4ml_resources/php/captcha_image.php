<?php

$in4ml_path = '../../in4ml/in4ml.class.php';

try{
	if( file_exists( $in4ml_path ) ){
		require_once( $in4ml_path );
		in4ml::Init( '../../in4ml_local/' );
		in4ml::ShowCaptchaImage();
	} else {
		throw new Exception( 'in4ml path not valid in captcha_image.php' );
	}
} catch( Exception $e ){
	echo( 'Unable to render captcha image' );
	echo( $e->getMessage() );
	error_log( $e->getMessage() );
}
?>