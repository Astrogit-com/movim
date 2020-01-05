<?php

namespace Moxl\Xec\Action\Presence;

use Moxl\Xec\Action;
use Moxl\Stanza\Presence;

use Movim\Session;

class Muc extends Action
{
    protected $_to;
    protected $_nickname;
    protected $_mam = false;
    protected $_mam2 = false;

    public function request()
    {
        $session = Session::start();

        if (empty($this->_nickname)) {
            $this->_nickname = $session->get('username');
        }

        if ($this->_mam == false && $this->_mam2 == false) {
            \App\User::me()->messages()->where('jidfrom', $this->_to)->delete();
        }

        $this->store(); // Set stanzaId

        /**
         * Some servers doesn't return the ID, so save it in another session key-value
         * and use the to and nickname as a key ¯\_(ツ)_/¯
         */
        $session->set($this->_to . '/' .$this->_nickname, $this->stanzaId);

        Presence::muc($this->_to, $this->_nickname, $this->_mam);
    }

    public function enableMAM()
    {
        $this->_mam = true;
        return $this;
    }

    public function enableMAM2()
    {
        $this->_mam2 = true;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $presence = \App\Presence::findByStanza($stanza);
        $presence->set($stanza);

        if ($stanza->attributes()->to) {
            $presence->mucjid = current(explode('/', (string)$stanza->attributes()->to));
        }

        $presence->save();

        if ($this->_mam) {
            $message = \App\User::me()->messages()
                                      ->where('jidfrom', $this->_to)
                                      ->whereNull('subject')
                                      ->orderBy('published', 'desc')
                                      ->first();

            $g = new \Moxl\Xec\Action\MAM\Get;
            $g->setTo($this->_to)
              ->setLimit(300);

            if (!empty($message)
            && strtotime($message->published) > strtotime('-3 days')) {
                $g->setStart(strtotime($message->published));
            } else {
                $g->setStart(strtotime('-3 days'));
            }

            if (!$this->_mam2) {
                $g->setVersion('1');
            }

            $g->request();
        }

        $this->pack($presence);
        $this->deliver();
    }

    public function errorRegistrationRequired($stanza, $parent = false)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorRemoteServerNotFound($stanza, $parent = false)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorNotAuthorized($stanza, $parent = false)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorItemNotFound($stanza, $parent = false)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorConflict($stanza, $message)
    {
        if (substr_count($this->_nickname, '_') > 5) {
            $this->deliver();
        } else {
            $this->setNickname($this->_nickname.'_');
            $this->request();
        }
    }
}
