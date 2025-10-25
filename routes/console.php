<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('send:appointment-reminders')->dailyAt('07:00')->timezone('Asia/Manila');
