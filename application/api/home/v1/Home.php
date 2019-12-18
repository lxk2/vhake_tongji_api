<?php

namespace app\api\home\v1;

use app\api\home\Base;

class Home extends Base
{
  public function getData()
  {
    file_put_contents('data.txt', $this->post_json);
    file_put_contents('data2.txt', json_encode($_GET, true));
    
    echo 1;die;
  }
}