<pre><?php   
	
require "MyFTP.class.php";
$MyFTP = new MyFTP();

$directory = 'upload/';
//$MyFTP->upload($uploadDirectory);


print_r( $MyFTP->browseDirectory($directory) );




?></pre>