<?php

require __DIR__ . '/autoload.php';

// import a calendar so we have something to work with.
// this should be a calendar which does not conflict with our tests.
passthru(sprintf(
    'php "%s/../bin/console" calendar:import ics %s -vv',
    __DIR__,
    __DIR__ . "/../data/cj@icj.me.ics"
));

