<?php

if (! function_exists('stdClassToArray')) {
    /**
     * Converts an stdClass object to an array
     *
     * @param $stdClass
     * @return array
     */
    function stdClassToArray($stdClass): array
    {
        return json_decode(json_encode($stdClass), true);
    }
}
