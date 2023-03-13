<?php declare(strict_types = 1);


namespace PHPblank\Model;

use Illuminate\Database\Eloquent\Model;


class Users extends Model {

    	public $timestamps = false;
	protected $table = 'users';
      protected $fillable = ['Username','Email','Telp',];

}