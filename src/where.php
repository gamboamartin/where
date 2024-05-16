<?php
namespace gamboamartin\src;
use gamboamartin\errores\errores;
use stdClass;

class where
{
    private errores $error;
    public function __construct()
    {
        $this->error = new errores();
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
     * Esta función procesa las entradas proporcionadas y devuelve el "campo" apropiado.
     *
     * @param array|string|null $data los datos proporcionados para extraer el campo. Pueden ser de tipos array, string o null.
     * @param string $key la clave proporcionada para extraer el campo del array.
     * @return string|array Devuelve el "campo" después de ser procesado y garantiza que no contenga caracteres de escape.
     *
     * @throws errores si la clave proporcionada está vacía.
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
    final public function campo_data_filtro(array $data_filtro): string|array
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
    final public function campo_filtro_especial(string $campo, array $columnas_extra): array|string
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje:'Error campo esta vacio',  data:$campo, es_final: true);
        }

        $es_subquery = (new \gamboamartin\src\where())->es_subquery(campo: $campo,columnas_extra:  $columnas_extra);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al subquery bool',  data:$es_subquery);
        }

        if($es_subquery){
            $campo = $columnas_extra[$campo];
        }
        return $campo;

    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
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
     */
    private function comparacion(array|string|null $data, string $default):string{
        return $data['comparacion'] ?? $default;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
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
    final public function data_sql(string $campo, string $campo_filtro, array $filtro): array|string
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
     * Determina si un campo es un subquery basado en la existencia del campo en las columnas extra.
     *
     * @param string $campo El campo a evaluar si es un subquery.
     * @param array $columnas_extra Las columnas extra donde se va a buscar el campo.
     * @return bool|array Retorna verdadero si el campo es un subquery, en caso contrario retorna falso.
     *  En el caso de que el campo esté vacío, se retorna un error.
     */
    final public function es_subquery(string $campo, array $columnas_extra): bool|array
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
    final public function genera_and_textos(array $columnas_extra, array $filtro):array|string{

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
     * Función privada que procesa los datos de entrada y los limpia para su posterior uso.
     *
     * @param array|string|null $data Datos de entrada para ser procesados.
     *
     * @return string|array En caso de error, retorna un array con detalles del error. De lo contrario,
     * retorna los datos de entrada procesados y limpios en forma de string.
     *
     * @throws errores en caso de que haya algún error durante el proceso.
     * @version 16.98.0
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

}
