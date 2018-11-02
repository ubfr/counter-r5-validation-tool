<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Members extends Model
{
    
	public $timestamps = false;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	  protected $table = 'consortium_member';
	  
	  protected $fillable = [
	      'name', 'customer_id','requestor_id','notes','institution_id_type','institution_id_value','provider_id',
	  ];
	  
	  protected $hidden = [
	      'remember_token',
	  ];
	   
}




