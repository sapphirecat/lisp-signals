<?php
use Sapphirecat\Signal\Signal;

require(__DIR__.'/common.inc.php');
foreach (collect_filenames($argv) as $filename) {
    $handle = Signal::receive('app_handle');
    print_r(analyze_log($filename));
    unset($handle);
}


function app_handle ($restarts, $signal, $args)
{
    if ($signal !== 'error') {
        return; // don't do anything about non-errors
    }

    // unpack the error info and issue the warning
    list($message, $data) = $args;
    Signal::warning("Ignoring an error: $message");

    // then continue without processing this line.
    $restarts->call('SkipEntry');
}
