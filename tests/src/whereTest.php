<?php

use gamboamartin\errores\errores;
use gamboamartin\src\where;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class whereTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_and_filtro_fecha(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $filtro_fecha_sql = '';
        $resultado = $wh->and_filtro_fecha($filtro_fecha_sql);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;


        $filtro_fecha_sql = 'a';
        $resultado = $wh->and_filtro_fecha($filtro_fecha_sql);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' AND ',$resultado);
        errores::$error = false;
    }

    public function test_asigna_data_filtro(): void
    {
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $filtro_especial_sql = '';
        $filtro_extra_sql = '';
        $filtro_rango_sql = '';
        $filtro_fecha_sql = '';
        $not_in_sql = '';
        $sentencia = '';
        $sql_extra = '';
        $in = '';
        $resultado = $wh->asigna_data_filtro('',$filtro_especial_sql, $filtro_extra_sql, $filtro_fecha_sql,
            $filtro_rango_sql, $in, $not_in_sql, $sentencia, $sql_extra);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $filtro_especial_sql = '';
        $filtro_extra_sql = '';
        $filtro_rango_sql = 'c';
        $filtro_fecha_sql = '';
        $not_in_sql = '';
        $sentencia = '';
        $sql_extra = '';
        $in = 'a';
        $resultado = $wh->asigna_data_filtro('',$filtro_especial_sql, $filtro_extra_sql, $filtro_fecha_sql,
            $filtro_rango_sql, $in, $not_in_sql, $sentencia, $sql_extra);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado->in);
        $this->assertEquals('c', $resultado->filtro_rango);


        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $filtro_especial_sql = '';
        $filtro_extra_sql = '';
        $filtro_rango_sql = 'c';
        $filtro_fecha_sql = '';
        $not_in_sql = '';
        $sentencia = '';
        $sql_extra = '';
        $in = 'a';
        $diferente_de_sql = 'dif';
        $resultado = $wh->asigna_data_filtro($diferente_de_sql,$filtro_especial_sql, $filtro_extra_sql,
            $filtro_fecha_sql, $filtro_rango_sql, $in, $not_in_sql, $sentencia, $sql_extra);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado->in);
        $this->assertEquals('c', $resultado->filtro_rango);
        $this->assertEquals('dif', $resultado->diferente_de);



        errores::$error = false;
    }

    public function test_campo(): void
    {
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $data = '';
        $key = '';
        $resultado = $wh->campo(data: $data,key:  $key);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error key vacio', $resultado['mensaje']);

        errores::$error = false;

        $data = '';
        $key = 'a';
        $resultado = $wh->campo(data: $data,key:  $key);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado);

        errores::$error = false;

        $data = array();
        $key = 'a';
        $data['b'] = '';

        $resultado = $wh->campo(data: $data,key:  $key);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado);

        errores::$error = false;

        $data = array();
        $key = 'a';
        $data['a'] = '';

        $resultado = $wh->campo(data: $data,key:  $key);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado);

        $data = array();
        $key = 'a';
        $data['campo'] = 'x';

        $resultado = $wh->campo(data: $data,key:  $key);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado);
        errores::$error = false;
    }

    public function test_campo_data_filtro(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $data_filtro = array();
        $data_filtro['a'] = '';
        $resultado = $wh->campo_data_filtro($data_filtro);
        //print_r($resultado);exit;
        $this->assertEquals('a', $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_campo_filtro_especial(): void
    {
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $campo = 'a';
        $columnas_extra = array();
        $columnas_extra['a'] = 'x';
        $resultado = $wh->campo_filtro_especial($campo, $columnas_extra);

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado);

        errores::$error = false;
    }

    public function test_comparacion(){

        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);
        $data = array();
        $resultado = $wh->comparacion(data: $data,default: '');
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;
    }

    public function test_comparacion_pura(){

        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);
        $data = array();
        $columnas_extra = array();
        $key = '';
        $resultado = $wh->comparacion_pura($columnas_extra, $data, $key);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error key vacio', $resultado['mensaje']);

        errores::$error = false;


        $resultado = $wh->comparacion_pura($columnas_extra, $data, $key);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error key vacio', $resultado['mensaje']);

        errores::$error = false;

        $data[] = '';
        $resultado = $wh->comparacion_pura($columnas_extra, $data, $key);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error key vacio', $resultado['mensaje']);


        errores::$error = false;
        $data = array();
        $data['value'] = '';
        $key = 'x';
        $resultado = $wh->comparacion_pura($columnas_extra, $data, $key);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado->campo);
        $this->assertEquals('', $resultado->value);


        errores::$error = false;

        $resultado = $wh->comparacion_pura($columnas_extra, $data, $key);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado->campo);
        $this->assertEquals('', $resultado->value);

        errores::$error = false;


    }

    public function test_data_sql(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $campo = 'z';
        $campo_filtro = 'x';
        $filtro = array();
        $filtro['x']['operador'] = 's';
        $filtro['x']['valor'] = 's';

        $resultado = $wh->data_sql($campo, $campo_filtro, $filtro);

        //print_r($resultado);exit;
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(" z s 's' ", $resultado);
        errores::$error = false;
    }

    public function test_data_sql_base(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $campo = 'c';
        $campo_filtro = 'a';
        $filtro = array();
        $filtro['a']['operador'] = '=>';
        $filtro['a']['valor'] = '';
        $resultado = $wh->data_sql_base($campo, $campo_filtro, $filtro);
        //print_r($resultado);exit;
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(" c => '' ", $resultado);
        errores::$error = false;
    }

    public function test_data_sql_campo(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $campo = 'v';
        $campo_filtro = 'a';
        $filtro = array();
        $filtro['a']['operador'] = 'c';
        $filtro['a']['valor'] = '';
        $resultado = $wh->data_sql_campo($campo, $campo_filtro, $filtro);
        // print_r($resultado);exit;
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("'v'c", $resultado);

        errores::$error = false;
    }

    public function test_es_subquery(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $campo = 'a';
        $columnas_extra = array();
        $resultado = $wh->es_subquery($campo, $columnas_extra);
        //print_r($resultado);exit;
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

        errores::$error = false;

        $campo = 'a';
        $columnas_extra['a'] = '';
        $resultado = $wh->es_subquery($campo, $columnas_extra);
        //print_r($resultado);exit;
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);


        errores::$error = false;
    }

    public function test_genera_and(){

        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);
        $filtro = array();
        $columnas_extra = array();
        $resultado = $wh->genera_and($columnas_extra, $filtro);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;

        $filtro[] = '';
        $resultado  = $wh->genera_and($columnas_extra, $filtro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Los key deben de ser campos asoci', $resultado['mensaje']);

        errores::$error = false;

        $filtro = array();
        $filtro['x'] = '';
        $resultado  = $wh->genera_and($columnas_extra, $filtro);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals( "x = ''", $resultado);
        errores::$error = false;
    }



    public function test_genera_and_textos(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $columnas_extra = array();
        $filtro = array();
        $resultado = $wh->genera_and_textos($columnas_extra, $filtro);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals( "", $resultado);
        errores::$error = false;
    }

    public function test_valida_campo_filtro(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $campo = '';
        $campo_filtro = '';
        $filtro = array();
        $resultado = $wh->valida_campo_filtro($campo, $campo_filtro, $filtro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error campo_filtro esta vacio',$resultado['mensaje_limpio']);
        errores::$error = false;

        $campo = '';
        $campo_filtro = 'a';
        $filtro = array();
        $resultado = $wh->valida_campo_filtro($campo, $campo_filtro, $filtro);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error campo esta vacio',$resultado['mensaje_limpio']);
        errores::$error = false;

        $campo = 'b';
        $campo_filtro = 'a';
        $filtro = array();
        $resultado = $wh->valida_campo_filtro($campo, $campo_filtro, $filtro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error no existe $filtro[a]',$resultado['mensaje_limpio']);

        errores::$error = false;

        $campo = 'b';
        $campo_filtro = 'a';
        $filtro = array();
        $filtro['a'] = '';
        $resultado = $wh->valida_campo_filtro($campo, $campo_filtro, $filtro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error no es un array $filtro[a]',$resultado['mensaje_limpio']);

        errores::$error = false;

        $campo = 'b';
        $campo_filtro = 'a';
        $filtro = array();
        $filtro['a'] = array();
        $resultado = $wh->valida_campo_filtro($campo, $campo_filtro, $filtro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error no existe $filtro[a][operador]',$resultado['mensaje_limpio']);

        errores::$error = false;

        $campo = 'b';
        $campo_filtro = 'a';
        $filtro = array();
        $filtro['a']['operador'] = '';
        $resultado = $wh->valida_campo_filtro($campo, $campo_filtro, $filtro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error no existe $filtro[a][valor]',$resultado['mensaje_limpio']);

        errores::$error = false;

        $campo = 'b';
        $campo_filtro = 'a';
        $filtro = array();
        $filtro['a']['operador'] = '';
        $filtro['a']['valor'] = '';
        $resultado = $wh->valida_campo_filtro($campo, $campo_filtro, $filtro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error esta vacio $filtro[a][operador]',$resultado['mensaje_limpio']);

        errores::$error = false;

        $campo = 'b';
        $campo_filtro = 'a';
        $filtro = array();
        $filtro['a']['operador'] = 'g';
        $filtro['a']['valor'] = '';
        $resultado = $wh->valida_campo_filtro($campo, $campo_filtro, $filtro);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;
    }
    public function test_value(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $data = '';
        $resultado = $wh->value($data);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;
    }


}