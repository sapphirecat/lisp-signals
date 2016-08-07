<?php
spl_autoload_register(function ($name) {
    if (substr_compare($name, 'Sapphirecat\\Signal\\', 0, 19) === 0) {
        // PHAR URLs require '/' and win32 accepts it; this is universal now.
        include(__DIR__.'/'.str_replace('\\', '/', substr($name, 19)));
    }
});
