<?php
namespace Sapphirecat\Signal;

/** Scope guard which takes no action.
 *
 * Exists to allow for returning a "null" ScopeGuard when Signal::receive() is
 * not actually establishing any signal handlers (due to a logic error on the
 * part of the caller.)
 */
class NullScopeGuard implements ScopeGuard
{
}
