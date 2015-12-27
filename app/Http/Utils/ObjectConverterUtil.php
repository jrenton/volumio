<?php

namespace App\Http\Utils;

class ObjectConverterUtil 
{
    static function arrayToObject($array, $theClass = 'stdClass', $strict = false) 
    {
        if (!is_array($array)) 
        {
            return $array;
        }
        
        // create an instance of an class without calling class's constructor
        $object = unserialize(sprintf('O:%d:"%s":0:{}', strlen($theClass), $theClass));

        //$object = new $theClass();
        
        if (is_array($array) && count($array) > 0) 
        {
            foreach ($array as $name => $value)
            {
                $name = lcfirst(trim($name));
                if (!empty($name)) 
                {
                    if(method_exists($object, 'set'.$name))
                    {
                        $object->{'set'.$name}(ObjectConverterUtil::arrayToObject($value));
                    }
                    else
                    {
                        if (($strict))
                        {
                            if (property_exists($theClass, $name))
                            {
                                $object->$name = ObjectConverterUtil::arrayToObject($value);
                            }
                        } 
                        else
                        {
                            $object->$name = ObjectConverterUtil::arrayToObject($value); 
                        }
                    }
                }
            }
            
            return $object;
        }
        
        return FALSE;
    }
}