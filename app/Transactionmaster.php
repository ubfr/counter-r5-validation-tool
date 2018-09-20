<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transactionmaster extends Model
{
    
	public $timestamps = false;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
	    'configuration_id','user_id','begin_date','end_date','client_ip','start_date_time', 'end_date_time', 'time_stamp'
	];
	  protected $table = 'transaction_master';
	  
	  public function parent(){
	      return $this->belongsTo('App\Parentreport','id','parent_id');
	  }
    
}
