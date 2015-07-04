<?php
namespace Ap\Bundle\YoBundle;

use Ap\Bundle\YoBundle\Loader\FilesystemLoader;

class Yo
{
    protected $_fileLoader;
    protected $_blocks = [];
    protected $_port = 8080;
    protected $_portEnd = 8081;
    
    public function __construct(FilesystemLoader $fileLoader = null)
    {
      $this->_fileLoader = $fileLoader;
    }
        
    protected function _getFileName($name)
    {
      if (isset($this->_fileLoader)) {
        $name = $this->_fileLoader->getFileName($name);
      }
      return $name;
    }
        
    public function render($file, array $parameters = array())
    {
      $start = microtime(true);
      $post = [
          'page' => $this->_applyBlocks($this->_loadTemplate($file)),
          'data' => $parameters,
      ];
      $output = $this->_curl($post);
      var_dump(microtime(true)-$start);
      
      return $output;
    }
    
    protected function _curl($post)
    {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:' . $this->_getCurlPort());
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $output = curl_exec ($ch);
      curl_close ($ch);
      
      return $output;
    }
    
    protected function _getCurlPort()
    {
      $file = 'yoPort';
      $port = $this->_port;
      if (file_exists($file)) {
        $port = file_get_contents('yoPort');
        if (++$port > $this->_portEnd) {
          $port = $this->_port;
        }
      }
      file_put_contents($file, $port);
      var_dump($port);
      return $port;
    }
    
    protected function _loadTemplate($file)
    {
      $output = null;
      $filename = $this->_getFileName($file);
      if (file_exists($filename)) {
        $content = file_get_contents($filename);
        $output = $this->_parseActions($content);
      }
      else {
        throw new Exception('File does not exist. ' . $filename);
      }
      return $output;
    }
    
    protected function _pregAction(&$content, $actions)
    {
      $ret = null;
      if (preg_match("/\<\!\-\-(".join('|', $actions).")\(([\w\.\-\:]*)\)\-\-\>/m", $content, $output, PREG_OFFSET_CAPTURE)) {
        $ret = (object)[
          'action' =>  $output[1][0],
          'params' => $output[2][0],
          'pos' => $output[0][1],
        ];
        $content = substr($content, 0, $ret->pos) 
                . substr($content, $ret->pos + strlen($output[0][0]));
      }
      return $ret;
    }
    
    protected function _parseActions($content, $actions = ['include', 'block'])
    {
      while($output = $this->_pregAction($content, $actions)) {
        $content = $this->_processAction($content, $output->action, 
                                $output->params, $output->pos);
      }
      return $content;
    }
    
    protected function _processAction($content, $action, $params, $pos)
    {
      $fn = '__' . $action . 'Action';
      if (method_exists($this, $fn)) {
        $content = $this->$fn($content, $params, $pos);
      }
      return $content;
    }
    
    protected function __includeAction($content, $params, $pos)
    {
      $inc = $this->_loadTemplate($params);
      $content = substr($content, 0, $pos) . $inc . substr($content, $pos);
      return $content;
    }
    
    protected function __blockAction($content, $params, $pos)
    {
      if ($output = $this->_pregAction($content, ['block', 'blockEnd'])) {
        if ($output->action === 'block') {
          throw new Exception('A block cannot contain another block.');
        }
        else {
          $contentBlock = substr($content, $pos, $output->pos - $pos);
          $content = substr($content, 0, $pos) 
                  . $this->_addBlock($params, $contentBlock) 
                  . substr($content, $output->pos);
        }
      }
      return $content;
    }
    
    protected function _addBlock($name, $content) 
    {
      $ret = '<!--blockRender('.$name.')-->';
      if (isset($this->_blocks[$name])) {
        $ret = '';
        $content = $this->_applyParentBlock($name, $content);
      }
      $this->_blocks[$name] = $content;
      
      return $ret;
    }
    
    protected function _applyParentBlock($name, $content) 
    {
      if ($output = $this->_pregAction($content, ['blockParent'])) {
        $content = substr($content, 0, $output->pos) . $this->_blocks[$name] 
                 . substr($content, $output->pos);
      }
      return $content;
    }
    
    protected function _applyBlocks($content) 
    {
      return $this->_parseActions($content, ['blockRender']);
    }
    
    protected function __blockRenderAction($content, $params, $pos)
    {
      $content = substr($content, 0, $pos) . $this->_blocks[$params] 
              . substr($content, $pos);
      return $content;
    }
}