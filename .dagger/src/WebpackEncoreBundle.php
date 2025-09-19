<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Attribute\DefaultPath;
use Dagger\Attribute\Doc;
use Dagger\Attribute\ReturnsListOfType;
use Dagger\Container;
use Dagger\Directory;

#[DaggerObject]
#[Doc('A generated module for WebpackEncoreBundle functions')]
class WebpackEncoreBundle
{
    #[DaggerFunction]
    #[Doc('Access to all tools for static code analysis.')]
    public function static(
        #[DefaultPath('.')]
        Directory $source,

        #[Doc('The PHP version you want to use [8.1, 8.2, 8.3, 8.4]')]
        string $phpVersion = '8.1',

        #[Doc('The Symfony version you want to use against the bundle: a valid composer version[>=5.4, 6.4.*, 7.0.*, etc...]')]
        string $symfonyVersion = '>=5.4',

        ?Container $symfonyContainer = null,
    ): StaticObject {
        $symfonyContainer ??= (new ContainerBuilder())->symfonyWithVendor($source, $phpVersion, $symfonyVersion);

        return new StaticObject($symfonyContainer);
    }

    #[DaggerFunction]
    #[Doc('Access to all functions for tests.')]
    public function test(
        #[DefaultPath('.')]
        Directory $source,

        #[Doc('The PHP version you want to use [8.1, 8.2, 8.3, 8.4]')]
        string $phpVersion = '8.1',

        #[Doc('The Symfony version you want to use against the bundle: a valid composer version[>=5.4, 6.4.*, 7.0.*, etc...]')]
        string $symfonyVersion = '>=5.4',
        ?Container $symfonyContainer = null,
    ): TestObject {
        $symfonyContainer ??= (new ContainerBuilder())->symfony($source, $phpVersion, $symfonyVersion);

        return new TestObject($symfonyContainer);
    }

    #[DaggerFunction]
    #[Doc('Matrix tests')]
    #[ReturnsListOfType(TestObject::class)]
    public function testMatrix(
        #[DefaultPath('.')]
        Directory $source,
    ): array {
        $matrix = require __DIR__.'/../matrix-tests.php';

        $tests = [];
        foreach ($matrix as $job) {
            $testObject = $this->test($source, $job['php-version'], $job['symfony-version']);

            if ($job['minimum-stability'] ?? false) {
                $testObject->setMinimumStability($job['minimum-stability']);
            }

            if ($job['dependency-version'] ?? false) {
                $testObject->setDependencyVersion($job['dependency-version']);
            }

            $tests[] = $testObject;
        }

        return $tests;
    }

    #[DaggerFunction]
    #[Doc('Get test matrix as json.')]
    public function testMatrixJson(): string
    {
        return json_encode(require __DIR__.'/../matrix-tests.php');
    }
}
