<?php
namespace gamboamartin\where;
use gamboamartin\errores\errores;
use gamboamartin\src\sql;
use gamboamartin\src\validaciones;
use gamboamartin\validacion\validacion;
use stdClass;

/**
 * Clase where
 *
 * Esta clase permite construir consultas SQL complejas mediante la generación dinámica de cláusulas y filtros.
 * Entre sus funcionalidades se incluyen:
 * - Generación de cláusulas SQL para filtros IN, NOT IN, BETWEEN, entre otros.
 * - Validación y transformación de datos para asegurar la integridad y seguridad de las consultas.
 * - Integración y concatenación de múltiples condiciones SQL a partir de diferentes filtros.
 *
 * La clase utiliza las siguientes dependencias:
 * - **gamboamartin\errores\errores**: Manejo y registro centralizado de errores.
 * - **gamboamartin\src\sql**: Funciones y validaciones específicas para la generación de sentencias SQL.
 * - **gamboamartin\src\validaciones**: Métodos de validación para la existencia y el formato correcto de los datos.
 * - **gamboamartin\validacion\validacion**: Validación de datos y construcción de condiciones SQL.
 * - **stdClass**: Utilizado para estructurar y retornar objetos con datos procesados.
 *
 * @package     gamboamartin\where
 * @category    SQL / Filtros
 * @author
 * @version     1.0
 * @license     MIT License
 * @link        https://github.com/gamboamartin/where/
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
     * REG
     * Valida y extrae la clave (campo) de un array de datos de filtro.
     *
     * Este método procesa un array asociativo que representa los datos de un filtro y realiza las siguientes validaciones:
     *
     * 1. Verifica que el array `$data_filtro` no esté vacío. Si el array está vacío, retorna un error indicando "Error data_filtro esta vacio".
     * 2. Obtiene la primera clave del array utilizando `key($data_filtro)` y la recorta con `trim()`.
     * 3. Si la clave resultante es una cadena vacía, retorna un error indicando "Error key vacio".
     * 4. Si la clave es numérica, retorna un error indicando "Error key debe ser un texto valido", ya que se espera un nombre de campo en formato de texto.
     *
     * Si todas las validaciones se cumplen, el método retorna la clave (campo) procesada y recortada.
     *
     * @param array $data_filtro Array asociativo que contiene los datos del filtro. Se espera que tenga al menos una clave que represente el campo a filtrar.
     *
     * @return string|array Devuelve la clave (campo) procesada (string) si la validación es exitosa;
     *                      en caso contrario, retorna un array con los detalles del error utilizando `$this->error->error()`.
     *
     * @example Ejemplo 1: Uso exitoso
     * ```php
     * $data_filtro = ['nombre' => 'Juan'];
     * $resultado = $obj->campo_data_filtro($data_filtro);
     * // Resultado esperado: "nombre"
     * ```
     *
     * @example Ejemplo 2: Error por array vacío
     * ```php
     * $data_filtro = [];
     * $resultado = $obj->campo_data_filtro($data_filtro);
     * // Resultado esperado: Array de error indicando "Error data_filtro esta vacio"
     * ```
     *
     * @example Ejemplo 3: Error por clave vacía
     * ```php
     * $data_filtro = ['' => 'valor'];
     * $resultado = $obj->campo_data_filtro($data_filtro);
     * // Resultado esperado: Array de error indicando "Error key vacio"
     * ```
     *
     * @example Ejemplo 4: Error por clave numérica
     * ```php
     * $data_filtro = [0 => 'valor'];
     * $resultado = $obj->campo_data_filtro($data_filtro);
     * // Resultado esperado: Array de error indicando "Error key debe ser un texto valido"
     * ```
     */
    private function campo_data_filtro(array $data_filtro): string|array
    {
        if (count($data_filtro) === 0) {
            return $this->error->error(
                mensaje: 'Error data_filtro esta vacio',
                data: $data_filtro,
                es_final: true
            );
        }
        $campo = key($data_filtro);
        $campo = trim($campo);
        if ($campo === '') {
            return $this->error->error(
                mensaje: "Error key vacio",
                data: $campo,
                es_final: true
            );
        }
        if (is_numeric($campo)) {
            return $this->error->error(
                mensaje: "Error key debe ser un texto valido",
                data: $campo,
                es_final: true
            );
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
     * REG
     * Procesa y valida los datos de un filtro de fecha.
     *
     * Esta función recibe un arreglo asociativo que se espera contenga los datos para un filtro de fecha.
     * Primero, se valida que el arreglo cumpla con los requisitos mínimos (que contenga las claves necesarias y
     * que la fecha tenga un formato válido) utilizando el método `valida_data_filtro_fecha()`. Si la validación falla,
     * se retorna un arreglo de error generado por el manejador de errores. Si la validación es exitosa, se extraen los
     * valores correspondientes a las claves `'campo_1'`, `'campo_2'` y `'fecha'`, y se organizan en un objeto de tipo `stdClass`.
     *
     * ## Flujo de Ejecución:
     *
     * 1. **Validación de datos:**
     *    Se llama al método `valida_data_filtro_fecha($fil_fecha)` para asegurar que:
     *    - El arreglo `$fil_fecha` contenga las claves obligatorias: `'campo_1'`, `'campo_2'` y `'fecha'`.
     *    - El valor asociado a la clave `'fecha'` cumpla con el formato de fecha esperado (por ejemplo, "yyyy-mm-dd").
     *
     * 2. **Manejo de errores:**
     *    Si durante la validación se detecta algún error (indicado por la bandera `errores::$error`),
     *    la función retorna inmediatamente un arreglo con la información del error utilizando `$this->error->error()`.
     *
     * 3. **Extracción y asignación de valores:**
     *    Si la validación es exitosa, se extraen los valores correspondientes a las claves `'campo_1'`,
     *    `'campo_2'` y `'fecha'` del arreglo `$fil_fecha`.
     *
     * 4. **Retorno de los datos procesados:**
     *    Se crea un objeto `stdClass`, se asignan las propiedades `campo_1`, `campo_2` y `fecha` con los
     *    respectivos valores extraídos y se retorna dicho objeto.
     *
     * ## Ejemplos de Uso Exitoso:
     *
     * **Ejemplo 1: Uso con datos válidos en un arreglo asociativo**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => 'inicio',
     *     'campo_2' => 'fin',
     *     'fecha'   => '2023-05-20'
     * ];
     * $resultado = $this->data_filtro_fecha($fil_fecha);
     * // Resultado esperado:
     * // Un objeto stdClass con:
     * //   $resultado->campo_1 = 'inicio'
     * //   $resultado->campo_2 = 'fin'
     * //   $resultado->fecha   = '2023-05-20'
     * ```
     *
     * **Ejemplo 2: Uso con datos válidos y formato de fecha correcto**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => 'valor1',
     *     'campo_2' => 'valor2',
     *     'fecha'   => '2022-12-31'
     * ];
     * $resultado = $this->data_filtro_fecha($fil_fecha);
     * // Resultado esperado: Objeto stdClass con:
     * //   $resultado->campo_1 = 'valor1'
     * //   $resultado->campo_2 = 'valor2'
     * //   $resultado->fecha   = '2022-12-31'
     * ```
     *
     * ## Ejemplos de Error:
     *
     * **Ejemplo 3: Falta la clave "fecha" en el arreglo**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => 'valor1',
     *     'campo_2' => 'valor2'
     * ];
     * $resultado = $this->data_filtro_fecha($fil_fecha);
     * // Resultado esperado: Se retorna un arreglo de error indicando que la clave 'fecha' es requerida,
     * // por ejemplo: ['error' => 1, 'mensaje' => 'Error al validar existencia de key', ...]
     * ```
     *
     * **Ejemplo 4: Fecha con formato inválido**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => 'valor1',
     *     'campo_2' => 'valor2',
     *     'fecha'   => '31-12-2022' // Formato incorrecto; se espera "yyyy-mm-dd"
     * ];
     * $resultado = $this->data_filtro_fecha($fil_fecha);
     * // Resultado esperado: Se retorna un arreglo de error indicando "Error al validar fecha"
     * // ya que la función valida_fecha detecta un formato no válido.
     * ```
     *
     * @param array $fil_fecha Arreglo asociativo que contiene los datos del filtro de fecha.
     *                         Se espera que incluya las siguientes claves:
     *                         - **campo_1**: Primer campo del filtro.
     *                         - **campo_2**: Segundo campo del filtro.
     *                         - **fecha**: Valor de la fecha en el formato esperado (por defecto "yyyy-mm-dd").
     *
     * @return stdClass|array Retorna un objeto de tipo `stdClass` con las propiedades `campo_1`, `campo_2` y `fecha` si la validación es exitosa.
     *                         En caso de error, retorna un array con la información detallada del error generado por `$this->error->error()`.
     */
    private function data_filtro_fecha(array $fil_fecha): stdClass|array
    {
        $valida = $this->valida_data_filtro_fecha(fil_fecha: $fil_fecha);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar fecha',
                data: $valida
            );
        }

        $campo_1 = $fil_fecha['campo_1'];
        $campo_2 = $fil_fecha['campo_2'];
        $fecha   = $fil_fecha['fecha'];

        $data = new stdClass();
        $data->campo_1 = $campo_1;
        $data->campo_2 = $campo_2;
        $data->fecha   = $fecha;

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
     * REG
     * Procesa los datos de un filtro especial para generar una condición SQL.
     *
     * Este método valida la información contenida en el array de filtro especial y construye una condición SQL
     * concatenando el campo, el operador y el valor (escapado con `addslashes()`) entre comillas simples. Además,
     * se requiere que el array de filtro contenga las claves `'operador'`, `'valor'` y `'comparacion'` para el campo a filtrar.
     *
     * Procedimiento:
     *
     * 1. Verifica que el array `$data_filtro` no esté vacío; en caso de estarlo, retorna un error.
     * 2. Obtiene el nombre del campo (clave) usando el método `campo_data_filtro()`, que valida que la clave exista,
     *    no sea vacía y sea un string válido.
     * 3. Comprueba que en `$data_filtro[$campo]` exista la clave `'operador'`. Si falta, retorna un error.
     * 4. Extrae el operador y valida que no esté vacío.
     * 5. Verifica que en `$data_filtro[$campo]` exista la clave `'valor'`; si no existe, se asigna una cadena vacía.
     * 6. Verifica que en `$data_filtro[$campo]` exista la clave `'comparacion'`; de lo contrario, retorna un error.
     * 7. Comprueba que el valor asociado a `$data_filtro[$campo]['valor']` no sea una cadena vacía; si lo es, retorna un error.
     * 8. Escapa el valor usando `addslashes()`.
     * 9. Construye la condición SQL concatenando el campo, el operador y el valor escapado.
     * 10. Crea y retorna un objeto `stdClass` con las propiedades:
     *     - `campo`: El campo procesado.
     *     - `operador`: El operador extraído del filtro.
     *     - `valor`: El valor escapado.
     *     - `comparacion`: La clave de comparación del filtro (usualmente un operador lógico, como "AND" u "OR").
     *     - `condicion`: La condición SQL completa, por ejemplo, `"precio > '100'"`.
     *
     * @param array $data_filtro Array asociativo que debe contener la configuración del filtro especial para un campo.
     *                           Su estructura debe ser similar a:
     *                           ```php
     *                           [
     *                               'nombre_del_campo' => [
     *                                   'operador'      => (string), // Ej.: '>', '=', '<'
     *                                   'valor'         => (mixed),  // Ej.: 100 o 'activo'
     *                                   'comparacion'   => (string)  // Ej.: 'AND', 'OR'
     *                                   // Opcional: 'valor_es_campo' => (bool)
     *                               ]
     *                           ]
     *                           ```
     *
     * @return array|stdClass Devuelve un objeto `stdClass` con las propiedades `campo`, `operador`, `valor`,
     *                         `comparacion` y `condicion` si la validación es exitosa; en caso contrario, retorna
     *                         un array con los detalles del error.
     *
     * @example Ejemplo 1: Uso exitoso
     * ```php
     * $data_filtro = [
     *     'precio' => [
     *         'operador'    => '>',
     *         'valor'       => 100,
     *         'comparacion' => 'AND'
     *     ]
     * ];
     *
     * $resultado = $obj->datos_filtro_especial($data_filtro);
     * // Resultado esperado (si no hay errores):
     * // stdClass {
     * //     campo       => "precio",
     * //     operador    => ">",
     * //     valor       => "100",         // Valor escapado con addslashes()
     * //     comparacion => "AND",
     * //     condicion   => "precio>'100'"
     * // }
     * ```
     *
     * @example Ejemplo 2: Error por falta de clave 'operador'
     * ```php
     * $data_filtro = [
     *     'precio' => [
     *         // Falta la clave 'operador'
     *         'valor'       => 100,
     *         'comparacion' => 'AND'
     *     ]
     * ];
     *
     * $resultado = $obj->datos_filtro_especial($data_filtro);
     * // Resultado esperado: Array de error indicando "Error data_filtro[precio][operador] debe existir"
     * ```
     *
     * @example Ejemplo 3: Error si el valor es un array
     * ```php
     * $data_filtro = [
     *     'precio' => [
     *         'operador'    => '=',
     *         'valor'       => [100, 200], // Valor inválido: se espera un dato escalar
     *         'comparacion' => 'AND'
     *     ]
     * ];
     *
     * $resultado = $obj->datos_filtro_especial($data_filtro);
     * // Resultado esperado: Array de error indicando "Error $filtro['precio']['valor'] debe ser un dato"
     * ```
     */
    private function datos_filtro_especial(array $data_filtro): array|stdClass {
        if (count($data_filtro) === 0) {
            return $this->error->error(
                mensaje: 'Error data_filtro esta vacio',
                data: $data_filtro,
                es_final: true
            );
        }
        $campo = $this->campo_data_filtro(data_filtro: $data_filtro);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener campo',
                data: $campo
            );
        }

        if (!isset($data_filtro[$campo]['operador'])) {
            return $this->error->error(
                mensaje: 'Error data_filtro[' . $campo . '][operador] debe existir',
                data: $data_filtro,
                es_final: true
            );
        }

        $operador = $data_filtro[$campo]['operador'];
        if ($operador === '') {
            return $this->error->error(
                mensaje: 'Error el operador debe de existir',
                data: $operador,
                es_final: true
            );
        }

        if (!isset($data_filtro[$campo]['valor'])) {
            return $this->error->error(
                mensaje: 'Error data_filtro[' . $campo . '][valor] debe existir',
                data: $data_filtro,
                es_final: true
            );
        }
        if (!isset($data_filtro[$campo]['comparacion'])) {
            return $this->error->error(
                mensaje: 'Error data_filtro[' . $campo . '][comparacion] debe existir',
                data: $data_filtro,
                es_final: true
            );
        }

        $valor = $data_filtro[$campo]['valor'];
        if ($valor === '') {
            return $this->error->error(
                mensaje: 'Error el operador debe de existir',
                data: $valor,
                es_final: true
            );
        }
        $valor = addslashes($valor);
        $comparacion = $data_filtro[$campo]['comparacion'];
        $condicion = $campo . $operador . "'$valor'";

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
     * REG
     * Genera la cláusula SQL especial combinando múltiples filtros especiales.
     *
     * Este método recorre cada entrada del arreglo `$filtro_especial`, que debe ser un arreglo asociativo donde:
     * - Las claves representan los nombres de los campos (por ejemplo, "tabla.campo").
     * - Los valores son arreglos que contienen la configuración de cada filtro especial para ese campo.
     *
     * Para cada entrada, se realiza lo siguiente:
     *
     * 1. Se verifica que el valor asociado a cada campo sea un arreglo. Si no lo es, se retorna un error indicando
     *    que cada filtro especial debe definirse como un arreglo.
     *
     * 2. Se llama al método `obten_filtro_especial()`, el cual se encarga de procesar el filtro especial para el campo
     *    en cuestión y de integrar la condición SQL resultante a la cláusula SQL especial acumulada (`$filtro_especial_sql`).
     *
     * 3. Si ocurre algún error durante el procesamiento de alguno de los filtros (por ejemplo, validaciones fallidas o
     *    problemas al concatenar la condición), se retorna un error con los detalles correspondientes.
     *
     * Al finalizar el ciclo, se retorna la cadena SQL resultante que contiene la combinación de todas las condiciones
     * especiales de filtro.
     *
     * @param array $columnas_extra Array asociativo con definiciones adicionales para los campos, que pueden utilizarse
     *                              para sobrescribir o complementar el nombre del campo en el filtro especial.
     *                              Ejemplo:
     *                              ```php
     *                              [
     *                                  'tabla.precio' => 'productos.precio'
     *                              ]
     *                              ```
     * @param array $filtro_especial  Arreglo asociativo que define los filtros especiales. Cada entrada debe tener la siguiente estructura:
     *                              ```php
     *                              [
     *                                  'tabla.campo' => [
     *                                      'operador'     => '>',    // Operador de comparación (por ejemplo, ">", "<", "=")
     *                                      'valor'        => 100,    // Valor para la comparación
     *                                      'comparacion'  => 'AND'   // (Opcional) Operador lógico para concatenar condiciones
     *                                  ]
     *                              ]
     *                              ```
     *
     * @return array|string Retorna la cadena SQL que resulta de concatenar todas las condiciones especiales de filtro.
     *                      En caso de error, retorna un arreglo con la información del error.
     *
     * @example Ejemplo 1: Uso exitoso con un único filtro especial
     * ```php
     * $columnas_extra = ['tabla.precio' => 'productos.precio'];
     * $filtro_especial = [
     *     'tabla.precio' => [
     *         'operador' => '>',
     *         'valor' => 100,
     *         'comparacion' => 'AND'
     *     ]
     * ];
     *
     * // Supongamos que el método obten_filtro_especial() procesa correctamente el filtro y retorna:
     * // "productos.precio > '100'"
     *
     * $resultado = $objeto->filtro_especial_sql($columnas_extra, $filtro_especial);
     * // Resultado esperado:
     * // "productos.precio > '100'"
     * ```
     *
     * @example Ejemplo 2: Uso exitoso con múltiples filtros especiales
     * ```php
     * $columnas_extra = [
     *     'tabla.precio' => 'productos.precio',
     *     'tabla.stock'  => 'productos.stock'
     * ];
     * $filtro_especial = [
     *     'tabla.precio' => [
     *         'operador' => '>',
     *         'valor' => 100,
     *         'comparacion' => 'AND'
     *     ],
     *     'tabla.stock' => [
     *         'operador' => '<',
     *         'valor' => 50,
     *         'comparacion' => 'AND'
     *     ]
     * ];
     *
     * // Supongamos que:
     * // - Para 'tabla.precio' se genera: "productos.precio > '100'"
     * // - Para 'tabla.stock' se genera: "productos.stock < '50'"
     * // La concatenación final (dependiendo de la lógica interna de obten_filtro_especial) sería:
     * $resultado = $objeto->filtro_especial_sql($columnas_extra, $filtro_especial);
     * // Resultado esperado (ejemplo):
     * // "productos.precio > '100' AND productos.stock < '50'"
     * ```
     *
     * @example Ejemplo 3: Error al procesar un filtro especial (clave no es un array)
     * ```php
     * $columnas_extra = ['tabla.precio' => 'productos.precio'];
     * $filtro_especial = [
     *     'tabla.precio' => "no_es_un_array"
     * ];
     *
     * $resultado = $objeto->filtro_especial_sql($columnas_extra, $filtro_especial);
     * // Resultado esperado: Un arreglo de error indicando "Error filtro debe ser un array filtro_especial[] = array()"
     * ```
     */
    final public function filtro_especial_sql(array $columnas_extra, array $filtro_especial): array|string {
        $filtro_especial_sql = '';
        foreach ($filtro_especial as $campo => $filtro_esp) {
            if (!is_array($filtro_esp)) {
                return $this->error->error(
                    mensaje: "Error filtro debe ser un array filtro_especial[] = array() del campo ".$campo,
                    data: $filtro_esp,
                    es_final: true
                );
            }

            $filtro_especial_sql = $this->obten_filtro_especial(
                columnas_extra: $columnas_extra,
                filtro_esp: $filtro_esp,
                filtro_especial_sql: $filtro_especial_sql
            );
            if (errores::$error) {
                return $this->error->error(
                    mensaje: "Error filtro",
                    data: $filtro_especial_sql
                );
            }
        }
        return $filtro_especial_sql;
    }


    /**
     * REG
     * Genera una cláusula SQL extra a partir de un conjunto de filtros adicionales.
     *
     * Este método recorre el array `$filtro_extra`, donde cada elemento representa un filtro adicional que se desea
     * integrar en la cláusula SQL final. Para cada filtro, se realiza lo siguiente:
     *
     * 1. Se valida que el filtro (cada elemento de `$filtro_extra`) sea un array. Si no lo es, se retorna un error
     *    indicando que cada filtro debe ser un array.
     * 2. Se llama al método `integra_filtro_extra()` para integrar el filtro actual en la cadena SQL acumulada (`$filtro_extra_sql`).
     * 3. Si ocurre algún error durante la integración de cualquier filtro, se retorna un error con los detalles del mismo.
     *
     * Al finalizar, se retorna la cadena SQL resultante que contiene la concatenación de todos los filtros extra.
     *
     * @param array $filtro_extra Array de filtros adicionales. Cada elemento debe ser un array que defina un filtro especial.
     *                            La estructura de cada filtro dependerá de los requerimientos del método
     *                            `integra_filtro_extra()`.
     *
     * @return array|string Retorna la cadena SQL resultante con los filtros extra integrados si la operación es exitosa.
     *                      En caso de error, retorna un array con información del error.
     *
     * @example Ejemplo 1: Uso exitoso con múltiples filtros extra
     * ```php
     * // Ejemplo de filtros extra:
     * $filtro_extra = [
     *     [
     *         // Suponiendo que la estructura requerida para cada filtro es la siguiente:
     *         // (La clave 'comparacion' define el operador lógico para concatenar este filtro con otros)
     *         'precio' => [
     *             'operador'    => '>',
     *             'valor'       => 100,
     *             'comparacion' => 'AND'
     *         ]
     *     ],
     *     [
     *         'stock' => [
     *             'operador'    => '<',
     *             'valor'       => 50,
     *             'comparacion' => 'AND'
     *         ]
     *     ]
     * ];
     *
     * $resultado = $obj->filtro_extra_sql($filtro_extra);
     * // Resultado esperado (ejemplo): "precio > '100' AND stock < '50'"
     * ```
     *
     * @example Ejemplo 2: Error por filtro extra no siendo un array
     * ```php
     * // En este caso, un elemento del array de filtros no es un array:
     * $filtro_extra = [
     *     "no_es_un_array"
     * ];
     *
     * $resultado = $obj->filtro_extra_sql($filtro_extra);
     * // Resultado esperado: Array de error indicando "Error $data_filtro debe ser un array"
     * ```
     */
    final public function filtro_extra_sql(array $filtro_extra): array|string {
        $filtro_extra_sql = '';
        foreach ($filtro_extra as $data_filtro) {
            if (!is_array($data_filtro)) {
                return $this->error->error(
                    mensaje: 'Error $data_filtro debe ser un array',
                    data: $filtro_extra,
                    es_final: true
                );
            }
            $filtro_extra_sql = $this->integra_filtro_extra(
                data_filtro: $data_filtro,
                filtro_extra_sql: $filtro_extra_sql
            );
            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error al generar filtro',
                    data: $filtro_extra_sql
                );
            }
        }
        return $filtro_extra_sql;
    }


    /**
     * REG
     * Genera o actualiza una cadena SQL para un filtro extra, concatenando una condición adicional.
     *
     * Este método se utiliza para construir o complementar una cláusula SQL adicional (por ejemplo, en un WHERE)
     * con una nueva condición. La lógica es la siguiente:
     *
     * - Si la cadena `$filtro_extra_sql` está vacía, se asigna directamente el valor de `$condicion` a la misma.
     * - Si `$filtro_extra_sql` ya contiene contenido, se concatena a ésta el operador de comparación (`$comparacion`)
     *   seguido de la nueva condición (`$condicion`), sin agregar espacios adicionales, según cómo se hayan definido
     *   estos parámetros.
     *
     * De esta forma, permite ir acumulando condiciones en una sentencia SQL extra.
     *
     * @param string $comparacion   El operador lógico que se utilizará para unir la condición nueva con la existente.
     *                              Ejemplos comunes: " AND " o " OR ".
     * @param string $condicion     La nueva condición SQL a agregar. Por ejemplo, "tabla.campo > '100'".
     * @param string $filtro_extra_sql La cadena SQL existente a la cual se le añadirá la nueva condición.
     *                                 Si está vacía, se inicializa con el valor de `$condicion`.
     *
     * @return string               Devuelve la cadena SQL resultante tras concatenar la nueva condición.
     *
     * @example Ejemplo 1: Sin filtro extra previo
     * ```php
     * // Si no hay condiciones previas, $filtro_extra_sql está vacío.
     * $comparacion = " AND ";
     * $condicion = "tabla.campo > '100'";
     * $filtro_extra_sql = "";
     *
     * $resultado = $obj->filtro_extra_sql_genera($comparacion, $condicion, $filtro_extra_sql);
     * // Resultado esperado: "tabla.campo > '100'"
     * ```
     *
     * @example Ejemplo 2: Con filtro extra previo
     * ```php
     * // Si ya existe una condición previa en $filtro_extra_sql, se concatena el operador y la nueva condición.
     * $comparacion = " OR ";
     * $condicion = "tabla.campo < '200'";
     * $filtro_extra_sql = "tabla.campo = '150'";
     *
     * $resultado = $obj->filtro_extra_sql_genera($comparacion, $condicion, $filtro_extra_sql);
     * // Resultado esperado: "tabla.campo = '150' OR tabla.campo < '200'"
     * ```
     */
    private function filtro_extra_sql_genera(string $comparacion, string $condicion, string $filtro_extra_sql): string {
        if ($filtro_extra_sql === '') {
            $filtro_extra_sql .= $condicion;
        } else {
            $filtro_extra_sql .= $comparacion . $condicion;
        }
        return $filtro_extra_sql;
    }


    /**
     * REG
     * Genera una condición SQL para filtrar registros en base a un rango de fechas.
     *
     * Este método procesa un arreglo asociativo que contiene la información del filtro de fecha y retorna
     * una cadena SQL que representa la condición necesaria para comparar una fecha dada con un rango
     * definido por dos valores (por ejemplo, para determinar si una fecha se encuentra entre dos límites).
     *
     * El proceso se realiza de la siguiente manera:
     *
     * 1. Se llama al método interno `filtro_fecha_base()` pasándole el arreglo `$filtro_fecha` para generar
     *    la parte central de la condición SQL. Este método se encarga de validar que el arreglo contenga los
     *    índices requeridos y que el valor de la fecha cumpla con el formato esperado (por lo general, "yyyy-mm-dd").
     *
     * 2. Si ocurre algún error durante la generación de la condición SQL (por ejemplo, por datos incompletos o
     *    formato de fecha incorrecto), se retorna un arreglo con los detalles del error utilizando el manejador
     *    de errores (`$this->error->error()`).
     *
     * 3. Si la condición SQL generada no es una cadena vacía, se envuelve entre paréntesis para delimitarla.
     *
     * @param array $filtro_fecha Arreglo asociativo que define el filtro de fecha. Debe incluir las siguientes claves:
     *   - **campo_1** (string): El límite inferior del rango (por ejemplo, la fecha mínima).
     *   - **campo_2** (string): El límite superior del rango (por ejemplo, la fecha máxima).
     *   - **fecha**   (string): La fecha a evaluar (por lo general, en el formato "yyyy-mm-dd").
     *
     * @return array|string Devuelve:
     *   - Un **string** con la condición SQL completa, por ejemplo:
     *     > ("'2020-06-15' >= 2020-01-01 AND '2020-06-15' <= 2020-12-31")
     *   - O un **array** con la información del error en caso de que se produzca algún fallo en la validación
     *     o generación de la consulta.
     *
     * @example Ejemplo de uso exitoso:
     * <pre>
     * // Definición del filtro de fecha con datos válidos:
     * $filtro_fecha = [
     *     'campo_1' => '2020-01-01',
     *     'campo_2' => '2020-12-31',
     *     'fecha'   => '2020-06-15'
     * ];
     *
     * // Supongamos que el método interno `filtro_fecha_base($filtro_fecha)` genera la siguiente cadena:
     * // "'2020-06-15' >= 2020-01-01 AND '2020-06-15' <= 2020-12-31"
     *
     * // La llamada a filtro_fecha() envolverá esta cadena entre paréntesis:
     * $sqlCondicion = $objeto->filtro_fecha($filtro_fecha);
     *
     * // Resultado esperado:
     * // $sqlCondicion = "('2020-06-15' >= 2020-01-01 AND '2020-06-15' <= 2020-12-31)"
     * </pre>
     *
     * @example Ejemplo cuando la condición SQL generada está vacía:
     * <pre>
     * // Si se pasan datos vacíos o incorrectos en el arreglo, el método interno podría retornar una cadena vacía:
     * $filtro_fecha = [
     *     'campo_1' => '',
     *     'campo_2' => '',
     *     'fecha'   => ''
     * ];
     *
     * $sqlCondicion = $objeto->filtro_fecha($filtro_fecha);
     *
     * // Resultado esperado:
     * // $sqlCondicion = ""
     * </pre>
     */
    final public function filtro_fecha(array $filtro_fecha): array|string
    {
        $filtro_fecha_sql = $this->filtro_fecha_base(filtro_fecha: $filtro_fecha);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sql', data: $filtro_fecha_sql);
        }

        if ($filtro_fecha_sql !== '') {
            $filtro_fecha_sql = "($filtro_fecha_sql)";
        }

        return $filtro_fecha_sql;
    }


    /**
     * REG
     * Genera una cláusula SQL basada en uno o varios filtros de fecha.
     *
     * Este método itera sobre un arreglo de filtros de fecha, donde cada filtro es a su vez un arreglo que
     * contiene la información necesaria para definir un rango de fecha. Para cada filtro, se realiza lo siguiente:
     *
     * 1. **Verificación del tipo de filtro:**
     *    Se comprueba que cada elemento del arreglo `$filtro_fecha` sea un arreglo. Si algún elemento no lo es,
     *    se retorna inmediatamente un error indicando que el filtro debe ser un arreglo.
     *
     * 2. **Validación del filtro de fecha:**
     *    Se invoca el método `valida_filtro_fecha()` pasándole el filtro actual. Este método se encarga de
     *    validar que el filtro contenga las claves necesarias (por ejemplo, `'campo_1'`, `'campo_2'` y `'fecha'`)
     *    y que el valor de la fecha tenga un formato correcto. En caso de error en la validación, se retorna un
     *    error con la información correspondiente.
     *
     * 3. **Generación de la cláusula SQL:**
     *    Se llama al método `genera_sql_filtro_fecha()` con el filtro actual y la cláusula SQL acumulada hasta el momento
     *    (almacenada en `$filtro_fecha_sql`). Este método genera la parte SQL que corresponde al filtro de fecha.
     *    Si ocurre algún error al generar el SQL, se retorna un error.
     *
     * 4. **Concatenación de las cláusulas SQL:**
     *    La cláusula SQL generada para cada filtro se concatena a la variable `$filtro_fecha_sql`.
     *
     * Finalmente, el método retorna la cláusula SQL completa resultante de la concatenación de todos los filtros procesados.
     *
     * ## Valor de Retorno
     * - **Éxito:** Retorna un string que contiene la cláusula SQL resultante, por ejemplo:
     *   ```sql
     *   AND('2023-05-20' >= campo_inicio AND '2023-05-20' <= campo_fin)AND('2023-06-15' >= campo_inicio2 AND '2023-06-15' <= campo_fin2)
     *   ```
     * - **Error:** Retorna un arreglo con la información del error generado por `$this->error->error()`.
     *
     * ## Ejemplos de Uso Exitoso
     *
     * **Ejemplo 1: Un solo filtro de fecha**
     * ```php
     * $filtro_fecha = [
     *     [
     *         'campo_1' => '2023-01-01',  // Valor inferior del rango
     *         'campo_2' => '2023-12-31',  // Valor superior del rango
     *         'fecha'   => '2023-06-15'   // Fecha a evaluar
     *     ]
     * ];
     *
     * $sqlFiltro = $this->filtro_fecha_base($filtro_fecha);
     * // Suponiendo que los métodos internos generan correctamente el SQL,
     * // $sqlFiltro podría contener una cadena similar a:
     * // "AND('2023-06-15' >= 2023-01-01 AND '2023-06-15' <= 2023-12-31)"
     * ```
     *
     * **Ejemplo 2: Múltiples filtros de fecha concatenados**
     * ```php
     * $filtro_fecha = [
     *     [
     *         'campo_1' => '2023-01-01',
     *         'campo_2' => '2023-06-30',
     *         'fecha'   => '2023-03-15'
     *     ],
     *     [
     *         'campo_1' => '2023-07-01',
     *         'campo_2' => '2023-12-31',
     *         'fecha'   => '2023-09-10'
     *     ]
     * ];
     *
     * $sqlFiltro = $this->filtro_fecha_base($filtro_fecha);
     * // El resultado podría ser algo como:
     * // "AND('2023-03-15' >= 2023-01-01 AND '2023-03-15' <= 2023-06-30)AND('2023-09-10' >= 2023-07-01 AND '2023-09-10' <= 2023-12-31)"
     * ```
     *
     * ## Consideraciones Adicionales
     * - Es importante que cada filtro de fecha en el arreglo `$filtro_fecha` esté correctamente formado y contenga
     *   las claves necesarias (`'campo_1'`, `'campo_2'` y `'fecha'`), de lo contrario, el método retornará un error.
     * - La cadena `$filtro_fecha_sql` se utiliza como acumulador de la cláusula SQL generada para cada filtro, permitiendo
     *   la concatenación de múltiples filtros.
     *
     * @param array $filtro_fecha Arreglo que contiene uno o más filtros de fecha. Cada filtro debe ser un arreglo asociativo
     *                            que incluya las claves:
     *                            - `'campo_1'`: Límite inferior del rango.
     *                            - `'campo_2'`: Límite superior del rango.
     *                            - `'fecha'`:   La fecha que se evaluará.
     *
     * @return array|string Retorna un string con la cláusula SQL completa si la validación y generación fueron exitosas;
     *                      en caso de error, retorna un arreglo con la información del error.
     */
    private function filtro_fecha_base(array $filtro_fecha): array|string
    {
        $filtro_fecha_sql = '';
        foreach ($filtro_fecha as $fil_fecha) {
            if (!is_array($fil_fecha)) {
                return $this->error->error(
                    mensaje: 'Error $fil_fecha debe ser un array',
                    data: $fil_fecha,
                    es_final: true
                );
            }

            $valida = $this->valida_filtro_fecha(fil_fecha: $fil_fecha);
            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error al validar filtro',
                    data: $valida
                );
            }

            $sql = $this->genera_sql_filtro_fecha(fil_fecha: $fil_fecha, filtro_fecha_sql: $filtro_fecha_sql);
            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error al obtener sql',
                    data: $sql
                );
            }

            $filtro_fecha_sql .= $sql;
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
     * REG
     * Genera o actualiza la cláusula SQL para un filtro especial concatenando una condición adicional.
     *
     * Este método se utiliza para construir o complementar una cláusula SQL especial en función de la condición
     * ya existente y la nueva condición que se desea agregar. El comportamiento es el siguiente:
     *
     * - Si el parámetro `$filtro_especial_sql` está vacío, se asigna el valor de `$data_sql` a `$filtro_especial_sql`.
     * - Si `$filtro_especial_sql` ya contiene información, se verifica que en el arreglo `$filtro_esp` exista
     *   la clave `'comparacion'` para el campo dado en `$campo`. Si no existe, se retorna un error.
     * - Además, se valida que `$data_sql` no sea una cadena vacía. Si lo es, se retorna un error.
     * - Si todas las validaciones son satisfactorias, se concatena a `$filtro_especial_sql` un espacio, el valor
     *   de `$filtro_esp[$campo]['comparacion']` (por ejemplo, "AND" u "OR"), otro espacio y finalmente `$data_sql`.
     *
     * De este modo, el método permite integrar condicionalmente una nueva condición SQL en una cláusula especial ya existente.
     *
     * @param string $campo              El nombre del campo al que se aplica el filtro especial. Este valor se utiliza
     *                                   para acceder a la clave correspondiente en el arreglo `$filtro_esp` y obtener el
     *                                   operador de comparación.
     * @param string $data_sql           La cadena SQL que representa la nueva condición que se desea agregar. Por ejemplo,
     *                                   podría ser `"tabla.campo > 'valor'"`.
     * @param array  $filtro_esp         Arreglo asociativo que contiene, para cada campo, los parámetros del filtro especial.
     *                                   Debe incluir la clave `'comparacion'` para el campo `$campo`, que especifica el operador
     *                                   lógico (por ejemplo, `"AND"` u `"OR"`) que se usará para concatenar la condición.
     *                                   Ejemplo:
     *                                   ```php
     *                                   [
     *                                       'tabla.campo' => [
     *                                           'comparacion' => 'AND'
     *                                       ]
     *                                   ]
     *                                   ```
     * @param string $filtro_especial_sql  La cadena SQL existente para el filtro especial que se desea complementar.
     *                                     Si está vacía, se inicializa con el valor de `$data_sql`.
     *
     * @return array|string              Retorna la cadena SQL resultante con la condición especial actualizada si la operación es exitosa;
     *                                   de lo contrario, retorna un array con los detalles del error.
     *
     * @example Ejemplo 1: Filtro especial sin condición previa
     * ```php
     * // Si no existe una cláusula previa, se asigna data_sql directamente.
     * $campo = 'tabla.campo';
     * $data_sql = "tabla.campo > '100'";
     * $filtro_esp = [
     *     'tabla.campo' => [
     *         'comparacion' => 'AND'
     *     ]
     * ];
     * $filtro_especial_sql = ""; // Vacío inicialmente
     *
     * $resultado = $objeto->genera_filtro_especial($campo, $data_sql, $filtro_esp, $filtro_especial_sql);
     * // Resultado esperado:
     * // "tabla.campo > '100'"
     * ```
     *
     * @example Ejemplo 2: Filtro especial con condición previa
     * ```php
     * // Se tiene una condición previa y se desea agregar otra condición con un operador lógico.
     * $campo = 'tabla.campo';
     * $data_sql = "tabla.campo < '200'";
     * $filtro_esp = [
     *     'tabla.campo' => [
     *         'comparacion' => 'OR'
     *     ]
     * ];
     * $filtro_especial_sql = "tabla.campo = '150'";
     *
     * $resultado = $objeto->genera_filtro_especial($campo, $data_sql, $filtro_esp, $filtro_especial_sql);
     * // Resultado esperado:
     * // "tabla.campo = '150' OR tabla.campo < '200'"
     * ```
     *
     * @example Ejemplo 3: Error por falta de la clave 'comparacion'
     * ```php
     * $campo = 'tabla.campo';
     * $data_sql = "tabla.campo > '100'";
     * $filtro_esp = [
     *     'tabla.campo' => [
     *         // 'comparacion' no está definida
     *     ]
     * ];
     * $filtro_especial_sql = "tabla.campo = '150'";
     *
     * $resultado = $objeto->genera_filtro_especial($campo, $data_sql, $filtro_esp, $filtro_especial_sql);
     * // Resultado esperado: Array de error indicando que $filtro_esp['tabla.campo']['comparacion'] debe existir.
     * ```
     */
    final public function genera_filtro_especial(
        string $campo,
        string $data_sql,
        array $filtro_esp,
        string $filtro_especial_sql
    ): array|string {
        if ($filtro_especial_sql === '') {
            $filtro_especial_sql .= $data_sql;
        } else {
            if (!isset($filtro_esp[$campo]['comparacion'])) {
                return $this->error->error(
                    mensaje: 'Error $filtro_esp[$campo][\'comparacion\'] debe existir',
                    data: $filtro_esp,
                    es_final: true
                );
            }
            if (trim($data_sql) === '') {
                return $this->error->error(
                    mensaje: 'Error $data_sql no puede venir vacio',
                    data: $data_sql,
                    es_final: true
                );
            }
            $filtro_especial_sql .= ' ' . $filtro_esp[$campo]['comparacion'] . ' ' . $data_sql;
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
     * REG
     * Integra un filtro extra en una cláusula SQL existente.
     *
     * Este método toma un array de datos de filtro especial y una cadena SQL existente para filtros extra,
     * y concatena la nueva condición al filtro existente. El proceso es el siguiente:
     *
     * 1. Verifica que el array `$data_filtro` no esté vacío. Si lo está, retorna un error indicando que los datos del filtro están vacíos.
     * 2. Llama al método `datos_filtro_especial()` para procesar y obtener los detalles del filtro especial a partir de `$data_filtro`.
     *    Este método devuelve un objeto `stdClass` con las propiedades:
     *      - `campo`: El nombre del campo filtrado.
     *      - `operador`: El operador de comparación (por ejemplo, ">", "<", "=").
     *      - `valor`: El valor (escapado) a comparar.
     *      - `comparacion`: El operador lógico que se usará para concatenar (por ejemplo, "AND" u "OR").
     *      - `condicion`: La condición SQL generada, por ejemplo, `"precio>'100'"`.
     *    Si ocurre un error, se retorna el error respectivo.
     * 3. Utiliza el método `filtro_extra_sql_genera()` para concatenar la condición obtenida (`$datos->condicion`) al
     *    filtro SQL extra existente (`$filtro_extra_sql`), utilizando el operador lógico definido en `$datos->comparacion`.
     *    - Si `$filtro_extra_sql` está vacío, se asigna directamente el valor de `$datos->condicion`.
     *    - En caso de error en la concatenación, se retorna el error correspondiente.
     * 4. Retorna la cadena SQL final con la condición extra integrada.
     *
     * @param array  $data_filtro     Array que contiene los datos del filtro especial. Debe tener la siguiente estructura:
     *                                ```php
     *                                [
     *                                    'nombre_campo' => [
     *                                        'operador'    => (string),  // Ej.: '>', '<', '='
     *                                        'valor'       => (mixed),   // Ej.: 100, 'activo'
     *                                        'comparacion' => (string)   // Ej.: 'AND', 'OR'
     *                                    ]
     *                                ]
     *                                ```
     * @param string $filtro_extra_sql La cadena SQL existente con filtros extra que se desea complementar.
     *
     * @return object|string|array Retorna:
     *                              - Una cadena SQL que representa el filtro extra actualizado,
     *                              - Un objeto `stdClass` en ciertos contextos, o
     *                              - Un array con los detalles del error si ocurre alguna falla.
     *
     * @example Ejemplo 1: Integración exitosa sin filtro extra previo
     * ```php
     * // Supongamos que:
     * // $data_filtro contiene:
     * // [
     * //     'precio' => [
     * //         'operador'    => '>',
     * //         'valor'       => 100,
     * //         'comparacion' => 'AND'
     * //     ]
     * // ]
     * // Y que el método datos_filtro_especial() genera:
     * // stdClass {
     * //     campo       => "precio",
     * //     operador    => ">",
     * //     valor       => "100",
     * //     comparacion => "AND",
     * //     condicion   => "precio>'100'"
     * // }
     * // Además, $filtro_extra_sql es una cadena vacía.
     *
     * $filtro_extra_sql = "";
     * $resultado = $obj->integra_filtro_extra($data_filtro, $filtro_extra_sql);
     * // Resultado esperado:
     * // "precio>'100'"
     * ```
     *
     * @example Ejemplo 2: Integración exitosa con filtro extra previo
     * ```php
     * // Supongamos que:
     * // $data_filtro contiene:
     * // [
     * //     'precio' => [
     * //         'operador'    => '<',
     * //         'valor'       => 200,
     * //         'comparacion' => 'OR'
     * //     ]
     * // ]
     * // Y que el método datos_filtro_especial() genera:
     * // stdClass {
     * //     campo       => "precio",
     * //     operador    => "<",
     * //     valor       => "200",
     * //     comparacion => "OR",
     * //     condicion   => "precio<'200'"
     * // }
     * // Además, $filtro_extra_sql tiene un valor previo, por ejemplo: "precio = '150'".
     *
     * $filtro_extra_sql = "precio = '150'";
     * $resultado = $obj->integra_filtro_extra($data_filtro, $filtro_extra_sql);
     * // Resultado esperado:
     * // "precio = '150' OR precio<'200'"
     * ```
     *
     * @example Ejemplo 3: Error por array de filtro vacío
     * ```php
     * $data_filtro = []; // Array vacío
     * $filtro_extra_sql = "";
     * $resultado = $obj->integra_filtro_extra($data_filtro, $filtro_extra_sql);
     * // Resultado esperado: Array de error indicando "Error data_filtro esta vacio"
     * ```
     */
    private function integra_filtro_extra(array $data_filtro, string $filtro_extra_sql): object|string|array {
        if (count($data_filtro) === 0) {
            return $this->error->error(
                mensaje: 'Error data_filtro esta vacio',
                data: $data_filtro,
                es_final: true
            );
        }

        $datos = $this->datos_filtro_especial(data_filtro: $data_filtro);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener datos de filtro',
                data: $datos
            );
        }

        $filtro_extra_sql = $this->filtro_extra_sql_genera(
            comparacion: $datos->comparacion,
            condicion: $datos->condicion,
            filtro_extra_sql: $filtro_extra_sql
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar filtro',
                data: $filtro_extra_sql
            );
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
     * REG
     * Genera una condición SQL para filtrar fechas.
     *
     * Esta función construye y retorna una cadena SQL que utiliza un operador lógico (por ejemplo, "AND")
     * para comparar una fecha proporcionada con dos límites: un valor mínimo y un valor máximo. Los límites se
     * extraen del objeto `$data` a partir de las propiedades `campo_1` y `campo_2`, y la fecha a evaluar se
     * obtiene de la propiedad `fecha`.
     *
     * Antes de construir la cadena SQL, la función realiza las siguientes validaciones:
     *
     * 1. **Validación de existencia y contenido de propiedades:**
     *    - Verifica que el objeto `$data` contenga las propiedades `'fecha'`, `'campo_1'` y `'campo_2'`.
     *    - Para cada propiedad, se comprueba que exista y que, tras aplicar `trim()`, su valor no sea una cadena vacía.
     *      Si alguna de estas condiciones no se cumple, se retorna un array de error utilizando el manejador de errores.
     *
     * 2. **Validación del formato de la fecha:**
     *    - Se valida que el valor de la propiedad `fecha` cumpla con el formato de fecha esperado llamando a
     *      `$this->validacion->valida_fecha()`. Si la validación falla, se retorna un array con el error correspondiente.
     *
     * Si todas las validaciones son exitosas, la función retorna una cadena SQL que aplica el operador lógico
     * `$and` y verifica que la fecha indicada se encuentre entre los valores de `campo_1` y `campo_2`.
     *
     * ## Ejemplos de Uso Exitoso:
     *
     * **Ejemplo 1: Uso correcto con datos válidos**
     * ```php
     * // Supongamos que $and contiene el operador lógico "AND" y $data es un objeto stdClass con las propiedades necesarias:
     * $data = new stdClass();
     * $data->fecha    = '2023-05-20';
     * $data->campo_1  = '2023-01-01';
     * $data->campo_2  = '2023-12-31';
     *
     * // Llamada a la función:
     * $sqlCondicion = $this->sql_fecha("AND", $data);
     *
     * // Resultado esperado:
     * // "AND('2023-05-20' >= 2023-01-01 AND '2023-05-20' <= 2023-12-31)"
     * ```
     *
     * **Ejemplo 2: Uso con espacios en blanco**
     * ```php
     * $data = new stdClass();
     * $data->fecha    = ' 2023-05-20 ';  // Espacios antes y después serán eliminados con trim()
     * $data->campo_1  = '2023-01-01';
     * $data->campo_2  = '2023-12-31';
     *
     * $sqlCondicion = $this->sql_fecha("AND", $data);
     * // Resultado: "AND('2023-05-20' >= 2023-01-01 AND '2023-05-20' <= 2023-12-31)"
     * ```
     *
     * ## Ejemplos de Casos de Error:
     *
     * **Ejemplo 3: Falta una propiedad requerida**
     * ```php
     * $data = new stdClass();
     * $data->fecha   = '2023-05-20';
     * // Falta la propiedad 'campo_1'
     * $data->campo_2 = '2023-12-31';
     *
     * $resultado = $this->sql_fecha("AND", $data);
     * // Resultado esperado: Un array de error indicando "error no existe $data->campo_1".
     * ```
     *
     * **Ejemplo 4: Valor de la fecha vacío**
     * ```php
     * $data = new stdClass();
     * $data->fecha    = '   ';  // Cadena vacía tras aplicar trim()
     * $data->campo_1  = '2023-01-01';
     * $data->campo_2  = '2023-12-31';
     *
     * $resultado = $this->sql_fecha("AND", $data);
     * // Resultado esperado: Un array de error indicando "error esta vacio $data->fecha".
     * ```
     *
     * **Ejemplo 5: Formato de fecha inválido**
     * ```php
     * $data = new stdClass();
     * $data->fecha    = '20-05-2023';  // Formato incorrecto
     * $data->campo_1  = '2023-01-01';
     * $data->campo_2  = '2023-12-31';
     *
     * $resultado = $this->sql_fecha("AND", $data);
     * // Resultado esperado: Un array de error indicando "error al validar fecha",
     * // derivado de la función valida_fecha que detecta el formato incorrecto.
     * ```
     *
     * @param string   $and  Operador lógico (por ejemplo, "AND") que se usará para concatenar la condición SQL.
     * @param stdClass $data Objeto que debe contener las propiedades:
     *                       - **fecha**: La fecha a comparar (en formato válido, por ejemplo, "yyyy-mm-dd").
     *                       - **campo_1**: Valor mínimo para la comparación.
     *                       - **campo_2**: Valor máximo para la comparación.
     *
     * @return string|array Retorna una cadena SQL que representa la condición de fecha si la validación es exitosa.
     *                      En caso de error, retorna un array con la información del error generado por `$this->error->error()`.
     */
    private function sql_fecha(string $and, stdClass $data): string|array
    {
        $keys = array('fecha', 'campo_1', 'campo_2');
        foreach ($keys as $key) {
            if (!isset($data->$key)) {
                return $this->error->error(
                    mensaje: 'error no existe $data->' . $key,
                    data: $data,
                    es_final: true
                );
            }
            if (trim($data->$key) === '') {
                return $this->error->error(
                    mensaje: 'error esta vacio $data->' . $key,
                    data: $data,
                    es_final: true
                );
            }
        }
        // Se valida que el valor de 'fecha' cumpla con el formato de fecha esperado.
        $keys = array('fecha');
        foreach ($keys as $key) {
            $valida = $this->validacion->valida_fecha(fecha: $data->$key);
            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'error al validar ' . $key,
                    data: $valida
                );
            }
        }

        return "$and('$data->fecha' >= $data->campo_1 AND '$data->fecha' <= $data->campo_2)";
    }


    /**
     * REG
     * Genera la cláusula SQL NOT IN a partir de un arreglo de datos que define la cláusula.
     *
     * Este método realiza las siguientes operaciones:
     *
     * 1. Llama al método `data_in()` pasando el arreglo `$not_in` para obtener un objeto que contenga:
     *    - `llave`: el nombre de la columna en la cláusula NOT IN.
     *    - `values`: el array de valores que se incluirán en la cláusula.
     *
     * 2. Si ocurre un error durante la obtención de los datos (por ejemplo, si faltan claves requeridas),
     *    se retorna un array de error utilizando `$this->error->error()`.
     *
     * 3. Con el objeto obtenido, se llama al método `not_in_sql()`, el cual genera la cláusula SQL NOT IN
     *    utilizando la llave y los valores proporcionados.
     *
     * 4. Si ocurre un error durante la generación de la cláusula SQL, se retorna un array con el error.
     *
     * 5. En caso de éxito, se retorna la cláusula SQL NOT IN generada.
     *
     * @param array $not_in Un arreglo asociativo que debe contener al menos las claves:
     *                      - `'llave'`  (string): El nombre de la columna sobre la que se aplicará la cláusula NOT IN.
     *                      - `'values'` (array): Un array de valores que se incluirán en la cláusula.
     *
     * @return array|string Retorna la cláusula SQL NOT IN en forma de cadena, por ejemplo:
     *                      "categoria_id NOT IN ('10','20','30')", o un array con los detalles del error en caso de fallo.
     *
     * @example Ejemplo de uso exitoso:
     * ```php
     * $not_in = [
     *     'llave'  => 'categoria_id',
     *     'values' => ['10', '20', '30']
     * ];
     * $resultado = $this->genera_not_in($not_in);
     * // Resultado esperado: "categoria_id NOT IN ('10','20','30')"
     * ```
     *
     * @example Ejemplo de error:
     * ```php
     * $not_in = [
     *     'llave'  => '', // Llave vacía provocará un error.
     *     'values' => ['10', '20', '30']
     * ];
     * $resultado = $this->genera_not_in($not_in);
     * // Resultado esperado: Array de error con el mensaje "Error al generar data in"
     * ```
     */
    private function genera_not_in(array $not_in): array|string
    {
        $data_in = $this->data_in(in: $not_in);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar data in',
                data: $data_in
            );
        }

        $not_in_sql = $this->not_in_sql(llave: $data_in->llave, values: $data_in->values);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar sql',
                data: $not_in_sql
            );
        }
        return $not_in_sql;
    }


    /**
     * REG
     * Genera la cláusula SQL NOT IN a partir de un arreglo de datos.
     *
     * Este método valida que el arreglo de entrada `$not_in` contenga las claves requeridas:
     * 'llave' y 'values'. Si la validación es exitosa, se procede a generar la cláusula SQL
     * NOT IN utilizando el método interno `genera_not_in()`. En caso de que el arreglo `$not_in`
     * esté vacío, se retorna una cadena vacía.
     *
     * El proceso es el siguiente:
     * 1. Se verifica que el arreglo `$not_in` tenga al menos un elemento. Si es así, se define un
     *    arreglo de claves requeridas: `['llave', 'values']`.
     * 2. Se utiliza el método `valida_existencia_keys()` de la propiedad `validacion` para confirmar
     *    que el arreglo `$not_in` contenga ambas claves. Si falta alguna, se retorna un error.
     * 3. Se llama al método interno `genera_not_in()` pasando el arreglo `$not_in`, el cual se encarga
     *    de generar la cláusula SQL NOT IN utilizando la llave y los valores.
     * 4. Si se produce algún error en la generación de la cláusula, se retorna el error correspondiente.
     * 5. En caso de éxito, se retorna la cláusula SQL NOT IN generada.
     *
     * @param array $not_in Arreglo asociativo que debe contener las claves:
     *                      - 'llave'  (string): el nombre de la columna sobre la cual se aplicará la cláusula NOT IN.
     *                      - 'values' (array): un array de valores que se incluirán en la cláusula.
     *
     * @return array|string Retorna una cadena que representa la cláusula SQL NOT IN, por ejemplo:
     *                      "categoria_id NOT IN ('10','20','30')". En caso de error, retorna un array
     *                      con los detalles del error.
     *
     * @example Ejemplo de uso exitoso:
     * ```php
     * $not_in = [
     *     'llave'  => 'categoria_id',
     *     'values' => ['10', '20', '30']
     * ];
     * $resultado = $this->genera_not_in_sql($not_in);
     * // Resultado esperado: "categoria_id NOT IN ('10','20','30')"
     * ```
     *
     * @example Ejemplo sin datos (arreglo vacío):
     * ```php
     * $not_in = [];
     * $resultado = $this->genera_not_in_sql($not_in);
     * // Resultado esperado: ""
     * ```
     *
     * @example Ejemplo de error:
     * ```php
     * $not_in = [
     *     'llave'  => 'categoria_id'
     *     // Falta la clave 'values'
     * ];
     * $resultado = $this->genera_not_in_sql($not_in);
     * // Resultado esperado: Array de error indicando que 'values' no existe.
     * ```
     */
    final public function genera_not_in_sql(array $not_in): array|string
    {
        $not_in_sql = '';
        if (count($not_in) > 0) {
            $keys = array('llave', 'values');
            $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $not_in);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar not_in', data: $valida);
            }
            $not_in_sql = $this->genera_not_in(not_in: $not_in);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar sql', data: $not_in_sql);
            }
        }
        return $not_in_sql;
    }


    /**
     * REG
     * Genera una condición SQL para filtrar resultados basados en una fecha.
     *
     * Esta función procesa un arreglo de datos de filtro para fechas y, mediante una serie de pasos
     * de validación y transformación, construye una cadena SQL que puede ser utilizada en una cláusula
     * WHERE para filtrar resultados según una fecha determinada.
     *
     * El flujo de ejecución es el siguiente:
     *
     * 1. **Validación de los datos del filtro de fecha:**
     *    Se llama al método `valida_data_filtro_fecha()` pasando el arreglo `$fil_fecha`.
     *    Este método valida que el arreglo contenga los índices requeridos (por ejemplo, `'campo_1'`, `'campo_2'` y `'fecha'`)
     *    y que cada uno tenga un valor no vacío y, en el caso de la fecha, que cumpla con el formato esperado.
     *    Si la validación falla, se retorna un arreglo de error.
     *
     * 2. **Generación de los datos del filtro:**
     *    Se invoca `data_filtro_fecha()` con `$fil_fecha` para transformar los datos de filtro en un objeto `stdClass`
     *    que contenga las propiedades necesarias para construir la condición SQL.
     *    Si ocurre algún error durante esta transformación, se retorna un arreglo de error.
     *
     * 3. **Obtención del operador lógico para el filtro:**
     *    Se llama al método `and_filtro_fecha()` pasando la cadena `$filtro_fecha_sql`.
     *    Este método devuelve el operador lógico (por ejemplo, "AND") o cualquier otra condición adicional que se
     *    deba aplicar en la cláusula SQL. Si falla, se retorna un error.
     *
     * 4. **Construcción de la condición SQL:**
     *    Finalmente, se utiliza el método `sql_fecha()` pasando el operador obtenido y el objeto de datos generado.
     *    Este método construye la cadena SQL final, validando nuevamente que la fecha cumpla con el formato esperado.
     *    Si ocurre un error durante la construcción, se retorna un arreglo de error.
     *
     * Si todas las operaciones se completan exitosamente, la función retorna una cadena SQL con la siguiente estructura:
     *
     *     "$and('$data->fecha' >= $data->campo_1 AND '$data->fecha' <= $data->campo_2)"
     *
     * ## Ejemplos de Uso Exitoso:
     *
     * **Ejemplo 1: Uso correcto con datos válidos**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => '2023-01-01',
     *     'campo_2' => '2023-12-31',
     *     'fecha'   => '2023-06-15'
     * ];
     * $filtro_fecha_sql = "AND";
     *
     * $sql = $this->genera_sql_filtro_fecha($fil_fecha, $filtro_fecha_sql);
     * // Resultado esperado:
     * // "AND('2023-06-15' >= 2023-01-01 AND '2023-06-15' <= 2023-12-31)"
     * ```
     *
     * **Ejemplo 2: Uso con datos que incluyen espacios**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => ' 2023-01-01 ',
     *     'campo_2' => ' 2023-12-31 ',
     *     'fecha'   => ' 2023-06-15 '
     * ];
     * $filtro_fecha_sql = "AND";
     *
     * $sql = $this->genera_sql_filtro_fecha($fil_fecha, $filtro_fecha_sql);
     * // Resultado esperado tras aplicar trim():
     * // "AND('2023-06-15' >= 2023-01-01 AND '2023-06-15' <= 2023-12-31)"
     * ```
     *
     * ## Ejemplos de Casos de Error:
     *
     * **Ejemplo 3: Falta un índice requerido**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => '2023-01-01',
     *     // Falta 'campo_2'
     *     'fecha'   => '2023-06-15'
     * ];
     *
     * $sql = $this->genera_sql_filtro_fecha($fil_fecha, "AND");
     * // Resultado esperado: Un arreglo de error indicando que "error no existe $data->campo_2".
     * ```
     *
     * **Ejemplo 4: Formato de fecha inválido**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => '2023-01-01',
     *     'campo_2' => '2023-12-31',
     *     'fecha'   => '15/06/2023'  // Formato incorrecto
     * ];
     *
     * $sql = $this->genera_sql_filtro_fecha($fil_fecha, "AND");
     * // Resultado esperado: Un arreglo de error indicando "Error al validar fecha", ya que el formato de 'fecha' es incorrecto.
     * ```
     *
     * @param array  $fil_fecha         Arreglo que contiene los datos de filtro. Debe incluir:
     *                                  - 'campo_1': Valor mínimo para la comparación de fecha.
     *                                  - 'campo_2': Valor máximo para la comparación de fecha.
     *                                  - 'fecha':   La fecha que se desea filtrar (debe tener un formato válido).
     * @param string $filtro_fecha_sql  Cadena que define el operador lógico o condiciones adicionales para el filtro SQL.
     *
     * @return array|string Retorna la cadena SQL que representa la condición de filtro si todo es válido, o un arreglo
     *                      con la información del error (generado a través de `$this->error->error()`) si alguna validación falla.
     */
    private function genera_sql_filtro_fecha(array $fil_fecha, string $filtro_fecha_sql): array|string
    {
        $valida = $this->valida_data_filtro_fecha(fil_fecha: $fil_fecha);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar fecha',
                data: $valida
            );
        }

        $data = $this->data_filtro_fecha(fil_fecha: $fil_fecha);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar datos',
                data: $data
            );
        }

        $and = $this->and_filtro_fecha(txt: $filtro_fecha_sql);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener and',
                data: $and
            );
        }

        $sql = $this->sql_fecha(and: $and, data: $data);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener sql',
                data: $sql
            );
        }
        return $sql;
    }


    /**
     * REG
     * Genera la parte SQL para un filtro especial basado en un campo, un conjunto de columnas adicionales y un arreglo de filtro.
     *
     * Este método se utiliza para construir la parte de una consulta SQL que representa un filtro especial para un campo
     * específico. El proceso es el siguiente:
     *
     * 1. Se recorta el campo `$campo` para eliminar espacios en blanco.
     * 2. Se valida que el filtro especial para el campo tenga la estructura correcta mediante el método
     *    `valida_data_filtro_especial()` de la clase `validaciones`. Si la validación falla, se retorna un error.
     * 3. Se comprueba que en el arreglo `$filtro[$campo]` exista la clave `'valor'` utilizando el método
     *    `valida_existencia_keys()`. Si la clave no existe, se retorna un error.
     * 4. Se guarda el nombre original del campo en la variable `$campo_filtro` para su uso posterior.
     * 5. Se obtiene el nombre correcto del campo para el filtro especial llamando al método `campo_filtro_especial()`,
     *    pasando el campo y el arreglo `$columnas_extra`. Esto permite que, en caso de existir una definición especial
     *    para el campo (por ejemplo, una subconsulta), se sobrescriba el nombre del campo.
     * 6. Finalmente, se genera la cadena SQL para el filtro especial mediante el método `data_sql()`, que utiliza el
     *    campo corregido, el campo original y el arreglo `$filtro` para construir la condición SQL.
     *
     * Si todas las validaciones son exitosas, el método retorna la cadena SQL resultante. En caso de error, se retorna
     * un array con los detalles del error.
     *
     * @param string $campo          El nombre del campo para el cual se genera el filtro especial.
     *                               Ejemplo: "precio"
     * @param array  $columnas_extra Array asociativo con definiciones adicionales para los campos.
     *                               Ejemplo:
     *                               ```php
     *                               [
     *                                   'precio' => 'productos.precio'
     *                               ]
     *                               ```
     * @param array  $filtro         Arreglo que define el filtro especial para el campo. Debe incluir al menos las claves:
     *                               - 'operador': el operador de comparación (por ejemplo, ">", "<", "=")
     *                               - 'valor': el valor a comparar.
     *                               Ejemplo:
     *                               ```php
     *                               [
     *                                   'precio' => [
     *                                       'operador' => '>',
     *                                       'valor' => 100
     *                                   ]
     *                               ]
     *                               ```
     *
     * @return array|string          Devuelve una cadena SQL que representa el filtro especial, o un array con información
     *                               de error si ocurre alguna falla durante el proceso.
     *
     * @example Ejemplo de uso exitoso:
     * ```php
     * // Definición de parámetros:
     * $campo = 'precio';
     * $columnas_extra = ['precio' => 'productos.precio'];
     * $filtro = [
     *     'precio' => [
     *         'operador' => '>',
     *         'valor' => 100
     *     ]
     * ];
     *
     * // Llamada al método:
     * $sql_filtro = $obj->maqueta_filtro_especial($campo, $columnas_extra, $filtro);
     *
     * // Supongamos que:
     * // - El método campo_filtro_especial() retorna "productos.precio"
     * // - El método data_sql() construye la cadena "productos.precio > '100'"
     * // Entonces, el resultado será:
     * // "productos.precio > '100'"
     * ```
     *
     * @example Ejemplo de error (falta la clave 'operador'):
     * ```php
     * $campo = 'precio';
     * $columnas_extra = ['precio' => 'productos.precio'];
     * $filtro = [
     *     'precio' => [
     *         // 'operador' no está definido
     *         'valor' => 100
     *     ]
     * ];
     *
     * $sql_filtro = $obj->maqueta_filtro_especial($campo, $columnas_extra, $filtro);
     * // Resultado esperado: Array de error indicando "Error debe existir $filtro[precio][operador]"
     * ```
     */
    private function maqueta_filtro_especial(string $campo, array $columnas_extra, array $filtro): array|string {
        $campo = trim($campo);

        $valida = (new validaciones())->valida_data_filtro_especial(campo: $campo, filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar filtro', data: $valida);
        }

        $keys = ['valor'];
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $filtro[$campo]);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar filtro', data: $valida);
        }

        $campo_filtro = $campo;

        $campo = $this->campo_filtro_especial(campo: $campo, columnas_extra: $columnas_extra);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener campo', data: $campo);
        }

        $data_sql = $this->data_sql(campo: $campo, campo_filtro: $campo_filtro, filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al genera sql', data: $data_sql);
        }

        return $data_sql;
    }


    /**
     * REG
     * Genera la cláusula SQL NOT IN a partir de una llave (nombre de columna) y un conjunto de valores.
     *
     * Esta función realiza las siguientes operaciones:
     * 1. Elimina espacios en blanco de la llave mediante `trim()` y verifica que la llave no esté vacía.
     *    Si la llave está vacía, retorna un error utilizando `$this->error->error()` con el mensaje
     *    "Error la llave esta vacia".
     * 2. Convierte el array de valores en una cadena SQL adecuada para una cláusula IN utilizando el método
     *    `values_sql_in()`. Si ocurre algún error durante este proceso, retorna dicho error.
     * 3. Si la cadena de valores generada no es vacía, construye la cláusula SQL NOT IN concatenando la llave,
     *    el literal "NOT IN" y la cadena de valores entre paréntesis.
     * 4. Finalmente, retorna la cláusula SQL resultante o, en caso de error, un array con los detalles del error.
     *
     * @param string $llave  Nombre del campo (columna) para la cláusula NOT IN. Este parámetro no debe estar vacío.
     * @param array  $values Array de valores que se incluirán en la cláusula NOT IN.
     *
     * @return array|string Retorna una cadena con la cláusula SQL NOT IN, por ejemplo:
     *                      "categoria_id NOT IN ('10','20','30')",
     *                      o un array con la información del error en caso de fallo.
     *
     * @example Ejemplo 1: Uso exitoso
     * ```php
     * $llave = "categoria_id";
     * $values = ["10", "20", "30"];
     * $resultado = $this->not_in_sql($llave, $values);
     * // Resultado esperado: "categoria_id NOT IN ('10','20','30')"
     * ```
     *
     * @example Ejemplo 2: Error por llave vacía
     * ```php
     * $llave = "";
     * $values = ["1", "2"];
     * $resultado = $this->not_in_sql($llave, $values);
     * // Resultado esperado: Array de error con mensaje "Error la llave esta vacia"
     * ```
     */
    private function not_in_sql(string $llave, array $values): array|string
    {
        $llave = trim($llave);
        if ($llave === '') {
            return $this->error->error(
                mensaje: 'Error la llave esta vacia',
                data: $llave,
                es_final: true
            );
        }

        $values_sql = $this->values_sql_in(values: $values);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar sql',
                data: $values_sql
            );
        }

        $not_in_sql = '';
        if ($values_sql !== '') {
            $not_in_sql .= "$llave NOT IN ($values_sql)";
        }

        return $not_in_sql;
    }


    /**
     * REG
     * Genera y actualiza la cláusula SQL para un filtro especial, integrando la condición SQL generada
     * a partir del arreglo de filtro especial con una cláusula SQL previa, si existe.
     *
     * El método sigue estos pasos:
     *
     * 1. Extrae el primer campo (clave) del arreglo `$filtro_esp` mediante `key($filtro_esp)`,
     *    lo recorta y lo asigna a `$campo`.
     *
     * 2. Valida la estructura del filtro especial para ese campo llamando a
     *    {@see validaciones;::valida_data_filtro_especial()}.
     *    - Si la validación falla, retorna un error con los detalles obtenidos.
     *
     * 3. Llama al método {@see maqueta_filtro_especial()} para generar la parte SQL correspondiente
     *    al filtro especial basado en el campo, utilizando las definiciones adicionales en `$columnas_extra`
     *    y el arreglo `$filtro_esp`. Si ocurre un error, retorna el error correspondiente.
     *
     * 4. Utiliza el método {@see genera_filtro_especial()} para integrar la nueva condición SQL (`$data_sql`)
     *    con la cláusula SQL especial preexistente (`$filtro_especial_sql`):
     *    - Si `$filtro_especial_sql` está vacío, se inicializa con `$data_sql`.
     *    - Si no, se verifica que en `$filtro_esp[$campo]` exista la clave `'comparacion'` y que `$data_sql`
     *      no esté vacío, para luego concatenar la comparación (por ejemplo, "AND" u "OR") y `$data_sql`
     *      a la cláusula existente.
     *    - Si ocurre algún error en este paso, se retorna el error correspondiente.
     *
     * 5. Finalmente, retorna la cláusula SQL especial resultante.
     *
     * @param array  $columnas_extra      Array asociativo con definiciones adicionales para los campos.
     *                                    Ejemplo:
     *                                    ```php
     *                                    [
     *                                        'tabla.precio' => 'productos.precio'
     *                                    ]
     *                                    ```
     * @param array  $filtro_esp          Arreglo que define el filtro especial para un campo.
     *                                    Debe tener una estructura similar a:
     *                                    ```php
     *                                    [
     *                                        'tabla.precio' => [
     *                                            'operador' => '>',
     *                                            'valor' => 100,
     *                                            'comparacion' => 'AND'
     *                                        ]
     *                                    ]
     *                                    ```
     * @param string $filtro_especial_sql La cláusula SQL especial preexistente a la que se desea agregar
     *                                    la nueva condición. Si está vacía, se usará el resultado de `$data_sql`.
     *
     * @return array|string              Devuelve la cláusula SQL final del filtro especial si la operación es exitosa;
     *                                    de lo contrario, retorna un array con información de error.
     *
     * @example Ejemplo 1: Sin cláusula SQL previa
     * ```php
     * // Supongamos que:
     * // - $columnas_extra = ['tabla.precio' => 'productos.precio'];
     * // - $filtro_esp = [
     * //       'tabla.precio' => [
     * //           'operador' => '>',
     * //           'valor' => 100,
     * //           'comparacion' => 'AND'
     * //       ]
     * //   ];
     * // - $filtro_especial_sql = ""; (vacío)
     *
     * $resultado = $obj->obten_filtro_especial($columnas_extra, $filtro_esp, $filtro_especial_sql);
     * // Resultado esperado (si no hay errores):
     * // "productos.precio > '100'"
     * ```
     *
     * @example Ejemplo 2: Con cláusula SQL previa
     * ```php
     * // Supongamos que:
     * // - $columnas_extra = ['tabla.precio' => 'productos.precio'];
     * // - $filtro_esp = [
     * //       'tabla.precio' => [
     * //           'operador' => '<',
     * //           'valor' => 200,
     * //           'comparacion' => 'OR'
     * //       ]
     * //   ];
     * // - $filtro_especial_sql = "productos.precio = '150'";
     *
     * $resultado = $obj->obten_filtro_especial($columnas_extra, $filtro_esp, $filtro_especial_sql);
     * // Resultado esperado:
     * // "productos.precio = '150' OR productos.precio < '200'"
     * ```
     *
     * @example Ejemplo 3: Error en la estructura del filtro especial
     * ```php
     * // Si $filtro_esp no contiene la clave 'operador' para el campo
     * $columnas_extra = ['tabla.precio' => 'productos.precio'];
     * $filtro_esp = [
     *     'tabla.precio' => [
     *         // Falta 'operador'
     *         'valor' => 100
     *     ]
     * ];
     * $filtro_especial_sql = "";
     *
     * $resultado = $obj->obten_filtro_especial($columnas_extra, $filtro_esp, $filtro_especial_sql);
     * // Resultado esperado: Array de error indicando que $filtro_esp['tabla.precio']['operador'] debe existir.
     * ```
     */
    private function obten_filtro_especial(
        array $columnas_extra,
        array $filtro_esp,
        string $filtro_especial_sql
    ): array|string {
        $campo = key($filtro_esp);
        $campo = trim($campo);

        $valida = (new validaciones())->valida_data_filtro_especial(campo: $campo, filtro: $filtro_esp);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error en filtro ", data: $valida);
        }

        $data_sql = $this->maqueta_filtro_especial(campo: $campo, columnas_extra: $columnas_extra, filtro: $filtro_esp);
        if (errores::$error) {
            return $this->error->error(mensaje:"Error filtro", data: $data_sql);
        }

        $filtro_especial_sql_r = $this->genera_filtro_especial(
            campo: $campo,
            data_sql: $data_sql,
            filtro_esp: $filtro_esp,
            filtro_especial_sql: $filtro_especial_sql
        );
        if (errores::$error) {
            return $this->error->error(mensaje:"Error filtro", data: $filtro_especial_sql_r);
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
     * REG
     * Valida los datos de un filtro de fecha.
     *
     * Este método se encarga de verificar que el arreglo proporcionado contenga las claves requeridas para un filtro de fecha
     * y que el valor asociado a la clave "fecha" cumpla con el formato esperado. La validación se realiza en dos pasos:
     *
     * 1. **Verificación de claves obligatorias:**
     *    Se valida que el arreglo `$fil_fecha` incluya las claves `'campo_1'`, `'campo_2'` y `'fecha'` utilizando el método
     *    `valida_existencia_keys()` de la clase de validación. Si alguna de estas claves falta o es inválida, se retorna un
     *    array de error.
     *
     * 2. **Validación del formato de la fecha:**
     *    Se utiliza el método `valida_fecha()` para confirmar que el valor contenido en `$fil_fecha['fecha']` cumpla con el
     *    formato de fecha esperado (por defecto, formato "yyyy-mm-dd"). Si la fecha no es válida, se retorna un array de error.
     *
     * Si todas las validaciones se cumplen correctamente, el método retorna `true`.
     *
     * ## Ejemplos de Uso Exitoso
     *
     * **Ejemplo 1: Registro con datos correctos en formato array**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => 'valor1',
     *     'campo_2' => 'valor2',
     *     'fecha'   => '2023-05-20'
     * ];
     * $resultado = $this->valida_data_filtro_fecha($fil_fecha);
     * // Resultado esperado: true
     * ```
     *
     * **Ejemplo 2: Registro con datos correctos en formato array (otra fecha válida)**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => 'dato1',
     *     'campo_2' => 'dato2',
     *     'fecha'   => '2022-12-31'
     * ];
     * $resultado = $this->valida_data_filtro_fecha($fil_fecha);
     * // Resultado esperado: true
     * ```
     *
     * ## Ejemplos de Error
     *
     * **Ejemplo 3: Falta la clave "fecha" en el arreglo**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => 'valor1',
     *     'campo_2' => 'valor2'
     * ];
     * $resultado = $this->valida_data_filtro_fecha($fil_fecha);
     * // Resultado esperado: Array de error indicando "Error al validar existencia de key" para la clave "fecha".
     * ```
     *
     * **Ejemplo 4: Formato de fecha inválido**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => 'valor1',
     *     'campo_2' => 'valor2',
     *     'fecha'   => '31-12-2022'  // Formato incorrecto; se espera "yyyy-mm-dd"
     * ];
     * $resultado = $this->valida_data_filtro_fecha($fil_fecha);
     * // Resultado esperado: Array de error indicando "Error al validar fecha" debido a un formato no válido.
     * ```
     *
     * @param array $fil_fecha Arreglo asociativo que contiene los datos del filtro de fecha.
     *                         Se espera que incluya las claves:
     *                         - **campo_1**: Primer campo del filtro.
     *                         - **campo_2**: Segundo campo del filtro.
     *                         - **fecha**: Valor que representa la fecha a validar (en formato "yyyy-mm-dd" por defecto).
     *
     * @return true|array Retorna `true` si todas las validaciones son exitosas; en caso de error, retorna un array
     *                    con la información detallada del error.
     */
    private function valida_data_filtro_fecha(array $fil_fecha): true|array
    {
        $keys = array('campo_1', 'campo_2', 'fecha');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $fil_fecha);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar filtro',
                data: $valida
            );
        }
        $valida = $this->validacion->valida_fecha(fecha: $fil_fecha['fecha']);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar fecha',
                data: $valida
            );
        }
        return true;
    }


    /**
     * REG
     * Valida la estructura y el contenido de un filtro de fecha.
     *
     * Este método se encarga de validar que el arreglo proporcionado `$fil_fecha` contenga las claves necesarias para definir un filtro de fecha,
     * y que el valor asociado a la clave `'fecha'` sea una fecha válida. La validación se realiza en dos pasos:
     *
     * 1. **Validación de existencia de claves obligatorias:**
     *    - Se define un arreglo de claves obligatorias: `['campo_1', 'campo_2', 'fecha']`.
     *    - Se utiliza el método `valida_existencia_keys()` de la clase de validación para verificar que todas estas claves existan en `$fil_fecha`.
     *    - Si alguna de estas claves falta, se retorna un arreglo de error indicando el problema.
     *
     * 2. **Validación del formato de la fecha:**
     *    - Se define un arreglo que contiene únicamente la clave `['fecha']`.
     *    - Se invoca el método `fechas_in_array()` pasándole el arreglo `$fil_fecha` y el arreglo de claves `['fecha']` para validar que el valor
     *      asociado a `'fecha'` cumpla con el formato esperado (por ejemplo, "yyyy-mm-dd", "yyyy-mm-dd hh:mm:ss", etc., según la configuración interna).
     *    - Si la validación del formato falla, se retorna un arreglo de error con la información correspondiente.
     *
     * Si ambas validaciones se realizan correctamente, el método retorna `true`.
     *
     * ## Ejemplos de Uso Exitoso:
     *
     * **Ejemplo 1: Filtro de fecha completo y correcto**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => '2023-01-01',  // Fecha de inicio
     *     'campo_2' => '2023-12-31',  // Fecha de fin
     *     'fecha'   => '2023-06-15'   // Fecha a validar, en formato "yyyy-mm-dd"
     * ];
     *
     * $resultado = $this->valida_filtro_fecha($fil_fecha);
     * // $resultado será true, ya que se cumplen:
     * // - Las claves 'campo_1', 'campo_2' y 'fecha' existen.
     * // - La fecha '2023-06-15' es válida según el formato esperado.
     * ```
     *
     * **Ejemplo 2: Filtro de fecha con formato correcto en un arreglo adicional**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => '2022-05-01',
     *     'campo_2' => '2022-05-31',
     *     'fecha'   => '2022-05-15'
     * ];
     *
     * $resultado = $this->valida_filtro_fecha($fil_fecha);
     * // $resultado será true.
     * ```
     *
     * ## Ejemplos de Casos de Error:
     *
     * **Ejemplo 3: Falta una clave obligatoria**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => '2023-01-01',
     *     // Falta 'campo_2'
     *     'fecha'   => '2023-06-15'
     * ];
     *
     * $resultado = $this->valida_filtro_fecha($fil_fecha);
     * // Se retornará un arreglo de error indicando "Error al validar filtro" por la ausencia de la clave 'campo_2'.
     * ```
     *
     * **Ejemplo 4: Fecha con formato inválido**
     * ```php
     * $fil_fecha = [
     *     'campo_1' => '2023-01-01',
     *     'campo_2' => '2023-12-31',
     *     'fecha'   => '15/06/2023'  // Formato incorrecto
     * ];
     *
     * $resultado = $this->valida_filtro_fecha($fil_fecha);
     * // Se retornará un arreglo de error indicando "Error al validar filtro" debido a que la fecha no cumple con el formato esperado.
     * ```
     *
     * @param array $fil_fecha Arreglo asociativo que representa el filtro de fecha. Se espera que contenga al menos las siguientes claves:
     *                         - 'campo_1': Representa el límite inferior (mínimo) de la fecha.
     *                         - 'campo_2': Representa el límite superior (máximo) de la fecha.
     *                         - 'fecha':   La fecha a validar, que debe cumplir con el formato esperado.
     *
     * @return bool|array Retorna `true` si el filtro de fecha es válido. En caso contrario, retorna un arreglo con la información del error
     *                    generado por el método `$this->error->error()`.
     */
    private function valida_filtro_fecha(array $fil_fecha): bool|array
    {
        $keys = array('campo_1', 'campo_2', 'fecha');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $fil_fecha);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar filtro', data: $valida);
        }

        $keys = array('fecha');
        $valida = $this->validacion->fechas_in_array(data: $fil_fecha, keys: $keys);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar filtro', data: $valida);
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
