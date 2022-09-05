<?php

use Migrations\AbstractMigration;

class CreateTestTables extends AbstractMigration
{
    /**
     * Creates tables
     *
     * @return void
     */
    public function up(): void
    {
        $this->table('authors')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->create();

        $this->table('articles')
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('text', 'text', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('published', 'string', [
                'default' => 'N',
                'limit' => 1,
                'null' => true,
            ])
            ->create();
    }

    public function down(): void
    {
        $this->table('authors')->drop()->save();
        $this->table('articles')->drop()->save();
    }
}
