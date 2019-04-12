<?php

namespace App\Logging;

use Monolog\Logger;
use Exception;
use Config;
use App\Exceptions\Handlers\Slack;
use App\Exceptions\Handlers\DatabaseHandler;

class CustomSlackLogger
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
            
            $slackHandler = new Slack(
                config('logging.slack_api_token'),  //$token
                config('logging.slack_api_channel'), //$channel
                'env-' . config('app.env'),  //$username
                true, //$useAttachment
                ':triangular_flag_on_post:', //$iconEmoji
                $config['level'], //$level
                true, //$bubble
                false, //$useShortAttachment
                true //$includeContextAndExtra
            );

            return new Logger("Server Exception", [$slackHandler]);

        } catch(Exception $e) {

            return new Logger("An error occurred: ". $e->getMessage());
        }
    }
}