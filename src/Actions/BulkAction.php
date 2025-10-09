<?php

namespace NyonCode\LivewireTable\Actions;

class BulkAction extends Action
{
    public function execute($records): mixed
    {
        if ($this->action) {
            return ($this->action)($records);
        }

        return null;
    }
}
