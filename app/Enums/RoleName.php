<?php

namespace App\Enums;

enum RoleName: string
{
    case SuperAdmin = 'SuperAdmin';
    case Admin = 'Admin';
    case Expert = 'Expert';
    case Customer = 'Customer';
}
