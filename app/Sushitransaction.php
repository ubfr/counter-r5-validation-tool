<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sushitransaction extends Model
{
    
	public $timestamps = false;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	  protected $table = 'sushi_transaction';
	  
	  protected $fillable = [
	      'id','user_email','session_id','sushi_url','request_name','platform','success','number_of_errors','date_time',
	  ];
	  
	  protected $hidden = [
	      'remember_token',
	  ];
	   
}




