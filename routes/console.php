<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run --only-db')->daily()->at('01:30');
Schedule::command('backup:run --only-files')->weekly()->at('01:45');
