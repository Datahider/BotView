<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\BotView;
use losthost\templateHelper\Template;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\HttpException;

/**
 * Description of newPHPClass
 *
 * @author drweb
 */
class BotView {
    
    const FIBO = [1, 2, 3, 5, 8, 13, 21, 34, 55, 89, 144];
    static protected $template_suffix = '.php';
    static protected $template_dir = 'templates';
    protected $chat_id;
    protected $api;
    protected $lang;
    protected $last_error;


    /**
     * The constructor
     * 
     * @param BotApi     $api             - an istance to TelegramBot\Api\BotApi
     * @param int|string $chat_id         - a chat id to show data to
     * @param string $lang - language code (defaults to <b>default</b>
     */
    public function __construct(BotApi $api, int|string $chat_id, $lang='default') {
        $this->api = $api;
        $this->chat_id = $chat_id;
        $this->lang = $lang;
    }
    
    /**
     * Displays a message to the chat. It can post a new message or edit 
     * the existing one (if $message_id is given)
     * 
     * @param string $message_template  - message template file name (will be appended by template suffix. See __constructor)
     * @param string $keyboard_template - keyboard template file name (will be appended by template suffix. See __constructor)
     *                                      The keyboard template must display a serialized instance of TelegramBot\Api\Types\Inline\InlineKeyboardMarkup 
     * @param array|null $data          - data array to use in templates. Each key will be assigned as an internal template var
     *                                      Ex. [ 'name' => 'sample', 'value' => 2 ] will assign internal template variables 
     *                                      $name = 'sample'; $value = 2;
     * @param int|null $message_id      - the id of the message to edit. If not given will post a new message
     * 
     * @return int                      - returns the id of posted or edited message
     */
    public function show(string $message_template, string $keyboard_template=null, ?array $data=[], ?int $message_id=null) : int|false {

        $count = 0;

        while (true) {
            $msg_id = $this->tryToShow($message_template, $keyboard_template, $data, $message_id);
            if ($msg_id) {
                return $msg_id;
            }

            if ($this->last_error == 'Bad Request: message to edit not found') {
                $message_id = null;
            } elseif ($this->last_error == 'Bad Request: message is not modified: specified new message content and reply markup are exactly the same as a current content and reply markup of the message') {
                return $message_id;
            } elseif ($this->last_error == 'Forbidden: bot was blocked by the user') {
                return false;
            } else {
                sleep(static::FIBO[$count]);
                $count++;
                if ($count >= count(static::FIBO)) {
                    throw new HttpException($this->last_error);
                }
            }
        }
    }

    protected function tryToShow(string $message_template, string $keyboard_template=null, ?array $data=[], ?int $message_id=null) : int|false {

        $text = $this->processTemplate($message_template. static::$template_suffix, $data);
        
        if ($keyboard_template !== null) {
            $reply_markup = unserialize($this->processTemplate($keyboard_template. BotView::$template_suffix, $data));
        } else {
            $reply_markup = null;
        }
        
        try {
            if ($message_id === null) {
                $response = $this->api->sendMessage($this->chat_id, $text, 'HTML', false, null, $reply_markup);
                return $response->getMessageId();
            } else {
                $response = $this->api->editMessageText($this->chat_id, $message_id, $text, 'HTML', false, $reply_markup);
                return $response->getMessageId();
            }
        } catch(HttpException $e) {
            $this->last_error = $e->getMessage();
            return false;
        }
    }
    
    protected function processTemplate(string $template, array $data) {
        $tpl_object = new Template($template, $this->lang);
        $tpl_object->setTemplateDir(static::$template_dir);
        $this->assignData($tpl_object, $data);
        return $tpl_object->process();
    }
    
    protected function assignData(Template &$template_object, array $data_array) {
        foreach ($data_array as $key => $value) {
            $template_object->assign($key, $value);
        }
    }
    
    static public function setTemplateSuffix($suffix) {
        static::$template_suffix = $suffix;
    }
    
    static public function setTemplateDir($dir) {
        static::$template_dir = $dir;
    }
    
}
