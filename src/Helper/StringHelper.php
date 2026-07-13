<?php

namespace Api\Helper;

class StringHelper
{

    static function explode(string $delemiter, string $input): array
    {

        // Array keys are preserved, and may result in gaps if the array was indexed. The result array is reindexed using the array_values() function.
        return array_values(array_filter(explode($delemiter, $input), fn($segment) => strlen($segment) > 0));
    }
}
