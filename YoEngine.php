<?php
namespace Ap\Bundle\YoBundle;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\FileLocatorInterface;

class YoEngine implements EngineInterface
{
    protected $yo;
    protected $parser;
    protected $locator;

    /**
     * Constructor.
     *
     * @param Yo_Engine             $yo A \Yo instance
     * @param TemplateNameParserInterface $parser   A TemplateNameParserInterface instance
     */
    public function __construct(Yo $yo, TemplateNameParserInterface $parser, 
            FileLocatorInterface $locator)
    {
        $this->yo = $yo;
        $this->parser = $parser;
        $this->locator = $locator;
    }

    /**
     * Renders a template.
     *
     * @param mixed $name       A template name
     * @param array $parameters An array of parameters to pass to the template
     *
     * @return string The evaluated template as a string
     *
     * @throws \InvalidArgumentException if the template does not exist
     * @throws \RuntimeException         if the template cannot be rendered
     */
    public function render($name, array $parameters = array())
    {
        try {
            $template = $this->parser->parse($name);
            $file = $this->locator->locate($template);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Unable to find template "%s".', $name));
        }
        
        return $this->yo->render($file, $parameters);
    }

    /**
     * Returns true if the template exists.
     *
     * @param mixed $name A template name
     *
     * @return Boolean true if the template exists, false otherwise
     */
    public function exists($name)
    {
        try {
            $this->load($name);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns true if this class is able to render the given template.
     *
     * @param string $name A template name
     *
     * @return Boolean True if this class supports the given resource, false otherwise
     */
    public function supports($name)
    {
        $template = $this->parser->parse($name);
        return 'yo' === $template->get('engine');
    }

    /**
     * Renders a view and returns a Response.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A Response instance
     *
     * @return Response A Response instance
     */
    public function renderResponse($view, array $parameters = array(), Response $response = null)
    {
        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($this->render($view, $parameters));

        return $response;
    }
}
