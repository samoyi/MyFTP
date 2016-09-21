<meta charset="utf-8">
﻿<pre><?php   

$host = '42.96.194.68';
$user = 'haoyunlai';
$password = 'LoApWqMjFQAs';

require "MyFTP.class.php";
$MyFTP = new MyFTP($host, $user, $password);

$sRemotefile = 'test/subscribeAutoPlayText.json';
$sLocalDir = 'download/';

$sRemoteDir = 'test/';
$sLocalFile = 'upload/upload.txt';

$MyFTP->uploadFile($sRemoteDir, $sLocalFile);



?></pre>