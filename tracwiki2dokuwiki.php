<?php 
/**
 * TracWiki2DokuWiki
 * (c) Alex N J, 2009. Free to use, but use at your own risk.
 * modified by Oliver Kopp, 2012
 *
 * Originally from http://blog.alexnj.com/php/a-php-class-to-convert-trac-wiki-to-dokuwiki-format
 */
class TracWiki2DokuWiki {

  private $mvCommands;
  
  /**
   * takes $markup and returns an array with 
   *   * the conversion result and 
   *   * necessary mv commands for attachments
   */
  public function convert( $markup ) {
    $class = new ReflectionClass( 'TracWiki2DokuWiki' );
                
    $callableMethods = array();
    foreach( $class->getMethods() as $method ) {
      $callableMethods[] = $method->getName();
    }

    $page = "";
    $this->mvCommands = "";

    foreach(preg_split("/(\r?\n)/", $markup) as $line) {
      foreach( $callableMethods as $methodName ) {
        if( preg_match( '/^replace/', $methodName ) ) {
          $line = call_user_func_array(array( $this, $methodName ), array( $line ));
        }
      }
      $page = $page . "$line\n";
    }

    return array("page" => $page, "mvCommands" => $this->mvCommands);
  }

  /** functions starting with replace_ called in their order of appearance **/

  private function replace_table_heading( $line ) {
    // match manually bolded headings
    $line = preg_replace( "/\|\|[ ]*'''([^']*)'''/", "^ $1", $line, -1, $count);
    if ($count > 0) {
      $line = trim($line);
      if (strpos($line,"||")==strlen($line)-2)
        // if first occurence of "||" are the last two characters, they have to be replaced by "^"
        $line = substr($line, 0, -2) . "^";
    }
    // real trac headings: format: ||= Table Header =||=Header =||
    $line = preg_replace( "/\|\|=([^=])+=/", "^ $1", $line, -1, $count);
    if ($count > 0) {
      $line = trim($line);
      if (strpos($line,"||")==strlen($line)-2)
        // if first occurence of "||" are the last two characters, they can be removed
        $line = substr($line, 0, -2);
    }
    return $line;
    //$line =  preg_replace( "/\|\*\*/", "^", $line );
    //$line =  preg_replace( "/\*\*\|/", "^", $line );
    //return( preg_replace( "/[\*]*\^[\*]*/", "^", $line ) );
  }

  private function replace_table( $line ) {
    // insert spaces for empty cells
    $line = preg_replace("/\|\|\|\|/", "|| ||", $line);
    
    // real conversion
    return( preg_replace( "/\|\|/u", "|", $line ) );
  }

  private function replace_h1( $line ) {
    return( preg_replace( "/^= ([^=]+)=/mu", "====== $1======", $line ) );
  }
  
  private function replace_h2( $line ) {
    return( preg_replace( "/^== ([^=]+)==/mu", "===== $1=====", $line ) );
  }

  private function replace_h3( $line ) {
    return( preg_replace( "/^=== ([^\=]+)===/mu", "==== $1====", $line ) );
  }

  private function replace_h4( $line ) {
    return( preg_replace( "/^==== ([^=]+)====/mu", "== $1==", $line ) );
  }

  private function replace_h5( $line ) {
    return( preg_replace( "/^===== ([^=]+)=====/mu", "= $1=", $line ) );
  }

  private function replace_inline_code( $line ) {
    return( preg_replace( "/{{{([^}]+)}}}/u", "''$1''", $line ) );
  }

  private function replace_code_block( $line ) {
    $line =  preg_replace( "/{{{\s*/u", "<code>", $line );
    return( preg_replace( "/}}}\s*/u", "</code>\n", $line ) );
  }

  private function replace_bold_italic( $line ) {
    return( preg_replace( "/'''''([^']+)'''''/u", "**//$1//**", $line ) );
  }

  private function replace_bold( $line ) {
    return( preg_replace( "/'''([^']+)'''/u", "**$1**", $line ) );
  }

  private function replace_italic( $line ) {
    return( preg_replace( "/''([^']+)''/u", "//$1//", $line ) );
  }

  private function replace_br( $line ) {
    return( str_replace("[[BR]]", "\\\\", $line) );
  }

  private function replace_toc( $line ) {
    return( str_replace("[[TOC]]", "", $line) );
  }

  private function replace_ignore_CamelCase( $line ) {
    $line = preg_replace("/^!/", "", $line);
    $line = preg_replace("/ !([A-Z])/", " $1", $line);
    return $line;
  }
  
  private static function tracFileName($fn) {
    $fn = rawurlencode($fn);
    return $fn;
  }
  
  public static function tracFileName2DokuWikiFileName($fn) {
    $fn = strtolower($fn);
    $fn = str_replace("%20", "_", $fn);
    $fn = rawurldecode($fn);
    $fn = iconv("UTF-8","ASCII//TRANSLIT", $fn);
    return $fn;
  }
  
  private function workOnAttachment($pattern, &$line) {
    if ($count = preg_match_all($pattern, $line, $hits)) {
      for ($i=0; $i<$count; $i++) {
        $curHit = $hits[1][$i];
        $curHit = TracWiki2DokuWiki::tracFileName($curHit);
        $fileName = TracWiki2DokuWiki::tracFileName2DokuWikiFileName($curHit);
        if ($curHit == $fileName) {
          print("Attachment: $curHit\n");
        } else {
          print("Attachment: $curHit -> $fileName\n");
          $this->mvCommands = $this->mvCommands . "mv \"$curHit\" $fileName\n";
        }
        $line = preg_replace($pattern, "{{:$fileName?linkonly|$2}}", $line, 1);
      }
      return true;
    };
    return false;
  }

  private function replace_link( $line ) {
    if ($this->workOnAttachment("/\[attachment:[\"']([^\"]+)[\"'] *([^\]]*)\]/u", $line)) return $line;
    if ($this->workOnAttachment("/\[attachment:([^ ]+) ([^\]]+)\]/u", $line)) return $line;
    if ($this->workOnAttachment("/\[attachment:([^ ]+)\]/u", $line)) return $line;
    // remove [wiki: prefix
    $line = preg_replace("/\[wiki:/", "[", $line);
    // links without description
    $line = preg_replace("/\[([^ \]]+)\]/u", "[[$1]]", $line );
    //echo "after link w/o description: $line\n";
    // links with description
    $line = preg_replace( "/\[([^ \]]+) ([^\]]+)\]/u", "[[$1|$2]]", $line );
    //echo "after link w description: $line";
    return $line;
  }

  private function replace_itemlists($line) {
    $line = preg_replace("/^( +)\* /", "$1 * ", $line);
    $line = preg_replace("/^\* /", "  * ", $line);
    return $line;
  }

  private function replace_numberedlists($line) {
    return( preg_replace("/^( +)1\. /", "$1 - ", $line) );
  }

}
?>