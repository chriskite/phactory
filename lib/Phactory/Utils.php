<?php

namespace Phactory;

class Utils {
  public static function getValueOrReturn($value) {
    if(is_object($value) && is_callable($value)
      || $value instanceof Closure) {
        return $value();
    }
    return $value;
  }
}