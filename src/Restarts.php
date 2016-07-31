<?php
namespace Sapphirecat\Signal;

use DomainException;

/** Active restarts registry. */
class Restarts
{
    private $set;

    /** Add a restart to the active set.
     *
     * @param string $type Restart type to be added.
     * @return ScopeGuard Value that removes the restart when destroyed.
     * @throws DomainException if the $type is already active.
     */
    public function add($type)
    {
        if (isset($this->set[$type])) {
            throw new DomainException("Restart already bound for $type");
        }

        $this->set[$type] = true;
        return new CallScopeGuard([$this, 'remove'], [$type]);
    }

    /** Remove a restart from the active set.
     *
     * @param string $type Restart type to be removed.
     * @return self
     * @throws DomainException if the $type is not active.
     */
    public function remove($type)
    {
        if (! isset($this->set[$type])) {
            throw new DomainException("No active restarts for $type");
        }

        unset($this->set[$type]);
        return $this;
    }

    /** Return a list of all active restarts.
     *
     * @return array[string] Restart types.
     */
    public function getAll()
    {
        return array_keys($this->set);
    }

    /** Return whether a given restart is active.
     *
     * @param string $type Restart type to check.
     * @return boolean TRUE if the restart is active, FALSE otherwise.
     */
    public function has($type)
    {
        return isset($this->set[$type]);
    }

    /** Invoke a restart.
     *
     * @param string $type Type of restart to be invoked.
     * @param array $args Arguments to be passed to the restart.
     * @throws BaseRestart to transfer control up the stack to code providing
     * the restart.
     */
    public function call($type, array $args=null)
    {
        if (! isset($this->set[$type])) {
            throw new DomainException("Restart is not active: $type");
        }

        // this is, as I said, the only "nonlocal return" I have, to transfer
        // control to the layer with the restart...
        throw new $type($args, "<restart for $type>");
    }
}
