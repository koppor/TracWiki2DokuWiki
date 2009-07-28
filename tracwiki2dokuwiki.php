<?php 

/**
 * TracWiki2DokuWiki
 * (c) Alex N J, 2009. Free to use, but use at your own risk.
 *
 * A class to automate conversion of a Trac wiki page markup to equivalent DokuWiki format.
 * Usage:
 *    $convertor = new TrackWiki2DokuWiki();
 *    $dokuWikiMarkup = $convertor->convert( $tracWikiMarkup );
 */
class TracWiki2DokuWiki {

	public function convert( $markup ) {
		$markup = trim( $markup );

		$class = new ReflectionClass( 'TracWiki2DokuWiki' );

		$callableMethods = array();
		foreach( $class->getMethods() as $method ) {
			$callableMethods[] = $method->getName();
		}
		asort( $callableMethods );

		foreach( $callableMethods as $methodName ) {
			if( preg_match( '/^replace/', $methodName ) ) {
				$markup = call_user_func_array(
                                     array( $this, $methodName ), array( $markup ) );
			}
		}

		return( $markup );
	}

	private function replace_h1( $markup ) {
		return( preg_replace( "/^= ([^=]+)=/mu", "====== $1=====\n", $markup ) );
	}

	private function replace_h2( $markup ) {
		return( preg_replace( "/^== ([^=]+)==/mu", "===== $1====\n", $markup ) );
	}

	private function replace_h3( $markup ) {
		return( preg_replace( "/^=== ([^\=]+)===/mu", "==== $1====\n", $markup ) );
	}

	private function replace_h4( $markup ) {
		return( preg_replace( "/^==== ([^=]+)====/mu", "== $1==\n", $markup ) );
	}

	private function replace_h5( $markup ) {
		return( preg_replace( "/^===== ([^=]+)=====/mu", "= $1=\n", $markup ) );
	}

	private function replace_10_inline_code( $markup ) {
		return( preg_replace( "/{{{([^}]+)}}}/u", "''$1''", $markup ) );
	}

	private function replace_code_block( $markup ) {
		$markup =  preg_replace( "/{{{\s*/u", "<code>", $markup );
		return( preg_replace( "/}}}\s*/u", "</code>\n", $markup ) );
	}

	private function replace_02_italic( $markup ) {
		return( preg_replace( "/''([^']+)''/u", "//$1//", $markup ) );
	}

	private function replace_01_bold( $markup ) {
		return( preg_replace( "/'''([^']+)'''/u", "**$1**", $markup ) );
	}

	private function replace_table( $markup ) {
		return( preg_replace( "/[ ]*\|\|[ ]*/u", "|", $markup ) );
	}

	private function replace_link_01( $markup ) {
		return( preg_replace( "/\[([^ ]+) ([^\]]+)\]/u", "[[$1|$2]]", $markup ) );
	}

	private function replace_table_heading( $markup ) {
		$markup =  preg_replace( "/\|\*\*/", "^", $markup );
		$markup =  preg_replace( "/\*\*\|/", "^", $markup );
		return( preg_replace( "/[\*]*\^[\*]*/", "^", $markup ) );
	}
}

?>