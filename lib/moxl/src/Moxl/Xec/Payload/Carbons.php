<?php

namespace Moxl\Xec\Payload;

use Movim\User;

class Carbons extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $parentfrom = current(explode('/', (string)$parent->attributes()->from));
        $message = $stanza->forwarded->message;

        if ($parentfrom == \App\User::me()->id) {
            if ($message->body || $message->subject) {
                $m = \App\Message::findByStanza($message);
                $m->set($message, $stanza->forwarded);

                if (!$m->isOTR()) {
                    $m->save();
                    $m->clearUnreads();

                    $this->pack($m);
                    $this->deliver();
                }
            } elseif ($message->displayed) {
                // Another client just displayed the message
                $displayed = new Displayed;
                $displayed->handle($message->displayed, $message);
            }
        }
    }
}
