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
}
