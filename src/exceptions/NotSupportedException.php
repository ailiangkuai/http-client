<?php
/**
 * 广州亚美科技
 * 本源代码由广州亚美科技及其作者共同所有，未经版权持有者的事先书面授权，
 * 不得使用、复制、修改、合并、发布、分发和/或销售本源代码的副本。
 *
 * @copyright Copyright (c) 2017 all rights reserved.
 */

namespace Ym\http\request\exceptions;


class NotSupportedException extends \Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Not Supported';
    }
}