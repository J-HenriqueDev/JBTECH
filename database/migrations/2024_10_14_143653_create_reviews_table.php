<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
  public function up()
  {
      Schema::create('reviews', function (Blueprint $table) {
          $table->id();
          // $table->string('google_review_id')->unique(); // Usando o tempo como identificador único
          $table->string('google_review_id')->nullable(); // Permite que o campo seja nulo
          $table->string('author_name');
          $table->string('profile_photo')->nullable();
          $table->integer('rating');
          $table->text('text');
          $table->timestamp('time');
          $table->timestamps();
      });
  }


    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
