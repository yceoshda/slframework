<?php

namespace spacelife\core;

use spacelife\exception\SLException;
/**
* Mails
*/
class Mails
{

    protected $smtp      = false;
    protected $sender    = false;
    protected $multimail = false;
    protected $recipient = false;
    protected $body      = false;
    protected $title     = false;

    protected $config = false;

    public function __construct($db, $config)
    {
        $this->config = $config;
        $this->smtp = $config->mail->server;
        $this->sender = $config->mail->from;

        if (isset($config->mailReplyTo)) {
            $this->replyto = $config->mail->replyTo;
        } else {
            $this->replyto = $config->mail->from;
        }

    }

    /**
    *   send
    *
    **/
    public function send($mail)
    {

        if (is_array($mail)) {
            $params = $this->loadFromArray($mail);
        } elseif (is_object($mail)) {
            $params = $this->loadFromObject($mail);
        }

        if ($params === false) {
            return false;
        }

        $headers = 'From: '.$this->config->mail->from_name.'<'.$this->sender.">\r\n";
        $headers .= 'Reply-To: '.$this->replyto."\r\n";
        $headers .= 'Return-Path: '.$this->replyto."\r\n";
        $headers .= 'X-Mailer: PHP/SLFramework'."\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf8' . "\r\n";
        if ($this->multimail) {
            $headers .= 'Bcc: '.$this->recipient."\r\n";
            $to = $this->replyto;
        } else {
            $to = $this->recipient;
        }

        // debug($this->title);

        //  send mail
        if (mail($to, "{$this->title}", "{$this->body}", $headers)) {
            // var_dump($this);
            return true;
        }

        // throw new SLException("Error sending email", 500);


        return false;
    }

    /**
    *   loadFromObject
    *
    **/
    protected function loadFromObject($mail)
    {
        if (!isset($mail->body)) {
            return false;
        }

        $this->body = $mail->body;
        if (!isset($mail->title)) {
            return false;
        }
        $this->title = $mail->title;

        if (!isset($mail->recipient)) {
            return false;
        }
        if (is_array($mail->recipient)) {
            if (count($mail->recipient) > $this->config->mail->maxRcpt) {
                return false;
            }
            if (count($mail->recipient) > 1) {
                $this->recipient = implode('; ', $mail->recipient);
                $this->multimail = true;
            } else {
                $this->recipient = $mail->recipient[0];
            }
        } else {
            $this->recipient = $mail->recipient;
        }

    }

    /**
    *   loadFromArray
    *
    **/
    protected function loadFromArray($mail)
    {
        if (!isset($mail['body'])) {
            return false;
        }
        $this->body = $mail['body'];

        if (!isset($mail['title'])) {
            return false;
        }
        $this->title = $mail['title'];

        if (!isset($mail->recipient)) {
            return false;
        }
        if (is_array($mail['recipient'])) {
            if (count($mail['recipient']) > $this->config->mail->maxRcpt) {
                return false;
            }
            $this->recipient = implode(',', $mail['recipient']);
            $this->multimail = true;
        } else {
            $this->recipient = $mail['recipient'];
        }

    }

}