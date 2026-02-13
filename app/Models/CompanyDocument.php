<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class CompanyDocument extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'branch_id',
        'sunat_document_id',
        'series',
        'number',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Sucursal::class, 'branch_id');
    }
}
