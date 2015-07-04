<?php

namespace Ap\Bundle\YoBundle\Loader;

use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Config\FileLocatorInterface;

class FilesystemLoader
{
    protected $locator;
    protected $parser;

    /**
     * Constructor.
     *
     * @param FileLocatorInterface        $locator A FileLocatorInterface instance
     * @param TemplateNameParserInterface $parser  A TemplateNameParserInterface instance
     */
    public function __construct(FileLocatorInterface $locator, 
            TemplateNameParserInterface $parser)
    {
        $this->locator = $locator;
        $this->parser = $parser;
    }

    /**
     * Helper function for getting template file name.
     *
     * @param string $name
     *
     * @return string Template file name
     */
    public function getFileName($name)
    {
        $name = (string) $name;
        try {
            $template = $this->parser->parse($name);
            $file = $this->locator->locate($template);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Unable to find template "%s".', $name));
        }

        return $file;
    }
}
