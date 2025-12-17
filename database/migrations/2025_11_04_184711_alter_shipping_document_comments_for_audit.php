<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shipping_document_comments', function (Blueprint $table) {
            // Verificar y agregar columnas solo si no existen
            $columns = ['action_type', 'old_values', 'new_values', 'ip_address', 'user_agent'];
            
            foreach ($columns as $column) {
                $exists = DB::selectOne("
                    SELECT EXISTS (
                        SELECT 1 
                        FROM information_schema.columns 
                        WHERE table_name = 'shipping_document_comments' 
                        AND column_name = ?
                    ) as exists
                ", [$column]);
                
                if (!$exists->exists) {
                    if ($column === 'action_type') {
                        $table->string('action_type')->default('comment');
                    } elseif ($column === 'old_values' || $column === 'new_values') {
                        $table->json($column)->nullable();
                    } elseif ($column === 'ip_address') {
                        $table->string('ip_address')->nullable();
                    } elseif ($column === 'user_agent') {
                        $table->text('user_agent')->nullable();
                    }
                }
            }
        });
        
        // Verificar y crear índice solo si no existe
        $indexExists = DB::selectOne("
            SELECT EXISTS (
                SELECT 1 
                FROM pg_indexes 
                WHERE schemaname = 'public' 
                AND tablename = 'shipping_document_comments'
                AND indexname = 'shipping_document_comments_action_type_index'
            ) as exists
        ");
        
        if (!$indexExists->exists) {
            Schema::table('shipping_document_comments', function (Blueprint $table) {
                $table->index('action_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_document_comments', function (Blueprint $table) {
            $table->dropIndex(['action_type']);
            $table->dropColumn(['action_type', 'old_values', 'new_values', 'ip_address', 'user_agent']);
        });
    }
};
