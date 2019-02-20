<?php 
	require_once('db_fns.php');
	//从数据库中取回用户书签
	function get_user_urls($username){
		$conn=db_connect();
		$result=$conn->query("select bm_URL from bookmark where username='".$username."'");
		if(!$result){
			return false;
		}
		//create an array of the URLs
		$url_array=array();
		for($count=1;$row=$result->fetch_row();++$count){
			$url_array[$count]=$row[0];
		}
		return $url_array;
	}

	//将用户提交的新书签添加到数据库中
	function add_bm($new_url){
		echo "Attempting to add ".htmlspecialchars($new_url)."<br />";
		$valid_user=$_SESSION['valid_user'];
		$conn=db_connect();
		//check not a repeat bookmark
		$result=$conn->query("select * from bookmark where username='".$valid_user."' and bm_URL='".$new_url."'");
		if($result && ($result->num_rows>0)){
			throw new Exception('Bookmark already exists.');
		}

		//insert the new bookmark
		if(!$conn->query("insert into bookmark values('".$valid_user."','".$new_url."')")){
			throw new Exception('Bookmark could not be inserted.');
		}

		return true;
	}

	//从用户的书签列表中删除一个书签
	function delete_bm($user,$url){
		$conn=db_connect();
		//delete the bookmark
		if(!$conn->query("delete from bookmark where username='".$user."' and bm_URL='".$url."'")){
			throw new Exception('Bookmark could not be deleted');
		}
		return true;
	}

	//做出实际推荐
	function recommend_urls($valid_user,$popularity=1){
		// We will provide semi intelligent recomendations to people
  		// If they have an URL in common with other users, they may like
  		// other URLs that these people like
  		$conn=db_connect();
  		// find other matching users
  		// with an url the same as you
  		// as a simple way of excluding people's private pages, and
  		// increasing the chance of recommending appealing URLs, we
  		// specify a minimum popularity level
  		// if $popularity = 1, then more than one person must have
  		// an URL before we will recomend it
  		$query="select bm_URL from bookmark
  				where username in
  				(select distinct(b2.username)
  				from bookmark b1,bookmark b2
  				where b1.username='".$valid_user."'
  					and b1.username!=b2.username 
  					and b1.bm_URL=b2.bm_URL)
  					and bm_URL not in (select bm_URL from bookmark
  						where username='".$valid_user."')
  						group by bm_URL having count(bm_URL)>".$popularity;
  		if(!($result=$conn->query($query))){
  			throw new Exception('Could not find any bookmarks to recommend.');
  		}
  		if($result->num_rows==0){
  			throw new Exception('Could not find any bookmarks to recommend.');
  		}

  		$urls=array();
  		//build an array of the relevant urls
  		for($count=0;$row=$result->fetch_object();$count++){
  			$urls[$count]=$row->bm_URL;
  		}
  		return $urls;
	}
 ?>