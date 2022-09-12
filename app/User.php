<?php

namespace app;

use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable{

    protected $table = 'users';
    protected $primaryKey = 'user_id';

    public $timestamps = false;



}

