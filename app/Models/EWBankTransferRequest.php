<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EWBankTransferRequest extends Model
{
    use HasFactory;
    protected $table    = 'ew_bank_transfer_requests';
    protected $fillable = ['transfer_request_id','sender_id','receiver_id','account_number','account_name','trx_activity_type','wallet_id','trx_amount','trx_currency','pgw_request','pgw_response','trx_note','trx_status','organization_ref_id','user_ref_id','role_ref_id','created_by','updated_by'];

}
