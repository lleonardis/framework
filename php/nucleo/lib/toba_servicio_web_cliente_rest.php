<?php
/**
 * Clase que representa un cliente de WS REST
 * @package Centrales
 */
class toba_servicio_web_cliente_rest extends toba_servicio_web_cliente
{
	const HEADER_VERSION = 'API-Version';
	const VERIFY_PEER_VAR = 'TOBA_REST_VERIFY_PEER';
	const ENV_PREFIX = 'CREDENCIALES_API_BASIC_';
	
	protected $guzzle;
	private $toba_verify_peer = true;
	
	function __construct($opciones, $id_servicio, $proyecto = null) 
	{
		parent::__construct($opciones, $id_servicio, $proyecto);
		$entorno = getenv(self::VERIFY_PEER_VAR);
		if ($entorno !== false) {
			$this->toba_verify_peer = ($entorno == '1');
		}
	}

	static function conectar($id_servicio, $opciones=array(), $proyecto = null)
	{
		if (! isset($proyecto)) {
			$proyecto = toba_editor::activado() ? toba_editor::get_proyecto_cargado() : toba::proyecto()->get_id();
		}
		self::get_modelo_proyecto($proyecto);
		$ini = self::get_datos_conexion($id_servicio);
		$opciones_ini = $ini->get_datos_entrada('conexion');

		//Convierte todos los '1' de texto en true
		foreach (array_keys($opciones_ini) as $id_opcion) {
			if ($opciones_ini[$id_opcion] === '1' || $opciones_ini[$id_opcion] === 1) {
				$opciones_ini[$id_opcion] = true;
			}
		}

		//-- Mezcla con las opciones recibidas y crea el objeto
		$opciones = array_merge($opciones_ini, $opciones);
		if (! isset($opciones['to'])) {
			throw new toba_error_def("Debe indicar la URL destino en el campo 'to'");
		}

		toba::logger()->debug("Invocando servicio $id_servicio. Opciones:<br>". var_export($opciones, true));
		$servicio = new toba_servicio_web_cliente_rest($opciones, $id_servicio);
		return $servicio;
	}
	
	/**
	 * @return GuzzleHttp\Client
	 */
	function guzzle()
	{
		if (! isset($this->guzzle)) {
			$options = array('base_uri' => $this->opciones['to']);
			if (isset($this->opciones['auth_tipo'])) {
				if ($this->opciones['auth_tipo'] != 'ssl') {
					$options['auth'] = array($this->opciones['auth_usuario'], 	$this->opciones['auth_password'], $this->opciones['auth_tipo']);
				} else {					
					if (isset($this->opciones['cert_file'])) {								//Busco el certificado del cliente
						if (isset($this->opciones['cert_pwd'])) {								//Si requiere un pwd se lo agrego
							$options['cert'] = array(realpath($this->opciones['cert_file']), $this->opciones['cert_pwd']);
						} else {
							$options['cert'] = realpath($this->opciones['cert_file']);
						}
					}
					if (isset($this->opciones['key_file'])) {
						$options['ssl_key'] = realpath($this->opciones['key_file']);
					}					
					//Agrego el certificado de la CA para verificar el certificado del servidor
					$options['verify'] = (isset($this->opciones['ca_cert']))? array($this->opciones['ca_cert']) : $this->toba_verify_peer;
				}
			}
			$this->guzzle = $client = new GuzzleHttp\Client($options);
		}
		return $this->guzzle;	
	}

	static function get_version_api($response)
	{		
		if ($response->hasHeader(self::HEADER_VERSION)) {
			$string_version = $response->getHeader(self::HEADER_VERSION);			
			return new toba_version(current($string_version));
		} else {
			return null;
		}
	}
	
	static function get_datos_conexion($id_servicio)
	{
		$var_name = self::ENV_PREFIX  . strtoupper($id_servicio);
		$env_value = \getenv($var_name);
		if (false === $env_value) {
			$archivo = toba_modelo_rest::get_path_archivo(self::$modelo_proyecto, toba_modelo_rest::TIPO_CLIENTE, $id_servicio);
			toba::config()->add_config_file('rest_cliente', $archivo);
			toba::config()->load();
			$datos = toba::config()->get_seccion('rest_cliente');
		} else {
			$conf = parse_rest_config_str($env_value);
			$datos = array('conexion' =>  array(
												'auth_usuario' => $conf[0][0], 
												'auth_password' => $conf[0][1], 
												'to' => $conf[0][2], 
												'auth_tipo' => 'basic'));
		}
		
		//Devuelvo un ini para mantener contrato
		$ini = new toba_ini();
		$ini->set_entradas($datos);
		return $ini;
	}
}
