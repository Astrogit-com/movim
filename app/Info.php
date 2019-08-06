<?php

namespace App;

use Movim\Model;

class Info extends Model
{
    protected $primaryKey = ['server', 'node'];
    public $incrementing = false;
    protected $fillable = ['server', 'node', 'avatarhash'];

    public function setAdminaddressesAttribute(array $arr)
    {
        $this->attributes['adminaddresses'] = serialize($arr);
    }

    public function getAdminaddressesAttribute(): array
    {
        return (isset($this->attributes['adminaddresses']))
            ? unserialize($this->attributes['adminaddresses'])
            : [];
    }

    public function setAbuseaddressesAttribute(array $arr)
    {
        $this->attributes['abuseaddresses'] = serialize($arr);
    }

    public function getAbuseaddressesAttribute(): array
    {
        return (isset($this->attributes['abuseaddresses']))
            ? unserialize($this->attributes['abuseaddresses'])
            : [];
    }

    public function setFeedbackaddressesAttribute(array $arr)
    {
        $this->attributes['feedbackaddresses'] = serialize($arr);
    }

    public function getFeedbackaddressesAttribute(): array
    {
        return (isset($this->attributes['feedbackaddresses']))
            ? unserialize($this->attributes['feedbackaddresses'])
            : [];
    }

    public function setSalesaddressesAttribute(array $arr)
    {
        $this->attributes['salesaddresses'] = serialize($arr);
    }

    public function getSalesaddressesAttribute(): array
    {
        return (isset($this->attributes['salesaddresses']))
            ? unserialize($this->attributes['salesaddresses'])
            : [];
    }
    public function setSecurityaddressesAttribute(array $arr)
    {
        $this->attributes['securityaddresses'] = serialize($arr);
    }

    public function getSecurityaddressesAttribute(): array
    {
        return (isset($this->attributes['securityaddresses']))
            ? unserialize($this->attributes['securityaddresses'])
            : [];
    }

    public function setSupportaddressesAttribute(array $arr)
    {
        $this->attributes['supportaddresses'] = serialize($arr);
    }

    public function getSupportaddressesAttribute(): array
    {
        return (isset($this->attributes['supportaddresses']))
            ? unserialize($this->attributes['supportaddresses'])
            : [];
    }

    public function getNameAttribute()
    {
        return isset($this->attributes['name'])
            ? $this->attributes['name']
            : $this->attributes['node'];
    }

    public function getRelatedAttribute()
    {
        if ($this->category == 'pubsub' && $this->type == 'leaf') {
            return \App\Info::where('related', 'xmpp:'.$this->server.'?;node='.$this->node)
                ->first();
        }

        if (isset($this->attributes['related'])
        && $this->category == 'conference' && $this->type == 'text') {
            $uri = parse_url($this->attributes['related']);

            if (isset($uri['query']) && isset($uri['path'])) {
                $params = explodeQueryParams($uri['query']);

                if (isset($params['node'])) {
                    return \App\Info::where('server', $uri['path'])
                        ->where('node', $params['node'])
                        ->first();
                }
            }
        }
    }

    /**
     * Only for gateways
     */
    public function getPresenceAttribute()
    {
        return \App\User::me()->session->presences
                    ->where('jid', $this->attributes['server'])
                    ->first();
    }

    public function getPhoto($size = 'm')
    {
        return isset($this->attributes['avatarhash'])
            ? getPhoto($this->attributes['avatarhash'], $size)
            : null;
    }

    public function set($query)
    {
        $from = (string)$query->attributes()->from;

        if (strpos($from, '/') == false
        && isset($query->query)) {
            $this->server   = $from;
            $this->node     = (string)$query->query->attributes()->node;

            foreach ($query->query->identity as $i) {
                if ($i->attributes()) {
                    $this->category = (string)$i->attributes()->category;
                    $this->type     = (string)$i->attributes()->type;

                    if ($i->attributes()->name) {
                        $this->name = (string)$i->attributes()->name;
                    } elseif (!empty($this->node)) {
                        $this->name = $this->node;
                    }
                }
            }

            foreach ($query->query->feature as $feature) {
                $key = (string)$feature->attributes()->var;

                switch ($key) {
                    // If it's a MUC we clear the node
                    case 'http://jabber.org/protocol/muc':
                        $this->node = '';
                        break;
                    case 'muc_public':
                        $this->mucpublic = true;
                        break;
                    case 'muc_persistent':
                        $this->mucpersistent = true;
                        break;
                    case 'muc_passwordprotected':
                        $this->mucpasswordprotected = true;
                        break;
                    case 'muc_membersonly':
                        $this->mucpasswordprotected = true;
                        break;
                    case 'muc_moderated':
                        $this->mucmoderated = true;
                        break;
                    case 'muc_semianonymous':
                        $this->mucsemianonymous = true;
                        break;
                }
            }

            if (isset($query->query->x)) {
                foreach ($query->query->x->field as $field) {
                    $key = (string)$field->attributes()->var;
                    switch ($key) {
                        case 'pubsub#title':
                            $this->name = (string)$field->value;
                            break;
                        case 'pubsub#creation_date':
                            $this->created = toSQLDate($field->value);
                            break;
                        case 'pubsub#access_model':
                            $this->pubsubaccessmodel = $field->value;
                            break;
                        case 'pubsub#publish_model':
                            $this->pubsubpublishmodel = $field->value;
                            break;
                        case 'muc#roominfo_pubsub':
                            if (!empty((string)$field->value)) {
                                $this->related = $field->value;
                            }
                            break;
                        case 'muc#roominfo_description':
                        case 'pubsub#description':
                            if (!empty((string)$field->value)) {
                                $this->description = (string)$field->value;
                            }
                            break;
                        case 'pubsub#num_subscribers':
                        case 'muc#roominfo_occupants':
                            $this->occupants = (int)$field->value;
                            break;
                        case 'abuse-addresses':
                            $arr = [];
                            foreach ($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->abuseaddresses = $arr;
                            break;
                        case 'admin-addresses':
                            $arr = [];
                            foreach ($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->adminaddresses = $arr;
                            break;
                        case 'feedback-addresses':
                            $arr = [];
                            foreach ($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->feedbackaddresses = $arr;
                            break;
                        case 'sales-addresses':
                            $arr = [];
                            foreach ($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->salesaddresses = $arr;
                            break;
                        case 'security-addresses':
                            $arr = [];
                            foreach ($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->securityaddresses = $arr;
                            break;
                        case 'support-addresses':
                            $arr = [];
                            foreach ($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->supportaddresses = $arr;
                            break;
                    }
                }
            }
        }
    }

    public function setItem($item)
    {
        $this->server = (string)$item->attributes()->jid;
        $this->node   = (string)$item->attributes()->node;

        if ($item->attributes()->name) {
            $this->name   = (string)$item->attributes()->name;
        }
    }

    public function isPubsubService()
    {
        return ($this->category == 'pubsub' && $this->type == 'service');
    }

    public function isMicroblogCommentsNode()
    {
        return (substr($this->node, 0, 29) == 'urn:xmpp:microblog:0:comments');
    }

    public function isOld()
    {
        return (strtotime($this->updated_at) < time() - 3600);
    }
}
