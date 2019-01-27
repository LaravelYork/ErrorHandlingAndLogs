<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Console\Commands\Traits\MeasuresElaspedTime;
use  App\Exceptions\Traits\Loggable;


class CoreCommand extends Command
{
    use MeasuresElaspedTime, Loggable;

    protected $name = "CoreCommand";

    public function run(InputInterface $input, OutputInterface $output)
    {   
        $this->init();
        return parent::run($input,$output);
    }

    public function init()
    {

        $this->beginMeasuringElapsedTime();
        
        DB::disableQueryLog();
    
    }

    public function finished()
    {
        $this->finishMeasuringElapsedTime();
    }

    
}
