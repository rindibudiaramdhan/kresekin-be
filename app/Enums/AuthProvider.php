<?php

namespace App\Enums;

enum AuthProvider: string
{
    case LOCAL = 'local';
    case GOOGLE = 'google';
}
