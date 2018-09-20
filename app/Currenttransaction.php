<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currenttransaction extends Model
{
    
	public $timestamps = false;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	  protected $table = 'current_transaction';
	  
	  protected $fillable = [
	      'transaction_id','configuration_id',
	  ];
	  
	   
}




