<?php

namespace Moxl\Stanza;

use Moxl\Stanza\Message;

use Movim\Session;

class Muc
{
    public static function message($to, $content = false, $html = false, $id = false, $file = false, $parentId = false, array $reactions = [], $originId = false)
    {
        Message::maker($to, $content, $html, 'groupchat', false, false, $id, false, $file, false, $parentId, $reactions, $originId);
    }

    public static function active($to)
    {
        Message::maker($to, false, false, 'groupchat', 'active');
    }

    public static function inactive($to)
    {
        Message::maker($to, false, false, 'groupchat', 'inactive');
    }

    public static function composing($to)
    {
        Message::maker($to, false, false, 'groupchat', 'composing');
    }

    public static function paused($to)
    {
        Message::maker($to, false, false, 'groupchat', 'paused');
    }

    public static function setSubject($to, $subject)
    {
        $session = Session::start();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        $dom->appendChild($message);
        $message->setAttribute('to', str_replace(' ', '\40', $to));
        $message->setAttribute('id', $session->get('id'));
        $message->setAttribute('type', 'groupchat');

        $message->appendChild($dom->createElement('subject', $subject));

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }

    public static function destroy($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#owner', 'query');

        $destroy = $dom->createElement('destroy');
        $destroy->setAttribute('jid', $to);
        $query->appendChild($destroy);

        $xml = \Moxl\API::iqWrapper($query, $to, 'set');
        \Moxl\API::request($xml);
    }

    public static function setRole($to, $nick, $role)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#admin', 'query');

        $item = $dom->createElement('item');
        $item->setAttribute('nick', $nick);
        $item->setAttribute('role', $role);
        $query->appendChild($item);

        $xml = \Moxl\API::iqWrapper($query, $to, 'set');
        \Moxl\API::request($xml);
    }

    public static function getConfig($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#owner', 'query');

        $xml = \Moxl\API::iqWrapper($query, $to, 'get');
        \Moxl\API::request($xml);
    }

    public static function setConfig($to, $data)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#owner', 'query');
        $dom->appendChild($query);

        $x = $dom->createElementNS('jabber:x:data', 'x');
        $x->setAttribute('type', 'submit');
        $query->appendChild($x);

        $xmpp = new \FormtoXMPP($data);
        $xmpp->create();
        $xmpp->appendToX($dom);

        $xml = \Moxl\API::iqWrapper($query, $to, 'set');
        \Moxl\API::request($xml);
    }
}
