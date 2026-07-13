<?php

namespace Api\Enum\Business;

enum ContentTranslationType: string
{
    case Lodging = 'Lodging';
    case Tag = 'Tag';
    case Equipment = 'Equipment';
    case Location = 'Location';
    case LocationArea = 'LocationArea';
}
