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
            $table->string('document_number');
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
        });
    }

    public function down() {
        Schema::dropIfExists('stampings');
    }

}
