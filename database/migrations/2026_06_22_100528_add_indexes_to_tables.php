<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function hasIndex(string $table, string $column): bool
    {
        $conn = Schema::getConnection();
        $indexName = $table . '_' . $column . '_index';
        $dbDriver = $conn->getDriverName();

        if ($dbDriver === 'sqlite') {
            $results = DB::select("PRAGMA index_list(" . $conn->getTablePrefix() . $table . ")");
            foreach ($results as $row) {
                if ($row->name === $indexName) {
                    return true;
                }
            }
            return false;
        }

        // For mysql
        $databaseName = $conn->getDatabaseName();
        $results = DB::select("
            SELECT INDEX_NAME 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND INDEX_NAME = ?
        ", [$databaseName, $conn->getTablePrefix() . $table, $indexName]);

        return !empty($results);
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Users Table (index on role)
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            if (!$this->hasIndex('users', 'role')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->index('role');
                });
            }
        }

        // 2. Checkins Table (index on status and created_at)
        if (Schema::hasTable('checkins')) {
            Schema::table('checkins', function (Blueprint $table) {
                if (!$this->hasIndex('checkins', 'status')) {
                    $table->index('status');
                }
                if (!$this->hasIndex('checkins', 'created_at')) {
                    $table->index('created_at');
                }
            });
        }

        // 3. Comments Table (index on created_at)
        if (Schema::hasTable('comments')) {
            if (!$this->hasIndex('comments', 'created_at')) {
                Schema::table('comments', function (Blueprint $table) {
                    $table->index('created_at');
                });
            }
        }

        // 4. Food Tour Diaries Table (index on created_at)
        if (Schema::hasTable('food_tour_diaries')) {
            if (!$this->hasIndex('food_tour_diaries', 'created_at')) {
                Schema::table('food_tour_diaries', function (Blueprint $table) {
                    $table->index('created_at');
                });
            }
        }

        // 5. Food Tours Table (index on status and shared_at)
        if (Schema::hasTable('food_tours')) {
            Schema::table('food_tours', function (Blueprint $table) {
                if (!$this->hasIndex('food_tours', 'status')) {
                    $table->index('status');
                }
                if (!$this->hasIndex('food_tours', 'shared_at')) {
                    $table->index('shared_at');
                }
            });
        }

        // 6. Daily Food Logs Table (index on log_date)
        if (Schema::hasTable('daily_food_logs')) {
            if (!$this->hasIndex('daily_food_logs', 'log_date')) {
                Schema::table('daily_food_logs', function (Blueprint $table) {
                    $table->index('log_date');
                });
            }
        }

        // 7. Purchase Invoices Table (index on invoice_date)
        if (Schema::hasTable('purchase_invoices')) {
            if (!$this->hasIndex('purchase_invoices', 'invoice_date')) {
                Schema::table('purchase_invoices', function (Blueprint $table) {
                    $table->index('invoice_date');
                });
            }
        }

        // 8. Food Safety Certificates Table (index on expired_at)
        if (Schema::hasTable('food_safety_certificates')) {
            if (!$this->hasIndex('food_safety_certificates', 'expired_at')) {
                Schema::table('food_safety_certificates', function (Blueprint $table) {
                    $table->index('expired_at');
                });
            }
        }

        // 9. Food Supply Contracts Table (index on signed_at)
        if (Schema::hasTable('food_supply_contracts')) {
            if (!$this->hasIndex('food_supply_contracts', 'signed_at')) {
                Schema::table('food_supply_contracts', function (Blueprint $table) {
                    $table->index('signed_at');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Users Table
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            if ($this->hasIndex('users', 'role')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropIndex(['role']);
                });
            }
        }

        // 2. Checkins Table
        if (Schema::hasTable('checkins')) {
            Schema::table('checkins', function (Blueprint $table) {
                if ($this->hasIndex('checkins', 'status')) {
                    $table->dropIndex(['status']);
                }
                if ($this->hasIndex('checkins', 'created_at')) {
                    $table->dropIndex(['created_at']);
                }
            });
        }

        // 3. Comments Table
        if (Schema::hasTable('comments')) {
            if ($this->hasIndex('comments', 'created_at')) {
                Schema::table('comments', function (Blueprint $table) {
                    $table->dropIndex(['created_at']);
                });
            }
        }

        // 4. Food Tour Diaries Table
        if (Schema::hasTable('food_tour_diaries')) {
            if ($this->hasIndex('food_tour_diaries', 'created_at')) {
                Schema::table('food_tour_diaries', function (Blueprint $table) {
                    $table->dropIndex(['created_at']);
                });
            }
        }

        // 5. Food Tours Table
        if (Schema::hasTable('food_tours')) {
            Schema::table('food_tours', function (Blueprint $table) {
                if ($this->hasIndex('food_tours', 'status')) {
                    $table->dropIndex(['status']);
                }
                if ($this->hasIndex('food_tours', 'shared_at')) {
                    $table->dropIndex(['shared_at']);
                }
            });
        }

        // 6. Daily Food Logs Table
        if (Schema::hasTable('daily_food_logs')) {
            if ($this->hasIndex('daily_food_logs', 'log_date')) {
                Schema::table('daily_food_logs', function (Blueprint $table) {
                    $table->dropIndex(['log_date']);
                });
            }
        }

        // 7. Purchase Invoices Table
        if (Schema::hasTable('purchase_invoices')) {
            if ($this->hasIndex('purchase_invoices', 'invoice_date')) {
                Schema::table('purchase_invoices', function (Blueprint $table) {
                    $table->dropIndex(['invoice_date']);
                });
            }
        }

        // 8. Food Safety Certificates Table
        if (Schema::hasTable('food_safety_certificates')) {
            if ($this->hasIndex('food_safety_certificates', 'expired_at')) {
                Schema::table('food_safety_certificates', function (Blueprint $table) {
                    $table->dropIndex(['expired_at']);
                });
            }
        }

        // 9. Food Supply Contracts Table
        if (Schema::hasTable('food_supply_contracts')) {
            if ($this->hasIndex('food_supply_contracts', 'signed_at')) {
                Schema::table('food_supply_contracts', function (Blueprint $table) {
                    $table->dropIndex(['signed_at']);
                });
            }
        }
    }
};
