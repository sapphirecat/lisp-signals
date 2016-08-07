<?php
use Sapphirecat\Signal\Signal;

require(__DIR__.'/common.inc.php');
foreach (collect_filenames($argv) as $filename) {
    $handle = Signal::receive('app_handle');
    print_r(analyze_log($filename));
    unset($handle);
}


function app_handle ($restarts, $signal)
{
    $data['*error'] = $signal->getMessage(); // store a "corrupt entry" message
    $restarts->call('UseValue', [ 'value' => $data ]);
}
