<?php

namespace App\Enum;

enum FacetOptionEnum: string
{
    case ALL = 'all';
    case DEFAULT_ALL = 'default_all';
    case BETWEEN = 'between';
    case DURATION = 'duration';
    case RANGE = 'range';
}
