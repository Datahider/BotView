<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\BotView;
use PHPUnit\Framework\TestCase;
/**
 * Description of BotViewTest
 *
 * @author drweb
 */
class BotViewTest extends TestCase {
    
    protected $bot_api;
    protected $test_chat = 203645978;
    protected $test_forum = -1001919666727;
    protected $test_thread = 2;

    protected function assertPreConditions(): void {
        $this->bot_api = new \TelegramBot\Api\BotApi(BOT_TOKEN);
        $this->bot_api->setCurlOption(CURLOPT_CAINFO, 'cacert.pem');
        BotView::setTemplateSuffix('.php');
        parent::assertPreConditions();
    }
    
    public function testPlainMessage() {
        $bot_view = new BotView($this->bot_api, $this->test_chat, 'ru');
        $this->assertIsInt(
            $bot_view->show('test-message', null, [ 'test_number' => 1 ])
        );
    }
    
    public function testMessageWithKeyboard() {
        $bot_view = new BotView($this->bot_api, $this->test_chat, 'ru');
        $this->assertIsInt(
            $bot_view->show('test-message', 'test-keyboard', [ 
                'test_number' => 2, 
                'keyboard' => 2 
            ])
        );
    }

    public function testMessageInEnglish() {
        $bot_view = new BotView($this->bot_api, $this->test_chat, 'en');
        $this->assertIsInt(
            $bot_view->show('test-message', 'test-keyboard', [ 
                'test_number' => 3, 
                'keyboard' => 2 
            ])
        );
    }
    
    public function testEditMessage() {
        $bot_view_en = new BotView($this->bot_api, $this->test_chat, 'en');
        $this->assertIsInt(
            $msg_id = $bot_view_en->show('test-message', 'test-keyboard', [ 
                'test_number' => 4, 
                'keyboard' => 2 
            ])
        );
        
        $bot_view_en = new BotView($this->bot_api, $this->test_chat, 'ru');
        $this->assertIsInt(
            $bot_view_en->show('test-message', 'test-keyboard', [
                'test_number' => 4,
                'keyboard' => 2
            ], $msg_id)
        );
    }
    
    public function testPlainMessageFromAnotherSet() {
        BotView::setTemplateSuffix('.tpl');
        BotView::setTemplateDir(__DIR__. '/tpl2');
        
        $bot_view = new BotView($this->bot_api, $this->test_chat, 'ru');
        $this->assertIsInt(
            $bot_view->show('test-message', null, [ 'test_number' => 5 ])
        );

        BotView::setTemplateSuffix('.php');
        BotView::setTemplateDir('templates');
    }

    public function testEditNonExistentMessage() {
        $bot_view = new BotView($this->bot_api, $this->test_chat, 'ru');
        $message_to_edit = $bot_view->show('test-message', null, [ 'test_number' => 6 ]);
        
        $this->bot_api->deleteMessage($this->test_chat, $message_to_edit);
        
        // trying to edit deleted message
        $this->assertIsInt(
            $bot_view->show('test-message', null, ['test_number' => 7], $message_to_edit)
        );
    }
    
    protected function assertPostConditions(): void {
        parent::assertPostConditions();
    }
    
}
