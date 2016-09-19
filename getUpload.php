<pre><?php   
	
require "MyFTP.class.php";
$MyFTP = new MyFTP();

$directory = 'upload/';
//$MyFTP->upload($uploadDirectory);

echo $MyFTP->moveFile( $directory, "test22.txt" , "upload/further/");




?></pre>