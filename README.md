# lisp-signals

A signal/condition/restart system, inspired by Common Lisp and
[Peter Seibel](http://gigamonkeys.com/book/).

# What even?

In most languages, PHP included, an exception is _thrown_ at a low level and
_caught_ higher on the call stack.  By the time it is caught, execution has
left the lower levels, and there's no way to return to them.

In Common Lisp, the equivalent notions are that a signal is sent, and handled
by a signal handler function.  But, there's a third part, **restarts,** which
can be registered at any level in between.  The signal handler can choose any
restart to invoke, and execution resumes _inside that function,_ even if it's
below the level of where the signal handler was registered.

The simplest example is implemented in the
[appDoesNothing.php](./examples/appDoesNothing.php) example, using stuff
defined in [common.inc.php](./examples/common.inc.php).  All of the examples
follow the log-parsing example in
[Chapter 19](http://gigamonkeys.com/book/beyond-exception-handling-conditions-and-restarts.html)
of Practical Common Lisp.

# I'm a lisper, how does it happen in PHP?

Restarts are a special exception class that gets thrown, and declaring the
restart is actually writing a try/catch block and telling the Signal class the
restart exists.  Low-level code calls _downward_ into the signal-handling
stuff to send errors or other conditions, so the stack stays intact.

I took another liberty with the Common Lisp design: signal names are actually
the class name of a signal object that is sent.  So an `error` is really a
`Sapphirecat\Signal\Error` instance.  This allows for signals to package
however much data they want, and provide behavior for it, all in a single
argument to the signal system (and handler functions.)

In this library, a signal handler is bound by `Signal::receive()`, signals are
sent with `Signal::send()`, and active restarts are declared by
`Signal::restart()`.  The condition is a `SignalInterface` and a restart is
actually invoked by throwing a `BaseRestart`.

The error and warning protocols are implemented in `Signal::error()` and
`Signal::warning` (with a `Silence` restart), respectively.  Finally, the
built-in `Error` condition can be subclassed, and sent with
`Signal::sendError()`.

(I hope this terminology is accurate.  I haven't gone deep into Lisp in a
while.)

# Installation

Get it from composer:

    composer require sapphirecat/lisp-signals "~0.9.0"

Alternatively, include the `autoload.php` in this repository's top level, and
enjoy.

# License

2-clause BSD.  If it breaks, you get to keep the pieces.
