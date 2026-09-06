<?php

namespace Tests\Unit;

use App\Models\Advertisement;
use PHPUnit\Framework\TestCase;

class AdvertisementPositionsTest extends TestCase
{
    /**
     * Guarda contra regressão: toda posição consultada pelas views precisa
     * existir em getPositions() (senão o espaço nunca exibe anúncio e o
     * fornecedor não consegue comprá-lo).
     */
    public function test_positions_incluem_todas_as_usadas_nas_views(): void
    {
        $positions = array_keys(Advertisement::getPositions());

        foreach ([
            'banner_topo', 'sidebar_guia', 'meio_blog', 'post_top', 'post_footer', 'banner_mobile_footer',
            'setor_vagas', 'setor_racas', 'setor_analises', 'setor_eventos', 'setor_canis', 'setor_classificados', 'setor_revistas',
        ] as $expected) {
            $this->assertContains($expected, $positions, "Posição ausente em getPositions(): {$expected}");
        }
    }

    public function test_cada_posicao_tem_medida_e_formato(): void
    {
        $specs = Advertisement::getPositionSpecs();

        $this->assertSame(array_keys($specs), array_keys(Advertisement::getPositions()));

        foreach ($specs as $key => $spec) {
            $this->assertNotEmpty($spec['label'], "Label vazia em {$key}");
            $this->assertNotEmpty($spec['location'], "Location vazia em {$key}");
            $this->assertNotEmpty($spec['format'], "Format vazio em {$key}");
            $this->assertGreaterThan(0, $spec['width'], "Largura inválida em {$key}");
            $this->assertGreaterThan(0, $spec['height'], "Altura inválida em {$key}");
            $this->assertSame(
                $spec['width'] . ' × ' . $spec['height'] . ' px',
                Advertisement::dimensionFor($key)
            );
        }

        $this->assertSame('1200 × 160 px', Advertisement::dimensionFor('banner_topo'));
        $this->assertSame('300 × 250 px', Advertisement::dimensionFor('sidebar_guia'));
        $this->assertSame('320 × 50 px', Advertisement::dimensionFor('banner_mobile_footer'));
        $this->assertSame('—', Advertisement::dimensionFor('posicao_inexistente'));
    }
}
