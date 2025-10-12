<?php

if (!function_exists('app_now')) {
    /**
     * Get the current time, or test time if in test mode
     * 
     * @return \Illuminate\Support\Carbon
     */
    function app_now() {
        if (config('app.test_mode') && config('app.test_time')) {
            return \Carbon\Carbon::parse(config('app.test_time'));
        }
        return now();
    }
}

