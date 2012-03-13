#!/usr/bin/php
<?php 

/**
 * TracWiki2DokuWiki: rename files
 * (c) Oliver Kopp. Free to use, but use at your own risk.
 *
 * first copy the attached files from your old trac instance to your dokuwiki instance
 *   find /trac/attachments/wiki -type f -exec cp -a \{\} /var/www/dokuwiki/data/media \;
 * (The conversion does NOT treat sub pages correctly. All are put in the same dir)
 *
 * Usage: ./renamefiles.php in dokuwiki/data/media
 */
 
require_once('tracwiki2dokuwiki.php');

$sourceDir = ".";
$handle = opendir($sourceDir) or exit("$sourceDir does not exist.\n");

while (false !== ($file = readdir($handle))) {
  if(!is_dir($file)) {
    $newFn = TracWiki2DokuWiki::tracFileName2DokuWikiFileName($file);
    if ($file != $newFn) {
      print "$file -> $newFn: ";
        if (rename($file, $newFn)) {
          print("success\n");
      } else {
        print("failed\n");
      }
    }
  }
}
?>
