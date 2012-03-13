# TracWiki2DokuWiki 
 * (c) Alex N J, 2009. Free to use, but use at your own risk.
 * modified by Oliver Kopp, 2012

## Contained files
 * tracwiki2dokuwiki.php: class doing the conversion for one page
 * t2d.php: command line wrapper reading pages from "exported/" and writing to "converted/"
 * renamefiles.php: command line tool to rename trac attachments to fit dokuwiki's requirements
 
## Page conversion
1. create directory exported/
2. issue trac-admin /path/to/trac wiki dump  exported/
3. start ./t2d.php
4. The converted pages are in converted/
5. The console lists necessary mv commands (are obsolete if you use renamefiles.php)

 
## renamefiles.php
1. First copy the attached files from your old trac instance to your dokuwiki instance:
        find /trac/attachments/wiki -type f -exec cp -a \{\} /var/www/dokuwiki/data/media \;
   (This conversion does NOT treat sub pages correctly. All are put in the same dir)
2. Copy renamefiles.php and tracwiki2dokuwiki.php into dokuwiki/data/media
3. cd into dokuwiki/data/media and run ./renamefiles.php

 
## Issues
 * DokuWiki does not support file attachments to pages: All files have to 
   be linked in the page.