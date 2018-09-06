<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reportname extends Model
{
    
	public $timestamps = false;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	  protected $table = 'reportnames';
	  
// 	  public function parent(){
// 	      return $this->belongsTo('App\Parentreport','id','parent_id');
// 	  }
    
}
