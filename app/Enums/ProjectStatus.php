<?php

namespace App\Enums;

enum ProjectStatus: int
{
    case Pending        = 0;
    case UnderReview    = 1;
    case NeedChanges    = 2;
    case Approved       = 3;
    case Rejected       = 4;
}