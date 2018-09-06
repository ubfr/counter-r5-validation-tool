<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Consortium extends Model
{
    
	public $timestamps = false;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	  protected $table = 'consortium_configuration';
	  
	  protected $fillable = [
	      'configuration_name','remarks', 'provider_name', 'provider_url', 'apikey', 'customer_id','requestor_id',
	  ];
	  
	  protected $hidden = [
	      'remember_token',
	  ];
	   
}




