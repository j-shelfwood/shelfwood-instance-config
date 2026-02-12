<?php

use Shelfwood\InstanceConfig\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(TestCase::class)->in('Feature');
uses(TestCase::class)->group('unit')->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toHaveInstanceId', function (string $id) {
    expect($this->value->id())->toBe($id);
    return $this;
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function createInstanceConfig(string $basePath, string $instanceId, string $filename, array $frontmatter, string $content = ''): void
{
    $yaml = \Symfony\Component\Yaml\Yaml::dump($frontmatter);
    $fullContent = "---\n{$yaml}---\n\n{$content}";

    $fullPath = "{$basePath}/{$instanceId}/{$filename}";
    $directory = dirname($fullPath);

    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    file_put_contents($fullPath, $fullContent);
}
