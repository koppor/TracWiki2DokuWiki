#!/usr/bin/php
<?php 

/**
 * TracWiki2DokuWiki: calling wrapper "t2d"
 * (c) Oliver Kopp, 2012. Free to use, but use at your own risk.
 *
 * PHP script to automate conversion of a Trac wiki page markup to equivalent DokuWiki format.
 * 
 * Converts all files from "export/" to "converted/"
 *
 * Usage: ./tracwiki2dokuwiki.php
 */
 
require_once('tracwiki2dokuwiki.php');


$o = new TracWiki2DokuWiki();

if (0) {
  $tm = file_get_contents('testpage');
  $res = $o->convert($tm);
  echo $res["page"];
  echo $res["mvCommands"];
  exit();
}

$sourceDir = 'exported/';
$targetDir = 'converted/';
// if conversion of Umlauts does not work, issue "locale-gen de_DE" as root
setlocale(LC_CTYPE, 'de_DE');

if (!is_dir($targetDir)) {
  exit("$targetDir does not exist.\n");
}

$mvCommands = "";

$handle = opendir($sourceDir) or exit("$sourceDir does not exist.\n");
// 'false !==' is necessary. Otherwise, a file named "0" would end the loop
while (false !== ($file = readdir($handle))) {
  if(!is_dir($file)) {
    $targetFileName = strtolower($file);
    $targetFileName = urldecode($targetFileName);
    $targetFileName = iconv("UTF-8","ASCII//TRANSLIT", $targetFileName);
    $targetFile = $targetDir . $targetFileName . ".txt";
    // dokuwiki uses subdirs for namespaces - TRAC separtes them using "/"
    // we have to create the directories
    $currentTargetDir = dirName($targetFile);
    if (!is_dir($currentTargetDir)) mkdir($currentTargetDir, 0777, true);
    $file = $sourceDir . $file;
    print("Converting $file -> $targetFile\n");
    $tm = file_get_contents($file);
    $res = $o->convert($tm);
    file_put_contents($targetFile, $res["page"]);
    $mvCommands = $mvCommands . $res["mvCommands"];
  }
}

echo "$mvCommands";

?>
