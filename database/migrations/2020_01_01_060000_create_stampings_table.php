<?php

use HDSSolutions\Laravel\Blueprints\BaseBlueprint as Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateStampingsTable extends Migration {

    public function up() {
        // get schema builder
        $schema = DB::getSchemaBuilder();

        // replace blueprint
        $schema->blueprintResolver(fn($table, $callback) => new Blueprint($table, $callback));

        $schema->create('stampings', function(Blueprint $table) {
            $table->id();
            $table->foreignTo('Company');
            $table->boolean('is_purchase')->default(false);
            $table->foreignTo('Provider')->nullable();
            $table->string('document_number');
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->unsignedTinyInteger('length')->nullable();
            $table->unsignedBigInteger('start')->nullable();
            $table->unsignedBigInteger('end')->nullable();
            $table->unsignedBigInteger('current')->nullable();
        });
    }

    public function down() {
        Schema::dropIfExists('stampings');
    }

}
