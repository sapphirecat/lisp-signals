<?php
namespace Sapphirecat\Signal;

/** Scope guard that invokes a callable on destruction.
 *
 * Used by the Restarts and Router classes to de-register the corresponding
 * restart and signal handlers when this object is destroyed.
 */
class CallScopeGuard implements ScopeGuard
{
    private $action;
    private $args;

    /** Constructor.
     *
     * Stores the callable and arguments for being invoked when __destruct() is
     * called.
     *
     * @param callable $action Actual function or method to be called.
     * @param array $args Arguments to be passed to the $action.
     */
    function __construct(callable $action, array $args)
    {
        $this->action = $action;
        $this->args = $args;
    }

    /** Destructor.
     *
     * Invokes the call that was stored by __construct().
     */
    function __destruct()
    {
        call_user_func_array($this->action, $this->args);
    }
}
