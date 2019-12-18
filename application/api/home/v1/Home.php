<?php

namespace app\api\home\v1;

use app\api\home\Base;

class Home extends Base
{
  public function getData()
  {
    file_put_contents('data.txt', $this->post_json);
    echo 1;die;
  }
}