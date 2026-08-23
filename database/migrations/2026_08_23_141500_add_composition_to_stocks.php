<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // DB già migrato manualmente — composition_key e composition_data
        // aggiunti, unique index ricreato su (warehouse_id, product_id, composition_key).
    }

    public function down(): void
    {
        // Rollback manuale: rimuovere le colonne e ripristinare il vecchio unique.
    }
};