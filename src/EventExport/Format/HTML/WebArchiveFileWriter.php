<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications\HasUiTPASBrand;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\Specifications\HasVliegBrand;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\UitpasEventInfoServiceInterface;
use ValueObjects\String\String;
use CultuurNet\UDB3\EventExport\FileWriterInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use League\Flysystem\ZipArchive;

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
     * @var UitpasEventInfoServiceInterface
     */
    protected $uitpas;

    /**
     * @param HTMLFileWriter $htmlFileWriter
     */
    public function __construct(
        HTMLFileWriter $htmlFileWriter,
        UitpasEventInfoServiceInterface $uitpas = null
    ) {
        $this->htmlFileWriter = $htmlFileWriter;
        $this->uitpas = $uitpas;

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
     * @param \Traversable|array $events
     */
    protected function writeHtml($dir, $events)
    {
        $filePath = $dir . '/index.html';

        // TransformingIteratorIterator requires a Traversable,
        // so if $events is a regular array we need to wrap it
        // inside an ArrayIterator.
        if (is_array($events)) {
            $events = new \ArrayIterator($events);
        }

        $formatter = new EventFormatter($this->uitpas);

        $formattedEvents = new TransformingIteratorIterator(
            $events,
            function ($event) use ($formatter) {
                return $formatter->formatEvent($event);
            }
        );

        $this->htmlFileWriter->write(
            $this->expandTmpPath($filePath),
            $formattedEvents
        );
    }

    /**
     * {@inheritdoc}
     */
    abstract public function write($filePath, $events);
}
