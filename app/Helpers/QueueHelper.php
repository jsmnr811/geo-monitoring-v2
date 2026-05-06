<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;

class QueueHelper
{
    public static function sync(): string
    {
        return App::environment('production')
            ? 'geo'
            : 'default';
    }
}