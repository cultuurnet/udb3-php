<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultuurNet\UDB3\EventExport\FileWriterInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\MountManager;
use League\Flysystem\ZipArchive;
use League\Flysystem\Filesystem;

abstract class WebArchiveFileWriter implements FileWriterInterface
{
    /**
     * @var HTMLFileWriter
     */
    protected $htmlFileWriter;

    /**
     * @var MountManager
     */
    protected $mountManager;

    /**
     * @var string
     */
    protected $tmpDir;

    /**
     * @param HTMLFileWriter $HTMLFileWriter
     */
    public function __construct(
        HTMLFileWriter $HTMLFileWriter
    ) {
        $this->htmlFileWriter = $HTMLFileWriter;

        $this->tmpDir = sys_get_temp_dir();

        $this->mountManager = $this->initMountManager($this->tmpDir);
    }

    /**
     * @param \Traversable $events
     * @return string
     *   The path of the temporary directory, relative to the 'tmp://' mounted
     *   filesystem.
     */
    protected function createWebArchiveDirectory($events)
    {
        $tmpDir = $this->createTemporaryArchiveDirectory();

        $this->writeHtml($tmpDir, $events);
        $this->copyAssets($tmpDir);

        return $tmpDir;
    }

    protected function copyAssets($tmpDir)
    {
        $assets = $this->mountManager->listContents('assets:///', true);

        foreach ($assets as $asset) {
            if ($asset['type'] !== 'file') {
                continue;
            }

            $this->mountManager->copy(
                $asset['filesystem'] . '://' . $asset['path'],
                'tmp://' . $tmpDir . '/' . $asset['path']
            );
        };
    }

    /**
     * @param string $tmpDir
     * @return MountManager
     */
    protected function initMountManager($tmpDir)
    {
        return new MountManager(
            [
                'tmp' => new Filesystem(
                    new Local($tmpDir)
                ),
                // @todo make this configurable
                'assets' => new Filesystem(
                    new Local(__DIR__ . '/assets')
                ),
            ]
        );
    }

    protected function removeTemporaryArchiveDirectory($tmpDir)
    {
        $this->mountManager->deleteDir('tmp://' . $tmpDir);
    }

    /**
     * @return string
     *   The path of the temporary directory, relative to the 'tmp://' mounted
     *   filesystem.
     */
    protected function createTemporaryArchiveDirectory()
    {
        $exportDir = uniqid('html-export');
        $path = 'tmp://' . $exportDir;
        $this->mountManager->createDir($path);

        return $exportDir;
    }

    /**
     * Expands a path relative to the tmp:// mount point to a full path.
     *
     * @param string $dir
     * @return string
     */
    protected function expandTmpPath($tmpPath)
    {
        return $this->tmpDir . '/' . $tmpPath;
    }

    /**
     * @param string $dir
     * @param \Traversable $events
     */
    protected function writeHtml($dir, $events)
    {
        $filePath = $dir . '/index.html';
        $this->htmlFileWriter->write(
            $this->expandTmpPath($filePath),
            $events
        );
    }

    /**
     * {@inheritdoc}
     */
    abstract public function write($filePath, $events);
}
