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
        Schema::create('amis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');//user qui send la demande
            $table->unsignedBigInteger('ami_id');//user qui recoit la demande
            $table->boolean('status')->default(false);//l'etat de la demande false en attente true accepted
            //definition des cles etrangers
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ami_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amis');
    }
};
