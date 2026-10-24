<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('users', 'admins');

        $this->fixIndexNames('admins', 'users_', 'admins_');
    }

    public function down(): void
    {
        Schema::rename('admins', 'users');
    }

    private function fixIndexNames(string $table, string $oldPrefix, string $newPrefix): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        $uniqueMap = [];
        $indexList = DB::select("PRAGMA index_list('{$table}')");
        foreach ($indexList as $idx) {
            $uniqueMap[$idx->name] = (bool) ($idx->unique ?? false);
        }

        $indexes = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name=?", [$table]);

        foreach ($indexes as $idx) {
            if (! str_starts_with($idx->name, $oldPrefix)) {
                continue;
            }

            $columns = [];
            $info = DB::select("PRAGMA index_info('{$idx->name}')");
            foreach ($info as $col) {
                $columns[] = '"'.$col->name.'"';
            }
            $colList = implode(', ', $columns);

            $newName = $newPrefix.substr($idx->name, strlen($oldPrefix));
            $isUnique = $uniqueMap[$idx->name] ?? false;

            DB::statement("DROP INDEX \"{$idx->name}\"");

            $keyword = $isUnique ? 'UNIQUE INDEX' : 'INDEX';
            DB::statement("CREATE {$keyword} \"{$newName}\" ON \"{$table}\" ({$colList})");
        }
    }
};
