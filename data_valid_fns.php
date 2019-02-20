<?php 
/**
***表单数据检验
**/
	//检验表单是否完全被填写
	function filled_out($form_vars){
		//test that each variable has a value
		foreach($form_vars as $key=>$value){
			if((!isset($key))||($value=='')){
				return false;
			}
		}
		return true;
	}
	//检验邮件地址是否有效
	function valid_email($address){
		if(@ereg('^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$',$address)){
			return true;
		}else{
			return false;
		}
	}
 ?>