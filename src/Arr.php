<?php

namespace CookieX;

class ArrEmpty {}

class Arr implements \Iterator, \Serializable, \JsonSerializable {
  private $position = 0;
  private $walk_empty = false;
  private $array;

  public static function isArray( $needle ) {
    if ( $needle instanceof Arr ) return true;
    return is_array( $needle );
  }
  public static function extract( $array ) {
    if ( $array instanceof Arr ) return $array->array;
    if ( is_array( $array ) ) return $array;
    return [];
  }
  public static function instance( ...$args ) { return new self( ...$args ); }
  public static function from( $iterator ) {
    $arr = [];
    foreach( $iterator as $value )
      if ( $value instanceof ArrEmpty ) array_push( $arr, null );
      else array_push( $arr, $value );

    return new self( $arr );
  }

  function __construct( &$arr = null, ...$items ) {
    if ( count( $items ) )
      $this->array = [ $arr, ...$items ];

    else if ( $arr instanceof Arr ) $this->array = &$arr->array;

    else if ( is_array( $arr ) ) $this->array = $arr;

    else if ( is_int( $arr ) ) $this->array = array_fill( 0, $arr, new ArrEmpty() );

    else if ( is_numeric( $arr ) ) throw new \Exception( "Error Processing Request", 1 );

    else $this->array = [];

    return $this;
  }
  function __get( $key ) {
    if ( $key == 'length' ) return count( $this->array );
    if ( is_numeric( $key ) ) return $this->get( $key );
  }
  function __call( $key, $arguments ) {
    if ( function_exists( "array_{$key}" ) )
      return call_user_func_array( "array_{$key}", [ &$this->array, ...$arguments ] );

    else if ( function_exists( "array{$key}" ) )
      return call_user_func_array( "array{$key}", [ &$this->array, ...$arguments ] );
  }

  public function get( int $index ) { return $this->array[$index]; }

  public function pop() { return array_pop( $this->array ); }
  public function shift() { return array_shift( $this->array ); }

  public function push( ...$items ) { return array_push( $this->array, ...$items ); }
  public function unshift( ...$items ) { return array_unshift( $this->array, ...$items ); }

  public function indexOf( $needle ) { return array_search( $needle, $this->array, true ); }
  public function slice( int $offset, int $length = null ) {
    return self::from( array_slice( $this->array, $offset, $length, false ) );
  }
  public function splice( int $offset, int $length = null, ...$replacement ) {
    return self::from( array_splice( $this->array, $offset, $length, $replacement ) );
  }
  public function forEach( $callable ) {
    foreach ($this as $index => $value)
      $callable( $value, $index, $this );
  }
  public function map( $callable ) {
    $arr = [];
    foreach ( $this as $index => $value )
      $arr[$index] = $callable( $value, $index, $this );
    return new self( $arr );
  }
  public function filter( $callable ) {
    $arr = [];
    $this->forEach( function( $value, $index, $array ) use ( &$callable, &$arr ) {
      if ( $callable( $value, $index, $array ) )
        $arr[] = $value;
    } );
    return new self( $arr );
  }
  public function reduce( $callable, $initial = null ) {
    $breaker = self::instance( $this );
    if ( !$breaker->length ) return null;
    $last = $initial !== null ? $initial : $breaker->shift();
    foreach( $breaker as $index => $value )
      $last = $callable( $last, $value, $index, $this );
    return $last;
  }
  public function reduceRight( $callable, $initial = null ) {
    $breaker = self::instance( $this );
    if ( !$breaker->length ) return null;
    $last = $initial !== null ? $initial : $breaker->pop();
    $index = $this->length;
    while( $index )
      $last = $callable( $last, $this->array[--$index], $index, $this );
    return $last;
  }
  public function fill( $item ) {
    $last_walk_empty = $this->walk_empty;
    $this->walk_empty = true;
    $this->forEach( function( $value, $index, $array ) use ( &$item ) {
      if ( $value instanceof ArrEmpty ) $this->array[$index] = $item;
    } );
    $this->walk_empty = $last_walk_empty;
    return $this;
  }
  public function find( callable $callable ) {
    foreach( $this as $key => $value )
      if ( $callable( $value, $key, $this ) ) return $value;
  }
  public function findIndex( callable $callable ) {
    foreach( $this as $key => $value )
      if ( $callable( $value, $key, $this ) ) return $key;
    return -1;
  }
  public function every( callable $callable ) {
    foreach( $this as $key => $value )
      if ( !$callable( $value, $key, $this ) ) return false;
    return true;
  }
  public function some( callable $callable ) {
    foreach( $this as $key => $value )
      if ( $callable( $value, $key, $this ) ) return true;
    return false;
  }
  public function concat( ...$items ) {
    return self::instance( [ ...$this->array, ...self::instance( $items )->flat( 1 ) ] );
  }
  public function includes( $needle ) { return in_array( $needle, $this->array ); }
  public function join( string $glue ) { return implode( $glue, $this->array ); }
  public function lastIndexOf( $callable ) {
    $index = $this->length;
    while( $index )
      if ( $this->array[--$index] instanceof ArrEmpty ) continue;
      else if ( $callable( $this->array[$index], $index, $this ) ) return $index;
    return -1;
  }
  public function reverse() {
    $arr = [];
    $index = $this->length;
    while( $index ) $arr[] = $this->array[--$index];
    return new self( $arr );
  }
  public function sort() {}
  public function flat( int $depths = 1 ) {
    if ( $depths <= 0 ) return self::instance( $this );
    $arr = self::instance();
    $this->forEach( function( $value ) use ( &$arr, $depths ) {
      if ( self::isArray( $value ) )
        if ( --$depths ) $arr->push( ...self::instance( $value )->flat( $depths ) );
        else $arr->push( ...$value );
      else $arr->push( $value );
    } );
    return $arr;
  }
  public function chunk( int $size ) {
    $arr = new self();
    $part = 0;
    do {
      $items = $this->slice( $part * $size, $size );
      if ( $items->length ) {
        $arr->push( $items );
        $part++;
      }
    } while( $items->length );
    return $arr;
  }

  public function current() { return $this->array[$this->position]; }
  public function key() { return $this->position; }
  public function next() { ++$this->position; }
  public function rewind() { $this->position = 0; }
  public function valid() {
    return $this->length > $this->position && ( $this->walk_empty || !( $this->array[$this->position] instanceof ArrEmpty ) );
  }
  public function serialize() { return json_encode( $this ); }
  public function unserialize( $json ) { $this->array = json_decode( $json ); }
  public function jsonSerialize() { return $this->array; }

  function __toString() { return $this->join( ',' ); }
}

?>