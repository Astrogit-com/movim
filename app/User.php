<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Movim\Session;
use App\Contact;
use App\Configuration;

class User extends Model
{
    protected $fillable = ['id', 'language', 'nightmode', 'chatmain', 'nsfw', 'nickname', 'notificationchat', 'notificationcall'];
    public $with = ['session', 'capability'];
    protected $keyType = 'string';
    public $incrementing = false;
    private static $me = null;
    private $unreads = null;

    public function save(array $options = [])
    {
        parent::save($options);

        // Reload the user
        self::me(true);
        (new \Movim\Bootstrap)->loadLanguage();
    }

    public function session()
    {
        return $this->hasOne('App\Session');
    }

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id');
    }

    public function capability()
    {
        return $this->hasOne('App\Info', 'server', 'id');
    }

    public function messages()
    {
        return $this->hasMany('App\Message');
    }

    public function unreads(string $jid = null, bool $quoted = false, $cached = false): int
    {
        if ($this->unreads !== null && $cached) return $this->unreads;

        $unreads = $this->messages()
                        ->where('seen', false)
                        ->where('jidfrom', '!=', $this->id)
                        ->where(function ($query) use ($quoted) {
                            $query->where('type', 'chat')
                                ->orWhere(function ($query) use ($quoted) {
                                    $query->where('type', 'groupchat')
                                          ->whereNull('subject');

                                    if ($quoted) {
                                        $query->where('quoted', true);
                                    }
                                });
                        });

        if ($jid) {
            $unreads = $unreads->where('jidfrom', $jid);
        } else {
            $unreads = $unreads->distinct('jidfrom');
        }

        $unreads = $unreads->count();

        if ($jid == null) {
            $this->unreads = $unreads;
        }

        return $unreads;
    }

    public function encryptedPasswords()
    {
        return $this->hasMany('App\EncryptedPassword');
    }

    public function subscriptions()
    {
        return $this->hasMany('App\Subscription', 'jid', 'id');
    }

    public static function me($reload = false)
    {
        $session = Session::start();

        if (self::$me != null
        && self::$me->id == $session->get('jid')
        && $reload == false) {
            return self::$me;
        }

        $me = self::find($session->get('jid'));
        self::$me = $me;

        return ($me) ? $me : new User;
    }

    public function isLogged()
    {
        return (bool)(Session::start())->get('jid');
    }

    public function init()
    {
        $contact = Contact::firstOrNew(['id' => $this->id]);
        $contact->save();
    }

    public function setConfig(array $config)
    {
        if (isset($config['language'])) {
            $this->language = $config['language'];
        }

        if (isset($config['nsfw'])) {
            $this->nsfw = $config['nsfw'];
        }

        if (isset($config['chatmain'])) {
            $this->chatmain = $config['chatmain'];
        }

        if (isset($config['nightmode'])) {
            $this->nightmode = $config['nightmode'];
        }

        if (isset($config['notificationcall'])) {
            $this->notificationcall = $config['notificationcall'];
        }

        if (isset($config['notificationchat'])) {
            $this->notificationchat = $config['notificationchat'];
        }
    }

    public function hasMAM()
    {
        return ($this->capability && $this->capability->hasFeature('urn:xmpp:mam:2'));
    }

    public function hasPubsub()
    {
        $configuration = Configuration::get();

        return (!$configuration->chatonly
            && $this->capability
            && $this->capability->hasFeature('http://jabber.org/protocol/pubsub#persistent-items')
        );
    }

    public function hasUpload()
    {
        return ($this->session && $this->session->getUploadService());
    }

    public function setPublic()
    {
        $this->attributes['public'] = true;
        $this->save();
    }

    public function setPrivate()
    {
        $this->attributes['public'] = false;
        $this->save();
    }
}
