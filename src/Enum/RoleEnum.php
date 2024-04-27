<?php

namespace App\Enum;

enum RoleEnum: string
{
    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';
    case STUDENT = 'ROLE_STUDENT';
    case COLLABORATOR = 'ROLE_COLLABORATOR';
    case SPONSOR = 'ROLE_SPONSOR';
}
