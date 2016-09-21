<?php   


class MyFTP
{
	private $ftp_host;
	private $user;
	private $password;
	function __construct( $host, $user, $password )
	{
		$this->ftp_host = $host;
		$this->ftp_user = $user;
		$this->ftp_password = $password;
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

		if( file_exists($sDirectory .  $sFileName) )
		{
			echo '创建文件失败。文件已存在。';
			return false;
		}
		else
		{
			return touch($sDirectory . $sFileName );
		}
	}

	// 删除文件
	// 文件名应是basename
	public function deleteFile($sDirectory, $sFileName)
	{
		if( !file_exists($sDirectory . $sFileName) )
		{
			echo '删除文件失败。文件不存在。';
			return false;
		}
		else
		{
			return unlink($sDirectory . $sFileName );
		}
	}

	// 复制文件 
	// 两个文件名应是basename
	// $sDestination 不含文件名。以 / 结尾
	public function copyFile($sDirectory, $sFileName, $sDestination)
	{
		if( !file_exists( $sDirectory . $sFileName ) )
		{
			echo '复制文件失败。文件不存在。';
			return false;
		}
		elseif( $sDirectory === $sDestination )
		{
			echo '复制文件失败。不能在同一目录下创建同名文件。';
			return false;
		}
		else
		{
			return copy($sDirectory . $sFileName, $sDestination . $sFileName);
		}
	}

	// 重命名文件
	// 文件名应是basename
	public function renameFile($sDirectory, $sFileName, $sNewName)
	{
		if( !file_exists( $sDirectory . $sFileName ) )
		{
			echo '重命名文件失败。文件不存在。';
			return false;
		}
		elseif( file_exists( $sDirectory . $sNewName ) )
		{
			echo '重命名文件失败。当前目录存在同名文件。';
			return false;
		}
		else
		{
			return rename($sDirectory . $sFileName, $sDirectory . $sNewName );
		}
	}

	// 移动文件
	// 文件名应是basename
	// $sNewDirectory 不含文件名。以 / 结尾
	public function moveFile($sDirectory, $sFileName, $sNewDirectory)
	{
		if( !file_exists($sDirectory . $sFileName) )
		{
			echo '移动文件失败。文件不存在。';
			return false;
		}
		elseif( file_exists( $sNewDirectory . $sFileName ) )
		{
			echo '移动文件失败。目标目录存在同名文件。';
			return false;
		}
		else
		{
			return rename($sDirectory . $sFileName, $sNewDirectory . $sFileName );
		}
	}


	// 从ftp服务器下载文件
	// $sRemotefile 包含路径和文件名
	// $sLocalDir 只包含路径，不包含文件名
	/*public function downloadFile($sRemotefile, $sLocalDir)
	{
		// connect to host
		$conn = ftp_connect( $this->ftp_host );
		if( !$conn )
		{
			echo 'Error : Could not connect to ftp server';
			exit;
		}
		echo 'Connect to ' . $this->ftp_host . '<br />';


		// log in to host
		$result = @ftp_login($conn, $this->ftp_user, $this->ftp_password);
		if( !$result )
		{
			echo 'Error : Could not log on as ' . $this->ftp_user;
			ftp_close( $conn );
			exit;
		}
		echo 'Logged in as ' . $this->ftp_user . '<br />';


		// check file times to see if an update is required

		echo 'Checking file time...<br />';
		$sLocalFile = $sLocalDir . basename($sRemotefile);
		if( file_exists( $sLocalFile ))
		{
			$localtime = filemtime( $sLocalFile );
			echo 'Local file last updated ';
			echo date('G:i j-M-Y', $localtime);
			echo '<br />';
		}
		else
		{
			$localtime = 0;
		}

		$remotetime = ftp_mdtm($conn, $sRemotefile);
		if( !($remotetime >= 0 )) // This dosen't mean the file is not there, server may not support mod time
		{
			echo 'could not access remote file time. <br />';
			$remotetime = $localtime + 1; // make sure of an update
		}
		else
		{
			echo 'Remote file last updated ';
			echo date('G:i j-M-Y', $remotetime);
			echo '<br />';
		}

		if( !($remotetime > $localtime ))
		{
			echo 'Local copy is up to date.<br />';
			exit;
		}


		// download file
		echo 'Gettig file from server ...<br />';
		if( !$success = ftp_get($conn, $sLocalFile, $sRemotefile, FTP_BINARY ))
		{
			echo 'Error : Could not download file';
			ftp_close( $conn );
			exit;
		}
		echo 'File download successfully';

		ftp_close( $conn );
	}*/

	// 向ftp服务器上传文件
	// $sRemoteDir 包含路径和文件名
	// $sLocalFile 只包含路径，不包含文件名
	public function uploadFile($sRemoteDir, $sLocalFile)
	{
		// connect to host
		$conn = ftp_connect( $this->ftp_host );
		if( !$conn )
		{
			echo 'Error : Could not connect to ftp server';
			exit;
		}
		//echo 'Connect to ' . $this->ftp_host . '<br />';


		// log in to host
		$result = @ftp_login($conn, $this->ftp_user, $this->ftp_password);
		if( !$result )
		{
			echo 'Error : Could not log on as ' . $this->ftp_user;
			ftp_close( $conn );
			exit;
		}
		//echo 'Logged in as ' . $this->ftp_user . '<br />';


		// check file times to see if an update is required
		echo 'Checking file time...<br />';
		if( file_exists( $sLocalFile ))
		{
			$localtime = filemtime( $sLocalFile );
			echo 'Local file last updated ';
			echo date('G:i j-M-Y', $localtime);
			echo '<br />';
		}
		else
		{
			echo 'Error : Local file dose not exsited';
			ftp_close( $conn );
			exit;
		}

		$sRemoteFile = $sRemoteDir . basename($sLocalFile);
		$remotetime = ftp_mdtm($conn, $sRemoteFile);
		if( !($remotetime >= 0 )) // This dosen't mean the file is not there, server may not support mod time
		{
			echo 'could not access remote file time. <br />';
			$remotetime = -1; // make sure of an update
		}
		else
		{
			echo 'Remote file last updated ';
			echo date('G:i j-M-Y', $remotetime);
			echo '<br />';
		}

		if( !($remotetime < $localtime ))
		{
			echo 'Remote copy is up to date.<br />';
			exit;
		}

		ftp_pasv ($conn , true );
		// upload file
		echo 'upload file to server ...<br />';
		echo $sRemoteFile . '<br />';
		echo $sLocalFile . '<br />';
		if( !$success = ftp_put($conn, $sRemoteFile, $sLocalFile, FTP_BINARY ))
		{
			echo 'Error : Could not upload file';
			ftp_close( $conn );
			exit;
		}
		echo 'File upload successfully';

		ftp_close( $conn );
	}
}
?>