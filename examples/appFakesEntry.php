<?php
use Sapphirecat\Signal\Signal;

require(__DIR__.'/common.inc.php');
foreach (collect_filenames($argv) as $filename) {
    $handle = Signal::receive('app_handle');
    print_r(analyze_log($filename));
}


function app_handle ($restarts, $signal, $args)
{
    list($message, $data) = $args;
    $data['*error'] = $message; // store a "corrupt entry" message
    $restarts->call('UseValue', [ 'value' => $data ]);
}
