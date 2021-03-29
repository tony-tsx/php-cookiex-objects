<?php

namespace CookieX;

class Str implements \Iterator {
  private $string;
  private $position = 0;
  public static function isString( $str ) {
    return $str instanceof Str || is_string( $str );
  }
  public static function extract( $str ) {
    if ( $str instanceof Str ) return $str->string;
    if ( is_string( $str ) ) return $str;
    return $str . '';
  }
  function __construct( string $string = '' ) {
    $this->string = $string;
  }
  public function charAt( int $index ) { return self::extract( $this )[$index]; }
  public function charCodeAt( int $index ) { return ord( $this->charAt( $index ) ); }
  public function concat( ...$strings ) {
    return new Str( self::extract( $this ) . Arr::instance( $strings )->join( '' ) );
  }
  public function includes( $str ) {
    return strpos( self::extract( $str ), self::extract( $this ) ) !== false;
  }
  public function startsWith( $haystack ) {
    $haystack = Str::extract( $haystack );
    $length = strlen( $haystack );
    return substr( $haystack, 0, $length ) === $haystack;
  }
  public function endsWith( $haystack ) {
    $haystack = Str::extract( $haystack );
    $length = strlen( $haystack );
    if( !$length ) return true;
    return substr( $haystack, -$length ) === $haystack;
  }
  public function match( $regex ) {
    return RegExp::normalize( $regex )->exec( $this );
  }
  public function indexOf( $str ) {
    $result = strpos( self::extract( $str ), self::extract( $this ) );
    if ( $result !== false ) return $result;
    return -1;
  }
  public function lastIndexOf( $str ) {
    $result = strrpos( self::extract( $str ), self::extract( $this ) );
    if ( $result !== false ) return $result;
    return -1;
  }
  public function compare( $str ) {
    return strcmp( self::extract( $this ), self::extract( $str ) );
  }
  public function repeat( int $count ) {
    return new self( Arr::instance( $count )->fill( $this )->join( '' ) );
  }
  public function replace( $search, $replacement ) {
    if ( RegExp::isRegExp( $search ) )
      return new self(
        preg_replace( RegExp::normalize( $search )->getPattern(), $replacement, self::extract( $this ) )
      );
    return new self( str_replace( $search, $replacement, self::extract( $this ) ) );
  }
  public function split( $match = '', $limit = null ) {
    if ( $match == "" ) return Arr::from( $this );

    if ( RegExp::isRegExp( $match ) ) {
      $result = preg_split(
        RegExp::normalize( $match )->getPattern(),
        self::extract( $this ),
        $limit
      );
      return new Arr( $result );
    }

    return new Arr( explode( $match, self::extract( $this ), $limit ) );
  }

  public function toLowerCase() {
    return new self( strtolower( self::extract( $this ) ) );
  }
  public function toUpperCase() {
    return new self( strtoupper( self::extract( $this ) ) );
  }
  public function trim() {
    return new self( trim( self::extract( $this ) ) );
  }
  public function trimLeft() {
    return new self( ltrim( self::extract( $this ) ) );
  }
  public function trimRight() {
    return new self( rtrim( self::extract( $this ) ) );
  }

  public function current() { return self::extract( $this->string )[$this->position]; }
  public function key() { return $this->position; }
  public function next() { ++$this->position; }
  public function rewind() { $this->position = 0; }
  public function valid() {
    return isset( self::extract( $this->string )[$this->position] );
  }

  public function __toString() { return self::extract( $this ); }
}

?>