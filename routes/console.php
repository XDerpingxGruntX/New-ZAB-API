<?php

use App\Jobs\CheckAllControllerActivity;
use App\Jobs\FetchControllerSessions;
use App\Jobs\FetchFlightSessions;
use App\Jobs\FetchPilotReports;
use App\Jobs\SyncRoster;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new SyncRoster)->everyTenMinutes();
Schedule::job(new FetchPilotReports)->everyTwoMinutes();
Schedule::job(new FetchFlightSessions)->everyFifteenSeconds();
Schedule::job(new FetchControllerSessions)->everyFifteenSeconds();
Schedule::job(new CheckAllControllerActivity)->daily();
