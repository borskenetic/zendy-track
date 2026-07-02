<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('zendy_logs')) {
            Schema::create('zendy_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('actor_role', 50)->nullable();
                $table->string('action', 100);
                $table->string('first_name', 100)->nullable();
                $table->string('last_name', 100)->nullable();
                $table->string('email')->nullable();
                $table->string('course', 150)->nullable();
                $table->string('department', 150)->nullable();
                $table->string('campus', 150)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['action', 'created_at']);
                $table->index(['email', 'created_at']);
                $table->index(['course', 'created_at']);
                $table->index(['campus', 'created_at']);
                $table->index(['actor_user_id', 'created_at']);
            });

            return;
        }

        Schema::table('zendy_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('zendy_logs', 'actor_user_id')) {
                $table->unsignedBigInteger('actor_user_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('zendy_logs', 'actor_role')) {
                $table->string('actor_role', 50)->nullable();
            }

            if (! Schema::hasColumn('zendy_logs', 'action')) {
                $table->string('action', 100);
            }

            if (! Schema::hasColumn('zendy_logs', 'first_name')) {
                $table->string('first_name', 100)->nullable();
            }

            if (! Schema::hasColumn('zendy_logs', 'last_name')) {
                $table->string('last_name', 100)->nullable();
            }

            if (! Schema::hasColumn('zendy_logs', 'email')) {
                $table->string('email')->nullable();
            }

            if (! Schema::hasColumn('zendy_logs', 'course')) {
                $table->string('course', 150)->nullable();
            }

            if (! Schema::hasColumn('zendy_logs', 'department')) {
                $table->string('department', 150)->nullable();
            }

            if (! Schema::hasColumn('zendy_logs', 'campus')) {
                $table->string('campus', 150)->nullable();
            }

            if (! Schema::hasColumn('zendy_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable();
            }

            if (! Schema::hasColumn('zendy_logs', 'user_agent')) {
                $table->text('user_agent')->nullable();
            }

            if (! Schema::hasColumn('zendy_logs', 'metadata')) {
                $table->json('metadata')->nullable();
            }
        });

        if (
            Schema::hasColumn('zendy_logs', 'actor_user_id')
            && ! $this->foreignKeyExists('zendy_logs', 'zendy_logs_actor_user_id_foreign')
        ) {
            Schema::table('zendy_logs', function (Blueprint $table) {
                $table->foreign('actor_user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('zendy_logs')) {
            return;
        }

        Schema::table('zendy_logs', function (Blueprint $table) {
            if (Schema::hasColumn('zendy_logs', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }

    private function foreignKeyExists(string $table, string $foreignKey): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->selectOne(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = ?',
            [$database, $table, $foreignKey, 'FOREIGN KEY']
        );

        return $result !== null;
    }
};
