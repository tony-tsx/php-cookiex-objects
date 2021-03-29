<?php

namespace CookieX;

class Obj {
  public static function entries( $object ) {
    $arr = [];
    foreach ( $object as $key => $value )
      $arr[] = [ $key, $value ];

    return new Arr( $arr );
  }
  public static function keys( $object ) {
    $arr = [];
    foreach ( $object as $key => $value )
      $arr[] = $key;

    return new Arr( $arr );
  }
  public static function values( $object ) {
    $arr = [];
    foreach ( $object as $key => $value )
      $arr[] = $value;

    return new Arr( $arr );
  }
  public static function fromEntries( $entries ) {
    $std = new Obj();
    foreach ( $entries as [ $key, $value ] )
      $std->{$key} = $value;
    return $std;
  }
}

?>