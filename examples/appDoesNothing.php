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
    // This app doesn't care at all about errors, so all it needs to do is
    // continue without using that particular log entry.
    $restarts->call('SkipEntry');
}
