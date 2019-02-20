<?php 
/**
***将用户密码重置为一个随机值并将新密码发送到用户的邮箱
**/
	require_once('bookmark_fns.php');
	do_html_header('Resetting password');
	$username=$_POST['username'];
	try{
		$password=reset_password($username);
		notify_password($username,$password);
		echo 'Your new password has been emailed to you.<br />';
	}
	catch(Exception $e){
		echo 'Your password could not be reset - please try again later.';
	}
	do_html_URL('login.php','Login');
	do_html_footer();
 ?>