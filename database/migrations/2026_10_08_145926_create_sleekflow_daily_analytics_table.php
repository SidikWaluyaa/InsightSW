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
        Schema::create('sleekflow_daily_analytics', function (Blueprint $table) {
            $table->id();
            $table->date('date_time')->comment('Dari dateTime (YYYY-MM-DD)');
            $table->time('response_time_first_messages')->nullable()->comment('Dari responseTimeForFirstMessages (HH:MM:SS)');
            $table->time('response_time_all_messages')->nullable()->comment('Dari responseTimeForAllMessages (HH:MM:SS)');
            $table->integer('number_of_contacts')->nullable()->default(0)->comment('Dari numberOfContacts');
            $table->decimal('number_of_contacts_average', 10, 2)->nullable()->default(0.00)->comment('Dari numberOfContactsAverage');
            $table->integer('number_of_all_conversations')->nullable()->default(0)->comment('Dari numberOfAllConversations');
            $table->decimal('number_of_all_conversations_average', 10, 2)->nullable()->default(0.00)->comment('Dari numberOfAllConversationsAverage');
            $table->integer('number_of_active_conversations')->nullable()->default(0)->comment('Dari numberOfActiveConversations');
            $table->decimal('number_of_active_conversations_average', 10, 2)->nullable()->default(0.00)->comment('Dari numberOfActiveConversationsAverage');
            $table->integer('number_of_unique_conversations')->nullable()->default(0)->comment('Dari numberOfUniqueConversations');
            $table->decimal('number_of_unique_conversation_average', 10, 2)->nullable()->default(0.00)->comment('Dari numberOfUniqueConversationAverage');
            $table->integer('number_of_unique_active_conversations')->nullable()->default(0)->comment('Dari numberOfUniqueActiveConversations');
            $table->decimal('number_of_unique_active_conversation_average', 10, 2)->nullable()->default(0.00)->comment('Dari numberOfUniqueActiveConversationAverage');
            $table->integer('number_of_new_enquires')->nullable()->default(0)->comment('Dari numberOfNewEnquires');
            $table->decimal('number_of_new_enquires_average', 10, 2)->nullable()->default(0.00)->comment('Dari numberOfNewEnquiresAverage');
            $table->integer('number_of_messages_sent')->nullable()->default(0)->comment('Dari numberOfMessagesSent');
            $table->decimal('number_of_messages_sent_average', 10, 2)->nullable()->default(0.00)->comment('Dari numberOfMessagesSentAverage');
            $table->integer('number_of_message_received')->nullable()->default(0)->comment('Dari numberOfMessageReceived');
            $table->decimal('number_of_message_received_average', 10, 2)->nullable()->default(0.00)->comment('Dari numberOfMessageReceivedAverage');
            $table->integer('number_of_automated_messages')->nullable()->default(0)->comment('Dari numberOfAutomatedMessages');
            $table->integer('number_of_broadcast_sent')->nullable()->default(0)->comment('Dari numberOfBroadcastSent');
            $table->integer('number_of_broadcast_bounced')->nullable()->default(0)->comment('Dari numberOfBroadcastBounced');
            $table->integer('number_of_broadcast_delivered')->nullable()->default(0)->comment('Dari numberOfBroadcastDelivered');
            $table->integer('number_of_broadcast_read')->nullable()->default(0)->comment('Dari numberOfBroadcastRead');
            $table->integer('number_of_broadcast_replied')->nullable()->default(0)->comment('Dari numberOfBroadcastReplied');
            $table->integer('active_agents')->nullable()->default(0)->comment('Dari activeAgents');
            
            // Custom timestamps untuk menyamai skema DB yang ada persis
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sleekflow_daily_analytics');
    }
};
