<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transactiondetailtemp extends Model
{
    
	public $timestamps = false;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
	    'transaction_id', 'providers', 'members', 'reports', 'begin_date', 'end_date',
	];
	  protected $table = 'transaction_detail_temp';
	  
	 /*  public function parent(){
	      return $this->belongsTo('App\Parentreport','id','parent_id');
	  } */
    
}
