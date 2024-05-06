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


}