<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use App\Dispatcher;
use App\Output;
use App\SiteMapGrabber;

$dispatcher = new Dispatcher(new SiteMapGrabber(), new Output());
$dispatcher->run();
