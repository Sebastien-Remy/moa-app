<?php

namespace App\Enum;

enum DocumentDirection: string
{
    case Incoming = 'incoming';
    case Outgoing = 'outgoing';
    case Internal = 'internal';

    public function getLabel(): string
    {
        return match ($this) {
            self::Incoming => 'Incoming',
            self::Outgoing => 'Outgoing',
            self::Internal => 'Internal',
        };
    }

    public function getFaIcon(): string
    {
        return match ($this) {
            self::Incoming => 'fa-arrow-down',
            self::Outgoing => 'fa-arrow-up',
            self::Internal => 'fa-right-left',
        };
    }
}
