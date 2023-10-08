<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\BotView;
use losthost\templateHelper\Template;
use TelegramBot\Api\BotApi;

/**
 * Description of newPHPClass
 *
 * @author drweb
 */
class BotView {
    
    protected $template_suffix;
    protected $chat_id;
    protected $api;
    
    /**
     * The constructor
     * 
     * @param BotApi $api           - an istance to TelegramBot\Api\BotApi
     * @param int|string $chat_id   - a chat id to show data to
     * @param type $template_suffix - a template suffix (optional, defaults to .php)
     */
    public function __construct(BotApi $api, int|string $chat_id, $template_suffix='.php') {
        $this->api = $api;
        $this->chat_id = $chat_id;
        $this->template_suffix = $template_suffix;
    }
    
    /**
     * Displays a message to the chat. It can post a new message or edit 
     * the existing one (if $message_id is given)
     * 
     * @param string $lang              - a language code
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
    public function show(string $lang, string $message_template, string $keyboard_template=null, ?array $data=[], ?int $message_id=null) : int {

        $text = $this->processTemplate($lang, $message_template. $this->template_suffix, $data);
        
        if ($keyboard_template !== null) {
            $reply_markup = unserialize($this->processTemplate($lang, $keyboard_template. $this->template_suffix, $data));
        } else {
            $reply_markup = null;
        }
        
        if ($message_id === null) {
            $response = $this->api->sendMessage($this->chat_id, $text, 'HTML', false, null, $reply_markup);
            return $response->getMessageId();
        } else {
            $response = $this->api->editMessageText($this->chat_id, $message_id, $text, 'HTML', false, $reply_markup);
            return $response->getMessageId();
        }
    }
    
    protected function processTemplate(string $lang, string $template, array $data) {
        $tpl_object = new Template($template, $lang);
        $this->assignData($tpl_object, $data);
        return $tpl_object->process();
    }
    
    protected function assignData(Template &$template_object, array $data_array) {
        foreach ($data_array as $key => $value) {
            $template_object->assign($key, $value);
        }
    }
}
