<?php   


class MyFTP
{

	// 确定一个目录下是否存在某文件
	// 第二个参数不含路径。如果含有路径也会被删除
	protected function isFileExisted($sDirectory, $sFileName)
	{
		$dir =  opendir($sDirectory);
		$sFileName = basename( $sFileName );
		while( false !== ($file=readdir($dir)))
		{
			if( $sFileName === $file )
			{
				closedir($dir);
				return true;
			}
		}
		closedir($dir);
		return false;
	}



	// 上传一个或多个文件
	public function upload($uploadDirectory)
	{
		$aUploadedFilesNames = $_FILES["userfile"]["name"];
		$aUploadedFilesTmpNames = $_FILES["userfile"]["tmp_name"];
		$aUploadedFilesTypes = $_FILES["userfile"]["type"];
		$aUploadedFilesSizes = $_FILES["userfile"]["size"];
		$aUploadedFilesErrorCodes = $_FILES["userfile"]["error"];

		function uploadEach( $sUploadedFileName, $sUploadedFileTmpName, $sUploadedFileType, $sUploadedFileSize, $nUploadedFileErrorCode, $uploadDirectory, $nFailedNumber )
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
				if( move_uploaded_file($sUploadedFileTmpName, $uploadDirectory.basename($sUploadedFileName) ))
				{
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
			if( uploadEach($aUploadedFilesNames[$i], $aUploadedFilesTmpNames[$i], $aUploadedFilesTypes[$i], $aUploadedFilesSizes[$i], $aUploadedFilesErrorCodes[$i], $uploadDirectory, $nFailedNumber ) )
			{
				$nFailedNumber--;
			}
				
		}
		if( !$nFailedNumber )
		{
			echo "全部上传成功";
		}
	}

	// 读取某个目录。
	// 第二个可选参数为不显示的文件名（包括文件夹）的名称数组
	// 返回所有文件组成的数组，包括 .（当前目录）和..（上一级）
	public function browseDirectory( $sDirectory, $aPrivateFiles=[] )
	{
		$dir =  opendir($sDirectory);
		while( false !== ($file=readdir($dir)))
		{
			if( !in_array($file, $aPrivateFiles) )
			{
				$aFiles[] = $file;	
			}
		}
		closedir($dir);
		return $aFiles;
	}

	// 显示文件信息
	// windows下不支持或者不可靠的支持这里的一些函数，包括posix_getpwuid() fileowner() filegroup()
	public function displayFileInfo( $sDirectory, $filename )
	{
		$basename = basename( $filename ); // strip off directory information for security
		$file = $sDirectory . $basename; 

		echo '<h1>Details of file: ' . $basename . '</h1>';
		echo '<h2>File data</h2>';
		echo 'File last accessed: ' . date('j F Y H:i', fileatime($file)) . '<br />';
		echo 'File last modified: ' . date('j F Y H:i', filemtime($file)) . '<br />';

		$user = posix_getpwuid( fileowner($file) );
		echo 'File owner: ' . $user['name'] . '<br />';

		$group = posix_getgrgid( filegroup($file) );
		echo 'File group: ' . $group['name'] . '<br />';

		echo 'File permissions: ' . decoct( fileperms($file) ) . '<br />'; // 转化为UNIX中使用的八进制格式
		echo 'File type: ' . filetype($file) . '<br />';
		echo 'File size: ' . filesize($file) . 'bytes<br />';

		echo '<h2>File tests</h2>';

		echo 'is_dir: ' . ( is_dir($file) ? 'true' : 'false' ) . '<br />';
		echo 'is_executable: ' . ( is_executable($file) ? 'true' : 'false' ) . '<br />';
		echo 'is_file: ' . ( is_file($file) ? 'true' : 'false' ) . '<br />';
		echo 'is_link: ' . ( is_link($file) ? 'true' : 'false' ) . '<br />';
		echo 'is_readable: ' . ( is_readable($file) ? 'true' : 'false' ) . '<br />';
		echo 'is_writable: ' . ( is_writable($file) ? 'true' : 'false' ) . '<br />';
	}

	// 创建文件 
	// 文件名应是basename
	public function createNewFile($sDirectory, $sFileName)
	{
		if( $this->isFileExisted($sDirectory, $sFileName) )
		{
			echo '创建文件失败。文件已存在。';
			return false;
		}
		else
		{
			return touch($sDirectory .basename($sFileName) );
		}
	}

	// 删除文件
	// 文件名应是basename
	public function deleteFile($sDirectory, $sFileName)
	{
		if( !$this->isFileExisted($sDirectory, $sFileName) )
		{
			echo '删除文件失败。文件不存在。';
			return false;
		}
		else
		{
			return unlink($sDirectory . basename($sFileName) );
		}
	}

	// 复制文件 
	// 两个文件名应是basename
	public function copyFile($sDirectory, $sFileName, $sDestination)
	{
		$sFileName = basename( $sFileName ); 
		$sDestination = basename( $sDestination );
		if( !$this->isFileExisted($sDirectory, $sFileName) )
		{
			echo '复制文件失败。文件不存在。';
			return false;
		}
		elseif( $sDirectory.$sFileName === $sDestination )
		{
			echo '复制文件失败。不能在同一目录下创建同名文件。';
			return false;
		}
		else
		{
			return copy($sDirectory . $sFileName, $sDirectory . $sDestination);
		}
	}

	// 重命名文件
	// 文件名应是basename
	public function renameFile($sDirectory, $sFileName, $sNewName)
	{
		if( !$this->isFileExisted($sDirectory, $sFileName) )
		{
			echo '重命名文件失败。文件不存在。';
			return false;
		}
		else
		{
			return rename($sDirectory . $sFileName, $sDirectory . basename($sNewName) );
		}
	}

	// 移动文件
	// 文件名应是basename
	public function moveFile($sDirectory, $sFileName, $sNewDirectory)
	{
		if( !$this->isFileExisted($sDirectory, $sFileName) )
		{
			echo '移动文件失败。文件不存在。';
			return false;
		}
		else
		{
			return rename($sDirectory . $sFileName, $sNewDirectory . basename($sFileName) );
		}
	}
}
?>