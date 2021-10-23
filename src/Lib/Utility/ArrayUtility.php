<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Utility;

class ArrayUtility
{
    /**
     * Removes empty values from an array
     *
     * @param array $array The array to scan
     * @param array $keys A list of keys to check for empty values
     * @return array
     */
    public static function removeEmptyVars(array $array, array $keys): array
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $array) && empty($array[$key])) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Removes null values from an array
     *
     * @param array $array The array to scan
     * @param array $keys A list of keys to check for null values
     * @return array
     */
    public static function removeNullValues(array $array, array $keys): array
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $array) && is_null($array[$key])) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Removes null values from an array
     *
     * @param array $array The array to scan
     * @param array $keys A list of keys to check for null values
     * @return array
     */
    public static function removeEmptyAndNullValues(array $array, array $keys): array
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $array) && (is_null($array[$key]) || empty($array[$key]))) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Removes matching values from the array
     *
     * @param array $array The array to scan on
     * @param array $match The array to match against
     * @return array
     */
    public static function removeValuesMatching(array $array, array $match): array
    {
        foreach ($match as $key => $value) {
            if (array_key_exists($key, $array) && $array[$key] === $value) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Removes matching values from the array by key
     *
     * @param array $array The array to scan on
     * @param array $keys The array to match against
     * @return array
     */
    public static function removeKeysMatching(array $array, array $keys): array
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Convert the null items in the array to empty strings
     *
     * @param array $array The array to scan on
     * @param array $keys The key values to change
     * @return array
     */
    public static function convertNullToEmptyString(array $array, array $keys): array
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $array) && $array[$key] === null) {
                $array[$key] = '';
            }
        }

        return $array;
    }
}
