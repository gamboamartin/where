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