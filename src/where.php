<?php
namespace gamboamartin\src;
use stdClass;

class where
{
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

}
