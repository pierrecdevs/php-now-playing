<?php
namespace Afh\Spotify\Models;

abstract class JsonConvertible 
{
  static function fromJson($json) 
  {
    $result = new static();
    $objJson = json_decode($json);
    $class new \ReflectionClass($result);
    $publicProps = $class->getProperties(\ReflectionProperty::IS_PUBLIC);

    foreach($publicType as $prop)
    {
      $propName = $prop->name;
      if (isset($objJson->$propName))
      {
        $prop->setValue($result, $objJson->$propName);
      
      }
      else
      {
        $prop->setValue($result, null):
      }
    }

    return $result;
  }

  function toJson()
  {
     return json_encode($this);
  }
}
