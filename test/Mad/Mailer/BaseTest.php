<?php
/**
 * @category   Mad
 * @package    Mad_Mailer
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config/environment.php';
}

/**
 * @group      mailer
 * @category   Mad
 * @package    Mad_Mailer
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential
 */
class Mad_Mailer_BaseTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->fixtures('users');
    }
    
    public function testCreateUsingStringAttributes()
    {
        $n = new Notifier();
        $result = $n->createConfirm(User::find(1));

        // result has both headers/body
        $this->assertContains('Date:',      $result);
        $this->assertContains('Dear Mike,', $result);
        $this->assertEquals('derek@maintainable.com', $n->getRecipients());
        $this->assertEquals('Confirmation for Mike',  $n->getSubject());
    
        // headers
        $this->assertContains('Date:',                      $n->getHeaders());
        $this->assertContains('From: test@example.com',     $n->getHeaders());
        $this->assertContains('Cc: test1@example.com',      $n->getHeaders());
        $this->assertContains('Mime-Version: 1.0',          $n->getHeaders());
        $this->assertNotContains('Bcc:',                    $n->getHeaders());
    
        // body
        $this->assertContains('Content-Type: text/plain; charset="utf-8"', $n->getBody());
        $this->assertContains('Dear Mike,',              $n->getBody());
        $this->assertContains('http://maintainable.com', $n->getBody());
    }

    public function testCreateUsingArrayAttributes()
    {
        $n = new Notifier();
        $result = $n->createSend(User::find(1));
    
        // result has both headers/body
        $this->assertContains('Date:',      $result);
        $this->assertContains('Dear Mike,', $result);
        $this->assertEquals('derek@maintainable.com, Mike Naberezny <mike@maintainable.com>', $n->getRecipients());
        $this->assertEquals('Confirmation for Mike',  $n->getSubject());
    
        $this->assertContains('From: test@example.com',                    $n->getHeaders());
        $this->assertContains('Cc: test1@example.com, test2@example.com',  $n->getHeaders());
        $this->assertContains('Bcc: test3@example.com, test4@example.com', $n->getHeaders());
        $this->assertContains('Mime-Version: 1.0',                         $n->getHeaders());
        $this->assertContains('Organization: Maintainable, LLC',           $n->getHeaders());
    
        // body
        $this->assertContains('Content-Type: text/plain; charset="utf-8"',   $n->getBody());
        $this->assertContains('Dear Mike,',              $n->getBody());
        $this->assertContains('http://maintainable.com', $n->getBody());
    }

    public function testSendWithAttachments()
    {
        $n = new Notifier();
        $result = $n->createSendWithAttachments();
        
        $attachments = $n->getAttachments();
        $attachment = current($attachments);
        $this->assertEquals('text/plain',        $attachment['contentType']);
        $this->assertEquals('the attachment',    $attachment['body']);
        $this->assertEquals('check_it_out.txt',  $attachment['filename']);
        $this->assertEquals('base64',            $attachment['transferEncoding']);

        
        // result has both headers/body
        $this->assertContains('Dear Derek,', $result);
        $this->assertEquals('derek@maintainable.com', $n->getRecipients());
        $this->assertEquals('Confirmation for test',  $n->getSubject());

        // headers
        $this->assertContains('Date:',                      $n->getHeaders());
        $this->assertContains('From: test@example.com',     $n->getHeaders());
        $this->assertContains('Mime-Version: 1.0',          $n->getHeaders());

        // body
        $this->assertContains('Content-Type: text/plain; charset="utf-8"', $n->getBody());
        $this->assertContains('Dear Derek,',             $n->getBody());
        $this->assertContains('The Maintainable Team',   $n->getBody());
        
        // attachments
        $this->assertContains('Content-Transfer-Encoding: base64',                            $n->getBody());
        $this->assertContains('Content-Disposition: attachment; filename="check_it_out.txt"', $n->getBody());
    }

    public function testSendWithUniqueAttachmentNames()
    {
        $n = new Notifier();
        $result = $n->createSendWithUniqueAttachmentNames(User::find(1));

        $attachments = $n->getAttachments();

        $attachment1 = current($attachments);
        $attachment2 = next($attachments);
        $attachment3 = next($attachments);

        $this->assertEquals('check_it_out.txt',   $attachment1['filename']);
        $this->assertEquals('check_it_out-1.txt', $attachment2['filename']);
        $this->assertEquals('check_it_out-2.txt', $attachment3['filename']);
    }

    public function testDeliver()
    {
        $n = new Notifier();
        $result = $n->deliverConfirm(User::find(1));
    
        $this->assertTrue($result);
    
        // headers
        $this->assertContains('Date:',                      $n->getHeaders());
        $this->assertContains('From: test@example.com',     $n->getHeaders());
        $this->assertContains('Cc: test1@example.com',      $n->getHeaders());
        $this->assertContains('Mime-Version: 1.0',          $n->getHeaders());
        $this->assertNotContains('Bcc:',                    $n->getHeaders());
    
        // body
        $this->assertContains('Content-Type: text/plain; charset="utf-8"', $n->getBody());
        $this->assertContains('Dear Mike,',              $n->getBody());
        $this->assertContains('http://maintainable.com', $n->getBody());
    }

}