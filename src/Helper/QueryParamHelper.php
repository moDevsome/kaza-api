<?php

namespace Api\Helper;

use Api\Exception\BusinessException;

class QueryParamHelper
{

    /**
     * Parse "sort" query param
     * @param string $input - the query param content
     * @param array $allowedProperties - mandatory array of string to define which object properties is allowed for sorting
     * @throws BusinessException
     * @return array
     */
    public static function parseSort(string $input, array $allowedProperties): array
    {

        $parse = array_map('trim', explode(',', $input));
        if (count($parse) !== 2)
            throw new BusinessException(400, 'The provided "sort" query param does not have the correct format, expected format: {property},{direction}');

        $property = $parse[0];
        if (!in_array($property, $allowedProperties))
            throw new BusinessException(400, 'The property "' . $property . '", is not allowed for sorting, allowed properties: ' . implode(', ', $allowedProperties));

        $direction = strtolower($parse[1]);
        $allowedDirections = ['asc', 'desc'];
        if (!in_array($direction, $allowedDirections))
            throw new BusinessException(400, '"' . $direction . '", is not a correct direction for sorting, allowed values: ' . implode(', ', $allowedDirections));

        return [$property, $direction];
    }
}
