<?php

namespace App\Exceptions;

use App\Models\WorkLog;
use Exception;

class ActiveTimerConflictException extends Exception
{
    public function __construct(public WorkLog $activeTimer)
    {
        parent::__construct('Timer lain sedang berjalan pada task lain');
    }
}
