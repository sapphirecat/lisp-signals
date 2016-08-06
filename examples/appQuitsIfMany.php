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
    // use static variables, to remember state between calls.
    // (sort of like a mini class; I'm too lazy to make a real one.)
    static $failures;
    static $skip;

    if ($skip === true) {
        // avoid calling Signal::error() recursively, until we run out of stack
        // and crash the whole PHP process.
        return;
    } elseif ($failures === null) {
        // initialize variables on first entry.
        $failures = 0;
        $skip = false;
    }

    // count failure
    ++$failures;
    if ($failures <= 3) {
        // still okay? keep going.
        $restarts->call('SkipEntry');
    }

    // too many failures: raise an error of our own.
    try {
        $skip = true;
        Signal::error("Too many failures; check the log file format.",
            ['failures' => 3]);
    } finally {
        $skip = false;
    }
}
