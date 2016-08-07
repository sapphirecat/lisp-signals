<?php
namespace Sapphirecat\Signal;

use stdClass;

/** Built-in, minimal error class.
 *
 * @see Signal::error()
 */
class Error extends stdClass implements SignalInterface
{
    /** @var string $message Message passed to the constructor. */
    protected $message = '';

    /** Constructor.
     *
     * Applies any key/value pairs in $data as object properties.  The `message`
     * key will be overwritten with the $message argument.
     *
     * @see send()
     */
    public function __construct($message, array $data=null)
    {
        if ($data) {
            foreach ($data as $prop => $value) {
                $this->$prop = $value;
            }
        }

        // overrides $data['message']
        $this->message = $message;
    }

    /** Get the error message.
     *
     * @return string The error message contained in this object.
     */
    public function getMessage()
    {
        return $this->message;
    }

    /** Log our message and exit PHP.
     *
     * Invoked when the caller has failed to find a signal handler for us.
     *
     * Does not return.
     *
     * @see Signal::error()
     */
    public function doUnhandled()
    {
        error_log("Uncaught error: $this->message");
        exit(1);
    }
}
