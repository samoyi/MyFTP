<pre><?php   
	
require "MyFTP.class.php";
$MyFTP = new MyFTP();

$uploadDirectory = 'upload/';
$MyFTP->upload($uploadDirectory);

?></pre>