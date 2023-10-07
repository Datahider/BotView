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
    
    protected $chat_id;
    protected $api;
    
    public function __construct(BotApi $api, int|string $chat_id) {
        $this->api = $api;
        $this->chat_id = $chat_id;
    }
    
    public function show(string $lang, string $message_template, string $keyboard_template=null, ?array $data=[], ?int $mot=null, bool $is_thread=false) {

        $text = $this->processTemplate($lang, $message_template, $data);
        
        if ($keyboard_template !== null) {
            $reply_markup = unserialize($this->processTemplate($lang, $keyboard_template, $data));
        } else {
            $reply_markup = null;
        }
        
        if ($mot === null) {
            $response = $this->api->sendMessage($this->chat_id, $text, 'HTML', false, null, $reply_markup);
            return $response->getMessageId();
        } elseif ($is_thread) {
            $response = $this->api->sendMessage($this->chat_id, $text, 'HTML', false, null, $reply_markup, false, $mot);
            return $response->getMessageId();
        } else {
            $response = $this->api->editMessageText($this->chat_id, $mot, $text, 'HTML', false, $reply_markup);
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
