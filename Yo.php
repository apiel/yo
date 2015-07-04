<?php
namespace Ap\Bundle\YoBundle;

use Ap\Bundle\YoBundle\Loader\FilesystemLoader;

class Yo
{
    protected $_fileLoader;
    protected $_blocks = [];
    
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
      //echo $this->_loadTemplate($file); 
      //die();
      
      $start = microtime(true);
      $post = [
          //'page' => file_get_contents($file),
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
      curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8080');
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $output = curl_exec ($ch);
      curl_close ($ch);
      
      return $output;
    }
    
    protected function _loadTemplate($file)
    {
      $output = null;
      $filename = $this->_getFileName($file);
      if (file_exists($filename)) {
        $content = file_get_contents($filename);
        $output = $this->_parseTemplateFile($content);
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
    
    protected function _parseTemplateFile($content)
    {
      while($output = $this->_pregAction($content, ['include', 'block'])) {
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
          $this->_addBlock($params, $contentBlock, $pos);
          $content = substr($content, 0, $pos) . substr($content, $output->pos);
        }
      }
      return $content;
    }
    
    protected function _addBlock($name, $content, $pos) 
    {
      if (!isset($this->_blocks[$name])) {
        $this->_blocks[$name] = (object) [
          'pos' => $pos,
          'content' => $content,
        ];
      }
      else {
        $content = $this->_applyParentBlock($name, $content);
      }
      $this->_blocks[$name]->content = $content;
    }
    
    protected function _applyParentBlock($name, $content) 
    {
      if ($output = $this->_pregAction($content, ['blockParent'])) {
        $content = substr($content, 0, $output->pos) . $this->_blocks[$name]->content 
                 . substr($content, $output->pos);
      }
      return $content;
    }
    
    protected function _applyBlocks($content) 
    {
      foreach($this->_blocks as $block) {
        $content = substr($content, 0, $block->pos) 
                . $block->content . substr($content, $block->pos);
      }
      return $content;
    }


//    public function ____render($file, array $parameters = array())
//    {
//      $content = file_get_contents($file);
//      // do some stuff
//      $tmpHtml = tempnam(sys_get_temp_dir(), 'Yo') . '.html';
//      file_put_contents($tmpHtml, $content);
//      $tmpJs = tempnam(sys_get_temp_dir(), 'Yo') . '.js';
//      file_put_contents($tmpJs, json_encode($parameters));
//
//      $cmd = 'phantomjs '.__DIR__.'/yo.js file://'.$tmpHtml.' '.$tmpJs;
//      $start = microtime(true);
//      $buffer = shell_exec($cmd);
//      var_dump(microtime(true)-$start);
//      return $buffer;
//      
//        //return $file . '::do some stuff with phantomjs::' . json_encode($parameters);
//    }
}