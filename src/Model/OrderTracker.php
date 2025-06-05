<?php

declare(strict_types=1);

namespace Grin\Module\Model;

class OrderTracker
{
    /**
     * @var boolean
     */
    private $new = false;

    /**
     * @param bool $flag
     * @return void
     */
    public function setNew(bool $flag)
    {
        $this->new = $flag;
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->new;
    }
}
