<?php

use Kick\Http\Response;

return fn () => new Response(302, ['location' => '/login']);

