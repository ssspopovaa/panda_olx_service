<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('adverts:enqueue-checks --interval=5')->everyFiveMinutes();
