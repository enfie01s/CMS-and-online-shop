<?php

// You can't simply echo everything right away because we need to set some headers first!
$output = ''; // Here we buffer the JavaScript code we want to send to the browser.
$delimiter = ""; // for eye candy... code gets new lines
$output .= 'var tinyMCEImageList = new Array(';

$directory = $_GET['dir']; // Use your correct (relative!) path here
// Since TinyMCE3.x you need absolute image paths in the list...
$abspath = "http://www.lechameau.co.uk";//preg_replace('~^/?(.*)/[^/]+$~', '/$1', $_SERVER['SCRIPT_NAME']);

$absdir=str_replace("../../","",$directory);
if (is_dir($directory)) {
    $direc = opendir($directory);

    while ($file = readdir($direc)) {
        if (1) { // no hidden files / directories here...
             if (is_file("$directory/$file") && getimagesize("$directory/$file") != FALSE) {
                // We got ourselves a file! Make an array entry:
                $output .= $delimiter
                    . '["'
                    . utf8_encode($file)
                    . '", "'
                    . utf8_encode("$abspath/$absdir/$file")
                    . '"],';
            }
        }
    }
    $output = substr($output, 0, -1); // remove last comma from array item list (breaks some browsers)
    $output .= $delimiter;
    closedir($direc);
}

// Finish code: end of array definition. Now we have the JavaScript code ready!
$output .= ');';

// Make output a real JavaScript file!
header('Content-type: text/javascript'); // browser will now recognize the file as a valid JS file

// prevent browser from caching
header('pragma: no-cache');
header('expires: 0'); // i.e. contents have already expired

// Now we can send data to the browser because all headers have been set!
echo $output;

?>