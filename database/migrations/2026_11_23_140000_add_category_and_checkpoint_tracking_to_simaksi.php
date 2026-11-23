<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('simaksi_registrations', function (Blueprint $table) {
            $table->string('hiking_category', 20)->default('camp')->after('total_climbers'); // 'tektok' | 'camp'
            $table->string('planned_campsite', 20)->nullable()->after('hiking_category');     // 'POS_2' | 'POS_3' | null
            $table->string('camp_location', 20)->nullable()->after('planned_campsite');        // Actual confirmed campsite
            $table->string('last_seen_pos', 20)->nullable()->after('camp_location');          // 'BASECAMP', 'POS_1', 'POS_2', 'POS_3', 'PUNCAK'
            $table->string('last_seen_direction', 20)->nullable()->after('last_seen_pos');    // 'ascending', 'descending', 'camping'
            $table->timestamp('last_seen_at')->nullable()->after('last_seen_direction');

            $table->index(['hiking_category', 'status']);
            $table->index(['last_seen_pos', 'last_seen_direction']);
        });

        Schema::create('simaksi_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simaksi_registration_id')->constrained('simaksi_registrations')->cascadeOnDelete();
            $table->string('pos_code', 20); // 'POS_1', 'POS_2', 'POS_3', 'PUNCAK', 'BASECAMP'
            $table->string('direction', 20); // 'ascending', 'descending', 'camping'
            $table->foreignId('ranger_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('entry_source', 30)->default('scanner'); // 'scanner', 'radio_ht', 'manual'
            $table->text('notes')->nullable();
            $table->timestamp('scanned_at');
            $table->timestamps();

            $table->index(['simaksi_registration_id', 'scanned_at']);
            $table->index(['pos_code', 'direction', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simaksi_checkpoints');

        Schema::table('simaksi_registrations', function (Blueprint $table) {
            $table->dropIndex(['hiking_category', 'status']);
            $table->dropIndex(['last_seen_pos', 'last_seen_direction']);
            $table->dropColumn([
                'hiking_category',
                'planned_campsite',
                'camp_location',
                'last_seen_pos',
                'last_seen_direction',
                'last_seen_at',
            ]);
        });
    }
};
