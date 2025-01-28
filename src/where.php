<?php
namespace gamboamartin\where;
use gamboamartin\errores\errores;
use gamboamartin\src\sql;
use gamboamartin\src\validaciones;
use gamboamartin\validacion\validacion;
use stdClass;

/**
 * TOTAL
 * Clase where
 *
 * Esta clase contiene una serie de métodos para generar consultas SQL con filtros
 * como IN, NOT IN, BETWEEN, y otros. Cada método está orientado a validar y construir
 * las partes de una consulta SQL según los parámetros dados.
 *
 * ### Dependencias:
 * - Utiliza las clases `errores`, `sql`, `validacion`, y `validaciones`.
 *
 * ### Métodos principales:
 * - Filtros SQL (`IN`, `NOT IN`, `BETWEEN`, etc.).
 * - Validación de campos y filtros.
 * - Generación de sentencias SQL con múltiples condiciones.
 *
 */

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
     * REG
     * Retorna la cadena `" AND "` si `$txt` no está vacío; de lo contrario, retorna una cadena vacía.
     *
     * Este método se puede usar para concatenar condiciones adicionales en una cláusula SQL
     * únicamente cuando el texto `$txt` contenga información (por ejemplo, un filtro o
     * condición). Si `$txt` está vacío, no se agrega `" AND "`.
     *
     * @param string $txt Cadena a validar. Si no es `''`, se retornará `" AND "`.
     *
     * @return string Retorna:
     *  - `" AND "` si `$txt` no está vacío.
     *  - `""` (cadena vacía) si `$txt` está vacío.
     *
     * @example
     *  Ejemplo 1: `$txt` con valor
     *  -----------------------------------------------------------------------------------
     *  $txt = "fecha > '2020-01-01'";
     *  $resultado = $this->and_filtro_fecha($txt);
     *  // $resultado será " AND ".
     *
     * @example
     *  Ejemplo 2: `$txt` vacío
     *  -----------------------------------------------------------------------------------
     *  $txt = "";
     *  $resultado = $this->and_filtro_fecha($txt);
     *  // $resultado será "" (cadena vacía).
     */
    final public function and_filtro_fecha(string $txt): string
    {
        $and = '';
        if ($txt !== '') {
            $and = ' AND ';
        }
        return $and;
    }


    /**
     * REG
     * Asigna datos relacionados con diferentes filtros SQL en un objeto `stdClass`.
     *
     * Este método organiza y asigna múltiples cláusulas y condiciones SQL en propiedades de un objeto,
     * facilitando el manejo estructurado de filtros complejos en consultas SQL.
     *
     * @param string $diferente_de_sql   Cláusula SQL para valores diferentes (`NOT EQUAL`).
     * @param string $filtro_especial_sql Cláusula SQL para filtros especiales personalizados.
     * @param string $filtro_extra_sql   Cláusula SQL para filtros adicionales.
     * @param string $filtro_fecha_sql   Cláusula SQL para filtros basados en fechas.
     * @param string $filtro_rango_sql   Cláusula SQL para filtros de rango.
     * @param string $in_sql             Cláusula SQL `IN`.
     * @param string $not_in_sql         Cláusula SQL `NOT IN`.
     * @param string $sentencia          Sentencia principal SQL generada.
     * @param string $sql_extra          SQL adicional que puede ser integrado a la consulta.
     *
     * @return stdClass Objeto con las siguientes propiedades asignadas:
     *  - `sentencia`: Sentencia principal SQL.
     *  - `filtro_especial`: Cláusula de filtros especiales.
     *  - `filtro_rango`: Cláusula de rango.
     *  - `filtro_extra`: Filtros adicionales.
     *  - `in`: Cláusula `IN`.
     *  - `not_in`: Cláusula `NOT IN`.
     *  - `diferente_de`: Cláusula para valores diferentes.
     *  - `sql_extra`: SQL adicional.
     *  - `filtro_fecha`: Filtros basados en fechas.
     *
     * @example
     *  Ejemplo 1: Asignar diferentes filtros en un objeto
     *  -------------------------------------------------------------------------
     *  $diferente_de_sql = "campo1 != 'valor1'";
     *  $filtro_especial_sql = "campo2 = 'valor2'";
     *  $filtro_extra_sql = "campo3 LIKE '%valor3%'";
     *  $filtro_fecha_sql = "fecha >= '2023-01-01'";
     *  $filtro_rango_sql = "campo4 BETWEEN '10' AND '20'";
     *  $in_sql = "campo5 IN ('1', '2', '3')";
     *  $not_in_sql = "campo6 NOT IN ('4', '5', '6')";
     *  $sentencia = "SELECT * FROM tabla";
     *  $sql_extra = "ORDER BY campo1 ASC";
     *
     *  $resultado = $this->asigna_data_filtro(
     *      $diferente_de_sql, $filtro_especial_sql, $filtro_extra_sql,
     *      $filtro_fecha_sql, $filtro_rango_sql, $in_sql, $not_in_sql, $sentencia, $sql_extra
     *  );
     *
     *  // Retorna un objeto con las propiedades asignadas:
     *  // $resultado->sentencia = "SELECT * FROM tabla";
     *  // $resultado->filtro_especial = "campo2 = 'valor2'";
     *  // $resultado->filtro_rango = "campo4 BETWEEN '10' AND '20'";
     *  // ...
     */
    final public function asigna_data_filtro(
        string $diferente_de_sql,
        string $filtro_especial_sql,
        string $filtro_extra_sql,
        string $filtro_fecha_sql,
        string $filtro_rango_sql,
        string $in_sql,
        string $not_in_sql,
        string $sentencia,
        string $sql_extra
    ): stdClass {
        $filtros = new stdClass();
        $filtros->sentencia = $sentencia;
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
     * REG
     * Obtiene el valor de un campo desde un arreglo o utiliza el valor de `$key` como valor predeterminado.
     *
     * - Si `$key` está vacío, se considera un error y se retorna un arreglo con los detalles del error.
     * - Si `$data` es un arreglo y contiene la clave `'campo'`, se retorna el valor de esa clave con `addslashes()` aplicado.
     * - Si no existe la clave `'campo'` en `$data`, se retorna `$key` como el valor predeterminado.
     * - En cualquier caso, el valor retornado tiene escapados los caracteres especiales mediante `addslashes()`.
     *
     * @param array|string|null $data Arreglo de datos del cual se intentará obtener el valor del campo `'campo'`.
     * @param string            $key  Clave predeterminada que se retornará si no se encuentra `'campo'` en `$data`.
     *
     * @return string|array Retorna:
     *  - Un `string` con el valor escapado del campo.
     *  - Un arreglo de error si `$key` está vacío.
     *
     * @example
     *  Ejemplo 1: `$data` contiene `'campo'`
     *  ---------------------------------------------------------------------
     *  $data = ['campo' => "nombre"];
     *  $key = "predeterminado";
     *  $resultado = $this->campo($data, $key);
     *  // $resultado será "nombre" (con escapado de caracteres especiales si aplica).
     *
     * @example
     *  Ejemplo 2: `$data` no contiene `'campo'`
     *  ---------------------------------------------------------------------
     *  $data = ['otro_campo' => "valor"];
     *  $key = "predeterminado";
     *  $resultado = $this->campo($data, $key);
     *  // $resultado será "predeterminado" (escapado si aplica).
     *
     * @example
     *  Ejemplo 3: `$key` vacío
     *  ---------------------------------------------------------------------
     *  $data = ['campo' => "nombre"];
     *  $key = "";
     *  $resultado = $this->campo($data, $key);
     *  // Retorna un arreglo de error:
     *  // [
     *  //   'error'   => 1,
     *  //   'mensaje' => "Error key vacio",
     *  //   'data'    => ""
     *  // ]
     */
    private function campo(array|string|null $data, string $key): string|array
    {
        // Validar que la clave no sea vacía
        if ($key === '') {
            return $this->error->error(
                mensaje: "Error key vacio",
                data: $key,
                es_final: true
            );
        }

        // Obtener el valor de 'campo' o utilizar $key como predeterminado
        $campo = $data['campo'] ?? $key;

        // Retornar el valor escapado con addslashes
        return addslashes($campo);
    }


    /**
     * TOTAL
     * La función campo_data_filtro se usa para aplicar ciertas validaciones en la clave del array $data_filtro.
     *
     * @param  array $data_filtro El array de entrada que se tiene que validar.
     * @throws errores Si la clave del array está vacía o si la clave no es un string válido (no numérico).
     * @return string|array Devuelve la clave del array $data_filtro después de apliar trim() si la validación es exitosa.
     *                     En caso de error, se devuelve un array con los detalles del error.
     *
     * @url https://github.com/gamboamartin/where/wiki/src.where.campo_data_filtro
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
     * REG
     * Procesa un campo para un filtro especial, permitiendo identificar si el campo
     * corresponde a una subconsulta definida en las columnas adicionales.
     * Si es una subconsulta, sustituye el valor del campo con su definición.
     *
     * @param string $campo El nombre del campo a procesar. No debe estar vacío.
     * @param array $columnas_extra Array asociativo donde las claves representan campos
     *                               y los valores contienen subconsultas o definiciones adicionales.
     *
     * @return array|string El campo procesado como cadena si no es una subconsulta,
     *                      o la definición de subconsulta si existe en $columnas_extra.
     *                      Devuelve un array de error en caso de fallos en la validación.
     *
     * @throws errores Si ocurre algún error al validar o procesar los datos.
     *
     * @example Uso exitoso:
     * ```php
     * $columnas_extra = [
     *     'total' => '(SELECT SUM(cantidad) FROM ventas WHERE ventas.producto_id = productos.id)',
     *     'descuento' => '(SELECT descuento FROM promociones WHERE promociones.id = productos.promocion_id)'
     * ];
     *
     * $campo = 'total';
     * $resultado = $objeto->campo_filtro_especial(campo: $campo, columnas_extra: $columnas_extra);
     *
     * // Resultado esperado:
     * // $resultado = '(SELECT SUM(cantidad) FROM ventas WHERE ventas.producto_id = productos.id)';
     * ```
     *
     * @example Campo sin subconsulta:
     * ```php
     * $columnas_extra = [
     *     'descuento' => '(SELECT descuento FROM promociones WHERE promociones.id = productos.promocion_id)'
     * ];
     *
     * $campo = 'nombre';
     * $resultado = $objeto->campo_filtro_especial(campo: $campo, columnas_extra: $columnas_extra);
     *
     * // Resultado esperado:
     * // $resultado = 'nombre';
     * ```
     */
    final public function campo_filtro_especial(string $campo, array $columnas_extra): array|string
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
     * REG
     * Obtiene el valor de comparación desde los datos proporcionados o utiliza un valor predeterminado si no está definido.
     *
     * - Si `$data` es un array y contiene la clave `'comparacion'`, retorna su valor.
     * - Si la clave `'comparacion'` no existe en `$data`, retorna el valor predeterminado `$default`.
     *
     * @param array|string|null $data    Datos desde los cuales se intentará obtener el valor de `'comparacion'`.
     * @param string            $default Valor predeterminado que se utilizará si `'comparacion'` no está definido en `$data`.
     *
     * @return string Retorna el valor de `'comparacion'` si está definido en `$data`. De lo contrario, retorna `$default`.
     *
     * @example
     *  Ejemplo 1: `$data` contiene la clave 'comparacion'
     *  ---------------------------------------------------------------------
     *  $data = ['comparacion' => 'igual'];
     *  $default = 'diferente';
     *
     *  $resultado = $this->comparacion($data, $default);
     *  // Retorna: "igual".
     *
     * @example
     *  Ejemplo 2: `$data` no contiene la clave 'comparacion'
     *  ---------------------------------------------------------------------
     *  $data = ['otro_campo' => 'valor'];
     *  $default = 'diferente';
     *
     *  $resultado = $this->comparacion($data, $default);
     *  // Retorna: "diferente".
     *
     * @example
     *  Ejemplo 3: `$data` es null
     *  ---------------------------------------------------------------------
     *  $data = null;
     *  $default = 'diferente';
     *
     *  $resultado = $this->comparacion($data, $default);
     *  // Retorna: "diferente".
     *
     * @example
     *  Ejemplo 4: `$data` como cadena
     *  ---------------------------------------------------------------------
     *  $data = 'texto';
     *  $default = 'diferente';
     *
     *  $resultado = $this->comparacion($data, $default);
     *  // Retorna: "diferente", ya que no es un array.
     */
    private function comparacion(array|string|null $data, string $default): string
    {
        // Retorna el valor de 'comparacion' si existe en $data, de lo contrario $default
        return $data['comparacion'] ?? $default;
    }


    /**
     * REG
     * Realiza una validación y procesamiento de datos para comparar una clave y su valor, considerando columnas adicionales si están presentes.
     *
     * Este método:
     * 1. **Validación de la clave (`$key`)**:
     *    - Si está vacía, retorna un error.
     * 2. **Validación de los datos (`$data`)**:
     *    - Si es un array vacío, retorna un error.
     * 3. **Maquetación de datos**:
     *    - Construye el campo y el valor utilizando los métodos {@see campo()} y {@see value()}.
     *    - Aplica validaciones durante el proceso.
     * 4. **Consideración de columnas adicionales**:
     *    - Si `$key` existe en `$columnas_extra`, sustituye el campo generado por el valor correspondiente en `$columnas_extra`.
     *
     * @param array               $columnas_extra Array asociativo de columnas adicionales donde la clave es el nombre del campo.
     * @param array|string|null   $data           Datos de entrada que contienen el campo y/o el valor a procesar.
     * @param string              $key            Clave que identifica el campo a procesar y validar.
     *
     * @return array|stdClass Retorna:
     *  - Un objeto `stdClass` con las propiedades:
     *      - `campo`: Campo procesado y validado (posiblemente sobrescrito por `$columnas_extra`).
     *      - `value`: Valor procesado y validado.
     *  - Un arreglo de error si alguna validación falla.
     *
     * @example
     *  Ejemplo 1: Procesar datos válidos
     *  -----------------------------------------------------------------------
     *  $columnas_extra = ['campo_extra' => 'tabla.campo_extra'];
     *  $data = ['campo' => 'id_usuario', 'value' => 123];
     *  $key = 'campo_extra';
     *
     *  $resultado = $this->comparacion_pura($columnas_extra, $data, $key);
     *  // Retorna un objeto stdClass con:
     *  // $resultado->campo => 'tabla.campo_extra'
     *  // $resultado->value => '123' (escapado con addslashes)
     *
     * @example
     *  Ejemplo 2: Error por clave vacía
     *  -----------------------------------------------------------------------
     *  $columnas_extra = ['campo_extra' => 'tabla.campo_extra'];
     *  $data = ['campo' => 'id_usuario', 'value' => 123];
     *  $key = '';
     *
     *  $resultado = $this->comparacion_pura($columnas_extra, $data, $key);
     *  // Retorna un arreglo de error indicando "Error key vacio".
     *
     * @example
     *  Ejemplo 3: Error por datos vacíos
     *  -----------------------------------------------------------------------
     *  $columnas_extra = ['campo_extra' => 'tabla.campo_extra'];
     *  $data = [];
     *  $key = 'campo_extra';
     *
     *  $resultado = $this->comparacion_pura($columnas_extra, $data, $key);
     *  // Retorna un arreglo de error indicando "Error datos vacio".
     */
    private function comparacion_pura(array $columnas_extra, array|string|null $data, string $key): array|stdClass
    {
        // Validar que la clave no esté vacía
        if ($key === '') {
            return $this->error->error(
                mensaje: "Error key vacio",
                data: $key,
                es_final: true
            );
        }

        // Validar que los datos no sean un array vacío
        if (is_array($data) && count($data) === 0) {
            return $this->error->error(
                mensaje: "Error datos vacio",
                data: $data,
                es_final: true
            );
        }

        // Maquetar el campo utilizando el método `campo`
        $datas = new stdClass();
        $datas->campo = $this->campo(data: $data, key: $key);
        if (errores::$error) {
            return $this->error->error(
                mensaje: "Error al maquetar campo",
                data: $datas->campo
            );
        }

        // Maquetar el valor utilizando el método `value`
        $datas->value = $this->value(data: $data);
        if (errores::$error) {
            return $this->error->error(
                mensaje: "Error al validar maquetacion",
                data: $datas->value
            );
        }

        // Verificar si la clave está presente en `$columnas_extra`
        $es_sq = false;
        if (isset($columnas_extra[$key])) {
            $es_sq = true;
        }

        // Sobrescribir el campo si está en `$columnas_extra`
        if ($es_sq) {
            $datas->campo = $columnas_extra[$key];
        }

        return $datas;
    }


    /**
     * REG
     * Genera una condición SQL para un intervalo en la forma `campo BETWEEN 'valor1' AND 'valor2'`.
     *
     * - Valida que `$campo` no sea una cadena vacía.
     * - Asegura que `$filtro` contenga las claves `valor1` y `valor2`.
     * - Luego construye la cadena para la condición `BETWEEN`.
     * - Si `$valor_campo` es `true`, se asume que `campo` ya viene con sus propias comillas y se omite el envoltorio de `'...'`
     *   en los valores `valor1` y `valor2`.
     *   De lo contrario, el campo se incluye directamente, rodeado de comillas simples en la parte izquierda y derecha.
     *
     * @param string $campo         Nombre de la columna para la cláusula BETWEEN (p. ej. `"fecha"`).
     * @param array  $filtro        Debe contener al menos `['valor1' => x, 'valor2' => y]`.
     * @param bool   $valor_campo   Indica si `$campo` y los valores se usan textualmente o con comillas para el BETWEEN.
     *
     * @return string|array Retorna la cadena de condición (p. ej. `"fecha BETWEEN '2023-01-01' AND '2023-12-31'"`),
     *                      o un arreglo de error si se presenta alguna falla.
     *
     * @example
     *  Ejemplo 1: Uso con valores de fecha
     *  --------------------------------------------------------------------------------
     *  $campo = "fecha_creacion";
     *  $filtro = [
     *      'valor1' => '2023-01-01',
     *      'valor2' => '2023-12-31'
     *  ];
     *  $resultado = $this->condicion_entre($campo, $filtro, false);
     *  // Retornará:
     *  // "fecha_creacion BETWEEN '2023-01-01' AND '2023-12-31'"
     *
     * @example
     *  Ejemplo 2: $valor_campo = true
     *  --------------------------------------------------------------------------------
     *  $campo = "campoEspecial";
     *  $filtro = [
     *      'valor1' => 100,
     *      'valor2' => 200
     *  ];
     *  $resultado = $this->condicion_entre($campo, $filtro, true);
     *  // Retornará:
     *  // "'campoEspecial' BETWEEN 100 AND 200"
     *
     * @example
     *  Ejemplo 3: Falta clave en $filtro
     *  --------------------------------------------------------------------------------
     *  $campo = "precio";
     *  $filtro = [
     *      'valor1' => 100
     *      // Falta 'valor2'
     *  ];
     *  $resultado = $this->condicion_entre($campo, $filtro, false);
     *  // Retornará un arreglo de error indicando "Error campo vacío $filtro[valor2]"
     */
    private function condicion_entre(string $campo, array $filtro, bool $valor_campo): string|array
    {
        $campo = trim($campo);
        if ($campo === '') {
            return $this->error->error(
                mensaje: 'Error campo vacío',
                data: $campo,
                es_final: true
            );
        }

        if (!isset($filtro['valor1'])) {
            return $this->error->error(
                mensaje: 'Error campo vacío $filtro[valor1]',
                data: $campo,
                es_final: true
            );
        }

        if (!isset($filtro['valor2'])) {
            return $this->error->error(
                mensaje: 'Error campo vacío $filtro[valor2]',
                data: $campo,
                es_final: true
            );
        }

        // Construye la condición BETWEEN dependiendo de valor_campo
        $condicion = $campo . ' BETWEEN ' . "'" . $filtro['valor1'] . "'" . ' AND ' . "'" . $filtro['valor2'] . "'";

        if ($valor_campo) {
            $condicion = "'" . $campo . "'" . ' BETWEEN ' . $filtro['valor1'] . ' AND ' . $filtro['valor2'];
        }

        return $condicion;
    }


    /**
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.data_filtro_fecha
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
     * REG
     * Valida y transforma un arreglo `$in` que describe una cláusula `IN`, asegurándose de que contenga
     * las claves `'llave'` y `'values'`. Además, verifica que `'values'` sea un arreglo.
     *
     * - Primero, usa {@see validacion->valida_existencia_keys()} para confirmar que `$in` contenga las claves requeridas.
     * - Luego, comprueba que `'values'` sea realmente un array.
     * - Si alguna validación falla, se invoca `$this->error->error()` y se retorna un arreglo con la información del error.
     * - Si todo es correcto, se retorna un `stdClass` con las propiedades:
     *   - `llave`: El nombre de la llave o columna.
     *   - `values`: El arreglo de valores a usar en la cláusula `IN`.
     *
     * @param array $in Estructura que debe contener al menos:
     *                  - 'llave'  (string): Nombre de la columna.
     *                  - 'values' (array): Lista de valores a incluir en la cláusula IN.
     *
     * @return array|stdClass Retorna:
     *  - Un objeto `stdClass` con las propiedades `llave` y `values` si todo es válido.
     *  - Un arreglo con información del error (generado por `$this->error->error()`) si algo falla.
     *
     * @example
     *  Ejemplo 1: Entrada válida
     *  ----------------------------------------------------------------------------
     *  $in = [
     *      'llave' => 'categoria_id',
     *      'values' => [10, 20, 30]
     *  ];
     *
     *  $resultado = $this->data_in($in);
     *  // $resultado será un stdClass con:
     *  // {
     *  //   llave: 'categoria_id',
     *  //   values: [10, 20, 30]
     *  // }
     *
     * @example
     *  Ejemplo 2: Falta la clave 'values'
     *  ----------------------------------------------------------------------------
     *  $in = [
     *      'llave' => 'categoria_id'
     *      // Falta 'values'
     *  ];
     *
     *  $resultado = $this->data_in($in);
     *  // Retorna un arreglo de error indicando que 'values' no existe.
     *
     * @example
     *  Ejemplo 3: 'values' no es un array
     *  ----------------------------------------------------------------------------
     *  $in = [
     *      'llave' => 'categoria_id',
     *      'values' => 'no_es_un_array'
     *  ];
     *
     *  $resultado = $this->data_in($in);
     *  // Retorna un arreglo de error indicando que 'values' debe ser un array.
     */
    final public function data_in(array $in): array|stdClass
    {
        $keys = array('llave', 'values');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $in);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar not_in',
                data: $valida
            );
        }

        $values = $in['values'];

        if (!is_array($values)) {
            return $this->error->error(
                mensaje: 'Error values debe ser un array',
                data: $values,
                es_final: true
            );
        }

        $data = new stdClass();
        $data->llave  = $in['llave'];
        $data->values = $in['values'];

        return $data;
    }


    /**
     * REG
     * Genera una cláusula SQL basada en un campo, un filtro y su configuración.
     * Realiza validaciones y construye la cláusula en función de si el valor en el filtro es un campo o un dato estático.
     *
     * @param string $campo El nombre del campo en la base de datos que será parte de la cláusula SQL.
     * @param string $campo_filtro El identificador dentro del array `$filtro` que contiene las claves necesarias para el filtro.
     * @param array $filtro Un array asociativo que define el filtro con las claves requeridas:
     *                      - `$filtro[$campo_filtro]['operador']`: Operador SQL (por ejemplo, `=`, `>`, `<`).
     *                      - `$filtro[$campo_filtro]['valor']`: Valor del filtro (por ejemplo, un número o cadena).
     *                      - `$filtro[$campo_filtro]['valor_es_campo']`: Opcional. Indica si el valor es un campo de base de datos (booleano).
     *
     * @return array|string Retorna una cadena con la cláusula SQL generada si los datos son válidos.
     *                      En caso de error, retorna un array con los detalles del error generado por la clase `errores`.
     *
     * @throws errores Si ocurre un error en las validaciones de los datos o en la generación de la cláusula SQL.
     *
     * @example Uso exitoso con valor estático:
     * ```php
     * $campo = 'precio';
     * $campo_filtro = 'filtro_precio';
     * $filtro = [
     *     'filtro_precio' => [
     *         'operador' => '>',
     *         'valor' => 100
     *     ]
     * ];
     *
     * $resultado = $this->data_sql(campo: $campo, campo_filtro: $campo_filtro, filtro: $filtro);
     * // Resultado esperado: " precio > '100' "
     * ```
     *
     * @example Uso exitoso con valor como campo:
     * ```php
     * $campo = 'precio';
     * $campo_filtro = 'filtro_precio';
     * $filtro = [
     *     'filtro_precio' => [
     *         'operador' => '=',
     *         'valor' => 'otro_campo',
     *         'valor_es_campo' => true
     *     ]
     * ];
     *
     * $resultado = $this->data_sql(campo: $campo, campo_filtro: $campo_filtro, filtro: $filtro);
     * // Resultado esperado: "'precio'=otro_campo"
     * ```
     *
     * @example Error por datos incompletos:
     * ```php
     * $campo = 'precio';
     * $campo_filtro = 'filtro_precio';
     * $filtro = [
     *     'filtro_precio' => [
     *         'operador' => '',
     *         'valor' => 100
     *     ]
     * ];
     *
     * $resultado = $this->data_sql(campo: $campo, campo_filtro: $campo_filtro, filtro: $filtro);
     * // Resultado esperado: Array indicando que el operador está vacío.
     * ```
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
     * REG
     * Genera una cláusula SQL basada en un campo, un filtro y su operador.
     * Valida que los datos de entrada sean correctos y estén completos antes de construir la cláusula.
     *
     * @param string $campo El nombre del campo que será parte de la cláusula SQL. No debe estar vacío.
     * @param string $campo_filtro El identificador dentro del array `$filtro` que contiene los valores del operador y del valor.
     * @param array $filtro El array que define el filtro. Debe incluir las claves requeridas:
     *                      `$filtro[$campo_filtro]['operador']` y `$filtro[$campo_filtro]['valor']`.
     *
     * @return string|array Retorna una cadena con la cláusula SQL generada si los datos son válidos.
     *                      En caso de error, devuelve un array con los detalles del error.
     *
     * @throws array Si los datos de entrada no cumplen con las validaciones, retorna un array con detalles del error.
     *
     * @example Uso exitoso:
     * ```php
     * $campo = 'precio';
     * $campo_filtro = 'filtro_precio';
     * $filtro = [
     *     'filtro_precio' => [
     *         'operador' => '>',
     *         'valor' => 100
     *     ]
     * ];
     *
     * $resultado = $this->data_sql_base(campo: $campo, campo_filtro: $campo_filtro, filtro: $filtro);
     * // Resultado esperado: " precio > '100' "
     * ```
     *
     * @example Error por datos incompletos:
     * ```php
     * $campo = 'precio';
     * $campo_filtro = 'filtro_precio';
     * $filtro = [
     *     'filtro_precio' => [
     *         'operador' => '',
     *         'valor' => 100
     *     ]
     * ];
     *
     * $resultado = $this->data_sql_base(campo: $campo, campo_filtro: $campo_filtro, filtro: $filtro);
     * // Resultado esperado: Array indicando que el operador está vacío.
     * ```
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
     * REG
     * Genera una cláusula SQL para un campo específico basándose en un filtro y su operador.
     * Valida los datos de entrada para garantizar la construcción correcta de la cláusula.
     *
     * @param string $campo El nombre del campo que será parte de la cláusula SQL. Debe ser no vacío.
     * @param string $campo_filtro El identificador dentro del array `$filtro` que contiene las claves necesarias para la cláusula SQL.
     * @param array $filtro El array asociativo que define el filtro. Debe contener las claves:
     *                      `$filtro[$campo_filtro]['operador']` y `$filtro[$campo_filtro]['valor']`.
     *
     * @return string|array Retorna una cadena con la cláusula SQL generada si los datos son válidos.
     *                      En caso de error, retorna un array con los detalles del error.
     *
     * @throws errores Si los datos de entrada no cumplen con las validaciones, retorna un array con detalles del error.
     *
     * @example Uso exitoso:
     * ```php
     * $campo = 'precio';
     * $campo_filtro = 'filtro_precio';
     * $filtro = [
     *     'filtro_precio' => [
     *         'operador' => '=',
     *         'valor' => 100
     *     ]
     * ];
     *
     * $resultado = $this->data_sql_campo(campo: $campo, campo_filtro: $campo_filtro, filtro: $filtro);
     * // Resultado esperado: "'precio'=100"
     * ```
     *
     * @example Error por datos incompletos:
     * ```php
     * $campo = 'precio';
     * $campo_filtro = 'filtro_precio';
     * $filtro = [
     *     'filtro_precio' => [
     *         'operador' => '',
     *         'valor' => 100
     *     ]
     * ];
     *
     * $resultado = $this->data_sql_campo(campo: $campo, campo_filtro: $campo_filtro, filtro: $filtro);
     * // Resultado esperado: Array indicando que el operador está vacío.
     * ```
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
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.datos_filtro_especial
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
     * REG
     * Determina si un campo específico está presente en las columnas adicionales (subqueries) proporcionadas.
     *
     * @param string $campo Nombre del campo a evaluar. No puede ser una cadena vacía.
     * @param array $columnas_extra Array asociativo que contiene las columnas adicionales, donde las claves representan los campos.
     *
     * @return bool|array Devuelve `true` si el campo existe en `$columnas_extra`, `false` en caso contrario.
     * Si el parámetro `$campo` está vacío, devuelve un array con los detalles del error.
     *
     * @throws errores Si:
     * - `$campo` está vacío.
     *
     * ### Ejemplos de uso:
     *
     * 1. **Caso exitoso: El campo es un subquery**:
     *    ```php
     *    $campo = 'total';
     *    $columnas_extra = [
     *        'total' => 'SUM(valor)',
     *        'promedio' => 'AVG(valor)',
     *    ];
     *    $resultado = $modelo->es_subquery(campo: $campo, columnas_extra: $columnas_extra);
     *    // Resultado esperado: true
     *    ```
     *
     * 2. **Caso exitoso: El campo no es un subquery**:
     *    ```php
     *    $campo = 'cantidad';
     *    $columnas_extra = [
     *        'total' => 'SUM(valor)',
     *        'promedio' => 'AVG(valor)',
     *    ];
     *    $resultado = $modelo->es_subquery(campo: $campo, columnas_extra: $columnas_extra);
     *    // Resultado esperado: false
     *    ```
     *
     * 3. **Error: Campo vacío**:
     *    ```php
     *    $campo = '';
     *    $columnas_extra = [
     *        'total' => 'SUM(valor)',
     *        'promedio' => 'AVG(valor)',
     *    ];
     *    $resultado = $modelo->es_subquery(campo: $campo, columnas_extra: $columnas_extra);
     *    // Resultado esperado: Array con el mensaje "Error campo esta vacio".
     *    ```
     *
     * ### Proceso de la función:
     * 1. Valida que `$campo` no sea una cadena vacía.
     * 2. Inicializa `$es_subquery` como `false`.
     * 3. Verifica si `$campo` existe como clave en `$columnas_extra`.
     *    - Si existe, establece `$es_subquery` como `true`.
     * 4. Devuelve el valor de `$es_subquery`.
     *
     * ### Resultado esperado:
     * - **Éxito**:
     *   - Devuelve `true` si el campo está en `$columnas_extra`.
     *   - Devuelve `false` si el campo no está en `$columnas_extra`.
     * - **Error**: Devuelve un array con detalles del error si `$campo` está vacío.
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
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.filtro_especial_sql
     */
    final public function filtro_especial_sql(array $columnas_extra, array $filtro_especial):array|string
    {

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
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.filtro_extra_sql
     *
     */
    final public function filtro_extra_sql(array $filtro_extra):array|string
    {
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
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.filtro_extra_sql_genera
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
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.filtro_fecha
     */
    final public function filtro_fecha(array $filtro_fecha):array|string
    {
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
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.filtro_fecha_base
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
     * REG
     * Genera una cláusula SQL que combina múltiples filtros de rango mediante la iteración
     * sobre el arreglo `$filtro_rango`. Cada entrada en el arreglo se interpreta como un rango
     * con las claves `valor1` y `valor2`, que definen los límites de cada filtro.
     *
     * Pasos principales:
     * 1. **Validación del formato de los filtros**:
     *    - Cada entrada en `$filtro_rango` debe ser un array.
     *    - Cada filtro debe incluir obligatoriamente las claves `valor1` y `valor2`.
     *    - La clave del filtro (`$campo`) debe ser un string no numérico.
     * 2. **Generación de la cláusula SQL**:
     *    - Para cada filtro, se llama a {@see genera_filtro_rango_base()} para generar y
     *      concatenar la condición de rango al resultado acumulado en `$filtro_rango_sql`.
     * 3. **Compatibilidad con valores textuales**:
     *    - Si un filtro incluye la clave `valor_campo` como `true`, se procesa sin comillas
     *      alrededor de los valores del rango.
     * 4. **Manejo de errores**:
     *    - Si alguna validación falla, se genera un error detallado mediante `$this->error->error()`.
     *
     * @param array $filtro_rango Arreglo asociativo donde las claves representan los campos,
     *                            y los valores son arrays con las claves `valor1`, `valor2` y
     *                            opcionalmente `valor_campo` (bool).
     *
     * @return array|string Retorna:
     *   - Un string con la cláusula SQL de rango generada.
     *   - Un arreglo con información de error si alguna validación falla.
     *
     * @example
     *  Ejemplo 1: Generar filtros de rango para múltiples campos
     *  -----------------------------------------------------------------------------
     *  $filtro_rango = [
     *      'fecha_creacion' => [
     *          'valor1' => '2023-01-01',
     *          'valor2' => '2023-12-31'
     *      ],
     *      'precio' => [
     *          'valor1' => 100,
     *          'valor2' => 500,
     *          'valor_campo' => true
     *      ]
     *  ];
     *
     *  $resultado = $this->filtro_rango_sql($filtro_rango);
     *  // Retorna algo como:
     *  // "fecha_creacion BETWEEN '2023-01-01' AND '2023-12-31' AND precio BETWEEN 100 AND 500"
     *
     * @example
     *  Ejemplo 2: Error por falta de valor2 en un filtro
     *  -----------------------------------------------------------------------------
     *  $filtro_rango = [
     *      'fecha_creacion' => [
     *          'valor1' => '2023-01-01'
     *          // Falta 'valor2'
     *      ]
     *  ];
     *
     *  $resultado = $this->filtro_rango_sql($filtro_rango);
     *  // Retorna un arreglo de error indicando que falta 'valor2'.
     */
    final public function filtro_rango_sql(array $filtro_rango): array|string
    {
        $filtro_rango_sql = '';
        foreach ($filtro_rango as $campo => $filtro) {
            // Validar que cada filtro sea un array
            if (!is_array($filtro)) {
                return $this->error->error(
                    mensaje: 'Error $filtro debe ser un array',
                    data: $filtro,
                    es_final: true
                );
            }

            // Verificar existencia de las claves 'valor1' y 'valor2' en cada filtro
            if (!isset($filtro['valor1'])) {
                return $this->error->error(
                    mensaje: 'Error $filtro[valor1] debe existir',
                    data: $filtro,
                    es_final: true
                );
            }

            if (!isset($filtro['valor2'])) {
                return $this->error->error(
                    mensaje: 'Error $filtro[valor2] debe existir',
                    data: $filtro,
                    es_final: true
                );
            }

            // Validar que la clave del campo sea un string no numérico
            $campo = trim($campo);
            if (is_numeric($campo)) {
                return $this->error->error(
                    mensaje: 'Error campo debe ser un string',
                    data: $campo,
                    es_final: true
                );
            }

            // Determinar si los valores se interpretan como texto o no
            $valor_campo = isset($filtro['valor_campo']) && $filtro['valor_campo'];

            // Generar la condición SQL para este campo y filtro
            $filtro_rango_sql = $this->genera_filtro_rango_base(
                campo: $campo,
                filtro: $filtro,
                filtro_rango_sql: $filtro_rango_sql,
                valor_campo: $valor_campo
            );

            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error $filtro_rango_sql al generar',
                    data: $filtro_rango_sql
                );
            }
        }

        return $filtro_rango_sql;
    }


    /**
     * REG
     * Genera una cláusula SQL con operadores lógicos (`AND`, `OR`) y condiciones de comparación, basándose en un conjunto de filtros.
     *
     * Este método:
     * 1. **Validación de claves**:
     *    - Cada clave en `$filtro` debe ser un campo asociativo en formato `tabla.campo`. No se permiten claves numéricas.
     * 2. **Construcción de la cláusula**:
     *    - Para cada filtro:
     *      - Genera el campo y el valor utilizando {@see comparacion_pura()}.
     *      - Obtiene el operador de comparación (por ejemplo, `'='`) utilizando {@see comparacion()}.
     *      - Valida y utiliza el operador lógico (`AND` o `OR`).
     *    - Concatena las condiciones en una cláusula SQL.
     * 3. **Errores**:
     *    - Si alguna validación falla, se retorna un arreglo con los detalles del error.
     *
     * @param array $columnas_extra Columnas adicionales que pueden sobrescribir los valores de campo.
     * @param array $filtro         Filtros que definen las condiciones de comparación.
     *                              Cada entrada debe tener:
     *                              - Clave: El nombre del campo (por ejemplo, `tabla.campo`).
     *                              - Valor: Un array con posibles claves:
     *                                  - `'value'`: El valor a comparar.
     *                                  - `'comparacion'`: El operador de comparación (por defecto `'='`).
     *                                  - `'operador'`: Operador lógico para unir condiciones (`AND`, `OR`).
     *
     * @return array|string Retorna:
     *  - Un string con la cláusula SQL generada.
     *  - Un arreglo de error si alguna validación falla.
     *
     * @example
     *  Ejemplo 1: Generar una cláusula AND
     *  -----------------------------------------------------------------------------
     *  $columnas_extra = ['usuario_id' => 'tabla.usuario_id'];
     *  $filtro = [
     *      'tabla.usuario_id' => ['value' => 123, 'comparacion' => '=', 'operador' => 'AND'],
     *      'tabla.status' => ['value' => 'activo', 'comparacion' => '=', 'operador' => 'AND']
     *  ];
     *
     *  $resultado = $this->genera_and($columnas_extra, $filtro);
     *  // Retorna: "tabla.usuario_id = '123' AND tabla.status = 'activo'"
     *
     * @example
     *  Ejemplo 2: Error por clave numérica
     *  -----------------------------------------------------------------------------
     *  $columnas_extra = [];
     *  $filtro = [
     *      0 => ['value' => 123, 'comparacion' => '=']
     *  ];
     *
     *  $resultado = $this->genera_and($columnas_extra, $filtro);
     *  // Retorna un arreglo de error indicando que las claves deben ser campos asociativos.
     *
     * @example
     *  Ejemplo 3: Uso del operador OR
     *  -----------------------------------------------------------------------------
     *  $columnas_extra = [];
     *  $filtro = [
     *      'tabla.usuario_id' => ['value' => 123, 'comparacion' => '=', 'operador' => 'OR'],
     *      'tabla.status' => ['value' => 'activo', 'comparacion' => '=', 'operador' => 'OR']
     *  ];
     *
     *  $resultado = $this->genera_and($columnas_extra, $filtro);
     *  // Retorna: "tabla.usuario_id = '123' OR tabla.status = 'activo'"
     */
    final public function genera_and(array $columnas_extra, array $filtro): array|string
    {
        $sentencia = '';

        foreach ($filtro as $key => $data) {
            // Validar que las claves sean asociativas
            if (is_numeric($key)) {
                return $this->error->error(
                    mensaje: 'Los key deben de ser campos asociativos con referencia a tabla.campo',
                    data: $filtro,
                    es_final: true
                );
            }

            // Generar el campo y valor de comparación
            $data_comparacion = $this->comparacion_pura(columnas_extra: $columnas_extra, data: $data, key: $key);
            if (errores::$error) {
                return $this->error->error(
                    mensaje: "Error al maquetar campo",
                    data: $data_comparacion
                );
            }

            // Determinar el operador de comparación
            $comparacion = $this->comparacion(data: $data, default: '=');
            if (errores::$error) {
                return $this->error->error(
                    mensaje: "Error al maquetar",
                    data: $comparacion
                );
            }

            // Validar y obtener el operador lógico
            $operador = $data['operador'] ?? ' AND ';
            if (trim($operador) !== 'AND' && trim($operador) !== 'OR') {
                return $this->error->error(
                    mensaje: 'El operador debe ser AND u OR',
                    data: $operador,
                    es_final: true
                );
            }

            // Construir la sentencia SQL
            $data_sql = "$data_comparacion->campo $comparacion '$data_comparacion->value'";
            $sentencia .= $sentencia === '' ? $data_sql : " $operador $data_sql";
        }

        return $sentencia;
    }




    /**
     * REG
     * Genera una cláusula SQL con operadores lógicos (`AND`, `OR`) y condiciones de comparación basadas en textos.
     *
     * Este método:
     * 1. **Validación de claves**:
     *    - Cada clave en `$filtro` debe ser un campo asociativo en formato `tabla.campo`. No se permiten claves numéricas.
     * 2. **Construcción de la cláusula**:
     *    - Para cada filtro:
     *      - Genera el campo y el valor utilizando {@see comparacion_pura()}.
     *      - Obtiene el operador de comparación (por defecto `'LIKE'`) utilizando {@see comparacion()}.
     *      - Aplica el operador lógico (`AND` o `OR`) y agrega el valor entre porcentajes (`%`), excepto cuando se especifica un operador diferente.
     * 3. **Errores**:
     *    - Si alguna validación falla, se retorna un arreglo con los detalles del error.
     *
     * @param array $columnas_extra Columnas adicionales que pueden sobrescribir los valores de campo.
     * @param array $filtro         Filtros que definen las condiciones de comparación.
     *                              Cada entrada debe tener:
     *                              - Clave: El nombre del campo (por ejemplo, `tabla.campo`).
     *                              - Valor: Un array con posibles claves:
     *                                  - `'value'`: El valor a comparar.
     *                                  - `'comparacion'`: El operador de comparación (por defecto `'LIKE'`).
     *                                  - `'operador'`: Operador lógico para unir condiciones (`AND`, `OR`).
     *
     * @return array|string Retorna:
     *  - Un string con la cláusula SQL generada.
     *  - Un arreglo de error si alguna validación falla.
     *
     * @example
     *  Ejemplo 1: Generar cláusula con `LIKE` y operador `AND`
     *  -----------------------------------------------------------------------------
     *  $columnas_extra = ['usuario_nombre' => 'tabla.usuario_nombre'];
     *  $filtro = [
     *      'tabla.usuario_nombre' => ['value' => 'Juan', 'comparacion' => 'LIKE', 'operador' => 'AND'],
     *      'tabla.status' => ['value' => 'activo', 'comparacion' => 'LIKE', 'operador' => 'AND']
     *  ];
     *
     *  $resultado = $this->genera_and_textos($columnas_extra, $filtro);
     *  // Retorna: "tabla.usuario_nombre LIKE '%Juan%' AND tabla.status LIKE '%activo%'"
     *
     * @example
     *  Ejemplo 2: Uso de operador `OR`
     *  -----------------------------------------------------------------------------
     *  $columnas_extra = [];
     *  $filtro = [
     *      'tabla.usuario_nombre' => ['value' => 'Juan', 'comparacion' => 'LIKE', 'operador' => 'OR'],
     *      'tabla.status' => ['value' => 'activo', 'comparacion' => 'LIKE', 'operador' => 'OR']
     *  ];
     *
     *  $resultado = $this->genera_and_textos($columnas_extra, $filtro);
     *  // Retorna: "tabla.usuario_nombre LIKE '%Juan%' OR tabla.status LIKE '%activo%'"
     *
     * @example
     *  Ejemplo 3: Error por clave numérica
     *  -----------------------------------------------------------------------------
     *  $columnas_extra = [];
     *  $filtro = [
     *      0 => ['value' => 'Juan', 'comparacion' => 'LIKE']
     *  ];
     *
     *  $resultado = $this->genera_and_textos($columnas_extra, $filtro);
     *  // Retorna un arreglo de error indicando que las claves deben ser campos asociativos.
     */
    private function genera_and_textos(array $columnas_extra, array $filtro): array|string
    {
        $sentencia = '';

        foreach ($filtro as $key => $data) {
            // Validar que las claves sean asociativas
            if (is_numeric($key)) {
                return $this->error->error(
                    mensaje: 'Los key deben de ser campos asociativos con referencia a tabla.campo',
                    data: $filtro,
                    es_final: true
                );
            }

            // Generar el campo y valor de comparación
            $data_comparacion = $this->comparacion_pura(columnas_extra: $columnas_extra, data: $data, key: $key);
            if (errores::$error) {
                return $this->error->error(
                    mensaje: "Error al maquetar",
                    data: $data_comparacion
                );
            }

            // Determinar el operador de comparación
            $comparacion = $this->comparacion(data: $data, default: 'LIKE');
            if (errores::$error) {
                return $this->error->error(
                    mensaje: "Error al maquetar",
                    data: $comparacion
                );
            }

            // Determinar el operador lógico y formato del texto
            $txt = '%';
            $operador = 'AND';
            if (isset($data['operador']) && $data['operador'] !== '') {
                $operador = $data['operador'];
                $txt = '';
            }

            // Construir la sentencia SQL
            $sentencia .= $sentencia === ""
                ? "$data_comparacion->campo $comparacion '$txt$data_comparacion->value$txt'"
                : " $operador $data_comparacion->campo $comparacion '$txt$data_comparacion->value$txt'";
        }

        return $sentencia;
    }


    /**
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.genera_filtro_especial
     */
    final public function genera_filtro_especial(
        string $campo, string $data_sql, array $filtro_esp, string $filtro_especial_sql):array|string
    {
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
     * REG
     * Genera y ajusta una cláusula de filtro de rango SQL basada en un campo específico,
     * valores límite proporcionados y un filtro de rango SQL existente.
     *
     * Flujo del método:
     * 1. **Validación del campo**: Verifica que `$campo` no sea una cadena vacía.
     * 2. **Verificación de claves en el filtro**: Asegura que en el arreglo `$filtro` existan
     *    las claves `'valor1'` y `'valor2'`, necesarias para definir un rango.
     * 3. **Construcción de la condición BETWEEN**:
     *    Utiliza {@see condicion_entre()} para generar una condición SQL que defina un
     *    rango basado en `$campo` y los valores en `$filtro`. El parámetro `$valor_campo`
     *    determina cómo se formatea la condición.
     * 4. **Integración de la condición en el filtro de rango SQL**:
     *    Llama a {@see setea_filtro_rango()} para añadir la condición generada a la cadena
     *    `$filtro_rango_sql`, precedida de `" AND "` si es necesario.
     *
     * En cada paso, si ocurre un error (por ejemplo, validaciones fallidas o problemas al
     * generar la condición), el método retorna un arreglo de error con detalles del problema.
     * Si todo es exitoso, retorna la cadena ajustada `$filtro_rango_sql_r` con la nueva
     * condición integrada.
     *
     * @param string $campo              Nombre de la columna sobre la cual se aplica el filtro de rango.
     * @param array  $filtro             Arreglo que debe contener las claves 'valor1' y 'valor2'
     *                                  para definir los límites del rango.
     * @param string $filtro_rango_sql   Cadena SQL inicial que representa los filtros de rango
     *                                  previos y a la cual se le añadirá una nueva condición.
     * @param bool   $valor_campo        (Opcional) Indica cómo se construye la condición BETWEEN:
     *                                   - `false` (por defecto): aplica comillas a los valores.
     *                                   - `true`: usa `$campo` y valores textuales sin comillas.
     *
     * @return array|string Retorna:
     *   - Un `string` con el filtro de rango SQL actualizado si todo se procesa correctamente.
     *   - Un `array` de error con detalles si ocurre alguna falla en el proceso.
     *
     * @example
     *  Ejemplo: Generar un filtro de rango para fechas
     *  ----------------------------------------------------------------------------
     *  $campo = "fecha_creacion";
     *  $filtro = [
     *      'valor1' => '2023-01-01',
     *      'valor2' => '2023-12-31'
     *  ];
     *  $filtro_rango_sql = "WHERE estado = 'activo'";
     *
     *  $resultado = $this->genera_filtro_rango_base($campo, $filtro, $filtro_rango_sql);
     *  // Supongamos que no hay errores y $valor_campo es false por defecto.
     *  // $resultado podría convertirse en:
     *  // "WHERE estado = 'activo' AND fecha_creacion BETWEEN '2023-01-01' AND '2023-12-31'"
     */
    final public function genera_filtro_rango_base(
        string $campo,
        array $filtro,
        string $filtro_rango_sql,
        bool $valor_campo = false
    ): array|string
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(
                mensaje: 'Error $campo no puede venir vacio',
                data: $campo,
                es_final: true
            );
        }

        $keys = array('valor1','valor2');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $filtro);
        if(errores::$error){
            return $this->error->error(
                mensaje: 'Error al validar filtro',
                data: $valida
            );
        }

        $condicion = $this->condicion_entre(
            campo: $campo,
            filtro:  $filtro,
            valor_campo:  $valor_campo
        );
        if(errores::$error){
            return $this->error->error(
                mensaje: 'Error al generar condicion',
                data: $condicion
            );
        }

        $filtro_rango_sql_r = $this->setea_filtro_rango(
            condicion: $condicion,
            filtro_rango_sql: $filtro_rango_sql
        );
        if(errores::$error){
            return $this->error->error(
                mensaje: 'Error $filtro_rango_sql al setear',
                data: $filtro_rango_sql_r
            );
        }

        return $filtro_rango_sql_r;
    }


    /**
     * REG
     * Genera una cláusula SQL `IN` (por ejemplo, `"campo IN ('valor1','valor2',...')"`), a partir de un arreglo `$in`
     * que debe contener al menos:
     *  - `'llave'`  (string): Nombre de la columna.
     *  - `'values'` (array):  Lista de valores a incluir en la cláusula IN.
     *
     * Flujo de validación y construcción:
     * 1. **Verifica la existencia de las claves** `'llave'` y `'values'` en `$in` mediante
     *    {@see validacion->valida_existencia_keys()}.
     * 2. **Obtiene un objeto** (`stdClass`) con `llave` y `values` usando {@see data_in()}, validando que `'values'` sea un array.
     * 3. **Construye la cláusula IN** con el método {@see in_sql()}.
     * 4. Si ocurre algún error en los pasos anteriores, se retorna un arreglo generado por `$this->error->error()`
     *    con la descripción del problema. De lo contrario, devuelve la cadena final de la forma
     *    `"llave IN ('val1','val2',...)"`
     *
     * @param array $in Estructura que contiene al menos `'llave'` y `'values'`:
     *                  - `'llave'`:  Nombre de la columna para la cláusula IN.
     *                  - `'values'`: Array con los valores que formarán parte del IN.
     *
     * @return array|string Retorna:
     *  - El string con la cláusula IN (p.ej. `"categoria_id IN ('10','20','30')"`),
     *  - o un arreglo con información del error si alguna validación falla.
     *
     * @example
     *  Ejemplo 1: Generar IN con datos válidos
     *  --------------------------------------------------------------------------------------
     *  $in = [
     *      'llave'  => 'categoria_id',
     *      'values' => ['10', '20', '30']
     *  ];
     *
     *  // Flujo:
     *  //  1. Se verifica que existan 'llave' y 'values'.
     *  //  2. data_in() valida que 'values' sea un array y retorna un stdClass con llaves y valores.
     *  //  3. in_sql() genera algo como "categoria_id IN ('10','20','30')".
     *  // Si todo va bien, se retorna la cadena.
     *
     *  $resultado = $this->genera_in($in);
     *  // $resultado: "categoria_id IN ('10','20','30')"
     *
     * @example
     *  Ejemplo 2: Falta la clave 'values'
     *  --------------------------------------------------------------------------------------
     *  $in = [
     *      'llave' => 'categoria_id'
     *      // falta 'values'
     *  ];
     *
     *  $resultado = $this->genera_in($in);
     *  // Retornará un arreglo de error indicando que 'values' no existe.
     *
     * @example
     *  Ejemplo 3: 'values' no es un array
     *  --------------------------------------------------------------------------------------
     *  $in = [
     *      'llave'  => 'categoria_id',
     *      'values' => 'no_es_array'
     *  ];
     *
     *  $resultado = $this->genera_in($in);
     *  // Retorna un arreglo de error indicando "Error values debe ser un array".
     */
    final public function genera_in(array $in): array|string
    {
        // 1. Verifica que existan las claves 'llave' y 'values'
        $keys = ['llave','values'];
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $in);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar not_in',
                data: $valida
            );
        }

        // 2. Obtén un stdClass con ->llave y ->values, validando que values sea un array
        $data_in = $this->data_in(in: $in);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar data in',
                data: $data_in
            );
        }

        // 3. Construye la cláusula IN con llave y values
        $in_sql = $this->in_sql(llave: $data_in->llave, values: $data_in->values);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar sql',
                data: $in_sql
            );
        }

        return $in_sql;
    }


    /**
     * REG
     * Genera una sentencia SQL basada en un conjunto de filtros y un tipo de filtro especificado.
     *
     * Este método:
     * 1. **Valida el tipo de filtro**:
     *    - Llama a {@see verifica_tipo_filtro()} para asegurar que `$tipo_filtro` sea válido (`numeros` o `textos`).
     * 2. **Construcción de la cláusula SQL**:
     *    - Si `$tipo_filtro` es `'numeros'`, llama a {@see genera_and()} para generar una cláusula con condiciones basadas en números.
     *    - Si `$tipo_filtro` es `'textos'`, llama a {@see genera_and_textos()} para generar una cláusula con condiciones basadas en textos.
     * 3. **Errores**:
     *    - Si falla alguna validación o generación, se retorna un arreglo con los detalles del error.
     *
     * @param array  $columnas_extra Columnas adicionales que pueden sobrescribir los valores de campo.
     * @param array  $filtro         Filtros que definen las condiciones de comparación.
     * @param string $tipo_filtro    Tipo de filtro a aplicar (`numeros` o `textos`).
     *
     * @return array|string Retorna:
     *  - Un string con la sentencia SQL generada.
     *  - Un arreglo de error si alguna validación falla.
     *
     * @example
     *  Ejemplo 1: Generar sentencia con tipo de filtro "numeros"
     *  -----------------------------------------------------------------------------
     *  $columnas_extra = ['id' => 'tabla.id'];
     *  $filtro = [
     *      'tabla.id' => ['value' => 123, 'comparacion' => '=', 'operador' => 'AND'],
     *      'tabla.status' => ['value' => 1, 'comparacion' => '=', 'operador' => 'AND']
     *  ];
     *  $tipo_filtro = 'numeros';
     *
     *  $resultado = $this->genera_sentencia_base($columnas_extra, $filtro, $tipo_filtro);
     *  // Retorna: "tabla.id = '123' AND tabla.status = '1'"
     *
     * @example
     *  Ejemplo 2: Generar sentencia con tipo de filtro "textos"
     *  -----------------------------------------------------------------------------
     *  $columnas_extra = [];
     *  $filtro = [
     *      'tabla.nombre' => ['value' => 'Juan', 'comparacion' => 'LIKE', 'operador' => 'AND'],
     *      'tabla.status' => ['value' => 'activo', 'comparacion' => 'LIKE', 'operador' => 'AND']
     *  ];
     *  $tipo_filtro = 'textos';
     *
     *  $resultado = $this->genera_sentencia_base($columnas_extra, $filtro, $tipo_filtro);
     *  // Retorna: "tabla.nombre LIKE '%Juan%' AND tabla.status LIKE '%activo%'"
     *
     * @example
     *  Ejemplo 3: Error en tipo de filtro
     *  -----------------------------------------------------------------------------
     *  $columnas_extra = [];
     *  $filtro = [];
     *  $tipo_filtro = 'invalido';
     *
     *  $resultado = $this->genera_sentencia_base($columnas_extra, $filtro, $tipo_filtro);
     *  // Retorna un arreglo de error indicando que el tipo de filtro no es válido.
     */
    final public function genera_sentencia_base(array $columnas_extra, array $filtro, string $tipo_filtro): array|string
    {
        // Validar el tipo de filtro
        $verifica_tf = $this->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar tipo_filtro',
                data: $verifica_tf
            );
        }

        $sentencia = '';

        // Generar sentencia SQL según el tipo de filtro
        if ($tipo_filtro === 'numeros') {
            $sentencia = $this->genera_and(columnas_extra: $columnas_extra, filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(
                    mensaje: "Error en and",
                    data: $sentencia
                );
            }
        } elseif ($tipo_filtro === 'textos') {
            $sentencia = $this->genera_and_textos(columnas_extra: $columnas_extra, filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(
                    mensaje: "Error en texto",
                    data: $sentencia
                );
            }
        }

        return $sentencia;
    }


    /**
     * REG
     * Construye una cláusula SQL `IN ( ... )` a partir de:
     *
     * 1. Un nombre de columna (`$llave`), que no debe estar vacío.
     * 2. Un arreglo de valores (`$values`) que se convertirán en una cadena con comillas simples
     *    y comas (por ejemplo: `"'valor1','valor2'"`).
     *
     * - Primero, valida que `$llave` no sea una cadena vacía.
     * - Luego, convierte `$values` a un string SQL adecuado mediante `values_sql_in($values)`.
     * - Llama a {@see sql::valida_in()} para verificar la coherencia entre `$llave` y la cadena de valores.
     * - Finalmente, construye la cláusula IN con {@see sql::in()}, devolviendo algo como:
     *   `"$llave IN ('valor1','valor2',...)"`
     *
     * Si se presenta algún error en la validación de la llave, la generación de la cadena de valores o la
     * construcción de la cláusula IN, se retornará un arreglo describiendo el error, generado por
     * `$this->error->error()`.
     *
     * @param string $llave  Nombre de la columna para la cláusula IN (no debe estar vacío).
     * @param array  $values Lista de valores que se convertirán a una cadena SQL.
     *
     * @return array|string  Retorna la cláusula IN en forma de cadena si todo es correcto, o un arreglo
     *                       con la información del error en caso contrario.
     *
     * @example
     *  Ejemplo 1: Uso con datos válidos
     *  ------------------------------------------------------------------------------------
     *  $llave  = "categoria_id";
     *  $values = ["10", "20", "30"];
     *
     *  $resultado = $this->in_sql($llave, $values);
     *  // Suponiendo que values_sql_in() genera "'10','20','30'",
     *  // $resultado podría ser: "categoria_id IN ('10','20','30')".
     *
     * @example
     *  Ejemplo 2: Llave vacía
     *  ------------------------------------------------------------------------------------
     *  $llave  = "";
     *  $values = ["abc"];
     *
     *  // Se detecta que la llave está vacía, se retorna un arreglo de error con el mensaje
     *  // "Error la llave esta vacia".
     *  $resultado = $this->in_sql($llave, $values);
     *
     * @example
     *  Ejemplo 3: Sin valores en el arreglo
     *  ------------------------------------------------------------------------------------
     *  $llave  = "usuario_id";
     *  $values = [];
     *
     *  // Es válido, pero resultará en la cadena de values_sql_in() vacía.
     *  // Luego, valida_in() detectará que si la llave tiene contenido,
     *  // $values_sql no debe estar vacío (error).
     *  $resultado = $this->in_sql($llave, $values);
     *  // Se retorna un arreglo describiendo el error.
     */
    private function in_sql(string $llave, array $values): array|string
    {
        // 1. Validar que la llave no esté vacía
        $llave = trim($llave);
        if ($llave === '') {
            return $this->error->error(
                mensaje: 'Error la llave esta vacia',
                data: $llave,
                es_final: true
            );
        }

        // 2. Generar la cadena SQL de valores (ej. "'10','20','30'")
        $values_sql = $this->values_sql_in(values: $values);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar sql',
                data: $values_sql
            );
        }

        // 3. Validar coherencia entre llave y cadena de valores
        $valida = (new sql())->valida_in(llave: $llave, values_sql: $values_sql);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar in',
                data: $valida
            );
        }

        // 4. Construir la cláusula IN
        $in_sql = (new sql())->in(llave: $llave, values_sql: $values_sql);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar sql',
                data: $in_sql
            );
        }

        return $in_sql;
    }


    /**
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.integra_filtro_extra
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
     * REG
     * Ajusta la cadena `$filtro_rango_sql` para añadirle la `$condicion` especificada, separada por `" AND "` si
     * `$filtro_rango_sql` no está vacío. Además, valida coherencia entre ambos parámetros:
     *
     * - Si `$filtro_rango_sql` tiene contenido y `$condicion` está vacío, se considera un error,
     *   pues no se puede tener un filtro sin condición.
     * - En caso contrario, si `$filtro_rango_sql` no está vacío, se antepone `" AND "` a la `$condicion`
     *   mediante el método {@see and_filtro_fecha()}.
     * - Finalmente, concatena la `$condicion` resultante al final de `$filtro_rango_sql`.
     *
     * @param string $condicion        Condición que se desea agregar (por ejemplo, `"fecha >= '2020-01-01'"`).
     * @param string $filtro_rango_sql Cadena existente a la cual se le añadirá la condición.
     *                                 Puede estar vacía o contener filtros previos.
     *
     * @return array|string Retorna:
     *  - Un `string` con `$filtro_rango_sql` concatenado a `$condicion`, separado por `" AND "` si corresponde.
     *  - Un `array` describiendo un error si se detecta incoherencia (por ejemplo, `$filtro_rango_sql` tiene info
     *    pero `$condicion` está vacío).
     *
     * @example
     *  Ejemplo 1: `$filtro_rango_sql` vacío, `$condicion` con valor
     *  ----------------------------------------------------------------------------------
     *  $filtroRango = "";
     *  $condicion   = "fecha >= '2023-01-01'";
     *
     *  // $filtroRango no tiene contenido, así que no se agrega " AND ".
     *  // El resultado final es "fecha >= '2023-01-01'".
     *  $resultado = $this->setea_filtro_rango($condicion, $filtroRango);
     *  // $resultado => "fecha >= '2023-01-01'"
     *
     * @example
     *  Ejemplo 2: `$filtro_rango_sql` con valor, `$condicion` no vacío
     *  ----------------------------------------------------------------------------------
     *  $filtroRango = "id_cliente = 100";
     *  $condicion   = "fecha >= '2023-01-01'";
     *
     *  // Dado que $filtroRango tiene info, se antepone " AND " a la $condicion
     *  // Resultado: "id_cliente = 100 AND fecha >= '2023-01-01'"
     *  $resultado = $this->setea_filtro_rango($condicion, $filtroRango);
     *
     * @example
     *  Ejemplo 3: `$filtro_rango_sql` con contenido, `$condicion` vacío
     *  ----------------------------------------------------------------------------------
     *  $filtroRango = "id_cliente = 100";
     *  $condicion   = "";
     *
     *  // Retorna un arreglo de error, pues no se permite tener un filtroRango con contenido
     *  // y condición vacía.
     *  $resultado = $this->setea_filtro_rango($condicion, $filtroRango);
     *  // $resultado => [
     *  //    'error'   => 1,
     *  //    'mensaje' => "Error if filtro_rango tiene info $condicion no puede venir vacio",
     *  //    'data'    => "id_cliente = 100",
     *  //    ...
     *  // ]
     */
    private function setea_filtro_rango(string $condicion, string $filtro_rango_sql): array|string
    {
        $filtro_rango_sql = trim($filtro_rango_sql);
        $condicion = trim($condicion);

        // Verifica que si hay información en $filtro_rango_sql, la condición no esté vacía
        if ($filtro_rango_sql !== '' && $condicion === '') {
            return $this->error->error(
                mensaje: 'Error if filtro_rango tiene info $condicion no puede venir vacio',
                data: $filtro_rango_sql,
                es_final: true
            );
        }

        // Generar posible " AND " entre $filtro_rango_sql y la nueva condición
        $and = $this->and_filtro_fecha(txt: $filtro_rango_sql);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'error al integrar and',
                data: $and
            );
        }

        // Concatena la condición con " AND " si corresponde
        $filtro_rango_sql .= $and . $condicion;

        return $filtro_rango_sql;
    }


    /**
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.sql_fecha
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
     * TOTAL
     * Método privado que genera una cláusula NOT IN SQL a partir de un arreglo proporcionado.
     *
     * @param array $not_in Arreglo de elementos a ser excluidos en la consulta SQL.
     *
     * @return array|string Regresa la cláusula NOT IN SQL generada o un mensaje de error en caso de un error
     * detectado en la generación de la cláusula.
     *
     * @throws errores Lanza una excepción de tipo Error en caso de error en la generación de la cláusula SQL.
     * @version 16.276.1
     * @url https://github.com/gamboamartin/where/wiki/src.where.genera_not_in
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
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.genera_not_in_sql
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
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.genera_sql_filtro_fecha
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
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.maqueta_filtro_especial
     */
    private function maqueta_filtro_especial(string $campo, array $columnas_extra, array $filtro):array|string
    {
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
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.not_in_sql
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
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.obten_filtro_especial
     *
     */

    private function obten_filtro_especial(
        array $columnas_extra, array $filtro_esp, string $filtro_especial_sql):array|string
    {
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
     * REG
     * Valida la estructura y contenido de un filtro aplicado a un campo en SQL.
     * Verifica que los datos en el filtro estén completos, correctos y definidos
     * según las claves requeridas (`operador` y `valor`).
     *
     * @param string $campo El nombre del campo al que se aplicará el filtro. No debe estar vacío.
     * @param string $campo_filtro El identificador del filtro dentro del array `$filtro`. No debe estar vacío.
     * @param array $filtro El array que contiene la configuración del filtro. Debe incluir las claves
     *                      `$filtro[$campo_filtro]['operador']` y `$filtro[$campo_filtro]['valor']`.
     *
     * @return true|array Devuelve `true` si la validación es exitosa. Si ocurre un error,
     *                    devuelve un array con los detalles del error.
     *
     * @throws errores Si alguna validación falla, genera un array con detalles del error.
     *
     * @example Uso exitoso:
     * ```php
     * $campo = 'precio';
     * $campo_filtro = 'filtro_precio';
     * $filtro = [
     *     'filtro_precio' => [
     *         'operador' => '>',
     *         'valor' => 100
     *     ]
     * ];
     *
     * $resultado = $objeto->valida_campo_filtro(campo: $campo, campo_filtro: $campo_filtro, filtro: $filtro);
     * // Resultado esperado: true
     * ```
     *
     * @example Filtro inválido:
     * ```php
     * $campo = 'precio';
     * $campo_filtro = 'filtro_precio';
     * $filtro = [
     *     'filtro_precio' => [
     *         'operador' => '',
     *         'valor' => 100
     *     ]
     * ];
     *
     * $resultado = $objeto->valida_campo_filtro(campo: $campo, campo_filtro: $campo_filtro, filtro: $filtro);
     * // Resultado esperado: Array con error indicando que el operador está vacío.
     * ```
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
     * TOTAL
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
     * @url https://github.com/gamboamartin/where/wiki/src.where.valida_data_filtro_fecha
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
     * TOTAL
     * Esta función valida un array de filtro de fecha.
     *
     * @param array $fil_fecha Array que contiene la información del filtro de fecha. Debería de contener los campos 'campo_1', 'campo_2' y 'fecha'.
     *
     * @return bool|array Retorna verdadero si el filtro de fecha es válido. De lo contrario, retorna un Error.
     *
     * @throws errores Posibles errores que pueden ocurrir durante la validación.
     * @version 16.305.1
     * @url https://github.com/gamboamartin/where/wiki/src.where.valida_filtro_fecha
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
     * REG
     * Obtiene y valida un valor desde un array o una cadena, aplicando validaciones específicas y escapando caracteres especiales.
     *
     * - Si `$data` es un array:
     *   - Si contiene la clave `'value'`, se utiliza su valor.
     *   - Si no contiene `'value'`, se genera un error.
     *   - Si está vacío, también se genera un error.
     * - Si `$data` no es un array, se utiliza directamente su valor.
     * - Si el valor resultante es `null`, se convierte en una cadena vacía.
     * - El valor final se retorna con caracteres especiales escapados mediante `addslashes()`.
     *
     * @param array|string|null $data Datos desde los cuales se intentará obtener el valor.
     *
     * @return string|array Retorna:
     *  - Un `string` con el valor obtenido, escapado con `addslashes()`.
     *  - Un arreglo de error si el valor no cumple las validaciones.
     *
     * @example
     *  Ejemplo 1: `$data` como array con clave 'value'
     *  --------------------------------------------------------------------------------
     *  $data = ['value' => "cadena de prueba"];
     *  $resultado = $this->value($data);
     *  // Retorna: "cadena de prueba" (con caracteres especiales escapados si aplica).
     *
     * @example
     *  Ejemplo 2: `$data` como array vacío
     *  --------------------------------------------------------------------------------
     *  $data = [];
     *  $resultado = $this->value($data);
     *  // Retorna un arreglo de error indicando que los datos están vacíos.
     *
     * @example
     *  Ejemplo 3: `$data` como cadena
     *  --------------------------------------------------------------------------------
     *  $data = "texto simple";
     *  $resultado = $this->value($data);
     *  // Retorna: "texto simple" (con caracteres especiales escapados si aplica).
     *
     * @example
     *  Ejemplo 4: `$data` como null
     *  --------------------------------------------------------------------------------
     *  $data = null;
     *  $resultado = $this->value($data);
     *  // Retorna: "" (cadena vacía).
     *
     * @example
     *  Ejemplo 5: `$data` como array sin 'value'
     *  --------------------------------------------------------------------------------
     *  $data = ['otro_dato' => "valor"];
     *  $resultado = $this->value($data);
     *  // Retorna un arreglo de error indicando que no existe la clave 'value'.
     */
    private function value(array|string|null $data): string|array
    {
        $value = $data;

        // Si es un array y contiene la clave 'value', usar ese valor
        if (is_array($data) && isset($data['value'])) {
            $value = trim($data['value']);
        }

        // Validar si el array está vacío
        if (is_array($data) && count($data) === 0) {
            return $this->error->error(
                mensaje: "Error datos vacio",
                data: $data,
                es_final: true
            );
        }

        // Validar si falta la clave 'value' en el array
        if (is_array($data) && !isset($data['value'])) {
            return $this->error->error(
                mensaje: "Error no existe valor",
                data: $data,
                es_final: true
            );
        }

        // Si el valor es null, convertirlo en una cadena vacía
        if (is_null($value)) {
            $value = '';
        }

        // Retornar el valor escapado
        return addslashes($value);
    }


    /**
     * REG
     * Prepara un valor `$value` y determina si debe ir precedido por una coma (`", "`) según el contenido de `$values_sql`.
     *
     * - Si `$value` está vacío tras hacer `trim()`, se retorna un arreglo de error generado por `$this->error->error()`.
     * - En caso contrario, se retorna un objeto `stdClass` con dos propiedades:
     *   - `value`: El valor de `$value` recortado (sin espacios al inicio y fin).
     *   - `coma`: Una cadena que contiene `", "` si `$values_sql` no está vacío, o `""` si sí lo está.
     *
     * @param string $value      Valor que se desea formatear y que no puede ser vacío.
     * @param string $values_sql Cadena previa, que si no está vacía, causará que `coma` sea `", "`.
     *
     * @return array|stdClass Retorna:
     *  - Un `stdClass` con propiedades `value` y `coma` en caso exitoso.
     *  - Un arreglo que describe un error si `$value` está vacío.
     *
     * @example
     *  Ejemplo 1: `$values_sql` está vacío
     *  -----------------------------------------------------------------------------
     *  // Si $values_sql = "" y $value = "nombre", entonces:
     *  $result = $this->value_coma("nombre", "");
     *
     *  // Se retorna un stdClass:
     *  // {
     *  //    value: "nombre",
     *  //    coma:  ""
     *  // }
     *
     * @example
     *  Ejemplo 2: `$values_sql` no está vacío
     *  -----------------------------------------------------------------------------
     *  // Si $values_sql = "id, nombre" y $value = "apellido", entonces:
     *  $result = $this->value_coma("apellido", "id, nombre");
     *
     *  // Se retorna un stdClass:
     *  // {
     *  //    value: "apellido",
     *  //    coma:  " ,"
     *  // }
     *  // indicando que se debe concatenar ", apellido" en la sentencia SQL.
     *
     * @example
     *  Ejemplo 3: `$value` está vacío
     *  -----------------------------------------------------------------------------
     *  // Si $value = "" (tras un trim) se retorna un arreglo de error.
     *  $result = $this->value_coma("", "id, nombre");
     *  // $result será un arreglo con la información del error:
     *  // [
     *  //    'error'       => 1,
     *  //    'mensaje'     => ...,
     *  //    'data'        => "",
     *  //    ...
     *  // ]
     */
    private function value_coma(string $value, string $values_sql): array|stdClass
    {
        $values_sql = trim($values_sql);
        $value = trim($value);
        if ($value === '') {
            return $this->error->error(
                mensaje: 'Error value esta vacio',
                data: $value,
                es_final: true
            );
        }

        $coma = '';
        if ($values_sql !== '') {
            $coma = ' ,';
        }

        $data = new stdClass();
        $data->value = $value;
        $data->coma  = $coma;
        return $data;
    }


    /**
     * REG
     * Construye una cadena de valores para una sentencia SQL `IN(...)` a partir de un arreglo de valores.
     *
     * - Itera sobre cada elemento de `$values`, validando que no sea una cadena vacía tras `trim()`.
     * - Cada valor válido se formatea escapándolo con `addslashes()` y rodeándolo con comillas simples (`'...'`).
     * - Se agrega una coma (`, `) antes del valor si ya hay contenido previo en la cadena `$values_sql`.
     * - Si en algún punto se detecta un valor vacío o se produce un error en la función auxiliar `value_coma()`,
     *   se retorna un arreglo con los detalles del error.
     * - Si todo es correcto, retorna un string adecuado para usarse en una cláusula `IN(...)` de SQL.
     *
     * @param array $values Lista de valores que se convertirán en una cadena de texto para un `IN`.
     *
     * @return string|array Retorna:
     *  - Un `string` con los valores formateados y separados por comas (p. ej. `'valor1','valor2','valor3'`).
     *  - Un `array` de error en caso de encontrar valores vacíos o fallos internos.
     *
     * @example
     *  Ejemplo 1: Lista con valores válidos
     *  -----------------------------------------------------------------------------------
     *  $values = ['apple', 'banana', 'cherry'];
     *  $resultado = $this->values_sql_in($values);
     *  // $resultado podría ser "'apple','banana','cherry'".
     *  // Útil en una sentencia: "SELECT * FROM tabla WHERE columna IN ($resultado)"
     *
     * @example
     *  Ejemplo 2: Algún valor vacío
     *  -----------------------------------------------------------------------------------
     *  $values = ['apple', '', 'cherry'];
     *  // Se detecta que uno de los valores está vacío, se retorna un arreglo de error
     *  $resultado = $this->values_sql_in($values);
     *  // Retornará algo como:
     *  // [
     *  //    'error'   => 1,
     *  //    'mensaje' => 'Error value esta vacio',
     *  //    'data'    => '',
     *  //    ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Aplicar a un WHERE IN
     *  -----------------------------------------------------------------------------------
     *  $values = ['10', '20', '30'];
     *  $inList = $this->values_sql_in($values); // "'10','20','30'"
     *  $sql = "SELECT * FROM productos WHERE id IN ($inList)";
     *  // Resultado:
     *  // SELECT * FROM productos WHERE id IN ('10','20','30')
     */
    final public function values_sql_in(array $values): string|array
    {
        $values_sql = '';
        foreach ($values as $value) {
            $value = trim($value);
            if ($value === '') {
                return $this->error->error(
                    mensaje: 'Error value esta vacio',
                    data: $value,
                    es_final: true
                );
            }

            // Llama a value_coma() para determinar si debe precederse de coma
            $data = $this->value_coma(value: $value, values_sql: $values_sql);
            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error obtener datos de value',
                    data: $data
                );
            }

            // Escapa el valor y lo envuelve en comillas simples
            $value = addslashes($value);
            $value = "'$value'";

            // Concatena coma si corresponde y el valor final
            $values_sql .= "$data->coma$value";
        }

        return $values_sql;
    }


    /**
     * REG
     * Verifica que el valor de `$tipo_filtro` sea válido dentro de un conjunto predefinido de tipos permitidos.
     *
     * - Si `$tipo_filtro` está vacío, se establece automáticamente en `'numeros'`.
     * - Los tipos permitidos son `'numeros'` y `'textos'`.
     * - Si `$tipo_filtro` no coincide con los tipos permitidos, se retorna un error con los detalles.
     * - Si `$tipo_filtro` es válido, retorna `true`.
     *
     * @param string $tipo_filtro Cadena que representa el tipo de filtro a verificar.
     *
     * @return true|array Retorna:
     *  - `true` si `$tipo_filtro` es válido.
     *  - Un arreglo con detalles del error si `$tipo_filtro` no es válido.
     *
     * @example
     *  Ejemplo 1: `$tipo_filtro` vacío
     *  ---------------------------------------------------------------------
     *  $tipo_filtro = "";
     *  $resultado = $this->verifica_tipo_filtro($tipo_filtro);
     *  // Dado que `$tipo_filtro` está vacío, se establece en `'numeros'`.
     *  // $resultado será `true`.
     *
     * @example
     *  Ejemplo 2: `$tipo_filtro` válido
     *  ---------------------------------------------------------------------
     *  $tipo_filtro = "textos";
     *  $resultado = $this->verifica_tipo_filtro($tipo_filtro);
     *  // $resultado será `true`, ya que "textos" es un tipo permitido.
     *
     * @example
     *  Ejemplo 3: `$tipo_filtro` inválido
     *  ---------------------------------------------------------------------
     *  $tipo_filtro = "fecha";
     *  $resultado = $this->verifica_tipo_filtro($tipo_filtro);
     *  // Retorna un arreglo con el error:
     *  // [
     *  //   'error'   => 1,
     *  //   'mensaje' => 'Error el tipo filtro no es correcto los filtros pueden ser o numeros o textos',
     *  //   'data'    => { tipo_filtro: "fecha" }
     *  // ]
     */
    final public function verifica_tipo_filtro(string $tipo_filtro): true|array
    {
        $tipo_filtro = trim($tipo_filtro);

        // Si el tipo de filtro está vacío, se establece en 'numeros' por defecto
        if ($tipo_filtro === '') {
            $tipo_filtro = 'numeros';
        }

        // Tipos de filtros permitidos
        $tipos_permitidos = array('numeros', 'textos');

        // Verifica si el tipo de filtro no es válido
        if (!in_array($tipo_filtro, $tipos_permitidos)) {
            $params = new stdClass();
            $params->tipo_filtro = $tipo_filtro;

            return $this->error->error(
                mensaje: 'Error el tipo filtro no es correcto los filtros pueden ser o numeros o textos',
                data: $params,
                es_final: true
            );
        }

        return true;
    }


}
