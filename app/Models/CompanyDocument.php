<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;
use App\Traits\BelongsToSucursal;

class CompanyDocument extends Model
{
    use HasFactory, BelongsToCompany, BelongsToSucursal;
    

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
