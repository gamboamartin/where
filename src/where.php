<?php
namespace gamboamartin\where;
use gamboamartin\errores\errores;
use gamboamartin\src\sql;
use gamboamartin\src\validaciones;
use gamboamartin\validacion\validacion;
use stdClass;

class where
{
    private errores $error;
    private validacion $validacion;

    public function __construct()
    {
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * La función 'and_filtro_fecha' agrega 'AND' al string dado si este no está vacío.
     *
     * @param string $txt Texto que se verificará si está vacío o no.
     * @return string Devuelve el texto original con ' AND ' agregado si el texto original no estaba vacío,
     * de lo contrario, devuelve el texto original.
     */
    final public function and_filtro_fecha(string $txt): string
    {
        $and = '';
        if($txt !== ''){
            $and = ' AND ';
        }
        return $and;
    }

    /**
     * Esta función asigna una serie de filtros SQL a un objeto stdClass y retorna este objeto.
     * Cada filtro es una cadena de texto formateada como una sentencia SQL.
     *
     * @param string $diferente_de_sql     La sentencia SQL para el filtro 'diferente_de'.
     * @param string $filtro_especial_sql  La sentencia SQL para el filtro especial.
     * @param string $filtro_extra_sql     La sentencia SQL para el filtro extra.
     * @param string $filtro_fecha_sql     La sentencia SQL para el filtro de fecha.
     * @param string $filtro_rango_sql     La sentencia SQL para el filtro de rango.
     * @param string $in_sql               La sentencia SQL para el filtro 'IN'.
     * @param string $not_in_sql           La sentencia SQL para el filtro 'NOT IN'.
     * @param string $sentencia            La sentencia SQL completa.
     * @param string $sql_extra            Cualquier sentencia SQL extra.
     *
     * @return stdClass El objeto que contiene todos los filtros SQL.
     */
   final public function asigna_data_filtro(string $diferente_de_sql, string $filtro_especial_sql,
                                                string $filtro_extra_sql, string $filtro_fecha_sql,
                                                string $filtro_rango_sql, string $in_sql, string $not_in_sql,
                                                string $sentencia, string $sql_extra): stdClass
    {
        $filtros = new stdClass();
        $filtros->sentencia = $sentencia ;
        $filtros->filtro_especial = $filtro_especial_sql;
        $filtros->filtro_rango = $filtro_rango_sql;
        $filtros->filtro_extra = $filtro_extra_sql;
        $filtros->in = $in_sql;
        $filtros->not_in = $not_in_sql;
        $filtros->diferente_de = $diferente_de_sql;
        $filtros->sql_extra = $sql_extra;
        $filtros->filtro_fecha = $filtro_fecha_sql;
        return $filtros;
    }

    /**
     * TOTAL
     * Esta función procesa las entradas proporcionadas y devuelve el "campo" apropiado.
     *
     * @param array|string|null $data los datos proporcionados para extraer el campo. Pueden ser de tipos array, string o null.
     * @param string $key la clave proporcionada para extraer el campo del array.
     * @return string|array Devuelve el "campo" después de ser procesado y garantiza que no contenga caracteres de escape.
     *
     * @throws errores si la clave proporcionada está vacía.
     * @url https://github.com/gamboamartin/where/wiki/src.where.campo
     */
    private function campo(array|string|null $data, string $key):string|array{
        if($key === ''){
            return $this->error->error(mensaje: "Error key vacio",data:  $key, es_final: true);
        }
        $campo = $data['campo'] ?? $key;
        return addslashes($campo);
    }

    /**
     * La función campo_data_filtro se usa para aplicar ciertas validaciones en la clave del array $data_filtro.
     *
     * @param  array $data_filtro El array de entrada que se tiene que validar.
     * @throws errores Si la clave del array está vacía o si la clave no es un string válido (no numérico).
     * @return string|array Devuelve la clave del array $data_filtro después de apliar trim() si la validación es exitosa.
     *                     En caso de error, se devuelve un array con los detalles del error.
     *
     */
    private function campo_data_filtro(array $data_filtro): string|array
    {
        if(count($data_filtro) === 0){
            return $this->error->error(mensaje:'Error data_filtro esta vacio',  data:$data_filtro, es_final: true);
        }
        $campo = key($data_filtro);
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: "Error key vacio",data:  $campo, es_final: true);
        }
        if(is_numeric($campo )){
            return $this->error->error(mensaje: "Error key debe ser un texto valido",data:  $campo, es_final: true);
        }
        return trim($campo);

    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función valida el campo proporcionado para ser filtrado y verifica si el campo es parte de una subconsulta.
     * Si el campo proporcionado está vacío, se retorna un error.
     * Una verificación adicional se realiza para garantizar si el campo proporcionado pertenece a una subconsulta.
     *
     * @param string $campo Representa el campo en el que se aplicará el filtro especial.
     * @param array $columnas_extra Un array de columnas adicionales que pueden estar presentes en la tabla objetivo.
     *
     * @return string|array Retorna el campo de filtro si la validación es exitosa o un objeto de error si hay algún problema.
     *
     * @throws errores Puede lanzar una excepción si el campo proporcionado es una subconsulta incorrecta.
     * @version 16.145.0
     */
    private function campo_filtro_especial(string $campo, array $columnas_extra): array|string
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje:'Error campo esta vacio',  data:$campo, es_final: true);
        }

        $es_subquery = $this->es_subquery(campo: $campo,columnas_extra:  $columnas_extra);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al subquery bool',  data:$es_subquery);
        }

        if($es_subquery){
            $campo = $columnas_extra[$campo];
        }
        return $campo;

    }

    /**
     * TOTAL
     * Función que realiza una comparación.
     *
     * Esta función toma un array, cadena de texto, o valor null como datos de entrada,
     * junto con una cadena de texto por defecto. Revisa si hay una llave 'comparacion'
     * en los datos de entrada y, si la hay, retorna su valor. Si no hay tal llave,
     * la función retorna la cadena de texto por defecto.
     *
     * @param array|string|null $data Los datos de entrada para la comparación.
     * @param string $default La cadena de texto por defecto a retornar si la llave 'comparacion' no se encuentra.
     * @return string El resultado de la comparación, o la cadena por defecto si no hay comparación.
     * @version 16.96.0
     * @url https://github.com/gamboamartin/where/wiki/src.where.comparacion
     */
    private function comparacion(array|string|null $data, string $default):string{
        return $data['comparacion'] ?? $default;
    }

    /**
     * TOTAL
     * La función comparacion_pura compara los datos pasados con las columnas extra en base a una llave.
     *
     * @param array $columnas_extra Las columnas extra a considerar en la comparación.
     * @param array|string|null $data Los datos que se van a comparar con las columnas extra, puede ser un array,
     *  un string o nulo.
     * @param string $key La llave que se usará en la comparación.
     *
     * @return array|stdClass Retorna un objeto con los resultados de la comparación, si se encuentra algún error
     *  durante la comparación,
     * se retornará un objeto con información del error.
     *
     * @throws errores Si la llave esta vacía.
     * @throws errores Si los datos están vacíos.
     * @throws errores Si hay un error al maquetar el campo con los datos y la llave.
     * @throws errores Si hay un error al validar la maquetación.
     * @version 16.99.0
     * @url https://github.com/gamboamartin/where/wiki/src.where.comparacion_pura
     *
     */
    private function comparacion_pura(array $columnas_extra, array|string|null $data, string $key):array|stdClass{

        if($key === ''){
            return $this->error->error(mensaje: "Error key vacio", data: $key, es_final: true);
        }
        if(is_array($data) && count($data) === 0){
            return $this->error->error(mensaje:"Error datos vacio",data: $data, es_final: true);
        }
        $datas = new stdClass();
        $datas->campo = $this->campo(data: $data,key:  $key);
        if(errores::$error){
            return $this->error->error(mensaje:"Error al maquetar campo",data: $datas->campo);
        }
        $datas->value = $this->value(data: $data);
        if(errores::$error){
            return $this->error->error(mensaje:"Error al validar maquetacion",data: $datas->value);
        }
        $es_sq = false;
        if(isset($columnas_extra[$key])){
            $es_sq = true;
        }
        if($es_sq){
            $datas->campo = $columnas_extra[$key];
        }

        return $datas;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Función privada que genera una condición BETWEEN para una consulta SQL.
     *
     * @param string $campo El nombre del campo en el que se aplicará la condición.
     * @param array $filtro Un array asociativo que debe contener los elementos 'valor1' y 'valor2'
     * los cuales delimitarán el rango de la condición BETWEEN.
     * @param bool $valor_campo Indica si el valor de $campo debe ser tratado como un string
     *        (si $valor_campo es true, se añaden comillas simples alrededor del nombre del campo).
     *
     * @return string|array Retorna la condición BETWEEN como un string si todo está correcto.
     *        En caso contrario, si $campo está vacío o $filtro no contiene los elementos 'valor1' y 'valor2',
     * retorna un error.
     * @version 16.232.0
     */
    private function condicion_entre(string $campo, array $filtro, bool $valor_campo): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo vacío', data: $campo, es_final: true);
        }
        if(!isset($filtro['valor1'])){
            return $this->error->error(mensaje: 'Error campo vacío $filtro[valor1]', data: $campo, es_final: true);
        }
        if(!isset($filtro['valor2'])){
            return $this->error->error(mensaje: 'Error campo vacío $filtro[valor2]', data: $campo, es_final: true);
        }
        $condicion = $campo . ' BETWEEN ' ."'" .$filtro['valor1'] . "'"." AND "."'".$filtro['valor2'] . "'";

        if($valor_campo){
            $condicion = "'".$campo."'" . ' BETWEEN '  .$filtro['valor1'] ." AND ".$filtro['valor2'];
        }

        return $condicion;

    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Manipula y valida los datos de filtro para las fechas.
     *
     * Esta función acepta un arreglo asociativo con tres claves: 'campo_1', 'campo_2' y 'fecha'.
     * Primero, realiza una validación de los elementos del arreglo.
     * Si la validación falla, se notifica el error y se detiene la ejecución de la función.
     * Si la validación es correcta, cada valor se asigna a un nuevo objeto $data como una propiedad separada.
     * Finalmente, se devuelve el objeto $data.
     *
     * @param array $fil_fecha El arreglo que contiene los campos de fecha para filtrar.
     *        Debe tener las claves 'campo_1', 'campo_2' y 'fecha'.
     * @return stdClass|array Retorna un objeto con los campos validados si todo es correcto.
     *         Si hay un error, se devuelve un arreglo con información sobre el error.
     * @throws errores Si ocurre un error durante la validación, se lanza una excepción.
     *
     * @version 16.307.1
     */
    private function data_filtro_fecha(array $fil_fecha): stdClass|array
    {

        $valida = $this->valida_data_filtro_fecha(fil_fecha: $fil_fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha',data: $valida);
        }

        $campo_1 = $fil_fecha['campo_1'];
        $campo_2 = $fil_fecha['campo_2'];
        $fecha = $fil_fecha['fecha'];
        $data = new stdClass();
        $data->campo_1 = $campo_1;
        $data->campo_2 = $campo_2;
        $data->fecha = $fecha;
        return $data;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función se utiliza para procesar los datos entrantes ($in) y los organiza en un formato específico.
     *
     * @param array $in Datos entrantes que se deben procesar.
     *                  Debe contener las claves 'llave' y 'values'.
     * @return array|stdClass Devuelve un objeto que contiene los datos procesados.
     *                        Si hay un error durante la validación, devuelve un array con detalles del error.
     *
     * @throws errores Si los datos entrantes no contienen las claves requeridas,
     *               o si 'values' no es un array. En caso de error, se devuelve un array con detalles del error.
     *
     * @version 16.259.1
     */
    final public function data_in(array $in): array|stdClass
    {
        $keys = array('llave','values');
        $valida = $this->validacion->valida_existencia_keys( keys:$keys, registro: $in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar not_in',data: $valida);
        }

        $values = $in['values'];

        if(!is_array($values)){
            return $this->error->error(mensaje: 'Error values debe ser un array',data: $values, es_final: true);
        }
        $data = new stdClass();
        $data->llave = $in['llave'];
        $data->values = $in['values'];
        return $data;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Genera una consulta SQL a partir de los parámetros proporcionados.
     *
     * @param string $campo Campo de la consulta SQL.
     * @param string $campo_filtro Campo para el filtrado de la consulta.
     * @param array $filtro Filtro a aplicar en la consulta.
     *
     * @return string|array Retorna el resultado de la consulta SQL o un error si algo va mal.
     *
     * @throws errores Error al validar datos o generar la consulta SQL.
     * @version 16.163.0
     */
    private function data_sql(string $campo, string $campo_filtro, array $filtro): array|string
    {
        $valida = $this->valida_campo_filtro(campo: $campo,campo_filtro:  $campo_filtro,filtro:  $filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar datos',  data:$valida);
        }

        $data_sql = $this->data_sql_base(campo: $campo,campo_filtro:  $campo_filtro,filtro:  $filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al genera sql',  data:$data_sql);
        }

        if(isset($filtro[$campo_filtro]['valor_es_campo']) && $filtro[$campo_filtro]['valor_es_campo']){
            $data_sql = $this->data_sql_campo(campo: $campo,campo_filtro:  $campo_filtro,filtro:  $filtro);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al genera sql',  data:$data_sql);
            }
        }
        return $data_sql;

    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Método para generar una cadena SQL para un filtro base.
     *
     * @param string $campo Nombre del campo en la base de datos.
     * @param string $campo_filtro Nombre del campo del filtro.
     * @param array $filtro El filtro a aplicar en la sentencia SQL.
     * @return string|array Retorna una cadena con la sentencia SQL en caso de que se haya generado correctamente,
     *                      en caso contrario retorna un array con los detalles del error.
     *
     * @throws errores Lanza una excepción en caso de errores.
     * @version 16.152.0
     */
    private function data_sql_base(string $campo, string $campo_filtro, array $filtro): string|array
    {
        $valida = $this->valida_campo_filtro(campo: $campo,campo_filtro:  $campo_filtro,filtro:  $filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar datos',  data:$valida);
        }

        return " ".$campo." " . $filtro[$campo_filtro]['operador'] . " '" . $filtro[$campo_filtro]['valor'] . "' ";
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Valida el campo del filtro y retorna un string para la consulta SQL o un mensaje de error.
     *
     * @param string $campo El campo a validar.
     * @param string $campo_filtro El campo del filtro a utilizar.
     * @param array $filtro El array del filtro a aplicar.
     *
     * @return string|array Retorna un string formateado para la consulta SQL o un mensaje de error.
     * @version 16.161.0
     */
    private function data_sql_campo(string $campo, string $campo_filtro, array $filtro): string|array
    {

        $valida = $this->valida_campo_filtro(campo: $campo,campo_filtro:  $campo_filtro,filtro:  $filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar datos',  data:$valida);
        }

        return "'".$campo."'".$filtro[$campo_filtro]['operador'].$filtro[$campo_filtro]['valor'];

    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función gestiona un array asociativo que implementa filtros especiales para la consulta SQL
     * que está siendo generada.
     *
     * @param array $data_filtro El array contiene múltiples campos para filtrar.
     *
     * @return stdClass|array Retorna un objeto con 5 propiedades: campo, operador, valor, comparacion,
     *                         y condicion si la operación fue exitosa. En caso de error, devuelve un objeto Error.
     *
     * @throws errores si el array $data_filtro está vacío.
     * @throws errores si el campo `operador` no existe en cada campo del array $data_filtro.
     * @throws errores si el campo `valor` no existe en cada campo del array $data_filtro.
     * @throws errores si el campo `comparacion` no existe en cada campo del array $data_filtro.
     *
     * @example
     * $where = new Where();
     * $filtrado = $where->datos_filtro_especial([
     *     'age' => [
     *         'operador' => '>',
     *         'valor' => '21',
     *         'comparacion' => 'AND',
     *     ],
     * ]);
     * // Resultado:
     * // stdClass Object
     * // (
     * //    [campo] => age
     * //    [operador] => >
     * //    [valor] => 21
     * //    [comparacion] => AND
     * //    [condicion] => age>'21'
     * // )
     * @version 16.248.1
     */
    private function datos_filtro_especial(array $data_filtro):array|stdClass
    {
        if(count($data_filtro) === 0){
            return $this->error->error(mensaje:'Error data_filtro esta vacio',  data:$data_filtro, es_final: true);
        }
        $campo = $this->campo_data_filtro(data_filtro: $data_filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener campo',data:  $campo);
        }

        if(!isset($data_filtro[$campo]['operador'])){
            return $this->error->error(mensaje:'Error data_filtro['.$campo.'][operador] debe existir',
                data:$data_filtro, es_final: true);
        }

        $operador = $data_filtro[$campo]['operador'];
        if($operador===''){
            return $this->error->error(mensaje:'Error el operador debe de existir',data:$operador, es_final: true);
        }

        if(!isset($data_filtro[$campo]['valor'])){
            return $this->error->error(mensaje:'Error data_filtro['.$campo.'][valor] debe existir',
                data:$data_filtro, es_final: true);
        }
        if(!isset($data_filtro[$campo]['comparacion'])){
            return $this->error->error(mensaje:'Error data_filtro['.$campo.'][comparacion] debe existir',
                data:$data_filtro, es_final: true);
        }

        $valor = $data_filtro[$campo]['valor'];
        if($valor===''){
            return $this->error->error(mensaje:'Error el operador debe de existir',data:$valor, es_final: true);
        }
        $valor = addslashes($valor);
        $comparacion = $data_filtro[$campo]['comparacion'];
        $condicion = $campo.$operador."'$valor'";

        $datos = new stdClass();
        $datos->campo = $campo;
        $datos->operador = $operador;
        $datos->valor = $valor;
        $datos->comparacion = $comparacion;
        $datos->condicion = $condicion;

        return $datos;

    }

    /**
     * Determina si un campo es un subquery basado en la existencia del campo en las columnas extra.
     *
     * @param string $campo El campo a evaluar si es un subquery.
     * @param array $columnas_extra Las columnas extra donde se va a buscar el campo.
     * @return bool|array Retorna verdadero si el campo es un subquery, en caso contrario retorna falso.
     *  En el caso de que el campo esté vacío, se retorna un error.
     */
    private function es_subquery(string $campo, array $columnas_extra): bool|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje:'Error campo esta vacio',  data:$campo, es_final: true);
        }
        $es_subquery = false;
        if(isset($columnas_extra[$campo])){
            $es_subquery = true;
        }
        return $es_subquery;

    }

    /**
     *
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Genera las condiciones sql de un filtro especial
     * @param array $columnas_extra Conjunto de columnas en forma de subquery
     * @param array $filtro_especial //arreglo con las condiciones $filtro_especial[0][tabla.campo]= array('operador'=>'<','valor'=>'x')
     *
     * @return array|string
     * @example
     *      Ej 1
     *      $filtro_especial[0][tabla.campo]['operador'] = '>';
     *      $filtro_especial[0][tabla.campo]['valor'] = 'x';
     *
     *      $resultado = filtro_especial_sql($filtro_especial);
     *      $resultado =  tabla.campo > 'x'
     *
     *      Ej 2
     *      $filtro_especial[0][tabla.campo]['operador'] = '<';
     *      $filtro_especial[0][tabla.campo]['valor'] = 'x';
     *
     *      $resultado = filtro_especial_sql($filtro_especial);
     *      $resultado =  tabla.campo < 'x'
     *
     *      Ej 3
     *      $filtro_especial[0][tabla.campo]['operador'] = '<';
     *      $filtro_especial[0][tabla.campo]['valor'] = 'x';
     *
     *      $filtro_especial[1][tabla.campo2]['operador'] = '>=';
     *      $filtro_especial[1][tabla.campo2]['valor'] = 'x';
     *      $filtro_especial[1][tabla.campo2]['comparacion'] = 'OR ';
     *
     *      $resultado = filtro_especial_sql($filtro_especial);
     *      $resultado =  tabla.campo < 'x' OR tabla.campo2  >= x
     *
     * @version 16.204.0
     */
    final public function filtro_especial_sql(array $columnas_extra, array $filtro_especial):array|string{ //DEBUG

        $filtro_especial_sql = '';
        foreach ($filtro_especial as $campo=>$filtro_esp){
            if(!is_array($filtro_esp)){

                return $this->error->error(mensaje: "Error filtro debe ser un array filtro_especial[] = array()",
                    data: $filtro_esp, es_final: true);
            }

            $filtro_especial_sql = $this->obten_filtro_especial(columnas_extra: $columnas_extra,
                filtro_esp: $filtro_esp, filtro_especial_sql: $filtro_especial_sql);
            if(errores::$error){
                return $this->error->error(mensaje:"Error filtro", data: $filtro_especial_sql);
            }
        }
        return $filtro_especial_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Funcion que genera las condiciones de sql de un filtro extra
     *
     * @param array $filtro_extra arreglo que contiene las condiciones
     * $filtro_extra[0]['tabla.campo']=array('operador'=>'>','valor'=>'x','comparacion'=>'AND');
     * @example
     *      $filtro_extra[0][tabla.campo]['operador'] = '<';
     *      $filtro_extra[0][tabla.campo]['valor'] = 'x';
     *
     *      $filtro_extra[0][tabla2.campo]['operador'] = '>';
     *      $filtro_extra[0][tabla2.campo]['valor'] = 'x';
     *      $filtro_extra[0][tabla2.campo]['comparacion'] = 'OR';
     *
     *      $resultado = filtro_extra_sql($filtro_extra);
     *      $resultado =  tabla.campo < 'x' OR tabla2.campo > 'x'
     *
     * @return array|string
     * @uses filtro_and()
     * @version 16.258.1
     *
     */
    final public function filtro_extra_sql(array $filtro_extra):array|string{
        $filtro_extra_sql = '';
        foreach($filtro_extra as $data_filtro){
            if(!is_array($data_filtro)){
                return $this->error->error(mensaje: 'Error $data_filtro debe ser un array',data: $filtro_extra,
                    es_final: true);
            }
            $filtro_extra_sql = $this->integra_filtro_extra(
                data_filtro: $data_filtro, filtro_extra_sql: $filtro_extra_sql);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar filtro',data:  $filtro_extra_sql);
            }
        }

        return $filtro_extra_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Función filtro_extra_sql_genera
     *
     * @param string $comparacion La cadena de texto utilizada para comparar
     * @param string $condicion La cadena de texto que representa la condición
     * @param string $filtro_extra_sql Una expresión SQL adicional que se añadirá al filtro
     * @return string $filtro_extra_sql Retorna la cadena de texto SQL actualizada
     *
     * Esta función genera un filtro SQL adicional a partir de las condiciones y la cadena de comparación proporcionadas.
     * Si el filtro SQL adicional ya está establecido, la función añadirá la condición a este utilizando la cadena de comparación.
     * Sin embargo, si el filtro SQL adicional no está establecido, la función simplemente añadirá la condición a este.
     * Finalmente, la función devuelve el filtro SQL adicional actualizado.
     * @version 16.252.1
     */
    private function filtro_extra_sql_genera(string $comparacion, string $condicion, string $filtro_extra_sql): string
    {
        if($filtro_extra_sql === ''){
            $filtro_extra_sql .= $condicion;
        }
        else {
            $filtro_extra_sql .=  $comparacion . $condicion;
        }
        return $filtro_extra_sql;

    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Este método procesa la fecha enviada y retorna una consulta SQL representando el filtro de la fecha.
     *
     * @param array $filtro_fecha Representa la fecha que se va a filtrar.
     *
     * @return array|string Retorna una consulta SQL del filtro de fecha si es exitoso. Si ocurre un error,
     *  retorna una cadena con mensaje de error.
     *
     * @throws errores si no se pudo generar la consulta SQL del filtro de fecha.
     *
     * @version 16.313.1
     */
    final public function filtro_fecha(array $filtro_fecha):array|string{


        $filtro_fecha_sql = $this->filtro_fecha_base(filtro_fecha: $filtro_fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener sql',data: $filtro_fecha_sql);
        }

        if($filtro_fecha_sql !==''){
            $filtro_fecha_sql = "($filtro_fecha_sql)";
        }

        return $filtro_fecha_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función se encarga de crear una cadena SQL para filtrar por fecha.
     *
     * @param array $filtro_fecha Un array que contiene los criterios de filtro de fecha.
     *
     * @return array|string Retorna una cadena SQL si todo fue exitoso, o un array de errores si hubo algún problema.
     *
     * @throws errores Si $fil_fecha no es un array.
     *
     * La función itera sobre cada $fil_fecha en $filtro_fecha.
     * Para cada $fil_fecha, valida el filtrado de la fecha utilizando $this->valida_filtro_fecha(fil_fecha: $fil_fecha).
     * Si hay algun error de validación, retorna un error con datos relacionados al error.
     *
     * Luego, genera la cadena SQL utilizando $this->genera_sql_filtro_fecha(fil_fecha: $fil_fecha, filtro_fecha_sql: $filtro_fecha_sql)
     * Si hay algun error al generar la cadena SQL, retorna un error con datos relacionados al error.
     *
     * Finalmente, agrega la cadena SQL al $filtro_fecha_sql y al final del ciclo retorna $filtro_fecha_sql
     *
     * @version 16.312.1
     */
    private function filtro_fecha_base(array $filtro_fecha): array|string
    {
        $filtro_fecha_sql = '';
        foreach ($filtro_fecha as $fil_fecha){
            if(!is_array($fil_fecha)){
                return $this->error->error(mensaje: 'Error $fil_fecha debe ser un array',data: $fil_fecha,
                    es_final: true);
            }

            $valida = $this->valida_filtro_fecha(fil_fecha: $fil_fecha);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
            }

            $sql = $this->genera_sql_filtro_fecha(fil_fecha: $fil_fecha, filtro_fecha_sql: $filtro_fecha_sql);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener sql',data: $sql);
            }

            $filtro_fecha_sql.= $sql;

        }
        return $filtro_fecha_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     *
     * Devuelve un conjunto de condiciones de tipo BETWEEN en forma de sql
     *
     * @param array $filtro_rango
     *                  Opcion1.- Debe ser un array con la siguiente forma array('valor1'=>'valor','valor2'=>'valor')
     *                  Opcion2.-
     *                      Debe ser un array con la siguiente forma
     *                          array('valor1'=>'valor','valor2'=>'valor','valor_campo'=>true)
     * @example
     *      $entrada = array();
     *      $resultado = filtro_rango_sql($entrada)
     *      //return = string ''
     *      $entrada['x'] = array('''valor1'=>'1','valor2=>2);
     *      $resultado = filtro_rango_sql($entrada)
     *      //return string x = BETWEEN '1' AND '2'
     *      $entrada['x'] = array('''valor1'=>'1','valor2=>2,'valor_campo'=>true);
     *      $resultado = filtro_rango_sql($entrada)
     *      //return string 'x' = BETWEEN 1 AND 2
     *      $entrada['x'] = array('''valor1'=>'1','valor2=>2,'valor_campo'=>true);
     *      $entrada['y'] = array('''valor1'=>'2','valor2=>3,'valor_campo'=>false);
     *      $entrada['z'] = array('''valor1'=>'4','valor2=>5);
     *      $resultado = filtro_rango_sql($entrada)
     *      //return string 'x' = BETWEEN 1 AND 2 AND y BETWEEN 2 AND 3 AND z BETWEEN 4 AND 5
     * @return array|string
     * @throws errores Si $filtro_rango[0] != array
     * @throws errores Si filtro[0] = array('valor1'=>'1') Debe existir valor2
     * @throws errores Si filtro[0] = array('valor2'=>'1') Debe existir valor1
     * @throws errores Si filtro[0] = array('valor1'=>'1','valor2'=>'2') key debe ser tabla.campo error sql
     * @version 16.236.0
     */
    final public function filtro_rango_sql(array $filtro_rango):array|string{
        $filtro_rango_sql = '';
        foreach ($filtro_rango as $campo=>$filtro){
            if(!is_array($filtro)){
                return  $this->error->error(mensaje: 'Error $filtro debe ser un array',data: $filtro, es_final: true);
            }
            if(!isset($filtro['valor1'])){
                return  $this->error->error(mensaje:'Error $filtro[valor1] debe existir',data:$filtro, es_final: true);
            }
            if(!isset($filtro['valor2'])){
                return  $this->error->error(mensaje:'Error $filtro[valor2] debe existir',data:$filtro, es_final: true);
            }
            $campo = trim($campo);
            if(is_numeric($campo)){
                return  $this->error->error(mensaje:'Error campo debe ser un string',data:$campo, es_final: true);
            }
            $valor_campo = false;

            if(isset($filtro['valor_campo']) && $filtro['valor_campo']){
                $valor_campo = true;
            }
            $filtro_rango_sql = $this->genera_filtro_rango_base(campo: $campo,filtro: $filtro,
                filtro_rango_sql: $filtro_rango_sql,valor_campo: $valor_campo);
            if(errores::$error){
                return  $this->error->error(mensaje:'Error $filtro_rango_sql al generar',data:$filtro_rango_sql);
            }
        }

        return $filtro_rango_sql;
    }

    /**
     * POR DOCUMENTAR WIKI FINAL REV
     * Esta función genera una cadena de declaración SQL AND basada en los filtros y columnas extras proporcionados.
     *
     * @param array $columnas_extra Las columnas adicionales que han de considerarse al generar la declaración SQL.
     * @param array $filtro Los filtros que se utilizarán para la generación de la declaración SQL.
     *
     * @return string|array Retornará una cadena que es la declaración SQL AND generada. Si ocurre algún error al
     * procesar, retornará un objeto de error.
     *
     * @throws errores si hay algún problema con los filtros o columnas proporcionados.
     * @version 16.100.0
     */
    final public function genera_and(array $columnas_extra, array $filtro):array|string{
        $sentencia = '';
        foreach ($filtro as $key => $data) {
            if(is_numeric($key)){
                return $this->error->error(
                    mensaje: 'Los key deben de ser campos asociativos con referencia a tabla.campo',data: $filtro,
                    es_final: true);
            }
            $data_comparacion = $this->comparacion_pura(columnas_extra: $columnas_extra, data: $data, key: $key);
            if(errores::$error){
                return $this->error->error(mensaje:"Error al maquetar campo",data:$data_comparacion);
            }

            $comparacion = $this->comparacion(data: $data,default: '=');
            if(errores::$error){
                return $this->error->error(mensaje:"Error al maquetar",data:$comparacion);
            }

            $operador = $data['operador'] ?? ' AND ';
            if(trim($operador) !=='AND' && trim($operador) !=='OR'){
                return $this->error->error(mensaje:'El operador debe ser AND u OR',data:$operador, es_final: true);
            }

            $data_sql = "$data_comparacion->campo $comparacion '$data_comparacion->value'";

            $sentencia .= $sentencia === ''? $data_sql :" $operador $data_sql";
        }

        return $sentencia;

    }



    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Genera y gestiona sentencias AND para operaciones SQL.
     * La función procesa el filtro y las columnas adicionales proporcionadas para generar una sentencia SQL AND.
     *
     * @param array $columnas_extra Columnas adicionales para usar en la generación de sentencias.
     * @param array $filtro Los filtros que se aplicarán a la sentencia SQL.
     * @return array|string Devuelve una sentencia SQL estructurada como un string.
     *
     * @throws errores Si los filtros proporcionados tienen claves numéricas.
     * Las claves deben hacer referencia a campo de una tabla en formato "tabla.campo".
     *
     * @throws errores Si se produce un error durante la construcción de la sentencia SQL.
     *
     * @example
     * genera_and_textos(['columna1', 'columna2'], ['tabla.campo' => 'valor']);
     * Esto generará una sentencia SQL AND que puede parecerse a "tabla.campo LIKE '%valor%'".
     * Nota: El operador predeterminado es 'LIKE'.
     * @version 16.101.0
     */
    private function genera_and_textos(array $columnas_extra, array $filtro):array|string{

        $sentencia = '';
        foreach ($filtro as $key => $data) {
            if(is_numeric($key)){
                return $this->error->error(
                    mensaje: 'Los key deben de ser campos asociativos con referencia a tabla.campo',data: $filtro,
                    es_final: true);
            }

            $data_comparacion = $this->comparacion_pura(columnas_extra: $columnas_extra, data: $data,key:  $key);
            if(errores::$error){
                return $this->error->error(mensaje: "Error al maquetar",data:$data_comparacion);
            }

            $comparacion = $this->comparacion(data: $data,default: 'LIKE');
            if(errores::$error){
                return $this->error->error(mensaje:"Error al maquetar",data:$comparacion);
            }

            $txt = '%';
            $operador = 'AND';
            if(isset($data['operador']) && $data['operador']!==''){
                $operador = $data['operador'];
                $txt= '';
            }

            $sentencia .= $sentencia === ""?"$data_comparacion->campo $comparacion '$txt$data_comparacion->value$txt'":
                " $operador $data_comparacion->campo $comparacion '$txt$data_comparacion->value$txt'";
        }


        return $sentencia;

    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Genera la condicion sql de un filtro especial
     *
     *
     * @param string $filtro_especial_sql //condicion en forma de sql
     * @param string $data_sql //condicion en forma de sql
     * @param array $filtro_esp //array con datos del filtro array('tabla.campo','AND')
     * @param string  $campo //string con el nombre del campo
     *
     * @example
     *      Ej 1
     *      $filtro_especial_sql = '';
     *      $data_sql = '';
     *      $filtro_esp = array();
     *      $campo = '';
     *      $resultado = genera_filtro_especial($filtro_especial_sql, $data_sql,$filtro_esp,$campo);
     *      $resultado = string vacio
     *
     *
     *      Ej 2
     *      $filtro_especial_sql = 'tabla.campo = 1';
     *      $data_sql = 'tabla.campo2 = 1';
     *      $filtro_esp['tabla.campo2']['comparacion'] = 'OR'
     *      $campo = 'tabla.campo2';
     *      $resultado = genera_filtro_especial($filtro_especial_sql, $data_sql,$filtro_esp,$campo);
     *      $resultado = tabla.campo = 1 OR tabla.campo2 = 1
     *
     *      Ej 3
     *      $filtro_especial_sql = 'tabla.campo = 1';
     *      $data_sql = 'tabla.campo2 = 1';
     *      $filtro_esp['tabla.campo2']['comparacion'] = 'AND'
     *      $campo = 'tabla.campo2';
     *      $resultado = genera_filtro_especial($filtro_especial_sql, $data_sql,$filtro_esp,$campo);
     *      $resultado = tabla.campo = 1 AND tabla.campo2 = 1
     *
     *
     * @return array|string
     * @throws errores $filtro_especial_sql != '' $filtro_esp[$campo]['comparacion'] no existe,
     *  Debe existir $filtro_esp[$campo]['comparacion']
     * @throws errores $filtro_especial_sql != '' = $data_sql = '',  data_sql debe tener info
     * @version 16.182.0
     */

    private function genera_filtro_especial(string $campo, string $data_sql, array $filtro_esp,
                                            string $filtro_especial_sql):array|string{//FIN //DEBUG
        if($filtro_especial_sql === ''){
            $filtro_especial_sql .= $data_sql;
        }
        else{
            if(!isset($filtro_esp[$campo]['comparacion'])){
                return $this->error->error(mensaje: 'Error $filtro_esp[$campo][\'comparacion\'] debe existir',
                    data: $filtro_esp, es_final: true);
            }
            if(trim($data_sql) === ''){
                return $this->error->error(mensaje:'Error $data_sql no puede venir vacio', data:$data_sql,
                    es_final: true);
            }

            $filtro_especial_sql .= ' '.$filtro_esp[$campo]['comparacion'].' '.$data_sql;
        }

        return $filtro_especial_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Devuelve una condicion en forma de sql validando si se tiene que precragar un AND o solo la sentencia
     * @param string $campo
     *                  Opcion 1.-Si valor_es_campo = false,
     *                      El valor definido debe ser un campo de la base de datos con la siguiente forma tabla.campo
     *                  Opcion 2.-Si valor_es_campo = true,
     *                      El valor definido debe ser un valor del registro del rango a buscar
     *
     * @param array $filtro Debe ser un array con la siguiente forma array('valor1'=>'valor','valor2'=>'valor')
     * @param string $filtro_rango_sql debe ser un sql con una condicion
     * @param bool $valor_campo
     *                  Opcion1.- true, Es utilizado para definir el campo para una comparacion como valor
     *                  Opcion2.- false, Es utilizado para definir el campo a comparar el rango de valores
     * @example
     *      $resultado = genera_filtro_rango_base('',array(),'');
     *      //return = array errores
     *      $resultado = genera_filtro_rango_base('x',array(),'');
     *      //return = array errores
     *      $resultado = genera_filtro_rango_base('x',array('valor1'=>x,'valor2'=>'y'),'');
     *      //return = string 'x BETWEEN 'x' AND 'y' ;
     *      $resultado = genera_filtro_rango_base('x',array('valor1'=>x,'valor2'=>'y'),'tabla.campo = 1');
     *      //return = string tabla.campo = 1 AND  x BETWEEN 'x' AND 'y' ;
     *      $resultado = genera_filtro_rango_base('x',array('valor1'=>x,'valor2'=>'y'),'tabla.campo = 1',true);
     *      //return = string tabla.campo = 1 AND  'x' BETWEEN x AND y ;
     * @return array|string
     * @throws errores Si $campo = vacio
     * @throws errores Si filtro[valor1] = vacio
     * @throws errores Si filtro[valor2] = vacio
     * @version 16.233.0
     */
    private function genera_filtro_rango_base(string $campo, array $filtro, string $filtro_rango_sql,
                                              bool $valor_campo = false):array|string{
        $campo = trim($campo);
        if($campo === ''){
            return  $this->error->error(mensaje: 'Error $campo no puede venir vacio',data: $campo, es_final: true);
        }
        $keys = array('valor1','valor2');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $filtro);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }

        $condicion = $this->condicion_entre(campo: $campo,filtro:  $filtro,valor_campo:  $valor_campo);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al generar condicion',data: $condicion);
        }

        $filtro_rango_sql_r = $this->setea_filtro_rango(condicion: $condicion, filtro_rango_sql: $filtro_rango_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error $filtro_rango_sql al setear',data: $filtro_rango_sql_r);
        }

        return $filtro_rango_sql_r;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV GENERAR EN PRIVATE
     * Genera una cadena SQL para la cláusula IN en una consulta SQL.
     *
     * Esta función toma un array asociativo $in que debe tener las claves:
     * - 'llave': representa el nombre de columna en la cláusula SQL IN
     * - 'values': un array de valores para la cláusula SQL IN
     *
     * Luego realiza las siguientes operaciones:
     * 1. Valida la existencia de las claves 'llave' y 'values' en el array proporcionado. Si algún de los claves no existe, retorna un error.
     * 2. Genera los datos `$data_in` basados en el array dado. Si ocurre un error mientras se genera `$data_in`, retorna un error.
     * 3. Genera la cadena SQL para la cláusula IN basado en `$data_in`. Si ocurre un error mientras se genera la cláusula SQL IN, retorna un error.
     * 4. Si todos los pasos anteriores se completan con éxito, retorna la cadena SQL para la cláusula IN.
     *
     * @param array $in  'llave': string, 'values': array } Array asociativo con las claves 'llave' y 'values'
     * @return string|array Retorna una cadena SQL para la cláusula IN si no hubo errores. En caso de error, devuelve un array que describe el error.
     *
     * @example genera_in(['llave' => 'id', 'values' => [1, 2, 3]]) retorna 'id IN (1,2,3)'
     *
     * @version 16.293.1
     */
    final public function genera_in(array $in): array|string
    {
        $keys = array('llave','values');
        $valida = $this->validacion->valida_existencia_keys( keys:$keys, registro: $in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar not_in',data: $valida);
        }
        $values = $in['values'];

        if(!is_array($values)){
            return $this->error->error(mensaje: 'Error values debe ser un array',data: $values, es_final: true);
        }

        $data_in = $this->data_in(in: $in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar data in',data: $data_in);
        }


        $in_sql = $this->in_sql(llave:  $data_in->llave, values:$data_in->values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $in_sql);
        }
        return $in_sql;
    }




    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Genera la sentencia SQL base de filtro según el tipo de filtro
     * proporcionado (numeros o textos).
     *
     * @param array $columnas_extra Array de columnas adicionales para incluir en la consulta.
     * @param array $filtro Array de condiciones para ser incluidos en la cláusula WHERE de la sentencia.
     * @param string $tipo_filtro Define el tipo del filtro. Puede ser "numeros" o "textos".
     *
     * @return array|string Retorna la sentencia generada o un string describiendo un error si sucede alguno.
     *
     * @throws errores si el tipo de filtro no es válido.
     * @version 16.102.0
     */
    final public function genera_sentencia_base(array $columnas_extra,  array $filtro, string $tipo_filtro):array|string{
        $verifica_tf = $this->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf);
        }
        $sentencia = '';
        if($tipo_filtro === 'numeros') {
            $sentencia = $this->genera_and(columnas_extra: $columnas_extra, filtro: $filtro);
            if(errores::$error){
                return $this->error->error(mensaje: "Error en and",data:$sentencia);
            }
        }
        elseif ($tipo_filtro==='textos'){
            $sentencia = $this->genera_and_textos(columnas_extra: $columnas_extra,filtro: $filtro);
            if(errores::$error){
                return $this->error->error(mensaje: "Error en texto",data:$sentencia);
            }
        }
        return $sentencia;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * La función in_sql genera y valida una instrucción SQL IN.
     *
     * @param string $llave El nombre del campo que se utilizará en la instrucción IN.
     * @param array $values Un array con los valores que se usarán en la instrucción IN.
     *
     * @return array|string Regresa una instrucción SQL IN si todo sale bien.
     * Regresa un mensaje de error si se detecta algún problema durante la generación o validación del SQL.
     *
     * La función sigue los siguientes pasos:
     * - Primero, verifica que la $llave no sea una cadena vacía.
     * - Luego, intenta generar una cadena con los valores para la instrucción IN.
     * - Después valida la instrucción `IN` generada.
     * - Finalmente, intenta generar una instrucción SQL `IN` completa y la retorna.
     *
     * Notas:
     * - Si se encuentra algún error durante el proceso, la función retorna inmediatamente un mensaje de error.
     * - Cada paso de generación y validación puede disparar un error, así que se comprueba después de cada paso.
     *
     * @version 16.291.1
     */
    private function in_sql(string $llave, array $values): array|string
    {
        $llave = trim($llave);
        if($llave === ''){
            return $this->error->error(mensaje: 'Error la llave esta vacia',data: $llave, es_final: true);
        }

        $values_sql = $this->values_sql_in(values:$values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $values_sql);
        }
        $valida = (new sql)->valida_in(llave: $llave, values_sql: $values_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar in', data: $valida);
        }

        $in_sql = (new sql())->in(llave: $llave,values_sql:  $values_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $in_sql);
        }

        return $in_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función toma un filtro adicional y lo integra a la consulta SQL actual.
     * Recibe una matriz de datos del filtro y una cadena que representa el filtro SQL extra.
     *
     * @param array $data_filtro La matriz de datos del filtro. La función devuelve un error si la matriz está vacía.
     * @param string $filtro_extra_sql La cadena que representa el filtro extra para la consulta SQL.
     *
     * Si se produce algún error durante el proceso, la función retornará detalles sobre el error.
     *
     * @return object|string|array Retorna el filtro SQL extra integrado en caso de éxito. Si ocurre un error,
     *  retorna un objeto de error.
     * @version 16.257.1
     */
    private function integra_filtro_extra(array $data_filtro, string $filtro_extra_sql): object|string|array
    {
        if(count($data_filtro) === 0){
            return $this->error->error(mensaje:'Error data_filtro esta vacio',  data:$data_filtro, es_final: true);
        }

        $datos = $this->datos_filtro_especial(data_filtro: $data_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener datos de filtro',data:  $datos);
        }

        $filtro_extra_sql = $this->filtro_extra_sql_genera(comparacion: $datos->comparacion,
            condicion:  $datos->condicion,filtro_extra_sql:  $filtro_extra_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtro',data:  $filtro_extra_sql);
        }

        return $filtro_extra_sql;

    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Devuelve una condicion en forma de sql validando si se tiene que precragar un AND o solo la sentencia
     * @access public
     * @param string $filtro_rango_sql debe ser un sql con una condicion
     * @param string $condicion debe ser un sql con una condicion
     * @example
     *      $filtro = setea_filtro_rango('','');
     *      //return = string ''
     *      $filtro = setea_filtro_rango('var1 = 1','');
     *      //return = array errores
     *      $filtro = setea_filtro_rango('var1 = 1','var2 = 2');
     *      //return = string 'var1 = 1 AND var2 = 2'
     *      $filtro = setea_filtro_rango('','var2 = 2');
     *      //return = string 'var2 = 2'
     * @return array|string
     * @throws errores Si $filtro_rango_sql es diferente de vacio y condicion es igual a vacio
     * @version 16.226.0
     */
    private function setea_filtro_rango(string $condicion, string $filtro_rango_sql):array|string{
        $filtro_rango_sql = trim($filtro_rango_sql);
        $condicion = trim($condicion);

        if(trim($filtro_rango_sql) !=='' && trim($condicion) === ''){

            return  $this->error->error(mensaje: 'Error if filtro_rango tiene info $condicion no puede venir vacio',
                data: $filtro_rango_sql, es_final: true);
        }

        $and = $this->and_filtro_fecha(txt: $filtro_rango_sql);
        if(errores::$error){
            return $this->error->error(mensaje:'error al integrar and ',data: $and);
        }


        $filtro_rango_sql.= $and.$condicion;

        return $filtro_rango_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * El método sql_fecha genera un fragmento de consulta SQL basado en un rango de fechas.
     *
     * Este método toma dos parámetros: uno para la conjunción de SQL (AND / OR etc.) y otro
     * para un objeto de datos que contiene las fechas. Las fechas ingresadas se validan y luego
     * se utilizan para construir una consulta SQL que puede usarse para filtrar registros entre dos fechas.
     *
     * @param string $and La conjunción de SQL (AND, OR, etc.).
     * @param stdClass $data Objeto de datos que debe contener `fecha`, `campo_1`, y `campo_2`.
     *                       `fecha` es la columna de la fecha, `campo_1` es la fecha de inicio
     *                       y `campo_2` es la fecha de fin para el filtro de la consulta SQL.
     *
     * @throws errores En caso de que el objeto de datos no contenga alguna clave requerida o
     *                   si algún valor está vacío o si la fecha proporcionada no es válida.
     *
     * @return string|array Consulta SQL generada como string. En caso de error, devuelve un array
     *                      con datos de error.
     *
     * @example sql_fecha('AND', (object)['fecha' => 'created_at', 'campo_1' => '2023-01-01', 'campo_2' => '2023-12-31']);
     *          Esto generaría: "(created_at >= '2023-01-01' AND created_at <= '2023-12-31')"
     *
     * @version 16.309.1
     */
    private function sql_fecha(string $and, stdClass $data): string|array
    {
        $keys = array('fecha','campo_1','campo_2');
        foreach($keys as $key){
            if(!isset($data->$key)){
                return $this->error->error(mensaje: 'error no existe $data->'.$key, data: $data, es_final: true);
            }
            if(trim($data->$key) === ''){
                return $this->error->error(mensaje:'error esta vacio $data->'.$key, data:$data, es_final: true);
            }
        }
        $keys = array('fecha');
        foreach($keys as $key){
            $valida = $this->validacion->valida_fecha(fecha: $data->$key);
            if(errores::$error){
                return $this->error->error(mensaje:'error al validar '.$key,data: $valida);
            }
        }

        return "$and('$data->fecha' >= $data->campo_1 AND '$data->fecha' <= $data->campo_2)";
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Método privado que genera una cláusula NOT IN SQL a partir de un arreglo proporcionado.
     *
     * @param array $not_in Arreglo de elementos a ser excluidos en la consulta SQL.
     *
     * @return array|string Regresa la cláusula NOT IN SQL generada o un mensaje de error en caso de un error
     * detectado en la generación de la cláusula.
     *
     * @throws errores Lanza una excepción de tipo Error en caso de error en la generación de la cláusula SQL.
     * @version 16.276.1
     */
    private function genera_not_in(array $not_in): array|string
    {
        $data_in = $this->data_in(in: $not_in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar data in',data: $data_in);
        }

        $not_in_sql = $this->not_in_sql(llave:  $data_in->llave, values:$data_in->values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $not_in_sql);
        }
        return $not_in_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Genera la cláusula SQL NOT IN basada en los valores proporcionados.
     *
     * Esta función toma una matriz asociativa como parámetro, donde `llave` es el nombre del campo y `values` es una
     * matriz de valores que se utilizarán en la cláusula NOT IN en una sentencia SQL. Luego, genera la cláusula SQL
     * NOT IN correspondiente.
     *
     * Si ocurre algún error durante la validación de los parámetros o la generación de la cláusula SQL NOT IN,
     * la función devolverá un mensaje de error.
     *
     * @param array $not_in Matriz asociativa con los claves 'llave' y 'values'.
     *        Ejemplo: ['llave' => 'miCampo', 'values' => [1, 2, 3]]
     *
     * @return string|array Devuelve la cláusula SQL NOT IN como una cadena si la función se ejecuta correctamente.
     *                      En caso de error, devuelve una matriz con los detalles del error.
     *
     * @version 16.278.1
     */
    final public function genera_not_in_sql(array $not_in): array|string
    {
        $not_in_sql = '';
        if(count($not_in)>0){
            $keys = array('llave','values');
            $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $not_in);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar not_in',data: $valida);
            }
            $not_in_sql = $this->genera_not_in(not_in: $not_in);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar sql',data: $not_in_sql);
            }

        }
        return $not_in_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * La función 'genera_sql_filtro_fecha' es privada y se encarga de generar un filtro de SQL para fechas.
     *
     * @param array $fil_fecha Es el filtro de fecha a validar y procesar.
     * @param string $filtro_fecha_sql Es un string que contiene la sentencia SQL para el filtro de fecha.
     * @return array|string Retorna un string con la sentencia SQL generada o un arreglo en caso de error.
     *
     * @throws errores Se puede lanzar una excepción en caso de que haya un error al validar la fecha, generar datos, obtener el 'and' o al obtener el sql.
     *
     * La función sigue estos pasos:
     * 1. Valida el filtro de fechas. Si hay un error, retorna un mensaje de error relatando un problema al validar la fecha.
     * 2. Genera datos a partir del filtro de fechas. Si hay un error, retorna un mensaje de error relatando un problema al generar datos.
     * 3. Obtiene el 'and' necesario para el filtro de fechas SQL. Si hay un error, retorna un mensaje de error relatando un problema al obtener el 'and'.
     * 4. Genera la sentencia SQL de fecha. Si hay un error, retorna un mensaje de error relatando un problema al generar la sentencia SQL.
     * 5. Si todo ha ido bien, retorna la sentencia SQL generada.
     *
     * @version 16.311.1
     */
    private function genera_sql_filtro_fecha(array $fil_fecha, string $filtro_fecha_sql): array|string
    {
        $valida = $this->valida_data_filtro_fecha(fil_fecha: $fil_fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha',data: $valida);
        }

        $data = $this->data_filtro_fecha(fil_fecha: $fil_fecha);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar datos',data:$data);
        }

        $and = $this->and_filtro_fecha(txt: $filtro_fecha_sql);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener and',data:$and);
        }

        $sql = $this->sql_fecha(and:$and,data:  $data);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener sql',data:$sql);
        }
        return $sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     *
     * Genera la condicion sql de un filtro especial
     *
     * @param string $campo campo de una tabla tabla.campo
     * @param array $columnas_extra Campos en forma de subquery del modelo
     * @param array $filtro filtro a validar
     *
     * @return array|string
     *
     * @example
     *      Ej 1
     *      $campo = 'x';
     *      $filtro['x'] = array('operador'=>'x','valor'=>'x');
     *      $resultado = maqueta_filtro_especial($campo, $filtro);
     *      $resultado = x>'x'
     *
     *      Ej 2
     *      $campo = 'x';
     *      $filtro['x'] = array('operador'=>'x','valor'=>'x','es_campo'=>true);
     *      $resultado = maqueta_filtro_especial($campo, $filtro);
     *      $resultado = 'x'> x
     *
     * @version 16.164.0
     */
    private function maqueta_filtro_especial(string $campo, array $columnas_extra, array $filtro):array|string{
        $campo = trim($campo);

        $valida = (new validaciones())->valida_data_filtro_especial(campo: $campo,filtro:  $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro', data: $valida);
        }

        $keys = array('valor');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $filtro[$campo]);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar filtro',  data:$valida);
        }


        $campo_filtro = $campo;

        $campo = $this->campo_filtro_especial(campo: $campo,columnas_extra:  $columnas_extra);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener campo',  data:$campo);
        }

        $data_sql = $this->data_sql(campo: $campo,campo_filtro:  $campo_filtro,filtro:  $filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al genera sql',  data:$data_sql);
        }


        return $data_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Genera una cláusula SQL NOT IN a partir de una llave y valores proporcionados.
     *
     * @param string $llave Clave que será usada en la cláusula NOT IN.
     * @param array $values Valores que serán incorporados en la cláusula NOT IN.
     *
     * @return string|array Devuelve una cadena que contiene una cláusula SQL NOT IN si la operación es exitosa.
     * Si ocurre un error, devuelve un array conteniendo detalles sobre el error.
     *
     * ## Uso:
     * ```php
     * not_in_sql("id", [1, 2, 3])
     * ```
     *
     * ## Ejemplo de respuesta en caso de éxito:
     * ```sql
     * "id NOT IN (1, 2, 3)"
     * ```
     *
     * ## Ejemplo de respuesta en caso de error:
     * ```php
     * [
     *     "codigo" => "ERR_CODE",
     *     "mensaje" => "Descripción detallada del error"
     * ]
     * ```
     * @version 16.272.1
     */
    private function not_in_sql(string $llave, array $values): array|string
    {
        $llave = trim($llave);
        if($llave === ''){
            return $this->error->error(mensaje: 'Error la llave esta vacia',data: $llave, es_final: true);
        }

        $values_sql = $this->values_sql_in(values:$values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $values_sql);
        }

        $not_in_sql = '';
        if($values_sql!==''){
            $not_in_sql.="$llave NOT IN ($values_sql)";
        }

        return $not_in_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Genera la condicion sql de un filtro especial
     * @param array $columnas_extra Conjunto de columnas en forma de subquery
     * @param array $filtro_esp //array con datos del filtro $filtro_esp[tabla.campo]= array('operador'=>'AND','valor'=>'x');
     *
     * @param string $filtro_especial_sql //condicion en forma de sql
     * @return array|string
     * @example
     *      Ej 1
     *      $filtro_esp[tabla.campo]['operador'] = '>';
     *      $filtro_esp[tabla.campo]['valor'] = 'x';
     *      $filtro_especial_sql = '';
     *      $resultado = obten_filtro_especial($filtro_esp, $filtro_especial_sql);
     *      $resultado =  tabla.campo > 'x'
     *
     *      Ej 2
     *      $filtro_esp[tabla.campo]['operador'] = '>';
     *      $filtro_esp[tabla.campo]['valor'] = 'x';
     *      $filtro_esp[tabla.campo]['comparacion'] = ' AND ';
     *      $filtro_especial_sql = ' tabla.campo2 = 1';
     *      $resultado = obten_filtro_especial($filtro_esp, $filtro_especial_sql);
     *      $resultado =  tabla.campo > 'x' AND tabla.campo2 = 1
     * @version 16.195.0
     *
     */

    private function obten_filtro_especial(
        array $columnas_extra, array $filtro_esp, string $filtro_especial_sql):array|string{
        $campo = key($filtro_esp);
        $campo = trim($campo);

        $valida =(new validaciones())->valida_data_filtro_especial(campo: $campo,filtro:  $filtro_esp);
        if(errores::$error){
            return $this->error->error(mensaje: "Error en filtro ", data: $valida);
        }
        $data_sql = $this->maqueta_filtro_especial(campo: $campo, columnas_extra: $columnas_extra,filtro: $filtro_esp);
        if(errores::$error){
            return $this->error->error(mensaje:"Error filtro", data:$data_sql);
        }
        $filtro_especial_sql_r = $this->genera_filtro_especial(campo:  $campo, data_sql: $data_sql,
            filtro_esp: $filtro_esp, filtro_especial_sql: $filtro_especial_sql);
        if(errores::$error){
            return $this->error->error(mensaje:"Error filtro",data: $filtro_especial_sql_r);
        }

        return $filtro_especial_sql_r;
    }


    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Comprueba y valida los valores de un campo y un campo de filtro.
     *
     * @param string $campo Representa el nombre del campo a validar.
     * @param string $campo_filtro Es el nombre del campo de filtro.
     * @param array $filtro Es un array que contiene los filtros a aplicar.
     *
     * @return true|array Si la validación es successful, retorna true.
     *                    En caso contrario, se retorna un array con detalles del error producido.
     *
     * @throws errores si algún parámetro no es del tipo esperado.
     *
     * Ejemplo de uso:
     *
     *      valida_campo_filtro("nombre", "nombre_filtro", array("nombre_filtro" => array("operador" => "igual", "valor" => "Juan")))
     *
     * Los posibles errores que retorna son:
     * - Error campo_filtro esta vacio.
     * - Error campo esta vacio.
     * - Error no existe $filtro[campo_filtro].
     * - Error no es un array $filtro[campo_filtro].
     * - Error no existe $filtro[campo_filtro][operador].
     * - Error no existe $filtro[campo_filtro][valor].
     * - Error esta vacio $filtro[campo_filtro][operador].
     * @version 16.160.0
     */
    private function valida_campo_filtro(string $campo, string $campo_filtro, array $filtro): true|array
    {
        $campo_filtro = trim($campo_filtro);
        if($campo_filtro === ''){
            return $this->error->error(mensaje:'Error campo_filtro esta vacio',  data:$campo_filtro, es_final: true);
        }
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje:'Error campo esta vacio',  data:$campo, es_final: true);
        }
        if(!isset($filtro[$campo_filtro])){
            return $this->error->error(mensaje:'Error no existe $filtro['.$campo_filtro.']',  data:$campo,
                es_final: true);
        }
        if(!is_array($filtro[$campo_filtro])){
            return $this->error->error(mensaje:'Error no es un array $filtro['.$campo_filtro.']',  data:$campo,
                es_final: true);
        }
        if(!isset($filtro[$campo_filtro]['operador'])){
            return $this->error->error(mensaje:'Error no existe $filtro['.$campo_filtro.'][operador]',  data:$campo,
                es_final: true);
        }
        if(!isset($filtro[$campo_filtro]['valor'])){
            return $this->error->error(mensaje:'Error no existe $filtro['.$campo_filtro.'][valor]',  data:$campo,
                es_final: true);
        }
        if(trim(($filtro[$campo_filtro]['operador'])) === ''){
            return $this->error->error(mensaje:'Error esta vacio $filtro['.$campo_filtro.'][operador]',  data:$campo,
                es_final: true);
        }
        return true;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función valida un filtro de fecha proporcionado en un array.
     *
     * Dentro del array proporcionado, la función busca la presencia de las claves 'campo_1', 'campo_2' y 'fecha'.
     * En caso de que estas claves no existan dentro del array, la función devuelve un error
     * describiendo la ausencia de las claves requeridas.
     *
     * Si las claves requeridas están presentes, la función procede a validar si el valor correspondiente
     * a la clave 'fecha' es una fecha válida. Si el valor no es una fecha válida, la función devuelve un error.
     *
     * En caso de que tanto las claves requeridas estén presentas y 'fecha' sea una fecha valida, la función devuelve true.
     *
     * @param array $fil_fecha El array que contiene el filtro de fechas. Este debe contener las claves 'campo_1', 'campo_2' y 'fecha'.
     * @return bool|array Retorna true si las validaciones son exitosas. Retorna un array de errores si alguna validación falla.
     *
     * @version 16.306.1
     */
    private function valida_data_filtro_fecha(array $fil_fecha): true|array
    {
        $keys = array('campo_1','campo_2','fecha');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $fil_fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }
        $valida = $this->validacion->valida_fecha(fecha: $fil_fecha['fecha']);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar fecha',data:$valida);
        }
        return true;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función valida un array de filtro de fecha.
     *
     * @param array $fil_fecha Array que contiene la información del filtro de fecha. Debería de contener los campos 'campo_1', 'campo_2' y 'fecha'.
     *
     * @return bool|array Retorna verdadero si el filtro de fecha es válido. De lo contrario, retorna un Error.
     *
     * @throws errores Posibles errores que pueden ocurrir durante la validación.
     * @version 16.305.1
     */
    private function valida_filtro_fecha(array $fil_fecha): bool|array
    {

        $keys = array('campo_1','campo_2','fecha');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $fil_fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }

        $keys = array('fecha');
        $valida = $this->validacion->fechas_in_array(data:  $fil_fecha, keys: $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }
        return true;
    }

    /**
     * TOTAL
     * Función privada que procesa los datos de entrada y los limpia para su posterior uso.
     *
     * @param array|string|null $data Datos de entrada para ser procesados.
     *
     * @return string|array En caso de error, retorna un array con detalles del error. De lo contrario,
     * retorna los datos de entrada procesados y limpios en forma de string.
     *
     * @throws errores en caso de que haya algún error durante el proceso.
     * @version 16.98.0
     * @url https://github.com/gamboamartin/where/wiki/src.where.value
     */
    private function value(array|string|null $data):string|array{
        $value = $data;
        if(is_array($data) && isset($data['value'])){
            $value = trim($data['value']);
        }
        if(is_array($data) && count($data) === 0){
            return $this->error->error(mensaje: "Error datos vacio",data: $data, es_final: true);
        }
        if(is_array($data) && !isset($data['value'])){
            return $this->error->error(mensaje:"Error no existe valor",data: $data,es_final: true);
        }
        if(is_null($value)){
            $value = '';
        }
        return addslashes($value);
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Este método comprueba si el valor proporcionado está vacío y, en caso de que no lo esté,
     * añade una coma al final de la cadena de valores SQL existente.
     *
     * @param string $value El valor para comprobar y añadir a la cadena SQL.
     * @param string $values_sql La cadena SQL existente que se actualizará.
     *
     * @return array|stdClass Devuelve un objeto con el valor y la coma si todo está bien,
     *                        de lo contrario retorna un mensaje de error.
     *
     * @throws errores Si el valor está vacío.
     * @version 16.261.1
     */
    private function value_coma(string $value, string $values_sql): array|stdClass
    {
        $values_sql = trim($values_sql);
        $value = trim($value);
        if($value === ''){
            return $this->error->error(mensaje: 'Error value esta vacio',data: $value, es_final: true);
        }

        $coma = '';
        if($values_sql !== ''){
            $coma = ' ,';
        }

        $data = new stdClass();
        $data->value = $value;
        $data->coma = $coma;
        return $data;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función privada toma un array de valores y los procesa para formar una parte de una consulta SQL.
     *
     * Recorre cada valor en el conjunto de valores proporcionado para escapar y formatear correctamente el valor en
     * una representación de cadena que puede ser utilizada en una consulta SQL.
     * Los valores son escapados para seguridad y entre comillas para representarlos como cadenas en SQL.
     * Finalmente, cada valor procesado se concatena a la cadena $values_sql con una coma y el valor.
     *
     * Si ocurre algún error durante este proceso, se devolverá un mensaje de error.
     *
     * @param array $values Un conjunto de valores que se deben formatear y escapar para su uso en una consulta SQL.
     * @return string|array Una cadena que representa la parte de una consulta SQL con valores formateados y escapados,
     * o un mensaje de error si se encuentra algún problema.
     * @version 16.262.1
     */
    private function values_sql_in(array $values): string|array
    {
        $values_sql = '';
        foreach ($values as $value){
            $value = trim($value);
            if($value === ''){
                return $this->error->error(mensaje: 'Error value esta vacio',data: $value, es_final: true);
            }
            $data = $this->value_coma(value:$value, values_sql: $values_sql);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error obtener datos de value',data: $data);
            }

            $value = addslashes($value);
            $value = "'$value'";

            $values_sql.="$data->coma$value";
        }
        return $values_sql;
    }

    /**
     * TOTAL
     * Verifica el tipo de filtro proporcionado.
     *
     * @param string $tipo_filtro El tipo de filtro a verificar.
     * @return true|array Devuelve true si el tipo de filtro es correcto,
     *         si no, devuelve un array con un error.
     *
     * La función realiza las siguientes acciones:
     * 1. Limpia el tipo de filtro ingresado.
     * 2. Si el tipo de filtro es una cadena vacía, se establece como 'numeros'.
     * 3. Define los tipos permitidos de filtro como 'numeros' y 'textos'.
     * 4. Verifica si el tipo de filtro ingresado pertenece a los tipos permitidos.
     *    Si no es así, crea un nuevo objeto stdClass y establece la propiedad
     *    tipo_filtro con el valor ingresado y retorna un error con el mensaje y los datos correspondientes.
     * @version 13.8.0
     * @url https://github.com/gamboamartin/where/wiki/src.where.verifica_tipo_filtro
     */
    final public function verifica_tipo_filtro(string $tipo_filtro): true|array
    {
        $tipo_filtro = trim($tipo_filtro);
        if($tipo_filtro === ''){
            $tipo_filtro = 'numeros';
        }
        $tipos_permitidos = array('numeros','textos');
        if(!in_array($tipo_filtro,$tipos_permitidos)){

            $params = new stdClass();
            $params->tipo_filtro = $tipo_filtro;

            return $this->error->error(
                mensaje: 'Error el tipo filtro no es correcto los filtros pueden ser o numeros o textos',
                data: $params, es_final: true);
        }
        return true;
    }

}
