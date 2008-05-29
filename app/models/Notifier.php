<?php

class Notifier extends Mad_Mailer_Base
{
    /**
     * This test message uses strings for all email lists
     * 
     * @param   User    $user
     */
    public function confirm($user)
    {
        $this->subject      = "Confirmation for $user->name";
        $this->body['user'] = $user;
        $this->body['url']  = 'http://maintainable.com';

        $this->recipients   = 'derek@maintainable.com';
        $this->from         = 'test@example.com';
        $this->cc           = 'test1@example.com';

        $this->sentOn       = time();
        $this->headers      = array();
    }

    /**
     * @param   User    $user
     */
    public function send($user)
    {
        $this->subject      = "Confirmation for $user->name";
        $this->body['user'] = $user;
        $this->body['url']  = 'http://maintainable.com';

        $this->recipients   = array('derek@maintainable.com', 'Mike Naberezny <mike@maintainable.com>');
        $this->from         = 'test@example.com';
        $this->cc           = array('test1@example.com', 'test2@example.com');
        $this->bcc          = array('test3@example.com', 'test4@example.com');

        $this->sentOn       = strtotime("-30 days");
        $this->headers      = array('Organization' => 'Maintainable, LLC');
    }
    
    public function sendWithAttachments() 
    {
        $this->subject      = "Confirmation for test";
        $this->body['url']  = 'http://maintainable.com';

        $this->recipients   = 'derek@maintainable.com';
        $this->from         = 'test@example.com';

        $this->attachment(array('contentType' => 'text/plain',
                                'body'        => 'the attachment',
                                'filename'    => 'check_it_out.txt'));
    }

    public function sendWithUniqueAttachmentNames($user) 
    {
        $this->subject      = "Confirmation for $user->name";
        $this->body['user'] = $user;
        $this->body['url']  = 'http://maintainable.com';

        $this->recipients   = 'derek@maintainable.com';
        $this->from         = 'test@example.com';

        $this->attachment(array('contentType' => 'text/plain',
                                'body'        => 'the attachment',
                                'filename'    => 'check_it_out.txt'));

        $this->attachment(array('contentType' => 'text/plain',
                                'body'        => 'another attachment',
                                'filename'    => 'check_it_out.txt'));

        $this->attachment(array('contentType' => 'text/plain',
                                'body'        => 'the attachment',
                                'filename'    => 'check_it_out.txt'));
    }
}
