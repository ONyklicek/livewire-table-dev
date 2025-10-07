<?php

namespace App\Support\Tables\Actions;

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
