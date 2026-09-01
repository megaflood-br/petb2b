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

        foreach (['banner_topo', 'sidebar_guia', 'meio_blog', 'post_top', 'post_footer', 'banner_mobile_footer'] as $expected) {
            $this->assertContains($expected, $positions, "Posição ausente em getPositions(): {$expected}");
        }
    }
}
