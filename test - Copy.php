<pre><?php   
	
print_r( $_FILES["userfile"] );	


define("UPLOAD_ERR_CODE", $_FILES["userfile"]["error"]);
define("UPLOAD_TMP_NAME", $_FILES["userfile"]["tmp_name"]);
define("UPLOAD_NAME", $_FILES["userfile"]["name"]);
define("DES_DIR", "./upload/");



// 上传错误处理	
if( UPLOAD_ERR_CODE > 0 )
{
	echo "传输错误：";
	switch( UPLOAD_ERR_CODE )
	{
		case 1:
		{
			echo '单个文件大小超出范围';
			break;
		}
		case 2:
		{
			echo '文件总大小超出范围';
			break;
		}
		case 3:
		{
			echo '只有部分文件成功上传';
			break;
		}
		case 4:
		{
			echo '没有上传任何文件';
			break;
		}
		case 6:
		{
			echo '没有指定保存文件临时目录';
			break;
		}
		case 7:
		{
			echo '文件写入磁盘失败';
			break;
		}
	}
	exit;
}


// 检查是否为上传文件，如果是移动到指定路径
if( is_uploaded_file(UPLOAD_TMP_NAME) )
{
	/*
		move_uploaded_file 虽然也能实现is_uploaded_file的检查。
		但如果它返回false，则有可能是文件不是上传的，也有可能是无法移动到指定目录。
		所以这里要加上 is_uploaded_file 来两步判断。 
	*/
	if( move_uploaded_file(UPLOAD_TMP_NAME, DES_DIR.UPLOAD_NAME) )
	{
		echo '<img src="' . DES_DIR . UPLOAD_NAME . '" />';
	}
	else
	{
		echo 'Could not move file to destination directory';
	}
}
else
{
	echo 'Problem: Possible file upload attack. Filename: ' . UPLOAD_NAME;
}



?></pre>