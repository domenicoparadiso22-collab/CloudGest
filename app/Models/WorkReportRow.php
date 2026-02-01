<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkReportRow extends Model
{
    protected $fillable = [
        'work_report_id', 'description', 'quantity', 'unit', 'price', 'total'
    ];
}