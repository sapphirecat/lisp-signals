<?php
namespace Sapphirecat\Signal;

/** Built-in, minimal warning class.
 *
 * @see Signal::warning()
 */
class Warning implements SignalInterface
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
    public function __construct($message)
    {
        $this->message = $message;
    }

    /** Get the warning message.
     *
     * @return string Warning message contained in this object.
     */
    public function getMessage()
    {
        return $this->message;
    }
}
