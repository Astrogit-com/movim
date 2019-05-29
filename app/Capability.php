<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Capability extends Model
{
    protected $primaryKey = 'node';
    protected $fillable = ['features'];
    public $incrementing = false;

    public function save(array $options = [])
    {
        try {
            parent::save($options);
        } catch (\Exception $e) {
            /*
             * When an capability is received by two accounts simultaenously
             */
        }
    }

    public function set($query, $node = false)
    {
        if (!$node) {
            $this->node     = (string)$query->query->attributes()->node;
        } else {
            $this->node     = $node;
        }

        if (isset($query->query)) {
            foreach ($query->query->identity as $i) {
                if ($i->attributes()) {
                    $this->category = (string)$i->attributes()->category;
                    $this->type     = (string)$i->attributes()->type;

                    if ($i->attributes()->name) {
                        $this->name = (string)$i->attributes()->name;
                    } else {
                        $this->name = $this->node;
                    }
                }
            }

            $fet = [];
            foreach ($query->query->feature as $f) {
                array_push($fet, (string)$f->attributes()->var);
            }

            $this->setFeaturesAttribute($fet);
        }
    }

    public function getPubsubRoles()
    {
        $roles = ['owner' => __('affiliation.owner'), 'none' =>  __('affiliation.no-aff')];

        foreach ($this->getFeaturesAttribute() as $feature) {
            preg_match("/http:\/\/jabber.org\/protocol\/pubsub#(.*)-affiliation$/", $feature, $matches);
            if (!empty($matches)) {
                $roles[$matches[1]] = __('affiliation.' . $matches[1]);
            }
        }

        return $roles;
    }

    public function isPubsub()
    {
        return (in_array('http://jabber.org/protocol/pubsub#persistent-items', $this->getFeaturesAttribute()));
    }

    public function isJingle()
    {
        return (in_array('urn:xmpp:jingle:1', $this->getFeaturesAttribute()));
    }

    public function isMAM()
    {
        return (in_array('urn:xmpp:mam:1', $this->getFeaturesAttribute()));
    }

    public function isMAM2()
    {
        return (in_array('urn:xmpp:mam:2', $this->getFeaturesAttribute()));
    }

    public function getDeviceIcon()
    {
        if (in_array($this->type, ['handheld', 'phone'])) {
            return 'smartphone';
        }
        if ($this->type == 'bot') {
            return 'memory';
        }
        if ($this->type == 'console') {
            return 'video_label';
        }
        if ($this->type == 'web') {
            if ($this->name == 'Movim') {
                return 'cloud_queue';
            }

            return 'language';
        }

        return 'desktop_windows';
    }

    public function getFeaturesAttribute()
    {
        return unserialize($this->attributes['features']);
    }

    public function setFeaturesAttribute($features)
    {
        $this->attributes['features'] = serialize($features);
    }
}
