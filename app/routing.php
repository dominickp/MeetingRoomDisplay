<?php

/**
 * This file should be included from app.php, and is where you hook
 * up routes to controllers.
 *
 * @link http://silex.sensiolabs.org/doc/usage.html#routing
 * @link http://silex.sensiolabs.org/doc/providers/service_controller.html
 */

$app->get('/{room_name}', 'app.calendar_controller:indexAction');
$app->get('/get-room/{room_name}', 'app.calendar_controller:getRoomAction');
