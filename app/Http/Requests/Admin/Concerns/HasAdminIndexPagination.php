<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Support\AdminIndexRegistry;
use Illuminate\Validation\Rule;

trait HasAdminIndexPagination
{
    protected function perPageRules(): array
    {
        return [
            'nullable',
            'integer',
            Rule::in(AdminIndexRegistry::perPageAllowed()),
        ];
    }
}
