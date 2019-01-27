<?php

namespace App\Console\Commands\Traits;

trait MeasuresElaspedTime {

    public $start_time;

    public function beginMeasuringElapsedTime(){

        $this->start_time = microtime(true);
    }

    public function finishMeasuringElapsedTime()
    {
        $this->question(sprintf("[%s] ~ Completed in %s" , $this->name , $this->getElapsedTime()));
    }

    public function getElapsedTime($format = true)
    {
        $start = $this->start_time;

        $finish = microtime(true);

        if ($format == true) {
            return $this->time_since($start, $finish);
        } else {
            return $finish - $start;
        }

        return number_format(microtime(true) - $this->starttime, 4);
    }

    protected function time_since($start, $finish)
    {
        $hash = [];
        $string = '';

        //how many mircoseconds in *
        $weeks = 6.048e+11;
        $days = 8.64e+10;
        $hours = 3600000000;
        $minutes = 60000000;
        $seconds = 1000000;
        $millisecond = 1000;

        $diff = intval((($finish - $start) * $seconds));

        $hash['week'] = intval($diff / $weeks);
        $diff = $diff % $weeks;

        $hash['day'] = intval($diff / $days);
        $diff = $diff % $days;

        $hash['hour'] = intval($diff / $hours);
        $diff = $diff % $hours;

        $hash['minute'] = intval($diff / $minutes);
        $diff = $diff % $minutes;

        $hash['second'] = intval($diff / $seconds);
        $diff = $diff % $seconds;

        $hash['millisecond'] = intval($diff / $millisecond);
        $diff = $diff % $millisecond;

        $hash['microsecond'] = intval($diff);

        foreach ($hash as $unit => $amount) {
            if ($amount > 0) {
                if ($amount > 1) {
                    $unit .= 's';
                }
                $string .= "$amount $unit ";
            }
        }

        return trim($string);
    }

    
} 