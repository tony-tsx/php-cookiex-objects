<?php

namespace CookieX;

define( 'REG_EXP_PATTERN', '^\/(?<pattern>.*)\/(?<flags>[a-z]*)$' );

class RegExp {
  private $_pattern;
  private $_flags;
  private $_global;
  private $_ignore_case;
  private $_multiline;
  private $_sticky;
  private $_unicode;
  private $_regex = null;
  private function _regex() {
    if ( $this->_regex ) return $this->_regex;
    $flags = str_replace( 'g', '', $this->_flags );
    return $this->_regex = "/{$this->_pattern}/{$flags}";
  }
  private function _refresh( $pattern, $flags ) {
    $this->_pattern = Str::extract( $pattern );
    $this->_flags = Str::extract( $flags );
    $this->_global = strpos( $this->_flags, 'g' ) !== false ? true : false;
    $this->_ignore_case = strpos( $this->_flags, 'i' ) !== false ? true : false;
    $this->_multiline = strpos( $this->_flags, 'm' ) !== false ? true : false;
    $this->_sticky = strpos( $this->_flags, 'y' ) !== false ? true : false;
    $this->_unicode = strpos( $this->_flags, 'u' ) !== false ? true : false;
    $this->_regex = null;
  }

  public static function isRegExp( $value ) {
    if ( $value instanceof RegExp ) return true;
    else if ( is_string( $value ) )
      return ( new RegExp( REG_EXP_PATTERN ) )->test( $value );
    return false;
  }

  public static function normalize( $regex ) {
    if ( $regex instanceof RegExp ) return $regex;
    else if ( is_string( $regex ) ) {
      $regExpForRegExp = new RegExp( REG_EXP_PATTERN );
      $match = $regExpForRegExp->exec( $regex );
      if ( !$match ) throw new \Exception( "Error Processing Request", 1 );
      return new self( $match->groups->pattern, @$match->groups->flags );
    }
    throw new \Exception( '' );
  }

  function __construct( $str, $flags = '' ) {
    $this->_refresh( $str, $flags );
  }

  public function getPattern() { return $this->_regex(); }

  public function test( $str ) {
    if ( $this->_global )
      return preg_match_all( $this->_regex(), Str::extract( $str ) ) === 1 ? true : false;
    return preg_match( $this->_regex(), Str::extract( $str ) ) === 1 ? true : false;
  }
  public function exec( $str ) {
    $pattern = $this->_regex();
    if ( $this->_global )
      preg_match_all( $pattern, Str::extract( $str ), $matches );
    else preg_match( $pattern, Str::extract( $str ), $matches );
    if ( $matches ) return new RegExpMatchArray( $matches );
    return null;
  }
  function __toString() { return $this->_regex(); }
}

?>