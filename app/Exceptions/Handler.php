<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }

    public static function traceStack($exception)
    {
        $d = [];
        $i = 0;

        $trace = array_reverse($exception->getTrace());

        foreach ($trace as $t) {

            if (isset($t['class']) && !preg_match("/^App/", $t['class'])){
                continue;
            }

            if (isset($t['function']) && (stristr($t['function'], '{main}') || stristr($t['function'], 'call_user_func') || stristr($t['function'], 'call_user_func') || stristr($t['function'], 'call_user_func_array') || stristr($t['function'], 'closure'))) {
                continue;
            }

            $d[$i] = '';
            if (isset($t['class'])) {
                $d[$i] .= $t['class'].'->';
            }
            if (isset($t['function'])) {
                $d[$i] .=  $t['function'].'()';
            }
            if (isset($t['line'])) {
                $d[$i] .= ' @ line '.$t['line'].'';
            }

            ++$i;
        }

        $file = $exception->getFile();
        if (!stristr($file, 'filters')) {
            $d[$i] = $file;
            $d[$i] .= ' @ line '.$exception->getLine();
        }

        return array_reverse($d);
    }
}
