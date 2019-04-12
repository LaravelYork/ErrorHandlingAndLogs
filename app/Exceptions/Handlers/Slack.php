<?php

namespace App\Exceptions\Handlers;

use Config;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SocketHandler;

class Slack extends SocketHandler
{
  /**
   * Slack API token
   * @var string
   */
    private $token;

  /**
   * Slack channel (encoded ID or name)
   * @var string
   */
    private $channel;

  /**
   * Name of a bot
   * @var string
   */
    private $username;

  /**
   * Emoji icon name
   * @var string
   */
    private $iconEmoji;

  /**
   * Whether the message should be added to Slack as attachment (plain text otherwise)
   * @var bool
   */
    private $useAttachment;

  /**
   * Whether the the context/extra messages added to Slack as attachments are in a short style
   * @var bool
   */
    private $useShortAttachment;

  /**
   * Whether the attachment should include context and extra data
   * @var bool
   */
    private $includeContextAndExtra;

  /**
   * @var LineFormatter
   */
    private $lineFormatter;

  /**
   * @param  string                    $token                  Slack API token
   * @param  string                    $channel                Slack channel (encoded ID or name)
   * @param  string                    $username               Name of a bot
   * @param  bool                      $useAttachment          Whether the message should be added to Slack as attachment (plain text otherwise)
   * @param  string|null               $iconEmoji              The emoji name to use (or null)
   * @param  int                       $level                  The minimum logging level at which this handler will be triggered
   * @param  bool                      $bubble                 Whether the messages that are handled can bubble up the stack or not
   * @param  bool                      $useShortAttachment     Whether the the context/extra messages added to Slack as attachments are in a short style
   * @param  bool                      $includeContextAndExtra Whether the attachment should include context and extra data
   * @throws MissingExtensionException If no OpenSSL PHP extension configured
   */
    public function __construct($token, $channel, $username = 'Monolog', $useAttachment = true, $iconEmoji = null, $level = Logger::CRITICAL, $bubble = true, $useShortAttachment = false, $includeContextAndExtra = false)
    {
        if (!extension_loaded('openssl')) {
            throw new MissingExtensionException('The OpenSSL PHP extension is required to use the SlackHandler');
        }

        parent::__construct('ssl://slack.com:443', $level, $bubble);

        $this->token = $token;
        $this->channel = $channel;
        $this->username = $username;
        $this->iconEmoji = trim($iconEmoji, ':');
        $this->useAttachment = $useAttachment;
        $this->useShortAttachment = $useShortAttachment;
        $this->includeContextAndExtra = $includeContextAndExtra;

        //  if ($this->includeContextAndExtra && $this->useShortAttachment) {
        
          $dateFormat = "Y n j, g:i a";
          // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
            
          $output = "%message%\n";

          $this->lineFormatter = new LineFormatter($output, $dateFormat);
        //  }
    }

  /**
   * {@inheritdoc}
   *
   * @param  array  $record
   * @return string
   */
    protected function generateDataStream($record)
    {
        $content = $this->buildContent($record);

        return $this->buildHeader($content) . $content;
    }

  /**
   * Builds the body of API call
   *
   * @param  array  $record
   * @return string
   */
    private function buildContent($record)
    {
        $dataArray = $this->prepareContentData($record);

        return http_build_query($dataArray);
    }

  /**
   * Prepares content data
   *
   * @param  array $record
   * @return array
   */
    protected function prepareContentData($record)
    {
        $dataArray = [
          'token'       => $this->token,
          'channel'     => $this->channel,
          'username'    => $this->username,
          'text'        => '',
          'attachments' => [],
        ];

        $message = $this->lineFormatter->format($record);
        if ($message == "" && isset($record['context']) && isset($record['context']['Exception'])) {
            $message = $record['context']['Exception'] . " was thrown.";
        }

        if ($this->useAttachment) {
            $main_attachment = [
            
              'fallback' => $message,
              'color'    => $this->getAttachmentColor($record['level']),
              'fields'   => [],
              'pretext' => "New Server Exception",
              'text'=> $message,
              'ts' => date('U')
              //'author_name' => 'Production Server',
              //'author_icon' => ':warning:'
            ];
            $stack_attachment = [];
            $debug_attachment = [];
          
            if ($this->includeContextAndExtra) {
                if (!empty($record['extra'])) {
                    // Add all extra fields as individual fields in attachment
                    foreach ($record['extra'] as $var => $val) {
                        $field = [
                          'title' => $var,
                          'value' => $val,
                          'short' => true,
                        ];
                          
                        if ($var == "Stack") {
                            $field['short'] = false;
                        }
                        
                        $attachment['fields'][] = $field;
                    }
                }

                if (!empty($record['context'])) {
                    $stack_attachment = ['color'=>'#1353B6'];
                    $debug_attachment = ['color'=>'#05613D'];
                  
                          // Add all context fields as individual fields in attachment
                    foreach ($record['context'] as $var => $val) {
                        if (is_array($val)) {
                              $val = json_encode($val);
                        }
                        
                        $field = [
                        'title' => $var,
                        'value' => $val,
                        'short' => true,
                        ];
                          
                        if (stristr($var, "Stack")) {
                            $field['short'] = false;
                            $stack_attachment['fields'][] = $field;
                        } else {
                            $debug_attachment['fields'][] = $field;
                        }
                    }
                }
            }

            $dataArray['attachments'] = json_encode([$main_attachment,$debug_attachment,$stack_attachment]);
        } else {
            $dataArray['text'] = $message;
        }

        if ($this->iconEmoji) {
            $dataArray['icon_emoji'] = ":{$this->iconEmoji}:";
        }
      

          return $dataArray;
    }

  /**
   * Builds the header of the API Call
   *
   * @param  string $content
   * @return string
   */
    private function buildHeader($content)
    {
        $header = "POST /api/chat.postMessage HTTP/1.1\r\n";
        $header .= "Host: slack.com\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($content) . "\r\n";
        $header .= "\r\n";

        return $header;
    }

  /**
   * {@inheritdoc}
   *
   * @param array $record
   */
    protected function write(array $record)
    {
        parent::write($record);
        $res = $this->getResource();
        if (is_resource($res)) {
            @fread($res, 2048);
        }
        $this->closeSocket();
    }

  /**
   * Returned a Slack message attachment color associated with
   * provided level.
   *
   * @param  int    $level
   * @return string
   */
    protected function getAttachmentColor($level)
    {
        switch (true) {
            case $level >= Logger::ERROR:
                return 'danger';
            case $level >= Logger::WARNING:
                return 'warning';
            case $level >= Logger::INFO:
                return 'good';
            default:
                return '#e3e4e6';
        }
    }

  /**
   * Stringifies an array of key/value pairs to be used in attachment fields
   *
   * @param  array  $fields
   * @return string
   */
    protected function stringify($fields)
    {
        $string = '';
        foreach ($fields as $var => $val) {
            $string .= $var.': '.$this->lineFormatter->stringify($val)." | ";
        }

        $string = rtrim($string, " |");

        return $string;
    }
}
