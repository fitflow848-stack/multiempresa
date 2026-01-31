<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Model;

class CompanyDocument extends Model
{
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

    protected static function booted()
    {
        static::creating(function ($document) {
            if (empty($document->company_id) && !empty($document->branch_id)) {
                $branch = Sucursal::find($document->branch_id);
                if ($branch) {
                    $document->company_id = $branch->company_id;
                }
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(Sucursal::class, 'branch_id');
    }
}
