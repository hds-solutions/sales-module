<?php

use HDSSolutions\Finpar\Blueprints\BaseBlueprint as Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration {
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

        // create table
        $schema->create('invoices', function(Blueprint $table) {
            $table->id();
            $table->foreignTo('Company');
            $table->foreignTo('Branch');
            $table->foreignTo('Warehouse')->nullable();
            $table->foreignTo('Currency');
            $table->foreignTo('Employee');
            $table->morphable('partner');
            $table->unsignedInteger('address_id')->nullable(); // TODO: Link to Partner.address
            $table->timestamp('transacted_at')->useCurrent();
            $table->string('stamping')->nullable();
            $table->string('document_number');
            $table->boolean('is_purchase')->default(false);
            $table->boolean('is_credit')->default(false);
            $table->amount('total')->default(0);
            $table->amount('paid_amount')->default(0);
            $table->boolean('is_paid')->default(false);
            // use table as document
            $table->asDocument();
        });

        $schema->create('invoice_lines', function(Blueprint $table) {
            $table->id();
            $table->foreignTo('Invoice');
            $table->foreignTo('Currency');
            $table->foreignTo('Employee');
            $table->foreignTo('Product');
            $table->foreignTo('Variant')->nullable();
            $table->unique([ 'invoice_id', 'product_id', 'variant_id' ]);
            $table->amount('price_reference')->nullable();
            $table->amount('price_ordered')->nullable();
            $table->amount('price_invoiced');
            $table->unsignedInteger('quantity_ordered')->nullable();
            $table->unsignedInteger('quantity_invoiced');
            $table->unsignedInteger('quantity_received')->nullable();
            $table->amount('total');
            $table->unsignedInteger('conversion_rate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
    }

}
