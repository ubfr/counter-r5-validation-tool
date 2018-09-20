<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    
	public $timestamps = false;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	  protected $table = 'provider_details';
	  
	  protected $fillable = [
	      'configuration_id','remarks','provider_name','provider_url','apikey','requestor_id','customer_id','remarks',
	  ];
	  
	  protected $hidden = [
	      'remember_token',
	  ];
	   
}




