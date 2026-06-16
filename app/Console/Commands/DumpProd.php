<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Dump SQL pentru import în phpMyAdmin pe shared hosting (fără SSH).
 * PDO pur (fără mysqldump — nu există în containerul app). utf8mb4.
 *
 * Structură pentru TOATE tabelele + date DOAR pentru conținut (categorii/produse/proiecte/
 * migrations/users). Fără date pentru orders/sesiuni/cache (prod pornește curat).
 * Include `migrations` cu date → schema e la zi, fără `migrate` după import.
 */
class DumpProd extends Command
{
    protected $signature = 'db:dump-prod';

    protected $description = 'Generează un dump SQL (structură toate + date conținut) pentru import phpMyAdmin';

    /** Tabele cu DATE incluse (conținut + schema versioning). */
    private const WITH_DATA = [
        'categories', 'products', 'product_images', 'category_product',
        'projects', 'project_images', 'migrations', 'users',
    ];

    public function handle(): int
    {
        $pdo = DB::connection()->getPdo();
        $database = DB::connection()->getDatabaseName();

        $tables = array_map(
            fn ($r) => $r->{'Tables_in_'.$database} ?? array_values((array) $r)[0],
            DB::select('SHOW TABLES')
        );

        // Ordine dependență: tabelele-părinte ÎNAINTEA copiilor cu FK (altfel CREATE TABLE
        // pe pivot/child eșuează cu errno 150 la import — referința încă nu există).
        $order = [
            'migrations', 'users', 'password_reset_tokens', 'sessions',
            'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs',
            'categories', 'products', 'category_product', 'product_images',
            'projects', 'project_images', 'orders', 'order_items',
        ];
        usort($tables, function ($a, $b) use ($order) {
            $ia = array_search($a, $order, true);
            $ib = array_search($b, $order, true);
            $ia = $ia === false ? PHP_INT_MAX : $ia;
            $ib = $ib === false ? PHP_INT_MAX : $ib;

            return $ia === $ib ? strcmp($a, $b) : $ia <=> $ib;
        });

        $out = [];
        $out[] = '-- Decor Urban — dump producție '.date('c');
        $out[] = '-- Structură: toate tabelele · Date: '.implode(', ', self::WITH_DATA);
        $out[] = 'SET NAMES utf8mb4;';
        $out[] = 'SET FOREIGN_KEY_CHECKS=0;';
        $out[] = "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';";
        $out[] = '';

        $report = [];
        foreach ($tables as $table) {
            $out[] = '-- ----------------------------';
            $out[] = '-- Tabel: '.$table;
            $out[] = '-- ----------------------------';
            $out[] = 'DROP TABLE IF EXISTS `'.$table.'`;';

            $create = (array) DB::select('SHOW CREATE TABLE `'.$table.'`')[0];
            $out[] = ($create['Create Table'] ?? $create['Create View'] ?? '').';';
            $out[] = '';

            if (in_array($table, self::WITH_DATA, true)) {
                $count = $this->dumpData($pdo, $table, $out);
                $report[$table] = $count;
            }
        }

        $out[] = 'SET FOREIGN_KEY_CHECKS=1;';
        $sql = implode("\n", $out)."\n";

        $dir = storage_path('app/prod');
        @mkdir($dir, 0775, true);
        $base = $dir.'/decor-urban-'.date('Ymd-Hi');
        file_put_contents($base.'.sql', $sql);
        file_put_contents($base.'.sql.gz', gzencode($sql, 9));

        $this->info('Dump scris:');
        $this->line('  '.$base.'.sql ('.$this->human(filesize($base.'.sql')).')');
        $this->line('  '.$base.'.sql.gz ('.$this->human(filesize($base.'.sql.gz')).')');
        $this->newLine();
        $this->table(['Tabel (cu date)', 'Rânduri'], collect($report)->map(fn ($n, $t) => [$t, $n])->values()->all());
        $this->line('Charset: utf8mb4 · '.count($tables).' tabele (structură) · orders/sesiuni/cache fără date.');
        $this->warn('⚠️ Userul admin vine cu parola actuală — schimb-o imediat după deploy (vezi DEPLOY-PROD.md).');

        return self::SUCCESS;
    }

    private function dumpData(\PDO $pdo, string $table, array &$out): int
    {
        $rows = DB::table($table)->get();
        if ($rows->isEmpty()) {
            return 0;
        }

        $columns = array_keys((array) $rows->first());
        $colList = '`'.implode('`, `', $columns).'`';

        // Batch-uri de 100 rânduri per INSERT.
        foreach ($rows->chunk(100) as $chunk) {
            $values = [];
            foreach ($chunk as $row) {
                $cells = [];
                foreach ((array) $row as $val) {
                    if ($val === null) {
                        $cells[] = 'NULL';
                    } elseif (is_int($val) || is_float($val)) {
                        $cells[] = (string) $val;
                    } else {
                        $cells[] = $pdo->quote((string) $val);
                    }
                }
                $values[] = '('.implode(', ', $cells).')';
            }
            $out[] = 'INSERT INTO `'.$table.'` ('.$colList.") VALUES\n".implode(",\n", $values).';';
        }
        $out[] = '';

        return $rows->count();
    }

    private function human(int $bytes): string
    {
        return $bytes > 1048576
            ? round($bytes / 1048576, 1).' MB'
            : round($bytes / 1024, 1).' KB';
    }
}
