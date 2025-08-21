<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('phosphorus_analyses', 'review_status')) {
                $table->string('review_status')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('review_status');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'review_date')) {
                $table->dateTime('review_date')->nullable()->after('reviewed_by');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'review_observations')) {
                $table->text('review_observations')->nullable()->after('review_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            if (Schema::hasColumn('phosphorus_analyses', 'review_observations')) {
                $table->dropColumn('review_observations');
            }
            if (Schema::hasColumn('phosphorus_analyses', 'review_date')) {
                $table->dropColumn('review_date');
            }
            if (Schema::hasColumn('phosphorus_analyses', 'reviewed_by')) {
                $table->dropColumn('reviewed_by');
            }
            if (Schema::hasColumn('phosphorus_analyses', 'review_status')) {
                $table->dropColumn('review_status');
            }
        });
    }
};
