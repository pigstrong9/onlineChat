<?php

namespace app\index\model;

use think\Model;

class Users extends Model {

    public function Message() {
        return $this->hasMany('Message','senderID','id');
    }

}
