<?php

namespace App\Enum;

enum DocumentDirection: string
{
    case Incoming = 'incoming';
    case Outgoing = 'outgoing';
    case Internal = 'internal';
}
