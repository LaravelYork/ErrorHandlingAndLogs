<?php

namespace App\Exceptions\Traits;

use Exception;
use App;
use Log;
use App\Exceptions\Handler;

trait Loggable {

   // https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#log-levels

    protected $is_console;
    protected $testing;
    
    /*
    *  System is unusable, should switch to maintenance mode, urgent action needed.
    */
    public function logEmergency($string, $debug = [])
    {   

        $debug = $this->addMeta($debug);

        Log::emergency($string, $debug);

        if ($this->isConsole()) {

            $this->alert(" ** 🚨  EMERGENCY  🚨 ** ");
            
            $this->line($string, 'error', 'normal');

           // App::down();

        }
    }

    /*
    *  System has reduced functionality, multiple features are failing, urgent action needed. 
    *  Unhandled deadlock
    */
    public function logAlert($string, $debug = [])
    {   

        $debug = $this->addMeta($debug);

        Log::alert($string, $debug);

        if ($this->isConsole()) {

            $this->alert(" 🚨 ALERT ");
            $this->line($string, 'error', 'normal');

        }
    }

    /*
    *  A feature has stopped working, action needed.
    */
    public function logCriticalError($string, $debug = [])
    {   

        $debug = $this->addMeta($debug);

        Log::critical($string, $debug);

        if ($this->isConsole()) {

            $this->alert(" CRITICAL ERROR ");
            $this->line($string, 'error', 'normal');

        }
    }

    /*
    *   An unexpected result occurred when trying to perform a necessary task
    */

    public function logError($string, $debug = [])
    {   

        $debug = $this->addMeta($debug);
        Log::error($string,  $debug);

        if ($this->isConsole()) {
            
            $this->alert("ERROR");

            $this->line($string, 'error', 'normal');
        }
    }

    // '-v' required 

    /*
    *    
    */

    public function logWarning($string, $debug = [])
    {   

        $debug = $this->addMeta($debug);

        Log::warning($string, $debug);

        if ($this->isConsole()) {

            $this->warn($string, 'v');

        }
    }
    public function logNotice($string, $debug = [])
    {   
      
        Log::notice($string, array_merge(['command'=>$this->name], $debug));
        if ($this->isConsole()) {
            $this->warn($string, 'v');
           
        }
    }

    // '-vv' required 

    public function logInfo($string, $debug = [], $verbosity = null)
    {
        Log::info($string, $this->addMeta($debug));

        if ($this->isConsole()) {
           $this->line($string, 'info', 'vv');
        }
    }

    // '-vvv' required 

    public function logDebug($string, $debug = [])
    {   

        $debug = $this->addMeta($debug);
        
        //config debug?

        Log::debug($string, $debug);

        if ($this->isConsole()) {
            $this->line($string, 'question', 'vvv');

            dump($debug);

        }
    }

    protected function isConsole(){

        if(is_null($this->is_console)){
            $this->is_console = App::runningInConsole();
        }

        return $this->is_console;

    }

    protected function addMeta(Array $debug){
        
        $meta_info = [];
        if ($this->isConsole()) {
            
            $meta_info['command'] = $this->name;
            $meta_info['timeElapsed'] = $this->getElapsedTime();

        }
        return array_merge($meta_info, $debug);

    }

    public static function parseException(Exception $e)
    {
    
         $debug = [
            'exception_class' => get_class($e),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'stack' => Handler::traceStack($e)
            ];

         Log::error($e->getMessage(), $debug);
    }
}