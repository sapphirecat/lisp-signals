<?php
namespace Sapphirecat\Signal;

use Exception;
use InvalidArgumentException;

class BaseRestart extends Exception implements RestartInterface
{
    private $args;

    public function __construct(array $args=null, $message=null, $code=null, $previous=null)
    {
        parent::__construct($message, $code, $previous);
        $this->args = $args ?: [];
    }

    protected function requireArgs (array $names)
    {
        $missing = array();
        foreach ($names as $name) {
            if (! array_key_exists($name, $this->args)) {
                $missing[] = $name;
            }
        }
        if ($missing) {
            throw new InvalidArgumentException("Missing required arguments to restart: " .
                implode(', ', $missing));
        }
    }

    public function getArgs()
    {
        return $this->args;
    }
}
