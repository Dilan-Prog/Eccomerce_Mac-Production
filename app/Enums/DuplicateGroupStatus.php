<?php

namespace App\Enums;

enum DuplicateGroupStatus: string
{
    case Pending = 'pending';
    case Discarded = 'discarded';
    case Resolved = 'resolved';
}
