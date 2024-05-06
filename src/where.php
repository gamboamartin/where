<?php
namespace gamboamartin\src;
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

}
