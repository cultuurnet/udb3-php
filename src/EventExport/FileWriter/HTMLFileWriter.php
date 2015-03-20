<?php

namespace CultuurNet\UDB3\EventExport\FileWriter;

class HTMLFileWriter implements FileWriterInterface
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    protected $variables;

    /**
     * @param string $filePath
     * @param string $template
     * @param array $variables
     */
    public function __construct($filePath, $template, $variables)
    {
        $this->filePath = $filePath;
        $this->template = $template;
        $this->variables = $variables;
    }

    /**
     * {@inheritdoc}
     */
    public function write($events)
    {
        file_put_contents($this->filePath, $this->getHTML($events));
    }

    /**
     * @param \Traversable $events
     * @return string
     */
    public function getHTML($events)
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../../../templates');
        $twig = new \Twig_Environment($loader);

        $variables = $this->variables;


        $variables['events'] = $events;

        return $twig->render($this->template, $variables);
    }
}
