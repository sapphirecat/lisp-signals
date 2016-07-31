<?php

/* This demo is pretty shamelessly ripped off of Peter Siebel's running example
 * in Chapter 19 of Practical Common Lisp.
 *
 * We are building example log-parsing applications, and they use some "low
 * level" code that is defined here, and the same for _every_ log.
 */

use Sapphirecat\Signal\Signal;
use Sapphirecat\Signal\BaseRestart;

error_reporting(-1);
ini_set('xdebug.scream', 1);

// minimalist PSR-4 implementation to autoload the Sapphirecat\Signal package
function sapphirecat_signal_autoload ($name)
{
    $tail = str_replace("Sapphirecat\\Signal\\", '', $name);
    if ($tail !== $name) {
        include(dirname(__DIR__).'/src/'.str_replace('\\', '/', $tail).'.php');
    }
}
spl_autoload_register('sapphirecat_signal_autoload');


class UseValue extends BaseRestart
{
    private $value;

    function __construct(array $args=null, $message=null, $code=null, $previous=null)
    {
        parent::__construct($args, $message, $code, $previous);
        $this->requireArgs(['value']);
        $this->value = $args['value'];
    }

    function getValue()
    {
        return $this->value;
    }
}

class SkipEntry extends BaseRestart
{
}


abstract class LogEntry
{
    const EMERGENCY = 700;
    const CRITICAL = 600;
    const ERROR = 500;
    const WARNING = 400;
    const NOTICE = 300;
    const INFO = 200;
    const DEBUG = 100;
    const UNKNOWN = 0;

    protected static $severities;

    protected $timestamp;
    protected $severity;
    protected $message;

    static function init()
    {
        // save a table of logfileText => internalCode for the parser
        self::$severities = [
            'emergency' => self::EMERGENCY,
            'critical' => self::CRITICAL,
            'error' => self::ERROR,
            'warning' => self::WARNING,
            'notice' => self::NOTICE,
            'info' => self::INFO,
            'debug' => self::DEBUG,
            'unknown' => self::UNKNOWN,
        ];
    }

    function getTime()
    {
        return $this->timestamp;
    }

    function getSeverityCode()
    {
        return self::$severities[$this->severity];
    }

    function getSeverity()
    {
        return $this->severity;
    }

    function getMessage()
    {
        return $this->message;
    }
}
LogEntry::init();

class MalformedLogEntry extends LogEntry
{
    function getTime()
    {
        return 0;
    }

    function getSeverity()
    {
        return 'unknown';
    }

    function getMessage()
    {
        return null;
    }
}

class ValidLogEntry extends LogEntry
{
    function __construct ($time, $severity, $message)
    {
        if (! isset(self::$severities[$severity])) {
            Signal::error("Unknown severity level: $severity", ['severity' => $severity]);
        }

        $this->timestamp = $time;
        $this->severity = $severity;
        $this->message = $message;
    }
}


function parse_log_entry ($line)
{
    try {
        $rh = Signal::restart('UseValue');

        $fields = preg_split('/\\s+/', $line, 3, PREG_SPLIT_NO_EMPTY);
        if (count($fields) < 3) {
            Signal::error("Not enough fields in line", ['line' => $line]);
        }
        return new ValidLogEntry($fields[0], $fields[1], $fields[2]);

    } catch (UseValue $ex) {
        return $ex->getValue();
    } finally {
        unset($rh);
    }
}

function parse_log_file ($stream)
{
    $lines = [];
    while (($line = fgets($stream)) !== false) {
        try {
            $rh = Signal::restart('SkipEntry');
            $lines[] = parse_log_entry($line);
        } catch (SkipEntry $ex) {
            // nothing added to $lines
        } finally {
            unset($rh);
        }
    }
    return $lines;
}

function analyze_log ($filename)
{
    $fp = fopen($filename, 'r');
    if (! $fp) {
        Signal::error("Can't open $filename for reading", ['filename' => $filename]);
    }

    return parse_log_file($fp);
}


function collect_filenames ($argList)
{
    $files = [];
    $max = count($argList);
    for ($i = 1; $i < $max; ++$i) {
        $name = $argList[$i];
        if (file_exists($name)) {
            $files[] = $name;
        } else {
            Signal::warning("Ignoring nonexistent file: $name");
        }
    }
    return $files;
}
