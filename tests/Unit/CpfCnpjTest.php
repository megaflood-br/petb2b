<?php

namespace Tests\Unit;

use App\Support\CpfCnpj;
use PHPUnit\Framework\TestCase;

class CpfCnpjTest extends TestCase
{
    public function test_aceita_cpf_e_cnpj_validos(): void
    {
        $this->assertTrue(CpfCnpj::isValid('390.533.447-05'));
        $this->assertTrue(CpfCnpj::isValid('12.345.678/0001-95'));
        $this->assertEquals('12345678000195', CpfCnpj::normalize('12.345.678/0001-95'));
        $this->assertEquals('39053344705', CpfCnpj::normalize('390.533.447-05'));
        $this->assertEquals('CPF', CpfCnpj::label('39053344705'));
        $this->assertEquals('CNPJ', CpfCnpj::label('12345678000195'));
        $this->assertEquals('390.533.447-05', CpfCnpj::format('39053344705'));
        $this->assertEquals('12.345.678/0001-95', CpfCnpj::format('12345678000195'));
    }

    public function test_rejeita_vazios_repetidos_e_digitos_errados(): void
    {
        $this->assertFalse(CpfCnpj::isValid(null));
        $this->assertFalse(CpfCnpj::isValid(''));
        $this->assertFalse(CpfCnpj::isValid('00000000000'));
        $this->assertFalse(CpfCnpj::isValid('00000000000000'));
        $this->assertFalse(CpfCnpj::isValid('12.345.678/0001-99'));
        $this->assertFalse(CpfCnpj::isValid('111.111.111-11'));
        $this->assertNull(CpfCnpj::normalize('00000000000'));
    }
}
