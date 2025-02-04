<?php
namespace Royalcms\Component\LaravelSqlGenerator\Tests;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Royalcms\Component\LaravelSqlGenerator\SqlFormatter;
use Royalcms\Component\LaravelSqlGenerator\Tests\TestCase;

class SqlGeneratorTest extends TestCase
{
    use DatabaseTransactions;

    //Test create database sql file generate or not
    public function testFileExitOrNot()
    {
        $this->copyMigration();
        $this->artisan('sql:generate');
        $this->deleteMigration();

        $this->assertTrue(true);
        $this->assertDirectoryExists($this->directory);
        $this->assertFileExists($this->directory.'/database.sql');
    }

    public function testSqlExitOrNot()
    {
        $content = file_get_contents($this->directory.'/database.sql');
        $queries = SqlFormatter::splitQuery($content);
        foreach ($queries as $query) {

        }
    }
}
