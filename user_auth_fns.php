<?php 
	require_once('db_fns.php');
	//将用户信息提交到数据库
	function register($username,$email,$password){
		//register new person with db
		//return true or error message

		//connect to db
		$conn=db_connect();
		//check if username is unique
		$result=$conn->query("select * from user where username='".$username."'");
		if(!$result){
			throw new Exception('Could not execute query');
		}
		if($result->num_rows>0){
			throw new Exception('That username is taken-go back and choose another one.');
		}

		//if ok,put in db
		$result=$conn->query("insert into user values('".$username."',sha1('".$password."'),'".$email."')");
		if(!$result){
			throw new Exception('Could not register you in database-please try again later.');
		}
		return true;
	}

	//将用户信息与数据库中保存的信息进行比较
	function login($username,$password){
		$conn=db_connect();
		//check if username is unique
		$result=$conn->query("select * from user where username='".$username."' and passwd=sha1('".$password."')");
		if(!$result){
			throw new Exception('Could not log you in.');
		}
		if($result->num_rows>0){
			return true;
		}else{
			throw new Exception('Could not log you in.');
		}
	}

	//检查用户是否有有效的会话
	function check_valid_user(){
		if(isset($_SESSION['valid_user'])){
			echo "Logged in as ".$_SESSION['valid_user'].".<br />";
		}else{
			//they are not logged in
			do_html_heading('Problem:');
			echo 'You are not logged in.<br />';
			do_html_url('login.php','Login');
			do_html_footer();
			exit;
		}
	}

	//更新数据库中的用户密码
	function change_password($username,$old_password,$new_password){
		// change password for username/old_password to new_password
		// return true or false

  		// if the old password is right
  		// change their password to new_password and return true
  		// else throw an exception
  		login($username,$old_password);
  		$conn=db_connect();
  		$result=$conn->query("update user set passwd=sha1('".$new_password."') where username='".$username."'");
  		if(!$result){
  			throw new Exception('Password could not be changed.');
  		}else{
  			return true;
  		}
	}

	//从字典中获取一个随机单词，以生成新密码
	function get_random_word($min_length,$max_length){
		// grab a random word from dictionary between the two lengths
		// and return it

   		// generate a random word
   		$word='';
   		$dictionary='F:/dictionary/American/2of12inf.txt';
   		$fp=@fopen($dictionary,'r');
   		if(!$fp){
   			return false;
   		}
   		$size=filesize($dictionary);

   		//go to a random location in dictionary
   		$rand_location=rand(0,$size);
   		fseek($fp,$rand_location);
   		//get the next whole word of the right length in the file
   		while((strlen($word)<$min_length)||(strlen($word)>$max_length)||(strstr($word,"'"))){
   			if(feof($fp)){
   				fseek($fp,0);//if at end,go to start
   			}
   			$word=fgets($fp,80);	//skip first word as it could be partial
   			$word=fgets($fp,80);	//the potential password
   		}
   		$word=trim($word);
   		return $word;
	}

	//将用户密码重置为随机值并将其发送到该用户的邮箱
	function reset_password($username){
		// set password for username to a random value
		// return the new password or false on failure
  		// get a random dictionary word b/w 6 and 13 chars in length
  		$new_password=get_random_word(6,13);

  		if($new_password==false){
  			throw new Exception('Could not generate new password.');
  		}

  		//add a number between 0 and 999 to it
  		//to make it a slightly better password
  		$rand_number=rand(0,999);
  		$new_password.=$rand_number;

  		// set user's password to this in database or return false
  		$conn = db_connect();
  		$result=$conn->query("update user set passwd=sha1('".$new_password."') where username='".$username."'");
  		if(!$result){
  			throw new Exception('Could not change password.');
  		}else{
  			return $new_password;
  		}
	}

	//将新密码以电子邮件方式发送给用户
	function notify_password($username,$password){
		// notify the user that their password has been changed
		$conn=db_connect();
		$result=$conn->query("select email from user where username='".$username."'");

		if(!$result){
			throw new Exception('Could not find email address.');
		}else if($result->num_rows==0){
			throw new Exception('Could not find email address.');
		}else{
			$row=$result->fetch_object();
			$email=$row->email;
			$from="From: support@phpbookmark \r\n";
			$mesg="Your PHPBookmark password has been changed to".$password."\r\n"
					."Please change it next time you log in.\r\n";
			if(mail($email,'PHPBookmark login information',$mesg,$from)){
				return true;
			}else{
				throw new Exception('Could not send email.');
			}
		}
	}
 ?>