<?php

namespace CookieX;

class RegExpMatchArray extends Arr {
  public $groups;
  function __construct( $match ) {
    $arr = [];
    $this->groups = new \StdClass();
    foreach( $match as $index => $value )
      if ( is_numeric( $index ) ) $arr[] = $value;
      else $this->groups->{$index} = $value;

    parent::__construct( $arr );
  }
}

?>