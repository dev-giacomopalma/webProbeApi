<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/api/mission' => [
            [['_route' => 'api_app_launchpad_getmovie', '_controller' => 'App\\Controller\\LaunchPadController::getMovieAction'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'api_app_launchpad_postmovie', '_controller' => 'App\\Controller\\LaunchPadController::postMovieAction'], null, ['POST' => 0], null, false, false, null],
        ],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/_error/(\\d+)(?:\\.([^/]++))?(*:35)'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        35 => [
            [['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
