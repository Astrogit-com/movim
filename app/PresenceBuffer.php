<?php

namespace App;

use Illuminate\Database\Capsule\Manager as DB;
use App\Presence;

class PresenceBuffer
{
    protected static $instance;
    private $_models = null;
    private $_calls = null;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        global $loop;

        $this->_models = collect();
        $this->_calls = collect();

        $loop->addPeriodicTimer(1, function () {
            $this->save();
        });
    }

    public function save()
    {
        if ($this->_models->count() > 0) {
            try {
                DB::beginTransaction();

                // We delete all the presences that might already be there
                $table = DB::table('presences');
                $first = $this->_models->shift();
                $table = $table->where([
                    ['session_id', $first['session_id']],
                    ['jid', $first['jid']],
                    ['resource', $first['resource']],
                ]);
                $this->_models->each(function ($presence) use ($table) {
                    $table->orWhere([
                        ['session_id', $presence['session_id']],
                        ['jid', $presence['jid']],
                        ['resource', $presence['resource']],
                    ]);
                });
                $table->delete();

                // And we save it
                Presence::insert($this->_models->toArray());
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                \Utils::error($e->getMessage());
            }
            $this->_models = collect();
        }

        if ($this->_calls->isNotEmpty()) {
            $this->_calls->each(function ($call) {
                $call();
            });
            $this->_calls = collect();
        }
    }

    public function append(Presence $presence, $call)
    {
            $key = $this->getPresenceKey($presence);
            $this->_models[$key] = $presence->toArray();
            $this->_calls->push($call);
    }

    private function getPresenceKey(Presence $presence)
    {
        return $presence->muc ? $presence->jid.$presence->mucjid : $presence->jid.$presence->resource;
    }
}
