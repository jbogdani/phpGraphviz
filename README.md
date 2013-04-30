# phpGraphviz
### An easy to setup php5 API for Graphviz. Graphviz should be installed on the server.
Only directed graphs (Graphviz' dot & tred) are actually tested.
 
## Building your URL Graphviz API
For a working example of the API, please check the well documented index.php file.

## URL parameters for building graphs
* dot_url: string, required, full URL pointing to DOT file
* format: string, optional, output file format (png, gif, jpeg, svg). Default value: png
* graph_type: string, optional, output graph type (directed, undirected, readial, circular, fdp, sfdb). Default vale: directed.

#### Minimal URL example
http://url-of-server-running-api/?dot_url=http://url-of-dot-file.dot
	

#### Customized URL example
http://url-of-server-running-api/?dot_url=http://url-of-dot-file.dot&format=svg&graph_type=undirected
