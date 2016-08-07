<?php
namespace Sapphirecat\Signal;

/** Interface for implementing restart exceptions.
 *
 * When a restart is called, a RestartInterface is thrown to transfer control up
 * to the code which defined the restart.  This interface is provided to allow
 * for implementing the restart exceptions independently of the BaseRestart
 * class provided in this package.
 */
interface RestartInterface
{
    public function getArgs();
}
