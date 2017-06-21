<?php 
$url='http://www.mum.cc/baidunews/';
$content=file_get_contents($url); 
if(file_exists($filename)) unlink($filename); 
$filename="../sitenews.html";
$fp = fopen($filename, 'w'); 
fwrite($fp, $content); 
?> 