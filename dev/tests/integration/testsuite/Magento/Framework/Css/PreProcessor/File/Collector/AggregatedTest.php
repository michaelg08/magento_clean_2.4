<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Css\PreProcessor\File\Collector;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;

class AggregatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Css\PreProcessor\File\Collector\Aggregated
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $backupRegistrar;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(
            [
                Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => [
                    DirectoryList::LIB_WEB => [
                        DirectoryList::PATH => dirname(dirname(__DIR__)) . '/_files/lib/web',
                    ],
                    DirectoryList::THEMES => [
                        DirectoryList::PATH => dirname(dirname(__DIR__)) . '/_files/design',
                    ],
                ],
            ]
        );
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');

        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $this->objectManager->create(
            'Magento\Framework\Filesystem',
            [
                'directoryList' => $this->objectManager->create(
                    'Magento\Framework\App\Filesystem\DirectoryList',
                    [
                        'root' => BP,
                        'config' => [
                            DirectoryList::THEMES => [
                                DirectoryList::PATH => dirname(dirname(__DIR__)) . '/_files/design',
                            ],
                        ]
                    ]
                )
            ]
        );

        /** @var \Magento\Framework\View\File\Collector\Base $sourceBase */
        $sourceBase = $this->objectManager->create(
            'Magento\Framework\View\File\Collector\Base', ['filesystem' => $filesystem, 'subDir' => 'web']
        );
        /** @var \Magento\Framework\View\File\Collector\Base $sourceBase */
        $overriddenBaseFiles = $this->objectManager->create(
            'Magento\Framework\View\File\Collector\Override\Base', ['filesystem' => $filesystem, 'subDir' => 'web']
        );
        $this->model = $this->objectManager->create(
            'Magento\Framework\Css\PreProcessor\File\Collector\Aggregated',
            ['baseFiles' => $sourceBase, 'overriddenBaseFiles' => $overriddenBaseFiles]
        );

        $reflection = new \ReflectionClass('Magento\Framework\Component\ComponentRegistrar');
        $paths = $reflection->getProperty('paths');
        $paths->setAccessible(true);
        $this->backupRegistrar = $paths->getValue();
        $paths->setValue(
            [ComponentRegistrar::MODULE => [], ComponentRegistrar::THEME => [], ComponentRegistrar::LANGUAGE => []]
        );
        $paths->setAccessible(false);

        ComponentRegistrar::register(
            ComponentRegistrar::MODULE,
            'Magento_Other',
            dirname(dirname(__DIR__)) . '/_files/code/Magento/Other'
        );

        ComponentRegistrar::register(
            ComponentRegistrar::MODULE,
            'Magento_Third',
            dirname(dirname(__DIR__)) . '/_files/code/Magento/Third'
        );
    }

    protected function tearDown()
    {
        $reflection = new \ReflectionClass('Magento\Framework\Component\ComponentRegistrar');
        $paths = $reflection->getProperty('paths');
        $paths->setAccessible(true);
        $paths->setValue($this->backupRegistrar);
        $paths->setAccessible(false);
    }

    /**
     * @magentoDataFixture Magento/Framework/Css/PreProcessor/_files/themes.php
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @param string $path
     * @param string $themeName
     * @param string[] $expectedFiles
     * @dataProvider getFilesDataProvider
     */
    public function testGetFiles($path, $themeName, array $expectedFiles)
    {
        /** @var \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory */
        $themeFactory = $this->objectManager->get('Magento\Framework\View\Design\Theme\FlyweightFactory');
        $theme = $themeFactory->create($themeName);
        $files = $this->model->getFiles($theme, $path);
        $actualFiles = [];
        foreach ($files as $file) {
            $actualFiles[] = $file->getFilename();
        }
        $this->assertEquals($expectedFiles, $actualFiles);

        /** @var $file \Magento\Framework\View\File */
        foreach ($files as $file) {
            if (!in_array($file->getFilename(), $expectedFiles)) {
                $this->fail(sprintf('File "%s" is not expected but found', $file->getFilename()));
            }
        }
    }

    /**
     * @return array
     */
    public function getFilesDataProvider()
    {
        $fixtureDir = dirname(dirname(__DIR__));
        return [
            'file in theme and parent theme' => [
                '1.file',
                'Test/default',
                [
                    str_replace(
                        '\\',
                        '/',
                         "$fixtureDir/_files/design/frontend/Test/default/web/1.file"
                    ),
                    str_replace(
                        '\\',
                        '/',
                        "$fixtureDir/_files/design/frontend/Test/parent/Magento_Second/web/1.file"
                    ),
                    str_replace(
                        '\\',
                        '/',
                        "$fixtureDir/_files/design/frontend/Test/default/Magento_Module/web/1.file"
                    ),
                ],
            ],
            'file in library' => [
                '2.file',
                'Test/default',
                [
                    str_replace(
                        '\\',
                        '/',
                        "$fixtureDir/_files/lib/web/2.file"
                    )
                ],
            ],
            'non-existing file' => [
                'doesNotExist',
                'Test/default',
                [],
            ],
            'file in library, module, and theme' => [
                '3.less',
                'Test/default',
                [
                    str_replace(
                        '\\',
                        '/',
                        "$fixtureDir/_files/lib/web/3.less"
                    ),
                    str_replace(
                        '\\',
                        '/',
                        "$fixtureDir/_files/code/Magento/Other/view/frontend/web/3.less"
                    ),
                    str_replace(
                        '\\',
                        '/',
                        "$fixtureDir/_files/design/frontend/Test/default/Magento_Third/web/3.less"
                    )
                ],
            ],
        ];
    }
}
