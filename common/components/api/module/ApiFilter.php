<?php
namespace common\components\api\module;

use common\base\BaseAccessControl;

class ApiFilter extends BaseAccessControl
{

    protected function denyAccess($user)
    {
        return null;
    }
}