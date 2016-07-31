<?php
namespace Sapphirecat\Signal;

/** Signaling system.
 *
 * This class breaks signal handling down into three parts.
 *
 * First, "signals" themselves: strings that are sent by low-level code, that
 * trigger a search for a signal handler.  These are set in motion by send().
 *
 * Second, the actual "signal handler", established through receive(), that
 * determines an action to take.
 *
 * Third, the "restarts", the choices of action presented to the signal handler
 * by ANY code between the code sending the signal, and the signal handler.  A
 * restart is defined by restart().
 *
 * When a restart is chosen, control is returned (via a special \Exception
 * subclass, because that's the only nonlocal return that PHP has) to the level
 * that defined the restart, NOT the level that defined the handler.
 *
 * Signal handlers are passed a Restarts instance; they can explore and call a
 * restart using that object.
 *
 * While send() is a low-level interface to sending any kind of signal, we also
 * borrow Lisp's error and warning mechanisms: error() provides a signal that
 * will either be handled, or terminate the program.  (We don't have a debugger
 * to break into like Lisp does.)  warning() provides a signal that merely
 * prints to the error log if no handler code silences it.
 *
 * The trick to all this is, sending a signal calls down--into this package,
 * and the signal handler function--instead of jumping straight up to a catch
 * block the way a thrown exception would.  This means control is still down
 * here when a restart is called, so we can go back up the call stack to the
 * restart even if that restart is below the signal handler.
 *
 * tl;dr:
 *
 * @see receive() for setting up signal handlers
 * @see Restarts for finding and calling available actions from a signal handler
 * @see restart() for setting up actions for signal handlers to take
 * @see send() for low-level signal sending
 * @see error() for sending errors that either restart or terminate the program
 * @see warning() for sending warning messages that are printed if not handled
 */
class Signal
{
    /** Handler and restart registries.
     *
     * @var Router $router Handler tree.
     * @var Restarts $restarts Active restart list.
     */
    private static $router;
    private static $restarts;

    /** Static constructor.
     *
     * Initializes the static properties.
     *
     * @return void
     */
    private static function init()
    {
        self::$router = new Router();
        self::$restarts = new Restarts();
    }

    /** Define a restart option.
     *
     * The $className specifies a restart exception class (a subclass of
     * Restart) that will be thrown to invoke this restart.
     *
     * The return value of this method MUST be saved; the restart will be
     * disabled when the returned value is destroyed, typically with unset in a
     * finally block, or when control returns from the calling function.
     *
     * @param string $className Restart class.
     * @return ScopeGuard Restart handle.
     */
    public static function restart($className)
    {
        if (! self::$restarts) {
            self::init();
        }

        $r = self::$restarts;
        return $r->add($className);
    }

    /** Define a signal handler function.
     *
     * Signal handlers receive notice of a signal and its arguments, and decide
     * on a course of action.  Their signature is: function
     * signalHandler(Restarts $registry, string $signal, array $args).  The
     * $signal and $args are provided by the code sending the signal.
     *
     * If the signal handler invokes a restart, then control is passed out to
     * the restart code; otherwise, it is returned here and the next higher
     * signal handler may be invoked.
     *
     * If no signal handlers opt to take action, then receive() returns to the
     * caller.
     *
     * The return value of this method MUST be saved; the handler will be
     * unbound when the returned value is destroyed, typically with unset in a
     * finally block, or when control returns from the calling function.
     *
     * This is a fairly low-level mechanism used by send() to locate a handler.
     * For a slightly higher-level system, see warning() and error().
     *
     * @param callable $signalHandler Signal handler function.
     * @param array[string] $signals List of signals to handle.
     * @return ScopeGuard Signal handler handle.
     */
    public static function receive(callable $signalHandler, array $signals=null)
    {
        if (! self::$router) {
            self::init();
        }

        if ($signals !== null && count($signals) === 0) {
            trigger_error("Interpreting empty signals array as 'handle no signals'",
                E_USER_WARNING);
            return new NullScopeGuard(); // must return some ScopeGuard
        }

        $r = self::$router;
        return $r->add($signalHandler, $signals);
    }

    /** Send a signal to the signal handlers.
     *
     * If no handlers opt to invoke a restart, then this function returns.  The
     * same happens if no handlers are active.
     *
     * @param string $signal Signal sent.
     * @param array $args Arguments to pass to the signal handler code.
     * @return void
     */
    public static function send($signal, array $args=null)
    {
        if (! self::$router) {
            return; // nothing ever registered, for sure
        }

        $r = self::$router;
        $r->invoke($signal, $args, self::$restarts);
    }

    /** Send an error signal.
     *
     * This function will not return.  If the error is not handled (and a
     * restart called), then PHP's exit function is called.
     *
     * @param string $message Error message.
     * @param array $data Auxiliary data to pass to signal handlers.
     */
    public static function error ($message, array $data=null) {
        self::send('error', [$message, $data]);

        // Unhandled error!  Log and quit immediately.
        error_log("Error: $message");
        exit(1);
    }

    /** Send a warning signal.
     *
     * If the 'Silence' restart is not invoked, the warning message will be
     * printed.
     *
     * @param string $message Warning message to be printed.
     * @return void
     */
    public static function warning ($message) {
        try {
            $guard = self::restart(__NAMESPACE__.'\\Silence');
            self::send('warning', [$message]);

            error_log($message);
        } catch (Silence $e) {
            // nothing: message silenced
        } finally {
            unset($guard);
        }
    }
}
