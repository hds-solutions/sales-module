<?php

use HDSSolutions\Laravel\Blueprints\BaseBlueprint as Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceLineOrderLineTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        // get schema builder
        $schema = DB::getSchemaBuilder();

        // replace blueprint
        $schema->blueprintResolver(fn($table, $callback) => new Blueprint($table, $callback));

        $schema->create('invoice_line_order_line', function(Blueprint $table) {
            $table->foreignTo('InvoiceLine');
            $table->foreignTo('OrderLine');
            $table->primary([ 'invoice_line_id', 'order_line_id' ]);
            $table->unsignedInteger('quantity_ordered');
            $table->unsignedInteger('quantity_invoiced')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('invoice_line_order_line');
    }

}
