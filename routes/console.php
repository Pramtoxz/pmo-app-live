<?php

use App\Http\Controllers\appsheet\AppsheetController;
use App\Http\Controllers\Aptana\SqmReminderCheckController;
use App\Http\Controllers\Aptana\WhatsAppController;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\Scheduler\NotifHarianController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

    // appsheet//
    Schedule::call(function () {
        app(AppsheetController::class)->StoreTask();
    })->everyThirtySeconds()
        ->name('appsheet-queue')
        ->withoutOverlapping()           
        ->onOneServer();        
    // Artisan::command('inspire', function () {
    //     $this->comment(Inspiring::quote());
    // })->purpose('Display an inspiring quote')->hourly();

    // Schedule::call(function () {
    //     app(NotifHarianController::class)->handle();
    // })->daily()->at('10:10');
    

    // aptana //
    Schedule::call(function () {
        app(WhatsAppController::class)->processQueue();
    })->everyTwoSeconds()
        ->name('whatsapp-queue')
        ->withoutOverlapping()           
        ->onOneServer();
    // sqm //
    Schedule::call(function () {
        app(SqmReminderCheckController::class)->checkTransactionDateDifference();
    })->hourly()
        ->name('sqm-check-reminder')
        ->withoutOverlapping()
        ->onOneServer();

    Schedule::call(function () {
        app(SqmReminderCheckController::class)->updateExpired();
    })->hourly()
        ->name('sqm-expired')
        ->withoutOverlapping()
        ->onOneServer();

    // Aptana - Send Report Every 10 Minutes
    Schedule::call(function () {
        try {
            app(WhatsAppController::class)->saveAndSendReport();
            Log::info('WhatsApp report sent successfully');
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    })->everyTenMinutes()
        ->name('whatsapp-report')
        ->withoutOverlapping()
        ->onOneServer();