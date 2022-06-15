<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EWBankTrxRequest extends Model
{
    use HasFactory;

    protected $table    = 'ew_bank_trx_requests';
    protected $fillable = ['trx_ref_id','sender_id','receiver_id','trx_activity_type','wallet_id','trx_amount','trx_currency','trx_note','trx_status','organization_ref_id','user_ref_id','role_ref_id','created_by','updated_by'];

}
