<?php

declare(strict_types=1);

namespace App\Domain;

enum TodoStatus: string
{
    case OPENED = 'opened';
    case CLOSED = 'closed';
}
