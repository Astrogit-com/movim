<?php
/*
 * Away.php
 *
 * Copyright 2012 edhelas <edhelas@edhelas-laptop>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 */

namespace Moxl\Xec\Action\Presence;

use Moxl\Xec\Action;
use Moxl\Stanza\Presence;
use App\Presence as DBPresence;
use App\PresenceBuffer;

class Away extends Action
{
    protected $_status;

    public function request()
    {
        $this->store();
        Presence::away($this->_status);
    }

    public function handle($stanza, $parent = false)
    {
        $presence = DBPresence::findByStanza($stanza);
        $presence->set($stanza);

        PresenceBuffer::getInstance()->append($presence, function () use ($stanza) {
            $this->event('mypresence', $stanza);
        });
    }
}
