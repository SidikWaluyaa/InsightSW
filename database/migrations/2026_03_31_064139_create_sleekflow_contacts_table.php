<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sleekflow_contacts', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('sleekflow_id')->unique();
            
            // Basic Info
            $blueprint->string('first_name')->nullable();
            $blueprint->string('last_name')->nullable();
            $blueprint->string('phone_number')->nullable();
            $blueprint->string('email')->nullable();
            
            // Ownership
            $blueprint->string('contact_owner_name')->nullable();
            $blueprint->string('contact_owner_email')->nullable();
            $blueprint->string('contact_owner_id')->nullable();
            $blueprint->string('assigned_team')->nullable();
            
            // Status & Stage
            $blueprint->string('status_chat')->nullable();
            $blueprint->string('lifecycle_stage')->nullable();
            $blueprint->string('lead_stage')->nullable();
            $blueprint->string('priority')->nullable();
            
            // Contact History
            $blueprint->dateTime('last_contact')->nullable();
            $blueprint->dateTime('last_contact_from_customers')->nullable();
            $blueprint->dateTime('last_contacted_from_company')->nullable();
            $blueprint->dateTime('last_contacted_from_user')->nullable();
            $blueprint->string('last_channel')->nullable();
            
            // Source & Marketing
            $blueprint->string('lead_source')->nullable();
            $blueprint->string('facebook_form_id')->nullable();
            $blueprint->text('labels')->nullable();
            $blueprint->text('lists')->nullable();
            
            // Profile Info
            $blueprint->string('company_name')->nullable();
            $blueprint->string('job_title')->nullable();
            $blueprint->string('country')->nullable();
            $blueprint->boolean('subscriber')->default(false);
            $blueprint->string('ai_agent_session')->nullable();
            $blueprint->text('collaborators')->nullable();
            
            // External IDs
            $blueprint->string('facebook_psid')->nullable();
            $blueprint->string('wechat_openid')->nullable();
            $blueprint->string('line_chatid')->nullable();
            
            // Timestamps
            $blueprint->dateTime('created_at_sleekflow')->nullable();
            $blueprint->dateTime('updated_at_sleekflow')->nullable();
            $blueprint->date('waktu_awal')->nullable();
            
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sleekflow_contacts');
    }
};
