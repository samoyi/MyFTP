<?php   
	
	/* TODO: 
	 *  1. 非英文文件名会报错
	 *  2. 要自动创建文件目录
	 */
class MyFTP
{
	function upload($uploadDirectory, $callback=null)
	{
		$aUploadedFilesNames = $_FILES["userfile"]["name"];
		$aUploadedFilesTmpNames = $_FILES["userfile"]["tmp_name"];
		$aUploadedFilesTypes = $_FILES["userfile"]["type"];
		$aUploadedFilesSizes = $_FILES["userfile"]["size"];
		$aUploadedFilesErrorCodes = $_FILES["userfile"]["error"];

		function uploadEach( $sUploadedFileName, $sUploadedFileTmpName, $sUploadedFileType, $sUploadedFileSize, $nUploadedFileErrorCode, $uploadDirectory, $nFailedNumber, $callback=null )
		{
			// 上传错误处理	
			if( $nUploadedFileErrorCode > 0 )
			{
				switch( $nUploadedFileErrorCode )
				{
					case 1:
					{
						echo $sUploadedFileName . '文件大小超出范围';
						break;
					}
					case 2:
					{
						echo '文件总大小超出范围'; // TODO 这个和上面什么区别
						break;
					}
					case 3:
					{
						echo $sUploadedFileName . '只有部分成功上传';
						break;
					}
					case 4:
					{
						echo $sUploadedFileName . '没有上传';
						break;
					}
					case 6:
					{
						echo '上传' . $sUploadedFileName . '时没有指定保存文件临时目录';
						break;
					}
					case 7:
					{
						echo $sUploadedFileName . '写入磁盘失败';
						break;
					}
				}
				return false;
			}

			// 检查是否为上传文件，如果是移动到指定路径
			if( is_uploaded_file($sUploadedFileTmpName) )
			{
				/*
					move_uploaded_file 虽然也能实现is_uploaded_file的检查。
					但如果它返回false，则有可能是文件不是上传的，也有可能是无法移动到指定目录。
					所以这里要加上 is_uploaded_file 来两步判断。 
				*/			
				echo '<br />$sUploadedFileTmpName: ' . $sUploadedFileTmpName . '<br />';								   
				echo '<br />$uploadDirectory.$sUploadedFileName: ' . $uploadDirectory.$sUploadedFileName . '<br />';
				if( move_uploaded_file($sUploadedFileTmpName, $uploadDirectory.$sUploadedFileName) )
				{
					if( $callback )
					{
						$callback();
					}
					return true;
				}
				else
				{
					echo 'Could not move file to destination directory';
					return false;
				}
			}
			else
			{
				echo 'Problem: Possible file upload attack. Filename: ' . $sUploadedFileName;
			}
		}

		$nUploadedFilesNumber = count( $aUploadedFilesNames );	
		$nFailedNumber = $nUploadedFilesNumber; // 上传失败数初始为上传文件数，成功一个减一
		for( $i=0; $i<$nUploadedFilesNumber; $i++ )
		{
			if( uploadEach($aUploadedFilesNames[$i], $aUploadedFilesTmpNames[$i], $aUploadedFilesTypes[$i], $aUploadedFilesSizes[$i], $aUploadedFilesErrorCodes[$i], $uploadDirectory, $nFailedNumber, $callback=null ) )
			{
				$nFailedNumber--;
			}
				
		}
		if( !$nFailedNumber )
		{
			echo "全部上传成功";
		}
	}
}
?>