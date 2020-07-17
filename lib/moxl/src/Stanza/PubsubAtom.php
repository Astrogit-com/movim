<?php

namespace Moxl\Stanza;

use Moxl\Utils;

class PubsubAtom
{
    public $id;
    public $name;
    public $jid;
    public $content;
    public $title;
    public $link;

    public $image;
    public $contentxhtml = false;

    public $repost;
    public $reply;

    public $to;
    public $node;

    public $geo = false;
    public $comments = false;
    public $open = false;

    public $tags = array();

    public $published = false;

    public function __construct()
    {
        $this->id = generateUUID();
    }

    public function enableComments($server = true)
    {
        $this->comments = $server;
    }

    public function isOpen()
    {
        $this->open = true;
    }

    public function getDom()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $entry = $dom->createElement('entry');
        $dom->appendChild($entry);
        $entry->appendChild($dom->createElement('id', $this->id));

        if ($this->title) {
            $entry->appendChild($dom->createElement('title', $this->title));
        }

        $author = $dom->createElement('author');
        $author->appendChild($dom->createElement('name', $this->name));
        $author->appendChild($dom->createElement('uri', 'xmpp:'.$this->jid));
        $entry->appendChild($author);

        /*$link = $dom->createElement('link');
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('type', 'application/atom+xml');
        $link->setAttribute('href', 'xmpp:'.$this->to.'?;node='.$this->node.';item='.$this->id);
        $entry->appendChild($link);*/

        $link = $dom->createElement('link');
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('href', 'xmpp:'.$this->to.'?;node='.$this->node.';item='.$this->id);
        $entry->appendChild($link);

        if ($this->comments) {
            $link = $dom->createElement('link');
            $link->setAttribute('rel', 'replies');
            $link->setAttribute('title', 'comments');

            if ($this->repost) {
                $link->setAttribute('href', 'xmpp:'.$this->repost[0].'?;node=urn:xmpp:microblog:0:comments/'.$this->repost[2]);
            } elseif ($this->comments === true) {
                $link->setAttribute('href', 'xmpp:'.$this->to.'?;node=urn:xmpp:microblog:0:comments/'.$this->id);
            } else {
                $link->setAttribute('href', 'xmpp:'.$this->comments.'?;node=urn:xmpp:microblog:0:comments/'.$this->id);
            }

            $entry->appendChild($link);
        }

        if ($this->open) {
            $link = $dom->createElement('link');
            $link->setAttribute('rel', 'alternate');
            $link->setAttribute('type', 'text/html');
            $link->setAttribute('title', $this->title);

            // Not very elegant
            if ($this->node == 'urn:xmpp:microblog:0') {
                $link->setAttribute('href', \Movim\Route::urlize('blog', [$this->to, $this->id]));
            } else {
                $link->setAttribute('href', \Movim\Route::urlize('node', [$this->to, $this->node, $this->id]));
            }

            $entry->appendChild($link);
        }

        if ($this->link && is_array($this->link)) {
            $link = $dom->createElement('link');
            $link->setAttribute('rel', 'related');
            $link->setAttribute('href', $this->link['href']);
            if ($this->link['type'] != null) {
                $link->setAttribute('type', $this->link['type']);
            }
            if ($this->link['title'] != null) {
                $link->setAttribute('title', $this->link['title']);
            }
            if ($this->link['description'] != null) {
                $link->setAttribute('description', $this->link['description']);
            }
            if ($this->link['logo'] != null) {
                $link->setAttribute('logo', $this->link['logo']);
            }
            $entry->appendChild($link);
        }

        if ($this->repost) {
            $link = $dom->createElement('link');
            $link->setAttribute('rel', 'via');
            $link->setAttribute('href', 'xmpp:'.$this->repost[0].'?;node='.$this->repost[1].';item='.$this->repost[2]);
            $entry->appendChild($link);
        }

        if ($this->reply) {
            $thr = $dom->createElement('thr:in-reply-to');
            $thr->setAttribute('href', $this->reply);
            $entry->appendChild($thr);
        }

        if ($this->image && is_array($this->image)) {
            $link = $dom->createElement('link');
            $link->setAttribute('rel', 'enclosure');
            $link->setAttribute('href', $this->image['href']);
            if ($this->image['type'] != null) {
                $link->setAttribute('type', $this->image['type']);
            }
            if ($this->image['title'] != null) {
                $link->setAttribute('title', $this->image['title']);
            }
            $entry->appendChild($link);
        }

        /*if ($this->geo) {
            $xml .= '
                    <geoloc xmlns="http://jabber.org/protocol/geoloc">
                        <lat>'.$this->geo['latitude'].'</lat>
                        <lon>'.$this->geo['longitude'].'</lon>
                        <altitude>'.$this->geo['altitude'].'</altitude>
                        <country>'.$this->geo['country'].'</country>
                        <countrycode>'.$this->geo['countrycode'].'</countrycode>
                        <region>'.$this->geo['region'].'</region>
                        <postalcode>'.$this->geo['postalcode'].'</postalcode>
                        <locality>'.$this->geo['locality'].'</locality>
                        <street>'.$this->geo['street'].'</street>
                        <building>'.$this->geo['building'].'</building>
                        <text>'.$this->geo['text'].'</text>
                        <uri>'.$this->geo['uri'].'</uri>
                        <timestamp>'.date('c').'</timestamp>
                    </geoloc>';
        }*/

        if ($this->content) {
            $content_raw = $dom->createElement('content', $this->content);
            $content_raw->setAttribute('type', 'text');
            $entry->appendChild($content_raw);
        }

        if ($this->contentxhtml) {
            $content = $dom->createElement('content');
            $div = $dom->createElement('div');
            $content->appendChild($div);
            $content->setAttribute('type', 'xhtml');
            $entry->appendChild($content);

            $f = $dom->createDocumentFragment();
            $f->appendXML($this->contentxhtml);
            $div->appendChild($f);
            $div->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        }

        if ($this->published != false) {
            $entry->appendChild($dom->createElement('published', date(DATE_ISO8601, $this->published)));
        } else {
            $entry->appendChild($dom->createElement('published', gmdate(DATE_ISO8601)));
        }

        if (is_array($this->tags)) {
            foreach ($this->tags as $tag) {
                $category = $dom->createElement('category');
                $entry->appendChild($category);
                $category->setAttribute('term', $tag);
            }
        }

        $entry->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
        if ($this->reply) {
            $entry->setAttribute('xmlns:thr', 'http://purl.org/syndication/thread/1.0');
        }
        $entry->appendChild($dom->createElement('updated', gmdate(DATE_ISO8601)));

        return $dom->documentElement;
    }

    public function __toString()
    {
        return $dom->saveXML($this->getDom());
    }
}
