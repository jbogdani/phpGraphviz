<?php
/**
 * @author			Julian Bogdani <jbogdani@gmail.com>
 * @copyright		BraDypUS 2007-2013
 * @license			All rights reserved
 * @since			Apr 23, 2013
 */

use graphviz\phpGraphviz;

try
{
	/**
	 * System settings
	 */
	$graphviz_path = '/usr/local/bin/';
	
	
	/**
	 * URL paramaters settings
	 */
	// dot_url: string, required, full URL to dot file.
	if (!$_GET['dot_url'])
	{
		throw new Exception('No path to dot file found!');
	}
	
	// dot_url is the full url to the DOT file to process
	$dot_url = $_GET['dot_url']; 

	// format: string, optional, output file format (png, gif, jpeg, svg). Default value: png.
	$_GET['format'] ? $format = strtolower($_GET['format']) : '';

	// graph_type: string, optional, output graph type (directed, undirected, readial, circular, fdp, sfdb). Default vale: directed.
	$_GET['graph_type'] ? $graph_type = strtolower($_GET['graph_type']) : '';
	
	// dont_clean: boolean, optinal. if true the transitive reduction filter for directed graphs will not be applied
	$clean  = $_GET['dont_clean'] ? false : true;
	
	
	
	// include class
	require_once 'phpGraphviz.php';

	// initialize object
	$graphviz = new graphviz\phpGraphviz($graphviz_path);

	$graphviz->getGraph($dot_url, $clean, $format, $graph_type);
}
// catch errors
catch (Exception $e)
{	// echo error message
	echo "Error: " . $e->getMessage();
}
