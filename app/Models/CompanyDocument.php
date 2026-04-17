<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;
use App\Traits\BelongsToSucursal;

class CompanyDocument extends Model
{
    use HasFactory, BelongsToCompany, BelongsToSucursal;
    
    protected $sucursalForeignKey = 'branch_id';

    protected $fillable = [
        'company_id',
        'branch_id',
        'sunat_document_id',
        'series',
        'number',
    ];

    protected static function booted(): void
    {
        static::creating(function (CompanyDocument $document) {
            if (empty($document->company_id) && $document->branch_id) {
                $document->company_id = Sucursal::find($document->branch_id)?->company_id;
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Sucursal::class, 'branch_id');
    }
}
