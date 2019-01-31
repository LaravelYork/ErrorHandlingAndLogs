<?php

namespace App\Exceptions;

use App;
use Config;
use Exception;

use App\Exceptions\Traits\Loggable;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

class Handler extends ExceptionHandler
{   

    use Loggable;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [

        //Illuminate
        AuthenticationException::class,
        ValidationException::class,
        AuthorizationException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class,

        //Symfony
        HttpException::class,
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

        if ($this->shouldReport($e)) {

            $log_detail = [
                'Exception' => get_class($e),
                'Environment' => ucfirst(App::environment()),
                'App' => Config::get('aha.name'),
                'Console'=> App::runningInConsole() ? 'Yes': 'No',
                'Server' => gethostname(),
                'IP' => (isset($_SERVER) && isset($_SERVER['SERVER_ADDR'])) ? $_SERVER['SERVER_ADDR'] : gethostbyname(Config::get('app.url')),
                'Ajax'=> (request()->ajax() || request()->wantsJson()) ? 'Yes' : 'No',
                'Api'=> (request()->is('api') || request()->is('api/*')) ? 'Yes' : 'No'
            ];

            $this->error($e->getMessage(), $log_detail);
        }

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

    protected function renderExceptionWithWhoops(Exception $e)
    {
        $whoops = new Run;
        $whoops->pushHandler(new PrettyPageHandler());

        return response(
            $whoops->handleException($e),
            $e->getStatusCode(),
            $e->getHeaders()
        );
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
