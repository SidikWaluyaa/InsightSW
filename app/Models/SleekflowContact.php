<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SleekflowContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'sleekflow_id',
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'contact_owner_name',
        'contact_owner_email',
        'contact_owner_id',
        'assigned_team',
        'status_chat',
        'lifecycle_stage',
        'lead_stage',
        'priority',
        'last_contact',
        'last_contact_from_customers',
        'last_contacted_from_company',
        'last_contacted_from_user',
        'last_channel',
        'lead_source',
        'facebook_form_id',
        'labels',
        'lists',
        'company_name',
        'job_title',
        'country',
        'subscriber',
        'ai_agent_session',
        'collaborators',
        'facebook_psid',
        'wechat_openid',
        'line_chatid',
        'created_at_sleekflow',
        'updated_at_sleekflow',
        'waktu_awal',
    ];

    protected $casts = [
        'last_contact' => 'datetime',
        'last_contact_from_customers' => 'datetime',
        'last_contacted_from_company' => 'datetime',
        'last_contacted_from_user' => 'datetime',
        'created_at_sleekflow' => 'datetime',
        'updated_at_sleekflow' => 'datetime',
        'waktu_awal' => 'date',
        'subscriber' => 'boolean',
        'labels' => 'array',
        'lists' => 'array',
        'collaborators' => 'array',
    ];
}
