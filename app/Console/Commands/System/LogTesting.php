<?php

namespace App\Console\Commands\System;

use Log;
use App;
use Config;
use Exception;
use ErrorException;
use Monolog\Logger;
use App\Console\Commands\CoreCommand;

class LogTesting extends CoreCommand
{

    protected $name = "Log Testing";
    protected $signature = 'system:log {--level=: Severity level of which to test}';
    protected $description = 'Testing Various Triggers to The Exception Handler';

    public function handle()
    {      
        $level = $this->option('level');
        switch($level){

            //Logging
            case 'emergency':
                $this->emergency("** Emergency ** Site is  down"); //System is unusable	
            break;
            case 'alert':
                $this->alert("Alert Test "); //Action must be taken immediately, corrupted database.
            break;
            case 'critical':
                $this->critical("Critical Test");
            break;
            case 'error':
                $this->error("Error Test");
            break;

            // '-v' required 
            case 'warning':
                $this->warning("Warning Test");
            break;
            case 'notice':
                $this->notice("Notice Test");
            break;
            // '-vv' required 
            case 'info':
                $this->info("Info Test");
            break;
            // '-vvv' required 
            case 'debug':
                $this->debug("Debug Test");
            break;

            //Exceptions

            case "exception":
                throw new Exception("Simple Exception");
            break;
            case "errorException":
                throw new ErrorException("ErrorException Class");
            break;
            case "errorLog":
                 trigger_error("Error Log Test", E_USER_NOTICE);
            break;

            //Debug
            case "dailylocation":

                dump(storage_path() . DIRECTORY_SEPARATOR . 'logs'. DIRECTORY_SEPARATOR . getenv('APP_NAME').'-'.getenv('APP_ENV').'.log');

            break;

            default:
                dump("please set --level [" . Logger::INFO . "]");
            break;
        }

        $this->finished();

    }
}
