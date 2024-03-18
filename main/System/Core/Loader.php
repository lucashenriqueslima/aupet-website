<?php

set_include_path ( get_include_path () . PATH_SEPARATOR . __DIR__ . '/../../system/core/' . PATH_SEPARATOR . __DIR__ . '/../../system/libs/' . PATH_SEPARATOR . __DIR__ . '/../../system/utils/' . PATH_SEPARATOR . __DIR__ . '/../../module/' . PATH_SEPARATOR . __DIR__ . '/../../' );

/**
 * Classe Loader
 *
 * @author <contato@hibridaweb.com.br>
 * @copyright Copyright (c) 2015 Híbrida
 * @link http://www.hibridaweb.com.br
 */
class Loader {
	public static function loader_system() {
		spl_autoload_register ( function ($_className) {
			if (strpos ( $_className, '\\' )) {
				$className = str_replace ( '\\', DIRECTORY_SEPARATOR, $_className );
				$caminho = dirname ( dirname ( __DIR__ ) ) . "/" . str_replace ( '\\', DIRECTORY_SEPARATOR, $_className ) . '.php';
				$servicos = dirname ( dirname ( dirname ( __DIR__ ) ) ) . "/environment/" . str_replace ( '\\', DIRECTORY_SEPARATOR, $_className ) . '.php';
			} else {
				$className = str_replace ( '//', DIRECTORY_SEPARATOR, $_className );
				$caminho = dirname ( dirname ( __DIR__ ) ) . "/" . str_replace ( '//', DIRECTORY_SEPARATOR, $_className ) . '.php';
				$servicos = dirname ( dirname ( dirname ( __DIR__ ) ) ) . "/environment/" . str_replace ( '//', DIRECTORY_SEPARATOR, $_className ) . '.php';
			}
			if (Loader::file_exists_ci ( $caminho ))
				// componentes
				require_once Loader::file_exists_ci ( $caminho );
			else if (Loader::file_exists_ci ( $servicos ))
				// serviços
				require_once Loader::file_exists_ci ( $servicos );
			else {
				// website
				$website = dirname ( dirname ( dirname ( __FILE__ ) ) ) . "/../" . $className . '.php';
				if (Loader::file_exists_ci ( $website ))
					require_once Loader::file_exists_ci ( $website );
				// else
				// 	echo 'Arquivo não existe ' . $caminho;
			}
		} );
	}
	public static function file_exists_ci($file) {
		if (file_exists ( $file ))
			return $file;
		$lowerfile = strtolower ( $file );
		foreach ( glob ( dirname ( $file ) . '/*' ) as $file )
			if (strtolower ( $file ) == $lowerfile)
				return $file;
		return false;
	}
}

// END CLASS LOADER
Loader::loader_system ();
