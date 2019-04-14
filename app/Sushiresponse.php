<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Sushiresponse extends Model
{

    public $timestamps = false;

    public function responsefile()
    {
        return $this->belongsTo('App\Storedfile');
    }

    public function checkresult()
    {
        return $this->belongsTo('App\Checkresult');
    }

    public static function store($responsefile, $checkresult, $sushitransaction)
    {
        if (! ($responsefile instanceof Storedfile)) {
            throw new \InvalidArgumentException("responsefile invalid, expecting Storedfile");
        }
        if (! ($checkresult instanceof Checkresult)) {
            throw new \InvalidArgumentException("checkresult invalid, expecting Checkresult");
        }
        if (! ($sushitransaction instanceof Sushitransaction)) {
            throw new \InvalidArgumentException("sushitransaction invalid, expecting Sushitransaction");
        }

        $sushiresponse = new Sushiresponse();
        $sushiresponse->responsefile_id = $responsefile->id;
        $sushiresponse->checkresult_id = $checkresult->id;
        $sushiresponse->sushitransaction_id = $sushitransaction->id;
        if (! $sushiresponse->save()) {
            throw new \Exception("failed to save Sushiresponse");
        }

        return $sushiresponse;
    }
}
