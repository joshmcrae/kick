<?php

use Kick\Test\ServiceD;

return fn (ServiceD $serviceD) => $serviceD->serviceC->serviceB->config;
