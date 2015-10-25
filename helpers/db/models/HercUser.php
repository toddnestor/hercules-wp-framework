<?php
use \WeDevs\ORM\Eloquent\Model as Model;

class HercUser extends Model
{
    protected $fillable = ['access_token','herc_user_id','name'];
}