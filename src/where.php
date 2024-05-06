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
    final public function campo(array|string|null $data, string $key):string|array{
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

}
