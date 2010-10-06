<?php

class Dja_Function
{
    const CONFIG_OVERWRITE = '__owerwrite__';
    
    /**
     * Merge two arrays recursively, overwriting keys of the same name
     * in $firstArray with the value in $secondArray.
     *
     * @param  mixed $firstArray  First array
     * @param  mixed $secondArray Second array to merge into first array
     * @return array
     */
    public static function arrayMergeRecursive($firstArray, $secondArray)
    {
        if (is_array($firstArray) && is_array($secondArray)) {
            if (isset($secondArray[0]) && $secondArray[0] == self::CONFIG_OVERWRITE) {
                unset($secondArray[0]);
                return $secondArray;
            }
            foreach ($secondArray as $key => $value) {
                if (isset($firstArray[$key])) {
                    if (is_string($key)) {
                        $firstArray[$key] = self::arrayMergeRecursive($firstArray[$key], $value);
                    } else {
                        $firstArray[] = self::arrayMergeRecursive($firstArray[$key], $value);
                    }
                } else {
                    if($key === 0) {
                        $firstArray = array(0 => self::arrayMergeRecursive($firstArray, $value));
                    } else {
                        $firstArray[$key] = $value;
                    }
                }
            }
        } else {
            $firstArray = $secondArray;
        }
        return $firstArray;
    }
}