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
    
    public function __construct(BotApi $api, int|string $chat_id, $template_suffix='.php') {
        $this->api = $api;
        $this->chat_id = $chat_id;
        $this->template_suffix = $template_suffix;
    }
    
    public function show(string $lang, string $message_template, string $keyboard_template=null, ?array $data=[], ?int $message_id=null) {

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
