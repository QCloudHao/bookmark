<?php 
	/*让用户在PHPbookmark系统中注册*/
	require_once('bookmark_fns.php');
	do_html_header('User Registration');
	display_registration_form();
	do_html_footer();
 ?>