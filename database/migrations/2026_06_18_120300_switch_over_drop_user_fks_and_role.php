<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $fksToDrop = [
        ['table' => 'applications',   'name' => 'applications_user_id_foreign',   'on_delete' => 'CASCADE'],
        ['table' => 'notifications',  'name' => 'notifications_user_id_foreign',  'on_delete' => 'SET NULL'],
        ['table' => 'security_logs',  'name' => 'security_logs_user_id_foreign',  'on_delete' => 'CASCADE'],
        ['table' => 'login_logs',     'name' => 'login_logs_user_id_foreign',     'on_delete' => 'SET NULL'],
        ['table' => 'activity_logs',  'name' => 'activity_logs_user_id_foreign',  'on_delete' => 'CASCADE'],
    ];

    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        foreach ($this->fksToDrop as $fk) {
            try {
                $this->dropForeignKey($driver, $fk['table'], $fk['name']);
            } catch (Throwable) {
            }
        }

        DB::table('admins')->where('role', 'user')->delete();

        if (Schema::hasColumn('admins', 'role')) {
            $indexName = null;
            try {
                $indexName = $this->indexNameFor($driver, 'admins', 'role');
            } catch (Throwable) {
            }

            if ($indexName) {
                try {
                    DB::statement("DROP INDEX `{$indexName}` ON `admins`");
                } catch (Throwable) {
                    try {
                        DB::statement('DROP INDEX '.$indexName);
                    } catch (Throwable) {
                    }
                }
            }

            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if (! Schema::hasColumn('admins', 'role')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->enum('role', ['admin', 'user'])->default('admin')->after('password');
            });

            try {
                DB::statement('CREATE INDEX admins_role_index ON admins (role)');
            } catch (Throwable) {
            }
        }

        $userDb = DB::connection('user');
        $adminColumns = Schema::getColumnListing('admins');
        $skip = ['role', 'department', 'two_factor_secret', 'two_factor_confirmed_at'];

        $payload = [];
        foreach ($userDb->table('users')->get() as $row) {
            $row = array_intersect_key((array) $row, array_flip($adminColumns));
            $row = array_diff_key($row, array_flip($skip));
            $row['role'] = 'user';
            $payload[] = $row;
        }
        if (! empty($payload)) {
            DB::table('admins')->insert($payload);
        }

        foreach ($this->fksToDrop as $fk) {
            try {
                if (! $this->fkExists($driver, $fk['table'], $fk['name'])) {
                    DB::statement(
                        "ALTER TABLE `{$fk['table']}` ADD CONSTRAINT `{$fk['name']}` FOREIGN KEY (`user_id`) REFERENCES admins(id) ON DELETE {$fk['on_delete']}"
                    );
                }
            } catch (Throwable) {
            }
        }
    }

    private function fkExists(string $driver, string $table, string $fkName): bool
    {
        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA foreign_key_list('{$table}')");
            foreach ($rows as $row) {
                if (($row->from ?? '') === $fkName || ($row->id ?? '') === $fkName) {
                    return true;
                }
            }

            return false;
        }

        $dbName = DB::connection()->getDatabaseName();
        $row = DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
            [$dbName, $table, $fkName]
        );

        return ((int) ($row->c ?? 0)) > 0;
    }

    private function indexNameFor(string $driver, string $table, string $column): ?string
    {
        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA index_list('{$table}')");
            foreach ($rows as $row) {
                $info = DB::select("PRAGMA index_info('{$row->name}')");
                foreach ($info as $col) {
                    if (($col->name ?? '') === $column) {
                        return $row->name;
                    }
                }
            }

            return null;
        }

        $dbName = DB::connection()->getDatabaseName();
        $row = DB::selectOne(
            'SELECT INDEX_NAME FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1',
            [$dbName, $table, $column]
        );

        return $row->INDEX_NAME ?? null;
    }

    private function dropForeignKey(string $driver, string $table, string $fkName): void
    {
        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA foreign_key_list('{$table}')");
            $exists = false;
            foreach ($rows as $row) {
                if (($row->from ?? '') === $fkName || ($row->id ?? '') === $fkName) {
                    $exists = true;
                    break;
                }
            }
            if (! $exists) {
                return;
            }

            $createSql = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$table]);
            if (! $createSql || empty($createSql->sql)) {
                return;
            }

            $sql = $createSql->sql;
            $lines = explode("\n", $sql);
            $newLines = [];
            foreach ($lines as $line) {
                $upperLine = strtoupper(trim($line));
                if (str_contains($upperLine, 'FOREIGN KEY') && str_contains($upperLine, '"'.$fkName.'"')) {
                    $line = rtrim($line);
                    if (str_ends_with($line, ',')) {
                        $line = rtrim(substr($line, 0, -1));
                    }
                    if (empty(trim($line))) {
                        continue;
                    }
                }
                $newLines[] = $line;
            }
            $newSql = implode("\n", $newLines);
            $newSql = preg_replace('/,\s*,/', ',', $newSql);
            $newSql = trim($newSql);

            DB::statement('PRAGMA foreign_keys = OFF');
            DB::statement('ALTER TABLE '.$table.' RENAME TO __temp_'.$table);
            DB::statement($newSql);

            $columns = Schema::getColumnListing('__temp_'.$table);
            $colList = implode(', ', array_map(fn ($c) => '"'.$c.'"', $columns));
            DB::statement("INSERT INTO {$table} ({$colList}) SELECT {$colList} FROM __temp_{$table}");
            DB::statement("DROP TABLE __temp_{$table}");
            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`");
    }
};
