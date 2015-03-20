<?php

namespace CultuurNet\UDB3\EventExport\FileWriter;

class HTMLFileWriter
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
     * @param mixed $events
     */
    public function exportEvents($events)
    {
        $this->variables['events'] = $events;
    }

    /**
     * @return string
     */
    public function getHTML()
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../../../templates');
        $twig = new \Twig_Environment($loader);
        return $twig->render($this->template, $this->variables);
    }

    public function close()
    {
        file_put_contents($this->filePath, $this->getHTML());
    }
}
