<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Curso;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CursoModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function un_curso_pertenece_a_una_categoria()
    {
        $categoria = Categoria::factory()->create(['nombre' => 'IA']);
        $curso = Curso::factory()->create(['categoria_id' => $categoria->id]);

        $this->assertInstanceOf(Categoria::class, $curso->categoria);
        $this->assertEquals('IA', $curso->categoria->nombre);
    }

    /** @test */
    public function una_categoria_tiene_muchos_cursos()
    {
        $categoria = Categoria::factory()->create();
        Curso::factory()->count(3)->create(['categoria_id' => $categoria->id]);

        $this->assertCount(3, $categoria->cursos);
    }

    /** @test */
    public function scope_buscar_filtra_por_nombre()
    {
        Curso::factory()->create(['nombre' => 'Machine Learning']);
        Curso::factory()->create(['nombre' => 'Lenguaje de programación']);

        $resultados = Curso::buscar('Lenguaje')->get();

        $this->assertCount(1, $resultados);
        $this->assertEquals('Lenguaje de programación', $resultados->first()->nombre);
    }

    /** @test */
    public function scope_por_categoria_filtra_correctamente()
    {
        $ia = Categoria::factory()->create(['nombre' => 'IA']);
        $ropa = Categoria::factory()->create(['nombre' => 'Python']);

        Curso::factory()->count(2)->create(['categoria_id' => $ia->id]);
        Curso::factory()->create(['categoria_id' => $ropa->id]);

        $resultados = Curso::porCategoria($ia->id)->get();

        $this->assertCount(2, $resultados);
    }

    /** @test */
    public function scope_vacantes_baja_filtra_correctamente()
    {
        Curso::factory()->create(['vacantes' => 5]);
        Curso::factory()->create(['vacantes' => 15]);

        $resultados = Curso::pocasVacantes()->get();

        $this->assertCount(1, $resultados);
        $this->assertEquals(5, $resultados->first()->vacantes);
    }

    /** @test */
    public function puede_combinar_multiples_scopes()
    {
        $categoria = Categoria::factory()->create();

        Curso::factory()->create([
            'nombre' => 'Python intensivo',
            'vacantes' => 5,
            'categoria_id' => $categoria->id
        ]);

        $resultados = Curso::buscar('Python')
            ->porCategoria($categoria->id)
            ->pocasVacantes()
            ->get();

        $this->assertCount(1, $resultados);
    }
}
