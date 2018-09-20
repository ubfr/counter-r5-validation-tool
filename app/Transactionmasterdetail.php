<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transactionmasterdetail extends Model
{
    
	public $timestamps = false;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
	    'user_id','transaction_id','config_name','client_ip','provider_name', 'member_name', 'report_id','begin_date','end_date', 'status', 'message', 'remarks', 'exception', 'details','file_name','file_size', 'start_date_time', 'end_date_time', 'time_stamp'
	];
	  protected $table = 'transaction_master_detail';
	  
	  public function parent(){
	      return $this->belongsTo('App\Parentreport','id','parent_id');
	  }
    
}
