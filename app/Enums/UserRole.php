<?php

namespace App\Enums;

enum UserRole: int
{
    case Student = 1;
    case Teacher = 2;
    case Admin   = 3;
}