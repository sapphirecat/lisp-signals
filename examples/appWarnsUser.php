<?php
use Sapphirecat\Signal\Signal;

require(__DIR__.'/common.inc.php');
foreach (collect_filenames($argv) as $filename) {
    // We emit a warning. Avoid having them passed to our error handler.
    $handle = Signal::receive('app_handle', ['Sapphirecat\\Signal\\Error']);
    print_r(analyze_log($filename));
    unset($handle);
}


function app_handle ($restarts, $signal)
{
    // convert the error message to a warning
    Signal::warning("Ignoring an error: {$signal->getMessage()}");

    // then continue without processing this line.
    $restarts->call('SkipEntry');
}
