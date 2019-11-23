<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Vcard\Get;

use Movim\Session;
use App\Presence as DBPresence;

class Presence extends Payload
{
    public function handle($stanza, $parent = false)
    {
        // Subscribe request
        if ((string)$stanza->attributes()->type == 'subscribe') {
            $session = Session::start();
            $notifs = $session->get('activenotifs');
            $notifs[(string)$stanza->attributes()->from] = 'sub';
            $session->set('activenotifs', $notifs);

            $this->event('subscribe', (string)$stanza->attributes()->from);
        } elseif((string)$stanza->attributes()->type === 'error'
            && isset($stanza->attributes()->id)) {
            // Let's drop errors with an id, useless for us
        } else {
            $presence = DBPresence::findByStanza($stanza);
            $presence->set($stanza);
            $presence->save();

            $refreshable = $presence->refreshable;
            if ($refreshable) {
                $r = new Get;
                $r->setAvatarhash($presence->avatarhash)
                  ->setTo((string)$refreshable)
                  ->request();
            }

            if ($presence->muc
            && isset($stanza->x)) {
                foreach ($stanza->x as $x) {
                    if ($x->attributes()->xmlns == 'http://jabber.org/protocol/muc#user'
                    && isset($stanza->x->status)
                    && \in_array((int)$stanza->x->status->attributes()->code, [110, 332, 307, 301])) {
                        if ($presence->value != 5 && $presence->value != 6) {
                            $this->method('muc_handle');
                            $this->pack($presence);
                        } elseif ($presence->value == 5) {
                            $this->method('unavailable_handle');
                            $this->pack($presence);
                        }

                        $this->deliver();
                    }
                }
            } else {
                $this->pack($presence->roster);

                if ($presence->value == 5 /*|| $p->value == 6*/) {
                    $presence->delete();
                }
            }

            $this->deliver();
        }
    }
}
