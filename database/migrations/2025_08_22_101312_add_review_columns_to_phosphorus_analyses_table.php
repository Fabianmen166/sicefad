<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReviewColumnsToPhosphorusAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            $table->enum('review_status', ['pending', 'approved', 'rejected'])->default('pending')->after('id');
            $table->text('review_observations')->nullable()->after('review_status');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('review_observations');
            $table->timestamp('review_date')->nullable()->after('reviewed_by');
            
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['review_status', 'review_observations', 'reviewed_by', 'review_date']);
        });
    }
}
