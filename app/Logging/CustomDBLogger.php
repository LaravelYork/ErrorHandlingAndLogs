<?php

namespace App\Logging;

use Monolog\Logger;
use Exception;
use Config;
use App\Exceptions\Handlers\Slack;
use App\Exceptions\Handlers\DatabaseHandler;

class CustomDBLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {

        try {

            $databaseHandler = new DatabaseHandler($config['level']);

            return new Logger("Server Exception", [$databaseHandler]);

        } catch(Exception $e) {

            return new Logger("An error occurred: ". $e->getMessage());
        }
    }
}