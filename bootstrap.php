<?php
spl_autoload_register(function($classToInclude) {
    
	// look for last namespace separator
	$pos = strrpos($classToInclude, '\\');
	if ($pos === false) {
		// no namespace, class portion only
		$namespace = '';
		$class	 = $classToInclude;
	} else {
		// pre-convert namespace portion to file path
		$namespace = substr($classToInclude, 0, $pos);
		$namespace = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
		$class = substr($classToInclude, $pos + 1);
	}
    
    $fileName = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';

	// the project's root directory
	$dir = __DIR__;
    
    // look for a source file
    $src = $dir 
            . DIRECTORY_SEPARATOR . $namespace
            . DIRECTORY_SEPARATOR . $fileName;
    
    if (is_readable($src)) {
        require $src;
    }
});