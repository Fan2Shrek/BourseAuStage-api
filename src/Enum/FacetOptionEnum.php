<?php

namespace App\Enum;

enum FacetOptionEnum: string
{
    case ALL = 'all';
    case DEFAULT_ALL = 'default_all';
    case BETWEEN = 'between';
    case BETWEEN_AND_MORE = 'between_and_more';
    case RANGE = 'range';
}
