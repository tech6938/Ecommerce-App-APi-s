<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'company_number',
        'company_address',
        'logo',
        'favicon',
        'admin_name',
        'job_types',
    ];
}
