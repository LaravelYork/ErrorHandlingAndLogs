<?php

namespace App\Exceptions\Handlers;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Exception;

class DatabaseHandler extends AbstractProcessingHandler
{
    private $table = 'logs';

    public function __construct($level = Logger::DEBUG, $bubble = true)
    {   
        parent::__construct($level, $bubble);
    }

    protected function write(array $record)
    {   

        $command = $record["channel"];
        if (empty($record['context'])) {
            $context = false;
        } else {
            $context = $record['context'];

            if (isset($context['command'])) {
                $command = $context['command'];
                unset($context['command']);
            }
        }

        try {
            DB::table($this->table)->insert([
                [
                    'command' => $command,
                    'type' => $record['level_name'],
                    'message' => $record['message'],
                    'info' => $context ? json_encode($context) : ""

                ],
            ]);
        } catch (Exception $e) {
            $flat_error = json_encode(compact("record","e"));
            error_log($flat_error);
            return false;
        }
    }
}
