<?php
//use SIU\ManejadorSalidaBootstrap\bootstrap_factory;
//use SIU\ManejadorSalidaBootstrap\bootstrap_config;

class contexto_ejecucion extends toba_contexto_ejecucion
{
	function conf__inicial()
	{
		require_once('php_referencia.php');
		//toba::menu()->set_abrir_nueva_ventana();
		toba::db()->set_parser_errores(new toba_parser_error_db_postgres7());
		toba::mensajes()->set_fuente_ini(toba::proyecto()->get_path().'/mensajes.ini');

		//Autenticacion personalizada
		/*$autentificacion = new toba_autenticacion_ldap('ldap-test.siu.edu.ar', "dc=ldap,dc=siu,dc=edu,dc=ar");
		toba::manejador_sesiones()->set_autenticacion($autentificacion);*/
		//------------------------------------------------------------------------------------------------------------//
		//     Cambio el manejador de salida en runtime (descomentar use arriba)
		//------------------------------------------------------------------------------------------------------------//
		//Instanciacion del provider base para boostrap
		/*$bootstrap_config = new bootstrap_factory();
		toba::output()->registrarServicio($bootstrap_config);				
		bootstrap_config::setMainColor( '#8B0C73');
		bootstrap_config::setLogoNombre(toba_recurso::imagen_proyecto('logo.gif', false));

		//Instanciacion del provider bootstrap extendido (en este caso propio del proyecto)
		/*$referencia_config = new referencia_factory();
		toba::output()->registrarServicio($referencia_config);
		referencia_config::setMainColor( '#11DD13');	*/
	}
	
	function conf__final()
	{
		
	}
}
?>
