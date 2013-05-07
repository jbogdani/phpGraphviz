<?php
/**
 * @author			Julian Bogdani <jbogdani@gmail.com>
 * @copyright		BraDypUS 2007-2013
 * @license			All rights reserved
 * @since			Apr 23, 2013
 * 
 * Makes directed Graphs using Graphviz from remote DOT files.
 * If required DOT files will be cleaned from redundant edges and a simple cycles controle will be performed.
 */

namespace graphviz;

class phpGraphviz
{
	private
		/**
		 * Path on server to dot file (inside tmp_dir) or to cleaned dot file 
		 */
		$dot_file,
		
		/**
		 * Absolute path to dot program
		 */
		$graphviz_path;
	
	
	/**
	 * 
	 * @param string $graphviz_path	Absolute path to graphviz executables
	 * @throws \Exception on errors
	 * 
	 * Sets main settings and performs basics checks on server settings.
	 */
	public function __construct($graphviz_path)
	{
		$this->graphviz_path = $graphviz_path;
	}
	
	/**
	 * Gets content of remote DOT file and saves it to server's temporary folder
	 * @param string $remotePath	Path, URL, to remote DOT file
	 * @throws \Exception on errors
	 */
	private function makeDotFile($remotePath)
	{
		$this->dot_file = sys_get_temp_dir() . '/' . uniqid('dot') . '.dot';
		
		$dot_content = file_get_contents($remotePath);
		
		if (!$dot_content)
		{
			throw new \Exception('Can not get content of file ' . $remotePath);
		}
		
		if (!@file_put_contents($this->dot_file, $dot_content))
		{
			throw new \Exception('Can not write content in ' . $this->dot_file);
		}
	}
	
	/**
	 * Cleans DOT file using TRED (transitive reduction filter for directed graphs)
	 * and saves file to server's temporary folder.
	 * @throws \Exception on errors
	 */
	private function cleanDotFile()
	{
		$command = $this->graphviz_path . 'tred ' . escapeshellarg($this->dot_file);
		
		exec($command, $msg, $return_val);
		
		if(is_array($msg))
		{
			if($msg[(count($msg)-2)] == "warning: G has cycle(s), transitive reduction not unique")
			{
				throw new \Exception('Cicles problem found! ' . var_export($msg, true));
			}
		}
		
		$text = implode("\n", $msg);
		
		$this->dot_file = str_replace('.dot', '-cleaned.dot', $this->dot_file);
		
		if (!file_put_contents($this->dot_file, $text))
		{
			throw new \Exception('Error in writing cleaned dot code in ' . $this->dot_file);
		}
	}
	
	/**
	 * Checks if image output format is valid
	 * @param string $format
	 * @return boolean
	 */
	private function isValidFormat($format)
	{
		$allowed_formats = array('png', 'gif', 'jpeg', 'svg');
		
		return  in_array($format, $allowed_formats);
	}
	
	/**
	 * 
	 * @param string $remotePath	Path, URL, to remote DOT file
	 * @param boolean $clean	If true the original DOT file will be cleaned using TRED
	 * @param string $format	Image file format. Optional (png, gif, jpeg, svg). Default value is png
	 * @param string $graph_type Graph type to create (directed, undirected, readial, circular, fdp, sfdb). Optional. Default directed
	 * @throws \Exception		on errors
	 * @return \graphviz\phpGraphviz
	 */
	public function getGraph($remotePath, $clean = false, $format = false, $graph_type = false)
	{
		if (!$format)
		{
			$format = 'png';
		}
		
		if (!$graph_type)
		{
			$graph_type = 'directed';
		}
		
		if (!$this->isValidFormat($format))
		{
			throw new \Exception('Filetype `' . $format . '` not valid.');
		}
		
		$this->makeDotFile($remotePath);
		
		if ($clean)
		{
			$this->cleanDotFile();
		}
		
		$outputfile = $this->dot_file . '.' . $format;
		
		if (!file_exists($this->dot_file))
		{
			throw new \Exception('Dot file ' . $this->dot_file . ' not found!');
		}
		
		switch($graph_type)
		{
			case 'directed':
				$cmd = 'dot';
				break;
				
			case 'undirected':
				$cmd = 'neato';
				break;
				
			case 'radial':
				$cmd = 'twopi';
				break;
				
			case 'circular':
				$cmd = 'circo';
				break;
				
			case 'fpd':
			case 'sfdp':
				$cmd = $graph_type;
				break;
				
			default:
				throw new \Exception('" ' . $graph_type . ' " graph type is not supported');
				break;
		}
		
		$command = $this->graphviz_path . $cmd
				. ' -T' . escapeshellarg($format)
				. ' -o' . escapeshellarg($outputfile)
				. ' ' . escapeshellarg($this->dot_file)
				. ' 2>&1';
		
		exec($command, $msg, $return_val);
		
		
		if ($msg AND !file_exists($outputfile))
		{
			throw new \Exception('Error in executing dot program');
		}
		
		clearstatcache();
		
		/**
		 * outputs created image
		 */
		$this->output($format, $outputfile);
	}
	
	/**
	 * Outputs image file content with file headers. Ready to use in any src attributo of an img tag.
	 * @param string $format output file format
	 * @param string $outputfile output file path + name
	 */
	private function output($format, $outputfile)
	{
		$type = 'image/' . $format;
		header('Content-Type:' . $type);
		header('Content-Length: ' . filesize($outputfile));
		readfile($outputfile);
	}
	
}