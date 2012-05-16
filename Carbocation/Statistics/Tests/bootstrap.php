<?php
spl_autoload_register(function($spec) {
    
	// look for last namespace separator
	$pos = strrpos($spec, '\\');
	if ($pos === false) {
		// no namespace, class portion only
		$namespace = '';
		$class	 = $spec;
	} else {
		// pre-convert namespace portion to file path
		$namespace = substr($spec, 0, $pos);
		$namespace = str_replace('\\', DIRECTORY_SEPARATOR, $namespace)
				   . DIRECTORY_SEPARATOR;
	
		// class portion
		$class = substr($spec, $pos + 1);
	}
    
    $file = $class . '.php';

	// the root directory
	$dir = dirname(dirname(dirname(__DIR__)));
    
    // look for a source file
    $src = $dir 
            . DIRECTORY_SEPARATOR . $namespace . $file;
    
    if (is_readable($src)) {
        require $src;
    }
});